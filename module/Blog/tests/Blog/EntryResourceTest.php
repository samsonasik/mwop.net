<?php
namespace Blog;

use CommonResource\DataSource\Mock as MockDataSource,
    CommonResource\DataSource\Query,
    CommonResource\Resource\Collection,
    DateInterval,
    DateTime,
    PHPUnit_Framework_TestCase as TestCase,
    stdClass,
    Zend\EventManager\EventManager,
    Zend\EventManager\StaticEventManager;

class EntryResourceTest extends TestCase
{
    public function setUp()
    {
        StaticEventManager::resetInstance();
        $this->dataSource = new MockDataSource();
        $this->resource   = new EntryResource();
        $this->resource->setDataSource($this->dataSource);
    }

    public function getItem()
    {
        return array(
            'id'        => 'some-slug',
            'title'     => 'Some Slug',
            'body'      => 'Some Slug.',
            'extended'  => '',
            'author'    => 'matthew',
            'is_draft'  => false,
            'is_public' => true,
            'created'   => strtotime('today'),
            'updated'   => strtotime('today'),
            'timezone'  => 'America/New_York',
            'tags'      => array('foo', 'bar'),
            'metadata'  => array(),
            'version'   => 2,
        );
    }

    public function getItems()
    {
        return array(
            array(
                'id'        => 'some-slug',
                'title'     => 'Some Slug',
                'body'      => 'Some Slug.',
                'extended'  => '',
                'author'    => 'matthew',
                'is_draft'  => false,
                'is_public' => true,
                'created'   => strtotime('today'),
                'updated'   => strtotime('today'),
                'timezone'  => 'America/New_York',
                'tags'      => array('foo', 'bar'),
                'metadata'  => array(),
                'version'   => 2,
            ),
            array(
                'id'        => 'some-other-slug',
                'title'     => 'Some Other Slug',
                'body'      => 'Some other slug.',
                'extended'  => '',
                'author'    => 'matthew',
                'is_draft'  => true,
                'is_public' => true,
                'created'   => strtotime('yesterday'),
                'updated'   => strtotime('today'),
                'timezone'  => 'America/New_York',
                'tags'      => array('foo'),
                'metadata'  => array(),
                'version'   => 2,
            ),
            array(
                'id'        => 'some-final-slug',
                'title'     => 'Some Final Slug',
                'body'      => 'Some final slug.',
                'extended'  => '',
                'author'    => 'matthew',
                'is_draft'  => false,
                'is_public' => true,
                'created'   => strtotime('2 days ago'),
                'updated'   => strtotime('yesterday'),
                'timezone'  => 'America/New_York',
                'tags'      => array('bar'),
                'metadata'  => array(),
                'version'   => 2,
            )
        );
    }

    public function getQuery()
    {
        $query = new Query();
        $query->where('is_draft', '=', false)
              ->where('is_public', '=', true)
              ->sort('created', 'DESC');
        return $query;
    }

    public function testEventManagerIsPopulatedByDefault()
    {
        $events = $this->resource->events();
        $this->assertInstanceOf('Zend\EventManager\EventManager', $events);
    }

    public function testCanInjectEventManagerObject()
    {
        $events = new EventManager();
        $this->resource->events($events);
        $test = $this->resource->events();
        $this->assertSame($events, $test);
    }

    public function testIsAclResource()
    {
        $this->assertInstanceOf('Zend\Acl\Resource', $this->resource);
    }

    public function testUsesClassNameAsAclResourceIdentifier()
    {
        $this->assertEquals(get_class($this->resource), $this->resource->getResourceId());
    }

    public function testGetAllReturnsCollection()
    {
        $test = $this->resource->getAll();
        $this->assertInstanceOf('CommonResource\Resource\Collection', $test);
    }

    public function testEventManagerCanShortCircuitGetAllExecutionIfItReturnsACollection()
    {
        $items = $this->getItems();
        $this->dataSource->when(new Query(), $items);
        $item  = $this->getItem();
        $collection = new Collection(array($item), 'Blog\EntryEntity');
        $this->resource->events()->attach('getAll.pre', function($e) use ($collection) {
            return $collection;
        });
        $test = $this->resource->getAll();
        $this->assertSame($collection, $test);
        $this->assertNotEquals(count($items), count($test));
    }

    public function testGetAllTriggersPostActionWithItemsFound()
    {
        $items  = $this->getItems();
        $this->dataSource->when(new Query(), $items);

        $result = new stdClass();
        $this->resource->events()->attach('getAll.post', function($e) use ($result) {
            $result->items = $e->getParam('items', false);
        });

        $collection = $this->resource->getAll();
        $this->assertObjectHasAttribute('items', $result);
        $this->assertSame($collection, $result->items);
    }

    public function testGetReturnsNullIfItemNotFound()
    {
        $this->assertNull($this->resource->get('foo'));
    }

    public function testGetReturnsEntryWhenFound()
    {
        $item = $this->getItem();
        $this->dataSource->create($item);
        $test = $this->resource->get('some-slug');
        $this->assertInstanceOf('Blog\EntryEntity', $test);
    }

    public function testEventManagerCanShortCircuitGetExecutionIfItReturnsAnEntry()
    {
        $item  = $this->getItem();
        $entry = $this->resource->create($item);

        $test  = new EntryEntity();
        $this->resource->events()->attach('get.pre', function ($e) use ($test) {
            return $test;
        });
        $receive = $this->resource->get($entry->getId());
        $this->assertSame($test, $receive);
    }

    public function testPostGetEventTriggeredWithEntry()
    {
        $item  = $this->getItem();
        $entry = $this->resource->create($item);
        $test  = new EntryEntity();
        $this->resource->events()->attach('get.post', function ($e) use ($test) {
            if (!$entry = $e->getParam('entity', false)) {
                return;
            }
            $test->fromArray($entry->toArray());
        });
        $receive = $this->resource->get($entry->getId());
        $this->assertSame($receive->toArray(), $test->toArray());
    }

    public function testPostGetEventNotTriggeredIfEntryNotFound()
    {
        $test  = new stdClass;
        $this->resource->events()->attach('get.post', function ($e) use ($test) {
            if (!$entry = $e->getParam('entity', false)) {
                return;
            }
            $test->entry = $entry;
        });
        $this->assertNull($this->resource->get('foo-bar'));
        $this->assertFalse(isset($test->entry));
    }

    public function testCreateReturnsEntryWithValidArrayData()
    {
        $item  = $this->getItem();
        $entry = $this->resource->create($item);
        $this->assertInstanceOf('Blog\EntryEntity', $entry);
    }

    public function testCreateAcceptsEntryObject()
    {
        $item  = $this->getItem();
        $entry = new EntryEntity();
        $entry->fromArray($item);
        $test = $this->resource->create($entry);
        $this->assertInstanceOf('Blog\EntryEntity', $test);
        $this->assertSame($entry, $test);
    }

    public function testCreateReturnsInputFilterOnArraySpecWithInvalidData()
    {
        $test = $this->resource->create(array());
        $this->assertInstanceOf('Zend\Filter\InputFilter', $test);
    }

    public function testCreateReturnsInputFilterForInvalidEntry()
    {
        $entry = new EntryEntity();
        $test = $this->resource->create($entry);
        $this->assertInstanceOf('Zend\Filter\InputFilter', $test);
    }

    public function testCreateThrowsExceptionWithInvalidSpec()
    {
        $this->setExpectedException('InvalidArgumentException', 'array or object');
        $this->resource->create('foo');
    }

    public function testCreateTriggersPreCreateEvent()
    {
        $o = new stdClass();
        $this->resource->events()->attach('create.pre', function ($e) use ($o) {
            if (!$entry = $e->getParam('spec', false)) {
                return;
            }
            $o->entry = $entry;
        });
        $entry = $this->resource->create($this->getItem());
        $this->assertObjectHasAttribute('entry', $o);
        $this->assertSame($entry, $o->entry);
    }

    public function testCreateTriggersPostCreateEvent()
    {
        $o = new stdClass();
        $this->resource->events()->attach('create.post', function ($e) use ($o) {
            if (!$entry = $e->getParam('entity', false)) {
                return;
            }
            $o->entry = $entry;
        });
        $entry = $this->resource->create($this->getItem());
        $this->assertObjectHasAttribute('entry', $o);
        $this->assertSame($entry, $o->entry);
    }

    public function testUpdateRaisesExceptionOnNonexistentItem()
    {
        $this->setExpectedException('DomainException', 'does not exist');
        $this->resource->update('foo', array('title' => 'updated title'));
    }

    public function testUpdateRaisesExceptionOnInvalidSpecification()
    {
        $item = $this->getItem();
        $this->dataSource->create($item);
        $this->setExpectedException('InvalidArgumentException', 'Expected an array or object');
        $this->resource->update('some-slug', 'foobar');
    }

    public function testUpdateTriggersPreUpdateEvent()
    {
        $this->resource->events()->attach('update.pre', function($e) {
            $id   = $e->getParam('id', false);
            $spec = $e->getParam('spec', new \ArrayObject());
            throw new \Exception(sprintf(
                'Received id "%s" and spec "%s"',
                $id,
                implode(':', $spec->getArrayCopy())
            ));
        });
        $item = $this->getItem();
        $this->dataSource->create($item);
        $this->setExpectedException('Exception', 'id "some-slug" and spec "some title"');
        $this->resource->update('some-slug', array('title' => 'some title'));
    }

    public function testPreUpdateEventReceivesReferenceToSpecArray()
    {
        $item = $this->getItem();
        $this->dataSource->create($item);

        $this->resource->events()->attach('update.pre', function($e) {
            if (!$spec = $e->getParam('spec', false)) {
                return;
            }
            $spec['updated'] = strtotime('tomorrow');
        });

        $entry    = $this->resource->update('some-slug', array('title' => 'some title'));
        $expected = strtotime('tomorrow');
        $this->assertEquals($expected, $entry->getUpdated());
    }

    public function testPostUpdateEventsAreTriggered()
    {
        $o = new stdClass;
        $this->resource->events()->attach('update.post', function($e) use ($o) {
            if (!$entry = $e->getParam('entity')) {
                return;
            }
            $o->entry = $entry;
        });

        $item = $this->getItem();
        $this->dataSource->create($item);
        $entry = $this->resource->update('some-slug', array('title' => 'some title'));

        $this->assertObjectHasAttribute('entry', $o);
        $this->assertSame($entry, $o->entry);
    }

    public function testDeleteReturnsFalseIfEntryDoesNotExist()
    {
        $this->assertFalse($this->resource->delete('foo-bar'));
    }

    public function testDeleteRemovesEntryIfItExists()
    {
        $item = $this->getItem();
        $this->dataSource->create($item);
        $this->assertTrue($this->resource->delete($item['id']));
        $this->assertNull($this->resource->get($item['id']));
    }

    public function testDeleteCanAcceptEntryObjects()
    {
        $item  = $this->getItem();
        $entry = $this->resource->create($item);
        $this->assertTrue($this->resource->delete($entry));
        $this->assertNull($this->resource->get($item['id']));
    }

    public function testDeleteTriggersPreDeleteEvent()
    {
        $item  = $this->getItem();
        $entry = $this->resource->create($item);
        $test  = new stdClass();
        $this->resource->events()->attach('delete.pre', function($e) use ($test) {
            $test->triggered = true;
        });
        $this->resource->delete($item['id']);
        $this->assertObjectHasAttribute('triggered', $test);
    }

    public function testPreDeleteEventHandlerCanCircumventDeletion()
    {
        $item  = $this->getItem();
        $entry = $this->resource->create($item);
        $this->resource->events()->attach('delete.pre', function($e) {
            return true;
        });
        $this->assertTrue($this->resource->delete($item['id']));
        $test = $this->resource->get($item['id']);
        $this->assertInstanceOf('Blog\EntryEntity', $test);
        $this->assertEquals($entry->toArray(), $test->toArray());
    }

    public function testPostDeleteEventIsCalled()
    {
        $item  = $this->getItem();
        $entry = $this->resource->create($item);
        $test  = new stdClass;
        $this->resource->events()->attach('delete.post', function($e) use ($test) {
            if (!$id = $e->getParam('id', false)) {
                return;
            }
            $test->id = $id;
        });
        $this->assertTrue($this->resource->delete($item['id']));
        $this->assertObjectHasAttribute('id', $test);
        $this->assertEquals($item['id'], $test->id);
    }

    public function runQueryComparisonTests($query, $method)
    {
        $args  = func_get_args();
        $args  = array_slice($args, 2);
        $items = $this->getItems();
        $this->dataSource->when($query, $items);
        $collection = call_user_func_array(array($this->resource, $method), $args);
        $test = array();
        foreach ($collection as $entity) {
            $test[] = $entity->toArray();
        }
        $message = "Expected: " . var_export($items, 1) . "\n"
                 . "Received: " . var_export($test, 1) . "\n";
        $this->assertEquals($items, $test, $message);
    }

    public function testCanGetRecentEntries()
    {
        $query = $this->getQuery();
        $query->where('created', '<=', $_SERVER['REQUEST_TIME'])
              ->limit(15, 0);
        $this->runQueryComparisonTests($query, 'getEntries', 0, 15);
    }

    public function testCanGetEntriesByYear()
    {
        $query = $this->getQuery();
        $start = new DateTime('2007-01-01');
        $end   = clone $start;
        $end->add(new DateInterval('P1Y'));

        $query->where('created', '>=', $start->getTimestamp())
              ->where('created', '<', $end->getTimestamp())
              ->limit(15, 0);
        $this->runQueryComparisonTests($query, 'getEntriesByYear', 2007, 0, 15);
    }

    public function testFetchingEntriesByYearWillNotSetEndDateGreaterThanCurrentDate()
    {
        $query = $this->getQuery();
        $start = new DateTime(date('Y') . '-01-01');

        $query->where('created', '>=', $start->getTimestamp())
              ->where('created', '<', $_SERVER['REQUEST_TIME'])
              ->limit(15, 0);
        $this->runQueryComparisonTests($query, 'getEntriesByYear', date('Y'), 0, 15);
    }

    public function testCanGetEntriesByMonth()
    {
        $query = $this->getQuery();
        $start = new DateTime('2007-01-01');
        $end   = clone $start;
        $end->add(new DateInterval('P1M'));

        $query->where('created', '>=', $start->getTimestamp())
              ->where('created', '<', $end->getTimestamp())
              ->limit(15, 0);
        $this->runQueryComparisonTests($query, 'getEntriesByMonth', 1, 2007, 0, 15);
    }

    public function testFetchingEntriesByMonthWillNotSetEndDateGreaterThanCurrentDate()
    {
        $query = $this->getQuery();
        $start = new DateTime(date('Y-m') . '-01');

        $query->where('created', '>=', $start->getTimestamp())
              ->where('created', '<', $_SERVER['REQUEST_TIME'])
              ->limit(15, 0);
        $this->runQueryComparisonTests($query, 'getEntriesByMonth', date('n'), date('Y'), 0, 15);
    }

    public function testCanGetEntriesByDay()
    {
        $query = $this->getQuery();
        $start = new DateTime('2007-01-01');
        $end   = clone $start;
        $end->add(new DateInterval('P1D'));

        $query->where('created', '>=', $start->getTimestamp())
              ->where('created', '<', $end->getTimestamp())
              ->limit(15, 0);
        $this->runQueryComparisonTests($query, 'getEntriesByDay', 1, 1, 2007, 0, 15);
    }

    public function testFetchingEntriesByDayWillNotSetEndDateGreaterThanCurrentDate()
    {
        $query = $this->getQuery();
        $start = new DateTime('tomorrow');

        $query->where('created', '>=', $start->getTimestamp())
              ->where('created', '<', $_SERVER['REQUEST_TIME'])
              ->limit(15, 0);
        $this->runQueryComparisonTests($query, 'getEntriesByDay', $start->format('j'), $start->format('n'), $start->format('Y'), 0, 15);
    }

    public function testCanGetEntriesByTag()
    {
        $query = $this->getQuery();
        $query->where('tags', '=', 'foo')
              ->where('created', '<=', $_SERVER['REQUEST_TIME'])
              ->limit(15, 0);
        $this->runQueryComparisonTests($query, 'getEntriesByTag', 'foo', 0, 15);
    }

    public function testStaticHandlersAreExecuted()
    {
        $test = new stdClass();
        $test->get         = false;
        $test->getAbstract = false;
        $test->getEntries  = false;

        StaticEventManager::getInstance()->attach('Blog\EntryResource', 'get.pre', function($e) use ($test) {
            $test->get = true;
        });
        StaticEventManager::getInstance()->attach('CommonResource\Resource\AbstractResource', 'get.pre', function($e) use ($test) {
            $test->getAbstract = true;
        });
        StaticEventManager::getInstance()->attach('Blog\EntryResource', 'getEntries.pre', function($e) use ($test) {
            $test->getEntries = true;
        });
        $this->resource->get('foo');
        $this->assertTrue($test->get);
        $this->assertTrue($test->getAbstract);
        $this->resource->getEntries();
        $this->assertTrue($test->getEntries);
    }
}
