<?php

namespace wde\V2\Rest\Entries;

use ZF\Apigility\DbConnectedResource;
use Zend\Db\TableGateway\TableGateway;
use ZF\ApiProblem\ApiProblem;

class EntriesResource extends DbConnectedResource {
    
    /**
     *
     * @var string
     */
    protected $realTableName;
    
    /**
     *
     * @var EntriesTableGateway
     */
    protected $realTableGateway;
    
    protected function switchTable() {
        $this->table->getSql()->setTable($this->realTableName);
        $this->realTableGateway = new TableGateway($this->realTableName,
                $this->table->getAdapter(),
                $this->table->getFeatureSet(),
                $this->table->getResultSetPrototype(),
                $this->table->getSql());
        $this->table = $this->realTableGateway;
    }
    
    public function fetchAll($data = array()) {
        $this->realTableName = $this->event->getRouteParam('dict_name');
        if ($this->realTableName === 'dict_users') {
            return new ApiProblem(404, 'Item not found');
        }
        $this->switchTable();
        $explicitPageSize = $this->getEvent()->getRequest()->getQuery('pageSize');
        if ($explicitPageSize !== null && $explicitPageSize <= 10) {
            $adapter = new EntriesTableGateway($this->table);
        } else {
            $adapter = new EntriesTableGateway($this->table, array('id', 'sid', 'lemma', 'status', 'locked', 'type'));
        }
        return new EntriesCollection($adapter);
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

