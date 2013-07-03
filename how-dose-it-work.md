---
layout: default
title: How dose ir work? - LibMigration
---

## How dose it work?

LibMigration provides version controll for your MySQL database.
This page describes how LibMigration works on your system.

If you don't know basic usage for LibMigration and have not read [Documentation](documentation.html) yet.
Please read [Documentation](documentation.html) page before reading this page.

### Migration task class

LibMigration `create` method (or `phpmigrate create` command) creates migration task class file like the following.

<pre class="sh">
20130702170043_yourdatabase_create_sample_table.php
</pre>

it's named using the timestamp of the date your were creaed.

LibMigration sorts task class files by this timestamp to execute your schema change SQL in the correct order.

### Schema version table

LibMigration creates a special table in your database. it's called "schema version table".
it keeps the timestamp of the latest executed migration.

<pre class="javascript">
mysql> select * from schema_version;
+----------------------+
| version              |
+----------------------+
| 20130702170043       |
+----------------------+
1 row in set (0.00 sec)
</pre>

By comparing timestamp of the available migrations, LibMigration can determine the next migration to execute.

The name of "schema version table" in your database can be changed by configuration.

<pre class="javascript">
return array(
  'colors' => true,
  'databases' => array(
    'yourdatabase' => array(
      ...
      'schema_version_table' => 'schema_version',  // Set table name you like.
      ...
</pre>