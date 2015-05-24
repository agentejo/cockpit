<?php

namespace Lime\Helper;

use Parsedown;
use ParsedownExtra;

/**
 *
 */
class Markdown extends \Lime\Helper {

    protected $parser;
    protected $parserExtra;

    /**
     * [initialize description]
     * @return [type] [description]
     */
    public function initialize() {

        $this->parser = new Parsedown();
        $this->parserExtra = new ParsedownExtra();
    }

    /**
     * [parse description]
     * @param  [type]  $text  [description]
     * @param  boolean $extra [description]
     * @return [type]         [description]
     */
    public function parse($text, $extra = true) {

        return $extra ? $this->parserExtra->text($text) : $this->parser->text($text);
    }

}