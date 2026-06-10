<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../GroupByTest.php');

class OracleGroupByTest extends GroupByTest
{
	protected static string $configClass = 'OracleBaseTestConfig';
}
