<?php

namespace wde\V2\Rest\Changes;

use wde\V2\Rest\Common\TableSwitchingResource;
use wde\V2\Rest\Common\LimitedColumnTableGateway;
use Zend\Db\TableGateway\TableGatewayInterface as TableGateway;
use Zend\Db\Sql\Where;
use ZF\ApiProblem\ApiProblem;

class ChangesResource extends TableSwitchingResource {

    public function __construct(TableGateway $table, $identifierName, $collectionClass) {
        $this->realTableNameExtension = '_cow';
        parent::__construct($table, $identifierName, $collectionClass);
    }
            
    public function fetchAll($data = array()) {
        parent::fetchAll($data);
        $explicitPageSize = $this->getEvent()->getRequest()->getQuery('pageSize');
        $requestedUser = $this->getEvent()->getRequest()->getQuery('user');
        $entryFilter = new Where();
        $entryFilter->equalTo('id', $this->getEvent()->getRouteParam('entries_id'));
        if ($requestedUser !== null) {
            $entryFilter->AND->equalTo('user', $requestedUser);
        }
        if ($explicitPageSize !== null && $explicitPageSize <= 10) {
            $adapter = new LimitedColumnTableGateway($this->table, array(), $entryFilter);
        } else {
            $adapter = new LimitedColumnTableGateway($this->table, array('key', 'user', 'id', 'sid', 'lemma'), $entryFilter);
        }
        return new ChangesCollection($adapter);
    }
}

