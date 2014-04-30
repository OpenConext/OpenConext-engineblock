-- Add keyid field to login_logs table for logging the used keypair. See OpenConext/OpenConext-engineblock#29.
ALTER TABLE `log_logins` ADD `keyid` VARCHAR( 50 ) NULL AFTER `voname`;