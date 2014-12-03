### ChangeLoggerBehavior

A behavior for Propel2, like the VersionableBehavior, but column based. It logs basically all changes
into a extra logger table, defined for each column you have specified in the `log` parameter.

## Usage

```xml
<table name="user">
    <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
    <column name="username" type="VARCHAR" size="100" primaryString="true" />
    <behavior name="change_logger">
        <parameter name="log" value="username"/>
        <parameter name="created_at" value="true"/>
    </behavior>
</table>
```

If you haven't installed this behavior through composer, you need to specify the full class name as behavior name:

```xml
    <behavior name="\MJS\ChangeLogger\ChangeLoggerBehavior">
```

You can also define multiple columns. Each column gets a own logger table.

```xml
    <parameter name="log" value="username, email"/>
```

```php
$user = UserQuery::create()->findByUsername('Klaus');
$user->setUsername('Erik');
$user->setUsernameChangeComment('Due to XY');
$user->setUsernameChangeBy('Superuser');
$user->save()

$usernameChangeLogs = UserUsernameLogQuery::create()
    ->filterByOrigin($user)
    ->orderByVersion('desc')
    ->find();

foreach ($usernameChangeLogs as $log) {
    echo $log->getVersion();
    echo $log->getId(); //foreignKey to `user`
    echo $log->getUsername(); //'Klaus'
    echo $log->getCreatedAt(); //timestamp
}
```

### Parameter

with its default value.

```xml
<parameter name="created_at" value="false" />
<parameter name="created_by" value="false" />
<parameter name="comment" value="false" />
<parameter name="created_at_column" value="log_created_at" />
<parameter name="created_by_column" value="log_created_by" />
<parameter name="comment_column" value="log_comment" />
<parameter name="version_column" value="version" />
<parameter name="log" value="" />
```
