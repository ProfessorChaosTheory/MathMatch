-- phpMyAdmin SQL Dump
-- Corrected version
-- Host: 127.0.0.1
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
-- Database: `mathmatch`
--

-- --------------------------------------------------------

--
-- Table structure for table `answers_given`
--

CREATE TABLE `answers_given` (
  `answers_given_ID` int(11) NOT NULL AUTO_INCREMENT,
  `userID`           int(100) NOT NULL,
  `question_ID`      int(11) NOT NULL,
  `answer`           text NOT NULL,
  PRIMARY KEY (`answers_given_ID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `classID`     int(11) NOT NULL AUTO_INCREMENT,
  `class_name`  varchar(80) NOT NULL,
  `description` varchar(256) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`classID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--
-- Note: session must be created before users because users.TT*_ID
--       reference session.session_ID.
--

CREATE TABLE `session` (
  `session_ID`   int(100) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date`         date NOT NULL,
  `time`         time NOT NULL,
  `ClassID`      int(11) NOT NULL,
  `TutorID`      int(11) NOT NULL,
  `StudentID`    int(11) NOT NULL,
  `is_scheduled` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`session_ID`),
  KEY `ClassID`   (`ClassID`),
  KEY `TutorID`   (`TutorID`),
  KEY `StudentID` (`StudentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Changes from original:
--   * password   varchar(40)  -> varchar(255)  (accommodates password_hash() output)
--   * usertype   NOT NULL     -> NOT NULL DEFAULT 0
--   * TT1_ID, TT2_ID, TT3_ID  NOT NULL -> DEFAULT NULL (sessions don't exist yet at registration)
--

CREATE TABLE `users` (
  `userID`            int(100) NOT NULL AUTO_INCREMENT,
  `usertype`          int(10) UNSIGNED NOT NULL DEFAULT 0,
  `username`          varchar(40) NOT NULL,
  `user_email`        varchar(120) NOT NULL,
  `password`          varchar(255) NOT NULL,
  `security_question` varchar(120) NOT NULL,
  `security_answer`   varchar(120) NOT NULL,
  `is_tutor`          tinyint(1) NOT NULL DEFAULT 0,
  `TT1_ID`            int(100) UNSIGNED DEFAULT NULL,
  `TT2_ID`            int(100) UNSIGNED DEFAULT NULL,
  `TT3_ID`            int(100) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`userID`),
  KEY `TT1_ID` (`TT1_ID`),
  KEY `TT2_ID` (`TT2_ID`),
  KEY `TT3_ID` (`TT3_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutors`
--

CREATE TABLE `tutors` (
  `tutorID` int(100) NOT NULL AUTO_INCREMENT,
  `userID`  int(100) NOT NULL,
  PRIMARY KEY (`tutorID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tutors_classes`
--

CREATE TABLE `tutors_classes` (
  `tutors_classes` int(100) NOT NULL AUTO_INCREMENT,
  `tutorID`        int(100) NOT NULL,
  `classID`        int(100) NOT NULL,
  PRIMARY KEY (`tutors_classes`),
  KEY `classID` (`classID`),
  KEY `tutorID` (`tutorID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions_asked`
--

CREATE TABLE `questions_asked` (
  `questions_asked_ID` int(11) NOT NULL AUTO_INCREMENT,
  `userID`             int(100) NOT NULL,
  `answer_ID`          int(11) NOT NULL,
  `question`           text NOT NULL,
  PRIMARY KEY (`questions_asked_ID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qa_ag`
--

CREATE TABLE `qa_ag` (
  `qa_ag_ID`           int(100) NOT NULL AUTO_INCREMENT,
  `questions_asked_ID` int(11) NOT NULL,
  `answers_given_ID`   int(11) NOT NULL,
  PRIMARY KEY (`qa_ag_ID`),
  KEY `questions_asked_ID` (`questions_asked_ID`),
  KEY `answers_given_ID`   (`answers_given_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Foreign key constraints
--
-- Note on users -> session:  ON DELETE SET NULL so a deleted session
--   doesn't orphan the user row.
-- All other FKs use ON DELETE CASCADE (child rows go with the parent).
--

ALTER TABLE `answers_given`
  ADD CONSTRAINT `answers_given_ibfk_1`
    FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

ALTER TABLE `questions_asked`
  ADD CONSTRAINT `questions_asked_ibfk_1`
    FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

ALTER TABLE `qa_ag`
  ADD CONSTRAINT `qa_ag_ibfk_1`
    FOREIGN KEY (`questions_asked_ID`) REFERENCES `questions_asked` (`questions_asked_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `qa_ag_ibfk_2`
    FOREIGN KEY (`answers_given_ID`)   REFERENCES `answers_given`   (`answers_given_ID`)   ON DELETE CASCADE;

ALTER TABLE `session`
  ADD CONSTRAINT `session_ibfk_1`
    FOREIGN KEY (`ClassID`)   REFERENCES `classes` (`classID`) ON DELETE CASCADE,
  ADD CONSTRAINT `session_ibfk_2`
    FOREIGN KEY (`TutorID`)   REFERENCES `users`   (`userID`)  ON DELETE CASCADE,
  ADD CONSTRAINT `session_ibfk_3`
    FOREIGN KEY (`StudentID`) REFERENCES `users`   (`userID`)  ON DELETE CASCADE;

ALTER TABLE `tutors`
  ADD CONSTRAINT `tutors_ibfk_1`
    FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

ALTER TABLE `tutors_classes`
  ADD CONSTRAINT `tutors_classes_ibfk_1`
    FOREIGN KEY (`classID`) REFERENCES `classes` (`classID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tutors_classes_ibfk_2`
    FOREIGN KEY (`tutorID`) REFERENCES `tutors`  (`tutorID`) ON DELETE CASCADE;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1`
    FOREIGN KEY (`TT1_ID`) REFERENCES `session` (`session_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2`
    FOREIGN KEY (`TT2_ID`) REFERENCES `session` (`session_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_3`
    FOREIGN KEY (`TT3_ID`) REFERENCES `session` (`session_ID`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
