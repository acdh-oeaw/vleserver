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
    
        /**
     *
     * @var array 
     */
    protected $tablesWithAuth;
    
    /**
     *
     * @var string 
     */
    protected $tableName;
    /**
     * @return boolean Whether the user is an admin user.
     */
    
    protected function isAdmin() {
        return in_array('dict_users', $this->tablesWithAuth) ||
               (in_array($this->tableName, $this->tablesWithAuth) &&
                ($this->getIdentity()->getAuthenticationIdentity()[$this->tableName]['writeown'] === false));
    }
    
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
            $table->addConstraint(new Constraint\UniqueKey(array('userID', 'table', 'pw')));
            $this->sql->getAdapter()->query(
                    $this->sql->getSqlStringForSqlObject($table),
                    \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        } else {
            $this->tableName = $data->name;
            $this->tablesWithAuth = array_intersect($allTableNames, 
                array_keys($this->getIdentity()->getAuthenticationIdentity()));
            if (!$this->isAdmin()) {
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
            $table->addColumn((new Column\Integer('id'))->setOption('autoincrement', 'unused'));
            $table->addColumn((new Column\Varchar('sid', 255))->setNullable(true));            
            $table->addColumn((new Column\Varchar('lemma', 255))->setNullable(true));
            $table->addColumn((new Column\Varchar('status', 255))->setNullable(true));
            $table->addColumn((new Column\Varchar('locked', 255))->setNullable(true));
            $table->addColumn((new Column\Varchar('type', 255))->setNullable(true));
            $table->addColumn(new Column\Text('entry', 255));           
            $table->addConstraint(new Constraint\PrimaryKey('id'));
            // Let's hack. TODO: Replace with doctrines DBAL?
            $sql = $this->sql->getSqlStringForSqlObject($table) . ' ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770';
            // Indices might need some serious rethinking.
            $sql = substr_replace($sql, ', ' .
                    'INDEX `'. $data->name .'_c_sid` (`sid`), ' .
                    'INDEX `'. $data->name .'_c_lemma` (`lemma`), ' .
                    'INDEX `'. $data->name .'_c_locked` (`locked`), ' .
                    'INDEX `'. $data->name .'_c_status` (`status`), ' .
                    'FULLTEXT INDEX `'. $data->name .'_c_entry` (`entry`) ',
                    strrpos($sql, ')'), 0);
            
// What exactly uses _lck?
//            $lck_table = new CreateTable($data->name . '_lck');
//            $mysql = "CREATE TABLE IF NOT EXISTS `" . $data->name. "_lck` (" .
//                            "`id` int(11) NOT NULL auto_increment," .
//                            "`resp` char(255) default NULL," .
//                            "`dt` char(255) default NULL," .
//                            "PRIMARY KEY  (`id`)," .
//                            "KEY `resp_ndx` (`resp`)," .
//                            "KEY `dt_ndx` (`resp`)" .
//                            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770";
//            $lck_table->addColumn((new Column\Integer('id')));
            
            $ndx_table = new CreateTable($data->name . '_ndx');
//            $mysql = "CREATE TABLE IF NOT EXISTS `" . $data->name . "_ndx` (" .
//                            "`id` int(11)," .
//                            "`xpath` char(255) default NULL," .
//                            "`txt` text," .
//                            "KEY  (`id`)," .
//                            "KEY `xpath_ndx` (`xpath`)," .
//                            "FULLTEXT KEY `txt_ndx` (`txt`)" .
//                            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770";
            $ndx_table->addColumn((new Column\Integer('id')));
            $ndx_table->addColumn((new Column\Char('xpath', 255)));
            $ndx_table->addColumn((new Column\Text('txt')));
            // Let's hack. TODO: Replace with doctrines DBAL?
            $ndx_sql = $this->sql->getSqlStringForSqlObject($ndx_table) . ' ENGINE=MyISAM DEFAULT CHARSET=utf8';
            // Indices might need some serious rethinking.
            $ndx_sql = substr_replace($ndx_sql, ', ' .
                    'INDEX `'. $data->name .'_ndx_c_id` (`id`), ' .
                    'INDEX `'. $data->name .'_ndx_c_xpath` (`xpath`), ' .
                    'FULLTEXT INDEX `'. $data->name .'_ndx_c_txt` (`txt`(360)) ',
                    strrpos($ndx_sql, ')'), 0);
            
            $cow_table = new CreateTable($data->name . '_cow');
//            $mysql = "CREATE TABLE IF NOT EXISTS `_cow` (".
//  `entry_before` mediumtext NOT NULL,
//  `user` varchar(255) NOT NULL,
//  `at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
//  `key` int(11) NOT NULL AUTO_INCREMENT,
//  `id` int(11) NOT NULL,
//  `sid` varchar(255) NOT NULL,
//  `lemma` varchar(255) NOT NULL,
//  PRIMARY KEY (`key`)
//) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE utf8_general_ci AUTO_INCREMENT=0 ;"
            $cow_table->addColumn((new Column\Integer('key'))->setOption('autoincrement', 'unused'));
            $cow_table->addColumn((new Column\Integer('id')));
            $cow_table->addColumn((new Column\Varchar('sid', 255))->setNullable(true));            
            $cow_table->addColumn((new Column\Varchar('lemma', 255))->setNullable(true));
            $cow_table->addColumn((new Column\Time('at')));
            $cow_table->addColumn((new Column\Varchar('user', 255)));
            $cow_table->addColumn((new Column\Text('entry_before')));                       
            $cow_table->addConstraint(new Constraint\PrimaryKey('key'));
            // Let's hack. TODO: Replace with doctrines DBAL?
            $cow_sql = $this->sql->getSqlStringForSqlObject($cow_table) . ' ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0';
            $cow_sql = substr_replace($cow_sql, ', ' .
                    'INDEX `'. $data->name .'_cow_c_user` (`user`), ' .
//                    'FULLTEXT INDEX `'. $data->name .'_c_entry_before` (`entry_before`), ' .
                    'INDEX `'. $data->name .'_cow_c_id` (`id`) ',
                     strrpos($cow_sql, ')'), 0);
            
            $this->sql->getAdapter()->query(
                    $cow_sql,
                    \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);            
            $this->sql->getAdapter()->query(
                    $ndx_sql,
                    \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);            
            $this->sql->getAdapter()->query(
                    $sql,
                    \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
        return new DictsEntity(array(
            'name' => $data->name,
        ));
    }
}

