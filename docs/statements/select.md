[SqlMap Docs](../README.md) -> [Statements](README.md) -> select

# select

A `<select>` element maps a SQL `SELECT` statement to a named identifier. It returns one or more result objects populated from the result set. Input parameters, result mapping, caching, and inheritance from a base statement are all configured through attributes.

---

## Syntax

```xml
<cacheModel id="account-cache" implementation="LRU">
    <flushInterval hours="24"/>
    <flushOnExecute statement="updateAccount"/>
    <property name="size" value="100"/>
</cacheModel>

<resultMap id="account-result" class="Account">
    <result property="id"           column="Account_Id"/>
    <result property="firstName"    column="Account_FirstName"/>
    <result property="lastName"     column="Account_LastName"/>
    <result property="emailAddress" column="Account_Email" nullValue="no_email@provided.com"/>
</resultMap>

<select id="getAccountById"
        parameterClass="int"
        resultMap="account-result"
        cacheModel="account-cache">
    SELECT Account_Id, Account_FirstName, Account_LastName, Account_Email
    FROM Accounts
    WHERE Account_Id = #value#
</select>
```

The body is the SQL text to execute. [Inline parameters](../dynamic-sql/inline-parameters.md) (`#property#`) and [dynamic SQL](../dynamic-sql/README.md) blocks are both supported.

---

## Attributes

| Attribute | Required | Description |
|---|---|---|
| `id` | Yes | Unique identifier for the statement within its namespace. Passed to [`TSqlMapGateway`](../../src/Data/SqlMap/TSqlMapGateway.php) query methods as the first argument. |
| `parameterClass` | No | PHP class name or primitive alias (`int`, `string`, `array`, `Hashtable`) of the input parameter. Omit when no parameters are required. |
| `parameterMap` | No | References a named [`<parameterMap>`](../mappings/parameterMap.md) for explicit column-to-property bindings. Mutually exclusive with `parameterClass` for inline parameters. |
| `resultClass` | No | PHP class name or primitive alias to use for automatic result mapping. Column names or aliases in the SQL must match property names on the class. |
| `resultMap` | No | References a named [`<resultMap>`](../mappings/resultMap.md) that defines explicit column-to-property bindings, type handlers, and lazy-load relationships. |
| `listClass` | No | PHP class name implementing `\ArrayAccess` to use as the result collection. Defaults to `TList` when omitted. |
| `cacheModel` | No | References a named [`<cacheModel>`](../configuration/cacheModel.md). Results are served from cache on subsequent calls until the cache is flushed. |
| `extends` | No | The `id` of another `<select>` (or `<statement>`) whose SQL body is prepended to this element's SQL body. Used to compose WHERE and ORDER BY clauses on a shared base query. |
| `remapResults` | No | `true` \| `false`. When `true`, the result map is re-evaluated on every call. Useful when the result set structure can vary between calls (e.g., with dynamic column selection). Defaults to `false`. |

---

## Gateway methods

All query methods are on [`TSqlMapGateway`](../../src/Data/SqlMap/TSqlMapGateway.php). Obtain the gateway from the configured [`TSqlMapConfig`](../../src/Data/SqlMap/TSqlMapConfig.php) module:

```php
/** @var \Prado\Data\SqlMap\TSqlMapGateway $gateway */
$gateway = $this->getModule('sqlmap')->getClient();
```

### queryForObject

```php
public function queryForObject(
    string $statementName,
    mixed  $parameter = null,
    mixed  $result    = null
): object|null
```

Executes the statement and returns a single populated object. Returns `null` when no row matches.

| Parameter | Type | Description |
|---|---|---|
| `$statementName` | `string` | The `id` of the `<select>` statement. |
| `$parameter` | `mixed` | Input parameter object, array, or scalar. |
| `$result` | `mixed` | An existing object to populate instead of creating a new one. |

### queryForList

```php
public function queryForList(
    string $statementName,
    mixed  $parameter = null,
    ?TList $result    = null,
    int    $skip      = -1,
    int    $max       = -1
): TList
```

Executes the statement and returns a `TList` (or the `listClass` collection) of result objects.

| Parameter | Type | Description |
|---|---|---|
| `$statementName` | `string` | The `id` of the `<select>` statement. |
| `$parameter` | `mixed` | Input parameter object, array, or scalar. |
| `$result` | `TList\|null` | An existing list to populate. Pass `null` to create a new one. |
| `$skip` | `int` | Number of leading rows to skip. `-1` means no skip. |
| `$max` | `int` | Maximum number of rows to return. `-1` means no limit. |

### queryForMap

```php
public function queryForMap(
    string  $statementName,
    mixed   $parameter     = null,
    ?string $keyProperty   = null,
    ?string $valueProperty = null,
    int     $skip          = -1,
    int     $max           = -1
): TMap
```

Executes the statement and returns a `TMap` keyed by the value of `$keyProperty` on each result object. When `$valueProperty` is `null`, the entire result object is stored as the value.

| Parameter | Type | Description |
|---|---|---|
| `$statementName` | `string` | The `id` of the `<select>` statement. |
| `$parameter` | `mixed` | Input parameter object, array, or scalar. |
| `$keyProperty` | `string\|null` | Property name on the result object used as the map key. |
| `$valueProperty` | `string\|null` | Property name used as the map value. `null` stores the full object. |
| `$skip` | `int` | Number of leading rows to skip. `-1` means no skip. |
| `$max` | `int` | Maximum number of rows to return. `-1` means no limit. |

### queryForPagedList

```php
public function queryForPagedList(
    string $statementName,
    mixed  $parameter = null,
    int    $pageSize  = 10,
    int    $page      = 0
): TSqlMapPagedList
```

Executes the statement and returns a [`TSqlMapPagedList`](../../src/Data/SqlMap/DataMapper/TSqlMapPagedList.php) that pages through results on demand.

| Parameter | Type | Description |
|---|---|---|
| `$statementName` | `string` | The `id` of the `<select>` statement. |
| `$parameter` | `mixed` | Input parameter object, array, or scalar. |
| `$pageSize` | `int` | Maximum number of objects per page. |
| `$page` | `int` | Zero-based page index to load initially. |

---

## resultClass vs resultMap

### resultClass

`resultClass` tells the framework to map columns to object properties automatically. Column names (or SQL aliases) are matched case-insensitively to property names on the target class. No separate `<resultMap>` element is needed.

Use `resultClass` when:
- The SQL uses column aliases that match property names.
- No type handlers, `nullValue` defaults, lazy loading, or joined sub-objects are needed.
- Rapid iteration matters and the schema is stable.

```xml
<select id="getAccountViaResultClass"
        parameterClass="int"
        resultClass="Account">
    SELECT
        Account_Id        AS Id,
        Account_FirstName AS FirstName,
        Account_LastName  AS LastName,
        Account_Email     AS EmailAddress
    FROM Accounts
    WHERE Account_Id = #value#
</select>
```

### resultMap

`resultMap` references a `<resultMap>` element that explicitly binds each column to a property and can specify `dbType`, `nullValue`, `typeHandler`, lazy-loaded sub-selects, and discriminator-based polymorphism.

Use `resultMap` when:
- Column names do not match property names and aliasing is impractical.
- `nullValue` substitution, custom `typeHandler`, or `dbType` coercion is needed.
- The result includes related objects loaded via a sub-select or join.

```xml
<resultMap id="account-result" class="Account">
    <result property="id"           column="Account_Id"/>
    <result property="emailAddress" column="Account_Email"
            nullValue="no_email@provided.com"/>
</resultMap>

<select id="getAccountViaResultMap"
        parameterClass="int"
        resultMap="account-result">
    SELECT * FROM Accounts WHERE Account_Id = #value#
</select>
```

`resultClass` and `resultMap` are mutually exclusive. When both are set, `resultMap` takes precedence.

---

## Caching

Assign a `cacheModel` attribute to serve repeated queries from an in-memory cache rather than the database.

```xml
<cacheModel id="account-cache" implementation="LRU">
    <flushInterval hours="24"/>
    <flushOnExecute statement="updateAccount"/>
    <flushOnExecute statement="deleteAccount"/>
    <property name="size" value="100"/>
</cacheModel>

<select id="getCachedAccounts"
        resultMap="account-result"
        cacheModel="account-cache">
    SELECT * FROM Accounts ORDER BY Account_Id
</select>
```

On the first call, results are fetched from the database and stored in the cache under a key derived from the statement id and serialized parameter. Subsequent calls with the same key return the cached value. The cache is invalidated when:

- The `<flushInterval>` duration elapses.
- Any statement listed in a `<flushOnExecute>` element executes successfully.
- [`TSqlMapGateway::flushCaches()`](../../src/Data/SqlMap/TSqlMapGateway.php) is called explicitly.

See [`<cacheModel>`](../configuration/cacheModel.md) for available `implementation` values (`LRU`, `FIFO`, `MEMORY`, `APPLICATION`) and all child element options.

---

## extends

The `extends` attribute prepends the SQL body of the referenced statement to the SQL body of the current statement. This allows a single base query to be shared, with child statements adding clauses such as `WHERE` or `ORDER BY`.

```xml
<!-- Base query: selects all columns -->
<select id="getAllAccounts"
        resultMap="indexed-account-result">
    SELECT Account_Id, Account_FirstName, Account_LastName, Account_Email
    FROM Accounts
</select>

<!-- ORDER BY variant: appends an ORDER BY clause -->
<select id="getAllAccountsOrderByName"
        extends="getAllAccounts"
        resultMap="indexed-account-result">
    ORDER BY Account_FirstName
</select>

<!-- WHERE variant: appends a WHERE clause -->
<select id="getOneAccount"
        extends="getAllAccounts"
        resultMap="indexed-account-result">
    WHERE Account_Id = #value#
</select>
```

The child statement inherits the full SQL of the parent. Any attribute set on the child (`resultMap`, `parameterClass`, etc.) overrides the inherited value from the parent.

---

## listClass

By default, `queryForList` returns a PRADO `TList`. Set `listClass` to any PHP class that implements `\ArrayAccess` to use a different collection type.

| `listClass` value | Behavior |
|---|---|
| Omitted | Returns a `TList` instance. |
| `TList` | Explicit `TList`; identical to omitting the attribute. |
| `array` | Returns a native PHP array. |
| Custom class | Instantiates the named class and appends each result object to it. |

```xml
<select id="getAllAccountsAsTList"
        resultClass="TList">
    SELECT Account_Id AS Id, Account_FirstName AS FirstName
    FROM Accounts
    ORDER BY Account_Id
</select>
```

```xml
<select id="getAllAccountsAsCustomList"
        resultMap="account-result"
        listClass="MyAccountCollection">
    SELECT * FROM Accounts ORDER BY Account_Id
</select>
```

`queryForPagedList` always returns a [`TSqlMapPagedList`](../../src/Data/SqlMap/DataMapper/TSqlMapPagedList.php) regardless of `listClass`.

---

## Examples

### Scalar result

Returns a single scalar value using a primitive `resultClass`.

```xml
<select id="getAccountCount"
        resultClass="int">
    SELECT COUNT(*) FROM Accounts
</select>
```

```php
$count = $gateway->queryForObject('getAccountCount');
// $count === 5
```

### Single object result

Returns one populated domain object or `null`.

```xml
<resultMap id="account-result" class="Account">
    <result property="id"           column="Account_Id"/>
    <result property="firstName"    column="Account_FirstName"/>
    <result property="lastName"     column="Account_LastName"/>
    <result property="emailAddress" column="Account_Email"
            nullValue="no_email@provided.com"/>
</resultMap>

<select id="getAccountById"
        parameterClass="int"
        resultMap="account-result">
    SELECT * FROM Accounts WHERE Account_Id = #value#
</select>
```

```php
/** @var Account|null $account */
$account = $gateway->queryForObject('getAccountById', 42);
```

### List result

Returns all rows as a `TList` of objects, with optional in-memory offset and limit.

```xml
<select id="getAccountsByLastName"
        parameterClass="string"
        resultMap="account-result">
    SELECT * FROM Accounts
    WHERE Account_LastName = #value#
    ORDER BY Account_FirstName
</select>
```

```php
// All matching rows
$accounts = $gateway->queryForList('getAccountsByLastName', 'Smith');

// First 10 rows only
$page = $gateway->queryForList('getAccountsByLastName', 'Smith', null, 0, 10);
```

---

[SqlMap Docs](../README.md) -> [Statements](README.md) -> select

## See also

- [`TSqlMapGateway`](../../src/Data/SqlMap/TSqlMapGateway.php) — public API facade; hosts all query methods.
- [resultMap](../mappings/resultMap.md) — explicit column-to-property binding with type handlers and lazy loading.
- [parameterMap](../mappings/parameterMap.md) — explicit parameter binding with `dbType` and `nullValue` control.
- [cacheModel](../configuration/cacheModel.md) — cache configuration, `<flushInterval>`, and `<flushOnExecute>` invalidation.
- [inline parameters](../dynamic-sql/inline-parameters.md) — `#property#` and `#property:dbType:nullValue#` syntax.
- [dynamic SQL](../dynamic-sql/README.md) — conditional SQL blocks (`<dynamic>`, `<isNotNull>`, `<iterate>`, etc.).
- [insert](insert.md) — DML statement for inserting rows, with optional generated-key retrieval.
