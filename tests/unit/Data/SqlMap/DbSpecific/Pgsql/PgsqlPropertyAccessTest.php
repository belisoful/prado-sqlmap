<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../PropertyAccessTest.php');

class PgsqlPropertyAccessTest extends PropertyAccessTest
{
	protected static string $configClass = 'PgsqlBaseTestConfig';
}
