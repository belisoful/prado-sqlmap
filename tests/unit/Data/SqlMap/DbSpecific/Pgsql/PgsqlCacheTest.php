<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../CacheTest.php');

class PgsqlCacheTest extends CacheTest
{
	protected static string $configClass = 'PgsqlBaseTestConfig';
}
