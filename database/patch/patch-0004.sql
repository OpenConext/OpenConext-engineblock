--
-- Table structure for table `emails`
--

CREATE TABLE `emails` (
  `id` bigint(20) NOT NULL auto_increment,
  `email_type` varchar(255) collate utf8_unicode_ci NOT NULL,
  `email_text` longtext collate utf8_unicode_ci NOT NULL,
  `email_from` varchar(255) collate utf8_unicode_ci NOT NULL,
  `email_subject` varchar(255) collate utf8_unicode_ci NOT NULL,
  `is_html` tinyint(1) default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email_type` (`email_type`)
) ENGINE=InnoDB COLLATE=utf8_unicode_ci;

INSERT INTO `emails` (`id`, `email_type`, `email_text`, `email_from`, `email_subject`, `is_html`)
VALUES
	(1, 'introduction_email', 'Dear {user},\n\nThis mail is a confirmation that we have created a profile for you on the SURFconext platform. Please visit https://profile.surfconext.nl to see and manage your profile. If you have any questions regarding this mail please contact help@surfconext.nl. \n\nBest regards,\nSurfconext\n', 'help@surfconext.nl', 'Welcome to SURFconext', 1);

