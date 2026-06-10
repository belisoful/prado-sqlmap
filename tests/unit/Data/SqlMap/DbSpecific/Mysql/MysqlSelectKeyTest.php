<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../SelectKeyTest.php');

class MysqlSelectKeyTest extends SelectKeyTest
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
