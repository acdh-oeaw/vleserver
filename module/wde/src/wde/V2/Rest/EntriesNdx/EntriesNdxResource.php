<?php

namespace wde\V2\Rest\EntriesNdx;

use wde\V2\Rest\Common\AccessCheckingTSResource;
use Zend\Db\TableGateway\TableGatewayInterface as TableGateway;

class EntriesNdxResource extends AccessCheckingTSResource {
    
    public function __construct(TableGateway $table, $identifierName, $collectionClass) {
        $this->realTableNameExtension = '_ndx';
        parent::__construct($table, $identifierName, $collectionClass);
    }
}
