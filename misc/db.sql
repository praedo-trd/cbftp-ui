-- Create syntax for TABLE 'approved'
CREATE TABLE `approved` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bookmark` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `chain` text COLLATE utf8_unicode_ci,
  `pattern` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('WILDCARD','REGEX') COLLATE utf8_unicode_ci DEFAULT 'WILDCARD',
  `hits` int(10) DEFAULT '0',
  `maxlimit` int(10) DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create syntax for TABLE 'auto_games'
CREATE TABLE `auto_games` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `season` int(5) DEFAULT NULL,
  `country` varchar(25) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT '-1',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'auto_movies'
CREATE TABLE `auto_movies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `imdbid` varchar(25) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT '-1',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'auto_tv'
CREATE TABLE `auto_tv` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `season` int(5) DEFAULT NULL,
  `country` varchar(25) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT '-1',
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`,`season`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'data_cache'
CREATE TABLE `data_cache` (
  `k` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `data` text COLLATE utf8_unicode_ci,
  `data_immutable` text COLLATE utf8_unicode_ci,
  `namespace` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `approved` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`k`),
  KEY `namespace` (`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create syntax for TABLE 'irc_message_queue'
CREATE TABLE `irc_message_queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `channel` varchar(50) DEFAULT NULL,
  `message` text,
  `processed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE 'pre'
CREATE TABLE `pre` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `rlsname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dupe_k` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dupe_season_episode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dupe_resolution` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dupe_source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dupe_codec` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rlsname` (`rlsname`),
  KEY `dupe_k` (`dupe_k`,`dupe_season_episode`,`dupe_resolution`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create syntax for TABLE 'race'
CREATE TABLE `race` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bookmark` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `chain` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `chain_complete` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `valid_sites` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rlsname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `started` tinyint(4) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `log` MEDIUMTEXT COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `rlsname` (`rlsname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `race_site` (
  `race_id` int(11) unsigned NOT NULL,
  `site` varchar(50) NOT NULL DEFAULT '',
  `created` datetime DEFAULT NULL,
  `ended` datetime DEFAULT NULL,
  PRIMARY KEY (`race_id`,`site`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
