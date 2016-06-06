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

Using unencrypted passwords stored in a DB table
------------------------------------------------

Is a legacy. Should be removed. The passwords are only protected by https.
