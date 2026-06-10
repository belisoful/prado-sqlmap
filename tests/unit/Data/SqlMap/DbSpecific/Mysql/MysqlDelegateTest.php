<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../DelegateTest.php');

class MysqlDelegateTest extends DelegateTest
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
