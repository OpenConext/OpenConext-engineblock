-- Add logo_url field to group_provider table for displaying the picture in teams
ALTER TABLE `group_provider` ADD `logo_url` VARCHAR( 1024 ) NULL AFTER `classname`;
-- Update existing content
update `group_provider` set `logo_url` = 'https://wayf-test.surfnet.nl/federate/surfnet/img/logo/avans.png' where `identifier` = 'avans';
update `group_provider` set `logo_url` = 'https://wayf-test.surfnet.nl/federate/surfnet/img/logo/hzeeland.png' where `identifier` = 'hz';

