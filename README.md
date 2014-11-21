### ChangeLoggerBehavior

A behavior for Propel2, like the VersionableBehavior, but column based.

## Usage

```xml
<table name="user">
    <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
    <column name="username" type="VARCHAR" size="100" primaryString="true" />
    <behavior name="\MJS\ChangeLogger\ChangeLoggerBehavior">
        <parameter name="log" value="username"/>
        <parameter name="created_at" value="true"/>
    </behavior>
</table>
```

```php

$user = UserQuery::create()->findByUsername('Klaus');
$user->setUsername('Erik');
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