[prado-sqlmap](../README.md) -> [SqlMap Docs](README.md) -> Migration

# Migration guide

SqlMap shipped inside the PRADO framework from PRADO 3.1. PRADO 4.4 extracts it into this package. The class names and namespaces are unchanged, so most applications need only add the dependency and clear the application cache.

## Install the package

```sh
composer require pradosoft/prado-sqlmap
```

PRADO reads the package's `extra.prado` section and registers its class map and error messages system-wide. Existing module definitions keep working without changes.

## What is unchanged

- **Namespaces**: every class keeps its `Prado\Data\SqlMap\...` namespace, for example `Prado\Data\SqlMap\TSqlMapConfig` and `Prado\Data\SqlMap\DataMapper\TSqlMapFifoCache`.
- **Short names** still resolve. `config/classes.json` maps them to their FQNs, so `<module class="TSqlMapConfig">` and `Prado::createComponent('TSqlMapFifoCache')` work as before.
- **Module configuration**: `TSqlMapConfig` properties (`ConfigFile`, `ConnectionID`, `EnableCache`) and the inline `<database>` element are unchanged.
- **XML formats**: `sqlMapConfig` and mapping files use the same elements and attributes.
- **API**: `TSqlMapGateway` methods and their arguments are unchanged.
- **Error codes**: existing `sqlmap_*` codes keep their text.

## What changed

### `TSqlMapException::getErrorCode()` returns the code

`getErrorCode()` returns the message code, matching other PRADO exceptions. It previously returned the translated message.

```php
try {
	$gateway->queryForObject('missing');
} catch (TSqlMapUndefinedException $e) {
	$e->getErrorCode(); // before: "Unable to find SQLMap statement 'missing'."
	                    // after:  "sqlmap_contains_no_statement"
	$e->getMessage();   // unchanged: "Unable to find SQLMap statement 'missing'."
}
```

Code that compares `getErrorCode()` with message text must compare `getMessage()` instead.

### Column names match regardless of case

When a row has no column with the exact mapped name, a case-insensitive match is used. Oracle (`pdo_oci`) and PostgreSQL (`pdo_pgsql`) results now fill properties that previously stayed empty. Queries that alias columns to work around the folding continue to work.

### Driver-specific parameter binding

| Driver | Value | Before | After |
|---|---|---|---|
| Firebird | PHP `bool` | bound as boolean; rejected by integer columns | bound as `0` or `1` |
| IBM DB2 | `null` without `dbType` | bound as `PDO::PARAM_NULL`; rejected by `pdo_ibm` | bound as `PDO::PARAM_STR` (SQL `NULL`) |

### Cached statements

Statements with a `cacheModel` build their cache key from the SQL text. Cached statements with bound parameters previously failed with "Serialization of 'PDOStatement' is not allowed" and now cache as configured. Keys for statements without parameters differ from before, so earlier entries are not reused.

### Serialized managers

With `EnableCache="true"`, `TSqlMapConfig` stores the parsed `TSqlMapManager` in the application cache. The serialized form of several classes differs from the framework copy. Clear the application cache once after upgrading so the manager is rebuilt.

## Verifying the move

After installing the package:

1. Confirm `class="TSqlMapConfig"` module definitions still resolve.
2. Clear the application cache, or delete the cache files under `protected/runtime`.
3. Replace any comparison of `TSqlMapException::getErrorCode()` with message text.
4. Run the application's own tests against its mapped statements.
