-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2025 at 03:25 PM
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
-- Database: `fnb`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL DEFAULT 'General',
  `total_income` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_expenses` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `total_income`, `total_expenses`) VALUES
(1, 'General', 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `password_hash`) VALUES
(1, 'zobayer Islam Jony', 'zobayer@gmail.com', '$2y$10$VL94JsqccUj2yYI0cxTDzuYnf60uFWX.v9MAJpcUj.pFTLdQel.hq');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `expense_code` varchar(32) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `category` varchar(60) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `employee_id`, `dept_id`, `expense_code`, `amount`, `category`, `reason`, `status`, `date`) VALUES
(1, 1, 1, 'REQ-AB9D9F', 1000.00, 'Travel', 'Work', 'Pending', '2025-09-07'),
(2, 1, 1, 'REQ-25FBB8', 20000.00, 'Meals', 'lunch', 'Approved', '2025-09-07'),
(3, 1, 1, 'REQ-7AC608', 20000.00, 'Equipment', 'Mid Exam', 'Pending', '2025-09-07');

-- --------------------------------------------------------

--
-- Table structure for table `salaries`
--

CREATE TABLE `salaries` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `annual_base` decimal(12,2) NOT NULL DEFAULT 0.00,
  `monthly_net` decimal(12,2) NOT NULL DEFAULT 0.00,
  `last_paid` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(250) NOT NULL,
  `fname` varchar(250) NOT NULL,
  `lname` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `phone` varchar(250) NOT NULL,
  `pass` varchar(250) NOT NULL,
  `cpass` varchar(250) NOT NULL,
  `desi` varchar(250) NOT NULL,
  `dept` varchar(250) NOT NULL,
  `status` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `email`, `phone`, `pass`, `cpass`, `desi`, `dept`, `status`) VALUES
(52, 'Md. Shihab Shahriar', 'Rafi Boss', 'shahriar.rafi1406315@gmail.com', '01706901915', '3318', '3318', 'Admin', 'Engineering', 'Active'),
(57, 'Md. Ashraful ', 'Islam', 'ashraf@gmail.com', '01474555', 'ashraf', 'ashraf', 'Manager', 'Finance', 'Active'),
(59, 'Zobayer', 'Islam', 'zobayer@gmail.com', '01796383940', 'jony', 'jony', 'Manager', 'HR', 'Active'),
(60, 'riya', 'babu', 'riya@gmail.com', '01706901915', 'aa', 'aa', 'Employee', 'IT', 'Active'),
(61, 'riya', 'aa', 'riyaa@gmail.com', '01474555', 'aa', 'aa', 'Admin', 'HR', 'Active'),
(62, 'ZOBAYER', 'JONY', 'jony@gmail.com', '01796383940', '1234', '1234', 'Employee', 'Finance', 'Active'),
(63, 'MISHU', 'MISH', 'mishu@gmail.com', '01796383940', '1234', '1234', 'Employee', 'Finance', 'Active'),
(64, 'Aman', 'sakib', 'aman@gmail.com', '01796383940', '1234', '1234', 'Employee', 'Finance', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expense_code` (`expense_code`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `salaries`
--
ALTER TABLE `salaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `salaries`
--
ALTER TABLE `salaries`
  ADD CONSTRAINT `salaries_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
