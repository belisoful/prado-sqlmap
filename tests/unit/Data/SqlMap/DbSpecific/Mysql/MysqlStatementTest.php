<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../StatementTest.php');

class MysqlStatementTest extends StatementTest
{
	protected static string $configClass = 'MySQLBaseTestConfig';
}
