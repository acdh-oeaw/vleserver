<?php

namespace wde\v2\Rest;

use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Authentication\Adapter\Http\Exception;
use Zend\Authentication\Result as AuthResult;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Select;

class DictsDictUsersBasicAuth implements ResolverInterface
{
    protected $table;
    
    public function __construct(AdapterInterface $adapter) {
        $this->table = new TableGateway('dict_users', $adapter);
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
        
        $resultSet = $this->table->select(function (Select $select) use ($username, $password) {
            $select
            ->where->equalTo('userID', $username)
            ->where->equalTo('pw', $password);
        });
        
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

