<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../PropertyAccessTest.php');

class IbmPropertyAccessTest extends PropertyAccessTest
{
	protected static string $configClass = 'IbmBaseTestConfig';
}
