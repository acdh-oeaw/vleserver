<?php
return array(
    'db' => array(
        'adapters' => array(
            'MySQLWDE' => array(
                'options' => array(
                    'buffer_results' => true,
                ),
            ),
            'MySQLWDETest' => array(),
            'MySQLOEWB' => array(
                'options' => array(
                    'buffer_results' => true,
                ),
            ),
        ),
    ),
    'zf-mvc-auth' => array(
        'authentication' => array(
            'http' => array(
                'accept_schemes' => array(
                    0 => 'basic',
                ),
                'realm' => 'rest',
                'htpasswd' => 'uses/database',
            ),
        ),
    ),
);
