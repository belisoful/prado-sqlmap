<?php

require_once(__DIR__ . '/../../GroupByTest.php');

class SqliteGroupByTest extends GroupByTest
{
	protected static string $configClass = 'SQLiteBaseTestConfig';
}
