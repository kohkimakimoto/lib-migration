---
layout: default
title: LibMigration Command
---

## Commands

`phpmigrate` is a executable command line interface of LibMigration.

### syntax

<pre class="sh">
phpmigrate [-h|-d|-f|-c] COMMAND
</pre>

### Command line Options

#### -d

Switch the debug mode to output log on the debug level.

Exsample:
<pre class="sh">
$ phpmigrate -d
DEBUG &gt;&gt; Getting schema version from 'db1'
[db1] Current schema version is 0
[db1] Your migrations yet to be executed are below.
</pre>
#### -h

List available command line options.

#### -f=FILE

Specify to load configuration file.

#### -c

List configurations.

Exsample:
<pre class="sh">
$ phpmigrate -c
Configurations :
  [config_file]                        =&gt; migration.php
  [debug]                              =&gt;
  [colors]                             =&gt; 1
  [databases/db1/database_dsn]         =&gt; mysql:dbname=yourdatabase;host=localhost
  [databases/db1/database_user]        =&gt; user
  [databases/db1/database_password]    =&gt; password
  [databases/db1/schema_version_table] =&gt; schema_version
</pre>

### commands

#### init

Create skeleton configuration file in the current working directory.

#### create NAME \[DATABASENAME ...\]

Create new skeleton migration file.

Exsample:
<pre class="sh">
$ phpmigrate create alter_table1
[db1] Created ./databases/db1/20130703025128_db1_alter_table1.php
</pre>

#### status \[DATABASENAME ...\]

List the migrations yet to be executed.

Exsample:
<pre class="sh">
$ phpmigrate status
[db1] Current schema version is 0
[db1] Your migrations yet to be executed are below.
20130703020613_db1_migration1.php
20130703020616_db1_migration2.php
20130703020617_db1_migration3.php
</pre>

#### migrate \[DATABASENAME ...\]

Execute the next migrations up.

Exsample:
<pre class="sh">
$ phpmigrate up
[db1] Current schema version is 0
[db1] Proccesing migrate up by 20130703020613_db1_migration1.php
[db1] Proccesing migrate up by 20130703020616_db1_migration2.php
</pre>


#### up \[DATABASENAME ...\]

Execute the next migration up.

#### down \[DATABASENAME ...\]

Execute the next migration down.

Exsample:
<pre class="sh">
$ phpmigrate down
[db1] Current schema version is 20130703020616
[db1] Proccesing migrate down to version 20130703020613 by 20130703020616_db1_migration2.php
</pre>