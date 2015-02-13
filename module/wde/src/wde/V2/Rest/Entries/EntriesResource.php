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
    
    public function __construct(TableGateway $table, $identifierName, $collectionClass) {
        $this->linkedTableExts = array('ndx', 'cow', 'lck');
        parent::__construct($table, $identifierName, $collectionClass);
    }
    
    public function fetchAll($data = array()) {
        if (($tryGetAccessAndSwitchTable = parent::fetchAll($data)) instanceof ApiProblem) { return $tryGetAccessAndSwitchTable; }
        $explicitPageSize = $this->getEvent()->getRequest()->getQuery('pageSize');
        $filter = new Where();
        $join = null;
        foreach ($data as $key=>$value) {
            switch ($key) {
                case 'sid':
                    $filter->equalTo('sid', $value);
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
                    break;
                case 'xpath':
                    $ndxTable = $this->realTableName . '_ndx';
                    $join = array();
                    $join['tableName'] = "$ndxTable";
                    $join['onExpression'] = "$this->realTableName.id = $ndxTable.id";
                    $join['groupBy'] = "$this->realTableName.id";
                    $filter->like("$ndxTable.xpath", "%$value%");
                    if (isset($data['txt'])) {
                      //XPath contains txt
                      $filter->AND->equalTo("$ndxTable.txt", $data['txt']);
                    } else { 
                      //XPath exists 
                    }
                    break;
                case 'txt':
                    if (!isset($data['xpath'])) {
                        return new ApiProblem(412, 'You need to have both a text and an XPath to search in.');
                    }
                    // processed above
                    break;
                   
            }
        }
        if ($explicitPageSize !== null && $explicitPageSize <= 10) {
            $adapter = new LimitedColumnTableGateway($this->table, array(), $filter);
        } else {
            $adapter = new LimitedColumnTableGateway($this->table, array('id', 'sid', 'lemma', 'status', 'locked', 'type'), $filter);
        }
        if (isset($join)) {
            $adapter->join($join['tableName'], $join['onExpression'])
                    ->group($join['groupBy']);
        }      
        return new EntriesCollection($adapter);
    }
    
    public function fetch($id) {
        $ret = parent::fetch($id);
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
        return parent::update($id, $data);
    }
    
    public function patch($id, $data) {
        if (($current = parent::fetch($id)) instanceof ApiProblem) {return $current;}
        if ($data->locked !== '') {
            $data->locked = $this->getIdentity()->getAuthenticationIdentity()['username'];
        }
        if ($current['locked'] !== $this->getIdentity()->getAuthenticationIdentity()['username'] &&
            !($this->isAdmin() && ($data->locked === ''))) {
            return new ApiProblem(409, "Conflict, you don't own the lock!");
        }
        $this->isUpdatingDontSetLock = true;
        $ret = parent::patch($id, $data);
        $this->isUpdatingDontSetLock = false;
        return $ret;
    }
    
    public function deleteList($data) {
        $data["id"] = 699;
        $data["operator"] = '>';
        $this->linkedTableExts = array('ndx');
        return parent::deleteList($data);
    }
    
}

