<?php
namespace wde\V2\Rest\Dicts;

use wde\V2\Rest\Dicts\DictsEntity;
use Zend\Db\Sql\Sql;
use Zend\Db\Metadata\Metadata;
use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\Constraint;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\DropTable;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Rest\AbstractResourceListener;
use ZF\ApiProblem\ApiProblem;

class DictsResource extends AbstractResourceListener {
    
    /**
     *
     * @var Zend\Db\Sql\Sql 
     */
    private $sql;
    
    /**
     *
     * @var Zend\Db\Metadata\Metadata 
     */
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
    
    public function create($data) {
        $allTableNames = $this->metadata->getTableNames();
        if (in_array($data->name, $allTableNames)) {
            return new ApiProblem(409, 'Dictionary already exists');
        } else if ($data->name === 'dict_users') {
            $table = new CreateTable('dict_users');
//            $mysql = "CREATE TABLE IF NOT EXISTS `dict_users` (" .
//                    "`id` int(11) NOT NULL auto_increment," .
//                    "`userID` char(255) default NULL," .
//                    "`pw` char(255) default NULL," .
//                    "`table` char(255) default NULL," .
//                    "`read` char(1) default NULL," .
//                    "`write` char(1) default NULL," .
//                    "`writeown` char(1) default NULL," .
//                    "PRIMARY KEY  (`id`)," .
//                    "KEY `userID_ndx` (`userID`)," .
//                    "KEY `pw_ndx` (`pw`)," .
//                    "KEY `table_ndx` (`table`)" .
//                    ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770";
            //Note: options are not interpreted by database platforms other than mysql (Zend Framework 2.3.1)!!
            //For Oracle and PostgreSQL there is this solution: https://www.apigility.org/documentation/recipes/customizing-table-gateway-with-features
            //(This refers to the SequenceFeature that can extend the TableGateway).
            //This should be extendable to SQL Server 2012+, previous versions don't support sequences.
            $table->addColumn(new Column\Integer('id', false, null, array('autoincrement' => 'unused')));
            $table->addColumn(new Column\Varchar('userID', 255));            
            $table->addColumn(new Column\Varchar('pw', 255));
            $table->addColumn(new Column\Varchar('table', 255));
            $table->addColumn(new Column\Varchar('read', 1));
            $table->addColumn(new Column\Varchar('write', 1));
            $table->addColumn(new Column\Varchar('writeown', 1));
            $table->addConstraint(new Constraint\PrimaryKey('id'));
            $table->addConstraint(new Constraint\UniqueKey(array('userID', 'pw', 'table')));
            $this->sql->getAdapter()->query(
                    $this->sql->getSqlStringForSqlObject($table),
                    \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        } else {
            $tablesWithAuth = array_intersect($allTableNames, 
                array_keys($this->getIdentity()->getAuthenticationIdentity()));
            if (!in_array('dict_users', $tablesWithAuth)) {
                return new ApiProblem(403, 'You are not authorized to create this dictionary');
            }
            $table = new CreateTable($data->name);
//            $mysql = "CREATE TABLE IF NOT EXISTS `" . $data->name . "` (" .
//                    "`id` int(11) NOT NULL auto_increment," .
//                    "`sid` char(255) default NULL," .
//                    "`lemma` char(255) default NULL," .
//                    "`status` char(255) default NULL," .
//                    "`locked` char(255) default NULL," .
//                    "`type` char(255) default NULL," .
//                    "`entry` MEDIUMTEXT," .
//                    "PRIMARY KEY  (`id`)," .
//                    "KEY `sid_ndx` (`sid`)," .
//                    "KEY `lemma_ndx` (`lemma`)," .
//                    "KEY `locked_ndx` (`locked`)," .
//                    "KEY `status_ndx` (`status`)," .
//                    "FULLTEXT KEY `entry_ndx` (`entry`)" .
//                    ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770";
            //Note: options are not interpreted by database platforms other than mysql (Zend Framework 2.3.1)!!
            //For Oracle and PostgreSQL there is this solution: https://www.apigility.org/documentation/recipes/customizing-table-gateway-with-features
            //(This refers to the SequenceFeature that can extend the TableGateway).
            //This should be extendable to SQL Server 2012+, previous versions don't support sequences.
            $table->addColumn(new Column\Integer('id', false, null, array('autoincrement' => 'unused')));
            $table->addColumn(new Column\Varchar('sid', 255));            
            $table->addColumn(new Column\Varchar('lemma', 255));
            $table->addColumn(new Column\Varchar('status', 255));
            $table->addColumn(new Column\Varchar('locked', 255));
            $table->addColumn(new Column\Varchar('type', 255));
            $table->addColumn(new Column\Text('entry', 255));           
            $table->addConstraint(new Constraint\PrimaryKey('id'));
        }
        return parent::create($data);
    }
}

