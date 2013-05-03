#!/bin/sh
export PHP_IDE_CONFIG="serverName=engine.demo.openconext.org"
export XDEBUG_CONFIG="idekey=PhpStorm, remote_connect_back=0, remote_host=172.18.5.1"
VVERBOSE=true APP_INCLUDE=bin/init.php QUEUE=logintracking,consent ./bin/pollResque.php