<?php

/* 
 * The MIT License
 *
 * Copyright 2016 OEAW/ACDH.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace wde\V2\Rest\Dicts;

use wde\V2\Rest\Dicts\DictsEntity;
use Zend\Db\Sql\Sql;
use Zend\Db\Metadata\Metadata;
use Zend\Db\TableGateway\TableGateway;
use Doctrine\DBAL\DriverManager;
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
        if (in_array('dict_users', $tablesWithAuth)) {
            $tablesWithAuth = array();
            foreach ($allTableNames as $tableName) {
                if (strlen($tableName) > 4 && 
                   (strrpos($tableName, '_cow', -4) ||
                    strrpos($tableName, '_ndx', -4) ||
                    strrpos($tableName, '_lck', -4) )) {
                    continue;
                }
                array_push($tablesWithAuth, $tableName);
            }
        }
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
            $conn = $this->getDBALConnection();
            $DBALPlatform = $conn->getDatabasePlatform();
            $query = $this->getUserTableSchema()->toSql($DBALPlatform);
            
            $this->sql->getAdapter()->query($query[0],
                    \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        } else {
            $this->tableName = $data->name;
            $this->tablesWithAuth = array_intersect($allTableNames, 
                array_keys($this->getIdentity()->getAuthenticationIdentity()));
            if (!$this->isAdmin()) {
                return new ApiProblem(403, 'You are not authorized to create this dictionary');
            }
          
            $conn = $this->getDBALConnection();
            $queries = $this->getSchema($data)->toSql($conn->getDatabasePlatform());            
            
            foreach ($queries as $query) {
                $this->sql->getAdapter()->query(
                        $query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            }
            
//            "INSERT INTO `" . $tablename . "`(`id`, `entry`) values(1, 'teiHeader')";
//            "INSERT INTO `" . $tablename . "`(`id`, `entry`) values(5, 'front')";
//            "INSERT INTO `" . $tablename . "`(`id`, `entry`) values(9, 'profile')";
//            "INSERT INTO `" . $tablename . "`(`id`, `entry`) values(10, 'xslt')";
//            "INSERT INTO `" . $tablename . "`(`id`, `entry`) values(20, 'schema')";
            $dictUsersTable = new TableGateway($this->tableName, $this->sql->getAdapter());
            if ($dictUsersTable->insert(array('id' => 1, 'entry' => 'teiHeader')) !== 1) 
            { return new ApiProblem(500, 'Unable to initalize dictionary (teiHeader).'); }
            if ($dictUsersTable->insert(array('id' => 5, 'entry' => 'front')) !== 1) 
            { return new ApiProblem(500, 'Unable to initalize dictionary (front).'); }
            if ($dictUsersTable->insert(array('id' => 9, 'entry' => 'profile')) !== 1) 
            { return new ApiProblem(500, 'Unable to initalize dictionary (profile).'); }
            if ($dictUsersTable->insert(array('id' => 10, 'entry' => 'xslt')) !== 1) 
            { return new ApiProblem(500, 'Unable to initalize dictionary (xslt).'); }
            if ($dictUsersTable->insert(array('id' => 20, 'entry' => 'schema')) !== 1) 
            { return new ApiProblem(500, 'Unable to initalize dictionary (schema).'); }            
            if ($dictUsersTable->insert(array('id' => 699, 'entry' => 'last reserved')) !== 1) 
            { return new ApiProblem(500, 'Unable to initalize dictionary (schema).'); }
            
            switch ($conn->getDriver()->getName()) {
                case 'mysqli':
                case 'pdo_mysql':
                    $query = "DELIMITER $$
CREATE TRIGGER lockchecktrigger
BEFORE INSERT ON $this->tableName"."_lck FOR EACH ROW
BEGIN
   DECLARE lastAt TIMESTAMP;
   SELECT at INTO lastAt FROM $this->tableName"."_lck WHERE id = NEW.id ORDER BY at DESC;
   IF(NEW.at) < (lastAt + 120) THEN
     DECLARE unused INT;
     SELECT `Cant get a lock.` INTO unused FROM $this->tableName"."_lck WHERE $this->tableName"."_lck.id=NEW.id
   END IF; 
END$$
DELIMITER ;";
//                    $this->sql->getAdapter()->query(
//                        $query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
                    break;
                default:
                    return new ApiProblem(500, 'The lock insert trigger needs to be implemented for each kind of database');
            }
        }
        return new DictsEntity(array(
            'name' => $data->name,
        ));
    }
    
    /**  @return \Doctrine\DBAL\Connection */
    private function getDBALConnection() {
            // Hack: works with mysqli and pdo_mysql
            $adapter = $this->sql->getAdapter();
            $driverFramework = explode('\\', get_class($adapter->getDriver()));
            $driverFramework = strtolower($driverFramework[count($driverFramework) - 1]);
            if ($driverFramework === 'pdo') {
                $platformName = $adapter->getPlatform()->getName();
                $driver = 'pdo_' . strtolower($platformName);
            } else {
                $driver = $driverFramework;
            } 
            return DriverManager::getConnection(array(
                'driver' => $driver,
                // Pass a (mysql) version. Else DBAL would connect to the
                // db to determine the exact version but it has no credentials.
                'url' => "$driver://unused/unused?serverVersion=1",
                ));        
    }
    
    protected function getSchema($data) {
        $schema = new \Doctrine\DBAL\Schema\Schema();
        $table = $schema->createTable($data->name);
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
        $table->addColumn('id', 'integer', array('autoincrement' => true, 'notnull' => true));
        $table->addColumn('sid', 'string', array('length' => 255));
        $table->addColumn('lemma', 'string', array(
            'length' => 255,
            'customSchemaOptions' => array(
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_bin',
                ),
            ));
        $table->addColumn('status', 'string', array(
            'length' => 255,
            'customSchemaOptions' => array(
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_bin',
                ),
            ));
        $table->addColumn('locked', 'string', array('length' => 255));
        $table->addColumn('type', 'string', array('length' => 255));
        $table->addColumn('entry', 'text', array(
            'length' => pow(2, 23),
            'customSchemaOptions' => array(
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_bin',
                ),
            ));
        $table->setPrimaryKey(array('id'));
        // Indices might need some serious rethinking.
        $table->addIndex(array('sid'));
        $table->addIndex(array('lemma'));
        $table->addIndex(array('locked'));
        $table->addIndex(array('status'));
        $table->addIndex(array('entry'), 'entry_fulltext');
// needs patched getIndexDeclarationSQL in Doctrine\DBAL\Platforms\AbstractPlatform line 1904
//         $type = $this->getCreateIndexSQLFlags($index);
        $table->getIndex('entry_fulltext')->addFlag('fulltext');
        $table->addOption('engine', 'MyISAM');
        $table->addOption('charset', 'utf8mb4');
        $table->addOption('collate', 'utf8mb4_bin');

// What exactly uses _lck? Charly says unused.
        $lck_table = $schema->createTable($data->name . '_lck');
//            $mysql = "CREATE TABLE IF NOT EXISTS `" . $data->name. "_lck` (" .
//                            "`id` int(11) NOT NULL auto_increment," .
//                            "`resp` char(255) default NULL," .
//                            "`dt` char(255) default NULL," .
//                            "PRIMARY KEY  (`id`)," .
//                            "KEY `resp_ndx` (`resp`)," .
//                            "KEY `dt_ndx` (`resp`)" .
//                            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770";
//        $lck_table->addColumn('id', 'integer', array('notnull' => true, 'autoincrement' => true));
//        $lck_table->addColumn('resp', 'string', array('length' => 255, 'autoincrement' => true));
//        $lck_table->addColumn('dt', 'string', array('length' => 255, 'autoincrement' => true));
//        $lck_table->setPrimaryKey(array('id'));
//        $lck_table->addIndex(array('resp'));
//        $lck_table->addIndex(array('dt'));
//        $lck_table->addOption('engine', 'MyISAM');
        $lck_table->addColumn('key', 'integer', array(
            'autoincrement' => true,
            'notnull' => true,
            ));
        $lck_table->addColumn('id', 'integer');
        $lck_table->addColumn('user', 'string', array('length' => 100));
        $lck_table->addColumn('at', 'datetime', array('default' => 'CURRENT_TIMESTAMP'));
        $lck_table->getColumn('at')->setPlatformOption('version', true);        
        $lck_table->setPrimaryKey(array('key'));
        $lck_table->addIndex(array('user'));
        $lck_table->addIndex(array('id'));
        $lck_table->addOption('engine', 'InnoDB');
        $lck_table->addOption('charset', 'utf8mb4');
        $lck_table->addOption('collate', 'utf8mb4_bin');

        $ndx_table = $schema->createTable($data->name . '_ndx');
//            $mysql = "CREATE TABLE IF NOT EXISTS `" . $data->name . "_ndx` (" .
//                            "`id` int(11)," .
//                            "`xpath` char(255) default NULL," .
//                            "`txt` text," .
//                            "`weight` int," .
//                            "KEY  (`id`)," .
//                            "KEY `xpath_ndx` (`xpath`)," .
//                            "FULLTEXT KEY `txt_ndx` (`txt`)" .
//                            ") ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=770";
        $ndx_table->addColumn('id', 'integer', array('notnull' => true));
        $ndx_table->addColumn('xpath', 'string', array('length' => 255, 'notnull' => true));
        $ndx_table->addColumn('txt', 'text', array(
            'notnull' => true,
            'customSchemaOptions' => array(
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_bin',
                ),
            ));
        $ndx_table->addColumn('weight', 'integer', array('default' => 0));
        // Indices might need some serious rethinking.
        $ndx_table->addIndex(array('id'));
        $ndx_table->addIndex(array('xpath'));
        $ndx_table->addIndex(array('txt'), 'txt_fulltext');
// needs patched getIndexDeclarationSQL in Doctrine\DBAL\Platforms\AbstractPlatform line 1904
        $ndx_table->getIndex('txt_fulltext')->addFlag('fulltext');
        $ndx_table->addOption('engine', 'MyISAM');
        $ndx_table->addOption('charset', 'utf8mb4');
        $ndx_table->addOption('collate', 'utf8mb4_bin');

        $cow_table = $schema->createTable($data->name . '_cow');
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
        $cow_table->addColumn('key', 'integer', array('autoincrement' => true, 'notnull' => true));
        $cow_table->addColumn('id', 'integer');
        $cow_table->addColumn('sid', 'string', array('length' => 255));
        $cow_table->addColumn('lemma', 'string', array(
            'length' => 255,
            'customSchemaOptions' => array(
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_bin',
                ),
            ));
        $cow_table->addColumn('at', 'datetime', array('default' => 'CURRENT_TIMESTAMP'));
        $cow_table->getColumn('at')->setPlatformOption('version', true);
        $cow_table->addColumn('user', 'string', array('length' => 255));
        $cow_table->addColumn('entry_before', 'text', array(
            'customSchemaOptions' => array(
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_bin',
                ),
            ));
        $cow_table->setPrimaryKey(array('key'));
        $cow_table->addIndex(array('user'));
        $cow_table->addIndex(array('id'));
        $cow_table->addIndex(array('entry_before'), 'entry_before_fulltext');
// needs patched getIndexDeclarationSQL in Doctrine\DBAL\Platforms\AbstractPlatform line 1904
        $cow_table->getIndex('entry_before_fulltext')->addFlag('fulltext');
        $cow_table->addOption('engine', 'MyISAM');
        $cow_table->addOption('charset', 'utf8mb4');
        $cow_table->addOption('collate', 'utf8mb4_bin');

        return $schema;
    }
    
    protected function getUserTableSchema() {
            $schema = new \Doctrine\DBAL\Schema\Schema();
            $table = $schema->createTable('dict_users');
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
            $table->addColumn('id', 'integer', array('autoincrement' => true, 'notnull' => true));
            $table->addColumn('userID', 'string',  array('length' => 100));            
            $table->addColumn('pw', 'string', array('length' => 100));
            $table->addColumn('`table`', 'string', array('length' => 100));
            $table->addColumn('read', 'string', array('length' => 1));
            $table->addColumn('write', 'string', array('length' => 1));
            $table->addColumn('writeown', 'string', array('length' => 1));
            $table->setPrimaryKey(array('id'));
            $table->addUniqueIndex(array('userID', '`table`', 'pw'));
            $table->addOption('engine', 'MyISAM');
            
            return $schema;
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        $allTableNames = $this->metadata->getTableNames();
        $this->tableName = $id;
        $this->tablesWithAuth = array_intersect($allTableNames, 
            array_keys($this->getIdentity()->getAuthenticationIdentity()));
        if (!in_array('dict_users', $this->tablesWithAuth)) {
            return new ApiProblem(403, 'You are not authorized to delete this dictionary');
        }
        
        if($id === 'dict_users') {
          $schema = $this->getUserTableSchema();  
        } else {        
          $data = (object)array('name' => $id);
          $schema = $this->getSchema($data);  
        }
        
        $conn = $this->getDBALConnection();
        $queries = $schema->toDropSql($conn->getDatabasePlatform());

        foreach ($queries as $query) {
            $this->sql->getAdapter()->query(
                    $query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        }
        return true;
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }
}

