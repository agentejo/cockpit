<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LimeExtra\Helper;

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