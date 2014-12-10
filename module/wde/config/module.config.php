<?php
return array(
    'router' => array(
        'routes' => array(
            'wde.rest.dicts' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/dicts[/:dicts_name]',
                    'defaults' => array(
                        'controller' => 'wde\\V2\\Rest\\Dicts\\Controller',
                    ),
                ),
            ),
            'wde.rest.entries' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/dicts/:dict_name/entries[/:entries_id]',
                    'defaults' => array(
                        'controller' => 'wde\\V2\\Rest\\Entries\\Controller',
                    ),
                ),
            ),
            'wde.rest.users' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/dicts/:dict_name/users[/:users_id]',
                    'defaults' => array(
                        'controller' => 'wde\\V2\\Rest\\Users\\Controller',
                    ),
                ),
            ),
            'wde.rest.changes' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/dicts/:dict_name/entries/:entries_id/changes[/:changes_id]',
                    'defaults' => array(
                        'controller' => 'wde\\V2\\Rest\\Changes\\Controller',
                    ),
                ),
            ),
        ),
    ),
    'zf-versioning' => array(
        'uri' => array(
            0 => 'wde.rest.dicts',
            1 => 'wde.rest.entries',
            2 => 'wde.rest.users',
            3 => 'wde.rest.changes',
        ),
    ),
    'zf-rest' => array(
        'wde\\V2\\Rest\\Dicts\\Controller' => array(
            'listener' => 'wde\\V2\\Rest\\Dicts\\DictsResource',
            'route_name' => 'wde.rest.dicts',
            'route_identifier_name' => 'dicts_name',
            'collection_name' => 'dicts',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'DELETE',
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
        'wde\\V2\\Rest\\Entries\\Controller' => array(
            'listener' => 'wde\\V2\\Rest\\Entries\\EntriesResource',
            'route_name' => 'wde.rest.entries',
            'route_identifier_name' => 'entries_id',
            'collection_name' => 'entries',
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
            'collection_query_whitelist' => array(
                0 => 'lem',
                1 => 'sid',
                2 => 'xpath',
                3 => 'txt',
                4 => 'pageSize',
            ),
            'page_size' => 25,
            'page_size_param' => 'pageSize',
            'entity_class' => 'wde\\V2\\Rest\\Entries\\EntriesEntity',
            'collection_class' => 'wde\\V2\\Rest\\Entries\\EntriesCollection',
            'service_name' => 'entries',
        ),
        'wde\\V2\\Rest\\Users\\Controller' => array(
            'listener' => 'wde\\V2\\Rest\\Users\\UsersResource',
            'route_name' => 'wde.rest.users',
            'route_identifier_name' => 'users_id',
            'collection_name' => 'users',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'DELETE',
                2 => 'POST',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'wde\\V2\\Rest\\Users\\UsersEntity',
            'collection_class' => 'wde\\V2\\Rest\\Users\\UsersCollection',
            'service_name' => 'users',
        ),
        'wde\\V2\\Rest\\Changes\\Controller' => array(
            'listener' => 'wde\\V2\\Rest\\Changes\\ChangesResource',
            'route_name' => 'wde.rest.changes',
            'route_identifier_name' => 'changes_id',
            'collection_name' => 'changes',
            'entity_http_methods' => array(
                0 => 'GET',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
            ),
            'collection_query_whitelist' => array(
                0 => 'user',
                1 => 'pageSize',
            ),
            'page_size' => 25,
            'page_size_param' => 'pageSize',
            'entity_class' => 'wde\\V2\\Rest\\Changes\\ChangesEntity',
            'collection_class' => 'wde\\V2\\Rest\\Changes\\ChangesCollection',
            'service_name' => 'changes',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'wde\\V2\\Rest\\Dicts\\Controller' => 'HalJson',
            'wde\\V2\\Rest\\Entries\\Controller' => 'HalJson',
            'wde\\V2\\Rest\\Users\\Controller' => 'HalJson',
            'wde\\V2\\Rest\\Changes\\Controller' => 'HalJson',
        ),
        'accept_whitelist' => array(
            'wde\\V2\\Rest\\Dicts\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'wde\\V2\\Rest\\Entries\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'wde\\V2\\Rest\\Users\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'wde\\V2\\Rest\\Changes\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content_type_whitelist' => array(
            'wde\\V2\\Rest\\Dicts\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/json',
            ),
            'wde\\V2\\Rest\\Entries\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/json',
            ),
            'wde\\V2\\Rest\\Users\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/json',
            ),
            'wde\\V2\\Rest\\Changes\\Controller' => array(
                0 => 'application/vnd.wde.v2+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'wde\\V2\\Rest\\Dicts\\DictsEntity' => array(
                'entity_identifier_name' => 'name',
                'route_name' => 'wde.rest.dicts',
                'route_identifier_name' => 'dicts_name',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'wde\\V2\\Rest\\Dicts\\DictsCollection' => array(
                'entity_identifier_name' => 'name',
                'route_name' => 'wde.rest.dicts',
                'route_identifier_name' => 'dicts_name',
                'is_collection' => true,
            ),
            'wde\\V2\\Rest\\Entries\\EntriesEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'wde.rest.entries',
                'route_identifier_name' => 'entries_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'wde\\V2\\Rest\\Entries\\EntriesCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'wde.rest.entries',
                'route_identifier_name' => 'entries_id',
                'is_collection' => true,
            ),
            'wde\\V2\\Rest\\Users\\UsersEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'wde.rest.users',
                'route_identifier_name' => 'users_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'wde\\V2\\Rest\\Users\\UsersCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'wde.rest.users',
                'route_identifier_name' => 'users_id',
                'is_collection' => true,
            ),
            'wde\\V2\\Rest\\Changes\\ChangesEntity' => array(
                'entity_identifier_name' => 'key',
                'route_name' => 'wde.rest.changes',
                'route_identifier_name' => 'changes_id',
                'hydrator' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
            ),
            'wde\\V2\\Rest\\Changes\\ChangesCollection' => array(
                'entity_identifier_name' => 'key',
                'route_name' => 'wde.rest.changes',
                'route_identifier_name' => 'changes_id',
                'is_collection' => true,
            ),
        ),
    ),
    'zf-apigility' => array(
        'db-connected' => array(
            'wde\\V2\\Rest\\Dicts\\DictsResource' => array(
                'adapter_name' => 'MySQLWDETest',
                'table_name' => 'unused',
                'hydrator_name' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
                'controller_service_name' => 'wde\\V2\\Rest\\Dicts\\Controller',
                'entity_identifier_name' => 'name',
                'table_service' => 'wde\\V2\\Rest\\Dicts\\DictsResource',
            ),
            'wde\\V2\\Rest\\Entries\\EntriesResource' => array(
                'adapter_name' => 'MySQLWDETest',
                'table_name' => 'unused',
                'hydrator_name' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
                'controller_service_name' => 'wde\\V2\\Rest\\Entries\\Controller',
                'entity_identifier_name' => 'id',
                'table_service' => 'wde\\V2\\Rest\\Entries\\EntriesResource\\Table',
                'resource_class' => 'wde\\V2\\Rest\\Entries\\EntriesResource',
            ),
            'wde\\V2\\Rest\\Users\\UsersResource' => array(
                'adapter_name' => 'MySQLWDETest',
                'table_name' => 'unused',
                'hydrator_name' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
                'controller_service_name' => 'wde\\V2\\Rest\\Users\\Controller',
                'entity_identifier_name' => 'id',
                'table_service' => 'wde\\V2\\Rest\\Users\\UsersResource\\Table',
                'resource_class' => 'wde\\V2\\Rest\\Users\\UsersResource',
            ),
            'wde\\V2\\Rest\\Changes\\ChangesResource' => array(
                'adapter_name' => 'MySQLWDETest',
                'table_name' => 'unused',
                'hydrator_name' => 'Zend\\Stdlib\\Hydrator\\ArraySerializable',
                'controller_service_name' => 'wde\\V2\\Rest\\Changes\\Controller',
                'entity_identifier_name' => 'key',
                'table_service' => 'wde\\V2\\Rest\\Changes\\ChangesResource\\Table',
                'resource_class' => 'wde\\V2\\Rest\\Changes\\ChangesResource',
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'wde\\V2\\Rest\\Dicts\\DictsResource' => 'wde\\V2\\Rest\\Dicts\\DictsResourceFactory',
            'ZF\\MvcAuth\\Authentication\\AuthHttpAdapter' => 'wde\\V2\\Rest\\DictsAuthHttpAdapterFactory',
        ),
    ),
    'zf-content-validation' => array(
        'wde\\V2\\Rest\\Dicts\\Controller' => array(
            'input_filter' => 'wde\\V2\\Rest\\Dicts\\Validator',
        ),
        'wde\\V2\\Rest\\Users\\Controller' => array(
            'input_filter' => 'wde\\V2\\Rest\\Users\\Validator',
        ),
        'wde\\V2\\Rest\\Entries\\Controller' => array(
            'input_filter' => 'wde\\V2\\Rest\\Entries\\Validator',
        ),
        'wde\\V2\\Rest\\Changes\\Controller' => array(
            'input_filter' => 'wde\\V2\\Rest\\Changes\\Validator',
        ),
    ),
    'input_filter_specs' => array(
        'wde\\V2\\Rest\\Dicts\\Validator' => array(
            0 => array(
                'name' => 'name',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
                'description' => 'Name of the dictionary.',
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
        ),
        'wde\\V2\\Rest\\Users\\Validator' => array(
            0 => array(
                'name' => 'id',
                'required' => true,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\Int',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'description' => 'The internal ID. When creating a new user this will be filled in automatically.',
                'allow_empty' => true,
                'continue_if_empty' => true,
                'error_message' => 'This has to be a positiv integer.',
            ),
            1 => array(
                'name' => 'userID',
                'required' => true,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\StringTrim',
                        'options' => array(),
                    ),
                ),
                'validators' => array(
                    0 => array(
                        'name' => 'Zend\\Validator\\StringLength',
                        'options' => array(
                            'min' => '5',
                            'max' => '100',
                        ),
                    ),
                ),
                'allow_empty' => false,
                'continue_if_empty' => false,
                'description' => 'The user\'s ID or user name.',
                'error_message' => 'Please use some text with a length between 5 and 100 characters.',
            ),
            2 => array(
                'name' => 'pw',
                'required' => true,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\StringTrim',
                        'options' => array(),
                    ),
                ),
                'validators' => array(
                    0 => array(
                        'name' => 'Zend\\Validator\\StringLength',
                        'options' => array(
                            'min' => '8',
                            'max' => '100',
                        ),
                    ),
                ),
                'allow_empty' => false,
                'continue_if_empty' => false,
                'description' => 'The password for that user and that table.',
                'error_message' => 'Please use some text with a length between 8 and 100 characters.',
            ),
            3 => array(
                'name' => 'read',
                'required' => true,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\StringTrim',
                        'options' => array(),
                    ),
                    1 => array(
                        'name' => 'Zend\\Filter\\StringToLower',
                        'options' => array(),
                    ),
                ),
                'validators' => array(
                    0 => array(
                        'name' => 'Zend\\Validator\\StringLength',
                        'options' => array(
                            'min' => '1',
                            'max' => '1',
                        ),
                    ),
                ),
                'allow_empty' => false,
                'continue_if_empty' => false,
                'description' => 'Whether the user has read access.',
                'error_message' => 'Either \'y\' or \'n\'.',
            ),
            4 => array(
                'name' => 'write',
                'required' => true,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\StringTrim',
                        'options' => array(),
                    ),
                    1 => array(
                        'name' => 'Zend\\Filter\\StringToLower',
                        'options' => array(),
                    ),
                ),
                'validators' => array(
                    0 => array(
                        'name' => 'Zend\\Validator\\StringLength',
                        'options' => array(
                            'min' => '1',
                            'max' => '1',
                        ),
                    ),
                ),
                'allow_empty' => false,
                'continue_if_empty' => false,
                'description' => 'Whether the user has write access.',
                'error_message' => 'Either \'y\' or \'n\'.',
            ),
            5 => array(
                'name' => 'writeown',
                'required' => true,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\StringTrim',
                        'options' => array(),
                    ),
                    1 => array(
                        'name' => 'Zend\\Filter\\StringToLower',
                        'options' => array(),
                    ),
                ),
                'validators' => array(
                    0 => array(
                        'name' => 'Zend\\Validator\\StringLength',
                        'options' => array(
                            'min' => '1',
                            'max' => '1',
                        ),
                    ),
                ),
                'allow_empty' => false,
                'continue_if_empty' => false,
                'description' => 'Whether the user may change entries that don\'t belong to her.',
                'error_message' => 'Either \'y\' or \'n\'.',
            ),
            6 => array(
                'name' => 'table',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\StringTrim',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'allow_empty' => true,
                'continue_if_empty' => true,
                'description' => 'A table name. Will only be returned on administrative queries on the special dict_users storage.',
            ),
        ),
        'wde\\V2\\Rest\\Entries\\Validator' => array(
            0 => array(
                'name' => 'id',
                'required' => false,
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\Int',
                        'options' => array(),
                    ),
                ),
                'validators' => array(),
                'description' => 'The automatically generated id.',
                'allow_empty' => true,
                'continue_if_empty' => true,
            ),
            1 => array(
                'name' => 'sid',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
                'description' => 'A string id. Ought to be unique. Should not contain any Unicode characters.',
            ),
            2 => array(
                'name' => 'lemma',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
                'description' => 'The lemma of the entry. Probably contains Unicode characters.',
            ),
            3 => array(
                'name' => 'status',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'description' => 'Status of the entry. E. g. released.',
                'allow_empty' => true,
                'continue_if_empty' => true,
            ),
            4 => array(
                'name' => 'locked',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'description' => 'The user that currently edits the entry.',
                'allow_empty' => true,
                'continue_if_empty' => true,
            ),
            5 => array(
                'name' => 'type',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'description' => 'Type of the entry. For quickly limiting searches. E. g. lemma, example, multi_word_unit.',
                'allow_empty' => true,
                'continue_if_empty' => true,
            ),
            6 => array(
                'name' => 'entry',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
                'description' => 'The entry in the dictionary. A TEI XML snippet (or a whole document).',
            ),
        ),
        'wde\\V2\\Rest\\Changes\\Validator' => array(
            0 => array(
                'name' => 'key',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'description' => 'Automatically generated sequence number of the save entry event.',
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            1 => array(
                'name' => 'user',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'description' => 'The user that saved the entry.',
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            2 => array(
                'name' => 'at',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'description' => 'The time at wich the entry was saved.',
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            3 => array(
                'name' => 'id',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'description' => 'The sid before the entry was updated. (Usually doesn\'t change.)',
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            4 => array(
                'name' => 'sid',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'description' => 'The sid before the entry was updated. (Might have changed.)',
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            5 => array(
                'name' => 'lemma',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'description' => 'The lemma before the entry was updated. (Might have changed.)',
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
            6 => array(
                'name' => 'entry_before',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
                'description' => 'The entry <strong>before</strong> the possibly updated entry was saved by the user.',
                'allow_empty' => false,
                'continue_if_empty' => false,
            ),
        ),
    ),
    'zf-mvc-auth' => array(
        'authorization' => array(
            'wde\\V2\\Rest\\Dicts\\Controller' => array(
                'entity' => array(
                    'GET' => true,
                    'POST' => false,
                    'PATCH' => false,
                    'PUT' => false,
                    'DELETE' => true,
                ),
                'collection' => array(
                    'GET' => true,
                    'POST' => true,
                    'PATCH' => false,
                    'PUT' => false,
                    'DELETE' => false,
                ),
            ),
            'wde\\V2\\Rest\\Entries\\Controller' => array(
                'entity' => array(
                    'GET' => true,
                    'POST' => false,
                    'PATCH' => true,
                    'PUT' => true,
                    'DELETE' => true,
                ),
                'collection' => array(
                    'GET' => true,
                    'POST' => true,
                    'PATCH' => false,
                    'PUT' => false,
                    'DELETE' => false,
                ),
            ),
            'wde\\V2\\Rest\\Users\\Controller' => array(
                'entity' => array(
                    'GET' => true,
                    'POST' => false,
                    'PATCH' => true,
                    'PUT' => true,
                    'DELETE' => true,
                ),
                'collection' => array(
                    'GET' => true,
                    'POST' => true,
                    'PATCH' => false,
                    'PUT' => false,
                    'DELETE' => false,
                ),
            ),
            'wde\\V2\\Rest\\Changes\\Controller' => array(
                'entity' => array(
                    'GET' => true,
                    'POST' => false,
                    'PATCH' => false,
                    'PUT' => false,
                    'DELETE' => false,
                ),
                'collection' => array(
                    'GET' => true,
                    'POST' => false,
                    'PATCH' => false,
                    'PUT' => false,
                    'DELETE' => false,
                ),
            ),
        ),
    ),
);
