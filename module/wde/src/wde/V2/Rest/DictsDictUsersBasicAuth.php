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

namespace wde\v2\Rest;

use Zend\Authentication\Adapter\Http\ResolverInterface;
use Zend\Authentication\Adapter\Http\Exception;
use Zend\Authentication\Result as AuthResult;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Metadata\Metadata;

class DictsDictUsersBasicAuth implements ResolverInterface
{
    protected $table;
    
    protected $metadata;
    
    /** @param AdapterInterface|null $adapter */
    public function __construct($adapter) {
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
                $resultSet = $this->table->select(function (Select $select) use ($username, $password) {
                    $select
                    ->where->equalTo('userID', $username)
                    ->where->equalTo('pw', $password);
                });
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

