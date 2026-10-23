<?php

use Prado\Caching\TMemoryCache;
use Prado\Data\SqlMap\TSqlMapConfig;
use Prado\Data\SqlMap\TSqlMapManager;
use Prado\Prado;
use Prado\Test\Unit\PradoUnit;

class TSqlMapManagerVersionTest extends PHPUnit\Framework\TestCase
{
	private $appCache;

	protected function setUp(): void
	{
		$this->appCache = PradoUnit::getProp(Prado::getApplication(), '_cache');
	}

	protected function tearDown(): void
	{
		PradoUnit::setProp(Prado::getApplication(), '_cache', $this->appCache);
	}

	private function cachingConfig(): TSqlMapConfig
	{
		$cache = new TMemoryCache();
		$cache->setPrimaryCache(false);
		$cache->init(null);
		PradoUnit::setProp(Prado::getApplication(), '_cache', $cache);

		$config = new TSqlMapConfig();
		$config->setConfigFile(__DIR__ . '/maps/tests.xml');
		$config->setEnableCache(true);
		return $config;
	}

	private function cacheKey(TSqlMapConfig $config): string
	{
		return $config->getID() . $config->getConfigFile();
	}

	public function testVersionIsSemantic()
	{
		$this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', TSqlMapManager::VERSION);
	}

	public function testNewManagerReportsPackageVersion()
	{
		$manager = new TSqlMapManager();
		$this->assertSame(TSqlMapManager::VERSION, $manager->getVersion());
		$this->assertSame(TSqlMapManager::VERSION, $manager->Version);
	}

	public function testVersionIsSerialized()
	{
		$manager = unserialize(serialize(new TSqlMapManager()));
		$this->assertSame(TSqlMapManager::VERSION, $manager->getVersion());
	}

	public function testManagerSerializedWithoutVersionReportsNull()
	{
		$class = TSqlMapManager::class;
		$manager = unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class));
		$this->assertInstanceOf(TSqlMapManager::class, $manager);
		$this->assertNull($manager->getVersion());
	}

	public function testConfigReusesCachedManagerOfSameVersion()
	{
		$config = $this->cachingConfig();
		$first = $config->getSqlMapManager();
		$this->assertSame(TSqlMapManager::VERSION, $first->getVersion());

		$cached = Prado::getApplication()->getCache()->get($this->cacheKey($config));
		$this->assertInstanceOf(TSqlMapManager::class, $cached);
		$this->assertSame(TSqlMapManager::VERSION, $cached->getVersion());

		$second = $config->getSqlMapManager();
		$this->assertSame(TSqlMapManager::VERSION, $second->getVersion());
		$this->assertTrue($second->getMappedStatements()->contains('test'));
	}

	public function testConfigDiscardsCachedManagerOfAnotherVersion()
	{
		$config = $this->cachingConfig();
		$cache = Prado::getApplication()->getCache();

		foreach (['0.9.0', null] as $version) {
			$stale = new TSqlMapManager();
			PradoUnit::setProp($stale, '_version', $version);
			$cache->set($this->cacheKey($config), $stale);

			$manager = $config->getSqlMapManager();
			$this->assertSame(TSqlMapManager::VERSION, $manager->getVersion());
			$this->assertTrue($manager->getMappedStatements()->contains('test'));
			$this->assertSame(TSqlMapManager::VERSION, $cache->get($this->cacheKey($config))->getVersion());
		}
	}
}
