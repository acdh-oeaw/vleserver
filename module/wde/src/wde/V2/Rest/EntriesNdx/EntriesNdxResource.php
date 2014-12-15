<?php

namespace wde\V2\Rest\EntriesNdx;

use wde\V2\Rest\Common\AccessCheckingTSResource;
use Zend\Db\TableGateway\TableGatewayInterface as TableGateway;
use wde\V2\Rest\Common\LimitedColumnTableGateway;
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
        parent::patchList($data);
    }
    
    public function deleteList($data) {
        parent::deleteList($data);
    }
}
