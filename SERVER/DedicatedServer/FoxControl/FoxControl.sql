-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 11. Februar 2011 um 20:21
-- Server Version: 5.1.41
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `FoxControl`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `playerlogin` varchar(50) NOT NULL,
  `rights` smallint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=280 ;

--
-- Daten für Tabelle `admins`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `karma`
--

CREATE TABLE IF NOT EXISTS `karma` (
  `challengeid` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `challengename` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `playerlogin` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `vote` smallint(6) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `karma`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `playerlogin` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `nickname` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `lastconnect` int(30) NOT NULL,
  `timeplayed` int(30) NOT NULL,
  `donations` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=120 ;

--
-- Daten für Tabelle `players`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `records`
--

CREATE TABLE IF NOT EXISTS `records` (
  `challengeid` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `playerlogin` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `nickname` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(11) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `records`
--


-- --------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
