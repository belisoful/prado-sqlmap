<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../CacheTest.php');

class FirebirdCacheTest extends CacheTest
{
	protected static string $configClass = 'FirebirdBaseTestConfig';
}
