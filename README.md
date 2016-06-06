Apigility based Rest Service for VLE
====================================

This project is a RESTful API service that can be used to store XML documents
in small parts that share a common ancestor. In practice it is used to store
TEI documents made up of tei:entry elements. These dictionary like data can
then be searched using XPath based indexes.

This project is based on the apigility template project.

Requirements
------------
  
Please see the [composer.json](composer.json) file.

Setting up the REST service
---------------------------

For more instructions also have a look at the [apigility documentation](https://apigility.org/documentation)!

### Via Git (clone)

First, clone the repository:

```bash
git clone https://github.com/acdh-oeaw/vleserver.git # optionally, specify the directory in which to clone
cd /path/to/install/vleserver
```

At this point, you need to use [Composer](https://getcomposer.org/) to install
dependencies. Assuming you already have Composer:

```bash
composer.phar install
```

### Run Composer

Once you have the basic installation, you need to put it in development mode:

```bash
cd /path/to/install/vleserver
php public/index.php development enable # put the skeleton in development mode
```

### Now, fire it up!

Do one of the following:

- Just create a symbolic link from the web servers htdocs/html directory to
  the `public/` directory. Rename that link e. g. `rest/` or `restttest/`
- Create a vhost in your web server that points the DocumentRoot to the
  `public/` directory of the project
- Fire up the built-in web server in PHP (5.4.8+) (**note**: do not use this for
  production!)

In the latter case, do the following:

```bash
cd /path/to/install/vleserver
php -S 0.0.0.0:8080 -t public public/index.php
```

You can then visit the site at http://localhost:8080/ - which will bring up a
welcome page and the ability to visit the dashboard in order to create and
inspect your APIs.

Database schema
---------------

The [database schema used](module/wde/src/wde/V2/Rest/Dicts/DictsResource.php#L231) can be found in the sources.

Classes
-------

The classes making up the service can be found in src/v2/wde.

* The RESTful endpoints form a tree structure:
** At the root there are the dicts. The url dicts is followed by the name of the
dictionary (e. g. dicts/arz_eng_002). Those names map to a set of database tables.
** dicts cointain entries and users
** entries contain entries_ndx and changes
(like dicts/<name>/entries/<id>/entries_ndx or dicts/<name>/entries/<id>/cahnges)
* The authentication is done using BASIC HTTP authentication right now. https
secured connections are actually mandatory because of this. The authentication
requests are resolved against a special "dictionary" dict_users.

More docs
---------

* [TODO](docs/TODO.md)
* [Design](docs/Design.md): A document about design decissions.

Notes
-----

### NOTE ON STRANGE BEHAVIOR, NOT ACCEPTING CONFIG CHANGES

* Check that you provided a valid config/autoload/local.php
* Production setup caches settings in cache/data subdirectory. Please try to remove the contents.
* Apache may need to be restarted (apachectl graceful at least).

### NOTE ABOUT USING THE PHP BUILT-IN WEB SERVER

PHP's built-in web server did not start supporting the `PATCH` HTTP method until
5.4.8. Since the admin API makes use of this HTTP method, you must use a version
&gt;= 5.4.8 when using the built-in web server.

### NOTE ABOUT OPCACHE

**Disable all opcode caches when running the admin!**

The admin cannot and will not run correctly when an opcode cache, such as APC or
OpCache, is enabled. Apigility does not use a database to store configuration;
instead, it uses PHP configuration files. Opcode caches will cache these files
on first load, leading to inconsistencies as you write to them, and will
typically lead to a state where the admin API and code become unusable.

The admin is a **development** tool, and intended for use a development
environment. As such, you should likely disable opcode caching, regardless.

When you are ready to deploy your API to **production**, however, you can
disable development mode, thus disabling the admin interface, and safely run an
opcode cache again. Doing so is recommended for production due to the tremendous
performance benefits opcode caches provide.
