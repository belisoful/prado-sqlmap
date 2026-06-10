<?php

require_once(__DIR__ . '/../../common.php');
require_once(__DIR__ . '/../../ActiveRecordSqlMapTestCase.php');

class IbmActiveRecordSqlMapTest extends ActiveRecordSqlMapTestCase
{
	protected static string $configClass = 'IbmBaseTestConfig';
}
