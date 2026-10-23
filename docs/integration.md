[SqlMap Docs](README.md) -> Prado Integration

# Prado Integration

This page covers registering prado-sqlmap as a PRADO application module, bootstrapping the manager, and calling the gateway from PHP code.

---

## Extension Registration

[`TSqlMapConfig`](#tsqlmapconfig) is a `TDataSourceConfig` subclass. Declare it as a module in `application.xml` to make it available throughout the application.

### Attributes

| Attribute | Type | Required | Description |
|---|---|---|---|
| `id` | string | yes | Module identifier used to retrieve the module in PHP. |
| `ConnectionID` | string | no | ID of an existing `TDataSourceConfig` module whose connection this module reuses. Declare that module before this one. |
| `ConfigFile` | string | yes | Path to the SqlMap XML config file. Accepts a PRADO namespace (e.g. `Application.Config.sqlmap`) or an absolute filesystem path. The file extension must be `.xml`. |
| `EnableCache` | bool | no (default `false`) | When `true`, the parsed [`TSqlMapManager`](#tsqlmapmanager) is stored in the application cache. See [Caching the parsed manager](#caching-the-parsed-manager). |

### Example — shared connection module

```xml
<modules>
    <module id="db" class="Prado\Data\TDataSourceConfig">
        <database ConnectionString="mysql:host=localhost;dbname=mydb"
                  Username="dbuser" Password="dbpass" />
    </module>

    <module id="sqlmap" class="Prado\Data\SqlMap\TSqlMapConfig"
            ConnectionID="db"
            ConfigFile="Application.Config.sqlmap"
            EnableCache="true" />
</modules>
```

### Example — inline database element

```xml
<modules>
    <module id="sqlmap" class="Prado\Data\SqlMap\TSqlMapConfig"
            ConfigFile="Application.Config.sqlmap"
            EnableCache="true">
        <database ConnectionString="sqlite:/var/data/myapp.db" />
    </module>
</modules>
```

### PHP configuration style

```php
return [
    'modules' => [
        'sqlmap' => [
            'class' => 'Prado\Data\SqlMap\TSqlMapConfig',
            'properties' => [
                'ConfigFile' => 'Application.Config.sqlmap',
                'EnableCache' => 'true',
            ],
            'database' => [
                'ConnectionString' => 'mysql:host=localhost;dbname=mydb',
                'Username' => 'dbuser',
                'Password' => 'dbpass',
            ],
        ],
    ],
];
```

---

## TSqlMapConfig

**Class:** `Prado\Data\SqlMap\TSqlMapConfig`

`TSqlMapConfig` bootstraps the extension. On first access of its `Client` property it:

1. Checks the application cache for a previously serialized [`TSqlMapManager`](#tsqlmapmanager) (when `EnableCache` is `true`). A cached manager whose `Version` is not `TSqlMapManager::VERSION` was written by another release of this package and is ignored.
2. If not cached, instantiates a new `TSqlMapManager`, calls `configureXml()` with the resolved config file path, and stores the result in the cache.
3. Calls `getSqlmapGateway()` on the manager and returns the resulting [`TSqlMapGateway`](#tsqlmapgateway).

When `ConnectionID` is set and the manager is loaded from cache, `TSqlMapConfig` injects the live `TDbConnection` into the cached manager so the connection object (which is not serialized) is always current.

| Property | PHP accessor | Description |
|---|---|---|
| `ConfigFile` | `getConfigFile()` / `setConfigFile()` | Resolved filesystem path to the SqlMap XML config file. |
| `EnableCache` | `getEnableCache()` / `setEnableCache()` | Whether the parsed manager is stored in the application cache. |
| `Client` | `getClient()` | Returns the [`TSqlMapGateway`](#tsqlmapgateway) instance. This is the main entry point for application code. |

`clearCache()` removes the cached manager entry, forcing a fresh parse on the next request.

---

## TSqlMapManager

**Class:** `Prado\Data\SqlMap\TSqlMapManager`

`TSqlMapManager` holds all parsed configuration — mapped statements, result maps, parameter maps, cache models — and owns the database connection.

### Key methods

| Method | Signature | Description |
|---|---|---|
| `configureXml` | `configureXml(string $file): void` | Parses a SqlMap XML config file and registers all statements, result maps, parameter maps, and cache models into this manager. |
| `getSqlmapGateway` | `getSqlmapGateway(): TSqlMapGateway` | Returns (creating once) the [`TSqlMapGateway`](#tsqlmapgateway) bound to this manager. |
| `getTypeHandlers` | `getTypeHandlers(): TSqlMapTypeHandlerRegistry` | Returns the type-handler registry. Use it to register custom [`TSqlMapTypeHandler`](mappings/typeHandler.md) implementations. |
| `getDbConnection` | `getDbConnection(): TDbConnection` | Returns the active database connection. |
| `setDbConnection` | `setDbConnection(TDbConnection $conn): void` | Replaces the database connection (used by `TSqlMapConfig` after a cache hit). |
| `flushCacheModels` | `flushCacheModels(): void` | Flushes all statement-level cache models registered with this manager. |
| `getVersion` | `getVersion(): ?string` | Returns the package version that built the manager, in the form of `Prado::getVersion()`. A new manager returns `TSqlMapManager::VERSION`; a manager unserialized from a cache written by another release returns that release's version, or `null` when none was recorded. |

The manager is also the target for direct (non-PRADO) use:

```php
$conn = new \Prado\Data\TDbConnection('sqlite:/var/data/myapp.db');
$manager = new \Prado\Data\SqlMap\TSqlMapManager($conn);
$manager->configureXml('/path/to/sqlmap.xml');
$sqlmap = $manager->getSqlmapGateway();
```

---

## TSqlMapGateway

**Class:** `Prado\Data\SqlMap\TSqlMapGateway`

`TSqlMapGateway` is the public API for executing mapped statements. Obtain it via `TSqlMapConfig::getClient()` (the `Client` property) or directly from `TSqlMapManager::getSqlmapGateway()`.

### Method reference

| Method | Signature | Returns | Description |
|---|---|---|---|
| `queryForObject` | `queryForObject(string $statementName, mixed $parameter = null, mixed $result = null): object\|null` | Single mapped object or `null` | Executes a `SELECT` and maps the first row to an object. Pass an existing object as `$result` to populate it in place. |
| `queryForList` | `queryForList(string $statementName, mixed $parameter = null, TList $result = null, int $skip = -1, int $max = -1): TList` | `TList` | Executes a `SELECT` and maps all rows to a list. `$skip` and `$max` limit the result window. |
| `queryWithRowDelegate` | `queryWithRowDelegate(string $statementName, callable $delegate, mixed $parameter = null, TList $result = null, int $skip = -1, int $max = -1): TList` | `TList` | Same as `queryForList` but calls `$delegate($list, $object, $index)` for each row, giving the caller control over accumulation. |
| `queryForPagedList` | `queryForPagedList(string $statementName, mixed $parameter = null, int $pageSize = 10, int $page = 0): TPagedList` | `TPagedList` | Returns a paginated list that fetches pages on demand. |
| `queryForPagedListWithRowDelegate` | `queryForPagedListWithRowDelegate(string $statementName, callable $delegate, mixed $parameter = null, int $pageSize = 10, int $page = 0): TPagedList` | `TPagedList` | Same as `queryForPagedList` with per-row delegate. |
| `queryForMap` | `queryForMap(string $statementName, mixed $parameter = null, string $keyProperty = null, string $valueProperty = null, int $skip = -1, int $max = -1): TMap` | `TMap` | Returns rows as a map keyed by `$keyProperty`. When `$valueProperty` is `null`, the full result object is the map value. |
| `queryForMapWithRowDelegate` | `queryForMapWithRowDelegate(string $statementName, callable $delegate, mixed $parameter = null, string $keyProperty = null, string $valueProperty = null, int $skip = -1, int $max = -1): TMap` | `TMap` | Same as `queryForMap` with per-row delegate. |
| `insert` | `insert(string $statementName, mixed $parameter = null): mixed` | Generated primary key or `null` | Executes an `INSERT`. Returns the value produced by the statement's `selectKey` element, or the last-insert-id from PDO when no `selectKey` is defined. |
| `update` | `update(string $statementName, mixed $parameter = null): int` | Affected row count | Executes an `UPDATE` (or any DML). Returns the number of affected rows. |
| `delete` | `delete(string $statementName, mixed $parameter = null): int` | Affected row count | Executes a `DELETE`. Delegates to `update` internally; returns affected row count. |
| `flushCaches` | `flushCaches(): void` | — | Flushes all statement-level cache models registered with the underlying manager. |
| `registerTypeHandler` | `registerTypeHandler(TSqlMapTypeHandler $typeHandler): void` | — | Registers a custom type handler with the manager's type-handler registry. |

---

## Getting the Gateway in PHP

Retrieve the module by its `id` from the application's module collection, then access the `Client` property:

```php
// Inside a TPage, TControl, or TModule subclass:
/** @var \Prado\Data\SqlMap\TSqlMapGateway $sqlmap */
$sqlmap = $this->Application->Modules['sqlmap']->Client;

// Query for a single object
$account = $sqlmap->queryForObject('GetAccountById', $accountId);

// Query for a list
$accounts = $sqlmap->queryForList('GetAllAccounts');

// Insert and retrieve the new primary key
$newId = $sqlmap->insert('InsertAccount', $accountData);

// Update, returns affected row count
$affected = $sqlmap->update('UpdateAccountEmail', $accountData);

// Delete
$sqlmap->delete('DeleteAccount', $accountId);
```

The `Client` property caches the gateway instance — calling it multiple times within a request returns the same object.

---

## Caching the Parsed Manager

Parsing SqlMap XML configuration files on every request adds overhead proportional to the number of statements and mapping files. Setting `EnableCache="true"` serializes the fully constructed [`TSqlMapManager`](#tsqlmapmanager) into the PRADO application cache after the first parse.

**Cache key:** the module `id` concatenated with the resolved `ConfigFile` path. Each module instance has its own cache entry.

**Cache invalidation:** unless the application is running in `TApplicationMode::Performance` mode, `TSqlMapConfig` attaches file-modification dependencies for each parsed XML file. The cached entry is invalidated automatically when any of those files change on disk.

In `Performance` mode the dependencies are not attached — the cache entry persists until it expires or is manually cleared. Call `TSqlMapConfig::clearCache()` to force invalidation:

```php
$this->Application->Modules['sqlmap']->clearCache();
```

After `clearCache()`, the next access to `Client` triggers a fresh parse and re-caches the result.

A PRADO application cache component (e.g. `TMemCache`, `TDbCache`, `TFileCache`) must be configured for caching to take effect. When no cache component is present, `EnableCache` is silently ignored and the manager is re-parsed on every request.

---

[SqlMap Docs](README.md) -> Prado Integration

## See also

- [SqlMap Config File](configuration/sqlMapConfig.md) — `sqlMapConfig` XML root element and all top-level configuration options
- [Statement Mappings](statements/select.md) — `select`, `insert`, `update`, `delete` statement definitions
- [Cache Models](configuration/cacheModel.md) — `cacheModel` configuration for statement-level caching
