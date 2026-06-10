[SqlMap Docs](../README.md) -> [Dynamic SQL](README.md) -> iterate

# iterate

The `<iterate>` tag repeats a SQL fragment once per element in a list-valued parameter property (or the parameter object itself when it is a plain list). The repeated fragments are joined by an optional conjunction, and an optional open/close pair wraps the entire result.

## Syntax

### Named property form

Use this form when the list is a property of the parameter object.

```xml
<iterate property="Ids" open="(" close=")" conjunction=",">
  #Ids[]#
</iterate>
```

`property` names the list property on the parameter object. Inside the body, reference each element as `#propertyName[]#` — the `[]` suffix tells the engine to substitute the current iteration element rather than the property itself.

### Parameter-list form

Use this form when the parameter object passed to the statement is the list itself (no wrapping object).

```xml
<iterate open="(" close=")" conjunction=",">
  #[]#
</iterate>
```

With no `property` attribute the engine iterates the parameter object directly. Inside the body, `#[]#` references the current element.

## Attributes

| Attribute | Required | Description |
|---|---|---|
| `property` | No | Name of the list property on the parameter object. Omit when the parameter object is the list. |
| `open` | No | String prepended before the first element, outside the loop (e.g. `(`). |
| `close` | No | String appended after the last element (e.g. `)`). |
| `conjunction` | No | String inserted between adjacent elements (e.g. `,` or `AND`). Not appended after the last element. |
| `prepend` | No | SQL keyword or fragment prepended to the entire `<iterate>` block when it produces output. Suppressed when the list is empty or when this is the first active child of a [`<dynamic>`](dynamic.md) element that provides its own `prepend`. |

## Element Reference Inside iterate

| List form | Body token | Resolves to |
|---|---|---|
| Named property (`property="Ids"`) | `#Ids[]#` | Current element of the `Ids` list |
| Named property with db type | `#Ids[]:INT#` | Current element cast to `INT` |
| Parameter-list (no `property`) | `#[]#` | Current element of the parameter list |
| Parameter-list with db type | `#[]:VARCHAR#` | Current element cast to `VARCHAR` |

The `[]` suffix is mandatory in the body. Writing `#Ids#` inside an `<iterate>` block refers to the whole list object, not the current element.

Object-valued elements are also supported. If each element has a `name` property, reference it as `#Ids[].name#` (named form) or `#[].name#` (parameter-list form).

## IN Clause Example

Build a `WHERE id IN (...)` predicate from a plain list passed as the parameter object.

Mapping:

```xml
<select id="GetAccountsByIds" resultMap="account-result">
  SELECT * FROM account
  WHERE account_id IN
  <iterate open="(" close=")" conjunction=",">
    #[]#
  </iterate>
</select>
```

PHP:

```php
$ids = [1, 3, 7];
$accounts = $gateway->queryForList('GetAccountsByIds', $ids);
```

Generated SQL (values are bound as PDO parameters, not interpolated):

```sql
SELECT * FROM account WHERE account_id IN (?, ?, ?)
```

## Named Property Example

Use `property` when the list is one field of a larger parameter object.

Mapping:

```xml
<select id="GetAccountsByStatus" resultMap="account-result">
  SELECT * FROM account
  WHERE status IN
  <iterate property="StatusList" open="(" close=")" conjunction=",">
    #StatusList[]#
  </iterate>
  AND region = #region#
</select>
```

PHP:

```php
$criteria = new stdClass();
$criteria->StatusList = ['active', 'pending'];
$criteria->region = 'EU';
$accounts = $gateway->queryForList('GetAccountsByStatus', $criteria);
```

## Multiple iterate in One Statement — MultiDynamicIterate Pattern

A statement can contain more than one `<iterate>` block, each driven by a different list property. Wrap them in a [`<dynamic>`](dynamic.md) element so that the connective keywords are emitted only when the corresponding list is non-empty.

```xml
<select id="SearchAccounts" resultMap="account-result">
  SELECT * FROM account
  <dynamic prepend="WHERE">
    <isNotEmpty prepend="AND" property="Ids">
      account_id IN
      <iterate property="Ids" open="(" close=")" conjunction=",">
        #Ids[]#
      </iterate>
    </isNotEmpty>
    <isNotEmpty prepend="AND" property="Statuses">
      status IN
      <iterate property="Statuses" open="(" close=")" conjunction=",">
        #Statuses[]#
      </iterate>
    </isNotEmpty>
  </dynamic>
</select>
```

Each `<isNotEmpty>` guard prevents the surrounding `IN (...)` fragment from being emitted when its list is empty. The `<dynamic>` element suppresses `WHERE` entirely when both lists are empty.

## Combining with isNotNull Guard

`<iterate>` does not test whether the list property is present or non-null before iterating. Wrap it in [`<isNotNull>`](conditionals.md#isnotnull) or [`<isNotEmpty>`](conditionals.md#isnotempty) when the property may be absent.

```xml
<dynamic prepend="WHERE">
  <isNotNull prepend="AND" property="TagIds">
    tag_id IN
    <iterate property="TagIds" open="(" close=")" conjunction=",">
      #TagIds[]#
    </iterate>
  </isNotNull>
</dynamic>
```

Without the guard, passing a parameter object whose `TagIds` property is `null` produces `tag_id IN ()`, which is invalid SQL in most databases.

## Notes

- **Empty list behavior** — when the list resolves to an empty array, `<iterate>` produces no output (not even the `open`/`close` wrappers). Any `prepend` on the `<iterate>` tag is also suppressed. A guard tag is still recommended because an empty-list result differs from a null-property result.
- **Null guard requirement** — if the list property can be `null` (not set on the parameter object), wrap `<iterate>` in `<isNotNull>` or `<isNotEmpty>`. Passing `null` to the iteration engine throws a runtime exception.
- **PDO binding** — each element is bound as a separate named PDO parameter. Values are never interpolated into the SQL string, so SQL injection via list elements is not possible.
- **Nested objects** — dot-notation (`#Ids[].subProperty#`) accesses a property of each list element when the list contains objects rather than scalars.
- **`conjunction` placement** — the conjunction appears between elements only, never before the first or after the last.

---

[SqlMap Docs](../README.md) -> [Dynamic SQL](README.md) -> iterate

## See Also

- [`<dynamic>`](dynamic.md) — container tag that manages prepend suppression for conditional blocks
- [Conditional Tags](conditionals.md) — `isNotNull`, `isNotEmpty`, and all other condition tags used to guard `<iterate>`
- [Inline Parameter Syntax](../mappings/inline-params.md) — `#property#`, db-type suffixes, and null values used inside iterate bodies
