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
COMMIT;

EOF
;
print $sql;
?>