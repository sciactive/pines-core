/* Run these queries to update your database to the latest optimized version. */

ALTER TABLE `pin_com_entity_entities` DROP INDEX `id_parent`;

ALTER TABLE `pin_com_entity_entities` DROP `parent`;

OPTIMIZE TABLE `pin_com_entity_entities`;

ALTER TABLE `pin_com_entity_data` ADD INDEX `id_name` ( `name` ( 65536 ) );

ALTER TABLE `pin_com_entity_data` CHANGE `value` `value` LONGTEXT NOT NULL;

OPTIMIZE TABLE `pin_com_entity_data`;