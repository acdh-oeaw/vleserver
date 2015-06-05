<?php

namespace wde\V2\Rest\Common;

use ZF\Apigility\DbConnectedResource;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
class TableSwitchingResource extends DbConnectedResource {
    /** @var string */
    protected $realTableNameExtension;
    
    /** @var string */
    protected $realTableName;
    
    /** @var TableGateway */
    protected $realTableGateway;
    
    /** @var array */
    protected $linkedTableExts;
    
     /** @var array(TableGateway) */
    protected $linkedTableGateways;   
    
    protected function switchTable($table = null, $realTableName = null) {
        $instanceSetup = false;
        if (!isset($table)){
            $table = $this->table;
            $realTableName = $this->realTableName;
            $instanceSetup = true;
        } 
        $table->getSql()->setTable($realTableName);
        $realTableGateway = new TableGateway($realTableName,
                $table->getAdapter(),
                $table->getFeatureSet(),
                $table->getResultSetPrototype(),
                $table->getSql());
        if ($instanceSetup) {
        $this->linkedTableGateways = array();
            if (isset($this->linkedTableExts)) {
                foreach ($this->linkedTableExts as $ext) {
                    $sql = new Sql($this->table->getSql()->getAdapter(), $realTableName . '_' . $ext, $this->table->getSql()->getSqlPlatform());
                    $this->linkedTableGateways[$ext] = new TableGateway($realTableName . '_' . $ext, $this->table->getAdapter(), $this->table->getFeatureSet(), $this->table->getResultSetPrototype(), $sql);
                }
            }
            $this->realTableGateway = $realTableGateway;
            $this->table = $this->realTableGateway;
        }
        return $realTableGateway;
    }
    
    public function patchList($data) {
        $this->table->getAdapter()->getDriver()->getConnection()->beginTransaction();
        foreach ($data as $part) {
            $dataItem = $part->getArrayCopy();
            $id = $dataItem["id"];
            $this->table->insert($dataItem);
// Checking is impossible as long as there is no primary key.
//            $id = $this->table->getLastInsertValue();
//            $singleRes = $this->fetch($id);
//            if ($singleRes instanceof ApiProblem) {
//                return $singleRes;
//            }
        }
        $this->table->getAdapter()->getDriver()->getConnection()->commit();
        return $this->fetchAll();
    }
    
    public function deleteList($data) {
        if (isset($data["operator"]) && ($data["operator"] === '>')) {
            $where = new Where();
            $where->greaterThan('id', $data["id"]);
            $item = $this->table->delete($where);
            foreach($this->linkedTableExts as $ext) {
               $this->linkedTableGateways[$ext]->delete($where); 
            }
            return ($item > 0);
        } else {
            return parent::delete($data["id"], array());
        }
    }
}

