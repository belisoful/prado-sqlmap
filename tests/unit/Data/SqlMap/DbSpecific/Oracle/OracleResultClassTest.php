<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../ResultClassTest.php');

class OracleResultClassTest extends ResultClassTest
{
	protected static string $configClass = 'OracleBaseTestConfig';
}
