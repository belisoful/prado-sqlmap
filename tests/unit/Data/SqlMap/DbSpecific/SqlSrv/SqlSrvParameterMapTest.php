<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../ParameterMapTest.php');

class SqlSrvParameterMapTest extends ParameterMapTest
{
	protected static string $configClass = 'SqlSrvBaseTestConfig';
}
