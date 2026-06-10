<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../CacheTest.php');

class SqlSrvCacheTest extends CacheTest
{
	protected static string $configClass = 'SqlSrvBaseTestConfig';
}
