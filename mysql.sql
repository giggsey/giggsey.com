
-- --------------------------------------------------------

--
-- Table structure for table `feed`
--

CREATE TABLE `feed` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `link_name` varchar(32) NOT NULL,
  `text` text NOT NULL,
  `link` varchar(255) NOT NULL,
  `timeposted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `link_name` (`link_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE `links` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `link_name` varchar(32) NOT NULL,
  `user` varchar(32) NOT NULL,
  `link_user` varchar(255) NOT NULL,
  `order` int(3) NOT NULL DEFAULT '0',
  `feed_lastaccessed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `feed_lastid` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `link_name` (`link_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `links_config`
--

CREATE TABLE `links_config` (
  `link_name` varchar(32) NOT NULL,
  `icon` varchar(32) NOT NULL,
  `link_url` varchar(255) NOT NULL,
  `feed_url` varchar(255) DEFAULT NULL,
  `update_frequency` int(4) NOT NULL DEFAULT '300',
  PRIMARY KEY (`link_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `links_config`
--

INSERT INTO `links_config` (`link_name`, `icon`, `link_url`, `feed_url`, `update_frequency`) VALUES
('Facebook', 'facebook.png', 'http://facebook.com/%user%', NULL, 300),
('Flickr', 'flickr.png', 'http://www.flickr.com/photos/%user%/', NULL, 300),
('Google', 'google.png', 'http://www.google.com/profiles/%user%', NULL, 300),
('Last.fm', 'lastfm.png', 'http://www.last.fm/user/%user%', 'http://ws.audioscrobbler.com/1.0/user/%user%/recenttracks.rss', 210),
('Linkedin', 'linkedin.png', 'http://linkedin.com/in/%user%', NULL, 300),
('Picasa', 'picasa.png', 'http://picasaweb.google.com/%user%/', 'https://picasaweb.google.com/data/feed/base/user/%user%?alt=rss&kind=album&hl=en_GB&access=public', 600),
('Reddit', 'reddit.png', 'http://www.reddit.com/user/%user%/', NULL, 600),
('Steam', 'steam.png', 'http://steamcommunity.com/id/%user%', NULL, 300),
('Twitter', 'twitter.png', 'http://twitter.com/%user%', 'http://twitter.com/statuses/user_timeline/%user%.xml', 60),
('YouTube', 'youtube.png', 'http://youtube.com/user/%user%', 'http://gdata.youtube.com/feeds/base/users/%user%/uploads?alt=rss&v=2&orderby=published&client=ytapi-youtube-profile', 600);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feed`
--
ALTER TABLE `feed`
  ADD CONSTRAINT `feed_ibfk_1` FOREIGN KEY (`link_name`) REFERENCES `links_config` (`link_name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `links`
--
ALTER TABLE `links`
  ADD CONSTRAINT `links_ibfk_1` FOREIGN KEY (`link_name`) REFERENCES `links_config` (`link_name`) ON DELETE CASCADE ON UPDATE CASCADE;
