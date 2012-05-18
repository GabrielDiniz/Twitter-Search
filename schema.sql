-- phpMyAdmin SQL Dump
-- version 3.3.4
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: Mai 18, 2012 as 11:46 AM
-- Versão do Servidor: 5.5.22
-- Versão do PHP: 5.3.10-1ubuntu3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Banco de Dados: `t`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `wait` float NOT NULL,
  `fator` float NOT NULL,
  `maximo` int(11) NOT NULL,
  `minimo` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `queries`
--

CREATE TABLE IF NOT EXISTS `queries` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `query` varchar(100) DEFAULT NULL,
  `ultimo` varchar(20) NOT NULL,
  `frequencia` double NOT NULL DEFAULT '100',
  `ultima_execucao` double NOT NULL,
  `block` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tweet`
--

CREATE TABLE IF NOT EXISTS `tweet` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `tweet` text,
  `id_tweeter` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_tweeter` (`id_tweeter`),
  FULLTEXT KEY `tweet` (`tweet`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

INSERT INTO `config` (`wait`, `fator`, `maximo`, `minimo`) VALUES
(0.1, 1000, 7, 10);

