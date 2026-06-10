<?php

require_once(__DIR__ . '/../../InheritanceTest.php');

class SqliteInheritanceTest extends InheritanceTest
{
	protected static string $configClass = 'SQLiteBaseTestConfig';
}
