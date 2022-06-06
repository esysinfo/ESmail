-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- 主機： localhost
-- 產生時間： 2021 年 12 月 28 日 13:06
-- 伺服器版本： 10.3.27-MariaDB
-- PHP 版本： 7.2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- 資料庫： `vmail`
--

-- --------------------------------------------------------

--
-- 資料表結構 `alias`
--

CREATE TABLE `alias` (
  `ID` int(11) NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '實際的 Mail',
  `alias` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '轉寄的 VMAIL'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `domain`
--

CREATE TABLE `domain` (
  `ID` int(11) NOT NULL,
  `virtual` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'domain 名稱'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `mail`
--

CREATE TABLE `mail` (
  `ID` int(11) NOT NULL,
  `account` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '虛擬帳號',
  `domain_id` int(11) NOT NULL,
  `password` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密碼（用 MySQL ENCRYPT）',
  `C_TIME` timestamp NOT NULL DEFAULT current_timestamp(),
  `limit` tinyint(4) NOT NULL DEFAULT 2,
  `expire_date` date NOT NULL DEFAULT '2099-12-31',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0 停用, 1 啟用'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `alias`
--
ALTER TABLE `alias`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `alias` (`alias`);

--
-- 資料表索引 `domain`
--
ALTER TABLE `domain`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `virtual` (`virtual`);

--
-- 資料表索引 `mail`
--
ALTER TABLE `mail`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `account` (`account`),
  ADD KEY `domain_id` (`domain_id`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `alias`
--
ALTER TABLE `alias`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `domain`
--
ALTER TABLE `domain`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `mail`
--
ALTER TABLE `mail`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
