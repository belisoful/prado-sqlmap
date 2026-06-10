<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../SelectKeyTest.php');

class OracleSelectKeyTest extends SelectKeyTest
{
	protected static string $configClass = 'OracleBaseTestConfig';
}
