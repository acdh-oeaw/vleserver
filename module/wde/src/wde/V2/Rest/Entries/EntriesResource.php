<?php

namespace wde\V2\Rest\Entries;

use wde\V2\Rest\Common\AccessCheckingTSResource;
use wde\V2\Rest\Common\LimitedColumnTableGateway;
use ZF\ApiProblem\ApiProblem;

class EntriesResource extends AccessCheckingTSResource {
    
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

    public function create($data)
    {
        return parent::create($data);
    }

    public function update($id, $data)
    {
        return parent::update($id, $data);
    }
    
}

