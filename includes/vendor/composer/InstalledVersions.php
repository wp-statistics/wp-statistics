<?php

namespace Composer;

use Composer\Semver\VersionParser;






class InstalledVersions
{
private static $installed = array (
  'root' => 
  array (
    'pretty_version' => 'dev-master',
    'version' => 'dev-master',
    'aliases' => 
    array (
    ),
    'reference' => 'd50c2fc7f17ad7cce00ffe728c45b23c92ce02ca',
    'name' => '__root__',
  ),
  'versions' => 
  array (
    '__root__' => 
    array (
      'pretty_version' => 'dev-master',
      'version' => 'dev-master',
      'aliases' => 
      array (
      ),
      'reference' => 'd50c2fc7f17ad7cce00ffe728c45b23c92ce02ca',
    ),
    'composer/ca-bundle' => 
    array (
      'pretty_version' => '1.2.8',
      'version' => '1.2.8.0',
      'aliases' => 
      array (
      ),
      'reference' => '8a7ecad675253e4654ea05505233285377405215',
    ),
    'geoip2/geoip2' => 
    array (
      'pretty_version' => 'v2.10.0',
      'version' => '2.10.0.0',
      'aliases' => 
      array (
      ),
      'reference' => '419557cd21d9fe039721a83490701a58c8ce784a',
    ),
    'jaybizzle/crawler-detect' => 
    array (
      'pretty_version' => 'v1.2.102',
      'version' => '1.2.102.0',
      'aliases' => 
      array (
      ),
      'reference' => '346cfd72d11bb41f15e82654e532dc55360612ac',
    ),
    'maxmind-db/reader' => 
    array (
      'pretty_version' => 'v1.8.0',
      'version' => '1.8.0.0',
      'aliases' => 
      array (
      ),
      'reference' => 'b566d429ac9aec10594b0935be8ff38302f8d5c8',
    ),
    'maxmind/web-service-common' => 
    array (
      'pretty_version' => 'v0.8.1',
      'version' => '0.8.1.0',
      'aliases' => 
      array (
      ),
      'reference' => '32f274051c543fc865e5a84d3a2c703913641ea8',
    ),
    'psr/cache' => 
    array (
      'pretty_version' => '1.0.1',
      'version' => '1.0.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'd11b50ad223250cf17b86e38383413f5a6764bf8',
    ),
    's1lentium/iptools' => 
    array (
      'pretty_version' => 'v1.1.1',
      'version' => '1.1.1.0',
      'aliases' => 
      array (
      ),
      'reference' => 'f6f8ab6132ca7443bd7cced1681f5066d725fd5f',
    ),
    'whichbrowser/parser' => 
    array (
      'pretty_version' => 'v2.0.42',
      'version' => '2.0.42.0',
      'aliases' => 
      array (
      ),
      'reference' => '4899110cd2f87b01e04ced62dbb9dec541031dee',
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
