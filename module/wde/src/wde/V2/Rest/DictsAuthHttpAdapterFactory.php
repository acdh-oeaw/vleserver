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

namespace wde\V2\Rest;

use Zend\Authentication\Adapter\Http as HttpAuth;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for creating the AuthHttp from configuration
 */
class DictsAuthHttpAdapterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @throws ServiceNotCreatedException
     * @return false|HttpAuthAdapter
     */
    public function createService(ServiceLocatorInterface $services)
    {
        // If no configuration present, nothing to create
        if (!$services->has('config')) {
            return false;
        }

        $config = $services->get('config');

        // If no HTTP adapter configuration present, nothing to create
        if (!isset($config['zf-mvc-auth']['authentication']['http'])) {
            return false;
        }

        $httpConfig = $config['zf-mvc-auth']['authentication']['http'];

        if (!isset($httpConfig['accept_schemes']) || !is_array($httpConfig['accept_schemes'])) {
            throw new ServiceNotCreatedException('"accept_schemes" is required when configuring an HTTP authentication adapter');
        }

        if (!isset($httpConfig['realm'])) {
            throw new ServiceNotCreatedException('"realm" is required when configuring an HTTP authentication adapter');
        }

        if (in_array('digest', $httpConfig['accept_schemes'])) {
            if (!isset($httpConfig['digest_domains'])
                || !isset($httpConfig['nonce_timeout'])
            ) {
                throw new ServiceNotCreatedException('Both "digest_domains" and "nonce_timeout" are required when configuring an HTTP digest authentication adapter');
            }
        }

        $httpAdapter = new HttpAuth(array_merge(
            $httpConfig,
            array(
                'accept_schemes' => implode(' ', $httpConfig['accept_schemes'])
            )
        ));

        $dbConnectedConfig = $config['zf-apigility']['db-connected']['wde\\V2\\Rest\\Dicts\\DictsResource'];
        
        try {
            $adapter    = $this->getAdapterFromConfig($dbConnectedConfig, $services);
        } catch (\Zend\ServiceManager\Exception\ServiceNotFoundException $e) { $adapter = null; }
        
        
        if (in_array('basic', $httpConfig['accept_schemes']) && isset($httpConfig['htpasswd'])) {
            try {
                $dictsDictUsersBasicAuth = new DictsDictUsersBasicAuth($adapter);
                $httpAdapter->setBasicResolver($dictsDictUsersBasicAuth);
            } catch (\Exception $e) {/* actually \Zend\Db\Adapter\Exception\ErrorException due to db access problems*/
                \error_log($e->getMessage());
            }
        }

//        if (in_array('digest', $httpConfig['accept_schemes']) && isset($httpConfig['htdigest'])) {
//            $httpAdapter->setDigestResolver(new HttpAuth\FileResolver($httpConfig['htdigest']));
//        }

        return $httpAdapter;        
    }
    
    protected function getAdapterFromConfig(array $config, ServiceLocatorInterface $services)
    {
        if (isset($config['adapter_name'])
            && $services->has($config['adapter_name'])
        ) {
          try {
            return $services->get($config['adapter_name']);
          } catch (\Zend\ServiceManager\Exception\ServiceNotCreatedException $e) {
                if (!$e->getPrevious()->getPrevious() instanceof \Zend\Db\Adapter\Exception\InvalidArgumentException)
                    throw $e; 
          }
        }

        return $services->get('Zend\Db\Adapter\Adapter');
    }
}

