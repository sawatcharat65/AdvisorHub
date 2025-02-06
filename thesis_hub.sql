-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 06, 2025 at 03:08 PM
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
-- Database: `thesis_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','advisor','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `password`, `role`) VALUES
('65310000', '123', 'student'),
('65310609', '123', 'student'),
('Admin', '123', 'admin'),
('F05003', '123', 'advisor'),
('F05010', '123', 'advisor');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `tel` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `first_name`, `last_name`, `tel`, `email`) VALUES
('Admin', 'Tharasuk', 'Chunkonghor', '055-963230', 'tharasukc@nu.ac.th');

-- --------------------------------------------------------

--
-- Table structure for table `advisor`
--

CREATE TABLE `advisor` (
  `id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `tel` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `advisor`
--

INSERT INTO `advisor` (`id`, `first_name`, `last_name`, `tel`, `email`) VALUES
('F05003', 'Chakkrit', 'Namahoot', '055-123-1234', 'chakkrits@nu.ac.th'),
('F05010', 'Janjira', 'Payakpate', '055-123-1234', ' janjirap@nu.ac.th');

-- --------------------------------------------------------

--
-- Table structure for table `advisor_profile`
--

CREATE TABLE `advisor_profile` (
  `id` int(11) NOT NULL,
  `advisor_id` varchar(50) NOT NULL,
  `expertise` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`expertise`)),
  `interests` text NOT NULL,
  `img` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `advisor_profile`
--

INSERT INTO `advisor_profile` (`id`, `advisor_id`, `expertise`, `interests`, `img`) VALUES
(14, 'F05010', '[\"Artificial Intelligence (AI)\",\"Machine Learning\",\"Cybersecurity\"]', 'As a professor advising on theses related to artificial intelligence (AI), my interests center on exploring the intersection of AI with real-world applications, ethical considerations, and theoretical advancements. Here are some of my key areas of interest for guiding AI thesis projects:\r\n\r\n1. AI and Machine Learning Algorithms:\r\nInvestigating novel machine learning algorithms or optimizing existing ones to improve efficiency and accuracy. I’m particularly interested in deep learning, reinforcement learning, and unsupervised learning methods.\r\nExploring algorithmic fairness and bias reduction in AI models, which is crucial as AI systems become more integrated into critical areas like healthcare, law enforcement, and hiring.\r\n2. AI in Healthcare:\r\nThe application of AI in diagnostics, predictive modeling for disease outbreaks, or personalized treatment plans. I find it compelling to see how AI can transform medical practices and improve patient outcomes.\r\nEthical challenges in AI-driven healthcare, including issues of privacy, data security, and accountability in medical decisions made by AI.\r\n3. AI and Natural Language Processing (NLP):\r\nExploring advancements in NLP, especially with the increasing interest in large language models (LLMs) and transformers. I’m keen on projects that develop applications for improving language translation, sentiment analysis, and text summarization.\r\nInvestigating the ethical concerns of LLMs, such as misinformation, manipulation, and the societal implications of AI-powered content generation.', '../uploads/67a3b4621a8ac.png'),
(20, 'F05003', '[\"Artificial Intelligence (AI)\",\"Machine Learning\",\"Deep Learning\",\"Data Science\",\"Big Data\",\"Internet of Things (IoT)\"]', 'As a professor advising on theses related to artificial intelligence (AI), my interests center on exploring the intersection of AI with real-world applications, ethical considerations, and theoretical advancements. Here are some of my key areas of interest for guiding AI thesis projects:\r\n\r\n1. AI and Machine Learning Algorithms:\r\nInvestigating novel machine learning algorithms or optimizing existing ones to improve efficiency and accuracy. I’m particularly interested in deep learning, reinforcement learning, and unsupervised learning methods.\r\nExploring algorithmic fairness and bias reduction in AI models, which is crucial as AI systems become more integrated into critical areas like healthcare, law enforcement, and hiring.\r\n2. AI in Healthcare:\r\nThe application of AI in diagnostics, predictive modeling for disease outbreaks, or personalized treatment plans. I find it compelling to see how AI can transform medical practices and improve patient outcomes.\r\nEthical challenges in AI-driven healthcare, including issues of privacy, data security, and accountability in medical decisions made by AI.\r\n3. AI and Natural Language Processing (NLP):\r\nExploring advancements in NLP, especially with the increasing interest in large language models (LLMs) and transformers. I’m keen on projects that develop applications for improving language translation, sentiment analysis, and text summarization.\r\nInvestigating the ethical concerns of LLMs, such as misinformation, manipulation, and the societal implications of AI-powered content generation.', '../uploads/67a4bdf9d30ac.png');

-- --------------------------------------------------------

--
-- Table structure for table `advisor_request`
--

CREATE TABLE `advisor_request` (
  `id` int(11) NOT NULL,
  `student_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`student_id`)),
  `advisor_id` varchar(50) NOT NULL,
  `thesis_topic_thai` text NOT NULL,
  `thesis_topic_eng` text NOT NULL,
  `thesis_description` text NOT NULL,
  `is_even` tinyint(1) NOT NULL DEFAULT 0,
  `semester` tinyint(1) NOT NULL,
  `academic_year` smallint(4) NOT NULL,
  `is_advisor_approved` tinyint(1) NOT NULL DEFAULT 0,
  `is_admin_approved` tinyint(1) NOT NULL DEFAULT 0,
  `time_stamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `advisor_request`
--

INSERT INTO `advisor_request` (`id`, `student_id`, `advisor_id`, `thesis_topic_thai`, `thesis_topic_eng`, `thesis_description`, `is_even`, `semester`, `academic_year`, `is_advisor_approved`, `is_admin_approved`, `time_stamp`) VALUES
(2, '65310609', 'F05003', 'ปัญญาประดิษฐ์อัจฉริยะ', 'Genius Artificial Inteligence', 'Genius Artificial Inteligence Genius Artificial InteligenceGenius Artificial InteligenceGenius Artificial InteligenceGenius Artificial InteligenceGenius Artificial InteligenceGenius Artificial InteligenceGenius Artificial Inteligence', 0, 2, 2024, 1, 1, '2025-02-05 18:03:40'),
(3, '[\"65310609\", \"65310000\"]', 'F05003', 'การศึกษาผลกระทบของการเปลี่ยนแปลงสภาพภูมิอากาศต่อการเกษตรในภาคอีสาน', 'A Study on the Impact of Climate Change on Agriculture in the Northeastern Region', 'A Study on the Impact of Climate Change on Agriculture in the Northeastern Region', 1, 2, 2024, 1, 1, '2025-02-05 18:12:47'),
(4, '[\"65310012\",\"65310234\"]', 'F05010', 'การพัฒนาแอปพลิเคชันบนโทรศัพท์มือถือเพื่อช่วยเสริมทักษะการเรียนรู้ภาษาอังกฤษสำหรับนักเรียน', 'Development of a Mobile Application to Enhance English Learning Skills for Students', 'Development of a Mobile Application to Enhance English Learning Skills for Students', 1, 2, 2024, 0, 0, '2025-02-05 18:17:23'),
(5, '[\"65123412\",\"65123413\"]', 'F05010', 'การศึกษาผลกระทบของการท่องเที่ยวต่อสิ่งแวดล้อมในพื้นที่อุทยานแห่งชาติ', 'A Study on the Environmental Impact of Tourism in National Park Areas', 'A Study on the Environmental Impact of Tourism in National Park Areas', 1, 2, 2024, 1, 1, '2025-02-05 18:18:29');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` varchar(50) NOT NULL,
  `receiver_id` varchar(50) NOT NULL,
  `title` text NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `time_stamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `tel` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `is_advised` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `first_name`, `last_name`, `tel`, `email`, `department`, `is_advised`) VALUES
('65310000', 'John', 'Doe', '055-123-1234', 'JohnD65@nu.ac.th', 'Information Technology', 0),
('65310609', 'Jakkrit', 'Umkhum', '055-123-1234', 'Jakkrit65@nu.ac.th', 'Computer Science', 0);

-- --------------------------------------------------------

--
-- Table structure for table `student_profile`
--

CREATE TABLE `student_profile` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `interests` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `student_profile`
--

INSERT INTO `student_profile` (`id`, `student_id`, `interests`) VALUES
(5, '65310609', 'As a professor advising on theses related to artificial intelligence (AI), my interests center on exploring the intersection of AI with real-world applications, ethical considerations, and theoretical advancements. Here are some of my key areas of interest for guiding AI thesis projects:\r\n\r\n1. AI and Machine Learning Algorithms:\r\nInvestigating novel machine learning algorithms or optimizing existing ones to improve efficiency and accuracy. I’m particularly interested in deep learning, reinforcement learning, and unsupervised learning methods.\r\nExploring algorithmic fairness and bias reduction in AI models, which is crucial as AI systems become more integrated into critical areas like healthcare, law enforcement, and hiring.\r\n2. AI in Healthcare:\r\nThe application of AI in diagnostics, predictive modeling for disease outbreaks, or personalized treatment plans. I find it compelling to see how AI can transform medical practices and improve patient outcomes.\r\nEthical challenges in AI-driven healthcare, including issues of privacy, data security, and accountability in medical decisions made by AI.\r\n3. AI and Natural Language Processing (NLP):\r\nExploring advancements in NLP, especially with the increasing interest in large language models (LLMs) and transformers. I’m keen on projects that develop applications for improving language translation, sentiment analysis, and text summarization.\r\nInvestigating the ethical concerns of LLMs, such as misinformation, manipulation, and the societal implications of AI-powered content generation.');

-- --------------------------------------------------------

--
-- Table structure for table `thesis`
--

CREATE TABLE `thesis` (
  `id` int(11) NOT NULL,
  `advisor_id` varchar(50) NOT NULL,
  `title` text NOT NULL,
  `authors` text NOT NULL,
  `keywords` text NOT NULL,
  `issue_date` date NOT NULL,
  `publisher` varchar(255) NOT NULL,
  `abstract` text NOT NULL,
  `uri` varchar(255) NOT NULL,
  `thesis_file` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thesis_resource`
--

CREATE TABLE `thesis_resource` (
  `id` int(11) NOT NULL,
  `uploader_id` varchar(50) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_data` longblob NOT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `advisor`
--
ALTER TABLE `advisor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `advisor_profile`
--
ALTER TABLE `advisor_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `advisor_id` (`advisor_id`);

--
-- Indexes for table `advisor_request`
--
ALTER TABLE `advisor_request`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`) USING HASH,
  ADD KEY `advisor_id` (`advisor_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `thesis`
--
ALTER TABLE `thesis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advisor_id` (`advisor_id`);

--
-- Indexes for table `thesis_resource`
--
ALTER TABLE `thesis_resource`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uploader_id` (`uploader_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advisor_profile`
--
ALTER TABLE `advisor_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `advisor_request`
--
ALTER TABLE `advisor_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_profile`
--
ALTER TABLE `student_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `thesis`
--
ALTER TABLE `thesis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`id`) REFERENCES `account` (`id`);

--
-- Constraints for table `advisor`
--
ALTER TABLE `advisor`
  ADD CONSTRAINT `advisor_ibfk_1` FOREIGN KEY (`id`) REFERENCES `account` (`id`);

--
-- Constraints for table `advisor_profile`
--
ALTER TABLE `advisor_profile`
  ADD CONSTRAINT `advisor_profile_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `advisor` (`id`);

--
-- Constraints for table `advisor_request`
--
ALTER TABLE `advisor_request`
  ADD CONSTRAINT `advisor_request_ibfk_2` FOREIGN KEY (`advisor_id`) REFERENCES `advisor` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `account` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `account` (`id`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`id`) REFERENCES `account` (`id`);

--
-- Constraints for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD CONSTRAINT `student_profile_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`);

--
-- Constraints for table `thesis`
--
ALTER TABLE `thesis`
  ADD CONSTRAINT `thesis_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `advisor` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
