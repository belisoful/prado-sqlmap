<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../InheritanceTest.php');

class FirebirdInheritanceTest extends InheritanceTest
{
	protected static string $configClass = 'FirebirdBaseTestConfig';
}
