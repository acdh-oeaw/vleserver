<?php

/* 
 * The MIT License
 *
 * Copyright 2016 OEAW/ACDH.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

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
    
    public function patchList($data, $delete_by_id_first = false) {
        $this->table->getAdapter()->getDriver()->getConnection()->beginTransaction();
        $ids_to_update = array();
        if ($delete_by_id_first) {
            foreach ($data as $part) {
                if (!in_array($part['id'], $ids_to_update)) {
                    $ids_to_update[] = $part['id'];
                }
            }
            foreach ($ids_to_update as $id) {
                $where_id = new Where();
                $where_id->equalTo('id', $id);
                $this->table->delete($where_id);
            }
        }
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

