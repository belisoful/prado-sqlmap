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
		// pdo_firebird keeps a single implicit transaction open for the life of the
		// connection and does not commit DDL/DML on its own; uncommitted DDL holds a
		// metadata lock that makes the next query block forever.  Enabling autocommit
		// (applied as a PDO constructor driver-option, since it is set before open)
		// commits each statement, releasing the lock.
		$this->_connection->setAutoCommit(true);
	}

	public function getScriptRunner()
	{
		return new FirebirdScriptRunner();
	}
}
