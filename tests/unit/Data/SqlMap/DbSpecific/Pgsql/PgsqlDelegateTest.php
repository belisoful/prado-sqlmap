<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../DelegateTest.php');

class PgsqlDelegateTest extends DelegateTest
{
	protected static string $configClass = 'PgsqlBaseTestConfig';
}
