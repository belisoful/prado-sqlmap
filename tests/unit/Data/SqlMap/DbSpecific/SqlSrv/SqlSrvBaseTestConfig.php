<?php

use Prado\Data\TDbConnection;

class SqlSrvBaseTestConfig extends BaseTestConfig
{
	public function __construct()
	{
		$this->_sqlmapConfigFile = SQLMAP_TESTS . '/maps/mssql/sqlmap.xml';
		$this->_scriptDir = SQLMAP_TESTS . '/scripts/sqlsrv/';
		$this->_features = ['insert_id'];
		$dsn = 'sqlsrv:Server=localhost,1433;Database=prado_unitest;TrustServerCertificate=yes';
		$this->_connection = new TDbConnection($dsn, 'prado_unitest', 'prado_unitest');
	}
}
