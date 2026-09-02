ALTER TABLE `products` ADD COLUMN `best_seller` TINYINT(1) NOT NULL DEFAULT 0 AFTER `upcoming`;

UPDATE `business_settings` SET `value` = '9.9.5' WHERE `business_settings`.`type` = 'current_version';

COMMIT;
