-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2022-01-15 23:54:19
-- 服务器版本： 5.7.34-log
-- PHP 版本： 8.0.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- 数据库： `cx_ba7jcm_live`
--

-- --------------------------------------------------------

--
-- 表的结构 `list`
--

DROP TABLE IF EXISTS `list`;
CREATE TABLE IF NOT EXISTS `list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `tel` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `push_id` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `last_time` int(11) DEFAULT NULL COMMENT '最后一次登录的时间',
  `last_single` int(11) DEFAULT NULL COMMENT '最后一次尝试签到的时间',
  `not_notice` int(11) NOT NULL DEFAULT '0',
  `last_success` int(11) NOT NULL COMMENT '最后一次成功签到的时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;
