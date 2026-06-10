[SqlMap Docs](../README.md) -> [Configuration](README.md) -> typeHandler

# typeHandler

A `typeHandler` registers a custom PHP class that converts values between PHP and database column representations for non-standard or specially handled types. The framework applies the handler automatically wherever the matching `dbType` or handler class is referenced in a result map, parameter map, or inline parameter.

---

## Implementing TSqlMapTypeHandler

Extend [`TSqlMapTypeHandler`](../../src/Data/SqlMap/DataMapper/TSqlMapTypeHandler.php) (in namespace `Prado\Data\SqlMap\DataMapper`) and implement three abstract methods:

| Method | Direction | Purpose |
|---|---|---|
| `getParameter($object)` | PHP → DB | Returns the value to bind as a statement parameter. |
| `getResult($string)` | DB → PHP | Converts the raw column value to a PHP value. |
| `createNewInstance($row = null)` | XML config / null handling | Returns a typed default used when the column value equals `nullValue`. |

### Example: OuiNonBoolHandler

Stores PHP booleans as the French strings `"OUI"` / `"NON"` in the database.

```php
namespace App\TypeHandlers;

use Prado\Data\SqlMap\DataMapper\TSqlMapTypeHandler;

class OuiNonBoolHandler extends TSqlMapTypeHandler
{
    /**
     * Converts a PHP bool to the database string representation.
     */
    public function getParameter($object): string
    {
        return $object ? 'OUI' : 'NON';
    }

    /**
     * Converts the database string to a PHP bool.
     */
    public function getResult($string): bool
    {
        return strtoupper((string) $string) === 'OUI';
    }

    /**
     * Returns the default PHP value used when the column matches nullValue.
     */
    public function createNewInstance($row = null): bool
    {
        return false;
    }
}
```

---

## Registration in XML

### In the SqlMap config file

Register a handler inside the `<typeHandlers>` block of your `sqlMapConfig` file. The handler is then available globally across all mapped statement files.

```xml
<sqlMapConfig>
    <typeHandlers>
        <typeHandler class="App\TypeHandlers\OuiNonBoolHandler" dbType="OUINON" />
    </typeHandlers>

    <!-- ... transactionManager, sqlMaps, etc. -->
</sqlMapConfig>
```

### In a mapping file

A `<typeHandler>` (or `<typeAlias>`) element may appear inside the `<sqlMap>` root element of an individual mapping file. Its scope is limited to that file.

```xml
<sqlMap namespace="Account">
    <typeHandlers>
        <typeHandler class="App\TypeHandlers\OuiNonBoolHandler" dbType="OUINON" />
    </typeHandlers>

    <!-- statements, resultMaps, etc. -->
</sqlMap>
```

---

## Attributes

| Attribute | Required | Description |
|---|---|---|
| `class` | Yes | Fully qualified PHP class name of the `TSqlMapTypeHandler` subclass. |
| `dbType` | No | Logical database type label used to look up this handler. Must match the `dbType` specified in result/parameter map entries. Defaults to `"NULL"` when omitted. |

---

## Using a Type Handler

### In a result map

Reference the handler's `dbType` in a [`<result>`](../mappings/resultMap.md) element via the `dbType` attribute:

```xml
<resultMap id="AccountResult" class="Account">
    <result property="active" column="ACTIVE" dbType="OUINON" />
</resultMap>
```

### In a parameter map

Reference the handler's `dbType` in a [`<parameter>`](../mappings/parameterMap.md) element:

```xml
<parameterMap id="AccountParam" class="Account">
    <parameter property="active" dbType="OUINON" />
</parameterMap>
```

### In inline parameters

Specify the `dbType` as the second token in an inline parameter expression:

```xml
<insert id="insertAccount" parameterClass="Account">
    INSERT INTO account (active) VALUES (#active:OUINON#)
</insert>
```

The full inline syntax is `#property:dbType:nullValue#`. Only `property` is required; `dbType` and `nullValue` are optional.

---

## Programmatic Registration

Register a handler at runtime through [`TSqlMapManager`](../../src/Data/SqlMap/TSqlMapManager.php):

```php
/** @var \Prado\Data\SqlMap\TSqlMapManager $manager */
$handler = new \App\TypeHandlers\OuiNonBoolHandler();
$handler->setDbType('OUINON');
$manager->getTypeHandlers()->registerTypeHandler($handler);
```

`getTypeHandlers()` returns the [`TSqlMapTypeHandlerRegistry`](../../src/Data/SqlMap/DataMapper/TSqlMapTypeHandlerRegistry.php) instance. `registerTypeHandler()` keys the handler by its `Type` property (defaults to the class name) and makes it available for all subsequent statement executions.

To retrieve a registered handler by its `dbType` label:

```php
$handler = $manager->getTypeHandlers()->getDbTypeHandler('OUINON');
```

---

[SqlMap Docs](../README.md) -> [Configuration](README.md) -> typeHandler

## See also

- [`TSqlMapTypeHandler`](../../src/Data/SqlMap/DataMapper/TSqlMapTypeHandler.php) — abstract base class with `getParameter`, `getResult`, and `createNewInstance`.
- [`TSqlMapTypeHandlerRegistry`](../../src/Data/SqlMap/DataMapper/TSqlMapTypeHandlerRegistry.php) — registry that stores and resolves handlers by type or dbType.
- [resultMap](../mappings/resultMap.md) — where `dbType` references a handler during result row mapping.
- [parameterMap](../mappings/parameterMap.md) — where `dbType` references a handler during parameter binding.
- [sqlMapConfig](sqlMapConfig.md) — top-level configuration file that hosts the `<typeHandlers>` block.
