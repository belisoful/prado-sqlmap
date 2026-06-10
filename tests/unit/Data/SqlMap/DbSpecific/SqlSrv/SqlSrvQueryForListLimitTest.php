<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../queryForListLimitTest.php');

class SqlSrvQueryForListLimitTest extends queryForListLimitTest
{
	protected static string $configClass = 'SqlSrvBaseTestConfig';
}
