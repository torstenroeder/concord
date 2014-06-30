/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `country` (
  `id` int(9) unsigned NOT NULL COMMENT 'wikipedia id',
  `country` smallint(3) unsigned NOT NULL COMMENT 'ISO-3166 numeric code',
  KEY `country` (`id`,`country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;

CREATE TABLE IF NOT EXISTS `normdaten` (
  `id` int(9) unsigned NOT NULL COMMENT 'wikipedia id',
  `pnd` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `gnd` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `lccn` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `viaf` varchar(30) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `pd` (
  `id` int(9) unsigned NOT NULL COMMENT 'wikipedia id',
  `title` varchar(120) COLLATE utf8_unicode_ci NOT NULL COMMENT 'wikipedia article name',
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT 'surname, forename',
  `altname` varchar(1000) COLLATE utf8_unicode_ci NOT NULL COMMENT 'other names',
  `description` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `born` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT 'date of birth',
  `b_um` tinyint(1) unsigned NOT NULL COMMENT '0=certain 1=approximately',
  `b_day` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `b_month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `b_year` smallint(4) NOT NULL DEFAULT '-9999',
  `b_place` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `died` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `d_um` tinyint(1) unsigned NOT NULL COMMENT '0=certain 1=approximately',
  `d_day` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `d_month` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `d_year` smallint(4) NOT NULL DEFAULT '-9999',
  `d_place` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `b_place` (`b_place`),
  FULLTEXT KEY `d_place` (`d_place`),
  FULLTEXT KEY `title_name_altname` (`title`,`name`,`altname`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `altname` (`altname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `sex` (
  `id` int(9) unsigned NOT NULL COMMENT 'wikipedia id',
  `sex` tinyint(1) unsigned NOT NULL COMMENT '1=male 2=female',
  KEY `sex` (`id`,`sex`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
