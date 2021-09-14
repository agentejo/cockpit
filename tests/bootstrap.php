<?php
/**
 * Make use of Cockpit autoloader to include it's own librararies.
 *
 * usage: open terminal and run { ./lib/vendor/bin/phpunit/ }
 *
 * @see https://phpunit.readthedocs.io/en/8.3/configuration.html#appendixes-configuration-phpunit-bootstrap
 */

/**
 * @see { phpunit.xml.dist }
 */
if (!defined('COCKPIT_PHPUNIT') || !COCKPIT_PHPUNIT ) {
    exit('Something goes wrong');
}

require_once __DIR__ . '/../bootstrap.php';
