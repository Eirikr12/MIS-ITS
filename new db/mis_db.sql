-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 01:43 PM
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
-- Database: `mis_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `department_info`
--

CREATE TABLE `department_info` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `department_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_info`
--

INSERT INTO `department_info` (`department_id`, `department_name`, `department_details`) VALUES
(1, 'BSCS', NULL),
(2, 'BSBA', NULL),
(3, 'BSIT', NULL),
(4, 'BSAS', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `edit_requests`
--

CREATE TABLE `edit_requests` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `e_fname` varchar(100) NOT NULL,
  `e_mname` varchar(50) NOT NULL,
  `e_lname` varchar(100) NOT NULL,
  `e_contact` int(11) NOT NULL,
  `e_department_id` int(11) NOT NULL,
  `e_position_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `edit_requests`
--

INSERT INTO `edit_requests` (`id`, `employee_id`, `e_fname`, `e_mname`, `e_lname`, `e_contact`, `e_department_id`, `e_position_id`, `request_date`, `status`) VALUES
(2, 4, 'Eric', 'Jo', 'Briones', 2147483647, 2, 2, '2025-04-29 16:09:14', 'pending'),
(3, 3, 'Joyce', 'Claire', 'Malubay', 2147483647, 3, 1, '2025-04-29 16:16:22', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `employee_info`
--

CREATE TABLE `employee_info` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `employee_fname` varchar(50) NOT NULL,
  `employee_mname` varchar(50) NOT NULL,
  `employee_lname` varchar(50) NOT NULL,
  `employee_contact` varchar(50) DEFAULT NULL,
  `position_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `reg_status` varchar(50) NOT NULL,
  `display_picture` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_info`
--

INSERT INTO `employee_info` (`id`, `employee_id`, `employee_fname`, `employee_mname`, `employee_lname`, `employee_contact`, `position_id`, `department_id`, `reg_status`, `display_picture`) VALUES
(18, 3, 'Joyce', 'C', 'Malubay', '', 1, 3, '', 0x75706c6f6164732f313734343637383639375f3434383931393731375f313533383730373133303339383539365f373331343231383039373133393339313037335f6e2e6a7067),
(19, 4, 'Eric', 'John', 'Briones', '', 2, 2, '', ''),
(23, 5, 'hazel', 'fer', 'quenct', '2147483647', 4, 2, 'Active', 0x75706c6f6164732f313734353932343134345f70656f706c652d70726f66696c652d677261706869635f32343931312d32313337332e61766966);

-- --------------------------------------------------------

--
-- Table structure for table `position_info`
--

CREATE TABLE `position_info` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(50) NOT NULL,
  `position_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `position_info`
--

INSERT INTO `position_info` (`position_id`, `position_name`, `position_details`) VALUES
(1, 'Admin', NULL),
(2, 'Tech', NULL),
(3, 'HR', NULL),
(4, 'Employee', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registration_request`
--

CREATE TABLE `registration_request` (
  `reg_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `f_name` varchar(50) NOT NULL,
  `m_name` varchar(50) NOT NULL,
  `l_name` varchar(50) NOT NULL,
  `contact` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `position_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `date_submitted` date NOT NULL,
  `status_id` int(11) NOT NULL,
  `date_declined` date DEFAULT NULL,
  `date_approved` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_request`
--

INSERT INTO `registration_request` (`reg_id`, `user_id`, `f_name`, `m_name`, `l_name`, `contact`, `email`, `position_id`, `department_id`, `date_submitted`, `status_id`, `date_declined`, `date_approved`) VALUES
(13, 15, 'jasper', 'tuagan', 'villaluz', 2147483647, 'jp@email.com', 4, 1, '2025-04-25', 0, NULL, NULL),
(14, 16, 'Vincent', 'Love', 'Janelle', 2147483647, 'hotmama@jonel.com', 1, 3, '2025-04-25', 2, '2025-04-29', NULL),
(15, 17, 'George', 'G', 'Cooper', 2147483647, 'g123@gmail.com', 2, 2, '2025-04-29', 0, NULL, NULL),
(16, 18, 'Geo', 'w', 'Coer', 2147483647, 'geee3@gmail.com', 2, 3, '2025-04-29', 0, NULL, NULL),
(17, 19, 'Rod', 'Nard', 'Deus', 2147483647, 'rod123@gmail.com', 4, 1, '2025-04-29', 1, NULL, '2025-04-29');

-- --------------------------------------------------------

--
-- Table structure for table `request_info`
--

CREATE TABLE `request_info` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `request_status` varchar(15) NOT NULL,
  `request_description` varchar(50) NOT NULL,
  `request_date` date NOT NULL,
  `department` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_info`
--

INSERT INTO `request_info` (`id`, `request_id`, `request_status`, `request_description`, `request_date`, `department`) VALUES
(2, 2, 'Pending', 'Snacks', '2025-04-07', 'BSCS'),
(2, 3, 'Pending', 'Blood Packets', '2025-04-07', 'BSN'),
(2, 4, 'Pending', 'Microscope', '2025-04-07', 'BSN'),
(2, 5, 'Pending', 'Tubig', '2025-04-07', 'BSN'),
(2, 6, 'Pending', 'PC', '2025-04-07', 'BSCS'),
(2, 7, 'Pending', '3pc chicken', '2025-04-07', 'BSCS'),
(2, 8, 'Pending', 'Computer', '2025-04-07', 'BSCS'),
(18, 9, 'Approved', 'Monitor not available due to black screen', '2025-04-08', 'BSCS'),
(2, 10, 'Pending', 'Laptop', '2025-04-08', 'BSCS'),
(23, 11, 'Pending', 'Broken window', '2025-04-29', '');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`status_id`, `status_name`) VALUES
(0, 'Pending'),
(1, 'Approved'),
(2, 'Rejected');

-- --------------------------------------------------------

--
-- Table structure for table `temporary_log_in`
--

CREATE TABLE `temporary_log_in` (
  `temp_user_id` int(11) NOT NULL,
  `temp_username` varchar(100) NOT NULL,
  `temp_password` varchar(500) NOT NULL,
  `status_id` int(11) NOT NULL,
  `approval_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `temporary_log_in`
--

INSERT INTO `temporary_log_in` (`temp_user_id`, `temp_username`, `temp_password`, `status_id`, `approval_status`) VALUES
(15, 'jasper.villaluz', '$2y$10$mSteZllac13YgQTpivv8V.aoQejdSVStROVyvtJeqbvbw838C7h8i', 0, 1),
(16, 'vincent.janelle', '$2y$10$Ne3hF.gXmJpuwU62i9LHEOuJzmUbRw73JAZIxuAHiuxSNiEJsbfT2', 0, 1),
(17, 'george.cooper', '$2y$10$cWRXBnaTQV2euOwtg7vgJOO7r9H6xcx71GvGFA1OM3HIqYmLbBBR6', 0, 0),
(18, 'geo.coer', '$2y$10$RYOg4tKf0QtStte7qH8Ft.W/geJ.3arxK940LXCQNFCtQT3Jo0z/W', 0, 0),
(19, 'rod.deus', '$2y$10$nbJFqBQBEBSgk.vJ.nktY.i2EkbGENfGiH0gvr8E9.HcF/7wCVvcW', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_login`
--

CREATE TABLE `user_login` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(500) NOT NULL,
  `email` varchar(50) NOT NULL,
  `date_reg` varchar(20) NOT NULL,
  `user_session_id` varchar(200) NOT NULL,
  `position` varchar(50) NOT NULL,
  `status` int(1) NOT NULL,
  `department` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_login`
--

INSERT INTO `user_login` (`id`, `user_id`, `username`, `password`, `email`, `date_reg`, `user_session_id`, `position`, `status`, `department`) VALUES
(1, '', 'Hr', '$2y$10$iTjaUNnEJJJfxHmY5QqDqOkOLWMxHfO//RUzCsFHOJDkwR9khp5kq', 'Hrdefault@yahoo.com', '', '3093673e9ed8133e1a4b44c5d9bbd9e32c3bd33a835175b1d4732a0448a7f554', 'hr', 1, ''),
(2, '', 'Employee', '$2y$10$fuzAQS/cbKqa.F/mz8OOgesMZ5Omwg1bBERHRiPC3s8oIaq17XcUu', 'Employeedefault@gmail.com', '', '', 'employee', 0, ''),
(5, '', 'Tech', '$2y$10$m819h8Fa2MU2ko9XtXgiHeXv//SIKb0QtKEQA2NWjEzrwL.2YUcPa', 'Techdefault@gmail.com', '', '', 'tech', 0, ''),
(6, '', 'Admin', '$2y$10$PukYQnEGSrSi8XpwR/38SuUVuvx64dCU2LnPS7T9Fp70zBei6hbLu', 'Admindefault@yahoo.com', '', '', 'admin', 0, ''),
(17, '', 'Jas', '$2y$10$GArIZC41Pn.kx93YyPedyuW0mdDMeFfCTe4dJPFUv6R0wfvz6dEb2', 'jasperphillip70@gmail.com', '', '', 'admin', 2, 'BSCS'),
(18, '', 'Joyce', '$2y$10$5dPXu4TstU9J6GcGSl2ijO/DBuq1/4aLwkUNCtJeK4/s10LNLm/oK', 'joycemalubay@gmail.com', '', '', 'employee', 0, 'BSCS'),
(19, '', 'Eric', '$2y$10$AlON9Ogj.tUhHNL885RekeYwnFW3K3uuhUiBH1TUJTTUmWkmnl18.', 'eric123@gmail.com', '', '', 'employee', 0, 'BSCS'),
(23, '', 'hazel', '$2y$10$qpWhKzVnouVovjz2urKhueRYevd3qfYF/Pu5mmTZdRAWkFg6a1HWm', 'haz123@gmail.com', '2025-04-29', '', '4', 0, '2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `department_info`
--
ALTER TABLE `department_info`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `edit_requests`
--
ALTER TABLE `edit_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `e_department_id` (`e_department_id`,`e_position_id`),
  ADD KEY `fk_edit_pos` (`e_position_id`);

--
-- Indexes for table `employee_info`
--
ALTER TABLE `employee_info`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `id` (`id`),
  ADD KEY `position_id` (`position_id`,`department_id`),
  ADD KEY `employee_info_ibfk_3` (`department_id`);

--
-- Indexes for table `position_info`
--
ALTER TABLE `position_info`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `registration_request`
--
ALTER TABLE `registration_request`
  ADD PRIMARY KEY (`reg_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `status_id_2` (`status_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `request_info`
--
ALTER TABLE `request_info`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `temporary_log_in`
--
ALTER TABLE `temporary_log_in`
  ADD PRIMARY KEY (`temp_user_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `user_login`
--
ALTER TABLE `user_login`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `department_info`
--
ALTER TABLE `department_info`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `edit_requests`
--
ALTER TABLE `edit_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employee_info`
--
ALTER TABLE `employee_info`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `position_info`
--
ALTER TABLE `position_info`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `registration_request`
--
ALTER TABLE `registration_request`
  MODIFY `reg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `request_info`
--
ALTER TABLE `request_info`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `temporary_log_in`
--
ALTER TABLE `temporary_log_in`
  MODIFY `temp_user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user_login`
--
ALTER TABLE `user_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `edit_requests`
--
ALTER TABLE `edit_requests`
  ADD CONSTRAINT `edit_request_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employee_info` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_edit_dep` FOREIGN KEY (`e_department_id`) REFERENCES `department_info` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_edit_pos` FOREIGN KEY (`e_position_id`) REFERENCES `position_info` (`position_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_info`
--
ALTER TABLE `employee_info`
  ADD CONSTRAINT `employee_info_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_login` (`id`),
  ADD CONSTRAINT `employee_info_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `position_info` (`position_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `employee_info_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `department_info` (`department_id`);

--
-- Constraints for table `registration_request`
--
ALTER TABLE `registration_request`
  ADD CONSTRAINT `fk_dept_id_1` FOREIGN KEY (`department_id`) REFERENCES `department_info` (`department_id`),
  ADD CONSTRAINT `fk_pos_id_1` FOREIGN KEY (`position_id`) REFERENCES `position_info` (`position_id`),
  ADD CONSTRAINT `fk_status_id_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `fk_temp_id` FOREIGN KEY (`user_id`) REFERENCES `temporary_log_in` (`temp_user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `request_info`
--
ALTER TABLE `request_info`
  ADD CONSTRAINT `request_info_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_login` (`id`);

--
-- Constraints for table `temporary_log_in`
--
ALTER TABLE `temporary_log_in`
  ADD CONSTRAINT `fk_temp_status_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
