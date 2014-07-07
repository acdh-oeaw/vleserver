<?php

namespace wde\V2\Rest\Users;

use ZF\Apigility\DbConnectedResource;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\TableGateway;
use ZF\ApiProblem\ApiProblem;

class UsersResource extends DbConnectedResource {
    
    public function __construct(TableGatewayInterface $table, $identifierName, $collectionClass) {
        $realTableName = 'dict_users';
        $table->getSql()->setTable($realTableName);
        $table = new TableGateway($realTableName,
                $table->getAdapter(),
                $table->getFeatureSet(),
                $table->getResultSetPrototype(),
                $table->getSql());         
        parent::__construct($table, $identifierName, $collectionClass);
    }
    
    public function fetchAll($data = array()) {
        $tableName = $this->event->getRouteParam('dict_name');
        return parent::fetchAll($data);
    }
    
    public function fetch($id) {
        $tableName = $this->event->getRouteParam('dict_name');
        return parent::fetch($id);
    }
}

