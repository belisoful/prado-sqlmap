[SqlMap Docs](../README.md) -> [Configuration](README.md) -> typeAlias

# typeAlias

A `typeAlias` declaration binds a short name to a fully-qualified PHP class name. Once defined, the alias can be used wherever a class name is accepted: `resultClass`, `parameterClass`, and `type` attributes on result/parameter properties.

## Syntax

In a config file (`sqlMapConfig`):

```xml
<sqlMapConfig>
    <typeAlias alias="Account" type="App\Domain\Account" />
</sqlMapConfig>
```

In a mapping file (`sqlMap` root element, inside an `alias` block):

```xml
<sqlMap namespace="Account">
    <alias>
        <typeAlias alias="Account" type="App\Domain\Account" />
    </alias>
</sqlMap>
```

## Attributes

| Attribute | Required | Description |
|-----------|----------|-------------|
| `alias`   | yes      | Short name used in mapping attributes. Case-insensitive when resolved. |
| `type`    | yes      | Fully-qualified PHP class name (FQN) that the alias resolves to. |

## Built-in Aliases

The SqlMap engine pre-registers the following aliases. User-defined aliases may not shadow these names.

| Alias(es)            | Resolved type  |
|----------------------|----------------|
| `array`, `Hashtable` | PHP `array`    |
| `TList`              | [`Prado\Collections\TList`](https://github.com/pradosoft/prado) |
| `TMap`               | [`Prado\Collections\TMap`](https://github.com/pradosoft/prado)  |
| `string`             | `string`       |
| `int`, `integer`     | `int`          |
| `float`              | `float`        |
| `bool`, `boolean`    | `bool`         |
| `object`             | `object`       |

## Scope

**Global scope** — aliases declared under `<sqlMapConfig>` are available to every mapping file loaded by that configuration. Use this for shared domain classes referenced across multiple mapping files.

**Local scope** — aliases declared inside an `<alias>` block within a `<sqlMap>` mapping file are visible only within that file. Local aliases do not leak to other mapping files.

When the same alias name appears in both scopes, the local declaration takes precedence within that mapping file.

## Example

Define an alias for a domain class globally, then use it in a mapping file.

**sqlmap-config.xml**

```xml
<sqlMapConfig>
    <typeAlias alias="Account" type="App\Domain\Account" />

    <sqlMaps>
        <sqlMap resource="maps/Account.xml" />
    </sqlMaps>
</sqlMapConfig>
```

**maps/Account.xml**

```xml
<sqlMap namespace="Account">

    <resultMap id="AccountResult" class="Account">
        <result property="id"       column="account_id" />
        <result property="username" column="username" />
        <result property="email"    column="email" />
    </resultMap>

    <select id="getById" parameterClass="int" resultClass="Account">
        SELECT account_id, username, email
        FROM   accounts
        WHERE  account_id = #value#
    </select>

</sqlMap>
```

`resultClass="Account"` resolves to `App\Domain\Account` through the alias registered in the config file.

---

[SqlMap Docs](../README.md) -> [Configuration](README.md) -> typeAlias

## See also

- [sqlMapConfig](sqlMapConfig.md) — top-level configuration file and global alias registration
- [typeHandler](typeHandler.md) — register custom type-conversion handlers
- [resultMap](../mappings/resultMap.md) — map result columns to object properties using class aliases
