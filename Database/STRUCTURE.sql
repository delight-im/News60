SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `_news_articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `thumbnail_state` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `streamID` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `shared_facebook_like` int(10) unsigned NOT NULL DEFAULT '0',
  `shared_facebook_share` int(10) unsigned NOT NULL DEFAULT '0',
  `shared_twitter` int(10) unsigned NOT NULL DEFAULT '0',
  `score` decimal(10,7) NOT NULL DEFAULT '0.0000000',
  `tweeted` tinyint(1) NOT NULL DEFAULT '0',
  `mediumID` int(10) unsigned NOT NULL DEFAULT '0',
  `last_facebook` int(10) unsigned NOT NULL DEFAULT '0',
  `last_score` int(10) unsigned NOT NULL DEFAULT '0',
  `randomSortID` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `link` (`link`),
  KEY `last_facebook` (`last_facebook`),
  KEY `last_score` (`last_score`),
  KEY `score` (`score`),
  KEY `date` (`date`,`mediumID`),
  KEY `tweeted` (`tweeted`),
  KEY `randomSortID` (`randomSortID`),
  KEY `streamID` (`streamID`,`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=531517 ;

CREATE TABLE IF NOT EXISTS `_news_media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `feed` varchar(255) NOT NULL,
  `avg_facebook_like` decimal(7,2) NOT NULL DEFAULT '0.00',
  `avg_facebook_share` decimal(7,2) NOT NULL DEFAULT '0.00',
  `avg_twitter` decimal(7,2) NOT NULL DEFAULT '0.00',
  `time_crawled` int(10) unsigned NOT NULL DEFAULT '0',
  `errors_occurred` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `feed` (`feed`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

CREATE TABLE IF NOT EXISTS `_news_settings` (
  `name` varchar(255) NOT NULL,
  `value` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_news_stopwords` (
  `word` varchar(255) NOT NULL,
  PRIMARY KEY (`word`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_news_streams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `score` decimal(10,7) NOT NULL DEFAULT '0.0000000',
  `tweeted` tinyint(1) NOT NULL DEFAULT '0',
  `time_origin` int(10) unsigned NOT NULL DEFAULT '0',
  `time_created` int(10) unsigned NOT NULL DEFAULT '0',
  `time_updated` int(10) unsigned NOT NULL DEFAULT '0',
  `featuredArticle` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `time_created` (`time_created`),
  KEY `tweeted` (`tweeted`),
  KEY `score` (`score`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=96528 ;

CREATE TABLE IF NOT EXISTS `_news_tweets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tweet_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `user_screenname` varchar(255) NOT NULL,
  `user_realname` varchar(255) NOT NULL DEFAULT '',
  `user_image` varchar(255) NOT NULL DEFAULT '',
  `link` varchar(255) NOT NULL,
  `link_original` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kombination` (`link`,`tweet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1381989 ;

CREATE TABLE IF NOT EXISTS `_news_twitterers` (
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `screen_name` varchar(255) NOT NULL DEFAULT '',
  `real_name` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `followers` int(10) unsigned NOT NULL DEFAULT '0',
  `following` int(10) unsigned NOT NULL DEFAULT '0',
  `fans` int(10) NOT NULL DEFAULT '0',
  `location` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL,
  `last_update` int(10) unsigned NOT NULL DEFAULT '0',
  `errors_occurred` int(10) unsigned NOT NULL DEFAULT '0',
  `twitteredThis` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `banned` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `screen_name` (`screen_name`(250)),
  KEY `selection` (`location`,`fans`,`user_id`),
  KEY `last_update` (`last_update`),
  KEY `twitteredThis` (`twitteredThis`),
  KEY `ranking` (`fans`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_news_twitterers_history` (
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `measure_day` varchar(10) NOT NULL,
  `followers` int(10) unsigned NOT NULL DEFAULT '0',
  `following` int(10) unsigned NOT NULL DEFAULT '0',
  `fans` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`measure_day`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
