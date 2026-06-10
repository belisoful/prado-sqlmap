[SqlMap Docs](../README.md) -> [Mappings](README.md) -> Inline Parameters

# Inline Parameters

Inline parameters embed parameter bindings directly in SQL statement text. They are an alternative to a separate [`parameterMap`](parameter-map.md) element.

---

## `#property#` Syntax

The `#property#` form creates a PDO bound parameter. The value is passed as a prepared-statement placeholder, never interpolated into the SQL string. This form is safe from SQL injection.

| Form | Purpose | Example |
|---|---|---|
| `#property#` | Basic binding | `#EmailAddress#` |
| `#property:dbType#` | Type hint for the PDO column type | `#Email:VarChar#` |
| `#property:dbType:nullValue#` | Substitute `nullValue` when the property is `null` | `#Email:VarChar:no_email@provided.com#` |

**`dbType`** names correspond to the type strings recognised by [`TSqlMapTypeHandlerRegistry`](../../src/Data/SqlMap/DataMapper/TSqlMapTypeHandlerRegistry.php) and the underlying PDO driver (e.g., `VarChar`, `Integer`, `Date`).

**`nullValue`** is a sentinel: when the property equals this value at runtime, `null` is bound instead.

```xml
<select id="GetAccountByEmail" parameterClass="Account" resultClass="Account">
    SELECT * FROM accounts
    WHERE email_address = #EmailAddress:VarChar:no_email@provided.com#
</select>
```

---

## Accessing Nested Properties

Dot notation traverses object graphs. Each segment resolves a getter on the preceding object.

```xml
<!-- Object graph -->
#Account.Address.City#

<!-- Array or map key access -->
#obj.Map.Id#
```

The [`TPropertyAccess`](../../src/Data/SqlMap/DataMapper/TPropertyAccess.php) helper resolves each segment in turn, supporting both public properties and `get*()` methods.

---

## `$property$` Syntax

The `$property$` form (dollar signs surrounding the property name) performs raw string substitution into the SQL text before the statement is prepared. The resolved value is inserted literally — it is **not** a bound parameter.

**SQL injection warning:** never use `$property$` with untrusted user input. Use this form only for SQL fragments that cannot be parameterised, such as column names, table names, `ORDER BY` expressions, or `LIKE` pattern prefixes.

```xml
<select id="GetAccountsSorted" parameterClass="map" resultClass="Account">
    SELECT * FROM accounts
    ORDER BY $sortColumn$
</select>
```

---

## `${property}` Syntax

`${property}` (dollar, brace, property name, closing brace) is identical to `$property$`. Both forms perform the same raw string substitution and carry the same SQL injection risk.

```xml
<select id="SearchAccounts" parameterClass="map" resultClass="Account">
    SELECT * FROM accounts
    WHERE last_name LIKE '${namePart}%'
</select>
```

---

## Escaping

| Literal character | Escape sequence |
|---|---|
| `#` | `##` (two consecutive hash marks) |
| `$` | `$$` (two consecutive dollar signs) |

Use these when the SQL itself must contain a hash or dollar character that is not a parameter token.

---

## Scalar Parameters

When `parameterClass` is a scalar type (`string`, `int`, `float`, `bool`), use `#value#` to reference the parameter itself.

```xml
<select id="GetAccountById" parameterClass="int" resultClass="Account">
    SELECT * FROM accounts WHERE account_id = #value#
</select>
```

The token `value` is a reserved keyword for scalar parameter classes. Any other property name resolves against the scalar via [`TPropertyAccess`](../../src/Data/SqlMap/DataMapper/TPropertyAccess.php), which returns the scalar itself when the property name is `value`.

---

## Comparison with `parameterMap`

| Situation | Recommendation |
|---|---|
| Few parameters, straightforward types | Inline `#property#` is sufficient |
| Need `nullValue`, `dbType`, or a type handler per property | Both inline (extended form) and [`parameterMap`](parameter-map.md) work; inline is more compact |
| Stored procedure with `OUT`/`INOUT` parameters | [`parameterMap`](parameter-map.md) is required; inline cannot express direction |
| Many parameters shared across multiple statements | [`parameterMap`](parameter-map.md) avoids duplication |
| Raw substitution (`ORDER BY`, table name) | `$property$` inline only; no `parameterMap` equivalent |

---

## Examples

| Scenario | SQL fragment | Notes |
|---|---|---|
| Scalar `int` parameter | `WHERE id = #value#` | `parameterClass="int"` |
| Object property | `WHERE email = #EmailAddress#` | Resolves `getEmailAddress()` |
| Nested object | `WHERE city = #Address.City#` | Dot-traversal via `TPropertyAccess` |
| Array/map key | `WHERE id = #map.AccountId#` | Key lookup on array parameter |
| Raw `ORDER BY` | `ORDER BY $sortColumn$` | Not bound; injection risk if user-supplied |
| Raw `LIKE` prefix | `WHERE name LIKE '${prefix}%'` | Brace form; same behaviour as dollar form |
| Null substitution | `#Email:VarChar:no_email@provided.com#` | Binds `null` when value equals sentinel |

---

[SqlMap Docs](../README.md) -> [Mappings](README.md) -> Inline Parameters

## See Also

- [Parameter Map](parameter-map.md) — explicit `parameterMap` element reference
- [select](select.md) — `<select>` statement tag where inline parameters appear
- [`TPropertyAccess`](../../src/Data/SqlMap/DataMapper/TPropertyAccess.php) — runtime property resolution for dot-notation
- [Dynamic SQL](dynamic-sql.md) — `<dynamic>` and conditional tags that wrap inline parameters
