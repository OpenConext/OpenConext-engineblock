-- Add voname field to login_logs table for logging the Virtual Organisation Context
ALTER TABLE `log_logins` ADD `voname` VARCHAR( 1024 ) NULL AFTER `useragent`;