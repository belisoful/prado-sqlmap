<?php

require_once(__DIR__ . '/../../ActiveRecordSqlMapTestCase.php');

class SqliteActiveRecordSqlMapTest extends ActiveRecordSqlMapTestCase
{
	protected static string $configClass = 'SQLiteBaseTestConfig';
}
