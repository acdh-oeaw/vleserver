<?php
return array(
    'wde\\V2\\Rest\\Dicts\\Controller' => array(
        'collection' => array(
            'GET' => array(
                'description' => 'Return all known dictionaries and the user rights storage "dict_users".',
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
                'description' => 'Create a new dictionary. Can create an empty user rights storage by using the special name "dict_users".
In order to create a dictionary you need to be an admin user at least for that dictionary. See users.
If the data base is empty any user may create "dict_users".',
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
                'description' => 'Get a dictionary. Does not provide any useful additional information about the dicionary yet but will only succede if the user has the right to access the dictionary using the supplied username and password. If the user is not authorized for the dictionary 404 Item not found is returned.
TODO: Get statistics about the dictionary?',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts[/:dicts_name]"
       }
   }
   "name": "Name of the dictionary."
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
                'description' => 'TODO: To be implemented.
Delete that dictionary.
Only global admins can do this. All others get 403 "Not allowed"',
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
                'description' => '<ul>
<li>For standard users: Get ones rights. The list is filtered to only contain the user that was provided as username during authentication.</li>
<li>For admin users: Get a list of all users and their rights for the table given by the url. This also means that global admin users which are authorized to see and manitpulate "dict_users" can see all users when querying "dict_user".</li>
</ul>
Note that the password is write only thus it is never shown in any response.',
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
                'description' => 'Create a new user for that table. Admin users only. With the special dictionary "dict_users" users can be created for any table by global admins.',
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
                'description' => 'Get a user\'s rights for that table. Non admin users can only query their rights using der user ID.<br/>
Note: The password is not sent over the wire.',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/users[/:users_id]"
       }
   }
   "id": "The internal ID. When creating a new user this will be filled in automatically.",
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
                'description' => 'TODO: Not implemented.
Delete a user for that table. Admin users only.',
                'request' => null,
                'response' => null,
            ),
            'description' => 'A user and her rights for a table.',
            'POST' => array(
                'description' => '<ul>
<li>For a standard user: Change the password. Any other fields are read-only, a 403 error is returned if an attempt is made to manipulate them.</li>
<li>For an admin user: Change the access rights and the password of some user. TODO: check setting rights without changing pw</li>
</ul>',
                'request' => '{
   "pw": "The password for that user and that table. Optional when changing rights. If not sent the password stays the way it is.",
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
        'description' => 'Manipulate the users and user rights for a dictionary. Can also be used by global admin users to manipulate users for any table when used with "dict_users".
Definitions:
<ul>
<li>A standard user is a user that is authorized to read and write her own entries in a dictionary.</li>
<li>An admin user is a user that is authorized to read and write any entry in a partictular dictionary (writeown = \'n\')</li>
<li>A global admin user is a user that is authorized to read and write any entry in "dict_users". Global admin users are authorized to manipulate users for any dictionary also through a url referriung to that dictionary.</li>
</ul>',
    ),
    'wde\\V2\\Rest\\Entries\\Controller' => array(
        'collection' => array(
            'GET' => array(
                'description' => 'Get a list of entries.
Limiting the query: TODO: to be implemented.
<ul>
<li>Query parameter "lem": limit using the lemma column. May contain * jokers.</li>
<li>Query parameter "sid": limit using the sid column</li>
<li>Query parameters "xpath" and "txt": Do a (limited) xpath search to narrow down the result.
The parameters may be used in a key => value style to secify multiple xpath criteria. "txt" may contain "*" jokers.</li>
</ul>
Notes on paging:
<ul>
<li>The page size can be set using the pageSize query parameter. For performance reasons better do not request more the a few hundred entries per page.</li>
<li>Only if 10 or less entries per page are requested the actual entry is sent for performance reasons</li>
</ul>',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/entries"
       },
       "first": {
           "href": "/dicts/:dict_name/entries?page={page}"
       },
       "prev": {
           "href": "/dicts/:dict_name/entries?page={page}"
       },
       "next": {
           "href": "/dicts/:dict_name/entries?page={page}"
       },
       "last": {
           "href": "/dicts/:dict_name/entries?page={page}"
       }
   }
   "_embedded": {
       "entries": [
           {
               "_links": {
                   "self": {
                       "href": "/dicts/:dict_name/entries[/:entries_id]"
                   }
               }
              "id": "The automatically generated id.",
              "sid": "A string id. Ought to be unique. Should not contain any Unicode characters.",
              "lemma": "The lemma of the entry. Probably contains Unicode characters.",
              "status": "Status of the entry. E. g. released.",
              "locked": "The user that currently edits the entry.",
              "type": "Type of the entry. For quickly limiting searches. E. g. lemma, example, multi_word_unit.",
              "entry": "The entry in the dictionary. A TEI XML snippet (or a whole document)."
           }
       ]
   }
}',
            ),
            'POST' => array(
                'description' => 'Create a new entry. An id is automatically assigned. TODO: implement -> An id may be supplied by admin users to create entries in the special entries section of the dictionary below id 700.',
                'request' => '{
   "id": "Optional for creating special entries below id 700.",
   "sid": "A string id. Ought to be unique. Should not contain any Unicode characters.",
   "lemma": "The lemma of the entry. Probably contains Unicode characters.",
   "status": "Status of the entry. E. g. released.",
   "locked": "The user that currently edits the entry.",
   "type": "Type of the entry. For quickly limiting searches. E. g. lemma, example, multi_word_unit.",
   "entry": "The entry in the dictionary. A TEI XML snippet (or a whole document)."
}',
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/entries[/:entries_id]"
       }
   }
   "id": "The automatically generated id.",
   "sid": "A string id. Ought to be unique. Should not contain any Unicode characters.",
   "lemma": "The lemma of the entry. Probably contains Unicode characters.",
   "status": "Status of the entry. E. g. released.",
   "locked": "The user that currently edits the entry.",
   "type": "Type of the entry. For quickly limiting searches. E. g. lemma, example, multi_word_unit.",
   "entry": "The entry in the dictionary. A TEI XML snippet (or a whole document)."
}',
            ),
            'description' => 'Entries in the given dictionary. Does not work for "dict_users" of course.',
        ),
        'entity' => array(
            'GET' => array(
                'description' => 'Get an entry and (TODO) lock it for writing if the user has the right to do this.',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/entries[/:entries_id]"
       }
   }
   "id": "The automatically generated id.",
   "sid": "A string id. Ought to be unique. Should not contain any Unicode characters.",
   "lemma": "The lemma of the entry. Probably contains Unicode characters.",
   "status": "Status of the entry. E. g. released.",
   "locked": "The user that currently edits the entry, that is the currently authenticated user if access rights permit writing.",
   "type": "Type of the entry. For quickly limiting searches. E. g. lemma, example, multi_word_unit.",
   "entry": "The entry in the dictionary. A TEI XML snippet (or a whole document)."
}',
            ),
            'PATCH' => array(
                'description' => 'Manipulate just one part of the entry. (TODO change to post for compatibility reasons?)',
                'request' => '{
   "id": "The automatically generated id.",
   "sid": "A string id. Ought to be unique. Should not contain any Unicode characters.",
   "lemma": "The lemma of the entry. Probably contains Unicode characters.",
   "status": "Status of the entry. E. g. released.",
   "locked": "The user that currently edits the entry.",
   "type": "Type of the entry. For quickly limiting searches. E. g. lemma, example, multi_word_unit.",
   "entry": "The entry in the dictionary. A TEI XML snippet (or a whole document)."
}',
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/entries[/:entries_id]"
       }
   }
   "id": "The automatically generated id.",
   "sid": "A string id. Ought to be unique. Should not contain any Unicode characters.",
   "lemma": "The lemma of the entry. Probably contains Unicode characters.",
   "status": "Status of the entry. E. g. released.",
   "locked": "The user that currently edits the entry.",
   "type": "Type of the entry. For quickly limiting searches. E. g. lemma, example, multi_word_unit.",
   "entry": "The entry in the dictionary. A TEI XML snippet (or a whole document)."
}',
            ),
            'PUT' => array(
                'description' => 'Recreate the whole entry.',
                'request' => '{
   "id": "The automatically generated id.",
   "sid": "A string id. Ought to be unique. Should not contain any Unicode characters.",
   "lemma": "The lemma of the entry. Probably contains Unicode characters.",
   "status": "Status of the entry. E. g. released.",
   "locked": "The user that currently edits the entry.",
   "type": "Type of the entry. For quickly limiting searches. E. g. lemma, example, multi_word_unit.",
   "entry": "The entry in the dictionary. A TEI XML snippet (or a whole document)."
}',
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/entries[/:entries_id]"
       }
   }
   "id": "The automatically generated id.",
   "sid": "A string id. Ought to be unique. Should not contain any Unicode characters.",
   "lemma": "The lemma of the entry. Probably contains Unicode characters.",
   "status": "Status of the entry. E. g. released.",
   "locked": "The user that currently edits the entry.",
   "type": "Type of the entry. For quickly limiting searches. E. g. lemma, example, multi_word_unit.",
   "entry": "The entry in the dictionary. A TEI XML snippet (or a whole document)."
}',
            ),
            'DELETE' => array(
                'description' => 'Delete an entry. Admin user authorization needed.',
                'request' => null,
                'response' => null,
            ),
            'description' => 'Entry in the dictionary. The id may not be changed (TODO check this!)',
        ),
        'description' => 'Create and manipulate entries in a given dictionary.',
    ),
    'wde\\V2\\Rest\\Changes\\Controller' => array(
        'description' => 'Access to the change log entries stored for a particular entry in the dictionaries.',
        'collection' => array(
            'GET' => array(
                'description' => 'Read only. The list size can be limited by passing pageSize. For a pageSize of more than 10 no entry before is returned as embedded result. Default pageSize is 25. For only getting the changes of a user pass a user paramter with the user name.',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/entries/:entries_id/changes"
       },
       "first": {
           "href": "/dicts/:dict_name/entries/:entries_id/changes?page={page}"
       },
       "prev": {
           "href": "/dicts/:dict_name/entries/:entries_id/changes?page={page}"
       },
       "next": {
           "href": "/dicts/:dict_name/entries/:entries_id/changes?page={page}"
       },
       "last": {
           "href": "/dicts/:dict_name/entries/:entries_id/changes?page={page}"
       }
   }
   "_embedded": {
       "changes": [
           {
               "_links": {
                   "self": {
                       "href": "/dicts/:dict_name/entries/:entries_id/changes[/:changes_id]"
                   }
               }
              "key": "Automatically generated sequence number of the save entry event.",
              "user": "The user that saved the entry.",
              "at": "The time at wich the entry was saved.",
              "id": "The sid before the entry was updated. (Usually doesn\'t change.)",
              "sid": "The sid before the entry was updated. (Might have changed.)",
              "lemma": "The lemma before the entry was updated. (Might have changed.)",
              "entry_before": "The entry <strong>before</strong> the possibly updated entry was saved by the user."
           }
       ]
   }
}',
            ),
            'description' => 'List of all chages to a particular entry.',
        ),
        'entity' => array(
            'GET' => array(
                'description' => 'Read only.',
                'request' => null,
                'response' => '{
   "_links": {
       "self": {
           "href": "/dicts/:dict_name/entries/:entries_id/changes[/:changes_id]"
       }
   }
   "key": "Automatically generated sequence number of the save entry event.",
   "user": "The user that saved the entry.",
   "at": "The time at wich the entry was saved.",
   "id": "The sid before the entry was updated. (Usually doesn\'t change.)",
   "sid": "The sid before the entry was updated. (Might have changed.)",
   "lemma": "The lemma before the entry was updated. (Might have changed.)",
   "entry_before": "The entry <strong>before</strong> the possibly updated entry was saved by the user."
}',
            ),
            'description' => 'The entry before it was chaged at the time denoted by at by user.',
        ),
    ),
    'wde\\V2\\Rest\\EntriesNdx\\Controller' => array(
        'description' => 'Access and update the XPaths that are used when limiting the data returned on an entries GET using the ndx parameter.',
    ),
);
