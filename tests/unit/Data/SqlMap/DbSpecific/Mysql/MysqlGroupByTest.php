<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../GroupByTest.php');

class MysqlGroupByTest extends GroupByTest
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
