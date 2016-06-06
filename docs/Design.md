Design decisions so far
=======================

Using Doctrine DBAL for creating the tables
----------------------------------

This project uses database abstraction layers. One is obviously the
[ZF2 database abstraction](http://framework.zend.com/manual/current/en/index.html#zend-db)
This abstraction is mandatory because apigility is obviously built around it.
On the other hand we could not express the way the tables need to be set up
(and were set up before this API was created) using ZF2.
So the [abstraction layer from the Doctrine](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/)
is used for creating the tables.

Not using MySQLs XPath capabilities
-------------------------------------------

We don't use MySQLs XPath capabilities right now. They don't work for us.
It is difficult to make up for
[the limitations](http://dev.mysql.com/doc/refman/5.7/en/xml-functions.html) (search for
"XPath Limitations"). What makes it useless for us for now is the fact that
with our current version of the DB (MariaDB 5.5) we cannot tokenize the results
on the fly we may get using XPath expresseion. This could be solved by a full-text
search on the ExtractValues result. Seems this at least needs the creation
of a temporary table, full-text indexing cannot be done on the fly.
This is by the way an entirely unportable feature even though other SQL DBs provide similar
functionalty but all with their own names and implementations.
Abstraction layers would need to be abandoned or ammended.

Using unencrypted passwords stored in a DB table
------------------------------------------------

Is a legacy. Should be removed. The passwords are only protected by https.

Entry locking is not safe
-------------------------

Entries are locked by writing a value to the main table and ia not guarded
against race conditions. The _lck table is actually unused.
There is [an idea in the code](module/wde/src/wde/V2/Rest/Dicts/DictsResource.php#L212)
which could make locking safe even under time critical conditions but it is DB specific.

There is no v1 version of that API
----------------------------------

Version starts with v2 because there was a much simpler, much more naive v1 implememntations.
