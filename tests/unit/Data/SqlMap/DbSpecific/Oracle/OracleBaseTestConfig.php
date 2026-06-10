<?php

use Prado\Data\TDbConnection;

/**
 * TDbConnection subclass for Oracle that sets ISO date/timestamp NLS formats
 * immediately after the connection opens.  Without this, pdo_oci uses the
 * session NLS_DATE_FORMAT / NLS_TIMESTAMP_FORMAT which defaults to
 * 'DD-MON-RR' style, causing ORA-01843 when PHP date strings like
 * '2005-05-20' are bound to DATE/TIMESTAMP columns.
 */
class OracleNLSConnection extends TDbConnection
{
	public function setActive($value)
	{
		$wasActive = $this->getActive();
		parent::setActive($value);
		if (!$wasActive && $this->getActive()) {
			$this->createCommand("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'")->execute();
			$this->createCommand("ALTER SESSION SET NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS'")->execute();
		}
	}
}

class OracleBaseTestConfig extends BaseTestConfig
{
	public function __construct()
	{
		$this->_sqlmapConfigFile = SQLMAP_TESTS . '/maps/oracle/sqlmap.xml';
		$this->_scriptDir = SQLMAP_TESTS . '/scripts/oracle/';
		$dsn = 'oci:dbname=//localhost:1521/FREEPDB1';
		$this->_connection = new OracleNLSConnection($dsn, 'prado_unitest', 'prado_unitest');
	}
}
