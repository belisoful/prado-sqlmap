<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../SelectKeyTest.php');

class PgsqlSelectKeyTest extends SelectKeyTest
{
	protected static string $configClass = 'PgsqlBaseTestConfig';
}
