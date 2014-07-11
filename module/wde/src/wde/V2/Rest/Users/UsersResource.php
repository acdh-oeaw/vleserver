<?php

namespace wde\V2\Rest\Users;

use ZF\Apigility\DbConnectedResource;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Where;
use Zend\Paginator\Adapter\DbTableGateway;
use ZF\ApiProblem\ApiProblem;

class UsersResource extends DbConnectedResource {
    
    /**
     *
     * @var array 
     */
    protected $tablesWithAuth;
    
    /**
     *
     * @var string 
     */
    protected $tableName;
    
    /**
     * @return boolean Whether the user is an admin user.
     */
    protected function isAdmin() {
        return in_array('dict_users', $this->tablesWithAuth) ||
               (in_array($this->tableName, $this->tablesWithAuth) &&
                ($this->getIdentity()->getAuthenticationIdentity()[$this->tableName]['writeown'] === false));
    }
    
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
        $this->tableName = $this->event->getRouteParam('dict_name');
        $this->tablesWithAuth = array_keys($this->getIdentity()->getAuthenticationIdentity());
        if (!(in_array($this->tableName, $this->tablesWithAuth) || $this->isAdmin)) {
            return new ApiProblem(403, 'Not allowed. You are not authorized for this dictionary.');
        }
        $filter = new Where();
        if (!$this->isAdmin()) {
            $filter->equalTo('userID', $this->identity->getName());
        }
        $adapter = new DbTableGateway($this->table, $filter);
        return new $this->collectionClass($adapter);
    }
    
    public function fetch($id) {
        $this->tableName = $this->event->getRouteParam('dict_name');
        $this->tablesWithAuth = array_keys($this->getIdentity()->getAuthenticationIdentity());
        if (!(in_array($this->tableName, $this->tablesWithAuth) || $this->isAdmin())) {
            return new ApiProblem(403, 'Not allowed. You are not authorized for this dictionary.');
        }
        $ret = parent::fetch($id);
        if (($ret['userID'] !== $this->identity->getName()) && !$this->isAdmin()) {
            return new ApiProblem(403, 'Not allowed. You are not allowed to see other users rights.');
        }
        return $ret;
    }
    
    public function create($data) {
        $this->tableName = $this->event->getRouteParam('dict_name');
        if (($this->tableName !== 'dict_users') && ($this->tableName !== $data->table)) {
            return new ApiProblem(403, 'You may only set user rights for any table using dict_users.');
        }
        $this->tablesWithAuth = array_keys($this->getIdentity()->getAuthenticationIdentity());
        try {
            $anyUsers = $this->table->select();
        }
        catch (\Exception $e)
        {
            return new ApiProblem(404, 'Database is not initialized.');
        }
        if ($anyUsers->count() === 0) {
            // Initial setup case
            if ($data->table !== 'dict_users' ||
                $data->userId !== $this->getIdentity()->getName()) {
                return new ApiProblem(403, 'You need to register yourself as admin user first.');
            }
        } else {
            if (!$this->isAdmin()) {
                return new ApiProblem(403, 'You need to be an admin user to do this.');
            }
            if ($data->table === 'dict_users' && in_array('dict_users', $this->tablesWithAuth)) {
                return new ApiProblem(403, 'You are already an admin user.');
            }
            $existsCheck = $this->table->select(array(
                'userId' => $data->userId,
                'table' => $data->table,
            ));
            if ($existsCheck->count() > 0) {
                return new ApiProblem(403, 'The user already has access rights to this table.');
            }
        }
        return parent::create($data);
    }
}

