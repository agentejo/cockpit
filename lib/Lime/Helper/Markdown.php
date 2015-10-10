<?php

namespace Lime\Helper;

use Parsedown;
use ParsedownExtra;

/**
 * Class Markdown
 * @package Lime\Helper
 */
class Markdown extends \Lime\Helper {

    protected $parser;
    protected $parserExtra;

    /**
     * @inherit
     */
    public function initialize() {

        $this->parser = new Parsedown();
        $this->parserExtra = new ParsedownExtra();
    }

    /**
     * @param $text
     * @param bool|true $extra
     * @return mixed
     */
    public function parse($text, $extra = true) {

        return $extra ? $this->parserExtra->text($text) : $this->parser->text($text);
    }

}