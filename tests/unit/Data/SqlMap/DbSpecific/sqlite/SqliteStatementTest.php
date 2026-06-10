<?php

require_once(__DIR__ . '/../../StatementTest.php');

class SqliteStatementTest extends StatementTest
{
	protected static string $configClass = 'SQLiteBaseTestConfig';
}
