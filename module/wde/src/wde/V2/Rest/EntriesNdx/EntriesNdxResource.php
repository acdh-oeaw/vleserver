<?php

namespace wde\V2\Rest\EntriesNdx;

use wde\V2\Rest\Common\AccessCheckingTSResource;
use Zend\Db\TableGateway\TableGatewayInterface as TableGateway;
use ZF\ApiProblem\ApiProblem;
use wde\V2\Rest\Common\LimitedColumnTableGateway;
use ZF\Apigility\DbConnectedResource;
use wde\V2\Rest\Entries\EntriesEntity;
use Zend\Db\Sql\Where;

class EntriesNdxResource extends AccessCheckingTSResource {
    
    public function __construct(TableGateway $table, $identifierName, $collectionClass) {
        $this->realTableNameExtension = '_ndx';
        parent::__construct($table, $identifierName, $collectionClass);
    }
    
    public function fetchAll($data = array()) {        
        parent::fetchAll($data);
        $entryFilter = new Where();
        $entryFilter->equalTo('id', $this->getEvent()->getRouteParam('entries_id'));
        $adapter = new LimitedColumnTableGateway($this->table, array(), $entryFilter, array());
        return new EntriesNdxCollection($adapter);
    }
    
    public function patchList($data) {
        $entry_id = $data[0]['id'];
        foreach ($data as $ndx_entry) {
           if ($ndx_entry['id'] !== $entry_id) {
               return new ApiProblem(412, "You cannot set more than one set of ndx data at once!");
           } 
        }
        if (true == $trySwitchFailed = $this->switchToTableInRouteIfExistsAndUserAuthorized()) { return $trySwitchFailed; } // is an ApiProblem
        $masterResourceTable = $this->switchTable((clone $this->table), $this->mainTableName);
        // Bypass all access checking.
        $masterResourceHandler = new DbConnectedResource($masterResourceTable, 'id', 'Zend\Paginator\Paginator');
        $masterResource = $masterResourceHandler->fetch($entry_id);
        if ($masterResource['locked'] !== $this->getIdentity()->getAuthenticationIdentity()['username']) {
            return new ApiProblem(409, "Conflict, you don't own the lock!");
        }
        return parent::patchList($data);
    }
    
    public function deleteList($data) {
        $data["id"] = $this->getEvent()->getRouteParam('entries_id');
        return parent::deleteList($data);
    }
}
