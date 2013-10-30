<?php

include(__DIR__.'/Leafo/lessc.inc.php');

class Less {

  public static function parse($source, $isFile = true) {

    $parser = new lessc;

    try {
        return $isFile ? $parser->compileFile($source) : $parser->compile($source);
    } catch (Exception $e) {
        return '/** LESS PARSE ERROR: '.$e->getMessage().' **/';
    }
  }
}