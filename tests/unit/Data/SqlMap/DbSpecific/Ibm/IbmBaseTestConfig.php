<?php

use Prado\Data\TDbConnection;

class IbmBaseTestConfig extends BaseTestConfig
{
	public function __construct()
	{
		$this->_sqlmapConfigFile = SQLMAP_TESTS . '/maps/ibm/sqlmap.xml';
		$this->_scriptDir = SQLMAP_TESTS . '/scripts/ibm/';
		$user     = getenv('DB2_USER')     ?: 'db2inst1';
		$password = getenv('DB2_PASSWORD') ?: 'Prado_Unitest1';
		$dbname   = getenv('DB2_DATABASE') ?: 'pradount';
		$dsn = 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=' . $dbname . ';HOSTNAME=localhost;PORT=50000;PROTOCOL=TCPIP';
		$this->_connection = new TDbConnection($dsn, $user, $password);
	}

	public function getScriptRunner()
	{
		return new IbmScriptRunner();
	}
}
