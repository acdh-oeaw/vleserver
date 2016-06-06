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

use Zend\Paginator\Adapter\DbTableGateway;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Predicate\PredicateInterface;
use Zend\Db\Sql\Select;

class LimitedColumnTableGateway extends DbTableGateway {
    
    /**
     * Note: $tableData is a reminder that this is only used to carry objects around.
     * SQL is executed through the provided $tableData->sql directly actually NOT using the
     * TableGateway
     * @param \Zend\Db\TableGateway\TableGatewayInterface $tableData
     * @param array $columns Optionally limit the select query to these columns.
     */
    public function __construct(TableGatewayInterface $tableData, array $columns = array(), PredicateInterface $where = null, array $order = null)
    {
        parent::__construct($tableData, $where, $order);
        if ($columns !== array()) {
            $this->select->columns($columns);
        }
    }
    
    /**
     * @param string $tableName
     * @param string|Expression $onExpression
     * @param mixed $columns
     * @param mixed $selectJoinConst
     * @return LimitedColumnTableGateway
     */
    public function join($tableName, $onExpression, $columns = array(), $selectJoinConst = Select::JOIN_INNER) {
        $this->select->join($tableName, $onExpression, $columns, $selectJoinConst);
        return $this;
    }
    
    /** 
     * @param array|string $group
     * @return LimitedColumnTableGateway
     */
    public function group($group) {
        $this->select->group($group);
        return $this;
    }
}

