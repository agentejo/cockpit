MongoDB library for PHP
=======================

This library provides a high-level abstraction around the lower-level drivers for
[PHP](https://github.com/mongodb/mongo-php-driver) and
[HHVM](https://github.com/mongodb/mongo-hhvm-driver) (i.e. the `mongodb`
extension).

While the extension provides a limited API for executing commands, queries, and
write operations, this library implements an API similar to that of the
[legacy PHP driver](http://php.net/manual/en/book.mongo.php). It contains
abstractions for client, database, and collection objects, and provides methods
for CRUD operations and common commands (e.g. index and collection management).

If you are developing an application with MongoDB, you should consider using
this library, or another high-level abstraction, instead of the extension alone.

For further information about the architecture of this library and the `mongodb`
extension, see:

 - http://www.mongodb.com/blog/post/call-feedback-new-php-and-hhvm-drivers

## Documentation

 - https://docs.mongodb.com/php-library/

# Installation

As a high-level abstraction for the driver, this library naturally requires that
the [`mongodb` extension be installed](http://php.net/manual/en/mongodb.installation.php):

    $ pecl install mongodb
    $ echo "extension=mongodb.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`

The preferred method of installing this library is with
[Composer](https://getcomposer.org/) by running the following from your project
root:

    $ composer require mongodb/mongodb

## Reporting Issues

Please use the following form to report any issues:

 - https://jira.mongodb.org/secure/CreateIssue.jspa?pid=12483&issuetype=1
