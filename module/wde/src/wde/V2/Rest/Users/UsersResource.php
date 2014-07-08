<?php

namespace wde\V2\Rest\Users;

use ZF\Apigility\DbConnectedResource;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Zend\Paginator\Adapter\DbTableGateway;
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
        $tablesWithAuth = array_keys($this->getIdentity()->getAuthenticationIdentity());
        if (!in_array($tableName, $tablesWithAuth)) {
            return new ApiProblem(403, 'Not allowed');
        }
        $filter = new Where();
        $filter->equalTo('userID', $this->identity->getName());
        $adapter = new DbTableGateway($this->table, $filter);
        return new $this->collectionClass($adapter);
    }
    
    public function fetch($id) {
        $tableName = $this->event->getRouteParam('dict_name');
        $tablesWithAuth = array_keys($this->getIdentity()->getAuthenticationIdentity());
        if (!in_array($tableName, $tablesWithAuth)) {
            return new ApiProblem(403, 'Not allowed');
        }
        $ret = parent::fetch($id);
        if ($ret['userID'] !== $this->identity->getName()) {
            return new ApiProblem(403, 'Not allowed');
        }
        return $ret;
    }
}

