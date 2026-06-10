[SqlMap Docs](../README.md) -> Getting Started

# Getting Started with prado-sqlmap

This guide walks through installing the extension, wiring it into a PRADO application, and executing your first mapped SQL statements.

---

## 1. Install via Composer

```bash
composer require prado/prado4-sqlmap
```

The extension registers [`TSqlMapConfig`](../src/Data/SqlMap/TSqlMapConfig.php) as a bootstrap module automatically via the `extra.bootstrap` entry in its `composer.json`.

---

## 2. Register TSqlMapConfig in application.xml

Add a `<module>` entry to your application's `application.xml`. At minimum, supply a `ConnectionID` pointing to an existing `TDataSourceConfig` module, and a `ConfigFile` path to your SqlMap configuration file.

```xml
<application id="MyApp">
  <modules>

    <!-- Database connection module -->
    <module
      id="db"
      class="Prado\Data\TDataSourceConfig"
      ConnectionString="sqlite:/path/to/myapp.db"
    />

    <!-- SqlMap module -->
    <module
      id="sqlmap"
      class="Prado\Data\SqlMap\TSqlMapConfig"
      ConnectionID="db"
      ConfigFile="Application.SqlMap.sqlmap-config"
    />

  </modules>
</application>
```

`ConfigFile` accepts a dot-notation PRADO path or a filesystem path to the XML file created in step 3.

---

## 3. Create the SqlMap config file

Create `protected/SqlMap/sqlmap-config.xml`. This file declares global settings and references one or more mapping files via [`sqlMaps`](configuration/README.md).

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sqlMapConfig>

  <sqlMaps>
    <sqlMap resource="Application.SqlMap.maps.Person" />
  </sqlMaps>

</sqlMapConfig>
```

The `resource` attribute uses the same dot-notation PRADO path as `ConfigFile`. A plain relative or absolute filesystem path is also accepted.

---

## 4. Write a mapping file

Create `protected/SqlMap/maps/Person.xml`. This file maps a `SELECT` statement to a PHP class using [`resultClass`](mappings/resultMap.md) and an inline [`#id#`](mappings/inline-params.md) parameter.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sqlMap namespace="Person">

  <select id="getPerson" parameterClass="int" resultClass="App\Model\Person">
    SELECT id, first_name AS firstName, last_name AS lastName, email
    FROM   person
    WHERE  id = #id#
  </select>

  <select id="getAllPeople" resultClass="App\Model\Person">
    SELECT id, first_name AS firstName, last_name AS lastName, email
    FROM   person
    ORDER  BY last_name
  </select>

</sqlMap>
```

`resultClass` names a PHP class whose public properties (or getter/setter pairs) match the column aliases returned by the query. The `#id#` token binds the parameter value as a PDO prepared-statement placeholder.

A minimal model class:

```php
<?php
namespace App\Model;

class Person
{
    public int    $id;
    public string $firstName;
    public string $lastName;
    public string $email;
}
```

---

## 5. Call the gateway in PHP

Retrieve the [`TSqlMapConfig`](../src/Data/SqlMap/TSqlMapConfig.php) module, obtain the [`TSqlMapGateway`](../src/Data/SqlMap/TSqlMapGateway.php), and call [`queryForObject`](statements/select.md).

```php
<?php
use Prado\Prado;

/** @var \Prado\Data\SqlMap\TSqlMapConfig $module */
$module  = Prado::getApplication()->getModule('sqlmap');
$gateway = $module->getSqlMapManager()->getSqlMapGateway();

// Fetch a single Person by primary key
/** @var \App\Model\Person|null $person */
$person = $gateway->queryForObject('Person.getPerson', 42);

if ($person !== null) {
    echo $person->firstName . ' ' . $person->lastName;
}

// Fetch all people as an array
/** @var \App\Model\Person[] $people */
$people = $gateway->queryForList('Person.getAllPeople');
```

Statement IDs are prefixed with the `namespace` declared in the mapping file (`Person.getPerson`). When no namespace is set the bare `id` is used.

---

## 6. Insert with a generated key

Use [`selectKey`](statements/insert.md) inside an [`insert`](statements/insert.md) statement to retrieve the auto-generated primary key after the row is written.

```xml
<insert id="insertPerson" parameterClass="App\Model\Person">

  <selectKey resultClass="int" type="post" keyProperty="id">
    SELECT last_insert_rowid()   <!-- SQLite; use LAST_INSERT_ID() for MySQL -->
  </selectKey>

  INSERT INTO person (first_name, last_name, email)
  VALUES (#firstName#, #lastName#, #email#)

</insert>
```

```php
<?php
$person            = new \App\Model\Person();
$person->firstName = 'Jane';
$person->lastName  = 'Smith';
$person->email     = 'jane@example.com';

// insert() returns the generated key and also sets $person->id when keyProperty is set
$newId = $gateway->insert('Person.insertPerson', $person);

echo 'Inserted with id ' . $newId;
```

`type="post"` means the `selectKey` query runs after the `INSERT`. Use `type="pre"` for sequence-based databases (Oracle, Firebird) where the key is fetched before the insert.

---

## Next Steps

- [Integration guide](integration.md) — module wiring, caching, multiple data sources
- [select statement reference](statements/select.md) — `resultMap`, `parameterMap`, `cacheModel`, paged lists
- [resultMap reference](mappings/resultMap.md) — explicit column-to-property mapping, discriminators, lazy loading
- [Dynamic SQL](dynamic-sql/README.md) — conditional clauses (`isNotNull`, `isNotEmpty`, `iterate`, and more)

---

[SqlMap Docs](../README.md) -> Getting Started

## See also

- [Configuration reference](configuration/README.md) — `sqlMapConfig`, `typeAlias`, `typeHandler`, `cacheModel`
- [statements/select.md](statements/select.md) — full `select` tag attribute reference
- [mappings/resultMap.md](mappings/resultMap.md) — `resultMap`, `result`, `discriminator`, `subMap`
