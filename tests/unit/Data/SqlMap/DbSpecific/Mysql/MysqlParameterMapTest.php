<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../ParameterMapTest.php');

class MysqlParameterMapTest extends ParameterMapTest
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
