<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../TestQueryForMap.php');

class PgsqlTestQueryForMapTest extends TestQueryForMap
{
	protected static string $configClass = 'PgsqlBaseTestConfig';
}
