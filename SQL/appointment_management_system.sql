-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql206.infinityfree.com
-- Generation Time: Jun 13, 2024 at 04:37 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_36695701_appointment_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appointment_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `appointment_status` varchar(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appointment_id`, `customer_id`, `service_id`, `staff_id`, `appointment_date`, `appointment_time`, `appointment_status`, `date_created`, `date_updated`) VALUES
(1, 1, 1, 3, '2024-06-17', '11:00:00', 'Coming Soon', '2024-06-10 09:24:41', '2024-06-10 09:24:41'),
(2, 4, 1, 3, '2024-06-16', '11:00:00', 'Completed', '2024-06-10 13:32:37', '2024-06-13 19:20:42'),
(6, 1, 3, 3, '2024-07-05', '12:00:00', 'Coming Soon', '2024-06-13 18:34:02', '2024-06-13 18:34:02'),
(7, 9, 3, 3, '2024-07-04', '14:00:00', 'Coming Soon', '2024-06-13 19:12:46', '2024-06-13 19:13:25'),
(9, 9, 4, 3, '2024-06-25', '12:00:00', 'Coming Soon', '2024-06-13 19:19:54', '2024-06-13 19:19:54');

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `login_logs`
--

INSERT INTO `login_logs` (`log_id`, `user_id`, `login_time`) VALUES
(1, 1, '2024-06-10 08:26:28'),
(2, 2, '2024-06-10 08:29:03'),
(3, 1, '2024-06-10 08:29:03'),
(4, 2, '2024-06-10 09:01:26'),
(5, 3, '2024-06-10 09:01:26'),
(6, 3, '2024-06-10 09:22:05'),
(7, 4, '2024-06-10 13:29:05'),
(8, 4, '2024-06-10 13:31:43'),
(9, 3, '2024-06-10 14:48:54'),
(10, 2, '2024-06-10 15:00:10'),
(11, 6, '2024-06-10 18:08:41'),
(12, 6, '2024-06-10 18:13:50'),
(13, 6, '2024-06-10 18:29:42'),
(14, 2, '2024-06-13 17:39:49'),
(15, 3, '2024-06-13 18:26:34'),
(16, 1, '2024-06-13 18:32:37'),
(17, 9, '2024-06-13 19:09:18'),
(18, 9, '2024-06-13 19:10:53'),
(19, 1, '2024-06-13 19:17:33'),
(20, 3, '2024-06-13 19:18:28'),
(21, 2, '2024-06-13 19:32:29'),
(22, 9, '2024-06-13 19:41:20');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message_text` text DEFAULT NULL,
  `send_datetime` datetime DEFAULT NULL,
  `message_status` varchar(255) DEFAULT NULL,
  `reply_text` text DEFAULT NULL,
  `reply_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`message_id`, `user_id`, `name`, `email`, `message_text`, `send_datetime`, `message_status`, `reply_text`, `reply_datetime`) VALUES
(1, 1, 'Alise', 'aliseyeap75@gmail.com', 'What is the best service suggested for oily skin ?', '2024-06-10 16:40:37', 'Replied', 'Facial Treatments', '2024-06-10 17:10:56'),
(2, 6, 'hhhh', 'eyehooi836@gmail.com', 'ddd', '2024-06-11 02:35:26', 'New', NULL, NULL),
(3, 1, 'Alise ', 'aliseyeap75@gmail.com', 'Testing message ', '2024-06-14 02:33:34', 'New', NULL, NULL),
(4, 9, 'Cx0330', 'paeyesila938@gmail.com', 'Hiii, how are you?', '2024-06-14 03:11:51', 'Replied', 'I&#039;m fine , thanks', '2024-06-14 03:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `otp`
--

CREATE TABLE `otp` (
  `otp_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiry` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `otp`
--

INSERT INTO `otp` (`otp_id`, `user_id`, `otp`, `otp_expiry`) VALUES
(1, 1, '972603', '2024-06-10 20:30:49'),
(2, 1, '135159', '2024-06-10 20:33:43'),
(3, 2, '759108', '2024-06-10 21:06:09'),
(4, 3, '437498', '2024-06-10 21:26:49'),
(5, 4, '608818', '2024-06-11 01:33:11'),
(6, 4, '214650', '2024-06-11 01:36:16'),
(7, 3, '321521', '2024-06-11 02:53:36'),
(8, 2, '426069', '2024-06-11 03:03:41'),
(9, 6, '409070', '2024-06-11 06:13:11'),
(10, 6, '472104', '2024-06-11 06:18:26'),
(11, 6, '393581', '2024-06-11 06:34:20'),
(12, 2, '278138', '2024-06-14 05:44:32'),
(13, 3, '354826', '2024-06-14 06:31:17'),
(14, 1, '474519', '2024-06-14 06:37:16'),
(15, 9, '426243', '2024-06-14 07:13:52'),
(16, 9, '171079', '2024-06-14 07:15:36'),
(17, 1, '416559', '2024-06-14 07:21:30'),
(18, 3, '991029', '2024-06-14 07:23:12'),
(19, 2, '460478', '2024-06-14 07:36:32'),
(20, 9, '398716', '2024-06-14 07:46:06');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `password_reset`
--

INSERT INTO `password_reset` (`id`, `email`, `token`, `created_at`) VALUES
(1, 'ai210338@siswa.uthm.edu.my', 'fa825ba156a46ea52261935c7bebb69cd3f23dbc47f4caa8e12085925cdaf660', '2024-06-13 19:24:06');

-- --------------------------------------------------------

--
-- Table structure for table `security_questions`
--

CREATE TABLE `security_questions` (
  `question_id` int(11) NOT NULL,
  `question_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `security_questions`
--

INSERT INTO `security_questions` (`question_id`, `question_text`) VALUES
(1, 'What was the name of your first pet?'),
(2, 'What is your mother’s maiden name?'),
(3, 'What was the make and model of your first car?'),
(4, 'In what city were you born?'),
(5, 'What was your favorite teacher’s name?'),
(6, 'What is your favorite book?'),
(7, 'What is your favorite movie?'),
(8, 'What is your father’s middle name?'),
(9, 'What was the name of your elementary school?'),
(10, 'What is the name of your favorite childhood friend?');

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_description` text DEFAULT NULL,
  `service_duration` int(11) DEFAULT NULL,
  `service_price` decimal(10,2) DEFAULT NULL,
  `service_image` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`service_id`, `service_name`, `service_description`, `service_duration`, `service_price`, `service_image`, `date_created`, `date_updated`) VALUES
(1, 'Facial Treatments', 'Skin treatments are designed to cleanse, exfoliate, and hydrate the skin. Facials can also be used to address specific skin concerns, such as acne, or wrinkles.', 2, '88.00', 'uploads/6666c45f0e3ba_1718010975.jpg', '2024-06-10 09:16:15', '2024-06-13 17:58:56'),
(2, 'Swedish massage', 'This is a gentle form of massage that is designed to promote relaxation and improve circulation. Swedish massage uses long, smooth strokes to knead the muscles.', 2, '108.00', 'uploads/666b2f4f1442b_1718300495.jpg', '2024-06-13 17:41:35', '2024-06-13 17:41:35'),
(3, 'Makeup Application', 'Unleash your inner radiance! Our expert makeup artists create personalized looks for every occasion, enhancing your natural beauty and boosting your confidence.', 2, '188.00', 'uploads/666b310f16f93_1718300943.jpg', '2024-06-13 17:49:03', '2024-06-13 17:49:03'),
(4, 'Laser Therapy', 'Safe and effective way to address a variety of concerns. This non-invasive treatment utilizes concentrated beams of light to stimulate healing and rejuvenation.', 2, '288.00', 'uploads/666b31f8098f3_1718301176.jpg', '2024-06-13 17:52:56', '2024-06-13 17:52:56'),
(6, 'LED Light Therapy', 'This treatment uses different wavelengths of light to improve skin health. Red light therapy can help to reduce inflammation and promote collagen production.', 2, '388.00', 'uploads/666b331947ba2_1718301465.jpg', '2024-06-13 17:57:45', '2024-06-13 17:57:45'),
(7, 'Hydra Treatments', 'This facial treatment uses a special machine to cleanse, exfoliate, extract, and hydrate the skin. It can be a good option for people with all skin types, including sensitive skin.', 1, '188.00', 'uploads/666b4a82d9a77_1718307458.jpg', '2024-06-13 19:37:39', '2024-06-13 19:38:07');

-- --------------------------------------------------------

--
-- Table structure for table `staff_availability`
--

CREATE TABLE `staff_availability` (
  `availability_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `available_start_date` date NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `available_end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `staff_availability`
--

INSERT INTO `staff_availability` (`availability_id`, `staff_id`, `service_id`, `available_start_date`, `date_created`, `date_updated`, `available_end_date`) VALUES
(1, 3, 1, '2024-06-16', '2024-06-10 09:24:00', '2024-06-10 09:24:00', '2024-06-22'),
(2, 3, 2, '2024-06-23', '2024-06-13 18:27:08', '2024-06-13 18:27:08', '2024-06-29'),
(3, 3, 3, '2024-06-30', '2024-06-13 18:28:42', '2024-06-13 18:28:42', '2024-07-06'),
(4, 3, 4, '2024-06-23', '2024-06-13 18:28:42', '2024-06-13 18:29:01', '2024-07-06');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `gender` enum('male','female','rather not say') NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `security_question_id` int(11) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `verification_code` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `role` enum('admin','staff','customer') NOT NULL DEFAULT 'customer',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `gender`, `password`, `phone_number`, `profile_picture`, `security_question_id`, `security_answer`, `verification_code`, `is_verified`, `role`, `date_created`, `date_updated`) VALUES
(1, 'Alise Yeap Rou Xin', 'aliseyeap75@gmail.com', 'rather not say', '$2y$10$3XWRm.zl5Gnv2yF/E0lkX.O76ZPU2O8UWZvDPGmWzsPVFSAGcR5k.', '0106668330', 'uploads/6666b91a4bd96_158705-zhe_ge_xing_qiu-shu_ma_yi_shu-jian_yue-kong_jian-chou_xiang_yi_shu-3840x2160.jpg', 4, 'Alor Setar', NULL, 1, 'customer', '2024-06-10 02:43:18', '2024-06-10 09:11:55'),
(2, 'AI210338', 'ai210338@siswa.uthm.edu.my', 'female', '$2y$10$31IBkRbwZhwKw48uo3y7se/lA7SdDaA/tedWvzUKJBXBoIYPjFDGm', '0106668330', NULL, 4, 'Alor Setar', NULL, 1, 'admin', '2024-06-10 08:57:49', '2024-06-13 19:31:01'),
(3, 'Rou Xuan', 'Rxuan0330@gmail.com', 'female', '$2y$10$thOksn.TyAK2Y.MtvOqixue8t0dRoX563.OYFw6MNzVVuBvWJsr6.', '0106668330', NULL, 4, 'Alor Setar', NULL, 1, 'staff', '2024-06-10 09:19:24', '2024-06-10 09:20:59'),
(4, 'Sherlynn Khor', 'xueyee12889@gmail.com', 'female', '$2y$10$xK4vpYutDOVrd.FLbHFd3uOi9WVOr8eXERUR6saeUck43Zpa0y5Ly', '0175842889', NULL, 2, 'lim siew chuan', NULL, 1, 'customer', '2024-06-10 13:16:56', '2024-06-13 18:00:37'),
(6, 'Vivian', 'eyehooi836@gmail.com', 'female', '$2y$10$OVA/UweeFuNhXCIIRZWpHuTHj4mARhoJBpC5.gFVbHpQIUExRF5ja', '01110886387', NULL, 1, 'Badman', NULL, 1, 'customer', '2024-06-10 18:00:14', '2024-06-10 18:23:05'),
(9, 'Rx330', 'paeyesila938@gmail.com', 'rather not say', '$2y$10$iCRezSLlqBMHiIoz7L3n8er276vM8u/9dN3X1iCEvvTx4NyAFGxB6', '0106668330', 'uploads/666b440704c62_143588-shan_mai-da_wu_shan-qi_fen-tian_kong-jian_yue-3840x2160.jpg', 4, 'Alor Setar', NULL, 1, 'staff', '2024-06-13 19:07:13', '2024-06-13 19:40:19'),
(10, 'Tia Yu', 'tia@gmail.com', 'female', '', '0123456789', NULL, NULL, NULL, NULL, 0, 'customer', '2024-06-13 19:22:49', '2024-06-13 19:23:00'),
(11, 'Rina Teoh', 'rina@gmail.com', 'female', '', '0123456789', NULL, NULL, NULL, NULL, 0, 'staff', '2024-06-13 19:36:33', '2024-06-13 19:36:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `otp`
--
ALTER TABLE `otp`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `security_questions`
--
ALTER TABLE `security_questions`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `staff_availability`
--
ALTER TABLE `staff_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `security_question_id` (`security_question_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `otp`
--
ALTER TABLE `otp`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `security_questions`
--
ALTER TABLE `security_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `staff_availability`
--
ALTER TABLE `staff_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`),
  ADD CONSTRAINT `appointment_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `otp`
--
ALTER TABLE `otp`
  ADD CONSTRAINT `otp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `staff_availability`
--
ALTER TABLE `staff_availability`
  ADD CONSTRAINT `staff_availability_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `staff_availability_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`security_question_id`) REFERENCES `security_questions` (`question_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
