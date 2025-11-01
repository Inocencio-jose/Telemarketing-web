-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 01-Nov-2025 às 20:27
-- Versão do servidor: 10.4.11-MariaDB
-- versão do PHP: 7.4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `db_tl`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `tb_roteiro`
--

CREATE TABLE `tb_roteiro` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `tb_roteiro`
--

INSERT INTO `tb_roteiro` (`id`, `titulo`, `conteudo`, `criado_em`, `atualizado_em`) VALUES
(2, 'Introdução Ante-Projeto', 'A tecnológia tem sido um dos grandes suportes para o desenvolvimento e simplificação das atividades humanas em todas as áreas formativas e informativas. Encontramos softwares (sistemas informatizados) para melhorar a produtividade nas tarefas diarias, favorecendo a população no quesito rápidas respostas e a simplificação de muitas atividades.\r\nSegundo Rezende e Abreu (2021), sistemas informatizados em instituições de saúde não apenas agilizam processos, mas também garantem maior precisão nos registros, aumentando a confiabilidade das informações utilizadas para tomada de decisões. A digitalização, além de ser uma tendência global, é considerada essencial para reduzir custos operacionais e melhorar a eficiência na gestão hospitalar.\r\nCria-se um sistema informatizado de atendimento para consultas pré-natais com o intuito de facilitar o processo de atendimneto e acompanhamento das gestantes. A criação desse sistema surge como uma solução para reforçar a assistencia da população e entregar praticidade nas tarefas.\r\nCom o objetivo de implementar a técnologia, cria-se então uma solução: Um sistema informatizado de atendimento para consultas pré-natais.', '2025-11-01 19:02:01', '2025-11-01 20:13:37'),
(3, 'Passos', '- Pega o roteiro do banco via PHP (ex: get_roteiro.php)\r\n- Mostra o texto na tela com scroll automático\r\n- Ativa o Text-to-Speech (Web Speech API) para ler o texto\r\n- Exibe um vídeo do apresentador (gravado antes, sincronizado)\r\n---\r\n🎥 ETAPA 4 – Gravação do vídeo do apresentador\r\n\r\n- Vídeo deve ser gravado lendo o texto (ou dublando a voz do TTS)\r\n- Duração parecida com o tempo do texto sendo lido\r\n- O vídeo será incluído na tela HTML ao lado ou acima do texto\r\n\r\n---\r\n\r\n🌐 ETAPA 5 – Integração com Rede Social\r\n\r\nDepois de tudo pronto:\r\n- Integra como um bloco de conteúdo ou post interativo\r\n- Pode ser incorporado via iframe ou página interna da rede social\r\n\r\n---\r\n\r\n📦 Em resumo:\r\n\r\nPRIMEIRA COISA A FAZER:\r\n1. Criar banco de dados com tabela roteiros\r\n2. Desenvolver painel PHP para cadastrar/editar textos\r\n3. Gerar a primeira apresentação com texto fixo (antes do vídeo)\r\n4. Gravar um vídeo lendo o texto (manual ou com avatar digital)\r\n5. Adicionar leitura em voz usando speechSynthesis\r\n\r\n---\r\n\r\nSe quiser, posso já te dar os arquivos prontos da etapa 1 para começar.\r\n\r\nConfirmas que queres isso agora?', '2025-11-01 19:14:11', '2025-11-01 20:14:11');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `tb_roteiro`
--
ALTER TABLE `tb_roteiro`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tb_roteiro`
--
ALTER TABLE `tb_roteiro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
