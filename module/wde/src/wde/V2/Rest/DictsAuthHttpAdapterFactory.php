<?php

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
        
        $adapter    = $this->getAdapterFromConfig($dbConnectedConfig, $services);
        
        if (in_array('basic', $httpConfig['accept_schemes']) && isset($httpConfig['htpasswd'])) {
            $httpAdapter->setBasicResolver(new DictsDictUsersBasicAuth($adapter));
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
            return $services->get($config['adapter_name']);
        }

        return $services->get('Zend\Db\Adapter\Adapter');
    }
}

