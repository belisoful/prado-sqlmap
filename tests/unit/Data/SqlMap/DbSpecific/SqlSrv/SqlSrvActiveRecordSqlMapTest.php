<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../ActiveRecordSqlMapTestCase.php');

class SqlSrvActiveRecordSqlMapTest extends ActiveRecordSqlMapTestCase
{
	protected static string $configClass = 'SqlSrvBaseTestConfig';
}
