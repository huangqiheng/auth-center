REPLACE INTO `settings` (`name`, `value`) VALUES ('version', '1.1.0.RC1');

ALTER TABLE `users` ADD `reminders` INT NOT NULL DEFAULT '0';
