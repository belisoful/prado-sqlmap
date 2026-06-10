<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../ParameterMapTest.php');

class PgsqlParameterMapTest extends ParameterMapTest
{
	protected static string $configClass = 'PgsqlBaseTestConfig';
}
