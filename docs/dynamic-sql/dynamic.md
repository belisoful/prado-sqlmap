[SqlMap Docs](../README.md) -> [Dynamic SQL](README.md) -> dynamic

# dynamic

The `dynamic` element is a container for conditional child tags inside a statement body. It emits its `prepend` text only when at least one child element contributes SQL to the output. When every child is suppressed, the entire `dynamic` block — including its `prepend` — is omitted from the final SQL.

---

## Syntax

```xml
<select id="searchProducts" parameterClass="Application\Model\ProductSearch"
        resultClass="Application\Model\Product">
    SELECT id, name, price, category_id, active
    FROM product
    <dynamic prepend="WHERE">
        <isNotEmpty prepend="AND" property="name">
            name LIKE #name#
        </isNotEmpty>
        <isNotEmpty prepend="AND" property="categoryId">
            category_id = #categoryId#
        </isNotEmpty>
        <isNotEmpty prepend="AND" property="minPrice">
            price &gt;= #minPrice#
        </isNotEmpty>
    </dynamic>
    ORDER BY name
</select>
```

When all three properties are empty the `WHERE` keyword does not appear. When one or more properties are non-empty, `WHERE` is prepended once before the first contributing child.

---

## Attributes

| Attribute | Required | Description |
|---|---|---|
| `prepend` | No | SQL text prepended before the first contributing child's output. Omitted entirely when no child contributes. Common values: `WHERE`, `SET`, `AND`, `OR`. |

`dynamic` has no `property`, `compareValue`, or other conditional attributes. Conditionality belongs to the child tags ([`isNotNull`](README.md#null-checks), [`isEqual`](README.md#comparisons), [`iterate`](README.md#iterate), etc.).

---

## Prepend Suppression

Each conditional child tag carries its own `prepend` attribute (typically `AND` or `OR`). The `dynamic` element suppresses the `prepend` of the **first** child that actually contributes SQL, replacing it with the `dynamic` element's own `prepend`. This prevents double keywords such as `WHERE AND name LIKE ?`.

Example of how prepend suppression works:

```xml
<dynamic prepend="WHERE">
    <isNotEmpty prepend="AND" property="status">
        status = #status#
    </isNotEmpty>
    <isNotEmpty prepend="AND" property="ownerId">
        owner_id = #ownerId#
    </isNotEmpty>
</dynamic>
```

Scenario — only `ownerId` is non-empty:

| Stage | SQL fragment |
|---|---|
| Raw child output | `AND owner_id = ?` |
| First-child suppression | `AND` is stripped; `WHERE` from `dynamic` takes its place |
| Final fragment | `WHERE owner_id = ?` |

Scenario — both properties are non-empty:

| Stage | SQL fragment |
|---|---|
| First child (status) | `AND` stripped; replaced by `WHERE` → `WHERE status = ?` |
| Second child (ownerId) | `AND` retained → `AND owner_id = ?` |
| Final fragment | `WHERE status = ? AND owner_id = ?` |

Scenario — neither property is non-empty:

The `dynamic` block produces nothing; `WHERE` is not emitted.

---

## Nested Dynamic Blocks

A statement body can contain multiple `dynamic` elements. Each manages its own prepend scope independently.

```xml
<update id="updateOrder" parameterClass="Application\Model\Order">
    UPDATE orders
    <dynamic prepend="SET">
        <isNotEmpty prepend="," property="status">
            status = #status#
        </isNotEmpty>
        <isNotEmpty prepend="," property="total">
            total = #total#
        </isNotEmpty>
        <isNotEmpty prepend="," property="shippedAt">
            shipped_at = #shippedAt#
        </isNotEmpty>
    </dynamic>
    <dynamic prepend="WHERE">
        <isNotNull prepend="AND" property="id">
            id = #id#
        </isNotNull>
    </dynamic>
</update>
```

The `SET` block and the `WHERE` block each suppress their own first-child prepend independently. If `status` and `total` are both set but `shippedAt` is null, the output is:

```sql
UPDATE orders SET status = ?, total = ? WHERE id = ?
```

If none of the `SET` properties are provided, neither `SET` nor any column assignment appears — producing a no-op update that the calling code should guard against.

---

## Dynamic Column Selection

A `dynamic` element without a `prepend` attribute acts as a pure conditional grouping container. This is useful for building a dynamic column list.

```xml
<select id="getReport" parameterClass="Application\Model\ReportOptions"
        resultClass="array">
    SELECT
        id
        <dynamic>
            <isEqual prepend="," property="includeEmail" compareValue="true">
                email
            </isEqual>
            <isEqual prepend="," property="includeName" compareValue="true">
                name
            </isEqual>
            <isEqual prepend="," property="includeBalance" compareValue="true">
                balance
            </isEqual>
        </dynamic>
    FROM account
</select>
```

Each `isEqual` child emits its comma-prefixed column only when the corresponding flag equals `true`. The `dynamic` wrapper itself emits no extra text. The first contributing child's `,` prepend is still suppressed within the dynamic scope, which would produce an invalid column list here — so when using a `dynamic` block for column lists without a `prepend`, ensure the static SQL always supplies at least one column before the block (as shown with `id` above) so that the first contributing child's `,` is preserved correctly.

---

## Full Query-by-Example

A `SELECT` statement where four optional filter fields are sourced from a parameter object. Any combination of fields — including none — produces valid SQL.

Parameter class:

```php
namespace Application\Model;

class ProductSearch
{
    public ?string $name        = null;
    public ?int    $categoryId  = null;
    public ?float  $minPrice    = null;
    public ?float  $maxPrice    = null;
}
```

Mapping:

```xml
<select id="findProducts"
        parameterClass="Application\Model\ProductSearch"
        resultClass="Application\Model\Product">
    SELECT id, name, price, category_id
    FROM product
    <dynamic prepend="WHERE">
        <isNotNull prepend="AND" property="name">
            name LIKE #name#
        </isNotNull>
        <isNotNull prepend="AND" property="categoryId">
            category_id = #categoryId#
        </isNotNull>
        <isNotNull prepend="AND" property="minPrice">
            price &gt;= #minPrice#
        </isNotNull>
        <isNotNull prepend="AND" property="maxPrice">
            price &lt;= #maxPrice#
        </isNotNull>
    </dynamic>
    ORDER BY name
</select>
```

PHP usage via [`TSqlMapGateway`](../statements/README.md#tsqlmapgateway):

```php
$search = new ProductSearch();
$search->categoryId = 5;
$search->maxPrice   = 50.00;

$products = $gateway->queryForList('findProducts', $search);
// Executes: SELECT ... FROM product WHERE category_id = ? AND price <= ? ORDER BY name
```

With an empty `ProductSearch` object (all `null`):

```sql
SELECT id, name, price, category_id FROM product ORDER BY name
```

---

[SqlMap Docs](../README.md) -> [Dynamic SQL](README.md) -> dynamic

## See Also

- [Dynamic SQL Overview](README.md) — index of all conditional tags and inline substitution tokens
- [isNotNull / isNull](README.md#null-checks) — conditional inclusion based on null property values
- [isNotEmpty / isEmpty](README.md#empty-checks) — conditional inclusion based on empty property values
- [isEqual / isNotEqual](README.md#comparisons) — conditional inclusion based on value comparisons
- [iterate](README.md#iterate) — repeating SQL fragments over list and array properties
