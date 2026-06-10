<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../InheritanceTest.php');

class PgsqlInheritanceTest extends InheritanceTest
{
	protected static string $configClass = 'PgsqlBaseTestConfig';
}
