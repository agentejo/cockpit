<?php

// Since doctrine/coding-standard requires PHP 7, we can't add it as a dependency
// yet. This autoload file adds more information to the phpcs error message,
// telling the user how they can fix the error presented to them by phpcs.
if (! file_exists(__DIR__ . '/../vendor/doctrine/coding-standard')) {
    echo <<<ERRORMESSAGE
==============================================================================
ERROR: Doctrine coding standard is not installed. To rectify this, please run:
composer require --dev doctrine/coding-standard=^6.0
==============================================================================


ERRORMESSAGE;
}
