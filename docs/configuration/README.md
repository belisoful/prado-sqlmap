[SqlMap Docs](../README.md) -> Configuration

# Configuration

`sqlMapConfig` is the root configuration file that wires together properties, type aliases, type handlers, a transaction manager, cache models, and `sqlMap` file references. It is parsed by [`TSqlMapXmlConfiguration`](../../src/Data/SqlMap/Configuration/TSqlMapXmlConfiguration.php) and consumed by [`TSqlMapManager`](../../src/Data/SqlMap/TSqlMapManager.php).

## `sqlMapConfig` — Root Element

`sqlMapConfig` has no attributes. All configuration is expressed through its child elements.

| Element | Required | Description |
|---|---|---|
| [`properties`](properties.md) | No | Loads external `.properties` files; values become `${key}` substitution tokens throughout the config. |
| [`typeAlias`](typeAlias.md) | No | Defines a short name for a fully qualified PHP class. |
| [`typeHandler`](typeHandler.md) | No | Registers a custom [`TSqlMapTypeHandler`](../../src/Data/SqlMap/DataMapper/TSqlMapTypeHandler.php) for a PHP or database type. |
| [`transactionManager`](transactionManager.md) | Yes | Declares the transaction strategy and the `dataSource` connection parameters. |
| [`sqlMap`](sqlMap.md) | Yes (at least one) | Includes a SQL mapping file that contains statements, result maps, and parameter maps. |
| [`cacheModel`](cacheModel.md) | No | Defines a named cache model that statements can reference for result caching. |

## Child Elements Overview

| Element | Doc page | Purpose |
|---|---|---|
| `properties` / `property` | [properties.md](properties.md) | Token substitution from `.properties` files or inline `property` declarations. |
| `typeAlias` | [typeAlias.md](typeAlias.md) | Short alias for a fully qualified class name. |
| `typeHandler` | [typeHandler.md](typeHandler.md) | Custom type conversion between PHP values and database columns. |
| `transactionManager` / `dataSource` | [transactionManager.md](transactionManager.md) | Database connection and transaction management. |
| `sqlMap` | [sqlMap.md](sqlMap.md) | Reference to an XML mapping file. |
| `cacheModel` | [cacheModel.md](cacheModel.md) | Named cache model with flush rules and type configuration. |

## Full Example

A complete `sqlMapConfig` file showing all sections:

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<sqlMapConfig>

    <!-- Load external token values -->
    <properties resource="database.properties" />

    <!-- Short aliases for domain classes -->
    <typeAlias alias="Person" type="Application\Model\Person" />
    <typeAlias alias="Account" type="Application\Model\Account" />

    <!-- Custom type handlers -->
    <typeHandler type="Application\Handler\BooleanHandler"
                 dbType="CHAR"
                 callbackType="boolean" />

    <!-- Transaction manager and data source -->
    <transactionManager type="PDO">
        <dataSource type="PDO"
                    driver="${db.driver}"
                    host="${db.host}"
                    dbname="${db.name}"
                    username="${db.user}"
                    password="${db.pass}" />
    </transactionManager>

    <!-- Cache models -->
    <cacheModel id="personCache" implementation="LRU" size="100">
        <flushInterval hours="24" />
        <flushOnExecute statement="updatePerson" />
        <flushOnExecute statement="deletePerson" />
    </cacheModel>

    <!-- SQL mapping files -->
    <sqlMaps>
        <sqlMap resource="maps/Person.xml" />
        <sqlMap resource="maps/Account.xml" />
    </sqlMaps>

</sqlMapConfig>
```

The `${db.driver}` tokens are resolved from the file loaded by `<properties resource="database.properties" />`. Each `sqlMap` `resource` path is relative to the config file's directory.

---

[SqlMap Docs](../README.md) -> Configuration

## See Also

- [transactionManager](transactionManager.md) — database connection and PDO configuration
- [sqlMap](sqlMap.md) — referencing SQL mapping files
- [cacheModel](cacheModel.md) — result caching with flush rules
- [TSqlMapConfig](../../src/Data/SqlMap/TSqlMapConfig.php) — the PRADO module that bootstraps SqlMap from this file
