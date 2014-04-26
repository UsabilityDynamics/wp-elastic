<?php
namespace elasticsearch;

if(!function_exists('elasticsearch\includeIfExists')){
	function includeIfExists($file)
	{
	    return file_exists($file) ? include $file : false;
	}
}

// if ((!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {}

//$loader->add(null, __DIR__.'/../src', true);

//return $loader;
