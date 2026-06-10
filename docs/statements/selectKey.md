[SqlMap Docs](../README.md) -> [Statements](README.md) -> selectKey

# selectKey

`<selectKey>` is a child element of [`<insert>`](insert.md) that retrieves a generated primary key and assigns it to a property on the parameter object. It runs either before (`type="pre"`) or after (`type="post"`) the `INSERT` statement, depending on the key-generation strategy the database uses.

## Syntax

### type="post" — auto-increment (MySQL)

Used when the database generates the key during the `INSERT`. The `<selectKey>` SQL runs after the insert and retrieves the last generated ID.

```xml
<insert id="InsertCategory" parameterClass="Category">
    <selectKey property="Id" type="post" resultClass="int">
        SELECT LAST_INSERT_ID() AS value
    </selectKey>
    INSERT INTO Categories (Category_Name, Category_Guid)
    VALUES (#Name#, #GuidString:Varchar#)
</insert>
```

### type="pre" — sequence (Oracle)

Used when the database provides a sequence whose next value must be fetched before the `INSERT`. The retrieved value is set on the parameter object and is available as an inline parameter (`#Id#`) inside the `INSERT` SQL.

```xml
<insert id="InsertLineItem" parameterClass="LineItem">
    <selectKey property="Id" type="pre" resultClass="int">
        SELECT categories_seq.NEXTVAL AS value FROM DUAL
    </selectKey>
    INSERT INTO LineItems
        (LineItem_ID, Order_ID, LineItem_Code, LineItem_Quantity, LineItem_Price)
    VALUES
        (#Id#, #Order.Id#, #Code#, #Quantity#, #Price#)
</insert>
```

## Attributes

| Attribute | Required | Default | Description |
|---|---|---|---|
| `property` | Yes | — | Property name on the parameter object that receives the generated key value. |
| `type` | No | `post` | Execution order relative to the `INSERT`. `pre` runs before; `post` runs after. |
| `resultClass` | No | — | PHP type or class to map the key value into (e.g., `int`, `string`). When omitted, the raw PDO value is used. |

All other attributes of a statement (`id`, `parameterClass`, `resultMap`, `cacheModel`, etc.) are inherited from [`TSqlMapStatement`](../../src/Data/SqlMap/Configuration/TSqlMapSelectKey.php) but are not applicable to `<selectKey>`. The `extends` attribute is explicitly unsupported and throws a configuration exception.

## type="pre" — sequence-based keys

With `type="pre"`, the execution order is:

1. The `<selectKey>` SQL executes and returns the next sequence value.
2. The value is assigned to the parameter object property named by `property`.
3. The parent `INSERT` SQL executes. Because the key is now on the parameter object, it is available as an inline parameter reference (e.g., `#Id#`) inside the `INSERT`.

This is the correct pattern for Oracle sequences, Firebird generators, and any other database where the key must be known before the row is written.

## type="post" — auto-increment keys

With `type="post"`, the execution order is:

1. The parent `INSERT` SQL executes. The database generates the key automatically.
2. The `<selectKey>` SQL executes and retrieves the generated key (e.g., via `LAST_INSERT_ID()`, `lastval()`, or `last_insert_rowid()`).
3. The value is assigned to the parameter object property named by `property`.

The key value is not available as an inline parameter inside the `INSERT` SQL because it does not exist at that point.

## Database-specific examples

| Database | `type` | `<selectKey>` SQL |
|---|---|---|
| MySQL | `post` | `SELECT LAST_INSERT_ID() AS value` |
| PostgreSQL | `post` | `SELECT lastval() AS value` |
| SQLite | `post` | `SELECT last_insert_rowid() AS value` |
| Oracle | `pre` | `SELECT my_seq.NEXTVAL AS value FROM DUAL` |
| Oracle (post) | `post` | `SELECT my_seq.CURRVAL AS value FROM DUAL` |
| SQL Server | `post` | `SELECT SCOPE_IDENTITY() AS value` |
| Firebird | `post` | `SELECT GEN_ID(my_gen, 0) AS value FROM RDB$DATABASE` |

> For Firebird and Oracle `CURRVAL`/`GEN_ID(..., 0)`, the sequence must have already been incremented by the `INSERT` trigger or procedure. Using `NEXTVAL` / `GEN_ID(..., 1)` with `type="pre"` is safer and more portable for those engines.

## Return value

[`TSqlMapGateway::insert()`](../api/TSqlMapGateway.md) returns the key value produced by `<selectKey>`. If no `<selectKey>` is defined on the `<insert>`, `insert()` returns `null`.

```php
$category = new Category();
$category->Name = 'Widgets';

$id = $gateway->insert('InsertCategory', $category);
// $id is the generated primary key value
// $category->Id is also set to the same value
```

---

[SqlMap Docs](../README.md) -> [Statements](README.md) -> selectKey

## See also

- [insert](insert.md) — the parent element that contains `<selectKey>`
- [Inline Parameters](../configuration/inline-parameters.md) — `#property#` syntax used to reference pre-generated keys inside INSERT SQL
- [TSqlMapGateway](../api/TSqlMapGateway.md) — `insert()` return value and gateway API
