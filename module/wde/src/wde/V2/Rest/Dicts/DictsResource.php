<?php
namespace wde\V2\Rest\Dicts;

use Zend\Db\Sql\Sql;
use Zend\Db\Metadata\Metadata;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\DropTable;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Rest\AbstractResourceListener;
use ZF\ApiProblem\ApiProblem;

class DictsResource extends AbstractResourceListener {
    
    private $sql;
    private $metadata;
    
    public function __construct(ServiceLocatorInterface $services, $name, $gatewayName) {
        $config            = $services->get('Config');
        $dbConnectedConfig = $config['zf-apigility']['db-connected'][$gatewayName];
        
        $adapter    = $this->getAdapterFromConfig($dbConnectedConfig, $services);
        $this->sql  = new Sql($adapter);
        $this->metadata = new Metadata($adapter);
        $this->collectionClass = $this->getCollectionFromConfig($dbConnectedConfig, $gatewayName);
    }
    
    protected function getAdapterFromConfig(array $config, ServiceLocatorInterface $services)
    {
        if (isset($config['adapter_name'])
            && $services->has($config['adapter_name'])
        ) {
            return $services->get($config['adapter_name']);
        }

        return $services->get('Zend\Db\Adapter\Adapter');
    }
    
    protected function getCollectionFromConfig(array $config, $requestedName)
    {
        $collection = isset($config['collection_class']) ? $config['collection_class'] : 'Zend\Paginator\Paginator';
        if (!class_exists($collection)) {
            throw new ServiceNotCreatedException(sprintf(
                'Unable to create instance for service "%s"; collection class "%s" cannot be found',
                $requestedName,
                $collection
            ));
        }
        return $collection;
    }
    
        /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        $tableNames = $this->metadata->getTableNames();
        return new $this->collectionClass(new \Zend\Paginator\Adapter\ArrayAdapter($tableNames));
    }
}

