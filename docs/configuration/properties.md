[SqlMap Docs](../README.md) -> [Configuration](README.md) -> properties

# properties / property

The `<properties>` and `<property>` elements declare named string values that the SqlMap XML parser substitutes into the configuration at parse time. They act as compile-time constants: every `${name}` token in the config and mapping files is replaced with the declared value before any SQL is executed.

## Syntax

Inline `<property>` elements inside `<properties>`:

```xml
<sqlMapConfig>
    <properties>
        <property name="db.host" value="localhost" />
        <property name="db.port" value="3306" />
        <property name="db.name" value="myapp" />
    </properties>

    <transactionManager type="PDO">
        <dataSource type="PDO"
            connectionString="mysql:host=${db.host};port=${db.port};dbname=${db.name}"
            username="root"
            password="" />
    </transactionManager>
</sqlMapConfig>
```

Loading values from an external `.properties` file via `resource`:

```xml
<sqlMapConfig>
    <properties resource="config/database.properties" />
    ...
</sqlMapConfig>
```

`config/database.properties`:

```
db.host=localhost
db.port=3306
db.name=myapp
```

Inline `<property>` elements and a `resource` file may coexist. Inline declarations are processed first; the resource file can then reference or extend them.

## Attribute Reference

### `<properties>`

| Attribute  | Required | Description |
|------------|----------|-------------|
| `resource` | No       | Path to an external `.properties` file (Java-style `key=value` lines). Resolved relative to the config file. |

### `<property>`

| Attribute | Required | Description |
|-----------|----------|-------------|
| `name`    | Yes      | Token name used in `${name}` substitutions. |
| `value`   | Yes      | String value substituted wherever `${name}` appears. |

## Usage

Place `${name}` anywhere in the sqlMapConfig XML or in referenced [sqlMap](sqlMap.md) mapping files:

- **Connection strings** — `connectionString="pgsql:host=${db.host};dbname=${db.name}"`
- **Attribute values** — `configFile="${maps.dir}/Accounts.xml"`
- **SQL text** — `SELECT * FROM ${schema}.account WHERE id = #id#`

Substitution is performed by the XML parser during configuration load ([TSqlMapXmlConfiguration](../../src/Data/SqlMap/Configuration/TSqlMapXmlConfiguration.php)). Every occurrence of a `${name}` token across all loaded files is replaced before any statement or result map is built.

## External Properties File

When `resource` is set on `<properties>`, the parser reads the file and adds each `key=value` pair to the substitution table. The file format follows Java `.properties` conventions:

- One `key=value` pair per line.
- Lines beginning with `#` are comments.
- Whitespace around `=` is trimmed.

```
# database.properties
db.host=db.example.com
db.port=5432
db.name=production
```

The `resource` path is resolved relative to the directory that contains the sqlMapConfig XML file.

## Example

Parameterising a `<selectKey>` query that differs across databases. Define the strategy in the properties block and reference it in a mapping file.

`sqlmap-config.xml`:

```xml
<sqlMapConfig>
    <properties>
        <property name="selectkey.sql" value="SELECT LAST_INSERT_ID()" />
    </properties>

    <sqlMaps>
        <sqlMap resource="maps/Account.xml" />
    </sqlMaps>
</sqlMapConfig>
```

`maps/Account.xml`:

```xml
<sqlMap namespace="Account">
    <insert id="insertAccount" parameterClass="Account">
        INSERT INTO account (username, email) VALUES (#username#, #email#)
        <selectKey resultClass="int" keyProperty="id" type="post">
            ${selectkey.sql}
        </selectKey>
    </insert>
</sqlMap>
```

For PostgreSQL, override the property value to `SELECT LASTVAL()` without touching the mapping file.

## Notes

- Substitution happens once, at parse time. Properties cannot be changed or overridden at runtime after the [TSqlMapManager](../../src/Data/SqlMap/TSqlMapManager.php) is initialized.
- `${name}` tokens that have no matching declared property are left unresolved and will appear literally in the SQL or attribute value, which typically causes a parse or connection error. Always declare every token that appears in the files.
- Property names are case-sensitive.
- If caching is enabled in [TSqlMapConfig](../../src/Data/SqlMap/TSqlMapConfig.php), the fully substituted manager is stored in the application cache. Changing a `.properties` file requires clearing that cache to take effect.

---

[SqlMap Docs](../README.md) -> [Configuration](README.md) -> properties

## See also

- [sqlMapConfig](sqlMapConfig.md) — root configuration element that contains `<properties>`
- [transactionManager / dataSource](transactionManager.md) — the primary consumer of `${db.*}` property tokens
- [sqlMaps / sqlMap](sqlMap.md) — mapping file references that also accept `${name}` tokens in their `resource` attributes
