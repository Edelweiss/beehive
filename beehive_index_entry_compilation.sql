ALTER TABLE `index_entry` ADD `tab` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `topic`, ADD `papy_new` BOOLEAN NULL AFTER `tab`, ADD `greek_new` BOOLEAN NULL AFTER `papy_new`, ADD `lemma` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `greek_new`,  ADD INDEX `index_entry_type` (`type`), ADD INDEX `index_entry_topic` (`topic`), ADD INDEX `index_entry_tab` (`tab`), ADD UNIQUE `index_entry_lemma` (`type`, `topic`, `lemma`); 

UPDATE index_entry SET papy_new = TRUE WHERE `phrase` LIKE '%*%'; /* 11 */
UPDATE index_entry SET papy_new = FALSE WHERE `phrase` NOT LIKE '%*%';
UPDATE index_entry SET greek_new = TRUE WHERE `phrase` LIKE '%†%'; /* 8 */
UPDATE index_entry SET greek_new = FALSE WHERE `phrase` NOT LIKE '%†%';

/*
†* 
† 
* 
  *
*/

UPDATE index_entry SET phrase = REPLACE(phrase, '†* ', '') WHERE `phrase` LIKE '%†* %';
UPDATE index_entry SET phrase = REPLACE(phrase, '† ',  '') WHERE `phrase` LIKE '%† %';
UPDATE index_entry SET phrase = REPLACE(phrase, '* ',  '') WHERE `phrase` LIKE '%* %';
UPDATE index_entry SET phrase = REPLACE(phrase, '  *', '') WHERE `phrase` LIKE '%  *%'; /* DOESN'T WORK?!?! */

CREATE TABLE `beehive`.`correction_index_entry` ( `correction_id` INT NOT NULL , `index_entry_id` INT NOT NULL ) ENGINE = InnoDB;
ALTER TABLE `correction_index_entry` ADD CONSTRAINT `correction_index_entry_correction` FOREIGN KEY (`correction_id`) REFERENCES `correction`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; ALTER TABLE `correction_index_entry` ADD CONSTRAINT `correction_index_entry_index_entry` FOREIGN KEY (`index_entry_id`) REFERENCES `index_entry`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; 

INSERT INTO correction_index_entry (correction_id, index_entry_id) VALUES
(1689, 50),
(1691, 50),
(1690, 50),
(1699, 50),
(1701, 50),
(1703, 50),
(1702, 50),
(460, 21),
(462, 21),
(464, 21),
(466, 21),
(720, 30),
(721, 30),
(722, 30),
(723, 30),
(1693, 54),
(1694, 54),
(1696, 54),
(1700, 54),
(1697, 60),
(1698, 60),
(1704, 60),
(1708, 60),
(1697, 63),
(1698, 63),
(1704, 63),
(1708, 63),
(4166, 108),
(3657, 108),
(459, 19),
(458, 19),
(170, 129),
(169, 129),
(384, 126),
(383, 126),
(4446, 119),
(4447, 119),
(500, 26),
(501, 26),
(1275, 124),
(1189, 124),
(1553, 36),
(1572, 36),
(1577, 43),
(1578, 43),
(1564, 37),
(1565, 37),
(4350, 118),
(290, 12),
(4165, 107),
(3708, 89),
(2997, 84),
(3878, 133),
(287, 11),
(1412, 131),
(481, 25),
(381, 128),
(1712, 145),
(2307, 139),
(4512, 122),
(3938, 90),
(1594, 47),
(1951, 80),
(1551, 35),
(4230, 114),
(2881, 83),
(4228, 113),
(1584, 45),
(4264, 115),
(985, 132),
(4539, 123),
(4707, 143),
(1937, 78),
(19, 2),
(4780, 140),
(331, 14),
(333, 16),
(4084, 94),
(4089, 106),
(4350, 134),
(1104, 138),
(1933, 75),
(1918, 142),
(400, 17),
(4097, 98),
(3296, 87),
(4087, 95),
(648, 29),
(277, 13),
(4102, 101),
(4176, 110),
(1942, 79),
(4102, 100),
(1733, 137),
(1933, 81),
(3010, 86),
(1937, 77),
(4796, 136),
(4103, 102),
(4109, 105),
(19, 8),
(19, 9),
(4512, 121),
(4111, 104),
(4000, 92),
(1952, 82),
(1594, 46),
(1569, 40),
(1569, 41),
(1535, 34),
(4223, 112),
(4793, 135),
(4092, 96),
(4000, 93),
(333, 15),
(4089, 99),
(4095, 97),
(4107, 103),
(648, 28),
(3999, 91);

DELETE FROM index_entry WHERE id IN (64,71,72,61,70,73,56,59,65,31,32,33,22,23,24,52,53,62,66,68,69,109,20,130,127,120,27,125,42,44,38);

UPDATE index_entry SET lemma = phrase;

UPDATE `index_entry` SET tab =  SUBSTRING(lemma, 1, 1);

ALTER TABLE `index_entry` CHANGE `tab` `tab` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `index_entry` CHANGE `papy_new` `papy_new` TINYINT(1) NOT NULL;
ALTER TABLE `index_entry` CHANGE `greek_new` `greek_new` TINYINT(1) NOT NULL;

/*

compilation_index_entry

alte Daten darin verewigen

Dann weiter im Excel-Sheet mit den neuen index_entry-Daten

(und dann Verweise)

*/