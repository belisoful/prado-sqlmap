<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../queryForListLimitTest.php');

class MysqlQueryForListLimitTest extends queryForListLimitTest
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
