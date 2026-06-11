<?php

use Prado\Data\TDbConnection;

if (!defined('SQLMAP_TESTS')) {
	define('SQLMAP_TESTS', realpath(__DIR__));
}

if (!class_exists('Account', false)) {
	include(SQLMAP_TESTS . '/domain/A.php');
	include(SQLMAP_TESTS . '/domain/Account.php');
	include(SQLMAP_TESTS . '/domain/Enumeration.php');
	include(SQLMAP_TESTS . '/domain/AccountBis.php');
	include(SQLMAP_TESTS . '/domain/AccountCollection.php');
	include(SQLMAP_TESTS . '/domain/B.php');
	include(SQLMAP_TESTS . '/domain/Document.php');
	include(SQLMAP_TESTS . '/domain/Book.php');
	include(SQLMAP_TESTS . '/domain/C.php');
	include(SQLMAP_TESTS . '/domain/Category.php');
	include(SQLMAP_TESTS . '/domain/Complex.php');
	include(SQLMAP_TESTS . '/domain/D.php');
	include(SQLMAP_TESTS . '/domain/DocumentCollection.php');
	include(SQLMAP_TESTS . '/domain/E.php');
	include(SQLMAP_TESTS . '/domain/F.php');
	include(SQLMAP_TESTS . '/domain/LineItem.php');
	include(SQLMAP_TESTS . '/domain/LineItemCollection.php');
	include(SQLMAP_TESTS . '/domain/Newspaper.php');
	include(SQLMAP_TESTS . '/domain/Order.php');
	include(SQLMAP_TESTS . '/domain/Other.php');
	include(SQLMAP_TESTS . '/domain/Sample.php');
	include(SQLMAP_TESTS . '/domain/Search.php');
	include(SQLMAP_TESTS . '/domain/User.php');
}

class DefaultScriptRunner
{
	public function runScript($connection, $script)
	{
		$sql = file_get_contents($script);
		$lines = explode(';', $sql);
		foreach ($lines as $line) {
			$line = trim($line);
			if (strlen($line) > 0) {
				$connection->createCommand($line)->execute();
			}
		}
	}
}

/**
 * Script runner for IBM DB2. Silently ignores SQLSTATE 42704 ("undefined name")
 * errors so that DROP TABLE/SEQUENCE statements do not abort on a fresh database
 * where the objects do not yet exist.
 */
class IbmScriptRunner extends DefaultScriptRunner
{
	public function runScript($connection, $script)
	{
		$sql = file_get_contents($script);
		$lines = explode(';', $sql);
		foreach ($lines as $line) {
			$line = trim($line);
			if (strlen($line) === 0) {
				continue;
			}
			try {
				$connection->createCommand($line)->execute();
			} catch (\Exception $e) {
				// Object does not exist — safe to ignore for DROP statements on a
				// fresh database. DB2 reports this as SQLSTATE 42704 / SQL0204N.
				$msg = $e->getMessage();
				$isObjectNotFound = stripos($msg, '42704') !== false
					|| stripos($msg, 'SQL0204') !== false
					|| stripos($msg, '-204') !== false;
				if (!$isObjectNotFound) {
					throw $e;
				}
			}
		}
	}
}

/**
 * Script runner for Firebird. Silently ignores "object unknown" errors (SQLCODE -204)
 * so that DROP TABLE/SEQUENCE statements do not abort on a fresh database where the
 * objects do not yet exist.
 */
class FirebirdScriptRunner extends DefaultScriptRunner
{
	public function runScript($connection, $script)
	{
		$sql = file_get_contents($script);
		$lines = explode(';', $sql);
		foreach ($lines as $line) {
			$line = trim($line);
			if (strlen($line) === 0) {
				continue;
			}
			// Transaction control statements cannot be executed as SQL via Firebird PDO.
			// Route them through the PDO API instead and swallow errors (e.g. no active
			// transaction to commit/rollback) so the script runner never aborts on them.
			if (preg_match('/^(COMMIT|ROLLBACK)(\s+WORK)?\s*$/i', $line)) {
				try {
					if (stripos($line, 'ROLLBACK') === 0) {
						$connection->getPdoInstance()->rollBack();
					} else {
						$connection->getPdoInstance()->commit();
					}
				} catch (\Exception $e) {
					// No active transaction or already committed — safe to ignore.
				}
				continue;
			}
			try {
				$connection->createCommand($line)->execute();
			} catch (\Exception $e) {
				// Firebird SQLCODE -204: object unknown (table/sequence does not exist).
				$msg = $e->getMessage();
				$isObjectUnknown = strpos($msg, '-204') !== false
					|| stripos($msg, 'does not exist') !== false
					|| stripos($msg, 'Table unknown') !== false
					|| stripos($msg, 'Sequence unknown') !== false
					|| stripos($msg, 'object unknown') !== false;
				if (!$isObjectUnknown) {
					throw $e;
				}
			}
		}
	}
}

class CopyFileScriptRunner
{
	protected $baseFile;
	protected $targetFile;

	public function __construct($base, $target)
	{
		$this->baseFile = $base;
		$this->targetFile = $target;
	}

	public function runScript($connection, $script)
	{
		copy($this->baseFile, $this->targetFile);
	}
}

class BaseTestConfig
{
	protected $_scriptDir;
	protected $_connection;
	protected $_sqlmapConfigFile;
	protected $_features = [];

	public function hasFeature($type)
	{
		return in_array($type, $this->_features, true);
	}

	public function getScriptDir()
	{
		return $this->_scriptDir;
	}

	public function getConnection()
	{
		return $this->_connection;
	}

	public function getSqlMapConfigFile()
	{
		return $this->_sqlmapConfigFile;
	}

	public function getScriptRunner()
	{
		return new DefaultScriptRunner();
	}

	public static function createConfigInstance()
	{
		//change this to connection to a different database

		//return new MySQLBaseTestConfig();

		return new SQLiteBaseTestConfig();

		//return new MSSQLBaseTestConfig();
	}
}

class SQLiteBaseTestConfig extends BaseTestConfig
{
	protected $baseFile;
	protected $targetFile;

	public function __construct()
	{
		$this->_sqlmapConfigFile = SQLMAP_TESTS . '/maps/sqlite/sqlmap.xml';
		$this->_scriptDir = SQLMAP_TESTS . '/scripts/sqlite/';
		$this->_features = ['insert_id'];

		$this->baseFile = realpath(SQLMAP_TESTS . '/sqlite/backup.db');
		// tests.db is always replaced by CopyFileScriptRunner before the first test
		// method runs; it does not need to exist in the repository.  Use a plain path
		// (no realpath()) so the DSN is valid even when the file is absent on a
		// fresh checkout.
		$this->targetFile = SQLMAP_TESTS . '/sqlite/tests.db';
		$this->_connection = new TDbConnection("sqlite:{$this->targetFile}");
	}

	public function getScriptRunner()
	{
		return new CopyFileScriptRunner($this->baseFile, $this->targetFile);
	}
}

require_once(__DIR__ . '/DbSpecific/Mysql/MySQLBaseTestConfig.php');
require_once(__DIR__ . '/DbSpecific/Pgsql/PgsqlBaseTestConfig.php');
require_once(__DIR__ . '/DbSpecific/SqlSrv/SqlSrvBaseTestConfig.php');
require_once(__DIR__ . '/DbSpecific/Oracle/OracleBaseTestConfig.php');
require_once(__DIR__ . '/DbSpecific/Firebird/FirebirdBaseTestConfig.php');
require_once(__DIR__ . '/DbSpecific/Ibm/IbmBaseTestConfig.php');
