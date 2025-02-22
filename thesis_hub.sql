-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2025 at 04:38 PM
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
  `account_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','advisor','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`account_id`, `password`, `role`) VALUES
('65310000', '123', 'student'),
('65310001', '123', 'student'),
('65310002', '123', 'student'),
('65310609', '123', 'student'),
('65312345', '123', 'student'),
('Admin', '123', 'admin'),
('F05003', '123', 'advisor'),
('F05010', '123', 'advisor');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` varchar(50) NOT NULL,
  `admin_first_name` varchar(100) NOT NULL,
  `admin_last_name` varchar(100) NOT NULL,
  `admin_tel` varchar(15) NOT NULL,
  `admin_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_first_name`, `admin_last_name`, `admin_tel`, `admin_email`) VALUES
('Admin', 'Tharasuk', 'Chunkonghor', '055-963230', 'tharasukc@nu.ac.th');

-- --------------------------------------------------------

--
-- Table structure for table `advisor`
--

CREATE TABLE `advisor` (
  `advisor_id` varchar(50) NOT NULL,
  `advisor_first_name` varchar(100) NOT NULL,
  `advisor_last_name` varchar(100) NOT NULL,
  `advisor_tel` varchar(15) NOT NULL,
  `advisor_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `advisor`
--

INSERT INTO `advisor` (`advisor_id`, `advisor_first_name`, `advisor_last_name`, `advisor_tel`, `advisor_email`) VALUES
('F05003', 'Chakkrit', 'Namahoot', '055-123-1234', 'chakkrits@nu.ac.th'),
('F05010', 'Janjira', 'Payakpate', '055-123-1234', ' janjirap@nu.ac.th');

-- --------------------------------------------------------

--
-- Table structure for table `advisor_profile`
--

CREATE TABLE `advisor_profile` (
  `advisor_profile_id` int(11) NOT NULL,
  `advisor_id` varchar(50) NOT NULL,
  `expertise` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`expertise`)),
  `advisor_interests` text NOT NULL,
  `img` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `advisor_profile`
--

INSERT INTO `advisor_profile` (`advisor_profile_id`, `advisor_id`, `expertise`, `advisor_interests`, `img`) VALUES
(23, 'F05003', '[\"Artificial Intelligence (AI)\",\"Machine Learning\",\"Deep Learning\",\"Data Science\",\"Big Data\",\"Robotics\"]', 'Advancing AI Ethics and Fairness:\r\n&amp;quot;I am passionate about researching ethical frameworks for Artificial Intelligence, focusing on reducing bias in machine learning algorithms. My goal is to develop methods that ensure AI systems are transparent, fair, and accountable, especially in critical areas like criminal justice and hiring processes.&amp;quot;\r\nAI in Healthcare Diagnostics:\r\n&amp;quot;My research interest lies in leveraging AI to improve diagnostic accuracy in healthcare. I aim to investigate how deep learning models can analyze medical imaging and patient data to assist doctors in detecting diseases such as cancder or neurodegenerative disorders at an early stage.&amp;quot;\r\nHuman-AI Collaboration:\r\n&amp;quot;I am intrigued by the potential of human-AI collaboration and want to explore how AI can augment human creativity and decision-making. My research would focus on designing systems that seamlessly integrate with human workflows, such as in creative industries or complex problem-solving environments.&amp;quot;\r\nAI for Climate Change Solutions:\r\n&amp;quot;I am interested in applying Artificial Intelligence to address climate change challenges. My research would examine how AI can optimize renewable energy systems, predict environmental disasters, and model sustainable practices to mitigate global warming effects.&amp;quot;\r\nNatural Language Processing for Low-Resource Languages:\r\n&amp;quot;My passion is in advancing natural language processing (NLP) for underrepresented and low-resource languages. I want to investigate how AI can preserve linguistic diversity by creating tools for translation, speech recognition, and text generation in these languages.&amp;quot;\r\nAI-Driven Personalized Education:\r\n&amp;quot;I am keen to research how AI can transform education through personalized learning experiences. My focus would be on developing adaptive AI systems that tailor educational content to individual student needs, improving engagement and outcomes in diverse learning environments.&amp;quot;\r\nAutonomous Systems and Safety:\r\n&amp;quot;My research interest centers on the safety and reliability of autonomous systems, such as self-driving cars or drones. I aim to explore AI techniques that enhance real-time decision-making and risk assessment to ensure these systems operate securely in unpredictable conditions.&amp;quot;\r\nExplainable AI (XAI):\r\n&amp;quot;I am fascinated by the concept of explainable AI and want to investigate methods to make complex AI models more interpretable. My research would focus on creating tools that allow users to understand and trust AI decisions, particularly in high-stakes domains like finance or law.&amp;quot;', '../uploads/67b9e2b09823c.png');

-- --------------------------------------------------------

--
-- Table structure for table `advisor_request`
--

CREATE TABLE `advisor_request` (
  `advisor_request_id` int(11) NOT NULL,
  `requester_id` varchar(50) NOT NULL,
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
  `partner_accepted` tinyint(4) NOT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` varchar(50) NOT NULL,
  `receiver_id` varchar(50) NOT NULL,
  `message_title` text NOT NULL,
  `message` text DEFAULT NULL,
  `message_file_name` text DEFAULT NULL,
  `message_file_data` longblob DEFAULT NULL,
  `message_file_type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `time_stamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` varchar(50) NOT NULL,
  `student_first_name` varchar(100) NOT NULL,
  `student_last_name` varchar(100) NOT NULL,
  `student_tel` varchar(15) NOT NULL,
  `student_email` varchar(255) NOT NULL,
  `student_department` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `student_first_name`, `student_last_name`, `student_tel`, `student_email`, `student_department`) VALUES
('65310000', 'John', 'Doe', '055-123-1234', 'JohnD65@nu.ac.th', 'Information Technology'),
('65310001', 'George', 'Brown', '055-123-1234', 'georgeb65@nu.ac.th', 'Computer Science'),
('65310002', 'Jane', 'Red', '066-123-1222', 'jane65@nu.ac.th', 'Information Technology'),
('65310609', 'Jakkrit', 'Umkhum', '055-123-1234', 'Jakkrit65@nu.ac.th', 'Computer Science'),
('65312345', 'Eric', 'Dickson', '055-120-4212', 'ericd65@nu.ac.th', 'Computer Science');

-- --------------------------------------------------------

--
-- Table structure for table `student_profile`
--

CREATE TABLE `student_profile` (
  `student_profile_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `student_interests` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `student_profile`
--

INSERT INTO `student_profile` (`student_profile_id`, `student_id`, `student_interests`) VALUES
(10, '65310609', 'Topic: The Rise of Natural Language Processing (NLP)w\r\n\r\nNatural Language Processing (NLP) has seen remarkable progress in recent years, transforming how computers understand and interact with human language. From chatbots and virtual assistants to language translation and sentiment analysis, NLP is reshaping our daily lives. Here&amp;#039;s why it&amp;#039;s a compelling area of interest:\r\n\r\nReal-World Applications:\r\n\r\nConversational AI: Chatbots like myself are revolutionizing customer service by providing instant support, personalized recommendations, and engaging conversations.\r\ne\r\nLanguage Translation: Tools like Microsoft Translator and Google Translate break language barriers, making communication seamless across different cultures.\r\n\r\nSentiment Analysis: Businesses use sentiment analysis to gauge customer opinions, helping them make data-driven decisions and improve their products and services.\r\n\r\nTechnological Advancements:\r\n\r\nDeep Learning: NLP models powered by deep learning algorithms can process vast amounts of text data, enabling more accurate and nuanced language understanding.\r\n\r\nTransformers: The introduction of transformer architectures, such as GPT (Generative Pre-trained Transformer), has significantly improved language generation, comprehension, and context retention.\r\n\r\nFuture Prospects:\r\n\r\nHealthcare: NLP is being used to analyze medical records, assist in diagnostics, and even predict patient outcomes.\r\n\r\nEducation: AI-driven educational tools provide personalized learning experiences, making education more accessible and effective.\r\n\r\nCreativity: AI is collaborating with humans in creative fields, generating art, music, and literature, pushing the boundaries of imagination.');

-- --------------------------------------------------------

--
-- Table structure for table `thesis`
--

CREATE TABLE `thesis` (
  `thesis_id` int(11) NOT NULL,
  `advisor_id` varchar(50) NOT NULL,
  `thesis_title` text NOT NULL,
  `authors` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`keywords`)),
  `issue_date` date NOT NULL,
  `publisher` varchar(255) NOT NULL,
  `abstract` text NOT NULL,
  `thesis_file` longblob NOT NULL,
  `thesis_file_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thesis_resource`
--

CREATE TABLE `thesis_resource` (
  `thesis_resource_id` int(11) NOT NULL,
  `advisor_request_id` int(50) NOT NULL,
  `uploader_id` varchar(50) NOT NULL,
  `thesis_resource_file_name` varchar(255) NOT NULL,
  `thesis_resource_file_data` longblob NOT NULL,
  `thesis_resource_file_type` varchar(255) NOT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`admin_email`);

--
-- Indexes for table `advisor`
--
ALTER TABLE `advisor`
  ADD PRIMARY KEY (`advisor_id`),
  ADD UNIQUE KEY `email` (`advisor_email`);

--
-- Indexes for table `advisor_profile`
--
ALTER TABLE `advisor_profile`
  ADD PRIMARY KEY (`advisor_profile_id`),
  ADD UNIQUE KEY `advisor_id` (`advisor_id`);

--
-- Indexes for table `advisor_request`
--
ALTER TABLE `advisor_request`
  ADD PRIMARY KEY (`advisor_request_id`),
  ADD KEY `advisor_id` (`advisor_id`),
  ADD KEY `requester_id` (`requester_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD PRIMARY KEY (`student_profile_id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `thesis`
--
ALTER TABLE `thesis`
  ADD PRIMARY KEY (`thesis_id`),
  ADD KEY `advisor_id` (`advisor_id`);

--
-- Indexes for table `thesis_resource`
--
ALTER TABLE `thesis_resource`
  ADD PRIMARY KEY (`thesis_resource_id`),
  ADD KEY `advisor_request_id` (`advisor_request_id`),
  ADD KEY `uploader_id` (`uploader_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `advisor_profile`
--
ALTER TABLE `advisor_profile`
  MODIFY `advisor_profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `advisor_request`
--
ALTER TABLE `advisor_request`
  MODIFY `advisor_request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `student_profile`
--
ALTER TABLE `student_profile`
  MODIFY `student_profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `thesis`
--
ALTER TABLE `thesis`
  MODIFY `thesis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `thesis_resource`
--
ALTER TABLE `thesis_resource`
  MODIFY `thesis_resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `account` (`account_id`);

--
-- Constraints for table `advisor`
--
ALTER TABLE `advisor`
  ADD CONSTRAINT `advisor_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `account` (`account_id`);

--
-- Constraints for table `advisor_profile`
--
ALTER TABLE `advisor_profile`
  ADD CONSTRAINT `advisor_profile_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `advisor` (`advisor_id`);

--
-- Constraints for table `advisor_request`
--
ALTER TABLE `advisor_request`
  ADD CONSTRAINT `advisor_request_ibfk_2` FOREIGN KEY (`advisor_id`) REFERENCES `advisor` (`advisor_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `account` (`account_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `account` (`account_id`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `account` (`account_id`);

--
-- Constraints for table `student_profile`
--
ALTER TABLE `student_profile`
  ADD CONSTRAINT `student_profile_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`);

--
-- Constraints for table `thesis`
--
ALTER TABLE `thesis`
  ADD CONSTRAINT `thesis_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `advisor` (`advisor_id`);

--
-- Constraints for table `thesis_resource`
--
ALTER TABLE `thesis_resource`
  ADD CONSTRAINT `thesis_resource_ibfk_1` FOREIGN KEY (`advisor_request_id`) REFERENCES `advisor_request` (`advisor_request_id`),
  ADD CONSTRAINT `thesis_resource_ibfk_2` FOREIGN KEY (`uploader_id`) REFERENCES `account` (`account_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
