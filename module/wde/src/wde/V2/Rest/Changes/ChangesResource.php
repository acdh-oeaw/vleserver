<?php

namespace wde\V2\Rest\Changes;

use wde\V2\Rest\Common\TableSwitchingResource;
use wde\V2\Rest\Common\LimitedColumnTableGateway;
use Zend\Db\TableGateway\TableGatewayInterface as TableGateway;
use ZF\ApiProblem\ApiProblem;

class ChangesResource extends TableSwitchingResource {

    public function __construct(TableGateway $table, $identifierName, $collectionClass) {
        $this->realTableNameExtension = '_cow';
        parent::__construct($table, $identifierName, $collectionClass);
    }
            
    public function fetchAll($data = array()) {
        parent::fetchAll($data);
        $explicitPageSize = $this->getEvent()->getRequest()->getQuery('pageSize');
        if ($explicitPageSize !== null && $explicitPageSize <= 10) {
            $adapter = new LimitedColumnTableGateway($this->table);
        } else {
            $adapter = new LimitedColumnTableGateway($this->table, array('key', 'user', 'id', 'sid', 'lemma'));
        }
        return new ChangesCollection($adapter);
    }
}

