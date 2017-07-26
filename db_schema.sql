-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 26, 2017 at 09:23 AM
-- Server version: 10.0.31-MariaDB
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `moneypla_budget2`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `off_budget` tinyint(1) NOT NULL,
  `description` tinytext NOT NULL,
  `last_reconciled_balance` decimal(10,2) NOT NULL,
  `last_reconciled` date NOT NULL,
  `priority` int(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` int(11) NOT NULL,
  `service` tinytext NOT NULL,
  `bill_interval` tinytext NOT NULL,
  `initial_due` date NOT NULL,
  `expect_amt` double(7,2) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `pay_method` tinytext NOT NULL,
  `account_num` tinytext NOT NULL,
  `last_paid` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bill_payments`
--

CREATE TABLE `bill_payments` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `date_paid` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `bud_month` tinyint(4) NOT NULL,
  `bud_year` smallint(6) NOT NULL,
  `amount_alloc` decimal(8,2) NOT NULL,
  `amount_injected` decimal(10,2) NOT NULL,
  `priority` int(11) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `description` tinytext NOT NULL,
  `special_cat` tinyint(1) NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `group_id`, `description`, `special_cat`, `updated_at`, `deleted`) VALUES
(4, 7, 'Parkhouse Insurance (CIBC VISA)', 0, '2017-07-11 02:43:27', 0),
(3, 7, 'Mortgage', 0, '2017-07-10 15:17:23', 0),
(5, 30, 'Chad', 0, '0000-00-00 00:00:00', 0),
(6, 30, 'Steph', 0, '0000-00-00 00:00:00', 0),
(7, 30, 'Family', 0, '2017-07-21 19:16:03', 0),
(8, 31, 'Miscellaneous', 0, '0000-00-00 00:00:00', 0),
(9, 31, 'Tithe & Giving', 0, '2017-07-18 04:52:34', 0),
(10, 30, 'Vacation (target $300)', 0, '0000-00-00 00:00:00', 0),
(11, 11, 'TV & Internet', 0, '2017-07-21 19:16:18', 0),
(12, 11, 'Hydro & Natural Gas (Hydro 28th)', 0, '2017-07-11 02:47:48', 0),
(16, 16, 'Mortgage - 7th', 0, '0000-00-00 00:00:00', 0),
(48, 30, 'Gifts', 0, '0000-00-00 00:00:00', 0),
(19, 32, 'Emergency Fund ($5k in TFSA)', 0, '0000-00-00 00:00:00', 0),
(20, 32, 'Home Refit Fund', 0, '0000-00-00 00:00:00', 0),
(21, 32, 'Administration (Licenses, Medical, Edified, Passports) - $20', 0, '0000-00-00 00:00:00', 0),
(25, 11, 'Cellphone - Steph', 0, '0000-00-00 00:00:00', 0),
(26, 32, 'Home Improvement', 0, '0000-00-00 00:00:00', 0),
(27, 10, 'Gas (monthly avg $325)', 0, '0000-00-00 00:00:00', 0),
(28, 10, 'Insurance - Rav4 (RBC)', 0, '0000-00-00 00:00:00', 0),
(30, 10, 'Insurance - CBR (PC) - 1st 43.48', 0, '0000-00-00 00:00:00', 0),
(31, 14, 'Daycare', 0, '0000-00-00 00:00:00', 0),
(42, 11, 'Cellphone - Chad', 0, '0000-00-00 00:00:00', 0),
(47, 7, 'Property Taxes', 0, '2017-06-21 02:00:52', 0),
(35, 33, 'RRSP - 7th', 0, '0000-00-00 00:00:00', 0),
(37, 31, 'Life Insurance (PC)', 0, '0000-00-00 00:00:00', 0),
(40, 11, 'Water Heater - Qrtly (101 625 222 2253 594)', 0, '0000-00-00 00:00:00', 0),
(1, 1, 'Surplus', 1, '0000-00-00 00:00:00', 0),
(39, 10, 'Auto Maintenance (monthly avg $131)', 0, '0000-00-00 00:00:00', 0),
(43, 10, 'New Vehicle Fund (target $450) ($3k in TFSA)', 0, '0000-00-00 00:00:00', 0),
(44, 31, 'Groceries & Household', 0, '2017-07-21 19:16:10', 0),
(49, 33, 'College Fund (target $200)', 0, '0000-00-00 00:00:00', 0),
(50, 33, 'SCCS Pension ($176.42/month)', 0, '0000-00-00 00:00:00', 0),
(63, 7, 'STuff', 0, '2017-07-25 19:39:23', 1),
(0, 0, 'Income', 1, '2017-07-11 03:00:37', 0),
(2, 0, 'Off Budget', 1, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` tinytext NOT NULL,
  `priority` int(11) NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `priority`, `updated_at`, `deleted`) VALUES
(7, 'Housing', 0, '2017-07-10 14:35:00', 0),
(10, 'Transportation', 3, '0000-00-00 00:00:00', 0),
(11, 'Utilities', 1, '0000-00-00 00:00:00', 0),
(14, 'Childcare', 5, '0000-00-00 00:00:00', 0),
(16, 'Fairfax Costs', 2, '0000-00-00 00:00:00', 0),
(31, 'General', 4, '0000-00-00 00:00:00', 0),
(1, 'Surplus', 9, '0000-00-00 00:00:00', 0),
(30, 'Recreation', 8, '0000-00-00 00:00:00', 0),
(32, 'Short Term Saving', 6, '0000-00-00 00:00:00', 0),
(33, 'Long Term Savings', 7, '0000-00-00 00:00:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `income_sources`
--

CREATE TABLE `income_sources` (
  `id` int(11) NOT NULL,
  `bud_year` smallint(6) NOT NULL,
  `bud_month` tinyint(4) NOT NULL,
  `source` tinytext NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `priority` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pending_transaction_imports`
--

CREATE TABLE `pending_transaction_imports` (
  `id` int(11) NOT NULL,
  `memo` tinytext NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `tran_date` date NOT NULL,
  `date_uploaded` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `tran_date` date NOT NULL,
  `cat_id` int(11) NOT NULL,
  `description` tinytext NOT NULL,
  `in_out` smallint(1) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `tran_type` tinytext NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `bill_id` int(11) DEFAULT '0',
  `updated_at` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` tinytext NOT NULL,
  `pw_hash` tinytext NOT NULL,
  `user_level` tinytext NOT NULL,
  `last_login` datetime NOT NULL,
  `first_name` tinytext NOT NULL,
  `last_name` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `allow_access` tinyint(1) NOT NULL,
  `api_key` tinytext NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `pw_hash`, `user_level`, `last_login`, `first_name`, `last_name`, `email`, `allow_access`, `api_key`, `updated_at`, `deleted`) VALUES
(2, 'chadtiffin', '$2y$10$gBUGdJ1HGZos41nYNxTvcuMhCWUY.N2cMBvw2f/3582fLjmfDhOly', 'Admin', '2017-07-26 06:46:49', 'Chad', 'Tiffin', 'chad@chadtiffin.com', 1, 'f9c0bb251a4d5a05ccf4837d405e0a7e0d76daa70bb244a46735382cef155682', '2017-07-24 18:28:54', 0),
(34, 'stephtif', '00b716a5f99b204be4e2e69a59c4857b2016988ed9473acb5806c2cfa2990083', 'User', '2017-05-19 17:50:12', 'Steph', 'Junkin', 'junkin.stephanie@gmail.com', 0, '74049ab4b43d5793f0a82a4c1408f3a11cec13ee9d028ecc3a45ccb159ed9512', '2017-07-24 19:45:39', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `token` text NOT NULL,
  `issued` datetime NOT NULL,
  `expiry` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bill_payments`
--
ALTER TABLE `bill_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `income_sources`
--
ALTER TABLE `income_sources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pending_transaction_imports`
--
ALTER TABLE `pending_transaction_imports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `bill_payments`
--
ALTER TABLE `bill_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1204;
--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
--
-- AUTO_INCREMENT for table `income_sources`
--
ALTER TABLE `income_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=859;
--
-- AUTO_INCREMENT for table `pending_transaction_imports`
--
ALTER TABLE `pending_transaction_imports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3574;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
