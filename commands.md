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

* -d

  Switch the debug mode to output log on the debug level.

* -h

  List available command line options.

* -f=FILE

  Specify to load configuration file.

* -c

  List configurations.


### commands

* init

  Create skeleton configuration file in the current working directory.

* create NAME \[DATABASENAME ...\]

  Create new skeleton migration file.

* status \[DATABASENAME ...\]

  List the migrations yet to be executed.

* migrate \[DATABASENAME ...\]

  Execute the next migrations up.

* up \[DATABASENAME ...\]

  Execute the next migration up.

* down \[DATABASENAME ...\]

  Execute the next migration down.
