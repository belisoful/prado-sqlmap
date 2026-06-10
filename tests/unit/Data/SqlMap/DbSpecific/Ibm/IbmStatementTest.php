<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../StatementTest.php');

class IbmStatementTest extends StatementTest
{
	protected static string $configClass = 'IbmBaseTestConfig';
}
