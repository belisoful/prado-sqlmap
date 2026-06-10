<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../ActiveRecordSqlMapTestCase.php');

class MysqlActiveRecordSqlMapTest extends ActiveRecordSqlMapTestCase
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
