<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../ResultClassTest.php');

class PgsqlResultClassTest extends ResultClassTest
{
	protected static string $configClass = 'PgsqlBaseTestConfig';
}
