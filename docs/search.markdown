Advanced Search Syntax
======================

Kanboard use a simple query language for advanced search.

Example of query
----------------

This example will returns all tasks assigned to me with a due date for tomorrow and a title that contains "my title":

```
assigne:me due:tomorrow my title
```

Search by task id or title
--------------------------

- Search by task id: `#123` or `123`
- Search by task title: anything that don't match any search attributes mentioned below

Search by status
----------------

Attribute: **status**

- Query to find open tasks: `status:open`
- Query to find closed tasks: `status:closed`

Search by assignee
------------------

Attribute: **assignee**

Query with the full name:

```
assignee:"Frederic Guillot"
```

Query with the username:

```
assignee:fguillot
```

Multiple assignee lookup:

```
assignee:user1 assignee:"John Doe"
```

Kanboard will search tasks assigned to the "user1" or "John Doe".

Query for unassigned tasks:

```
assignee:nobody
```

Query for my assigned tasks

```
assignee:me
```

Search by color
---------------

Attribute: **color**

Query to search by color id:

```
color:blue
```

Query to search by color name:

```
color:"Deep Orange"
```

Search by due date
------------------

Attribute: **due**

Query to search tasks due today:

```
due:today
```

Query to search tasks due tomorrow:

```
due:tomorrow
```

Query to search tasks due yesterday:

```
due:yesterday
```

Query to search tasks due with the exact date:

```
due:2015-06-29
```

The date must use the ISO8601 format: **YYYY-MM-DD**.

Operators supported:

- Greater than: **due:>2015-06-29**
- Lower than: **due:<2015-06-29**
- Greater than or equal: **due:>=2015-06-29**
- Lower than or equal: **due:<=2015-06-29**

Search by description
---------------------

Attribute: **description**

Example: `description:"text search"`

Search by category
------------------

Attribute: **category**

- Find tasks with a specific category: `category:"Feature Request"`
- Find all tasks that have those categories: `category:"Bug" category:"Improvements"`
- Find tasks with no category assigned: `category:none`

Search by project
-----------------

Attribute: **project**

- Find tasks by project name: `project:"My project name"`
- Find tasks by project id: `project:23`
- Find tasks for several projects: `project:"My project A" project:"My project B"`

Search by column
----------------

Attribute: **column**

- Find tasks by column name: `column:"Work in progress"`
- Find tasks for several columns: `column:"Backlog" column:ready`

Search by external reference
----------------------------

The task reference is an external id of your task, by example a ticket number from another software.

- Find tasks with a reference: `ref:1234` or `reference:TICKET-1234`
