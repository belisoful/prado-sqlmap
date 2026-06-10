<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../queryForListLimitTest.php');

class OracleQueryForListLimitTest extends queryForListLimitTest
{
	protected static string $configClass = 'OracleBaseTestConfig';
}
