<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../CacheTest.php');

class MysqlCacheTest extends CacheTest
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
