-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 01, 2026 at 02:59 PM
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
-- Database: `learning-platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `answer_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `answers`
--

INSERT INTO `answers` (`id`, `question_id`, `answer_text`, `is_correct`, `answer_order`, `created_at`) VALUES
(1, 1, 'Hyper Text Markup Language', 1, 1, '2026-01-21 23:06:15'),
(2, 1, 'High Tech Modern Language', 0, 2, '2026-01-21 23:06:15'),
(3, 1, 'Home Tool Markup Language', 0, 3, '2026-01-21 23:06:15'),
(4, 1, 'Hyperlinks and Text Markup Language', 0, 4, '2026-01-21 23:06:15'),
(5, 2, '<a>', 1, 1, '2026-01-21 23:06:15'),
(6, 2, '<link>', 0, 2, '2026-01-21 23:06:15'),
(7, 2, '<href>', 0, 3, '2026-01-21 23:06:15'),
(8, 2, '<hyperlink>', 0, 4, '2026-01-21 23:06:15'),
(9, 3, 'True', 0, 1, '2026-01-21 23:06:15'),
(10, 3, 'False', 1, 2, '2026-01-21 23:06:15'),
(11, 4, '<h1>', 1, 1, '2026-01-21 23:06:15'),
(12, 4, '<h6>', 0, 2, '2026-01-21 23:06:15'),
(13, 4, '<heading>', 0, 3, '2026-01-21 23:06:15'),
(14, 4, '<head>', 0, 4, '2026-01-21 23:06:15'),
(15, 5, 'True', 1, 1, '2026-01-21 23:06:16'),
(16, 5, 'False', 0, 2, '2026-01-21 23:06:16'),
(17, 6, 'Cascading Style Sheets', 1, 1, '2026-01-21 23:06:16'),
(18, 6, 'Computer Style Sheets', 0, 2, '2026-01-21 23:06:16'),
(19, 6, 'Creative Style Sheets', 0, 3, '2026-01-21 23:06:16'),
(20, 6, 'Colorful Style Sheets', 0, 4, '2026-01-21 23:06:16'),
(21, 7, 'background-color', 1, 1, '2026-01-21 23:06:16'),
(22, 7, 'bgcolor', 0, 2, '2026-01-21 23:06:16'),
(23, 7, 'color-background', 0, 3, '2026-01-21 23:06:16'),
(24, 7, 'bg-color', 0, 4, '2026-01-21 23:06:16'),
(25, 8, 'True', 1, 1, '2026-01-21 23:06:16'),
(26, 8, 'False', 0, 2, '2026-01-21 23:06:16'),
(27, 9, 'font-size', 1, 1, '2026-01-21 23:06:16'),
(28, 9, 'text-size', 0, 2, '2026-01-21 23:06:16'),
(29, 9, 'font-style', 0, 3, '2026-01-21 23:06:16'),
(30, 9, 'text-style', 0, 4, '2026-01-21 23:06:16'),
(31, 10, 'Battle of Dunkirk', 0, 1, '2026-01-26 11:51:33'),
(32, 10, 'Battle of Stalingrad', 0, 2, '2026-01-26 11:51:33'),
(33, 10, 'Battle of the Bulge', 0, 3, '2026-01-26 11:51:33'),
(34, 10, 'Battle of France', 1, 4, '2026-01-26 11:51:33'),
(35, 11, 'Winston Churchill', 0, 1, '2026-01-26 11:51:33'),
(36, 11, 'Charles de Gaulle', 0, 2, '2026-01-26 11:51:33'),
(37, 11, 'Adolf Hitler', 0, 3, '2026-01-26 11:51:33'),
(38, 11, 'Philippe Pétain', 1, 4, '2026-01-26 11:51:33'),
(39, 12, 'Battle of Dunkirk', 0, 1, '2026-01-26 11:51:33'),
(40, 12, 'Battle of Britain', 0, 2, '2026-01-26 11:51:33'),
(41, 12, 'Battle of Stalingrad', 0, 3, '2026-01-26 11:51:33'),
(42, 12, 'Battle of France', 1, 4, '2026-01-26 11:51:33'),
(43, 13, 'False', 1, 1, '2026-01-26 11:51:33'),
(44, 13, 'True', 0, 2, '2026-01-26 11:51:33'),
(45, 14, 'Battle of El Alamein', 1, 1, '2026-01-26 11:51:33'),
(46, 14, 'Battle of the Bulge', 0, 2, '2026-01-26 11:51:33'),
(47, 14, 'Battle of Kasserine Pass', 0, 3, '2026-01-26 11:51:33'),
(48, 14, 'Battle of Dunkirk', 0, 4, '2026-01-26 11:51:33'),
(49, 15, 'Operation Barbarossa', 0, 1, '2026-01-26 11:51:33'),
(50, 15, 'Operation Overlord', 1, 2, '2026-01-26 11:51:33'),
(51, 15, 'Battle of the Bulge', 0, 3, '2026-01-26 11:51:33'),
(52, 15, 'Battle of Normandy', 0, 4, '2026-01-26 11:51:33'),
(53, 16, 'Battle of Dunkirk', 0, 1, '2026-01-26 11:51:33'),
(54, 16, 'Battle of the Bulge', 0, 2, '2026-01-26 11:51:33'),
(55, 16, 'Battle of Normandy', 1, 3, '2026-01-26 11:51:33'),
(56, 16, 'Battle of Stalingrad', 0, 4, '2026-01-26 11:51:33'),
(57, 17, '1939', 0, 1, '2026-01-26 11:51:40'),
(58, 17, '1940', 1, 2, '2026-01-26 11:51:40'),
(59, 17, '1941', 0, 3, '2026-01-26 11:51:40'),
(60, 17, '1942', 0, 4, '2026-01-26 11:51:40'),
(61, 18, 'True', 1, 1, '2026-01-26 11:51:40'),
(62, 18, 'False', 0, 2, '2026-01-26 11:51:40'),
(63, 19, 'Charles de Gaulle', 0, 1, '2026-01-26 11:51:40'),
(64, 19, 'Philippe Pétain', 1, 2, '2026-01-26 11:51:40'),
(65, 19, 'Winston Churchill', 0, 3, '2026-01-26 11:51:40'),
(66, 19, 'Édouard Daladier', 0, 4, '2026-01-26 11:51:40'),
(67, 20, 'Alsace-Lorraine', 1, 1, '2026-01-26 11:51:40'),
(68, 20, 'Normandy', 0, 2, '2026-01-26 11:51:40'),
(69, 20, 'Burgundy', 0, 3, '2026-01-26 11:51:40'),
(70, 20, 'Provence', 0, 4, '2026-01-26 11:51:40'),
(71, 21, 'Operation Overlord', 0, 1, '2026-01-26 11:51:40'),
(72, 21, 'Operation Barbarossa', 0, 2, '2026-01-26 11:51:40'),
(73, 21, 'Operation Sea Lion', 0, 3, '2026-01-26 11:51:40'),
(74, 21, 'Operation Fall Gelb', 1, 4, '2026-01-26 11:51:40'),
(75, 22, 'True', 1, 1, '2026-01-26 11:51:40'),
(76, 22, 'False', 0, 2, '2026-01-26 11:51:40'),
(77, 23, 'Marseille', 0, 1, '2026-01-26 11:51:40'),
(78, 23, 'Bordeaux', 0, 2, '2026-01-26 11:51:40'),
(79, 23, 'Paris', 0, 3, '2026-01-26 11:51:40'),
(80, 23, 'Vichy', 1, 4, '2026-01-26 11:51:40'),
(81, 24, 'Jean Moulin', 1, 1, '2026-01-26 11:51:40'),
(82, 24, 'Jacques Chirac', 0, 2, '2026-01-26 11:51:40'),
(83, 24, 'Simone Segouin', 0, 3, '2026-01-26 11:51:40'),
(84, 24, 'Charles de Gaulle', 0, 4, '2026-01-26 11:51:40'),
(85, 25, 'Operation Overlord', 0, 1, '2026-01-26 11:51:40'),
(86, 25, 'Operation Dragoon', 1, 2, '2026-01-26 11:51:40'),
(87, 25, 'Operation Market Garden', 0, 3, '2026-01-26 11:51:40'),
(88, 25, 'Operation Torch', 0, 4, '2026-01-26 11:51:40'),
(89, 26, 'Philippe Pétain', 0, 1, '2026-01-26 11:51:40'),
(90, 26, 'Charles de Gaulle', 1, 2, '2026-01-26 11:51:40'),
(91, 26, 'Henri Giraud', 0, 3, '2026-01-26 11:51:40'),
(92, 26, 'Alphonse Juin', 0, 4, '2026-01-26 11:51:40'),
(93, 27, 'Personal Home Page', 0, 1, '2026-01-28 14:36:55'),
(94, 27, 'PHP: Hypertext Preprocessor', 1, 2, '2026-01-28 14:36:55'),
(95, 27, 'Programming Hyper Processor', 0, 3, '2026-01-28 14:36:55'),
(96, 27, 'Public Hosting Platform', 0, 4, '2026-01-28 14:36:55'),
(97, 28, 'True', 1, 1, '2026-01-28 14:36:55'),
(98, 28, 'False', 0, 2, '2026-01-28 14:36:55'),
(99, 29, 'String', 0, 1, '2026-01-28 14:36:55'),
(100, 29, 'Boolean', 0, 2, '2026-01-28 14:36:55'),
(101, 29, 'Float', 0, 3, '2026-01-28 14:36:55'),
(102, 29, 'Long', 1, 4, '2026-01-28 14:36:55'),
(103, 30, '%', 0, 1, '2026-01-28 14:36:55'),
(104, 30, '$', 0, 2, '2026-01-28 14:36:55'),
(105, 30, '&', 0, 3, '2026-01-28 14:36:55'),
(106, 30, '<?php', 1, 4, '2026-01-28 14:36:55'),
(107, 31, 'True', 1, 1, '2026-01-28 14:36:55'),
(108, 31, 'False', 0, 2, '2026-01-28 14:36:55'),
(109, 32, 'print', 0, 1, '2026-01-28 14:36:55'),
(110, 32, 'echo', 1, 2, '2026-01-28 14:36:55'),
(111, 32, 'output', 0, 3, '2026-01-28 14:36:55'),
(112, 32, 'display', 0, 4, '2026-01-28 14:36:55'),
(113, 33, '//', 1, 1, '2026-01-28 14:36:55'),
(114, 33, '/*', 0, 2, '2026-01-28 14:36:55'),
(115, 33, '<!--', 0, 3, '2026-01-28 14:36:55'),
(116, 33, '-->', 0, 4, '2026-01-28 14:36:55'),
(117, 34, 'color', 1, 1, '2026-01-28 14:37:04'),
(118, 34, 'background-color', 0, 2, '2026-01-28 14:37:04'),
(119, 34, 'font-size', 0, 3, '2026-01-28 14:37:04'),
(120, 34, 'border-color', 0, 4, '2026-01-28 14:37:04'),
(121, 35, 'Size of the box', 0, 1, '2026-01-28 14:37:04'),
(122, 35, 'Padding and border around the content box', 1, 2, '2026-01-28 14:37:04'),
(123, 35, 'Margin around the content box', 0, 3, '2026-01-28 14:37:04'),
(124, 35, 'Border style of the box', 0, 4, '2026-01-28 14:37:04'),
(125, 36, 'background-color', 0, 1, '2026-01-28 14:37:04'),
(126, 36, 'background-image', 1, 2, '2026-01-28 14:37:04'),
(127, 36, 'color', 0, 3, '2026-01-28 14:37:04'),
(128, 36, 'background-position', 0, 4, '2026-01-28 14:37:04'),
(129, 37, 'Text alignment', 0, 1, '2026-01-28 14:37:04'),
(130, 37, 'Text decoration like underline or strikethrough', 1, 2, '2026-01-28 14:37:04'),
(131, 37, 'Text color', 0, 3, '2026-01-28 14:37:04'),
(132, 37, 'Text spacing', 0, 4, '2026-01-28 14:37:04'),
(133, 38, 'line-height', 1, 1, '2026-01-28 14:37:04'),
(134, 38, 'word-spacing', 0, 2, '2026-01-28 14:37:04'),
(135, 38, 'letter-spacing', 0, 3, '2026-01-28 14:37:04'),
(136, 38, 'text-indent', 0, 4, '2026-01-28 14:37:04'),
(137, 39, 'border-radius', 1, 1, '2026-01-28 14:37:04'),
(138, 39, 'border-style', 0, 2, '2026-01-28 14:37:04'),
(139, 39, 'border-color', 0, 3, '2026-01-28 14:37:04'),
(140, 39, 'border-width', 0, 4, '2026-01-28 14:37:04'),
(141, 40, 'table-spacing', 0, 1, '2026-01-28 14:37:04'),
(142, 40, 'cell-spacing', 0, 2, '2026-01-28 14:37:04'),
(143, 40, 'border-spacing', 1, 3, '2026-01-28 14:37:04'),
(144, 40, 'padding', 0, 4, '2026-01-28 14:37:04'),
(145, 41, 'True', 1, 1, '2026-01-28 14:37:04'),
(146, 41, 'False', 0, 2, '2026-01-28 14:37:04'),
(147, 42, 'Breaks the line', 1, 1, '2026-01-28 14:37:08'),
(148, 42, 'Creates a bold text', 0, 2, '2026-01-28 14:37:08'),
(149, 42, 'Adds a new paragraph', 0, 3, '2026-01-28 14:37:08'),
(150, 42, 'Inserts an image', 0, 4, '2026-01-28 14:37:08'),
(151, 43, '<a>', 1, 1, '2026-01-28 14:37:08'),
(152, 43, '<img>', 0, 2, '2026-01-28 14:37:08'),
(153, 43, '<div>', 0, 3, '2026-01-28 14:37:08'),
(154, 43, '<p>', 0, 4, '2026-01-28 14:37:08'),
(155, 44, 'True', 1, 1, '2026-01-28 14:37:08'),
(156, 44, 'False', 0, 2, '2026-01-28 14:37:08'),
(157, 45, '<span>', 1, 1, '2026-01-28 14:37:08'),
(158, 45, '<div>', 0, 2, '2026-01-28 14:37:08'),
(159, 45, '<a>', 1, 3, '2026-01-28 14:37:08'),
(160, 45, '<strong>', 1, 4, '2026-01-28 14:37:08'),
(161, 45, '<table>', 0, 5, '2026-01-28 14:37:08'),
(162, 46, '<ul>', 0, 1, '2026-01-28 14:37:08'),
(163, 46, '<ol>', 1, 2, '2026-01-28 14:37:08'),
(164, 46, '<li>', 0, 3, '2026-01-28 14:37:08'),
(165, 46, '<dl>', 0, 4, '2026-01-28 14:37:08'),
(166, 47, 'True', 1, 1, '2026-01-28 14:37:08'),
(167, 47, 'False', 0, 2, '2026-01-28 14:37:08'),
(168, 48, 'transition-duration', 1, 1, '2026-01-28 14:37:31'),
(169, 48, 'animation-duration', 0, 2, '2026-01-28 14:37:31'),
(170, 48, 'transition-timing-function', 0, 3, '2026-01-28 14:37:31'),
(171, 48, 'transition-property', 0, 4, '2026-01-28 14:37:31'),
(172, 49, 'color', 1, 1, '2026-01-28 14:37:31'),
(173, 49, 'font-size', 1, 2, '2026-01-28 14:37:31'),
(174, 49, 'visibility', 0, 3, '2026-01-28 14:37:31'),
(175, 49, 'width', 1, 4, '2026-01-28 14:37:31'),
(176, 49, 'background-color', 1, 5, '2026-01-28 14:37:31'),
(177, 50, 'transition-delay', 1, 1, '2026-01-28 14:37:31'),
(178, 50, 'animation-delay', 0, 2, '2026-01-28 14:37:31'),
(179, 50, 'transition-timing-function', 0, 3, '2026-01-28 14:37:31'),
(180, 50, 'transition-property', 0, 4, '2026-01-28 14:37:31'),
(181, 51, 'ease-in-out', 0, 1, '2026-01-28 14:37:31'),
(182, 51, 'ease-out', 0, 2, '2026-01-28 14:37:31'),
(183, 51, 'ease-in', 0, 3, '2026-01-28 14:37:31'),
(184, 51, 'cubic-bezier()', 1, 4, '2026-01-28 14:37:31'),
(185, 52, 'transition', 1, 1, '2026-01-28 14:37:31'),
(186, 52, 'transition-property', 0, 2, '2026-01-28 14:37:31'),
(187, 52, 'transition-duration', 0, 3, '2026-01-28 14:37:31'),
(188, 52, 'transition-delay', 0, 4, '2026-01-28 14:37:31'),
(189, 53, 'False', 1, 1, '2026-01-28 14:37:31'),
(190, 53, 'True', 0, 2, '2026-01-28 14:37:31'),
(191, 54, '<a>', 1, 1, '2026-01-31 22:51:58'),
(192, 54, '<h1>', 0, 2, '2026-01-31 22:51:58'),
(193, 54, '<p>', 0, 3, '2026-01-31 22:51:58'),
(194, 54, '<div>', 0, 4, '2026-01-31 22:51:58'),
(195, 55, 'Inserts an image', 1, 1, '2026-01-31 22:51:58'),
(196, 55, 'Creates a link', 0, 2, '2026-01-31 22:51:58'),
(197, 55, 'Defines a paragraph', 0, 3, '2026-01-31 22:51:58'),
(198, 55, 'None of the above', 0, 4, '2026-01-31 22:51:58'),
(199, 56, '<br>', 1, 1, '2026-01-31 22:51:58'),
(200, 56, '<hr>', 0, 2, '2026-01-31 22:51:58'),
(201, 56, '<p>', 0, 3, '2026-01-31 22:51:58'),
(202, 56, '<div>', 0, 4, '2026-01-31 22:51:58'),
(203, 57, 'Heading 1', 1, 1, '2026-01-31 22:51:58'),
(204, 57, 'Hyperlink', 0, 2, '2026-01-31 22:51:58'),
(205, 57, 'Horizontal rule', 0, 3, '2026-01-31 22:51:58'),
(206, 57, 'Paragraph', 0, 4, '2026-01-31 22:51:58'),
(207, 58, '<ul>', 1, 1, '2026-01-31 22:51:58'),
(208, 58, '<li>', 0, 2, '2026-01-31 22:51:58'),
(209, 58, '<ol>', 1, 3, '2026-01-31 22:51:58'),
(210, 58, '<dl>', 0, 4, '2026-01-31 22:51:58'),
(211, 59, 'Makes text bold', 1, 1, '2026-01-31 22:51:58'),
(212, 59, 'Creates a button', 0, 2, '2026-01-31 22:51:58'),
(213, 59, 'Defines a block of text', 0, 3, '2026-01-31 22:51:58'),
(214, 59, 'None of the above', 0, 4, '2026-01-31 22:51:58'),
(215, 60, '<html>', 1, 1, '2026-01-31 22:51:58'),
(216, 60, '<body>', 0, 2, '2026-01-31 22:51:58'),
(217, 60, '<head>', 0, 3, '2026-01-31 22:51:58'),
(218, 60, '<title>', 0, 4, '2026-01-31 22:51:58'),
(219, 61, 'Italicizes text', 1, 1, '2026-01-31 22:51:58'),
(220, 61, 'Inserts an image', 0, 2, '2026-01-31 22:51:58'),
(221, 61, 'Defines a block of text', 0, 3, '2026-01-31 22:51:58'),
(222, 61, 'None of the above', 0, 4, '2026-01-31 22:51:58'),
(223, 62, 'The Internet is a network of networks, while the World Wide Web is a collection of websites', 1, 1, '2026-02-01 13:28:32'),
(224, 62, 'The Internet and World Wide Web are the same thing', 0, 2, '2026-02-01 13:28:32'),
(225, 62, 'The World Wide Web existed before the Internet', 0, 3, '2026-02-01 13:28:32'),
(226, 62, 'The Internet is only used for communication', 0, 4, '2026-02-01 13:28:32'),
(227, 63, 'False', 1, 1, '2026-02-01 13:28:32'),
(228, 63, 'True', 0, 2, '2026-02-01 13:28:32'),
(229, 64, 'Email communication', 0, 1, '2026-02-01 13:28:32'),
(230, 64, 'Browsing websites', 0, 2, '2026-02-01 13:28:32'),
(231, 64, 'Online shopping', 0, 3, '2026-02-01 13:28:32'),
(232, 64, 'All of the above', 1, 4, '2026-02-01 13:28:32'),
(233, 65, 'True', 1, 1, '2026-02-01 13:28:32'),
(234, 65, 'False', 0, 2, '2026-02-01 13:28:32'),
(235, 66, 'HTTP', 1, 1, '2026-02-01 13:28:32'),
(236, 66, 'FTP', 0, 2, '2026-02-01 13:28:32'),
(237, 66, 'SMTP', 0, 3, '2026-02-01 13:28:32'),
(238, 66, 'SSH', 0, 4, '2026-02-01 13:28:32'),
(239, 67, 'True', 1, 1, '2026-02-01 13:28:32'),
(240, 67, 'False', 0, 2, '2026-02-01 13:28:32'),
(241, 68, 'Global information sharing', 1, 1, '2026-02-01 13:28:32'),
(242, 68, 'Instant messaging', 0, 2, '2026-02-01 13:28:32'),
(243, 68, 'File storage', 0, 3, '2026-02-01 13:28:32'),
(244, 68, 'None of the above', 0, 4, '2026-02-01 13:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `verification_code` varchar(50) NOT NULL,
  `download_url` varchar(255) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `course_id`, `user_id`, `verification_code`, `download_url`, `generated_at`) VALUES
(1, 1, 5, '1', NULL, '2025-12-23 23:06:16'),
(2, 1, 9, 'CERT-6976A62419459', NULL, '2026-01-25 23:24:20');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `short_description` varchar(500) NOT NULL,
  `long_description` text DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `thumbnail` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_published` tinyint(1) DEFAULT 0,
  `estimated_duration` int(11) DEFAULT 0 COMMENT 'Total duration in minutes',
  `enrollment_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `short_description`, `long_description`, `category`, `difficulty`, `thumbnail`, `created_by`, `created_at`, `updated_at`, `is_published`, `estimated_duration`, `enrollment_count`) VALUES
(1, 'Complete Web Development Bootcamp', 'Learn HTML, CSS, JavaScript, PHP and MySQL from scratch', 'This comprehensive course covers everything you need to become a full-stack web developer. Starting from the basics of HTML and CSS, progressing through JavaScript fundamentals, and culminating in backend development with PHP and MySQL. Perfect for beginners!', 'Web Development', 'beginner', NULL, 2, '2026-01-21 23:06:15', '2026-01-26 12:00:02', 1, 1800, 5),
(2, 'Advanced JavaScript & ES6+', 'Master modern JavaScript with ES6, async/await, and advanced concepts', 'Take your JavaScript skills to the next level with this advanced course. Learn ES6+ features, asynchronous programming, closures, prototypes, and modern development patterns. Includes real-world projects.', 'Programming', 'advanced', NULL, 2, '2026-01-21 23:06:15', '2026-01-21 23:06:16', 1, 900, 1),
(3, 'Database Design & MySQL Mastery', 'Learn database design, normalization, and advanced MySQL queries', 'Master the art of database design and SQL. This course covers normalization, indexing, complex queries, stored procedures, triggers, and optimization techniques. Essential for backend developers.', 'Database', 'intermediate', NULL, 3, '2026-01-21 23:06:15', '2026-01-21 23:06:16', 1, 720, 1),
(4, 'PHP & MySQL Projects', 'Build real-world applications with PHP and MySQL', 'Apply your PHP and MySQL knowledge by building actual projects including a blog system, e-commerce platform, and user authentication system. Learn best practices and security.', 'Web Development', 'intermediate', NULL, 2, '2026-01-21 23:06:15', '2026-01-21 23:06:15', 1, 1200, 0),
(5, 'Introduction to Programming', 'Start your coding journey with programming fundamentals', 'Never coded before? This beginner-friendly course introduces you to programming concepts using simple examples. Learn variables, loops, conditions, functions, and problem-solving techniques.', 'Programming', 'beginner', NULL, 3, '2026-01-21 23:06:15', '2026-01-21 23:06:16', 0, 600, 1),
(6, 'intro to testing the project', 'This is a Test', 'TESTINGGGG', 'Programing', 'advanced', '6977415655d57_1769423190.png', 1, '2026-01-26 10:26:30', '2026-01-26 10:27:38', 1, 0, 1),
(7, 'feffe', 'fewfeweedfw', 'ewtewtewttew', 'tewtew', 'beginner', NULL, 0, '2026-01-28 14:35:03', '2026-01-28 14:35:03', 1, 0, 0),
(8, 'sfasfafsaf', 'fasfasfasfa', 'fsafasfasf', 'fasfasfas', 'intermediate', NULL, 14, '2026-01-31 22:38:58', '2026-01-31 22:39:20', 1, 0, 1),
(9, 'introduction to web', 'CSCI252', 'Introduction to Web introduces the basics of how websites work, including web structure, browsers, and core technologies like HTML, CSS, and basic JavaScript. It helps learners understand fundamental web concepts and create simple web pages.', 'computer science', 'beginner', NULL, 16, '2026-02-01 13:25:42', '2026-02-01 13:30:58', 1, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `enrolled_at`, `progress_percentage`, `is_completed`, `completed_at`) VALUES
(1, 4, 1, '2026-01-21 23:06:15', 62.50, 0, NULL),
(2, 4, 2, '2026-01-21 23:06:15', 0.00, 0, NULL),
(3, 5, 1, '2026-01-21 23:06:15', 100.00, 1, NULL),
(4, 5, 3, '2026-01-21 23:06:15', 75.00, 0, NULL),
(5, 6, 1, '2026-01-21 23:06:15', 25.00, 0, NULL),
(6, 6, 5, '2026-01-21 23:06:15', 50.00, 0, NULL),
(7, 9, 1, '2026-01-25 22:29:27', 100.00, 1, '2026-01-25 21:24:11'),
(8, 9, 6, '2026-01-26 10:27:38', 0.00, 0, NULL),
(9, 7, 1, '2026-01-26 12:00:02', 12.50, 0, NULL),
(10, 11, 8, '2026-01-31 22:39:20', 0.00, 0, NULL),
(11, 17, 9, '2026-02-01 13:30:58', 0.00, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `lesson_order` int(11) NOT NULL DEFAULT 0,
  `estimated_duration` int(11) DEFAULT 0 COMMENT 'Duration in minutes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `file_attachment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `course_id`, `title`, `content`, `video_url`, `lesson_order`, `estimated_duration`, `created_at`, `updated_at`, `file_attachment`) VALUES
(1, 1, 'Introduction to Web Development', 'Welcome to the world of web development! In this lesson, we will explore what web development is, the different roles (frontend, backend, full-stack), and the technologies we will be learning throughout this course.', 'https://www.youtube.com/watch?v=example1', 1, 30, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(2, 1, 'HTML Basics - Structure & Tags', 'Learn the fundamental building blocks of web pages. We cover HTML structure, common tags like headings, paragraphs, links, images, and how to create your first web page.', 'https://www.youtube.com/watch?v=example2', 2, 45, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(3, 1, 'HTML Forms & Tables', 'Master HTML forms and tables. Learn how to create interactive forms with various input types, and how to structure data using tables. Essential for collecting user input.', 'https://www.youtube.com/watch?v=example3', 3, 60, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(4, 1, 'CSS Fundamentals', 'Introduction to CSS (Cascading Style Sheets). Learn how to style your HTML elements, work with colors, fonts, and basic layout techniques.', 'https://www.youtube.com/watch?v=example4', 4, 50, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(5, 1, 'CSS Box Model & Layout', 'Deep dive into the CSS box model, margin, padding, borders, and layout techniques including flexbox and grid. Create responsive layouts.', 'https://www.youtube.com/watch?v=example5', 5, 70, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(6, 1, 'JavaScript Introduction', 'Your first steps into programming! Learn JavaScript basics: variables, data types, operators, and how to add interactivity to web pages.', 'https://www.youtube.com/watch?v=example6', 6, 55, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(7, 1, 'JavaScript Control Flow', 'Master control structures in JavaScript: if statements, switch cases, loops (for, while), and how to control program flow.', 'https://www.youtube.com/watch?v=example7', 7, 60, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(8, 1, 'JavaScript Functions & Scope', 'Learn about functions, parameters, return values, scope, and closures. Functions are the building blocks of JavaScript programs.', 'https://www.youtube.com/watch?v=example8', 8, 65, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(9, 2, 'ES6+ Features Overview', 'Explore modern JavaScript features including let/const, arrow functions, template literals, destructuring, and spread operators.', 'https://www.youtube.com/watch?v=example9', 1, 45, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(10, 2, 'Async JavaScript - Promises', 'Master asynchronous programming with Promises. Learn how to handle async operations, error handling, and Promise chaining.', 'https://www.youtube.com/watch?v=example10', 2, 50, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(11, 2, 'Async/Await & Error Handling', 'Modern async programming with async/await syntax. Learn how to write cleaner asynchronous code and proper error handling.', 'https://www.youtube.com/watch?v=example11', 3, 55, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(12, 2, 'JavaScript Classes & OOP', 'Object-oriented programming in JavaScript. Learn about classes, inheritance, encapsulation, and design patterns.', 'https://www.youtube.com/watch?v=example12', 4, 60, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(13, 3, 'Database Fundamentals', 'Introduction to databases, DBMS, relational databases, and why we need them. Understanding data persistence and organization.', 'https://www.youtube.com/watch?v=example13', 1, 40, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(14, 3, 'Database Normalization', 'Learn normalization forms (1NF, 2NF, 3NF, BCNF) and how to design efficient, redundancy-free database schemas.', 'https://www.youtube.com/watch?v=example14', 2, 55, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(15, 3, 'Advanced SQL Queries', 'Master complex queries including JOINs, subqueries, aggregations, and window functions for powerful data analysis.', 'https://www.youtube.com/watch?v=example15', 3, 70, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(16, 3, 'Indexes & Query Optimization', 'Learn how to optimize database performance using indexes, query optimization techniques, and understanding execution plans.', 'https://www.youtube.com/watch?v=example16', 4, 50, '2026-01-21 23:06:15', '2026-01-21 23:06:15', NULL),
(17, 6, 'testing Lesson101', 'This is just a testing', 'https://youtu.be/zOjov-2OZ0E?si=LwBWBQgYOJDtfVoY', 1, 0, '2026-01-26 10:27:01', '2026-01-26 10:39:28', NULL),
(18, 7, 'fsegsegewgewgew', 'gewg', '0', 1, 100, '2026-01-28 14:35:48', '2026-01-28 14:35:48', NULL),
(19, 9, 'what is web ?', 'The web (World Wide Web) is a system of connected websites and web pages that you access through the internet using a browser. It allows people to view, share, and interact with information online using links, text, images, and videos.', 'https://www.youtube.com/watch?v=GxmfcnU3feo', 1, 30, '2026-02-01 13:27:25', '2026-02-01 13:31:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lesson_completion`
--

CREATE TABLE `lesson_completion` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_completion`
--

INSERT INTO `lesson_completion` (`id`, `lesson_id`, `user_id`, `completed_at`) VALUES
(1, 1, 4, '2026-01-21 23:06:16'),
(2, 2, 4, '2026-01-21 23:06:16'),
(3, 3, 4, '2026-01-21 23:06:16'),
(4, 4, 4, '2026-01-21 23:06:16'),
(5, 5, 4, '2026-01-21 23:06:16'),
(6, 1, 5, '2026-01-21 23:06:16'),
(7, 2, 5, '2026-01-21 23:06:16'),
(8, 3, 5, '2026-01-21 23:06:16'),
(9, 4, 5, '2026-01-21 23:06:16'),
(10, 5, 5, '2026-01-21 23:06:16'),
(11, 6, 5, '2026-01-21 23:06:16'),
(12, 7, 5, '2026-01-21 23:06:16'),
(13, 8, 5, '2026-01-21 23:06:16'),
(14, 9, 5, '2026-01-21 23:06:16'),
(15, 10, 5, '2026-01-21 23:06:16'),
(16, 11, 5, '2026-01-21 23:06:16'),
(17, 1, 6, '2026-01-21 23:06:16'),
(18, 2, 6, '2026-01-21 23:06:16'),
(19, 1, 9, '2026-01-25 23:23:33'),
(20, 2, 9, '2026-01-25 23:23:37'),
(21, 3, 9, '2026-01-25 23:23:41'),
(22, 4, 9, '2026-01-25 23:23:44'),
(23, 5, 9, '2026-01-25 23:23:47'),
(24, 6, 9, '2026-01-25 23:24:04'),
(25, 7, 9, '2026-01-25 23:24:08'),
(26, 8, 9, '2026-01-25 23:24:11'),
(27, 1, 7, '2026-01-26 12:00:55');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('mcq','true_false','multiple_select') DEFAULT 'mcq',
  `points` int(11) DEFAULT 1,
  `question_order` int(11) DEFAULT 0,
  `explanation` text DEFAULT NULL COMMENT 'Explanation for correct answer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `quiz_id`, `question_text`, `question_type`, `points`, `question_order`, `explanation`, `created_at`) VALUES
(1, 1, 'What does HTML stand for?', 'mcq', 1, 1, NULL, '2026-01-21 23:06:15'),
(2, 1, 'Which HTML tag is used to create a hyperlink?', 'mcq', 1, 2, NULL, '2026-01-21 23:06:15'),
(3, 1, 'The <img> tag requires a closing tag.', 'true_false', 1, 3, NULL, '2026-01-21 23:06:15'),
(4, 1, 'Which tag is used to define the most important heading?', 'mcq', 1, 4, NULL, '2026-01-21 23:06:15'),
(5, 1, 'HTML5 is the latest version of HTML.', 'true_false', 1, 5, NULL, '2026-01-21 23:06:15'),
(6, 2, 'What does CSS stand for?', 'mcq', 1, 1, NULL, '2026-01-21 23:06:16'),
(7, 2, 'Which property is used to change the background color?', 'mcq', 1, 2, NULL, '2026-01-21 23:06:16'),
(8, 2, 'CSS can be included inline, internal, and external.', 'true_false', 1, 3, NULL, '2026-01-21 23:06:16'),
(9, 2, 'Which CSS property controls the text size?', 'mcq', 1, 4, NULL, '2026-01-21 23:06:16'),
(10, 8, 'Which battle marked the German invasion of France in 1940?', 'mcq', 3, 1, NULL, '2026-01-26 11:51:33'),
(11, 8, 'Who was the French leader during the Battle of France?', 'mcq', 2, 2, NULL, '2026-01-26 11:51:33'),
(12, 8, 'Which battle resulted in the surrender of France to Germany in 1940?', 'mcq', 3, 3, NULL, '2026-01-26 11:51:33'),
(13, 8, 'True or False: The Maginot Line effectively protected France from German invasion during WW2.', 'true_false', 2, 4, NULL, '2026-01-26 11:51:33'),
(14, 8, 'Which battle was a major turning point in favor of the Allies against Germany in North Africa?', 'mcq', 3, 5, NULL, '2026-01-26 11:51:33'),
(15, 8, 'Which battle marked the largest airborne operation in history at the time during WW2?', 'mcq', 4, 6, NULL, '2026-01-26 11:51:33'),
(16, 8, 'Which battle in 1944 saw the successful Allied invasion of German-occupied France?', 'multiple_select', 5, 7, NULL, '2026-01-26 11:51:33'),
(17, 9, 'When did Germany invade France in WW2?', 'mcq', 3, 1, NULL, '2026-01-26 11:51:40'),
(18, 9, 'True or False: France surrendered to Germany after the Battle of Dunkirk.', 'true_false', 2, 2, NULL, '2026-01-26 11:51:40'),
(19, 9, 'Which French leader signed the armistice with Germany in 1940?', 'mcq', 4, 3, NULL, '2026-01-26 11:51:40'),
(20, 9, 'Which region of France was under direct German occupation during WW2?', 'multiple_select', 5, 4, NULL, '2026-01-26 11:51:40'),
(21, 9, 'What was the code name for the German invasion of France in 1940?', 'mcq', 3, 5, NULL, '2026-01-26 11:51:40'),
(22, 9, 'True or False: The Vichy government in France collaborated with Nazi Germany during WW2.', 'true_false', 2, 6, NULL, '2026-01-26 11:51:40'),
(23, 9, 'Which French city served as the capital of German-occupied France during WW2?', 'mcq', 4, 7, NULL, '2026-01-26 11:51:40'),
(24, 9, 'Which French resistance leader was known for his daring raids against German forces?', 'mcq', 4, 8, NULL, '2026-01-26 11:51:40'),
(25, 9, 'Which military operation led to the liberation of Paris from German occupation in 1944?', 'mcq', 3, 9, NULL, '2026-01-26 11:51:40'),
(26, 9, 'Which French general famously led the Free French Forces during WW2?', 'mcq', 5, 10, NULL, '2026-01-26 11:51:40'),
(27, 10, 'What does PHP stand for?', 'mcq', 2, 1, NULL, '2026-01-28 14:36:55'),
(28, 10, 'Is PHP a server-side scripting language?', 'true_false', 1, 2, NULL, '2026-01-28 14:36:55'),
(29, 10, 'Which of the following is not a PHP data type?', 'mcq', 2, 3, NULL, '2026-01-28 14:36:55'),
(30, 10, 'What symbol is used to begin a PHP code block?', 'mcq', 2, 4, NULL, '2026-01-28 14:36:55'),
(31, 10, 'Can PHP work with databases?', 'true_false', 1, 5, NULL, '2026-01-28 14:36:55'),
(32, 10, 'Which function is used to output data in PHP?', 'mcq', 2, 6, NULL, '2026-01-28 14:36:55'),
(33, 10, 'Which of the following is a correct way to start a single-line comment in PHP?', 'mcq', 2, 7, NULL, '2026-01-28 14:36:55'),
(34, 11, 'Which property is used to change the text color in CSS?', 'mcq', 2, 1, NULL, '2026-01-28 14:37:04'),
(35, 11, 'What does the \'box-sizing\' property in CSS control?', 'mcq', 3, 2, NULL, '2026-01-28 14:37:04'),
(36, 11, 'Which CSS property is used to set the background image of an element?', 'mcq', 2, 3, NULL, '2026-01-28 14:37:04'),
(37, 11, 'What does the \'text-decoration\' property in CSS control?', 'mcq', 3, 4, NULL, '2026-01-28 14:37:04'),
(38, 11, 'Which CSS property is used to control the spacing between lines of text?', 'mcq', 2, 5, NULL, '2026-01-28 14:37:04'),
(39, 11, 'Select the CSS properties that can be used to create rounded corners for an element.', 'multiple_select', 4, 6, NULL, '2026-01-28 14:37:04'),
(40, 11, 'Which CSS property is used to specify the space between cells in a table?', 'mcq', 2, 7, NULL, '2026-01-28 14:37:04'),
(41, 11, 'True or False: The \'display: inline-block;\' property value makes an element behave like an inline element but allows it to have block-level properties.', 'true_false', 3, 8, NULL, '2026-01-28 14:37:04'),
(42, 12, 'What does the <br> tag do in HTML?', 'mcq', 3, 1, NULL, '2026-01-28 14:37:08'),
(43, 12, 'Which of the following tags is used for creating a hyperlink?', 'mcq', 4, 2, NULL, '2026-01-28 14:37:08'),
(44, 12, 'True or False: The <title> tag is used to define the title of an HTML document.', 'true_false', 2, 3, NULL, '2026-01-28 14:37:08'),
(45, 12, 'Select the inline HTML tag(s):', 'multiple_select', 5, 4, NULL, '2026-01-28 14:37:08'),
(46, 12, 'Which tag is used to create a numbered list in HTML?', 'mcq', 3, 5, NULL, '2026-01-28 14:37:08'),
(47, 12, 'True or False: The <h1> tag represents the most important heading in HTML.', 'true_false', 2, 6, NULL, '2026-01-28 14:37:08'),
(48, 13, 'What property is used to specify the duration of a CSS transition?', 'mcq', 3, 1, NULL, '2026-01-28 14:37:31'),
(49, 13, 'Which of the following CSS properties can be transitioned?', 'multiple_select', 4, 2, NULL, '2026-01-28 14:37:31'),
(50, 13, 'How can you create a delay before a CSS transition starts?', 'mcq', 3, 3, NULL, '2026-01-28 14:37:31'),
(51, 13, 'What value of the transition-timing-function property will make a transition start slow, then speed up, then slow down again?', 'mcq', 4, 4, NULL, '2026-01-28 14:37:31'),
(52, 13, 'Which CSS property allows you to apply multiple transitions to different CSS properties?', 'mcq', 4, 5, NULL, '2026-01-28 14:37:31'),
(53, 13, 'True or False: CSS transitions only work when triggered by a user action, such as a hover or click event.', 'true_false', 2, 6, NULL, '2026-01-28 14:37:31'),
(54, 15, 'Which tag is used to create a hyperlink in HTML?', 'mcq', 2, 1, NULL, '2026-01-31 22:51:58'),
(55, 15, 'What does the <img> tag do in HTML?', 'mcq', 2, 2, NULL, '2026-01-31 22:51:58'),
(56, 15, 'Which tag is used to create a line break in HTML?', 'mcq', 2, 3, NULL, '2026-01-31 22:51:58'),
(57, 15, 'What does the <h1> tag represent in HTML?', 'mcq', 2, 4, NULL, '2026-01-31 22:51:58'),
(58, 15, 'Which of the following tags is used to create a list in HTML?', 'multiple_select', 3, 5, NULL, '2026-01-31 22:51:58'),
(59, 15, 'What does the <b> tag do in HTML?', 'mcq', 2, 6, NULL, '2026-01-31 22:51:58'),
(60, 15, 'Which tag is used to define the structure of an HTML document?', 'mcq', 2, 7, NULL, '2026-01-31 22:51:58'),
(61, 15, 'What does the <i> tag do in HTML?', 'mcq', 2, 8, NULL, '2026-01-31 22:51:58'),
(62, 16, 'What is the main difference between the Internet and the World Wide Web?', 'mcq', 2, 1, NULL, '2026-02-01 13:28:32'),
(63, 16, 'True or False: The Internet and the World Wide Web are terms that can be used interchangeably.', 'true_false', 1, 2, NULL, '2026-02-01 13:28:32'),
(64, 16, 'Which of the following is a function of the Internet?', 'mcq', 2, 3, NULL, '2026-02-01 13:28:32'),
(65, 16, 'True or False: The World Wide Web is a collection of interconnected documents and resources accessed through the Internet.', 'true_false', 1, 4, NULL, '2026-02-01 13:28:32'),
(66, 16, 'Which protocol is commonly used on the Internet for transferring web pages?', 'mcq', 2, 5, NULL, '2026-02-01 13:28:32'),
(67, 16, 'True or False: The Internet is a physical network of computers and servers connected globally.', 'true_false', 1, 6, NULL, '2026-02-01 13:28:32'),
(68, 16, 'Which of the following is a major benefit of the World Wide Web?', 'mcq', 2, 7, NULL, '2026-02-01 13:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lesson_id` int(11) DEFAULT NULL COMMENT 'NULL = course-level quiz',
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `passing_score` int(11) DEFAULT 70 COMMENT 'Percentage',
  `time_limit` int(11) DEFAULT NULL COMMENT 'Time in minutes, NULL = no limit',
  `max_attempts` int(11) DEFAULT NULL COMMENT 'NULL = unlimited',
  `show_correct_answers` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `course_id`, `lesson_id`, `title`, `description`, `passing_score`, `time_limit`, `max_attempts`, `show_correct_answers`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'HTML Basics Quiz', 'Test your knowledge of HTML fundamentals and tags', 70, 15, NULL, 1, '2026-01-21 23:06:15', '2026-01-21 23:06:15'),
(2, 1, 4, 'CSS Fundamentals Quiz', 'Assess your understanding of CSS basics', 75, 20, NULL, 1, '2026-01-21 23:06:15', '2026-01-21 23:06:15'),
(3, 1, 6, 'JavaScript Basics Quiz', 'Quiz on JavaScript fundamentals', 70, 25, 3, 1, '2026-01-21 23:06:15', '2026-01-21 23:06:15'),
(4, 1, NULL, 'Web Development Final Exam', 'Comprehensive test covering all course topics', 80, 60, 2, 0, '2026-01-21 23:06:15', '2026-01-21 23:06:15'),
(5, 2, 1, 'ES6 Features Quiz', 'Test your knowledge of modern JavaScript features', 75, 20, NULL, 1, '2026-01-21 23:06:15', '2026-01-21 23:06:15'),
(6, 2, 2, 'Promises & Async Quiz', 'Quiz on asynchronous JavaScript', 80, 25, NULL, 1, '2026-01-21 23:06:15', '2026-01-21 23:06:15'),
(7, 3, 2, 'Database Normalization Quiz', 'Test your understanding of normalization', 75, 30, NULL, 1, '2026-01-21 23:06:15', '2026-01-21 23:06:15'),
(8, 1, NULL, 'WW2 Battle Quiz: Germany vs France', 'Test your knowledge on key battles between Germany and France during WW2', 70, 30, NULL, 1, '2026-01-26 11:51:33', '2026-01-26 11:51:33'),
(9, 1, NULL, 'Advanced WW2 History: Germany and France', 'Challenge yourself with advanced questions on the interactions between Germany and France in WW2', 80, 45, NULL, 1, '2026-01-26 11:51:40', '2026-01-26 11:51:40'),
(10, 7, NULL, 'PHP Basics Quiz', 'Test your knowledge of basic PHP concepts', 60, 20, NULL, 1, '2026-01-28 14:36:55', '2026-01-28 14:36:55'),
(11, 7, NULL, 'CSS Styling Quiz', 'Test your understanding of CSS styling properties', 70, 30, NULL, 1, '2026-01-28 14:37:04', '2026-01-28 14:37:04'),
(12, 7, NULL, 'HTML Tags Quiz', 'Test your knowledge of HTML tags and attributes', 80, 45, NULL, 1, '2026-01-28 14:37:08', '2026-01-28 14:37:08'),
(13, 7, NULL, 'Advanced CSS Transitions Quiz', 'Challenge your understanding of CSS transitions', 80, 45, NULL, 1, '2026-01-28 14:37:31', '2026-01-28 14:37:31'),
(14, 8, NULL, 'dffgsdg', '', 70, 30, 1, 1, '2026-01-31 22:48:33', '2026-01-31 22:48:33'),
(15, 8, NULL, 'HTML Tags Quiz', 'Quiz on different HTML tags and their usage', 70, 30, NULL, 1, '2026-01-31 22:51:58', '2026-01-31 22:51:58'),
(16, 9, NULL, 'Internet vs. World Wide Web Quiz', 'Explore the differences between the Internet and the World Wide Web', 60, 20, NULL, 1, '2026-02-01 13:28:32', '2026-02-01 13:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `earned_points` int(11) NOT NULL DEFAULT 0,
  `passed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` datetime NOT NULL,
  `time_taken` int(11) DEFAULT NULL COMMENT 'Time in seconds',
  `answers_json` text DEFAULT NULL COMMENT 'JSON encoded user answers',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `quiz_id`, `user_id`, `score`, `total_points`, `earned_points`, `passed`, `completed_at`, `time_taken`, `answers_json`, `created_at`) VALUES
(1, 14, 11, 0.00, 0, 0, 0, '2026-02-01 15:11:24', 652, '[]', '2026-02-01 13:11:24'),
(2, 15, 11, 0.00, 17, 0, 0, '2026-02-01 15:13:12', 89, '{\"question_54\":\"192\",\"question_55\":\"196\",\"question_56\":\"200\",\"question_57\":\"204\",\"question_58\":[\"208\"],\"question_59\":\"212\",\"question_60\":\"216\",\"question_61\":null}', '2026-02-01 13:13:12'),
(3, 16, 17, 36.36, 11, 4, 0, '2026-02-01 15:32:32', 13, '{\"question_62\":\"223\",\"question_63\":\"227\",\"question_64\":\"231\",\"question_65\":\"233\",\"question_66\":\"236\",\"question_67\":\"240\",\"question_68\":null}', '2026-02-01 13:32:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','instructor','admin') DEFAULT 'student',
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `profile_picture`, `bio`, `created_at`, `updated_at`, `is_active`) VALUES
(11, 'saabaaaadfddwd', 'Saab11@gmail.com', '$2y$10$3k9AbUnAzv9ilUbqcefyPeExR9eg5xJphfRxZ2YdnBD8KgRJGlKx6', 'student', NULL, NULL, '2026-01-28 15:06:56', '2026-01-28 15:07:18', 1),
(14, 'saabaaaa1', 'saaba4444@gmail.com', '$2y$10$ig4pl3K48gSCR.udnQdFfuPjnUCh9UxV4zQpyqB7c0mfVfq8zmlvG', 'instructor', NULL, NULL, '2026-01-31 22:38:05', '2026-02-01 13:18:16', 1),
(15, 'saaba8', 'saaba325@gmail.com', '$2y$10$YhBhaO6W4z/oHo1ppzVK.OuRF1kdWlg3V/PM6iii3tTImqPRyR/Qy', 'admin', NULL, NULL, '2026-02-01 13:16:18', '2026-02-01 13:17:27', 1),
(16, 'saab3333', 'saabinstructor@gmail.com', '$2y$10$5WMu15xaNxCEcxbayRCptOqEmw03P8FTmO/K9FUVOPpzLh9nFzrSa', 'instructor', NULL, NULL, '2026-02-01 13:22:37', '2026-02-01 13:22:37', 1),
(17, 'ali saab', 'saabstudent@gmail.com', '$2y$10$Wt09UPwAzfWS5mMX6M.VY.CkjjkTFkhBgtJw5QdlwquY6zlS5iaDe', 'student', NULL, NULL, '2026-02-01 13:29:42', '2026-02-01 13:29:42', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_question` (`question_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `verification_code` (`verification_code`),
  ADD UNIQUE KEY `unique_certificate` (`course_id`,`user_id`),
  ADD KEY `idx_verification` (`verification_code`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_difficulty` (`difficulty`),
  ADD KEY `idx_published` (`is_published`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `idx_user_course` (`user_id`,`course_id`),
  ADD KEY `idx_completed` (`is_completed`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_order` (`course_id`,`lesson_order`),
  ADD KEY `idx_course` (`course_id`);

--
-- Indexes for table `lesson_completion`
--
ALTER TABLE `lesson_completion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_completion` (`lesson_id`,`user_id`),
  ADD KEY `idx_user_lesson` (`user_id`,`lesson_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz` (`quiz_id`),
  ADD KEY `idx_quiz_order` (`quiz_id`,`question_order`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_lesson` (`lesson_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_quiz_id` (`quiz_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_quiz_user` (`quiz_id`,`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `lesson_completion`
--
ALTER TABLE `lesson_completion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `fk_quiz_attempts_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_quiz_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
