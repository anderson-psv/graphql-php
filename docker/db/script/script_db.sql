-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql-db
-- Tempo de geração: 24-Jan-2022 às 00:48
-- Versão do servidor: 8.0.28
-- versão do PHP: 7.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `db_graphql`
--
CREATE DATABASE IF NOT EXISTS `db_graphql` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `db_graphql`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `graph_categoria`
--

CREATE TABLE `graph_categoria` (
  `idcategoria` int NOT NULL,
  `descricao` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `idcategoria_pai` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `graph_categoria`
--

INSERT INTO `graph_categoria` (`idcategoria`, `descricao`, `idcategoria_pai`) VALUES
(1, 'Esportivos', NULL),
(2, 'Tenis Futebol', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `graph_pedido`
--

CREATE TABLE `graph_pedido` (
  `idpedido` int NOT NULL,
  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `graph_pedido`
--

INSERT INTO `graph_pedido` (`idpedido`, `status`) VALUES
(1, 'TENTATIVA_PAGAMENTO'),
(2, 'PAGO'),
(3, 'PENDENTE_PAGAMENTO'),
(4, 'CANCELADO');

-- --------------------------------------------------------

--
-- Estrutura da tabela `graph_pedido_item`
--

CREATE TABLE `graph_pedido_item` (
  `iditem` int NOT NULL,
  `idproduto` int NOT NULL,
  `idpedido` int NOT NULL,
  `valor` decimal(10,0) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `graph_pedido_item`
--

INSERT INTO `graph_pedido_item` (`iditem`, `idproduto`, `idpedido`, `valor`) VALUES
(1, 1, 1, '51'),
(2, 2, 2, '48'),
(3, 1, 3, '51'),
(4, 3, 4, '90');

-- --------------------------------------------------------

--
-- Estrutura da tabela `graph_produto`
--

CREATE TABLE `graph_produto` (
  `idproduto` int NOT NULL,
  `descricao` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `valor` decimal(10,0) DEFAULT '0',
  `idcategoria` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;

--
-- Extraindo dados da tabela `graph_produto`
--

INSERT INTO `graph_produto` (`idproduto`, `descricao`, `valor`, `idcategoria`) VALUES
(1, 'Tênis Nike para Futebol', '51', 2),
(2, 'Tenis Puma para Futebol', '48', 2),
(3, 'Capacete Rugbi', '90', 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `graph_categoria`
--
ALTER TABLE `graph_categoria`
  ADD PRIMARY KEY (`idcategoria`);

--
-- Índices para tabela `graph_pedido`
--
ALTER TABLE `graph_pedido`
  ADD PRIMARY KEY (`idpedido`);

--
-- Índices para tabela `graph_pedido_item`
--
ALTER TABLE `graph_pedido_item`
  ADD PRIMARY KEY (`iditem`);

--
-- Índices para tabela `graph_produto`
--
ALTER TABLE `graph_produto`
  ADD PRIMARY KEY (`idproduto`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `graph_categoria`
--
ALTER TABLE `graph_categoria`
  MODIFY `idcategoria` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `graph_pedido`
--
ALTER TABLE `graph_pedido`
  MODIFY `idpedido` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `graph_pedido_item`
--
ALTER TABLE `graph_pedido_item`
  MODIFY `iditem` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `graph_produto`
--
ALTER TABLE `graph_produto`
  MODIFY `idproduto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
