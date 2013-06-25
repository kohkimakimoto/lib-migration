#!/bin/bash

PHP=`which php`
GIT=`which git`

DIR=`$PHP -r "echo dirname(realpath('$0'));"`

if [ ! -d "$DIR/lib-migration" ]; then
    mkdir -p $DIR/lib-migration
fi

cd $DIR/lib-migration
if [ ! -f "composer.phar" ]; then
    curl -s http://getcomposer.org/installer 2>/dev/null | $PHP >/dev/null 2>/dev/null
else
    $PHP composer.phar self-update >/dev/null 2>/dev/null
fi


cp $DIR/skeleton/composer.json composer.json
cp $DIR/skeleton/migration.php migration.php
cp -pr $DIR/skeleton/databases databases
cp -pr $DIR/skeleton/bin bin

$PHP composer.phar install -q

rm composer.phar

cd $DIR
tar zcpf "$DIR/lib-migration.tgz" lib-migration

rm -rf lib-migration
