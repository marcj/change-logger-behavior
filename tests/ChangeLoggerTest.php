<?php

namespace Propel\Tests\Generator\Behavior\ChangeLogger;

use Propel\Generator\Util\QuickBuilder;
use Propel\Tests\TestCase;

class ChangeLoggerTest extends TestCase
{

    public function setUp()
    {
        if (!class_exists('\ChangeloggerBehaviorSingle')) {
            $schema = <<<EOF
<database name="changelogger_behavior_test">
    <table name="changelogger_behavior_single">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <column name="title" type="VARCHAR" size="100" primaryString="true" />
        <column name="age" type="INTEGER" />
        <behavior name="change_logger">
            <parameter name="log" value="title"/>
        </behavior>
    </table>
    <table name="changelogger_behavior_multiple">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />
        <column name="title" type="VARCHAR" size="100" primaryString="true" />
        <column name="age" type="INTEGER" />
        <behavior name="change_logger">
            <parameter name="log" value="title, age"/>
        </behavior>
    </table>
</database>
EOF;
            QuickBuilder::buildSchema($schema);
        }
    }

    public function testSingle()
    {
        \ChangeloggerBehaviorSingleQuery::create()->deleteAll();
        \ChangeloggerBehaviorSingleTitleLogQuery::create()->deleteAll();

        $this->assertTrue(class_exists('\ChangeloggerBehaviorSingle'));
        $this->assertTrue(class_exists('\ChangeloggerBehaviorSingleTitleLog'));

        $item = new \ChangeloggerBehaviorSingle();

        $this->assertTrue(method_exists($item, 'addTitleVersion'));
        $item->save();
        $this->assertEquals(0, \ChangeloggerBehaviorSingleTitleLogQuery::create()->count());

        $item->setTitle('Teschd');
        $item->save();

        //initial save saves already a log entry
        $this->assertEquals(1, \ChangeloggerBehaviorSingleTitleLogQuery::create()->count());
        $this->assertEquals(1, \ChangeloggerBehaviorSingleTitleLogQuery::create()->findOne()->getVersion());

        $item->setAge(2);
        $item->save();

        //another column doesnt trigger a new version for `title`
        $this->assertEquals(1, \ChangeloggerBehaviorSingleTitleLogQuery::create()->count());
        $lastVersion = \ChangeloggerBehaviorSingleTitleLogQuery::create()->orderByVersion('desc')->findOne();
        $this->assertEquals(1, $lastVersion->getVersion());
        $this->assertEquals('Teschd', $lastVersion->getTitle());

        $item->setTitle('Changed');
        $item->save();

        //title has been changed, we have now two versions
        $this->assertEquals(2, \ChangeloggerBehaviorSingleTitleLogQuery::create()->count());
        $lastVersion = \ChangeloggerBehaviorSingleTitleLogQuery::create()->orderByVersion('desc')->findOne();
        $this->assertEquals(2, $lastVersion->getVersion());
        $this->assertEquals('Changed', $lastVersion->getTitle());
    }

    public function testMultiple()
    {
        \ChangeloggerBehaviorMultipleQuery::create()->deleteAll();
        \ChangeloggerBehaviorMultipleTitleLogQuery::create()->deleteAll();

        $this->assertTrue(class_exists('\ChangeloggerBehaviorMultiple'));
        $this->assertTrue(class_exists('\ChangeloggerBehaviorMultipleTitleLog'));
        $this->assertTrue(class_exists('\ChangeloggerBehaviorMultipleAgeLog'));

        $item = new \ChangeloggerBehaviorMultiple();

        $this->assertTrue(method_exists($item, 'addTitleVersion'));
        $this->assertTrue(method_exists($item, 'addAgeVersion'));
        $item->save();
        $this->assertEquals(0, \ChangeloggerBehaviorMultipleTitleLogQuery::create()->count());

        $item->setTitle('Teschd');
        $item->save();

        //initial save saves already a log entry
        $this->assertEquals(1, \ChangeloggerBehaviorMultipleTitleLogQuery::create()->count());
        $this->assertEquals(1, \ChangeloggerBehaviorMultipleTitleLogQuery::create()->findOne()->getVersion());
        $lastVersion = \ChangeloggerBehaviorMultipleTitleLogQuery::create()->orderByVersion('desc')->findOne();
        $this->assertEquals(1, $lastVersion->getVersion());
        $this->assertEquals('Teschd', $lastVersion->getTitle());
        $this->assertEquals(0, \ChangeloggerBehaviorMultipleAgeLogQuery::create()->count());

        $item->setAge(2);
        $item->save();

        //title has not changed anything, so check as above
        $this->assertEquals(1, \ChangeloggerBehaviorMultipleTitleLogQuery::create()->count());
        $lastVersion = \ChangeloggerBehaviorMultipleTitleLogQuery::create()->orderByVersion('desc')->findOne();
        $this->assertEquals(1, $lastVersion->getVersion());
        $this->assertEquals('Teschd', $lastVersion->getTitle());

        //we have now additional a `age` log entry
        $this->assertEquals(1, \ChangeloggerBehaviorMultipleAgeLogQuery::create()->count());
        $lastVersion = \ChangeloggerBehaviorMultipleAgeLogQuery::create()->orderByVersion('desc')->findOne();
        $this->assertEquals(1, $lastVersion->getVersion());
        $this->assertEquals(2, $lastVersion->getAge());

        $item->setTitle('Changed');
        $item->save();

        //title has been changed, we have now two versions
        $this->assertEquals(2, \ChangeloggerBehaviorMultipleTitleLogQuery::create()->count());
        $lastVersion = \ChangeloggerBehaviorMultipleTitleLogQuery::create()->orderByVersion('desc')->findOne();
        $this->assertEquals(2, $lastVersion->getVersion());
        $this->assertEquals('Changed', $lastVersion->getTitle());

        //age has not changed anything, so check as above
        $this->assertEquals(1, \ChangeloggerBehaviorMultipleAgeLogQuery::create()->count());
        $lastVersion = \ChangeloggerBehaviorMultipleAgeLogQuery::create()->orderByVersion('desc')->findOne();
        $this->assertEquals(1, $lastVersion->getVersion());
        $this->assertEquals(2, $lastVersion->getAge());
    }

}