-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 08, 2014 at 06:36 
-- Server version: 5.6.12
-- PHP Version: 5.5.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `timeliner`
--
CREATE DATABASE IF NOT EXISTS `timeliner` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `timeliner`;

-- --------------------------------------------------------

--
-- Table structure for table `map_parts`
--

CREATE TABLE IF NOT EXISTS `map_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `gps_set` tinyint(1) NOT NULL,
  `gps_1` varchar(255) NOT NULL,
  `gps_2` varchar(255) NOT NULL,
  `gps_3` varchar(255) NOT NULL,
  `pixel_1` text NOT NULL,
  `pixel_2` text NOT NULL,
  `pixel_3` text NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `map_parts`
--

INSERT INTO `map_parts` (`id`, `name`, `gps_set`, `gps_1`, `gps_2`, `gps_3`, `pixel_1`, `pixel_2`, `pixel_3`, `timestamp`) VALUES
(10, 'Map_Großherzogliche_Residenzstadt_Darmstadt.png', 1, '49.87319,8.65604', '49.87794,8.65145', '', '506,424', '360,179', '', -2147483648),
(12, 'Darmstadt-Hofmeierei_1850.jpg', 1, '49.87319,8.65604', '49.87794,8.65145', '', '1563,1304', '809,192', '', -2147483648),
(13, 'Fr_darmstadt_1874_heberer_gross.jpg', 1, '49.87319,8.65604', '49.87794,8.65145', '', '1477,1710', '1148,1174', '', -3029446800),
(14, 'Fr_residenz1836_gross.jpg', 1, '49.87319,8.65604', '49.87794,8.65145', '', '1044,768', '744,297', '', -4228678800),
(15, 'Fr_plan1908_gross.jpg', 1, '49.87319,8.65604', '49.87794,8.65145', '', '686,974', '581,809', '', -1956618000);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
