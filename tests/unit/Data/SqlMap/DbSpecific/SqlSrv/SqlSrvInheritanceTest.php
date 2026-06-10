<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../InheritanceTest.php');

class SqlSrvInheritanceTest extends InheritanceTest
{
	protected static string $configClass = 'SqlSrvBaseTestConfig';
}
