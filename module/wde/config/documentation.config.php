<?php
return array(
    'wde\\V2\\Rest\\Entries\\Controller' => array(
        'collection' => array(
            'GET' => array(
                'description' => 'Get a list of all possible entries.',
                'request' => null,
                'response' => null,
            ),
            'POST' => array(
                'description' => null,
                'request' => null,
                'response' => null,
            ),
            'description' => 'All entries in the given dictionary.',
        ),
        'entity' => array(
            'GET' => array(
                'description' => null,
                'request' => null,
                'response' => null,
            ),
            'PATCH' => array(
                'description' => null,
                'request' => null,
                'response' => null,
            ),
            'PUT' => array(
                'description' => null,
                'request' => null,
                'response' => null,
            ),
            'DELETE' => array(
                'description' => null,
                'request' => null,
                'response' => null,
            ),
        ),
        'description' => 'Create and manipulate entries in a given dictionary.',
    ),
    'wde\\V2\\Rest\\Dicts\\Controller' => array(
        'collection' => array(
            'GET' => array(
                'description' => 'Return all known dictionaries and the user rights storage dict_users.',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts"
       },
       "first": {
           "href": "/dicts?page={page}"
       },
       "prev": {
           "href": "/dicts?page={page}"
       },
       "next": {
           "href": "/dicts?page={page}"
       },
       "last": {
           "href": "/dicts?page={page}"
       }
   }
   "_embedded": {
       "dicts": [
           {
               "_links": {
                   "self": {
                       "href": "/dicts[/:dicts_name]"
                   }
               }
              "name": "Name of the dictionary."
           }
       ]
   }
}',
            ),
            'POST' => array(
                'description' => 'Create a new dictionary. Can create an empty user rights storage by using the special name dict_users.',
                'request' => '{
   "name": "Name of the dictionary."
}',
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts[/:dicts_name]"
       }
   }
   "name": "Name of the dictionary."
}',
            ),
            'description' => 'Get all known dictionatries.',
        ),
        'entity' => array(
            'GET' => array(
                'description' => 'Get statistics about the dictionary.',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts[/:dicts_name]"
       }
   }
   "name": "Name of the dictionary.",
   "user": "The users name, part of our current authorization stragegy.",
   "password": "Password for the user, part of our current authorization stragegy. TODO: needs to be changed to some challenge response system!"
}',
            ),
            'PATCH' => array(
                'description' => null,
                'request' => null,
                'response' => null,
            ),
            'PUT' => array(
                'description' => null,
                'request' => null,
                'response' => null,
            ),
            'DELETE' => array(
                'description' => 'Delete that dictionary.',
                'request' => null,
                'response' => null,
            ),
            'description' => 'A dictionary.',
        ),
        'description' => 'Query and manipulate dictionaries and the users table.',
    ),
    'wde\\V2\\Rest\\Users\\Controller' => array(
        'collection' => array(
            'GET' => array(
                'description' => 'Get a list of all users and their rights.',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/users"
       },
       "first": {
           "href": "/dicts/:dict_name/users?page={page}"
       },
       "prev": {
           "href": "/dicts/:dict_name/users?page={page}"
       },
       "next": {
           "href": "/dicts/:dict_name/users?page={page}"
       },
       "last": {
           "href": "/dicts/:dict_name/users?page={page}"
       }
   }
   "_embedded": {
       "users": [
           {
               "_links": {
                   "self": {
                       "href": "/dicts/:dict_name/users[/:users_id]"
                   }
               }
              "id": "The internal ID. When creating a new user this will be filled in automatically.",
              "userId": "The user\'s ID or user name.",
              "pw": "The password for that user and that table.",
              "read": "Whether the user has read access.",
              "write": "Whether the user has write access.",
              "writeown": "Whether the user may change entries that don\'t belong to her.",
              "table": "A table name. Will only be returned on administrative queries on the special dict_users storage."
           }
       ]
   }
}',
            ),
            'POST' => array(
                'description' => 'Create a new user for that table. Admin users only. With the special dict_users users can be created for any table.',
                'request' => '{
   "id": "The internal ID. When creating a new user this will be filled in automatically.",
   "pw": "The password for that user and that table.",
   "read": "Whether the user has read access.",
   "write": "Whether the user has write access.",
   "writeown": "Whether the user may change entries that don\'t belong to her.",
   "table": "A table name. Will only be returned on administrative queries on the special dict_users storage."
}',
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/users[/:users_id]"
       }
   }
   "id": "The internal ID. When creating a new user this will be filled in automatically.",
   "userId": "The user\'s ID or user name.",
   "pw": "The password for that user and that table.",
   "read": "Whether the user has read access.",
   "write": "Whether the user has write access.",
   "writeown": "Whether the user may change entries that don\'t belong to her.",
   "table": "A table name. Will only be returned on administrative queries on the special dict_users storage."
}',
            ),
            'description' => 'A list off all users for a dictionary. Only admin users can really see lists of users.',
        ),
        'entity' => array(
            'GET' => array(
                'description' => 'Get a user\'s rights for that table. Non admin users can only query their rights using der user ID.',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/users[/:users_id]"
       }
   }
   "id": "The internal ID. When creating a new user this will be filled in automatically.",
   "userId": "The user\'s ID or user name.",
   "pw": "The password for that user and that table.",
   "read": "Whether the user has read access.",
   "write": "Whether the user has write access.",
   "writeown": "Whether the user may change entries that don\'t belong to her.",
   "table": "A table name. Will only be returned on administrative queries on the special dict_users storage."
}',
            ),
            'PATCH' => array(
                'description' => null,
                'request' => null,
                'response' => null,
            ),
            'PUT' => array(
                'description' => null,
                'request' => null,
                'response' => null,
            ),
            'DELETE' => array(
                'description' => 'Delete a user for that table.',
                'request' => null,
                'response' => null,
            ),
            'description' => 'A user and her rights for a table.',
            'POST' => array(
                'description' => 'Change the password for a standard user, change the access rights for an admin user.',
                'request' => '{
   "pw": "The password for that user and that table.",
   "read": "Whether the user has read access.",
   "write": "Whether the user has write access.",
   "writeown": "Whether the user may change entries that don\'t belong to her.",
   "table": "A table name. Will only be returned on administrative queries on the special dict_users storage."
}',
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/users[/:users_id]"
       }
   }
   "id": "The internal ID. When creating a new user this will be filled in automatically.",
   "userId": "The user\'s ID or user name.",
   "pw": "The password for that user and that table.",
   "read": "Whether the user has read access.",
   "write": "Whether the user has write access.",
   "writeown": "Whether the user may change entries that don\'t belong to her.",
   "table": "A table name. Will only be returned on administrative queries on the special dict_users storage."
}',
            ),
        ),
        'description' => 'Get a list off all users authorized for a dictionary and change the password for standard users or manage users for admin users,',
    ),
);
