<?php

use Prado\Data\TDbConnection;

class FirebirdBaseTestConfig extends BaseTestConfig
{
	public function __construct()
	{
		$this->_sqlmapConfigFile = SQLMAP_TESTS . '/maps/firebird/sqlmap.xml';
		$this->_scriptDir = SQLMAP_TESTS . '/scripts/firebird/';
		$dbPath = getenv('FIREBIRD_DB_PATH') ?: '/var/lib/firebird/data/prado_unitest.fdb';
		$dsn = 'firebird:dbname=localhost:' . $dbPath . ';charset=UTF8';
		$this->_connection = new TDbConnection($dsn, 'SYSDBA', 'masterkey');
	}

	public function getScriptRunner()
	{
		return new FirebirdScriptRunner();
	}
}
