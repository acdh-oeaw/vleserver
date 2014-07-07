<?php
namespace wde\V2\Rest\Dicts;

use wde\V2\Rest\Dicts\DictsEntity;
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
    
    public function __construct(ServiceLocatorInterface $services, $name, $resourceName) {
        $config            = $services->get('Config');
        $dbConnectedConfig = $config['zf-apigility']['db-connected'][$resourceName];
        
        $adapter    = $this->getAdapterFromConfig($dbConnectedConfig, $services);
        $this->sql  = new Sql($adapter);
        $this->metadata = new Metadata($adapter);
        $this->collectionClass = $this->getCollectionFromConfig($dbConnectedConfig, $resourceName);
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
        $allTableNames = $this->metadata->getTableNames();
        $tablesWithAuth = array_intersect($allTableNames, 
                array_keys($this->getIdentity()->getAuthenticationIdentity()));
        $tableNames = array();
        foreach ($tablesWithAuth as $tableName) {
            array_push($tableNames, new DictsEntity(array(
                'name' => $tableName,
                )));
        }
        return new $this->collectionClass(new \Zend\Paginator\Adapter\ArrayAdapter($tableNames));
    }
    
    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        if ($id !== 'dict_users') {
            $tablesWithAuth = array_keys($this->getIdentity()->getAuthenticationIdentity());
            if (!in_array($id, $tablesWithAuth)) {
                return new ApiProblem(404, 'Item not found');
            }
        }
        try {
            $table = $this->metadata->getTable($id);
            return new DictsEntity(array(
                'name' => $table->getName(),
            ));
        } catch (\Exception $e) {
            return new ApiProblem(404, 'Item not found', null, null, array('trace' => $e->getTrace()));
        }
    }
}

