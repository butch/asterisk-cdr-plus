CREATE TABLE IF NOT EXISTS `cdr` (
   `accountcode` varchar(20) NOT NULL default '',
   `src` varchar(100) NOT NULL,
   `dst` varchar(120) NOT NULL,
   `dcontext` varchar(120) NOT NULL,
   `clid` varchar(100) NOT NULL,
   `channel` varchar(100) NOT NULL,
   `dstchannel` varchar(160) NOT NULL,
   `lastapp` varchar(80) NOT NULL default '',
   `lastdata` varchar(140) NOT NULL,
   `calldate` datetime NOT NULL default '0000-00-00 00:00:00',
   `enddate` datetime NOT NULL default '0000-00-00 00:00:00',
   `duration` int(11) NOT NULL default '0',
   `billsec` int(11) NOT NULL default '0',
   `disposition` varchar(45) NOT NULL default '',
   `amaflags` int(11) NOT NULL default '0',
   `uuid` varchar(255) NOT NULL default '',
   `userfield` varchar(255) NOT NULL default '',
    KEY `calldate` (`calldate`),
    KEY `dst` (`dst`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;