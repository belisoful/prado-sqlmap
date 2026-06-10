<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../CacheTest.php');

class IbmCacheTest extends CacheTest
{
	protected static string $configClass = 'IbmBaseTestConfig';
}
