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
		// pdo_ibm defaults PDO::ATTR_CASE to CASE_UPPER, which also folds quoted
		// aliases such as "Id". CASE_NATURAL keeps the names DB2 reports.
		$this->_connection->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
	}

	public function getScriptRunner()
	{
		return new IbmScriptRunner();
	}
}
