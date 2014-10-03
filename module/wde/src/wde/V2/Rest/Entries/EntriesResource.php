<?php

namespace wde\V2\Rest\Entries;

use wde\V2\Rest\Common\TableSwitchingResource;
use wde\V2\Rest\Common\LimitedColumnTableGateway;
use ZF\ApiProblem\ApiProblem;

class EntriesResource extends TableSwitchingResource {
    
    public function fetchAll($data = array()) {
        parent::fetchAll($data);
        $explicitPageSize = $this->getEvent()->getRequest()->getQuery('pageSize');
        if ($explicitPageSize !== null && $explicitPageSize <= 10) {
            $adapter = new LimitedColumnTableGateway($this->table);
        } else {
            $adapter = new LimitedColumnTableGateway($this->table, array('id', 'sid', 'lemma', 'status', 'locked', 'type'));
        }
        return new EntriesCollection($adapter);
    }
    
}

