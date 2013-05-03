#!/bin/sh
VVERBOSE=true APP_INCLUDE=bin/init.php QUEUE=logintracking,consent ./bin/pollResque.php