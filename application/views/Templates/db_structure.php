<?php
if ( ! defined('BASEPATH')){
	exit('No direct script access allowed');
}
$sql = <<<EOF
-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Mag 05, 2023 alle 15:37
-- Versione del server: 10.3.38-MariaDB-0ubuntu0.20.04.1-log
-- Versione PHP: 7.4.3-4ubuntu2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `{$dbname}`
--
DROP DATABASE IF EXISTS `{$dbname}`;
CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `{$dbname}`;

-- --------------------------------------------------------

--
-- Struttura della tabella `csd_sessions`
--

DROP TABLE IF EXISTS `csd_sessions`;
CREATE TABLE `csd_sessions` (
  `session_id` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0',
  `ip_address` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0',
  `user_agent` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `last_activity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_data` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `csd_login_attempts`
--

DROP TABLE IF EXISTS `csd_login_attempts`;
CREATE TABLE `csd_login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `login` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `csd_users`
--

DROP TABLE IF EXISTS `csd_users`;
CREATE TABLE `csd_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `code` tinytext NOT NULL,
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT 1,
  `banned` tinyint(1) NOT NULL DEFAULT 0,
  `ban_reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `new_password_key` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `new_password_requested` datetime DEFAULT NULL,
  `new_email` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `new_email_key` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `last_ip` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `invited_by` int(10) UNSIGNED DEFAULT 0,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `csd_user_autologin`
--

DROP TABLE IF EXISTS `csd_user_autologin`;
CREATE TABLE `csd_user_autologin` (
  `key_id` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `user_agent` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `last_ip` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


-- --------------------------------------------------------

--
-- Struttura della tabella `csd_user_profiles`
--

DROP TABLE IF EXISTS `csd_user_profiles`;
CREATE TABLE `csd_user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `options` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `csd_keys`
--

DROP TABLE IF EXISTS `csd_keys`;
CREATE TABLE `csd_keys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `key` varchar(40) NOT NULL,
  `ip_addresses` text DEFAULT NULL,
  `expiration_date` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Struttura della tabella `exampleTable`
--

DROP TABLE IF EXISTS `exampleTable`;
CREATE TABLE `exampleTable` (
  `id` int(10) UNSIGNED NOT NULL COMMENT 'n,o:1',
  `id_joinedTable` int(10) UNSIGNED NOT NULL COMMENT 'n,o:2',
  `name` tinytext DEFAULT NULL COMMENT 't,o:4',
  `unique_code` tinytext NOT NULL COMMENT 's,t,o:3',
  `insert_date` int(10) UNSIGNED NOT NULL COMMENT 'd,o:6',
  `update_date` int(10) UNSIGNED NOT NULL COMMENT 'd,o:5',
  `active` tinyint(1) DEFAULT 0 COMMENT 'n'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `exampleTable`
--

INSERT INTO `exampleTable` (`id`, `id_joinedTable`, `name`, `unique_code`, `insert_date`, `update_date`, `active`) VALUES
(1, 1, '1st value for mt', 'AbCd3', 1716213599, 1716213599, 1),
(2, 2, '2nd value for mt', 'xyz-31', 1716272696, 1716213599, 0),
(3, 2, '3th value for mt', 'kjl-qwe-54', 1716297092, 1716213599, 0),
(4, 1, '4th value for mt', 'jntla987', 1716169629, 1716169629, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `joinedTable`
--

DROP TABLE IF EXISTS `joinedTable`;
CREATE TABLE `joinedTable` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` tinytext NOT NULL COMMENT 's',
  `description` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `joinedTable`
--

INSERT INTO `joinedTable` (`id`, `name`, `description`) VALUES
(1, 'joined 1', 'Phasellus pellentesque nibh eu felis vulputate, vel luctus mauris dictum. Praesent dictum, est sagittis accumsan auctor, nisl orci porta justo, eget hendrerit ligula purus vitae sem. In vestibulum varius elit, vitae semper nulla condimentum id. Aenean blandit et ante sed eleifend. Phasellus a urna sit amet lacus viverra iaculis quis a ipsum. Fusce at lorem sit amet nunc mollis consectetur ullamcorper at dolor. Donec ultrices molestie metus id varius. Donec elementum, quam ac dapibus venenatis, neque mauris auctor ipsum, at gravida justo justo vel nulla. Pellentesque vel elit nec enim fermentum mattis vitae vel sem.'),
(2, 'joined 2', 'Maecenas mattis orci sed laoreet placerat. Sed suscipit eget velit eu faucibus. Sed facilisis aliquam dolor. Duis eget cursus mauris. Sed risus dui, accumsan quis turpis at, laoreet lacinia turpis. Etiam efficitur consequat ullamcorper. Duis sit amet egestas diam. Nullam id tellus blandit, dignissim sem sit amet, fringilla ex. Aliquam ut ullamcorper massa. Pellentesque a enim vel lectus pulvinar porta. Nulla sed augue magna. Vestibulum sit amet sapien risus.');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `csd_sessions`
--
ALTER TABLE `csd_sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- Indici per le tabelle `csd_login_attempts`
--
ALTER TABLE `csd_login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `csd_users`
--
ALTER TABLE `csd_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `CodiceUnivoco` (`code`(12));

--
-- Indici per le tabelle `csd_user_autologin`
--
ALTER TABLE `csd_user_autologin`
  ADD PRIMARY KEY (`key_id`,`user_id`);

--
-- Indici per le tabelle `csd_user_profiles`
--
ALTER TABLE `csd_user_profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `csd_keys`
--
ALTER TABLE `csd_keys`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

-- Indici per le tabelle `exampleTable`
--
ALTER TABLE `exampleTable`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `joinedTable`
--
ALTER TABLE `joinedTable`
  ADD PRIMARY KEY (`id`);
		
--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `csd_login_attempts`
--
ALTER TABLE `csd_login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `csd_users`
--
ALTER TABLE `csd_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `csd_user_profiles`
--
ALTER TABLE `csd_user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		
--
-- AUTO_INCREMENT per la tabella `csd_keys`
--
ALTER TABLE `csd_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;		
--
		
--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `exampleTable`
--
ALTER TABLE `exampleTable`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'n,o:1', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `joinedTable`
--
ALTER TABLE `joinedTable`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
		
COMMIT;

EOF
;
print $sql;
?>