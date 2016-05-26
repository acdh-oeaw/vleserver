<?php
namespace wde\V2\Rest\Dicts;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DictsResourceFactory implements FactoryInterface{

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $name = '', $resourceName = '') {
        return new DictsResource($serviceLocator, $name, $resourceName);
    }   
}

