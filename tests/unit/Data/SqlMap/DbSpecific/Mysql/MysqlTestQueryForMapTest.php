<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../TestQueryForMap.php');

class MysqlTestQueryForMapTest extends TestQueryForMap
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
