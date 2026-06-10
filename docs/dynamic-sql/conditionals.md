[SqlMap Docs](../README.md) -> [Dynamic SQL](README.md) -> Conditional Tags

# Conditional Tags

Conditional tags are child elements of [`<dynamic>`](dynamic.md) (or any other conditional tag) that include or exclude a SQL fragment based on a runtime condition evaluated against the parameter object.

## Common Attributes

All conditional tags share these three attributes. Not every tag uses all three; the per-tag tables below show which apply.

| Attribute | Required | Description |
|---|---|---|
| `prepend` | No | SQL keyword or fragment injected before this tag's body when the condition is true. The outermost active child's `prepend` is replaced by the parent `<dynamic>` element's own `prepend`. Typical values: `AND`, `OR`, `WHERE`. |
| `property` | Varies | Name of the property on the parameter object to test. When omitted on tags that support it, the parameter object itself is tested directly (useful for scalar parameters). |
| `compareValue` | Varies | Literal value to compare the property against. Used by equality and relational tags. Mutually exclusive with `compareProperty`. |
| `compareProperty` | Varies | Name of a second property on the parameter object whose value is used as the comparison target. Mutually exclusive with `compareValue`. |

---

## `<isParameterPresent>`

Includes its body when a parameter object was supplied to the statement (i.e., the parameter is not `null`).

| Attribute | Required |
|---|---|
| `prepend` | No |

`property`, `compareValue`, and `compareProperty` are not applicable.

```xml
<statement id="findById" parameterClass="integer" resultClass="Account">
    SELECT Account_ID as Id, Account_FirstName as FirstName
    FROM Accounts
    <isParameterPresent>
        WHERE Account_ID = #value#
    </isParameterPresent>
</statement>
```

---

## `<isNotParameterPresent>`

Includes its body when no parameter was supplied (parameter is `null`). The inverse of [`<isParameterPresent>`](#isparameterpresent).

| Attribute | Required |
|---|---|
| `prepend` | No |

```xml
<select id="findAll" resultClass="Account">
    SELECT Account_ID as Id, Account_FirstName as FirstName
    FROM Accounts
    <isNotParameterPresent>
        ORDER BY Account_LastName
    </isNotParameterPresent>
</select>
```

---

## `<isPropertyAvailable>`

Includes its body when the named property exists on the parameter object. The check is structural (via reflection); it does not test the property's value.

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | Yes | Property name to check for existence |

```xml
<dynamic prepend="WHERE">
    <isPropertyAvailable property="Id">
        Account_ID = #Id#
    </isPropertyAvailable>
</dynamic>
```

---

## `<isNotPropertyAvailable>`

Includes its body when the named property does not exist on the parameter object. The inverse of [`<isPropertyAvailable>`](#ispropertyavailable).

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | Yes | Property name to check for absence |

```xml
<dynamic prepend="WHERE">
    <isNotPropertyAvailable property="filters">
        Account_Status = 'active'
    </isNotPropertyAvailable>
</dynamic>
```

---

## `<isNull>`

Includes its body when the specified property value is `null`.

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | No | Omit to test the parameter object itself |

```xml
<dynamic prepend="WHERE">
    <isNull prepend="AND" property="EmailAddress">
        Account_Email IS NULL
    </isNull>
</dynamic>
```

---

## `<isNotNull>`

Includes its body when the specified property value is not `null`. The inverse of [`<isNull>`](#isnull).

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | No | Omit to test the parameter object itself |

```xml
<dynamic prepend="WHERE">
    <isNotNull prepend="AND" property="Ids">
        Account_ID IN
        <iterate property="Ids" open="(" close=")" conjunction=",">
            #Ids[]#
        </iterate>
    </isNotNull>
</dynamic>
```

---

## `<isEmpty>`

Includes its body when the specified property is `null`, an empty string (`""`), zero (`0`), an empty array, or a `Countable` object with count 0.

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | No | Omit to test the parameter object itself |

```xml
<dynamic prepend="WHERE">
    <isEmpty prepend="AND" property="SearchTerm">
        Account_Status = 'active'
    </isEmpty>
</dynamic>
```

---

## `<isNotEmpty>`

Includes its body when the specified property is not empty (not `null`, not `""`, not `0`, not an empty array or `Countable`). The inverse of [`<isEmpty>`](#isempty).

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | No | Omit to test the parameter object itself |

```xml
<dynamic prepend="WHERE">
    <isNotEmpty prepend="AND" property="FirstName">
        Account_FirstName = #FirstName#
    </isNotEmpty>
    <isNotEmpty prepend="AND" property="LastName">
        Account_LastName = #LastName#
    </isNotEmpty>
</dynamic>
```

---

## `<isEqual>`

Includes its body when the property value equals `compareValue` or the value of `compareProperty`.

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | No | Omit to compare the parameter object itself against `compareValue` |
| `compareValue` | Cond. | Required when `compareProperty` is absent |
| `compareProperty` | Cond. | Required when `compareValue` is absent |

```xml
<isEqual prepend="AND" property="EmailAddress" compareValue="Joe">
    Account_Email = 'joe@example.com'
</isEqual>
```

Comparing against another property:

```xml
<isEqual prepend="AND" property="Status" compareProperty="DefaultStatus">
    Account_Verified = 1
</isEqual>
```

---

## `<isNotEqual>`

Includes its body when the property value does not equal `compareValue` or the value of `compareProperty`. The inverse of [`<isEqual>`](#isequal).

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | No | Omit to compare the parameter object itself |
| `compareValue` | Cond. | Required when `compareProperty` is absent |
| `compareProperty` | Cond. | Required when `compareValue` is absent |

```xml
<isNotEqual prepend="AND" property="EmailAddress" compareValue="Joe">
    Account_Email = #EmailAddress#
</isNotEqual>
```

---

## `<isGreaterThan>`

Includes its body when the property value is numerically greater than `compareValue` or the value of `compareProperty`.

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | No | Omit to test the parameter object itself |
| `compareValue` | Cond. | Required when `compareProperty` is absent |
| `compareProperty` | Cond. | Required when `compareValue` is absent |

```xml
<dynamic prepend="WHERE">
    <isGreaterThan prepend="AND" property="Id" compareValue="0">
        Account_ID = #Id#
    </isGreaterThan>
</dynamic>
```

---

## `<isGreaterEqual>`

Includes its body when the property value is numerically greater than or equal to `compareValue` or the value of `compareProperty`.

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | No | Omit to test the parameter object itself |
| `compareValue` | Cond. | Required when `compareProperty` is absent |
| `compareProperty` | Cond. | Required when `compareValue` is absent |

```xml
<isGreaterEqual compareValue="3">
    WHERE Account_ID = 1
</isGreaterEqual>
```

---

## `<isLessThan>`

Includes its body when the property value is numerically less than `compareValue` or the value of `compareProperty`.

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | No | Omit to test the parameter object itself |
| `compareValue` | Cond. | Required when `compareProperty` is absent |
| `compareProperty` | Cond. | Required when `compareValue` is absent |

```xml
<isLessThan compareValue="3">
    WHERE Account_ID = 1
</isLessThan>
```

---

## `<isLessEqual>`

Includes its body when the property value is numerically less than or equal to `compareValue` or the value of `compareProperty`.

| Attribute | Required | Notes |
|---|---|---|
| `prepend` | No | |
| `property` | No | Omit to test the parameter object itself |
| `compareValue` | Cond. | Required when `compareProperty` is absent |
| `compareProperty` | Cond. | Required when `compareValue` is absent |

```xml
<isLessEqual compareValue="3">
    WHERE Account_ID = 1
</isLessEqual>
```

---

## Combining Tags

Conditional tags nest freely. An outer tag guards entry into a block; inner tags refine the SQL within that block. Each level's `prepend` suppression applies independently relative to its immediate parent.

```xml
<dynamic prepend="WHERE">
    <isNotNull prepend="AND" property="EmailAddress">
        <isEqual property="EmailAddress" compareValue="Joe">
            Account_Email = 'joe@example.com'
        </isEqual>
        <isNotEqual property="EmailAddress" compareValue="Joe">
            Account_Email = #EmailAddress#
        </isNotEqual>
    </isNotNull>
</dynamic>
```

Behavior:

- `EmailAddress` is `null` — the entire block is omitted; no `WHERE` clause appears.
- `EmailAddress` is `"Joe"` — generates `WHERE Account_Email = 'joe@example.com'`.
- `EmailAddress` is any other value — generates `WHERE Account_Email = #EmailAddress#`.

The `<isNotNull>` tag's `AND` is replaced by the parent `<dynamic>`'s `WHERE` because it is the first (and only) active child. The inner `<isEqual>` and `<isNotEqual>` carry no `prepend`, so no extra connective is injected between them.

---

## Testing the Parameter Itself

When `property` is omitted on tags that support it, the condition applies to the parameter object directly. This is the standard pattern for scalar parameters where the value is passed as `#value#`.

```xml
<statement id="findByName" parameterClass="string" resultClass="Account">
    SELECT Account_ID as Id, Account_FirstName as FirstName
    FROM Accounts
    <isNotEmpty>
        WHERE Account_FirstName = #value#
    </isNotEmpty>
</statement>
```

If the caller passes an empty string or `null`, the `WHERE` clause is omitted entirely and all rows are returned. The same applies to [`<isParameterPresent>`](#isparameterpresent), [`<isNotParameterPresent>`](#isnotparameterpresent), [`<isNull>`](#isnull), [`<isNotNull>`](#isnotnull), and the comparison tags when `property` is omitted.

---

[SqlMap Docs](../README.md) -> [Dynamic SQL](README.md) -> Conditional Tags

## See Also

- [`<dynamic>` tag reference](dynamic.md) — container element and prepend suppression rules
- [`<iterate>` tag reference](iterate.md) — looping over list-valued parameters
- [Inline Parameters](../mappings/inline-parameters.md) — `#property#` and `$property$` syntax used inside tag bodies
