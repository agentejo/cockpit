<?php


include(__DIR__.'/Leafo/scss.inc.php');

class Sass {

  public static function parse($source, $isFile = true) {

    $parser = new scssc;

    if($isFile) {
        $parser->setImportPaths(dirname($source));
    }

    try {
        return $isFile ? $parser->compile('@import "'.basename($source).'"') : $parser->compile($source);
    } catch (Exception $e) {
        return '/** SASS PARSE ERROR: '.$e->getMessage().' **/';
    }
  }
}