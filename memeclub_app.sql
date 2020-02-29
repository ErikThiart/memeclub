-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 29, 2020 at 08:41 PM
-- Server version: 10.2.27-MariaDB
-- PHP Version: 7.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `memeclub_app`
--
CREATE DATABASE IF NOT EXISTS `memeclub_app` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `memeclub_app`;

-- --------------------------------------------------------

--
-- Table structure for table `memes`
--
-- Creation: Jul 18, 2019 at 05:25 PM
--

DROP TABLE IF EXISTS `memes`;
CREATE TABLE IF NOT EXISTS `memes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `meme_impressions`
--
-- Creation: Jul 18, 2019 at 05:25 PM
--

DROP TABLE IF EXISTS `meme_impressions`;
CREATE TABLE IF NOT EXISTS `meme_impressions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meme_name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `meme_votes`
--
-- Creation: Jul 22, 2019 at 08:55 PM
--

DROP TABLE IF EXISTS `meme_votes`;
CREATE TABLE IF NOT EXISTS `meme_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meme_name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_type` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--
-- Creation: Jul 18, 2019 at 05:25 PM
--

DROP TABLE IF EXISTS `referrals`;
CREATE TABLE IF NOT EXISTS `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referral_id` int(11) NOT NULL COMMENT 'user who got the invite link',
  `referrer_id` int(11) NOT NULL COMMENT 'user who owns the link',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--
-- Creation: Jul 19, 2019 at 01:41 PM
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `logout_time` timestamp NULL DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL COMMENT 'time in seconds',
  `ip_address` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Creation: Jul 18, 2019 at 05:25 PM
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `activation_token` varchar(255) NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT 0,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `invite_code` varchar(255) NOT NULL,
  `cell_number` varchar(12) DEFAULT NULL,
  `first_name` varchar(150) DEFAULT NULL,
  `last_name` varchar(150) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'user',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_impressions`
--
-- Creation: Jul 18, 2019 at 05:25 PM
--

DROP TABLE IF EXISTS `user_impressions`;
CREATE TABLE IF NOT EXISTS `user_impressions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL COMMENT 'who is viewed',
  `user_id` int(11) NOT NULL COMMENT 'who viewed it',
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
