CREATE TABLE IF NOT EXISTS `#__customform` (
	`articleId` int(10) NOT NULL AUTO_INCREMENT,
	`customField1` varchar(30) NOT NULL,
	`customField2` varchar(30) NOT NULL,

  PRIMARY KEY (`articleId`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;