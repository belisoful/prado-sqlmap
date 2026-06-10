<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../PropertyAccessTest.php');

class MysqlPropertyAccessTest extends PropertyAccessTest
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
