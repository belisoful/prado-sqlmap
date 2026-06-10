<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../StatementTest.php');

class OracleStatementTest extends StatementTest
{
	protected static string $configClass = 'OracleBaseTestConfig';
}
