<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../ResultMapTest.php');

class MysqlResultMapTest extends ResultMapTest
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
