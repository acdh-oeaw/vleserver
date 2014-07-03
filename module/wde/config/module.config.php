<?php
return array(
    'router' => array(
        'routes' => array(
            'wde.rest.dict-users' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/dict_users[/:dict_users_id]',
                    'defaults' => array(
                        'controller' => 'wde\\V2\\Rest\\DictUsers\\Controller',
                    ),
                ),
            ),
            'wde.rest.dicts' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/dicts[/:dicts_name]',
                    'defaults' => array(
                        'controller' => 'wde\\V2\\Rest\\Dicts\\Controller',
                    ),
                ),
            ),
        ),
    ),
    'zf-versioning' => array(
        'uri' => array(
            0 => 'wde.rest.dict-users',
            1 => 'wde.rest.tables',
            2 => 'wde.rest.dicts',
            3 => 'wde.rest.dicts',
        ),
    ),
    'zf-rest' => array(
        'wde\\V2\\Rest\\DictUsers\\Controller' => array(
            'listener' => 'wde\\V2\\Rest\\DictUsers\\DictUsersResource',
            'route_name' => 'wde.rest.dict-users',
            'route_identifier_name' => 'dict_users_id',
            'collection_name' => 'dict_users',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'wde\\V2\\Rest\\DictUsers\\DictUsersEntity',
            'collection_class' => 'wde\\V2\\Rest\\DictUsers\\DictUsersCollection',
            'service_name' => 'dict_users',
        ),
        'wde\\V2\\Rest\\Dicts\\Controller' => array(
            'listener' => 'wde\\V2\\Rest\\Dicts\\DictsResource',
            'route_name' => 'wde.rest.dicts',
            'route_identifier_name' => 'dicts_id',
            'collection_name' => 'dicts',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'wde\\V2\\Rest\\Dicts\\DictsEntity',
            'collection_class' => 'wde\\V2\\Rest\\Dicts\\DictsCollection',
            'service_name' => 'dicts',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'wde\\V2\\Rest\\DictUsers\\Controller' => 'HalJson',
            'wde\\V2\\Rest\\Dicts\\Controller' => 'HalJson',
        ),
        'accept_whitelist' => array(
            'wde\\V2\\Rest\\DictUsers\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'wde\\V2\\Rest\\Dicts\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content_type_whitelist' => array(
            'wde\\V2\\Rest\\DictUsers\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/json',
            ),
            'wde\\V2\\Rest\\Dicts\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'wde\\V2\\Rest\\DictUsers\\DictUsersEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'wde.rest.dict-users',
                'route_identifier_name' => 'dict_users_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'wde\\V2\\Rest\\DictUsers\\DictUsersCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'wde.rest.dict-users',
                'route_identifier_name' => 'dict_users_id',
                'is_collection' => true,
            ),
            'wde\\V2\\Rest\\Dicts\\DictsEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'wde.rest.dicts',
                'route_identifier_name' => 'dicts_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'wde\\V2\\Rest\\Dicts\\DictsCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'wde.rest.dicts',
                'route_identifier_name' => 'dicts_id',
                'is_collection' => true,
            ),
        ),
    ),
    'zf-apigility' => array(
        'db-connected' => array(
            'wde\\V2\\Rest\\DictUsers\\DictUsersResource' => array(
                'adapter_name' => 'MySQLWDE',
                'table_name' => 'dict_users',
                'hydrator_name' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
                'controller_service_name' => 'wde\\V2\\Rest\\DictUsers\\Controller',
                'entity_identifier_name' => 'id',
                'table_service' => 'wde\\V2\\Rest\\DictUsers\\DictUsersResource\\Table',
            ),
            'wde\\V2\\Rest\\Dicts\\DictsResource' => array(
                'adapter_name' => 'MySQLWDE',
                'table_name' => 'unused',
                'hydrator_name' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
                'controller_service_name' => 'wde\\V2\\Rest\\Dicts\\Controller',
                'entity_identifier_name' => 'id',
                'table_service' => 'wde\\V2\\Rest\\Dicts\\DictsResource',
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'wde\\V2\\Rest\\Dicts\\DictsResource' => function ($services, $name, $requestedName) {
                return new wde\V2\Rest\Dicts\DictsResource($services, $name, $requestedName);
            }
        ),
    ),
);
