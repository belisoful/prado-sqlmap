<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../SelectKeyTest.php');

class FirebirdSelectKeyTest extends SelectKeyTest
{
	protected static string $configClass = 'FirebirdBaseTestConfig';
}
