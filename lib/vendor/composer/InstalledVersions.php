<?php











namespace Composer;

use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => 'dev-next',
    'version' => 'dev-next',
    'aliases' => 
    array (
    ),
    'reference' => '8856aa2cd2ccf1c92bef6374ba238aa02ca080d3',
    'name' => 'agentejo/cockpit',
  ),
  'versions' => 
  array (
    'agentejo/cockpit' => 
    array (
      'pretty_version' => 'dev-next',
      'version' => 'dev-next',
      'aliases' => 
      array (
      ),
      'reference' => '8856aa2cd2ccf1c92bef6374ba238aa02ca080d3',
    ),
    'claviska/simpleimage' => 
    array (
      'pretty_version' => '3.3.4',
      'version' => '3.3.4.0',
      'aliases' => 
      array (
      ),
      'reference' => '3786d80af8e6d05e5e42f0350e5e5da5b92041a0',
    ),
    'colinodell/json5' => 
    array (
      'pretty_version' => 'v2.1.0',
      'version' => '2.1.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '86136a74726d48ec60285d5b8585d62e0645b56c',
    ),
    'erusev/parsedown' => 
    array (
      'pretty_version' => '1.7.3',
      'version' => '1.7.3.0',
      'aliases' => 
      array (
      ),
      'reference' => '6d893938171a817f4e9bc9e86f2da1e370b7bcd7',
    ),
    'erusev/parsedown-extra' => 
    array (
      'pretty_version' => '0.7.1',
      'version' => '0.7.1.0',
      'aliases' => 
      array (
      ),
      'reference' => '0db5cce7354e4b76f155d092ab5eb3981c21258c',
    ),
    'firebase/php-jwt' => 
    array (
      'pretty_version' => 'v5.0.0',
      'version' => '5.0.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '9984a4d3a32ae7673d6971ea00bae9d0a1abba0e',
    ),
    'ksubileau/color-thief-php' => 
    array (
      'pretty_version' => 'v1.4.1',
      'version' => '1.4.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'fc2acefacbd037f68cf61bcc62b30ac1bb16ed59',
    ),
    'league/color-extractor' => 
    array (
      'pretty_version' => '0.3.2',
      'version' => '0.3.2.0',
      'aliases' => 
      array (
      ),
      'reference' => '837086ec60f50c84c611c613963e4ad2e2aec806',
    ),
    'league/flysystem' => 
    array (
      'pretty_version' => '1.0.57',
      'version' => '1.0.57.0',
      'aliases' => 
      array (
      ),
      'reference' => '0e9db7f0b96b9f12dcf6f65bc34b72b1a30ea55a',
    ),
    'maennchen/zipstream-php' => 
    array (
      'pretty_version' => 'v0.5.2',
      'version' => '0.5.2.0',
      'aliases' => 
      array (
      ),
      'reference' => '95922b6324955974675fd4923f987faa598408af',
    ),
    'matthecat/colorextractor' => 
    array (
      'replaced' => 
      array (
        0 => '*',
      ),
    ),
    'mongodb/mongodb' => 
    array (
      'pretty_version' => '1.4.3',
      'version' => '1.4.3.0',
      'aliases' => 
      array (
      ),
      'reference' => '18fca8cc8d0c2cc07f76605760d20632bb3dab96',
    ),
    'phpmailer/phpmailer' => 
    array (
      'pretty_version' => 'v6.1.1',
      'version' => '6.1.1.0',
      'aliases' => 
      array (
      ),
      'reference' => '26bd96350b0b2fcbf0ef4e6f0f9cf3528302a9d8',
    ),
  ),
);







public static function getInstalledPackages()
{
return array_keys(self::$installed['versions']);
}









public static function isInstalled($packageName)
{
return isset(self::$installed['versions'][$packageName]);
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

$ranges = array();
if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}





public static function getVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['version'])) {
return null;
}

return self::$installed['versions'][$packageName]['version'];
}





public static function getPrettyVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return self::$installed['versions'][$packageName]['pretty_version'];
}





public static function getReference($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
}

if (!isset(self::$installed['versions'][$packageName]['reference'])) {
return null;
}

return self::$installed['versions'][$packageName]['reference'];
}





public static function getRootPackage()
{
return self::$installed['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
}
}
