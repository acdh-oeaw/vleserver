<?php
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

