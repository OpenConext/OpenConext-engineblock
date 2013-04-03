# Tails the log showing only the log messages
#!/bin/sh
tail -f /var/log/messages | grep '\[Message ' | sed s/.*\\\[Message\ /\[/