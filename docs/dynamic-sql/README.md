[SqlMap Docs](../README.md) -> Dynamic SQL

# Dynamic SQL

Dynamic SQL tags adapt a statement at runtime based on parameter values. They eliminate the need for near-duplicate statements that differ only by which clauses are present.

## How It Works

A [`<dynamic>`](dynamic.md) element wraps one or more conditional child tags. At execution time, each child tag evaluates a condition against the parameter object. Tags whose condition is satisfied contribute their SQL fragment to the final statement; tags whose condition is not satisfied are omitted entirely.

The `<dynamic>` element itself has a `prepend` attribute — a connective keyword such as `WHERE` or `AND` — that is injected before the block when at least one child tag matches. When no child matches the entire `<dynamic>` block, including its prepend, is suppressed.

Example: a select that conditionally filters by two columns:

```xml
<select id="findAccounts" resultMap="account-result">
  SELECT * FROM account
  <dynamic prepend="WHERE">
    <isNotNull prepend="AND" property="firstName">
      first_name = #firstName#
    </isNotNull>
    <isNotNull prepend="AND" property="lastName">
      last_name = #lastName#
    </isNotNull>
  </dynamic>
</select>
```

When both properties are `null`, the `WHERE` clause is absent. When only one matches, `WHERE` appears without a leading `AND`.

## The Prepend Mechanism

Each conditional child tag carries its own `prepend` attribute. The outermost (first) active child tag's prepend is suppressed and replaced by the parent `<dynamic>` element's prepend. Subsequent active siblings keep their own prepend values.

This prevents the generated SQL from starting with a dangling `AND` or `OR`. The rule applies one level deep: child tags nested inside another conditional tag follow the same rule relative to their own parent.

Example showing `WHERE` appearing only when at least one condition matches:

```xml
<dynamic prepend="WHERE">
  <isGreaterThan prepend="AND" property="age" compareValue="0">
    age > #age#
  </isGreaterThan>
  <isNotEmpty prepend="AND" property="city">
    city = #city#
  </isNotEmpty>
</dynamic>
```

- `age = 25`, `city = ""` — generates `WHERE age > #age#` (first active child's `AND` is replaced by `WHERE`; empty city is skipped).
- `age = 0`, `city = "Oslo"` — generates `WHERE city = #city#`.
- Neither matches — generates nothing.

## Tag Overview

| Tag | Tests | Notes |
|---|---|---|
| [`<dynamic>`](dynamic.md) | Container; no condition of its own | Injects its `prepend` when any child matches; suppresses when none match |
| [`<isParameterPresent>`](conditionals.md#isparameterpresent) | Parameter object is not `null` | True when a parameter was supplied at all |
| [`<isNotParameterPresent>`](conditionals.md#isnotparameterpresent) | Parameter object is `null` | True when no parameter was supplied |
| [`<isPropertyAvailable>`](conditionals.md#ispropertyavailable) | Named property exists on the parameter | Checks via reflection; does not test the value |
| [`<isNotPropertyAvailable>`](conditionals.md#isnotpropertyavailable) | Named property does not exist | Inverse of `<isPropertyAvailable>` |
| [`<isNull>`](conditionals.md#isnull) | Property value is `null` | |
| [`<isNotNull>`](conditionals.md#isnotnull) | Property value is not `null` | |
| [`<isEmpty>`](conditionals.md#isempty) | Property is `null`, `""`, or `0` | Also matches empty arrays and `Countable` with count 0 |
| [`<isNotEmpty>`](conditionals.md#isnotempty) | Property is not empty | Inverse of `<isEmpty>` |
| [`<isEqual>`](conditionals.md#isequal) | Property equals `compareValue` or `compareProperty` | String or numeric comparison |
| [`<isNotEqual>`](conditionals.md#isnotequalh) | Property does not equal the compare target | Inverse of `<isEqual>` |
| [`<isGreaterThan>`](conditionals.md#isgreaterthan) | Property is numerically greater than `compareValue` or `compareProperty` | |
| [`<isGreaterEqual>`](conditionals.md#isgreaterequal) | Property is greater than or equal to the compare target | |
| [`<isLessThan>`](conditionals.md#islessthan) | Property is numerically less than the compare target | |
| [`<isLessEqual>`](conditionals.md#islessequal) | Property is less than or equal to the compare target | |
| [`<iterate>`](iterate.md) | Iterates over a `List`-valued property | Repeats its body for each element; supports `open`, `close`, `conjunction` |

## Dollar Substitution in Dynamic Context

Dollar substitution (`$property$` or `${property}`) performs literal string injection without binding. It can be combined with dynamic tags when the substitution itself should only appear under a condition:

```xml
<dynamic prepend="ORDER BY">
  <isNotNull property="sortColumn">
    $sortColumn$ $sortDirection$
  </isNotNull>
</dynamic>
```

Because `$sortColumn$` is injected verbatim into the SQL string, validate or whitelist the value before passing it as a parameter to avoid SQL injection. Use `#property#` bound parameters wherever possible. See [Inline Parameters](../mappings/inline-parameters.md) for full syntax.

## Further Reading

- [dynamic tag reference](dynamic.md) — full attribute list and nesting rules
- [Conditional tags reference](conditionals.md) — all 14 condition tags with attribute tables and examples
- [iterate tag reference](iterate.md) — looping over list-valued parameters

---

[SqlMap Docs](../README.md) -> Dynamic SQL

## See Also

- [Statements overview](../statements/README.md) — where dynamic SQL is embedded
- [Inline Parameters](../mappings/inline-parameters.md) — `#property#` and `$property$` syntax
- [parameterMap reference](../mappings/parameter-map.md) — explicit parameter mapping as an alternative to inline params
