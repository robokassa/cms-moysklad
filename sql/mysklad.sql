-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Май 27 2021 г., 08:57
-- Версия сервера: 8.0.20
-- Версия PHP: 7.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `mysklad`
--

-- --------------------------------------------------------

--
-- Структура таблицы `links`
--

CREATE TABLE `links` (
  `id` varchar(50) NOT NULL,
  `id_user` bigint NOT NULL DEFAULT '0',
  `url` int NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` text,
  `invoice_id` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint NOT NULL,
  `sklad_app` varchar(100) DEFAULT NULL,
  `sklad_account` varchar(50) DEFAULT NULL,
  `sklad_account_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `sklad_token` varchar(50) DEFAULT NULL,
  `robo_shop` varchar(30) DEFAULT NULL,
  `robo_test` tinyint(1) DEFAULT '1',
  `robo_key_1` varchar(50) DEFAULT NULL,
  `robo_key_2` varchar(50) DEFAULT NULL,
  `robo_key_test_1` varchar(50) DEFAULT NULL,
  `robo_key_test_2` varchar(50) DEFAULT NULL,
  `email_from` varchar(50) DEFAULT NULL,
  `email_smtp` varchar(50) DEFAULT NULL,
  `email_pass` varchar(50) DEFAULT NULL,
  `email_copy` varchar(200) DEFAULT NULL,
  `robo_fisk` tinyint(1) DEFAULT '0',
  `robo_crc` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'MD5',
  `robo_country` varchar(30) DEFAULT NULL,
  `robo_sn` varchar(20) DEFAULT NULL,
  `robo_pobject` varchar(20) DEFAULT NULL,
  `robo_pmethod` varchar(20) DEFAULT NULL,
  `robo_vat` varchar(20) DEFAULT NULL,
  `shop_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sklad_account` (`sklad_account`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
