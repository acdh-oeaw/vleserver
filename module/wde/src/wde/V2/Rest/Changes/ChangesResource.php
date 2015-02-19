<?php

namespace wde\V2\Rest\Changes;

use wde\V2\Rest\Common\AccessCheckingTSResource;
use wde\V2\Rest\Common\LimitedColumnTableGateway;
use Zend\Db\TableGateway\TableGatewayInterface as TableGateway;
use Zend\Db\Sql\Where;
use ZF\ApiProblem\ApiProblem;

class ChangesResource extends AccessCheckingTSResource {

    public function __construct(TableGateway $table, $identifierName, $collectionClass) {
        $this->realTableNameExtension = '_cow';
        parent::__construct($table, $identifierName, $collectionClass);
    }
            
    public function fetchAll($data = array()) {
        if (($tryGetAccessAndSwitchTable = parent::fetchAll($data)) instanceof ApiProblem) { return $tryGetAccessAndSwitchTable; }
        $explicitPageSize = $this->getEvent()->getRequest()->getQuery('pageSize');
        $requestedUser = $this->getEvent()->getRequest()->getQuery('user');
        $entryFilter = new Where();
        $entryFilter->equalTo('id', $this->getEvent()->getRouteParam('entries_id'));
        if ($requestedUser !== null) {
            $entryFilter->AND->equalTo('user', $requestedUser);
        }
        if ($explicitPageSize !== null && $explicitPageSize <= 10) {
            $adapter = new LimitedColumnTableGateway($this->table, array(), $entryFilter, array('at' => 'desc'));
        } else {
            $adapter = new LimitedColumnTableGateway($this->table, array('key', 'user', 'at', 'id', 'sid', 'lemma'), $entryFilter, array('at' => 'desc'));
        }
        return new ChangesCollection($adapter);
    }
    
    public function fetch($id) {
        $ret = parent::fetch($id);
        if ($ret['id'] !== (int)$this->getEvent()->getRouteParam('entries_id')) {
            return new ApiProblem(500, 'Database is inconsistent!');
        }
        return $ret;
    }
    
    public function create($data) {
        return new ApiProblem(403, 'This is read only!');
    }
    
    public function update($id, $data) {
        return new ApiProblem(403, 'This is read only!');
    }
    
    public function delete($id) {
        return new ApiProblem(403, 'This is read only!');
    }
    
    public function deleteList($data) {
        return new ApiProblem(403, 'This is read only!');
    }
    
    public function patch($id, $data) {
        return new ApiProblem(403, 'This is read only!');
    }
    
    public function patchList($data) {
        return new ApiProblem(403, 'This is read only!');
    }
}

