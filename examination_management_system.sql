-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2024 at 09:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `exam_management_system`
--
CREATE DATABASE IF NOT EXISTS `exam_management_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `exam_management_system`;

-- --------------------------------------------------------

--
-- Table structure for table `choose`
--

CREATE TABLE `choose` (
  `sid` varchar(100) NOT NULL,
  `did` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `dates`
--

CREATE TABLE `dates` (
  `did` varchar(100) NOT NULL,
  `starttime` time DEFAULT NULL,
  `endtime` time DEFAULT NULL,
  `dates` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dates`
--



-- --------------------------------------------------------

--
-- Table structure for table `exam`
--

CREATE TABLE `exam` (
  `eid` varchar(100) NOT NULL,
  `ename` varchar(100) DEFAULT NULL,
  `fees` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam`
--



-- --------------------------------------------------------

--
-- Table structure for table `has_dates`
--

CREATE TABLE `has_dates` (
  `eid` varchar(100) NOT NULL,
  `did` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `has_dates`
--



-- --------------------------------------------------------

--
-- Table structure for table `has_questions`
--

CREATE TABLE `has_questions` (
  `eid` varchar(100) NOT NULL,
  `qid` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `has_questions`
--



-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `qid` varchar(100) NOT NULL,
  `qcontent` varchar(100) DEFAULT NULL,
  `qsolutions` varchar(100) DEFAULT NULL,
  `difficulty` int(11) DEFAULT NULL,
  `qexp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question`
--



-- --------------------------------------------------------

--
-- Table structure for table `register`
--

CREATE TABLE `register` (
  `sid` varchar(100) NOT NULL,
  `eid` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `register`
--

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `sid` varchar(100) NOT NULL,
  `sname` varchar(100) DEFAULT NULL,
  `phno` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`


-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `eid` varchar(100) NOT NULL,
  `qid` varchar(100) NOT NULL,
  `sid` varchar(100) NOT NULL,
  `answer` varchar(100) DEFAULT NULL,
  `time_taken` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_data` varchar(100) NOT NULL,
  `role` enum('Student','Admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--


--
-- Indexes for dumped tables
--

--
-- Indexes for table `choose`
--
ALTER TABLE `choose`
  ADD PRIMARY KEY (`sid`,`did`),
  ADD KEY `did` (`did`);

--
-- Indexes for table `dates`
--
ALTER TABLE `dates`
  ADD PRIMARY KEY (`did`);

--
-- Indexes for table `exam`
--
ALTER TABLE `exam`
  ADD PRIMARY KEY (`eid`);

--
-- Indexes for table `has_dates`
--
ALTER TABLE `has_dates`
  ADD PRIMARY KEY (`eid`,`did`),
  ADD KEY `did` (`did`);

--
-- Indexes for table `has_questions`
--
ALTER TABLE `has_questions`
  ADD PRIMARY KEY (`eid`,`qid`),
  ADD KEY `qid` (`qid`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`qid`);

--
-- Indexes for table `register`
--
ALTER TABLE `register`
  ADD PRIMARY KEY (`sid`,`eid`),
  ADD KEY `eid` (`eid`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`sid`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`eid`,`qid`,`sid`),
  ADD KEY `qid` (`qid`),
  ADD KEY `sid` (`sid`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `choose`
--
ALTER TABLE `choose`
  ADD CONSTRAINT `choose_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `student` (`sid`),
  ADD CONSTRAINT `choose_ibfk_2` FOREIGN KEY (`did`) REFERENCES `dates` (`did`);

--
-- Constraints for table `has_dates`
--
ALTER TABLE `has_dates`
  ADD CONSTRAINT `has_dates_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `exam` (`eid`),
  ADD CONSTRAINT `has_dates_ibfk_2` FOREIGN KEY (`did`) REFERENCES `dates` (`did`);

--
-- Constraints for table `has_questions`
--
ALTER TABLE `has_questions`
  ADD CONSTRAINT `has_questions_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `exam` (`eid`),
  ADD CONSTRAINT `has_questions_ibfk_2` FOREIGN KEY (`qid`) REFERENCES `question` (`qid`);

--
-- Constraints for table `register`
--
ALTER TABLE `register`
  ADD CONSTRAINT `register_ibfk_1` FOREIGN KEY (`sid`) REFERENCES `student` (`sid`),
  ADD CONSTRAINT `register_ibfk_2` FOREIGN KEY (`eid`) REFERENCES `exam` (`eid`);

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `exam` (`eid`),
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`qid`) REFERENCES `question` (`qid`),
  ADD CONSTRAINT `submissions_ibfk_3` FOREIGN KEY (`sid`) REFERENCES `student` (`sid`);
--
