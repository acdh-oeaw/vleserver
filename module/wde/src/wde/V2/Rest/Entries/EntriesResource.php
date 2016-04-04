<?php

namespace wde\V2\Rest\Entries;

use wde\V2\Rest\Common\AccessCheckingTSResource;
use wde\V2\Rest\Common\LimitedColumnTableGateway;
use Zend\Db\TableGateway\TableGatewayInterface as TableGateway;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use ZF\ApiProblem\ApiProblem;

class EntriesResource extends AccessCheckingTSResource {
    /** @var boolean */
    protected $isUpdatingDontSetLock = false;
    /** @var boolean */
    protected $doCopyOnWrite = true;
    
    private function charlyEncode($string) {
        $convmap = array(0xFF, 0x2FFFF, 0, 0xFFFF);
        $semicolMasked = str_replace(';', '.,', $string);
        $htmlEncoded = mb_encode_numericentity($semicolMasked, $convmap, 'UTF-8');
        return str_replace('.,', ';', str_replace(';', '#9#', str_replace('&#', '#8#', $htmlEncoded)));
    }
            
    public function __construct(TableGateway $table, $identifierName, $collectionClass) {
        $this->linkedTableExts = array('ndx', 'cow', 'lck');
        parent::__construct($table, $identifierName, $collectionClass);
    }
    
    public function fetchAll($data = array()) {
        if (($tryGetAccessAndSwitchTable = parent::fetchAll($data)) instanceof ApiProblem) { return $tryGetAccessAndSwitchTable; }
        $explicitPageSize = $this->getEvent()->getRequest()->getQuery('pageSize');
        $filter = new Where();
        $join = null;
        $order = array('id ASC');
        foreach ($data as $key=>$value) {
            switch ($key) {
                case 'sid':
                    $filter->equalTo('sid', $value);
                    $order = array();
                    break;
                case 'idRange':
                    $bounds = explode('-', $value);
                    $lowerBound = $bounds[0];
                    $upperBound = $bounds[1];
                    $filter->greaterThanOrEqualTo('id', $lowerBound)->AND
                           ->lessThanOrEqualTo('id', $upperBound);
                    break;
                case 'lem':
                    $filter->like('lemma', $value);
                    $order = array('lemma ASC');
                    break;
                case 'xpath':
                    if (strpos($value, '//') === 0) {
                        $value = $this->xpath2NdxSqlLike($value, $data);
                        if ($value instanceof ApiProblem) {return $value;}
                    }
                    $value = str_replace('*', '%', $value);
                    $ndxTable = $this->realTableName . '_ndx';
                    $join = array();
                    $join['tableName'] = "$ndxTable";
                    $join['onExpression'] = "$this->realTableName.id = $ndxTable.id";
                    $join['groupBy'] = "$this->realTableName.id";
                    if (strpos($value, '%') !== false) {
                        $filter->like("$ndxTable.xpath", "$value");
                    } else {
                        $filter->like("$ndxTable.xpath", "%$value%");
                    }
                    if (isset($data['txt'])) {
                      //XPath contains txt
                      $txtValue = str_replace('*', '%', $data['txt']);
                      $filter->AND
                              ->NEST
                                ->like("$ndxTable.txt", $txtValue)
                                ->OR->like("$ndxTable.txt", $this->charlyEncode($txtValue))
                              ->UNNESST;
                    } else { 
                      //XPath exists 
                    }
                    $order = array('lemma ASC');
                    break;
                case 'txt':
                    if (!isset($data['xpath'])) {
                        //search for every occurence as text regardless of the xpath
                        $value = str_replace('*', '%', $value);
                        $ndxTable = $this->realTableName . '_ndx';
                        $join = array();
                        $join['tableName'] = "$ndxTable";
                        $join['onExpression'] = "$this->realTableName.id = $ndxTable.id";
                        $join['groupBy'] = "$this->realTableName.id";
                        $filter->like("$ndxTable.txt", "$value");
                        $filter->OR->like("$ndxTable.txt", $this->charlyEncode($value));
                        $order = array('lemma ASC');
                    }
                    // processed above
                    break;
                   
            }
        }
        if ($explicitPageSize !== null && $explicitPageSize <= 10) {
            $adapter = new LimitedColumnTableGateway($this->table, array(), $filter, $order);
        } else {
            $adapter = new LimitedColumnTableGateway($this->table, array('id', 'sid', 'lemma', 'status', 'locked', 'type'), $filter, $order);
        }
        if (isset($join)) {
            $adapter->join($join['tableName'], $join['onExpression'])
                    ->group($join['groupBy']);
        }      
        return new EntriesCollection($adapter);
    }
    
    private function xpath2NdxSqlLike($value, &$data) {
        $functionPredicateSearch = '~\[(?<relation>[-a-z]+)\((?<node>[^,])+,\s*"(?<term>.+)"\)\]~';
        $equalsPredicateSearch = '~\[?<node>([^=])+\s*"(?<term>.+)"\]~';
        $value = str_replace('//', '%', $value);
        if (preg_match('~\[.+\]$~', $value)) {
            $this->getAndRemoveSearchTerm($value, $data);
        }
        $value = str_replace('%text()', '', $value);
        $value .= '%';
        $this->splitOr($value);
        return $value;
    }
    
    private function getAndRemoveSearchTerm(&$value, &$data) {
        try {
            // find query txt
            $search = array();
            if (preg_match_all($functionPredicateSearch, $value, $search, PREG_SET_ORDER)) {
                $search = end($search);
                $value = preg_replace($functionPredicateSearch, '', $value);
                switch ($search['relation']) {
                    case "starts-with":
                        $data['txt'] = $search['term'].'*';
                        break;
                    case "contains":
                        $data['txt'] ='*'. $search['term'].'*';
                        break;
                    case "ends-with":
                        $data['txt'] ='*'. $search['term'];
                        break;
                    default: throw new \Exception('Can\'t create SQL from XPath! Check implementation.'); 
                }
            } elseif (preg_match_all($equalsPredicateSearch, $value, $search, PREG_SET_ORDER)) {
                $search = end($search);
                $value = preg_replace($equalsPredicateSearch, '', $value);
                $data['txt'] = $search['term'];
            } else {
                $this->xpath2SQLFailed();
            }
        } catch (\Exception $exc) {
            return new ApiProblem('501', $exc);
        }       
    }

    private function splitOr(&$value) {
        $findOr = '(?<firstPred>.+)\s+or\s+(?<lastPred>[^\]]+)';
        $findOrInPred = '~^(?<before>[^[]+\[)'.$findOr.'(?<after>.*)$~';
        $findOr = '~'.$findOr.'~';
        $parts = array();
        if (preg_match($findOrInPred, $value, $parts)){
            $predParts = $parts;
            while(preg_match($findOr, $predParts['firstPred'], $predParts)) {
                $this->xpath2SQLFailed();
            }
        }
    }
    
    private function xpath2SQLFailed() {
        throw new \Exception('Can\'t create SQL from XPath! Check implementation.');   
    }
    
    public function fetch($id) {
        if (($ret = parent::fetch($id)) instanceof ApiProblem) {return $ret;}
        $doLock = (bool)$this->getEvent()->getQueryParam('lock');
        if ($doLock && ($ret['locked'] === '') && !$this->isUpdatingDontSetLock) {
            $ret['locked'] = $this->getIdentity()->getAuthenticationIdentity()['username'];
            if (($trylock = parent::update($id, $ret)) instanceof ApiProblem) {
                if ($tryLock->status !== 403) {
                    return $trylock;
                } else {
                    $ret['locked'] = '';
                }
            }
        }
        return $ret;
    }

    public function create($data)
    {
        return parent::create($data);
    }

    public function update($id, $data) {
        if (($current = parent::fetch($id)) instanceof ApiProblem) {return $current;}
        if (!$this->isUpdatingDontSetLock) {$data->locked = $this->getIdentity()->getAuthenticationIdentity()['username'];};
        if (!$this->isUpdatingDontSetLock &&
            $current['locked'] !== $this->getIdentity()->getAuthenticationIdentity()['username']) {
            return new ApiProblem(409, "Conflict, you don't own the lock!");
        }
        if (($backupCreated = $this->saveOnWrite($id)) !== true) { return $backupCreated; } // is an ApiProblem
        return parent::update($id, $data);
    }
    
    protected function saveOnWrite($id) {
        if ($this->doCopyOnWrite) {
            $current = $this->fetch($id);
            $cowTable = $this->linkedTableGateways['cow'];
            $rowsAffected = $cowTable->insert(array(
                    'user' => $this->getIdentity()->getAuthenticationIdentity()['username'],
                    'id' => $current['id'],
                    'sid' => $current['sid'],
                    'lemma' => $current['lemma'],
                    'entry_before' => $current['entry'],
            ));
            if ($rowsAffected != 1) {
                return new ApiProblem(500, "Couldn't create backup copy!");
            }
        }
        return true;
    }
    
    protected function retrieveData($data) {
        $data = (array)$data;
        $locked = $data['locked'];
        // filters field 'locked' so its set to empty
        $ret = parent::retrieveData($data);
        // restore the lock (if appropriate)
        if ($locked === $this->getIdentity()->getAuthenticationIdentity()['username']) {
            $ret['locked'] = $this->getIdentity()->getAuthenticationIdentity()['username'];
        }
        return $ret;
    }
    
    public function patch($id, $data) {
        if (($current = parent::fetch($id)) instanceof ApiProblem) {return $current;}
        if ($data->locked !== '') {
            $data->locked = $this->getIdentity()->getAuthenticationIdentity()['username'];
        }
        if (($current['locked'] === '') && ($data->locked === '')) {
            return new ApiProblem(412, "The entry isn't locked!");
        } 
        if ($current['locked'] !== $this->getIdentity()->getAuthenticationIdentity()['username'] &&
            !($this->isAdmin() && ($data->locked === ''))) {
            return new ApiProblem(409, "Conflict, you don't own the lock!");
        }
        $this->doCopyOnWrite = $data->locked !== '';
        $this->isUpdatingDontSetLock = true;
        $ret = parent::patch($id, $data);
        $this->isUpdatingDontSetLock = false;
        $this->doCopyOnWrite = true;
        return $ret;
    }
       
    public function patchList($data) {
        $this->table->getAdapter()->getDriver()->getConnection()->beginTransaction();
        $patchingExisting = false;
        foreach ($data as $part) {
            try { $current = $this->fetch($part['id']); }
            catch (\Exception $e) {                
                if (!$patchingExisting && 
                    ($e->getCode() === 404)) {
                    $this->table->getAdapter()->getDriver()->getConnection()->commit();
                    // try insert, which super class does.
                    return parent::patchList($data);
                } else { throw $e; };
            }
            if ($current instanceof ApiProblem) {return $current;}
            $patchingExisting = true;
            if ($part['locked'] !== '') {
                $part['locked'] = $this->getIdentity()->getAuthenticationIdentity()['username'];
            }
            if (($current['locked'] === '') && ($part['locked'] === '')) {
                return new ApiProblem(412, "The entry isn't locked!");
            }
            if ($current['locked'] !== $this->getIdentity()->getAuthenticationIdentity()['username'] &&
                !($this->isAdmin() && ($part['locked'] === ''))) {
                //Allow mass locking for anyone. Without even admins would need a lock.
                if (!($current['locked'] === ''))
                    return new ApiProblem(409, "Conflict, you don't own the lock!");
            }
            $dataItem = $part->getArrayCopy();
            unset($dataItem["id"]);
            $this->table->update($dataItem, array('id' => $part['id']));
        }
        $this->table->getAdapter()->getDriver()->getConnection()->commit();
        return $this->fetchAll();
    }
    
    public function deleteList($data) {
        $data["id"] = 699;
        $data["operator"] = '>';
        $this->linkedTableExts = array('ndx');
        return parent::deleteList($data);
    }
    
    public function delete($id) {
//        if (true == $trySwitchFailed = $this->switchToTableInRouteIfExistsAndUserAuthorized()) { return $trySwitchFailed; } // is an ApiProblem
        if (true == $isNoAdmin = $this->checkHasNoAdminRights()) { return $isNoAdmin; } // is an ApiProblem
        if (($backupCreated = $this->saveOnWrite($id)) !== true) { return $backupCreated; } // is an ApiProblem
        return parent::delete($id);
    }
}

