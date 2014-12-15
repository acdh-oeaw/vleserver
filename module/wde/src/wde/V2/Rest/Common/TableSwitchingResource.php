<?php

namespace wde\V2\Rest\Common;

use ZF\Apigility\DbConnectedResource;
use Zend\Db\TableGateway\TableGateway;

class TableSwitchingResource extends DbConnectedResource {
    /**
     *
     * @var string
     */
    protected $realTableNameExtension;
    
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
    
}

