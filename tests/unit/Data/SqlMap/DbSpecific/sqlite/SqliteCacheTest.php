<?php

require_once(__DIR__ . '/../../CacheTest.php');

class SqliteCacheTest extends CacheTest
{
	protected static string $configClass = 'SQLiteBaseTestConfig';
}
