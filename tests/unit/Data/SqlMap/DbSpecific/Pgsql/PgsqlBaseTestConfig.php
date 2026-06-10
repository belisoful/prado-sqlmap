<?php

use Prado\Data\TDbConnection;
use Prado\Data\SqlMap\DataMapper\TSqlMapTypeHandler;

/**
 * TypeHandler that maps a PHP boolean to a PostgreSQL SMALLINT (0/1).
 *
 * PDO_PGSQL with native prepared statements encodes PHP true as the text literal
 * 't', which PostgreSQL refuses to cast to SMALLINT.  This handler returns an
 * integer (0 or 1) from getParameter() so the column receives a value it can
 * accept.  On the result side it casts the fetched string back to PHP bool.
 */
class PgsqlSmallintBoolHandler extends TSqlMapTypeHandler
{
	public function getResult($string)
	{
		return (bool) (int) $string;
	}

	public function getParameter($parameter)
	{
		return $parameter ? 1 : 0;
	}

	public function createNewInstance($data = null)
	{
		return false;
	}
}

class PgsqlBaseTestConfig extends BaseTestConfig
{
	public function __construct()
	{
		$this->_sqlmapConfigFile = SQLMAP_TESTS . '/maps/pgsql/sqlmap.xml';
		$this->_scriptDir = SQLMAP_TESTS . '/scripts/pgsql/';
		$this->_features = ['insert_id'];
		$dsn = 'pgsql:host=localhost;dbname=prado_unitest;port=5432';
		$this->_connection = new TDbConnection($dsn, 'prado_unitest', 'prado_unitest');
	}
}
