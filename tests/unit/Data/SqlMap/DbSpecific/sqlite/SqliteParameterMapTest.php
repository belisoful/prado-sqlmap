<?php

require_once(__DIR__ . '/../../ParameterMapTest.php');

class SqliteParameterMapTest extends ParameterMapTest
{
	protected static string $configClass = 'SQLiteBaseTestConfig';
}
