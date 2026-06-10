[SqlMap Docs](../README.md) -> [Configuration](README.md) -> transactionManager

# transactionManager / dataSource

The `<transactionManager>` element (and its child `<dataSource>`) configures the PDO database connection that SqlMap uses when running outside a full PRADO application — CLI scripts, standalone tools, and unit tests.

## Syntax

```xml
<sqlMapConfig>

    <transactionManager type="PDO">
        <dataSource driver="mysql"
                    host="127.0.0.1"
                    port="3306"
                    database="mydb"
                    username="dbuser"
                    password="secret"
                    charset="utf8mb4" />
    </transactionManager>

    <sqlMaps>
        <sqlMap resource="maps/Account.xml" />
    </sqlMaps>

</sqlMapConfig>
```

For SQLite, omit `host`, `port`, `username`, `password`, and `charset`; set `database` to the file path:

```xml
<transactionManager type="PDO">
    <dataSource driver="sqlite"
                database="/var/data/myapp.db" />
</transactionManager>
```

## Attributes

### `<transactionManager>`

| Attribute | Required | Description |
|-----------|----------|-------------|
| `type`    | yes      | Transaction manager type. Use `PDO` for all supported databases. |

### `<dataSource>`

| Attribute  | Required              | Description |
|------------|-----------------------|-------------|
| `driver`   | yes                   | PDO driver name. See [PDO driver names](#pdo-driver-names) below. |
| `host`     | no (not SQLite)       | Hostname or IP address of the database server. |
| `port`     | no                    | TCP port. When omitted, the driver default is used. |
| `database` | yes                   | Database name, or file path for SQLite. |
| `username` | no (not SQLite)       | Database user name. |
| `password` | no                    | Database password. |
| `charset`  | no                    | Connection character set (MySQL/MariaDB). |

## PDO Driver Names

| `driver` value | Database              |
|----------------|-----------------------|
| `sqlite`       | SQLite (file or `:memory:`) |
| `mysql`        | MySQL / MariaDB       |
| `pgsql`        | PostgreSQL            |
| `sqlsrv`       | Microsoft SQL Server  |
| `oci`          | Oracle                |
| `firebird`     | Firebird              |
| `ibm`          | IBM DB2               |

## Preferred Alternative in a PRADO Application

Inside a running PRADO application, use the `ConnectionID` property of [`TSqlMapConfig`](../api/TSqlMapConfig.md) instead. Point it to a [`TDataSourceConfig`](integration.md) module already configured in the application config:

```xml
<!-- application.xml -->
<modules>
    <module id="db"
            class="Prado\Data\TDataSourceConfig"
            ConnectionString="pgsql:host=127.0.0.1;dbname=myapp"
            Username="dbuser"
            Password="secret" />

    <module id="sqlmap"
            class="Prado\Data\SqlMap\TSqlMapConfig"
            ConnectionID="db"
            ConfigFile="protected/sqlmap/sqlmap-config.xml" />
</modules>
```

This approach lets the application connection pool manage the PDO connection and keeps credentials in the application config rather than the SqlMap config file. See [Integrating with a PRADO Application](integration.md) for full details.

## Notes

- Use `<transactionManager>` for CLI scripts, standalone data-import tools, and unit tests where no `TApplication` is booted.
- The `type="PDO"` value is the only supported transaction manager type in this extension.
- Inline `<database>` credentials are loaded at config-parse time. In production, prefer environment variables or PRADO's `<parameters>` substitution to avoid storing plaintext passwords in source control.
- When `ConnectionID` is set on [`TSqlMapConfig`](../api/TSqlMapConfig.md), any `<transactionManager>` block in the SqlMap config file is ignored.

---

[SqlMap Docs](../README.md) -> [Configuration](README.md) -> transactionManager

## See also

- [sqlMapConfig](sqlMapConfig.md) — top-level config file structure and all child elements
- [Integrating with a PRADO Application](integration.md) — using `ConnectionID` and `TDataSourceConfig`
- [sqlMaps / sqlMap](sqlMaps.md) — loading mapping files from the config
