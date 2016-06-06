# Things that need to be done to make this project future proof

* There should be PHPUnit tests
* The creation of the _ndx table entries relies on the client using
the interface correctly and creating them entirely. A default algorithm
should provide the _ndx table entries whenever an entries is crated or changed.
* use some other password storage stregy
** Encrypt the passwords in the dict_users table and implement a reset
startegy and/or
** use htdigest instead of basic password transfer and/or
** use OAuth2 in some combination with Shibboleth
* make use of enhanced (proprietary) SQL to do real XPaht processing or
* limit the result using SQL and then filter that using \DOMXPatth
