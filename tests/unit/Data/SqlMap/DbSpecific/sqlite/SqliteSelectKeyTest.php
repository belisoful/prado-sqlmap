<?php

require_once(__DIR__ . '/../../SelectKeyTest.php');

class SqliteSelectKeyTest extends SelectKeyTest
{
	protected static string $configClass = 'SQLiteBaseTestConfig';
}
