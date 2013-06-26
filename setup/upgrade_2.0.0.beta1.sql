REPLACE INTO `settings` (`name`, `value`) VALUES ('version', '2.0.0.beta1');

CREATE TABLE `users_images` (
    `id` int(11) NOT NULL auto_increment,
    `user_id` int(11) NOT NULL,
    `image` mediumblob NOT NULL,
    `mime` varchar(15) NOT NULL,
    `cookie` char(32) NOT NULL,
    PRIMARY KEY  (`id`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

ALTER TABLE `users_images`
  ADD CONSTRAINT `users_images_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

CREATE TABLE `profiles` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

ALTER TABLE `profiles` ADD CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `fields_values` ADD `profile_id` INT NOT NULL AFTER `user_id`;
ALTER TABLE `fields_values`ADD INDEX ( `profile_id` );

ALTER TABLE `fields_values` DROP FOREIGN KEY `fields_values_ibfk_1` ;
ALTER TABLE `fields_values` DROP PRIMARY KEY;
ALTER TABLE `fields_values` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
ALTER TABLE `fields_values` ADD INDEX ( `user_id` );
ALTER TABLE `fields_values` ADD FOREIGN KEY ( `user_id` ) REFERENCES `users` (`id`) ON DELETE CASCADE ;

ALTER TABLE `users` ADD `last_login` DATETIME NOT NULL AFTER `registration_date`;

ALTER TABLE `users` ADD `auth_type` TINYINT NOT NULL DEFAULT '0' AFTER `last_login`;
ALTER TABLE `users` ADD `yubikey_publicid` VARCHAR( 50 ) NOT NULL AFTER `password_changed`;
