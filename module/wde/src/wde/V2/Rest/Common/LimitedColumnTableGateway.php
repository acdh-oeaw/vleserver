<?php
namespace wde\V2\Rest\Common;

use Zend\Paginator\Adapter\DbTableGateway;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\Sql\Predicate\PredicateInterface;

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
}

