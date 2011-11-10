-- Add useragent field to login_logs table for logging the User-Agent
ALTER TABLE `log_logins` ADD `useragent` VARCHAR( 1024 ) NULL AFTER `idpentityname`;