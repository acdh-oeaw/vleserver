<?php
return array(
    'db' => array(
        'adapters' => array(
            'MySQLWDE' => array(),
            'MySQLWDETest' => array(),
            'MySQLOEWB' => array(),
        ),
    ),
    'zf-mvc-auth' => array(
        'authentication' => array(
            'http' => array(
                'accept_schemes' => array(
                    0 => 'basic',
                ),
                'realm' => 'rest',
            ),
        ),
    ),
);
