# MongoDB PHP Library

[![Build Status](https://api.travis-ci.org/mongodb/mongo-php-library.png?branch=master)](https://travis-ci.org/mongodb/mongo-php-library)

This library provides a high-level abstraction around the lower-level
[PHP driver](https://github.com/mongodb/mongo-php-driver) (`mongodb` extension).

While the extension provides a limited API for executing commands, queries, and
write operations, this library implements an API similar to that of the
[legacy PHP driver](https://php.net/manual/en/book.mongo.php). It contains
abstractions for client, database, and collection objects, and provides methods
for CRUD operations and common commands (e.g. index and collection management).

If you are developing an application with MongoDB, you should consider using
this library, or another high-level abstraction, instead of the extension alone.

Additional information about the architecture of this library and the `mongodb`
extension may be found in
[Architecture Overview](https://php.net/manual/en/mongodb.overview.php).

## Documentation

 - https://docs.mongodb.com/php-library/
 - https://docs.mongodb.com/ecosystem/drivers/php/

## Installation

The preferred method of installing this library is with
[Composer](https://getcomposer.org/) by running the following from your project
root:

    $ composer require mongodb/mongodb

Additional installation instructions may be found in the
[library documentation](https://docs.mongodb.com/php-library/current/tutorial/install-php-library/).

Since this library is a high-level abstraction for the driver, it also requires
that the `mongodb` extension be installed:

    $ pecl install mongodb
    $ echo "extension=mongodb.so" >> `php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||"`

Additional installation instructions for the extension may be found in its
[PHP.net documentation](https://php.net/manual/en/mongodb.installation.php).

## Reporting Issues

Issues pertaining to the library should be reported in the
[PHPLIB](https://jira.mongodb.org/secure/CreateIssue!default.jspa?project-field=PHPLIB)
project in MongoDB's JIRA. Extension-related issues should be reported in the
[PHPC](https://jira.mongodb.org/secure/CreateIssue!default.jspa?project-field=PHPC)
project.

For general questions and support requests, please use one of MongoDB's
[Technical Support](https://docs.mongodb.com/manual/support/) channels.

### Security Vulnerabilities

If you've identified a security vulnerability in a driver or any other MongoDB
project, please report it according to the instructions in
[Create a Vulnerability Report](https://docs.mongodb.org/manual/tutorial/create-a-vulnerability-report).

## Development

Development is tracked in the
[PHPLIB](https://jira.mongodb.org/projects/PHPLIB/summary) project in MongoDB's
JIRA. Documentation for contributing to this project may be found in
[CONTRIBUTING.md](CONTRIBUTING.md).
