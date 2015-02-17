<?php

namespace wde\v2\Rest;

use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Authentication\Adapter\Http\Exception;
use Zend\Authentication\Result as AuthResult;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Metadata\Metadata;
use Zend\Db\Adapter\Exception\ErrorException as DbError;

class DictsDictUsersBasicAuth implements ResolverInterface
{
    protected $table;
    
    protected $metadata;
    
    public function __construct(AdapterInterface $adapter) {
        if (isset($adapter)) {
          $this->metadata = new Metadata($adapter);
          $this->table = new TableGateway('dict_users', $adapter);
        }
    }
    
    protected function getDefaultDictUsersAuthorization() {
        return new \ArrayObject(array(
                new \ArrayObject(array(
                'table' => 'dict_users',
                'read' => 'y',
                'write' => 'y',
                'writeown' => 'n',
            ), \ArrayObject::ARRAY_AS_PROPS)));
    }
    
    public function resolve($username, $realm, $password = null) {
        
        if (empty($username)) {
            throw new Exception\InvalidArgumentException('Username is required');
        }

        if (!ctype_print($username) || strpos($username, ':') !== false) {
            throw new Exception\InvalidArgumentException(
                'Username must consist only of printable characters, excluding the colon'
            );
        }

        if (!empty($realm) && (!ctype_print($realm) || strpos($realm, ':') !== false)) {
            throw new Exception\InvalidArgumentException(
                'Realm must consist only of printable characters, excluding the colon'
            );
        }

        if (empty($password)) {
            throw new Exception\InvalidArgumentException('Password is required');
        } 
        
        if (isset($this->metadata)) {
            $tableNames = $this->metadata->getTableNames();
        } else {
            $tableNames = array();
        }

        if (in_array('dict_users', $tableNames)) {
            $testAnyUsers = $this->table->select();
            if ($testAnyUsers->count() > 0) {
                try {
                    $resultSet = $this->table->select(function (Select $select) use ($username, $password) {
                        $select
                        ->where->equalTo('userID', $username)
                        ->where->equalTo('pw', $password);
                    });
                } catch (DbError $e) {
                    return new AuthResult(AuthResult::FAILURE_CREDENTIAL_INVALID, null, array('Database unaccessible!'));
                }
            } else {
                $resultSet = $this->getDefaultDictUsersAuthorization();
            }
        } else {
            $resultSet = $this->getDefaultDictUsersAuthorization();
        }
        
        if ($resultSet->count() > 0) {
            $ret = array(
                'username' => $username,
            );
            foreach ($resultSet as $result) {
                $ret[$result->table] = array(
                    'read' => ($result->read === 'y'),
                    'write' => ($result->write === 'y'),
                    'writeown' => ($result->writeown !== 'n'),
                );
            }
            return new AuthResult(AuthResult::SUCCESS, $ret);
        }

        return new AuthResult(AuthResult::FAILURE_CREDENTIAL_INVALID, null, array('Passwords did not match.'));            
    }
}

