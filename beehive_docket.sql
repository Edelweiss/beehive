-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 09. Nov 2023 um 01:45
-- Server-Version: 10.4.11-MariaDB
-- PHP-Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `beehive`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `docket`
--

CREATE TABLE `docket` (
  `id` int(11) NOT NULL,
  `text` varchar(64) DEFAULT NULL,
  `position` text DEFAULT NULL,
  `info` text NOT NULL,
  `type` varchar(64) NOT NULL,
  `sort` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `docket`
--

INSERT INTO `docket` (`id`, `text`, `position`, `info`, `type`, `sort`) VALUES
(1, NULL, NULL, 'Siehe jetzt O. Tait', 'reference', 0),
(2, NULL, NULL, 'Für alle Neudrucke, Photos und Berichtigungen bis zu Januar 1978, siehe die Listen in P.L. Bat 21, S. 6-10 und S. 98-103.', 'preamble', 0),
(3, NULL, 'Photos', 'Konkordanz der publizierten Texten und Photos:\r\nNr.  118 Planche  I\r\nNr.  123 Planche  VII\r\nNr.  128 Planche  XIV-XV\r\nNr.  119 Planche  II\r\nNr.  124* Planche  VIII\r\nNr.  129 Planche  XVI\r\nNr.  120 Planche  III-IV\r\nNr.  125 Planche  IX-X\r\nNr.  130* Planche  II\r\nNr.  121 Planche  V\r\nNr.  126 Planche  XI-XII\r\nNr.  131* Planche  XVII\r\nNr.  122 Planche  VI\r\nNr.  127 Planche  XIII\r\nNr.  132 Planche  XVIII\r\nNr.  133 Planche  XIX\r\nNr.  138* Planche  XXV\r\nNr.  143 Planche  XXX\r\nNr.  134 Planche  XX\r\nNr.  139* Planche  XXVI\r\nNr.  144 Planche  XXXI\r\nNr.  135 Planche  XXI\r\nNr.  140 Planche  XXVII\r\nNr.  145* Planche  XXXII\r\nNr.  136 Planche  XXII-XXIII\r\nNr.  141* Planche  XXVIII\r\nNr.  146 Planche  XXVIII\r\nNr.  137* Planche  XXIV\r\nNr.  142* Planche  XXIX\r\n\r\nVon den hier mit einem * bezeichneten Texten sind auch Photos in der ed.pr. publiziert.', 'preamble', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `edition_docket`
--

CREATE TABLE `edition_docket` (
  `edition_id` int(11) NOT NULL,
  `docket_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `edition_docket`
--

INSERT INTO `edition_docket` (`edition_id`, `docket_id`) VALUES
(71, 2),
(72, 2),
(73, 2),
(74, 2),
(75, 2),
(569, 1),
(570, 1),
(582, 1),
(175, 3);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `docket`
--
ALTER TABLE `docket`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `edition_docket`
--
ALTER TABLE `edition_docket`
  ADD KEY `edition_id` (`edition_id`,`docket_id`),
  ADD KEY `edition_docket_d` (`docket_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `docket`
--
ALTER TABLE `docket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `edition_docket`
--
ALTER TABLE `edition_docket`
  ADD CONSTRAINT `edition_docket_d` FOREIGN KEY (`docket_id`) REFERENCES `docket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `edition_docket_e` FOREIGN KEY (`edition_id`) REFERENCES `edition` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
