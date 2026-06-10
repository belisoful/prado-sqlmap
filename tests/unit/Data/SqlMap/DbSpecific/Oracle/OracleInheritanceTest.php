<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../InheritanceTest.php');

class OracleInheritanceTest extends InheritanceTest
{
	protected static string $configClass = 'OracleBaseTestConfig';
}
