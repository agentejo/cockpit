<?php


class BaseTest extends PHPUnit_Framework_TestCase {


    public function testRequirementsPhpVersion() {

        $this->assertTrue(version_compare(PHP_VERSION, '5.4.0') >= 0);
    }

    public function testRequirementsPdoLoaded() {

        $this->assertTrue(extension_loaded('pdo'));
    }

    public function testRequirementsPdoSqliteLoaded() {

        $pass = true;

        try {

            $test = new PDO('sqlite::memory:');

        } catch(Exception $e) {

            $pass = false;
        }

        $this->assertTrue($pass);
    }
}