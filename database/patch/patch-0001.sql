SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `janus__allowedEntity` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `remoteentityid` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__arp` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `description` text,
  `attributes` text,
  `created` char(25) NOT NULL,
  `updated` char(25) NOT NULL,
  `ip` char(15) NOT NULL,
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__attribute` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `key` text NOT NULL,
  `value` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__blockedEntity` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `remoteentityid` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__disableConsent` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `remoteentityid` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__entity` (
  `eid` int(11) NOT NULL,
  `entityid` text NOT NULL,
  `revisionid` int(11) DEFAULT NULL,
  `state` text,
  `type` text,
  `expiration` char(25) DEFAULT NULL,
  `metadataurl` text,
  `allowedall` char(3) NOT NULL DEFAULT 'yes',
  `arp` int(11) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `created` char(25) DEFAULT NULL,
  `ip` char(15) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `revisionnote` text,
  UNIQUE KEY `eid` (`eid`,`revisionid`),
  UNIQUE KEY `janus__entity__eid_revisionid` (`eid`,`revisionid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__hasEntity` (
  `uid` int(11) NOT NULL,
  `eid` int(11) DEFAULT NULL,
  `created` char(25) DEFAULT NULL,
  `ip` char(15) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__message` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `subject` text NOT NULL,
  `message` text,
  `from` int(11) NOT NULL,
  `subscription` text NOT NULL,
  `read` enum('yes','no') DEFAULT 'no',
  `created` char(25) NOT NULL,
  `ip` char(15) DEFAULT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__metadata` (
  `eid` int(11) NOT NULL,
  `revisionid` int(11) NOT NULL,
  `key` text NOT NULL,
  `value` text NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL,
  UNIQUE KEY `janus__metadata__eid_revisionid_key` (`eid`,`revisionid`,`key`(50))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__subscription` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `subscription` text NOT NULL,
  `type` text,
  `created` char(25) DEFAULT NULL,
  `ip` char(15) DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(320) NOT NULL,
  `token` varchar(255) NOT NULL,
  `notvalidafter` varchar(255) NOT NULL,
  `usedat` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` text,
  `type` text,
  `email` varchar(320) DEFAULT NULL,
  `active` char(3) DEFAULT 'yes',
  `update` char(25) DEFAULT NULL,
  `created` char(25) DEFAULT NULL,
  `ip` char(15) DEFAULT NULL,
  `data` text,
  `secret` text,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `janus__userData` (
  `uid` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `update` char(25) NOT NULL,
  `created` char(25) NOT NULL,
  `ip` char(15) NOT NULL,
  UNIQUE KEY `uid` (`uid`,`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;