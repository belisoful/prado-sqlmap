<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../queryForListLimitTest.php');

class PgsqlQueryForListLimitTest extends queryForListLimitTest
{
	protected static string $configClass = 'PgsqlBaseTestConfig';
}
