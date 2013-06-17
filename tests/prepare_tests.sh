#!/usr/bin/env bash

mysql -e 'create database migration_lib_test;'
mysql -e "GRANT ALL PRIVILEGES ON *.* TO test_user@'127.0.0.1' IDENTIFIED BY 'test_user';"

