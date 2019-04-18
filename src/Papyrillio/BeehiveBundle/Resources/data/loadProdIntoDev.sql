TRUNCATE TABLE `bl_dev`.`task`;
TRUNCATE TABLE `bl_dev`.`index_entry`;
TRUNCATE TABLE `bl_dev`.`ext_translations`;
TRUNCATE TABLE `bl_dev`.`ext_log_entries`;
TRUNCATE TABLE `bl_dev`.`correction`;
TRUNCATE TABLE `bl_dev`.`edition`;
TRUNCATE TABLE `bl_dev`.`compilation`;
TRUNCATE TABLE `bl_dev`.`user`;


SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `bl_dev`.`user`
SELECT * FROM `bl`.`user`;

INSERT INTO `bl_dev`.`compilation`
SELECT * FROM `bl`.`compilation`;

INSERT INTO `bl_dev`.`edition`
SELECT * FROM `bl`.`edition`;

INSERT INTO `bl_dev`.`correction`
SELECT * FROM `bl`.`correction`;

INSERT INTO `bl_dev`.`ext_log_entries`
SELECT * FROM `bl`.`ext_log_entries`;

INSERT INTO `bl_dev`.`ext_translations`
SELECT * FROM `bl`.`ext_translations`;

INSERT INTO `bl_dev`.`index_entry`
SELECT * FROM `bl`.`index_entry`;

INSERT INTO `bl_dev`.`task`
SELECT * FROM `bl`.`task`;