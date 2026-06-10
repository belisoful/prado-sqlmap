<?php

require_once(__DIR__ . '/../../PropertyAccessTest.php');

class SqlitePropertyAccessTest extends PropertyAccessTest
{
	protected static string $configClass = 'SQLiteBaseTestConfig';
}
