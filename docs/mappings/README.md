[SqlMap Docs](../README.md) -> Mappings

# Mappings

SqlMap transfers data between SQL and PHP in two directions: parameters flow from PHP into SQL, and result rows flow from SQL back into PHP objects. Two explicit XML constructs handle these concerns — [`parameterMap`](parameterMap.md) and [`resultMap`](resultMap.md) — along with inline parameter syntax embedded directly in the SQL string. For result mapping, an implicit `resultClass` shortcut is also available when full control is not needed.

## Comparison

| Approach | Declaration | Use when |
|---|---|---|
| `resultClass` | `resultClass` attribute on a statement; no separate XML block | Auto-mapping suffices — column names match PHP property names, or the result is a primitive/array |
| [`resultMap`](resultMap.md) | `<resultMap>` element; referenced by `resultMap` attribute | Column names differ from property names; type coercion is required; lazy loading, discriminators, or `groupBy` aggregation is needed |
| `parameterClass` | `parameterClass` attribute on a statement; no separate XML block | Inline parameters (`#property#`) resolve all needed values from a single object or primitive |
| [`parameterMap`](parameterMap.md) | `<parameterMap>` element; referenced by `parameterMap` attribute | Stored procedures with `OUT`/`INOUT` parameters; explicit `dbType`, `nullValue`, or `mode` control per parameter; `?`-placeholder style SQL |

## Topics

- [parameterMap](parameterMap.md) — explicit parameter mapping with `dbType`, `nullValue`, and `mode` control
- [resultMap](resultMap.md) — explicit result mapping with column aliases, type handlers, lazy loading, discriminators, and `groupBy` trees
- [Inline Parameters](inline-parameters.md) — `#property#` and `$property$` syntax embedded directly in SQL strings

---

[SqlMap Docs](../README.md) -> Mappings

## See also

- [Statements overview](../statements/README.md)
- [Configuration overview](../config/README.md)
- [Dynamic SQL](../statements/dynamic.md)
