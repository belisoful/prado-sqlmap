<?php

require_once(__DIR__ . '/../../queryForListLimitTest.php');

class SqliteQueryForListLimitTest extends queryForListLimitTest
{
	protected static string $configClass = 'SQLiteBaseTestConfig';
}
