<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../ResultMapTest.php');

class PgsqlResultMapTest extends ResultMapTest
{
	protected static string $configClass = 'PgsqlBaseTestConfig';
}
