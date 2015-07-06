<?php

require_once __DIR__.'/Base.php';

use Model\Project;
use Model\User;
use Model\TaskFilter;
use Model\TaskCreation;
use Model\DateParser;
use Model\Category;
use Model\Config;

class TaskFilterTest extends Base
{
    public function testIcalEventsWithCreatorAndDueDate()
    {
        $dp = new DateParser($this->container);
        $p = new Project($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task1', 'creator_id' => 1, 'date_due' => $dp->getTimestampFromIsoFormat('-2 days'))));

        $events = $tf->create()->filterByDueDateRange(strtotime('-1 month'), strtotime('+1 month'))->addAllDayIcalEvents();
        $ics = $events->render();

        $this->assertContains('UID:task-#1-date_due', $ics);
        $this->assertContains('DTSTART;TZID=UTC;VALUE=DATE:'.date('Ymd', strtotime('-2 days')), $ics);
        $this->assertContains('DTEND;TZID=UTC;VALUE=DATE:'.date('Ymd', strtotime('-2 days')), $ics);
        $this->assertContains('URL:http://localhost/?controller=task&action=show&task_id=1&project_id=1', $ics);
        $this->assertContains('SUMMARY:#1 task1', $ics);
        $this->assertContains('ATTENDEE:MAILTO:admin@kanboard.local', $ics);
        $this->assertContains('X-MICROSOFT-CDO-ALLDAYEVENT:TRUE', $ics);
    }

    public function testIcalEventsWithAssigneeAndDueDate()
    {
        $dp = new DateParser($this->container);
        $p = new Project($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);
        $u = new User($this->container);
        $c = new Config($this->container);

        $this->assertNotFalse($c->save(array('application_url' => 'http://kb/')));
        $this->assertEquals('http://kb/', $c->get('application_url'));

        $this->assertNotFalse($u->update(array('id' => 1, 'email' => 'bob@localhost')));
        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task1', 'owner_id' => 1, 'date_due' => $dp->getTimestampFromIsoFormat('+5 days'))));

        $events = $tf->create()->filterByDueDateRange(strtotime('-1 month'), strtotime('+1 month'))->addAllDayIcalEvents();
        $ics = $events->render();

        $this->assertContains('UID:task-#1-date_due', $ics);
        $this->assertContains('DTSTART;TZID=UTC;VALUE=DATE:'.date('Ymd', strtotime('+5 days')), $ics);
        $this->assertContains('DTEND;TZID=UTC;VALUE=DATE:'.date('Ymd', strtotime('+5 days')), $ics);
        $this->assertContains('URL:http://kb/?controller=task&action=show&task_id=1&project_id=1', $ics);
        $this->assertContains('SUMMARY:#1 task1', $ics);
        $this->assertContains('ORGANIZER:MAILTO:bob@localhost', $ics);
        $this->assertContains('X-MICROSOFT-CDO-ALLDAYEVENT:TRUE', $ics);
    }

    public function testSearchWithEmptyResult()
    {
        $dp = new DateParser($this->container);
        $p = new Project($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is awesome', 'date_due' => $dp->getTimestampFromIsoFormat('-2 days'))));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is amazing', 'date_due' => $dp->getTimestampFromIsoFormat('+1 day'))));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'Bob at work', 'date_due' => $dp->getTimestampFromIsoFormat('-1 day'))));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'youpi', 'date_due' => $dp->getTimestampFromIsoFormat(time()))));

        $this->assertEmpty($tf->search('search something')->findAll());
    }

    public function testSearchById()
    {
        $p = new Project($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task1')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task2')));

        $tf->search('#2');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task2', $tasks[0]['title']);

        $tf->search('1');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task1', $tasks[0]['title']);

        $tf->search('something');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);

        $tf->search('#');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);

        $tf->search('#abcd');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);

        $tf->search('task1');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task1', $tasks[0]['title']);
    }

    public function testSearchWithReference()
    {
        $p = new Project($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task1')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task2', 'reference' => 123)));

        $tf->search('ref:123');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task2', $tasks[0]['title']);

        $tf->search('reference:123');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task2', $tasks[0]['title']);

        $tf->search('ref:plop');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);

        $tf->search('ref:');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);
    }

    public function testSearchWithStatus()
    {
        $p = new Project($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is awesome')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is amazing')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is amazing', 'is_active' => 0)));

        $tf->search('status:open');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(2, $tasks);

        $tf->search('status:plop');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(3, $tasks);

        $tf->search('status:closed');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
    }

    public function testSearchWithDescription()
    {
        $p = new Project($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task1')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task2', 'description' => '**something to do**')));

        $tf->search('description:"something"');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task2', $tasks[0]['title']);

        $tf->search('description:"rainy day"');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);
    }

    public function testSearchWithCategory()
    {
        $p = new Project($this->container);
        $c = new Category($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertEquals(1, $c->create(array('name' => 'Feature request', 'project_id' => 1)));
        $this->assertEquals(2, $c->create(array('name' => 'hé hé', 'project_id' => 1)));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task1')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task2', 'category_id' => 1)));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task3', 'category_id' => 2)));

        $tf->search('category:"Feature request"');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task2', $tasks[0]['title']);
        $this->assertEquals('Feature request', $tasks[0]['category_name']);

        $tf->search('category:"hé hé"');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task3', $tasks[0]['title']);
        $this->assertEquals('hé hé', $tasks[0]['category_name']);

        $tf->search('category:"Feature request" category:"hé hé"');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(2, $tasks);
        $this->assertEquals('task2', $tasks[0]['title']);
        $this->assertEquals('Feature request', $tasks[0]['category_name']);
        $this->assertEquals('task3', $tasks[1]['title']);
        $this->assertEquals('hé hé', $tasks[1]['category_name']);

        $tf->search('category:none');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task1', $tasks[0]['title']);
        $this->assertEquals('', $tasks[0]['category_name']);

        $tf->search('category:"not found"');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);
    }

    public function testSearchWithProject()
    {
        $p = new Project($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'My project A')));
        $this->assertEquals(2, $p->create(array('name' => 'My project B')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task1')));
        $this->assertNotFalse($tc->create(array('project_id' => 2, 'title' => 'task2')));

        $tf->search('project:"My project A"');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task1', $tasks[0]['title']);
        $this->assertEquals('My project A', $tasks[0]['project_name']);

        $tf->search('project:2');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task2', $tasks[0]['title']);
        $this->assertEquals('My project B', $tasks[0]['project_name']);

        $tf->search('project:"My project A" project:"my project b"');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(2, $tasks);
        $this->assertEquals('task1', $tasks[0]['title']);
        $this->assertEquals('My project A', $tasks[0]['project_name']);
        $this->assertEquals('task2', $tasks[1]['title']);
        $this->assertEquals('My project B', $tasks[1]['project_name']);

        $tf->search('project:"not found"');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);
    }

    public function testSearchWithColumn()
    {
        $p = new Project($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'My project A')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task1')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'task2', 'column_id' => 3)));

        $tf->search('column:Backlog');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('task1', $tasks[0]['title']);
        $this->assertEquals('Backlog', $tasks[0]['column_name']);

        $tf->search('column:backlog column:"Work in progress"');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(2, $tasks);
        $this->assertEquals('task1', $tasks[0]['title']);
        $this->assertEquals('Backlog', $tasks[0]['column_name']);
        $this->assertEquals('task2', $tasks[1]['title']);
        $this->assertEquals('Work in progress', $tasks[1]['column_name']);

        $tf->search('column:"not found"');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);
    }

    public function testSearchWithDueDate()
    {
        $dp = new DateParser($this->container);
        $p = new Project($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is awesome', 'date_due' => $dp->getTimestampFromIsoFormat('-2 days'))));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is amazing', 'date_due' => $dp->getTimestampFromIsoFormat('+1 day'))));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'Bob at work', 'date_due' => $dp->getTimestampFromIsoFormat('-1 day'))));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'youpi', 'date_due' => $dp->getTimestampFromIsoFormat(time()))));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'no due date')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'due date at 0', 'date_due' => 0)));

        $tf->search('due:>'.date('Y-m-d'));
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('my task title is amazing', $tasks[0]['title']);

        $tf->search('due:>='.date('Y-m-d'));
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(2, $tasks);
        $this->assertEquals('my task title is amazing', $tasks[0]['title']);
        $this->assertEquals('youpi', $tasks[1]['title']);

        $tf->search('due:<'.date('Y-m-d'));
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(2, $tasks);
        $this->assertEquals('my task title is awesome', $tasks[0]['title']);
        $this->assertEquals('Bob at work', $tasks[1]['title']);

        $tf->search('due:<='.date('Y-m-d'));
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(3, $tasks);
        $this->assertEquals('my task title is awesome', $tasks[0]['title']);
        $this->assertEquals('Bob at work', $tasks[1]['title']);
        $this->assertEquals('youpi', $tasks[2]['title']);

        $tf->search('due:tomorrow');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('my task title is amazing', $tasks[0]['title']);

        $tf->search('due:yesterday');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('Bob at work', $tasks[0]['title']);

        $tf->search('due:today');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('youpi', $tasks[0]['title']);
    }

    public function testSearchWithColor()
    {
        $p = new Project($this->container);
        $u = new User($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertEquals(2, $u->create(array('username' => 'bob', 'name' => 'Bob Ryan')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is awesome', 'color_id' => 'light_green')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is amazing', 'color_id' => 'blue')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'Bob at work')));

        $tf->search('color:"Light Green"');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('my task title is awesome', $tasks[0]['title']);

        $tf->search('color:"Light Green" amazing');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);

        $tf->search('color:"plop');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);

        $tf->search('color:unknown');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(3, $tasks);

        $tf->search('color:blue amazing');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('my task title is amazing', $tasks[0]['title']);

        $tf->search('color:blue color:Yellow');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(2, $tasks);
        $this->assertEquals('my task title is amazing', $tasks[0]['title']);
        $this->assertEquals('Bob at work', $tasks[1]['title']);
    }

    public function testSearchWithAssignee()
    {
        $p = new Project($this->container);
        $u = new User($this->container);
        $tc = new TaskCreation($this->container);
        $tf = new TaskFilter($this->container);

        $this->assertEquals(1, $p->create(array('name' => 'test')));
        $this->assertEquals(2, $u->create(array('username' => 'bob', 'name' => 'Bob Ryan')));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is awesome', 'owner_id' => 1)));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'my task title is amazing', 'owner_id' => 0)));
        $this->assertNotFalse($tc->create(array('project_id' => 1, 'title' => 'Bob at work', 'owner_id' => 2)));

        $tf->search('assignee:john');
        $tasks = $tf->findAll();
        $this->assertEmpty($tasks);

        $tf->search('assignee:admin my task title');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('my task title is awesome', $tasks[0]['title']);

        $tf->search('my task title');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(2, $tasks);
        $this->assertEquals('my task title is awesome', $tasks[0]['title']);
        $this->assertEquals('my task title is amazing', $tasks[1]['title']);

        $tf->search('my task title assignee:nobody');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(1, $tasks);
        $this->assertEquals('my task title is amazing', $tasks[0]['title']);

        $tf->search('assignee:"Bob ryan" assignee:nobody');
        $tasks = $tf->findAll();
        $this->assertNotEmpty($tasks);
        $this->assertCount(2, $tasks);
        $this->assertEquals('my task title is amazing', $tasks[0]['title']);
        $this->assertEquals('Bob at work', $tasks[1]['title']);
    }

    public function testCopy()
    {
        $tf = new TaskFilter($this->container);
        $filter1 = $tf->create();
        $filter2 = $tf->copy();

        $this->assertTrue($filter1 !== $filter2);
        $this->assertTrue($filter1->query !== $filter2->query);
        $this->assertTrue($filter1->query->condition !== $filter2->query->condition);
    }
}
