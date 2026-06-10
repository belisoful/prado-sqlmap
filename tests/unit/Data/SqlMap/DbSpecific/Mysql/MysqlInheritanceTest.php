<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../InheritanceTest.php');

class MysqlInheritanceTest extends InheritanceTest
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
