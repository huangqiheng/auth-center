ALTER TABLE `associations` DROP PRIMARY KEY;

ALTER TABLE `associations` ADD PRIMARY KEY ( `handle` );

REPLACE INTO `settings` (`name`, `value`) VALUES ('version', '1.1.0');
