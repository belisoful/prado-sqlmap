[SqlMap Docs](../README.md) -> [Configuration](README.md) -> sqlMap (file reference)

# sqlMap (file reference)

The `sqlMap` element serves two distinct roles:

1. **File reference** — a `<sqlMap>` child of `<sqlMaps>` inside [`sqlMapConfig`](README.md), pointing to a mapping file.
2. **Mapping file root** — the root element of every mapping file, wrapping all statement, result map, parameter map, and cache model declarations.

Both roles are covered on this page.

---

## File Reference Syntax

Inside the config file, one or more `<sqlMap>` elements appear inside a `<sqlMaps>` wrapper:

```xml
<sqlMapConfig>

    <transactionManager type="PDO">
        <dataSource type="PDO" driver="sqlite" host=":memory:" />
    </transactionManager>

    <sqlMaps>
        <sqlMap resource="maps/Account.xml" />
        <sqlMap resource="maps/Order.xml" />
        <sqlMap resource="maps/LineItem.xml" />
    </sqlMaps>

</sqlMapConfig>
```

Each `<sqlMap>` instructs [`TSqlMapXmlConfiguration`](../../src/Data/SqlMap/Configuration/TSqlMapXmlConfiguration.php) to parse that file and register its statements, result maps, parameter maps, and cache models into the shared [`TSqlMapManager`](../../src/Data/SqlMap/TSqlMapManager.php).

### Attributes

| Attribute  | Required | Description |
|------------|----------|-------------|
| `resource` | Yes      | Path to the mapping XML file. Resolved relative to the config file's directory. `${token}` substitutions from [`properties`](properties.md) are applied before path resolution. |

There is no `url` or `class` variant; only `resource` (filesystem path) is supported.

---

## Loading Order

Mapping files are loaded in the order they appear in `<sqlMaps>`. This matters for:

- **`extends` on result maps and parameter maps** — a result map that extends another must be loaded after the parent, or the parent must appear earlier in the same file. When an `extends` target is not yet registered, the parser searches the current document before raising an error; cross-file `extends` requires the parent file to be listed first.
- **Statement uniqueness** — statement IDs and result map IDs are registered globally. A duplicate ID in a later file raises [`TSqlMapDuplicateException`](../../src/Data/SqlMap/DataMapper/TSqlMapDuplicateException.php) unless the files use distinct identifiers (see [namespace](#namespace) below).

---

## The sqlMap Root Element

Every mapping file starts with `<sqlMap>` as its root:

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<sqlMap namespace="Account">

    <alias>
        <typeAlias alias="Account" type="App\Domain\Account" />
    </alias>

    <typeHandlers>
        <typeHandler type="App\Handler\StatusHandler" dbType="CHAR" callbackType="string" />
    </typeHandlers>

    <resultMap id="AccountResult" class="Account">
        <result property="id"       column="account_id" />
        <result property="username" column="username" />
    </resultMap>

    <parameterMap id="AccountInsert" class="Account">
        <parameter property="username" dbType="VARCHAR" />
    </parameterMap>

    <select id="getById" parameterClass="int" resultMap="AccountResult">
        SELECT account_id, username FROM accounts WHERE account_id = #value#
    </select>

    <cacheModel id="accountCache" implementation="LRU" size="50">
        <flushInterval hours="1" />
    </cacheModel>

</sqlMap>
```

### Root Element Attributes

| Attribute   | Required | Description |
|-------------|----------|-------------|
| `namespace` | No       | Logical name for this mapping file. Used as a qualifier prefix when referencing statements across files (see [namespace](#namespace) below). |

### Child Sections

| Element | Doc page | Purpose |
|---------|----------|---------|
| `alias` / `typeAlias` | [typeAlias.md](typeAlias.md) | Short aliases for fully-qualified PHP class names, scoped to this file. |
| `typeHandlers` / `typeHandler` | [typeHandler.md](typeHandler.md) | Custom type-conversion handlers, scoped to this file. |
| `resultMap` | [resultMap.md](../mappings/resultMap.md) | Maps result-set columns to PHP object properties. |
| `parameterMap` | [parameterMap.md](../mappings/parameterMap.md) | Maps PHP object properties to ordered `?` SQL parameters. |
| `select` | [select.md](../statements/select.md) | SQL `SELECT` statement. |
| `insert` | [insert.md](../statements/insert.md) | SQL `INSERT` statement, with optional `selectKey` for generated keys. |
| `update` | [update.md](../statements/update.md) | SQL `UPDATE` statement. |
| `delete` | [delete.md](../statements/delete.md) | SQL `DELETE` statement. |
| `statement` | [statement.md](../statements/statement.md) | Generic SQL statement for any DML or DDL not covered by the specific types. |
| `procedure` | [procedure.md](../statements/procedure.md) | Stored procedure call. |
| `cacheModel` | [cacheModel.md](cacheModel.md) | Named cache configuration referenced by `cacheModel` on select statements. |

---

## namespace

The `namespace` attribute assigns a logical name to the mapping file. Its purpose is to qualify statement and result-map IDs when making cross-file references, avoiding ID collisions across large configurations.

When `namespace` is set, the convention is to prefix every `id` in that file with `Namespace.`:

```xml
<!-- LineItem.xml -->
<sqlMap namespace="LineItem">

    <select id="LineItem.GetLineItemsForOrder" parameterClass="int" resultMap="LineItem">
        SELECT * FROM line_items WHERE order_id = #value#
    </select>

</sqlMap>
```

Another file then references the fully-qualified ID:

```xml
<!-- Order.xml -->
<sqlMap namespace="Order">

    <resultMap id="OrderWithLines" class="Order" extends="lite-order-result">
        <result property="lineItems" column="order_id"
                select="LineItem.GetLineItemsForOrder" type="TList" />
    </resultMap>

</sqlMap>
```

The `namespace` attribute itself is **not** processed by the engine — it does not automatically prepend the namespace to registered IDs. The prefixed form (`Namespace.id`) must appear in the `id` attribute of each statement or result map declaration. The namespace value serves as documentation and as a naming convention to keep global IDs unique.

When `namespace` is omitted, statement IDs must be unique across all loaded mapping files without qualification.

---

[SqlMap Docs](../README.md) -> [Configuration](README.md) -> sqlMap (file reference)

## See Also

- [sqlMapConfig](README.md) — top-level configuration file; the `<sqlMaps>` wrapper lives here
- [resultMap](../mappings/resultMap.md) — cross-file result map references and `extends`
- [cacheModel](cacheModel.md) — cache model declarations inside mapping files
- [typeAlias](typeAlias.md) — file-scoped type aliases inside `<alias>`
- [TSqlMapXmlConfiguration](../../src/Data/SqlMap/Configuration/TSqlMapXmlConfiguration.php) — parses config and dispatches mapping file loading
- [TSqlMapManager](../../src/Data/SqlMap/TSqlMapManager.php) — holds all registered statements, result maps, and parameter maps
