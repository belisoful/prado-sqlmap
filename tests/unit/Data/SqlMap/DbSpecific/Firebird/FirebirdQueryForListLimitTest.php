<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../queryForListLimitTest.php');

class FirebirdQueryForListLimitTest extends queryForListLimitTest
{
	protected static string $configClass = 'FirebirdBaseTestConfig';
}
