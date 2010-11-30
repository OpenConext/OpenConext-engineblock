CREATE TABLE `log_logins` (
  `loginstamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `userid` varchar(255) collate utf8_unicode_ci NOT NULL,
  `spentityid` varchar(255) collate utf8_unicode_ci NOT NULL,
  `idpentityid` varchar(255) collate utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;