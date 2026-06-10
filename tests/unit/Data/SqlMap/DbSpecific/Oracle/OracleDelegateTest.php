<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../DelegateTest.php');

class OracleDelegateTest extends DelegateTest
{
	protected static string $configClass = 'OracleBaseTestConfig';
}
