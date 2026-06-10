<?php

require_once(__DIR__ . '/../../DelegateTest.php');

class SqliteDelegateTest extends DelegateTest
{
	protected static string $configClass = 'SQLiteBaseTestConfig';
}
