<?php

namespace wde\V2\Rest\Entries;

use ZF\Apigility\DbConnectedResource;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\TableGateway;
use ZF\ApiProblem\ApiProblem;

class EntriesResource extends DbConnectedResource {
    
    public function __construct(TableGatewayInterface $table, $identifierName, $collectionClass) {
        parent::__construct($table, $identifierName, $collectionClass);
    }
    
    protected $realTableName;
    
    protected function switchTable() {
        $this->table->getSql()->setTable($this->realTableName);
        $this->table = new TableGateway($this->realTableName,
                $this->table->getAdapter(),
                $this->table->getFeatureSet(),
                $this->table->getResultSetPrototype(),
                $this->table->getSql());        
    }
    
    public function fetchAll($data = array()) {
        $this->realTableName = $this->event->getRouteParam('dict_name');
        if ($this->realTableName === 'dict_users') {
            return new ApiProblem(404, 'Item not found');
        }
        $this->switchTable();
        return parent::fetchAll($data);
    }
    
    public function fetch($id) {
        $this->realTableName = $this->event->getRouteParam('dict_name');
        if ($this->realTableName === 'dict_users') {
            return new ApiProblem(404, 'Item not found');
        }
        $this->switchTable();
        return parent::fetch($id);
    }
}

