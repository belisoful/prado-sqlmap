<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../PropertyAccessTest.php');

class SqlSrvPropertyAccessTest extends PropertyAccessTest
{
	protected static string $configClass = 'SqlSrvBaseTestConfig';
}
