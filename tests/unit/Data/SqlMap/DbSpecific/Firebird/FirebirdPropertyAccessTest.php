<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../PropertyAccessTest.php');

class FirebirdPropertyAccessTest extends PropertyAccessTest
{
	protected static string $configClass = 'FirebirdBaseTestConfig';
}
