<?php

// Grant backwards compatibility to PHPUnit 6 tests
if (!class_exists('\PHPUnit\Framework\TestCase') &&
    class_exists('\PHPUnit_Framework_TestCase')) {
	class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

if (file_exists($file = __DIR__.'/../vendor/autoload.php')) {
	$autoload = require_once $file;
} else {
	throw new RuntimeException('Install dependencies using Composer, to be able to run test suite.');
}
return $autoload;