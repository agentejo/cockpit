<?php

namespace Test\Markdownify;

use Markdownify\Converter;

require_once(__DIR__ . '/../vendor/autoload.php');

class ConverterTest extends ConverterTestCase
{


    /* UTILS
     *************************************************************************/
    public function setUp()
    {
        $this->converter = new Converter;
    }

}