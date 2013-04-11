#!/bin/sh
# Tails the log showing only the log messages
tail -f /var/log/messages | grep '\[Message ' | sed s/.*\\\[Message\ /\[/