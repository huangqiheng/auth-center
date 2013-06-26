CREATE TABLE `news` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `test` tinyint(4) NOT NULL,
    `title` VARCHAR( 255 ) NOT NULL ,
    `date` DATETIME NOT NULL ,
    `excerpt` TEXT NOT NULL ,
    `content` TEXT NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `auth_attempts` (
  `id` int(11) NOT NULL auto_increment,
  `IP` varchar(15) NOT NULL,
  `session_id` varchar(32) NOT NULL,
  `failed_attempts` tinyint(4) NOT NULL,
  `last_attempt` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;

INSERT INTO `settings` (`name`, `value`) VALUES ('version', '1.1.0.beta1');

DROP TABLE `associations`;

CREATE TABLE `associations` (
  `server_url` blob NOT NULL,
  `handle` varchar(255) NOT NULL,
  `secret` blob NOT NULL,
  `issued` int(11) NOT NULL,
  `lifetime` int(11) NOT NULL,
  `assoc_type` varchar(64) NOT NULL,
  PRIMARY KEY  (`server_url`(255),`handle`)
) ENGINE=InnoDB;

CREATE TABLE `nonces` (
  `server_url` varchar(2047) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `salt` char(40) NOT NULL,
  UNIQUE KEY `server_url` (`server_url`(255),`timestamp`,`salt`)
) ENGINE=InnoDB;
