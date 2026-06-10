[SqlMap Docs](../README.md) -> [Mappings](README.md) -> parameterMap

# parameterMap / parameter

A `parameterMap` declares an ordered list of PHP object properties and maps them, left to right, to positional `?` placeholders in a SQL statement. It is required for stored procedures that have output (`OUT`) or bidirectional (`INOUT`) parameters, and is the appropriate choice whenever precise control over DB type, null handling, or parameter mode is needed.

## Syntax

```xml
<parameterMap id="AccountParameterMap" class="Account">
    <parameter property="username"  dbType="VARCHAR"  />
    <parameter property="email"     dbType="VARCHAR"  nullValue=""    />
    <parameter property="balance"   dbType="DECIMAL"  type="float"    />
    <parameter property="active"    dbType="INTEGER"  nullValue="-1"  />
</parameterMap>

<insert id="insertAccount" parameterMap="AccountParameterMap">
    INSERT INTO accounts (username, email, balance, active)
    VALUES (?, ?, ?, ?)
</insert>
```

Each `<parameter>` child corresponds, in declaration order, to one `?` in the SQL string.

## Attributes — parameterMap

| Attribute | Required | Description |
|---|---|---|
| `id` | yes | Unique identifier within this mapping file. Referenced by the `parameterMap` attribute on statement tags. |
| `class` | no | Fully qualified PHP class name (or type alias) of the parameter object. When set, SqlMap validates that the supplied parameter is an instance of this class. Omit when passing an associative array or a generic object. |

## Attributes — parameter

| Attribute | Required | Default | Description |
|---|---|---|---|
| `property` | yes | — | Property name on the parameter object. SqlMap reads this value via [`TPropertyAccess`](../../src/Data/SqlMap/DataMapper/TPropertyAccess.php). |
| `column` | no | same as `property` | Database column name. Relevant for stored procedure output binding; has no effect on input-only parameters. |
| `dbType` | no | driver default | SQL type hint passed to the PDO driver, e.g. `VARCHAR`, `INTEGER`, `DECIMAL`, `DATE`. |
| `type` | no | — | PHP type used when reading the value back from an output parameter. Accepts `string`, `int`, `float`, `bool`. |
| `nullValue` | no | — | A PHP-side sentinel value that is substituted with SQL `NULL` on write and restored on read. See [nullValue](#nullvalue--mapping-php-sentinels-to-sql-null). |
| `typeHandler` | no | — | ID of a registered [`TSqlMapTypeHandler`](../../src/Data/SqlMap/DataMapper/TSqlMapTypeHandler.php) that performs custom serialization for this parameter. |
| `mode` | no | `IN` | Binding direction: `IN`, `OUT`, or `INOUT`. Required for stored procedure output parameters. |

## Order Matters — Left-to-Right Binding

SqlMap binds each `<parameter>` element to the corresponding `?` placeholder in the SQL string by position. The first `<parameter>` binds to the first `?`, the second to the second `?`, and so on.

```xml
<parameterMap id="OrderMap" class="Order">
    <parameter property="customerId" />  <!-- binds to first ?  -->
    <parameter property="total"      />  <!-- binds to second ? -->
    <parameter property="status"     />  <!-- binds to third ?  -->
</parameterMap>

<insert id="insertOrder" parameterMap="OrderMap">
    INSERT INTO orders (customer_id, total, status) VALUES (?, ?, ?)
</insert>
```

The number of `<parameter>` elements must equal the number of `?` placeholders. A mismatch causes a runtime exception.

## Output Parameters — mode=OUT / INOUT

When `mode="OUT"` or `mode="INOUT"`, SqlMap registers the parameter as an output binding with the PDO driver and, after statement execution, writes the returned value back to the corresponding property on the parameter object.

```xml
<parameterMap id="CalcTaxMap">
    <parameter property="subtotal"  mode="IN"    dbType="DECIMAL" />
    <parameter property="taxRate"   mode="IN"    dbType="DECIMAL" />
    <parameter property="taxAmount" mode="OUT"   dbType="DECIMAL" type="float" />
</parameterMap>

<procedure id="calculateTax" parameterMap="CalcTaxMap">
    { CALL sp_calc_tax(?, ?, ?) }
</procedure>
```

After `TSqlMapGateway::queryForObject('calculateTax', $order)` returns, `$order->taxAmount` holds the value written by the stored procedure.

The `type` attribute controls the PHP type used when writing the value back to the object property.

## nullValue — Mapping PHP Sentinels to SQL NULL

The `nullValue` attribute defines a PHP-side sentinel that SqlMap treats as SQL `NULL` on the way into the database, and restores when reading the value back out.

```xml
<parameter property="middleName" dbType="VARCHAR" nullValue="" />
<parameter property="score"      dbType="INTEGER" nullValue="-1" />
```

- An empty string `""` in `middleName` is sent as `NULL`.
- A score of `-1` is sent as `NULL`.

On retrieval (output parameters), a `NULL` from the database is converted back to the sentinel value.

## Inline Parameters vs parameterMap

Use inline parameters (e.g., `#property#`) for most `SELECT`, `INSERT`, `UPDATE`, and `DELETE` statements. They are concise and co-located with the SQL.

Use a `parameterMap` when:

- The statement calls a stored procedure with `OUT` or `INOUT` parameters.
- The SQL driver requires explicit `dbType` hints that differ per property.
- A custom `typeHandler` must be applied to specific parameters.
- The same parameter map is reused across multiple statements.

Full inline parameter syntax is documented in [inline-parameters.md](inline-parameters.md).

## Stored Procedure Example — Input and Output Parameters

The following example calls a stored procedure that accepts a user ID, looks up the user's account balance, and returns both the balance and a status code.

```xml
<parameterMap id="BalanceLookupMap" class="BalanceLookup">
    <parameter property="userId"     mode="IN"    dbType="INTEGER" />
    <parameter property="balance"    mode="OUT"   dbType="DECIMAL" type="float" />
    <parameter property="statusCode" mode="OUT"   dbType="INTEGER" type="int"   />
</parameterMap>

<procedure id="getAccountBalance" parameterMap="BalanceLookupMap">
    { CALL sp_get_balance(?, ?, ?) }
</procedure>
```

PHP usage:

```php
$lookup = new BalanceLookup();
$lookup->userId = 42;

$gateway->queryForObject('getAccountBalance', $lookup);

echo $lookup->balance;    // populated by the stored procedure
echo $lookup->statusCode; // populated by the stored procedure
```

The parameter object is passed by reference. After execution, `balance` and `statusCode` hold the values written by the stored procedure.

---

[SqlMap Docs](../README.md) -> [Mappings](README.md) -> parameterMap

## See also

- [inline-parameters.md](inline-parameters.md) — `#property#` and `$property$` syntax embedded directly in SQL strings
- [resultMap.md](resultMap.md) — mapping SQL result columns back to PHP object properties
- [statements.md](statements.md) — `select`, `insert`, `update`, `delete`, and `procedure` statement tags that reference `parameterMap`
