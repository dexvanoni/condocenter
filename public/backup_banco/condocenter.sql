-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 08, 2025 at 06:02 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `condocenter`
--

-- --------------------------------------------------------

--
-- Table structure for table `agregado_permissions`
--

CREATE TABLE `agregado_permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `granted_by` bigint UNSIGNED NOT NULL,
  `permission_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission_level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'view',
  `is_granted` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agregado_permissions`
--

INSERT INTO `agregado_permissions` (`id`, `user_id`, `granted_by`, `permission_key`, `permission_level`, `is_granted`, `notes`, `created_at`, `updated_at`) VALUES
(15, 6, 1, 'spaces', 'crud', 1, 'Permissão concedida por Denis Vieira Vanoni', '2025-11-08 20:28:36', '2025-11-08 20:28:36'),
(16, 6, 1, 'marketplace', 'crud', 1, 'Permissão concedida por Denis Vieira Vanoni', '2025-11-08 20:28:36', '2025-11-08 20:28:36'),
(17, 6, 1, 'pets', 'crud', 1, 'Permissão concedida por Denis Vieira Vanoni', '2025-11-08 20:28:36', '2025-11-08 20:28:36'),
(18, 6, 1, 'notifications', 'view', 1, 'Permissão concedida por Denis Vieira Vanoni', '2025-11-08 20:28:36', '2025-11-08 20:28:36'),
(19, 6, 1, 'packages', 'view', 1, 'Permissão concedida por Denis Vieira Vanoni', '2025-11-08 20:28:36', '2025-11-08 20:28:36'),
(20, 6, 1, 'messages', 'view', 1, 'Permissão concedida por Denis Vieira Vanoni', '2025-11-08 20:28:36', '2025-11-08 20:28:36'),
(21, 6, 1, 'financial', 'view', 1, 'Permissão concedida por Denis Vieira Vanoni', '2025-11-08 20:28:36', '2025-11-08 20:28:36');

-- --------------------------------------------------------

--
-- Table structure for table `assemblies`
--

CREATE TABLE `assemblies` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `agenda` json DEFAULT NULL,
  `scheduled_at` timestamp NOT NULL,
  `voting_opens_at` timestamp NULL DEFAULT NULL,
  `voting_closes_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `duration_minutes` int NOT NULL DEFAULT '120',
  `status` enum('scheduled','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `voting_type` enum('open','secret') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `urgency` enum('low','normal','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `allow_delegation` tinyint(1) NOT NULL DEFAULT '0',
  `allow_comments` tinyint(1) NOT NULL DEFAULT '0',
  `results_visibility` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'final_only',
  `voter_scope` json DEFAULT NULL,
  `minutes` text COLLATE utf8mb4_unicode_ci,
  `minutes_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assembly_allowed_roles`
--

CREATE TABLE `assembly_allowed_roles` (
  `assembly_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assembly_attachments`
--

CREATE TABLE `assembly_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `assembly_id` bigint UNSIGNED NOT NULL,
  `uploaded_by` bigint UNSIGNED NOT NULL,
  `collection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'documents',
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assembly_items`
--

CREATE TABLE `assembly_items` (
  `id` bigint UNSIGNED NOT NULL,
  `assembly_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `options` json DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `status` enum('pending','open','closed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `opens_at` timestamp NULL DEFAULT NULL,
  `closes_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assembly_status_logs`
--

CREATE TABLE `assembly_status_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `assembly_id` bigint UNSIGNED NOT NULL,
  `changed_by` bigint UNSIGNED DEFAULT NULL,
  `from_status` enum('scheduled','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` enum('scheduled','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assembly_votes`
--

CREATE TABLE `assembly_votes` (
  `id` bigint UNSIGNED NOT NULL,
  `assembly_id` bigint UNSIGNED NOT NULL,
  `assembly_item_id` bigint UNSIGNED NOT NULL,
  `voter_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED DEFAULT NULL,
  `choice` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `encrypted_choice` text COLLATE utf8mb4_unicode_ci,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audits`
--

CREATE TABLE `audits` (
  `id` bigint UNSIGNED NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint UNSIGNED NOT NULL,
  `old_values` text COLLATE utf8mb4_unicode_ci,
  `new_values` text COLLATE utf8mb4_unicode_ci,
  `url` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(1023) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audits`
--

INSERT INTO `audits` (`id`, `user_type`, `user_id`, `event`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `url`, `ip_address`, `user_agent`, `tags`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"telefone_celular\":null,\"cpf\":null,\"data_nascimento\":null,\"local_trabalho\":null,\"photo\":null}', '{\"telefone_celular\":\"67991224547\",\"cpf\":\"004.701.621-39\",\"data_nascimento\":\"1985-10-15 00:00:00\",\"local_trabalho\":\"BANT\",\"photo\":\"photos\\/users\\/user_1_1762620416_e4tAB9pPDK.jpg\"}', 'http://192.168.0.7:8000/users/1', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 19:46:56', '2025-11-08 19:46:56'),
(2, 'App\\Models\\User', 1, 'created', 'App\\Models\\Unit', 1, '[]', '{\"condominium_id\":\"1\",\"number\":\"102\",\"block\":\"3\",\"type\":\"residential\",\"situacao\":\"habitado\",\"cep\":\"59140-840\",\"logradouro\":\"Avenida Professor Clementino C\\u00e2mara\",\"numero\":\"186\",\"complemento\":null,\"bairro\":\"Cohabinal\",\"cidade\":\"Parnamirim\",\"estado\":\"RN\",\"area\":\"100\",\"floor\":\"1\",\"num_quartos\":\"3\",\"num_banheiros\":\"2\",\"notes\":null,\"is_active\":\"1\",\"id\":1}', 'http://192.168.0.7:8000/units', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 19:47:39', '2025-11-08 19:47:39'),
(3, 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"unit_id\":null}', '{\"unit_id\":\"1\"}', 'http://192.168.0.7:8000/users/1', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 19:47:56', '2025-11-08 19:47:56'),
(4, 'App\\Models\\User', 1, 'updated', 'App\\Models\\Unit', 1, '{\"block\":\"3\"}', '{\"block\":\"Bloco 3\"}', 'http://192.168.0.7:8000/units/1', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 19:55:34', '2025-11-08 19:55:34'),
(5, 'App\\Models\\User', 1, 'deleted', 'App\\Models\\Unit', 1, '{\"id\":1,\"condominium_id\":1,\"number\":\"102\",\"block\":\"Bloco 3\",\"type\":\"residential\",\"situacao\":\"habitado\",\"cep\":\"59140-840\",\"logradouro\":\"Avenida Professor Clementino C\\u00e2mara\",\"numero\":\"186\",\"complemento\":null,\"bairro\":\"Cohabinal\",\"cidade\":\"Parnamirim\",\"estado\":\"RN\",\"ideal_fraction\":\"1.0000\",\"area\":\"100.00\",\"num_quartos\":3,\"num_banheiros\":2,\"foto\":null,\"possui_dividas\":0,\"floor\":1,\"notes\":null,\"is_active\":1}', '[]', 'http://192.168.0.7:8000/units/1', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 19:59:43', '2025-11-08 19:59:43'),
(6, 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"unit_id\":1}', '{\"unit_id\":\"219\"}', 'http://192.168.0.7:8000/users/1', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 20:00:25', '2025-11-08 20:00:25'),
(7, 'App\\Models\\User', 1, 'created', 'App\\Models\\User', 6, '[]', '{\"condominium_id\":\"1\",\"unit_id\":\"219\",\"morador_vinculado_id\":\"1\",\"name\":\"Fabiana Bezerra de Souza Vanoni\",\"email\":\"fabianartv@gmail.com\",\"phone\":\"67993100550\",\"telefone_residencial\":null,\"telefone_celular\":\"67993100550\",\"telefone_comercial\":null,\"cpf\":\"004.690.481-66\",\"cnh\":null,\"data_nascimento\":\"1985-10-15 00:00:00\",\"data_entrada\":\"2025-11-08 00:00:00\",\"data_saida\":null,\"descricao_cuidados_especiais\":null,\"local_trabalho\":null,\"is_active\":\"1\",\"password\":\"$2y$12$nde9KMDw9lfkF0xxSUV4RuP5XemgqPzabE4HRhQW\\/BaQky3DpENUq\",\"senha_temporaria\":true,\"id\":6}', 'http://192.168.0.7:8000/users', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 20:14:50', '2025-11-08 20:14:50'),
(8, 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 6, '{\"qr_code\":null}', '{\"qr_code\":\"QR-690f7a8a3d92d3.13830873\"}', 'http://192.168.0.7:8000/users', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 20:14:50', '2025-11-08 20:14:50'),
(9, 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 6, '{\"photo\":null}', '{\"photo\":\"photos\\/users\\/user_6_1762622304_ZnMBgRNchI.jpg\"}', 'http://192.168.0.7:8000/users/6', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 20:18:25', '2025-11-08 20:18:25'),
(10, 'App\\Models\\User', 1, 'updated', 'App\\Models\\User', 1, '{\"photo\":\"photos\\/users\\/user_1_1762620416_e4tAB9pPDK.jpg\"}', '{\"photo\":\"photos\\/users\\/user_1_1762622323_VtxcddulwA.jpg\"}', 'http://192.168.0.7:8000/users/1', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 20:18:44', '2025-11-08 20:18:44'),
(11, 'App\\Models\\User', 6, 'updated', 'App\\Models\\User', 6, '{\"password\":\"$2y$12$nde9KMDw9lfkF0xxSUV4RuP5XemgqPzabE4HRhQW\\/BaQky3DpENUq\",\"senha_temporaria\":1}', '{\"password\":\"$2y$12$i8qKU\\/Krx3fuAIJWNbygNeBx3lvIdOrWfiLwJno.hnmaJ.AORu9p6\",\"senha_temporaria\":false}', 'http://192.168.0.7:8000/password/change', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 20:26:14', '2025-11-08 20:26:14'),
(12, 'App\\Models\\User', 1, 'created', 'App\\Models\\InternalRegulation', 1, '[]', '{\"content\":\"REGIMENTO INTERNO DO CONDOM\\u00cdNIO HABITACIONAL AUGUSTO SEVERO (CHAS)\\r\\nT\\u00cdTULO I - DISPOSI\\u00c7\\u00d5ES PRELIMINARES\\r\\nCAP\\u00cdTULO I - DA FINALIDADE E APLICA\\u00c7\\u00c3O\\r\\nArt. 1\\u00ba Este Regimento Interno tem por finalidade regulamentar o uso e a conviv\\u00eancia nas \\u00e1reas comuns e privativas do Condom\\u00ednio Habitacional Augusto Severo (CHAS), complementando o Estatuto da Administra\\u00e7\\u00e3o de Compossuidores.\\r\\nArt. 2\\u00ba O CHAS \\u00e9 constitu\\u00eddo por 120 (cento e vinte) casas e 144 (cento e quarenta e quatro) apartamentos, distribu\\u00eddos em 3 (tr\\u00eas) torres com 48 (quarenta e oito) unidades cada, destinados \\u00e0 moradia de militares, suboficiais, sargentos da For\\u00e7a A\\u00e9rea Brasileira e seus dependentes.\\r\\nArt. 3\\u00ba Este Regimento aplica-se a todos os compossuidores, seus dependentes, visitantes, prestadores de servi\\u00e7os e demais pessoas que transitarem ou permanecerem no conjunto habitacional.\\r\\nArt. 4\\u00ba Este Regimento observa subsidiariamente a Lei n\\u00ba 4.591\\/64, o C\\u00f3digo Civil Brasileiro, o M\\u00f3dulo 3 do Manual do SISPNR e demais legisla\\u00e7\\u00f5es aplic\\u00e1veis.\\r\\nArt. 5\\u00ba Em caso de conflito entre este Regimento e o Estatuto da Administra\\u00e7\\u00e3o de Compossuidores, prevalecer\\u00e1 o Estatuto.\\r\\n\\r\\nT\\u00cdTULO II - DAS \\u00c1REAS COMUNS E PRIVATIVAS\\r\\nCAP\\u00cdTULO II - DA DEFINI\\u00c7\\u00c3O DAS \\u00c1REAS\\r\\nArt. 6\\u00ba S\\u00e3o consideradas \\u00e1reas comuns:\\r\\nI - vias internas de circula\\u00e7\\u00e3o de ve\\u00edculos e pedestres;\\r\\nII - \\u00e1reas verdes, jardins e pra\\u00e7as;\\r\\nIII - playground, quadras esportivas e \\u00e1reas de lazer;\\r\\nIV - sal\\u00e3o de festas e churrasqueiras;\\r\\nV - portarias e guaritas;\\r\\nVI - estacionamentos de visitantes;\\r\\nVII - casa de m\\u00e1quinas, reservat\\u00f3rios de \\u00e1gua e instala\\u00e7\\u00f5es t\\u00e9cnicas;\\r\\nVIII - halls de entrada, corredores, escadas e elevadores dos edif\\u00edcios;\\r\\nIX - sistema de coleta de lixo;\\r\\nX - rede el\\u00e9trica, hidr\\u00e1ulica e de esgoto at\\u00e9 os pontos de liga\\u00e7\\u00e3o com as unidades;\\r\\nXI - muros, grades e cercas perimetrais;\\r\\nXII - telhados e lajes de cobertura; e\\r\\nXIII - demais \\u00e1reas n\\u00e3o individualizadas como privativas.\\r\\nArt. 7\\u00ba S\\u00e3o consideradas \\u00e1reas privativas as unidades habitacionais (casas e apartamentos) e suas respectivas vagas de garagem quando individualizadas.\\r\\n\\r\\nT\\u00cdTULO III - DO USO DAS \\u00c1REAS COMUNS\\r\\nCAP\\u00cdTULO III - DAS NORMAS GERAIS DE CONVIV\\u00caNCIA\\r\\nArt. 8\\u00ba O uso das \\u00e1reas comuns deve observar os princ\\u00edpios da boa-f\\u00e9, respeito m\\u00fatuo e finalidade social, vedando-se qualquer utiliza\\u00e7\\u00e3o que:\\r\\nI - prejudique o sossego, a salubridade ou a seguran\\u00e7a dos demais moradores;\\r\\nII - cause danos ao patrim\\u00f4nio comum;\\r\\nIII - impe\\u00e7a ou dificulte o uso pelos demais moradores; ou\\r\\nIV - descaracterize a fun\\u00e7\\u00e3o residencial do conjunto habitacional.\\r\\nArt. 9\\u00ba \\u00c9 vedado obstruir, ainda que temporariamente, vias de circula\\u00e7\\u00e3o, escadas, corredores, halls, sa\\u00eddas de emerg\\u00eancia e demais \\u00e1reas de uso comum.\\r\\nArt. 10 \\u00c9 proibido depositar entulhos, m\\u00f3veis, materiais de constru\\u00e7\\u00e3o ou quaisquer objetos nas \\u00e1reas comuns, salvo autoriza\\u00e7\\u00e3o pr\\u00e9via e por prazo determinado pela Administra\\u00e7\\u00e3o.\\r\\n\\r\\nCAP\\u00cdTULO IV - DO SIL\\u00caNCIO E SOSSEGO\\r\\nArt. 11 Fica estabelecido o hor\\u00e1rio de sil\\u00eancio das 22h \\u00e0s 6h, diariamente, devendo os moradores, seus dependentes e visitantes abster-se de produzir ru\\u00eddos que ultrapassem os n\\u00edveis aceit\\u00e1veis de toler\\u00e2ncia.\\r\\nPar\\u00e1grafo 1\\u00ba Durante o hor\\u00e1rio de sil\\u00eancio, s\\u00e3o vedados:\\r\\nI - som alto de aparelhos eletr\\u00f4nicos, instrumentos musicais ou similares;\\r\\nII - obras, reformas ou atividades ruidosas;\\r\\nIII - festas ou eventos sem autoriza\\u00e7\\u00e3o pr\\u00e9via;\\r\\nIV - uso de ferramentas el\\u00e9tricas, furadeiras, serras ou equipamentos ruidosos; e\\r\\nV - gritos, discuss\\u00f5es ou qualquer comportamento que perturbe o sossego.\\r\\nPar\\u00e1grafo 2\\u00ba Fora do hor\\u00e1rio de sil\\u00eancio, os n\\u00edveis de ru\\u00eddo devem permanecer dentro dos limites razo\\u00e1veis, observando-se a legisla\\u00e7\\u00e3o municipal aplic\\u00e1vel.\\r\\nArt. 12 Reformas e obras que gerem ru\\u00eddo somente poder\\u00e3o ser realizadas:\\r\\nI - de segunda a sexta-feira, das 8h \\u00e0s 17h;\\r\\nII - aos s\\u00e1bados, das 9h \\u00e0s 13h; e\\r\\nIII - vedadas aos domingos e feriados.\\r\\nPar\\u00e1grafo \\u00fanico Em situa\\u00e7\\u00f5es excepcionais e emergenciais, a Diretoria poder\\u00e1 autorizar obras fora dos hor\\u00e1rios estabelecidos.\\r\\n\\r\\nCAP\\u00cdTULO V - DOS ANIMAIS DOM\\u00c9STICOS\\r\\nArt. 13 \\u00c9 permitida a perman\\u00eancia de animais dom\\u00e9sticos nas unidades habitacionais, desde que observadas as condi\\u00e7\\u00f5es deste Regimento.\\r\\nArt. 14 Os propriet\\u00e1rios de animais devem:\\r\\nI - manter o animal vacinado conforme legisla\\u00e7\\u00e3o sanit\\u00e1ria vigente;\\r\\nII - impedir que o animal circule desacompanhado pelas \\u00e1reas comuns;\\r\\nIII - conduzir o animal sempre com guia\\/coleira nas \\u00e1reas comuns;\\r\\nIV - recolher imediatamente as fezes do animal, descartando-as em local apropriado;\\r\\nV - impedir que o animal defeque ou urine em \\u00e1reas comuns, jardins, playgrounds ou locais de circula\\u00e7\\u00e3o;\\r\\nVI - evitar latidos, miados ou sons excessivos que perturbem os vizinhos;\\r\\nVII - responsabilizar-se por quaisquer danos causados pelo animal; e\\r\\nVIII - impedir a perman\\u00eancia do animal em locais de uso coletivo como sal\\u00e3o de festas, playground e quadras esportivas.\\r\\nArt. 15 \\u00c9 vedado manter animais em estado de abandono, maus-tratos ou condi\\u00e7\\u00f5es insalubres.\\r\\nArt. 16 Animais com doen\\u00e7as contagiosas n\\u00e3o devem circular pelas \\u00e1reas comuns at\\u00e9 a completa recupera\\u00e7\\u00e3o.\\r\\nArt. 17 \\u00c9 proibida a cria\\u00e7\\u00e3o de animais para fins comerciais ou a manuten\\u00e7\\u00e3o de n\\u00famero excessivo de animais que caracterize canil ou gatil.\\r\\nPar\\u00e1grafo \\u00fanico Considera-se n\\u00famero excessivo a quantidade superior a:\\r\\nI - 3 (tr\\u00eas) animais de m\\u00e9dio\\/grande porte; ou\\r\\nII - 5 (cinco) animais de pequeno porte.\\r\\n\\r\\nCAP\\u00cdTULO VI - DA GARAGEM E ESTACIONAMENTO\\r\\nSe\\u00e7\\u00e3o I - Disposi\\u00e7\\u00f5es Gerais\\r\\nArt. 18 Cada unidade habitacional tem direito a, no m\\u00ednimo, 1 (uma) vaga de estacionamento, conforme especificado no Estatuto.\\r\\nArt. 19 As vagas de garagem s\\u00e3o privativas e vinculadas \\u00e0s respectivas unidades habitacionais, n\\u00e3o podendo ser locadas ou cedidas a terceiros n\\u00e3o moradores.\\r\\nArt. 20 As vagas excedentes ou descobertas poder\\u00e3o ser locadas pela Administra\\u00e7\\u00e3o aos compossuidores interessados, mediante sorteio ou crit\\u00e9rios definidos em Assembleia Geral.\\r\\nPar\\u00e1grafo \\u00fanico Os valores arrecadados com vagas excedentes reverter\\u00e3o ao Fundo de Reserva de Emerg\\u00eancia, conforme item 3.6.6, al\\u00ednea \\\"c\\\" do M\\u00f3dulo 3.\\r\\nSe\\u00e7\\u00e3o II - Normas de Utiliza\\u00e7\\u00e3o\\r\\nArt. 21 Nas \\u00e1reas de garagem e estacionamento, devem ser observadas as seguintes regras:\\r\\nI - velocidade m\\u00e1xima de 10 km\\/h;\\r\\nII - proibido estacionar em locais n\\u00e3o demarcados, em frente a outras vagas ou obstruindo a circula\\u00e7\\u00e3o;\\r\\nIII - proibido lavar ve\\u00edculos nas \\u00e1reas de garagem, salvo em local espec\\u00edfico quando existente;\\r\\nIV - proibido fazer reparos mec\\u00e2nicos que sujem ou atrapalhem os demais usu\\u00e1rios;\\r\\nV - proibido armazenar combust\\u00edveis, produtos inflam\\u00e1veis ou corrosivos;\\r\\nVI - manter os ve\\u00edculos em condi\\u00e7\\u00f5es adequadas de conserva\\u00e7\\u00e3o, sem vazamentos; e\\r\\nVII - respeitar as sinaliza\\u00e7\\u00f5es e \\u00e1reas reservadas a manobras.\\r\\nArt. 22 Ve\\u00edculos abandonados, sem condi\\u00e7\\u00f5es de uso ou sem documenta\\u00e7\\u00e3o h\\u00e1 mais de 60 (sessenta) dias poder\\u00e3o ser removidos pela Administra\\u00e7\\u00e3o, ap\\u00f3s notifica\\u00e7\\u00e3o ao propriet\\u00e1rio.\\r\\nArt. 23 Nas vias internas do conjunto habitacional, a velocidade m\\u00e1xima \\u00e9 de 20 km\\/h.\\r\\nSe\\u00e7\\u00e3o III - Estacionamento de Visitantes\\r\\nArt. 24 O estacionamento de visitantes \\u00e9 destinado exclusivamente a ve\\u00edculos de pessoas em visita aos moradores, pelo per\\u00edodo m\\u00e1ximo de 12 (doze) horas.\\r\\nPar\\u00e1grafo \\u00fanico Ve\\u00edculos que permanecerem al\\u00e9m do prazo estabelecido poder\\u00e3o ser notificados e, persistindo a irregularidade, removidos por conta e risco do propriet\\u00e1rio.\\r\\n\\r\\nCAP\\u00cdTULO VII - DAS MUDAN\\u00c7AS\\r\\nArt. 25 Mudan\\u00e7as somente poder\\u00e3o ser realizadas mediante comunica\\u00e7\\u00e3o pr\\u00e9via ao Presidente da Administra\\u00e7\\u00e3o, com anteced\\u00eancia m\\u00ednima de 48 (quarenta e oito) horas.\\r\\nArt. 26 Os hor\\u00e1rios permitidos para mudan\\u00e7as s\\u00e3o:\\r\\nI - segunda a sexta-feira: das 8h \\u00e0s 18h;\\r\\nII - s\\u00e1bados: das 9h \\u00e0s 17h; e\\r\\nIII - domingos e feriados: vedados, salvo autoriza\\u00e7\\u00e3o excepcional.\\r\\nArt. 27 Durante a mudan\\u00e7a, o morador respons\\u00e1vel deve:\\r\\nI - providenciar prote\\u00e7\\u00f5es nos elevadores (nos edif\\u00edcios), paredes e pisos das \\u00e1reas comuns;\\r\\nII - garantir que os transportadores n\\u00e3o obstruam vias de circula\\u00e7\\u00e3o;\\r\\nIII - providenciar a limpeza imediata de sujeiras decorrentes da mudan\\u00e7a;\\r\\nIV - responsabilizar-se por danos causados \\u00e0s \\u00e1reas comuns; e\\r\\nV - zelar para que n\\u00e3o haja perturba\\u00e7\\u00e3o aos demais moradores.\\r\\nArt. 28 O acesso de caminh\\u00f5es de mudan\\u00e7a deve ser previamente coordenado com a portaria para evitar congestionamentos.\\r\\n\\r\\nCAP\\u00cdTULO VIII - DOS ELEVADORES (ESPEC\\u00cdFICO PARA APARTAMENTOS)\\r\\nArt. 29 O uso dos elevadores deve observar as seguintes normas:\\r\\nI - dar prefer\\u00eancia a idosos, gestantes, pessoas com mobilidade reduzida e crian\\u00e7as de colo;\\r\\nII - n\\u00e3o sobrecarregar al\\u00e9m da capacidade indicada;\\r\\nIII - n\\u00e3o obstruir as portas impedindo o fechamento autom\\u00e1tico;\\r\\nIV - n\\u00e3o permitir que crian\\u00e7as desacompanhadas operem os elevadores;\\r\\nV - em caso de emerg\\u00eancia, aguardar socorro sem tentar sair sozinho;\\r\\nVI - proibido fumar, cuspir ou sujar o interior do elevador; e\\r\\nVII - proibido transportar materiais que danifiquem ou sujem o equipamento sem prote\\u00e7\\u00e3o adequada.\\r\\nArt. 30 Para transporte de materiais de constru\\u00e7\\u00e3o, mudan\\u00e7as ou objetos volumosos, dever\\u00e1 ser utilizado preferencialmente o elevador de servi\\u00e7o, quando existente, ou elevador social com prote\\u00e7\\u00f5es adequadas.\\r\\nArt. 31 Em caso de mau funcionamento, os usu\\u00e1rios devem comunicar imediatamente \\u00e0 Administra\\u00e7\\u00e3o e aguardar o atendimento t\\u00e9cnico.\\r\\n\\r\\nCAP\\u00cdTULO IX - DAS OBRAS E REFORMAS\\r\\nSe\\u00e7\\u00e3o I - Autoriza\\u00e7\\u00f5es\\r\\nArt. 32 Qualquer obra ou reforma nas unidades habitacionais, mesmo que interna, deve ser previamente comunicada \\u00e0 Administra\\u00e7\\u00e3o de Compossuidores.\\r\\nArt. 33 Obras estruturais ou que alterem a fachada externa dependem de autoriza\\u00e7\\u00e3o pr\\u00e9via e formal do Elo Executivo, conforme M\\u00f3dulo 3, item 3.3.7, al\\u00ednea \\\"c\\\" e \\\"p\\\".\\r\\nPar\\u00e1grafo \\u00fanico S\\u00e3o consideradas obras estruturais aquelas que envolvam:\\r\\nI - remo\\u00e7\\u00e3o ou altera\\u00e7\\u00e3o de paredes mestras;\\r\\nII - altera\\u00e7\\u00e3o de estrutura de concreto, vigas ou pilares;\\r\\nIII - modifica\\u00e7\\u00e3o da fachada, cores externas ou elementos arquitet\\u00f4nicos;\\r\\nIV - altera\\u00e7\\u00e3o do layout original das casas sem autoriza\\u00e7\\u00e3o; e\\r\\nV - amplia\\u00e7\\u00f5es ou constru\\u00e7\\u00f5es adicionais.\\r\\nArt. 34 Para casas, \\u00e9 vedado:\\r\\nI - construir al\\u00e9m dos limites do terreno da unidade;\\r\\nII - alterar o gabarito (altura) sem autoriza\\u00e7\\u00e3o;\\r\\nIII - realizar modifica\\u00e7\\u00f5es que prejudiquem a drenagem ou \\u00e1reas verdes comuns; e\\r\\nIV - construir muros, grades ou cercas fora dos padr\\u00f5es estabelecidos.\\r\\nSe\\u00e7\\u00e3o II - Normas de Execu\\u00e7\\u00e3o\\r\\nArt. 35 Durante a execu\\u00e7\\u00e3o de obras e reformas, o morador deve:\\r\\nI - observar os hor\\u00e1rios estabelecidos no Art. 12;\\r\\nII - manter os acessos e \\u00e1reas comuns limpos e desobstru\\u00eddos;\\r\\nIII - providenciar ca\\u00e7ambas ou recipientes adequados para entulhos;\\r\\nIV - retirar os entulhos em at\\u00e9 48 (quarenta e oito) horas ap\\u00f3s o t\\u00e9rmino da obra;\\r\\nV - evitar poeira excessiva, molhando os entulhos quando necess\\u00e1rio;\\r\\nVI - impedir que materiais caiam em unidades vizinhas ou \\u00e1reas comuns;\\r\\nVII - garantir que os oper\\u00e1rios utilizem os banheiros da unidade em reforma; e\\r\\nVIII - responsabilizar-se por danos causados a terceiros ou \\u00e1reas comuns.\\r\\nArt. 36 \\u00c9 vedado:\\r\\nI - depositar entulhos nas \\u00e1reas comuns al\\u00e9m do prazo estabelecido;\\r\\nII - realizar obras que comprometam a seguran\\u00e7a da edifica\\u00e7\\u00e3o;\\r\\nIII - alterar ou interferir em instala\\u00e7\\u00f5es el\\u00e9tricas, hidr\\u00e1ulicas ou de g\\u00e1s das \\u00e1reas comuns;\\r\\nIV - obstruir caixas de inspe\\u00e7\\u00e3o, hidrantes ou equipamentos de seguran\\u00e7a; e\\r\\nV - realizar obras sem a devida Anota\\u00e7\\u00e3o de Responsabilidade T\\u00e9cnica (ART) quando exig\\u00edvel.\\r\\n\\r\\nCAP\\u00cdTULO X - DO SAL\\u00c3O DE FESTAS E \\u00c1REAS DE LAZER\\r\\nArt. 37 O sal\\u00e3o de festas, churrasqueiras e demais \\u00e1reas de lazer s\\u00e3o de uso comum e podem ser reservados pelos compossuidores.\\r\\nArt. 38 A reserva deve ser feita com anteced\\u00eancia m\\u00ednima de 7 (sete) dias e m\\u00e1xima de 60 (sessenta) dias junto \\u00e0 Administra\\u00e7\\u00e3o.\\r\\nPar\\u00e1grafo 1\\u00ba Cada compossuidor poder\\u00e1 reservar o sal\\u00e3o no m\\u00e1ximo 1 (uma) vez por m\\u00eas.\\r\\nPar\\u00e1grafo 2\\u00ba Em caso de m\\u00faltiplos interessados na mesma data, ter\\u00e1 prefer\\u00eancia quem solicitar primeiro.\\r\\nArt. 39 O uso do sal\\u00e3o de festas est\\u00e1 condicionado:\\r\\nI - ao pagamento de taxa de utiliza\\u00e7\\u00e3o, conforme valor estabelecido em Assembleia Geral;\\r\\nII - ao dep\\u00f3sito cau\\u00e7\\u00e3o reembols\\u00e1vel, para cobertura de eventuais danos;\\r\\nIII - \\u00e0 entrega do espa\\u00e7o nas mesmas condi\\u00e7\\u00f5es recebidas, limpo e organizado;\\r\\nIV - ao respeito aos hor\\u00e1rios: t\\u00e9rmino obrigat\\u00f3rio \\u00e0s 23h; e\\r\\nV - \\u00e0 responsabilidade por danos causados durante o evento.\\r\\nArt. 40 \\u00c9 vedado no sal\\u00e3o de festas:\\r\\nI - realiza\\u00e7\\u00e3o de eventos com fins lucrativos ou comerciais;\\r\\nII - n\\u00famero de pessoas acima da capacidade m\\u00e1xima estabelecida;\\r\\nIII - uso de som em volume que perturbe os moradores;\\r\\nIV - consumo de drogas il\\u00edcitas;\\r\\nV - jogos de azar; e\\r\\nVI - comportamentos que atentem contra a moral e os bons costumes.\\r\\nArt. 41 A loca\\u00e7\\u00e3o do sal\\u00e3o para terceiros n\\u00e3o compossuidores somente ser\\u00e1 permitida mediante autoriza\\u00e7\\u00e3o formal da Diretoria e contrapartida financeira superior \\u00e0 cobrada dos moradores, conforme item 3.3.7, al\\u00ednea \\\"s\\\" do M\\u00f3dulo 3.\\r\\n\\r\\nCAP\\u00cdTULO XI - DOS VISITANTES\\r\\nArt. 42 A identifica\\u00e7\\u00e3o de visitantes \\u00e9 obrigat\\u00f3ria, mediante apresenta\\u00e7\\u00e3o de documento oficial com foto na portaria.\\r\\nArt. 43 O acesso de visitantes est\\u00e1 condicionado \\u00e0 autoriza\\u00e7\\u00e3o do morador visitado.\\r\\nArt. 44 O morador \\u00e9 respons\\u00e1vel pelos atos de seus visitantes, inclusive por danos causados ao patrim\\u00f4nio ou a terceiros.\\r\\nArt. 45 Visitantes que permanecerem por per\\u00edodo superior a 7 (sete) dias consecutivos devem ser cadastrados na Administra\\u00e7\\u00e3o.\\r\\nArt. 46 A perman\\u00eancia de visitantes por per\\u00edodo superior a 30 (trinta) dias caracteriza ocupa\\u00e7\\u00e3o irregular e deve ser comunicada \\u00e0 Administra\\u00e7\\u00e3o.\\r\\n\\r\\nCAP\\u00cdTULO XII - DAS ENTREGAS E PRESTADORES DE SERVI\\u00c7OS\\r\\nArt. 47 Entregadores e prestadores de servi\\u00e7os devem ser identificados na portaria, informando:\\r\\nI - nome completo;\\r\\nII - documento de identifica\\u00e7\\u00e3o;\\r\\nIII - empresa ou finalidade da visita; e\\r\\nIV - unidade de destino.\\r\\nArt. 48 \\u00c9 responsabilidade do morador informar a portaria sobre a expectativa de recebimento de entregas ou servi\\u00e7os.\\r\\nArt. 49 Prestadores de servi\\u00e7os que executem atividades nas unidades devem:\\r\\nI - portar identifica\\u00e7\\u00e3o vis\\u00edvel;\\r\\nII - utilizar os acessos de servi\\u00e7o quando existentes;\\r\\nIII - observar os hor\\u00e1rios comerciais; e\\r\\nIV - recolher e remover os res\\u00edduos gerados.\\r\\nArt. 50 A Administra\\u00e7\\u00e3o n\\u00e3o se responsabiliza por encomendas, correspond\\u00eancias ou objetos deixados com porteiros ou funcion\\u00e1rios.\\r\\nPar\\u00e1grafo \\u00fanico Encomendas devem ser entregues diretamente aos moradores ou em local espec\\u00edfico designado pela Administra\\u00e7\\u00e3o.\\r\\n\\r\\nCAP\\u00cdTULO XIII - DA PORTARIA E SEGURAN\\u00c7A\\r\\nArt. 51 A portaria funciona 24 (vinte e quatro) horas por dia, sendo respons\\u00e1vel por:\\r\\nI - controlar o acesso de pessoas e ve\\u00edculos;\\r\\nII - registrar visitantes, entregadores e prestadores de servi\\u00e7os;\\r\\nIII - acionar os moradores para autoriza\\u00e7\\u00e3o de acesso;\\r\\nIV - zelar pela seguran\\u00e7a das \\u00e1reas comuns;\\r\\nV - comunicar \\u00e0 Administra\\u00e7\\u00e3o situa\\u00e7\\u00f5es anormais ou emergenciais; e\\r\\nVI - orientar visitantes e prestadores sobre as normas do condom\\u00ednio.\\r\\nArt. 52 Os moradores devem tratar os porteiros e funcion\\u00e1rios com respeito e cordialidade.\\r\\nArt. 53 \\u00c9 vedado aos moradores:\\r\\nI - solicitar aos porteiros servi\\u00e7os estranhos \\u00e0s suas fun\\u00e7\\u00f5es;\\r\\nII - interferir nas atividades de seguran\\u00e7a; ou\\r\\nIII - permitir acesso n\\u00e3o autorizado de pessoas.\\r\\nArt. 54 Em casos de suspeita de atividade il\\u00edcita ou comportamento amea\\u00e7ador, os porteiros devem acionar imediatamente as autoridades competentes.\\r\\n\\r\\nCAP\\u00cdTULO XIV - DO LIXO E RES\\u00cdDUOS\\r\\nArt. 55 O descarte de lixo deve ser realizado em local e hor\\u00e1rios apropriados, observando-se a coleta municipal.\\r\\nArt. 56 Os moradores devem:\\r\\nI - acondicionar o lixo em sacos pl\\u00e1sticos fechados;\\r\\nII - separar res\\u00edduos recicl\\u00e1veis quando houver coleta seletiva;\\r\\nIII - n\\u00e3o depositar lixo fora dos recipientes destinados para este fim;\\r\\nIV - descartar entulhos de obras em ca\\u00e7ambas pr\\u00f3prias;\\r\\nV - n\\u00e3o descartar materiais perigosos, t\\u00f3xicos ou hospitalares no lixo comum; e\\r\\nVI - recolher as fezes de animais conforme Art. 14.\\r\\nArt. 57 \\u00c9 vedado:\\r\\nI - deixar sacos de lixo em corredores, halls ou \\u00e1reas comuns;\\r\\nII - jogar lixo ou objetos pelas janelas;\\r\\nIII - depositar lixo nos hor\\u00e1rios de sil\\u00eancio, evitando ru\\u00eddos excessivos; e\\r\\nIV - utilizar as lixeiras comuns para descarte de fezes de animais.\\r\\n\\r\\nCAP\\u00cdTULO XV - DOS FUNCION\\u00c1RIOS\\r\\nArt. 58 A Administra\\u00e7\\u00e3o de Compossuidores poder\\u00e1 contratar funcion\\u00e1rios para zeladoria, limpeza, manuten\\u00e7\\u00e3o, seguran\\u00e7a e demais servi\\u00e7os necess\\u00e1rios.\\r\\nArt. 59 Os funcion\\u00e1rios devem:\\r\\nI - portar identifica\\u00e7\\u00e3o vis\\u00edvel durante o expediente;\\r\\nII - cumprir as normas de conduta estabelecidas pela Administra\\u00e7\\u00e3o;\\r\\nIII - tratar os moradores e visitantes com respeito e cordialidade; e\\r\\nIV - zelar pelo patrim\\u00f4nio comum.\\r\\nArt. 60 \\u00c9 vedado aos moradores:\\r\\nI - contratar funcion\\u00e1rios da Administra\\u00e7\\u00e3o para servi\\u00e7os particulares durante o expediente;\\r\\nII - solicitar servi\\u00e7os n\\u00e3o previstos nas atribui\\u00e7\\u00f5es do funcion\\u00e1rio; ou\\r\\nIII - oferecer gorjetas ou vantagens que comprometam a imparcialidade do servi\\u00e7o.\\r\\n\\r\\nT\\u00cdTULO IV - DAS PROIBI\\u00c7\\u00d5ES GERAIS\\r\\nCAP\\u00cdTULO XVI - DAS VEDA\\u00c7\\u00d5ES\\r\\nArt. 61 \\u00c9 expressamente vedado no conjunto habitacional:\\r\\nI - utilizar \\u00e1reas comuns para fins comerciais, propagandas ou atividades lucrativas n\\u00e3o autorizadas;\\r\\nII - realizar reuni\\u00f5es ou eventos pol\\u00edticos, religiosos ou ideol\\u00f3gicos que causem transtornos;\\r\\nIII - afixar cartazes, faixas ou publicidade sem autoriza\\u00e7\\u00e3o da Administra\\u00e7\\u00e3o;\\r\\nIV - guardar ou manipular explosivos, combust\\u00edveis, materiais corrosivos ou perigosos;\\r\\nV - remover ou danificar equipamentos de seguran\\u00e7a, hidrantes ou extintores;\\r\\nVI - alterar ou desrespeitar sinaliza\\u00e7\\u00f5es de tr\\u00e2nsito e seguran\\u00e7a;\\r\\nVII - colocar objetos nas janelas, varandas ou peitoris que possam cair;\\r\\nVIII - estender roupas em locais vis\\u00edveis externamente nos apartamentos;\\r\\nIX - cultivar plantas em locais que prejudiquem a estrutura ou escorram \\u00e1gua em unidades vizinhas;\\r\\nX - praticar jogos ou esportes que causem transtornos ou danos;\\r\\nXI - usar linguagem ofensiva, comportamento agressivo ou desrespeitoso;\\r\\nXII - portar armas de fogo sem autoriza\\u00e7\\u00e3o legal nas \\u00e1reas comuns;\\r\\nXIII - dedetizar \\u00e1reas comuns sem coordena\\u00e7\\u00e3o pr\\u00e9via com a Administra\\u00e7\\u00e3o; e\\r\\nXIV - instalar antenas, aparelhos de ar-condicionado ou equipamentos externos sem autoriza\\u00e7\\u00e3o pr\\u00e9via.\\r\\n\\r\\nT\\u00cdTULO V - DAS SAN\\u00c7\\u00d5ES\\r\\nCAP\\u00cdTULO XVII - DAS PENALIDADES\\r\\nArt. 62 O descumprimento das normas deste Regimento sujeitar\\u00e1 o infrator \\u00e0s seguintes penalidades:\\r\\nI - Advert\\u00eancia por escrito - para infra\\u00e7\\u00f5es leves ou de primeira ocorr\\u00eancia;\\r\\nII - Multa - para infra\\u00e7\\u00f5es m\\u00e9dias, reincid\\u00eancias ou infra\\u00e7\\u00f5es graves; e\\r\\nIII - Suspens\\u00e3o do uso de \\u00e1reas comuns - para infra\\u00e7\\u00f5es reiteradas ou graves relacionadas ao uso inadequado das \\u00e1reas de lazer.\\r\\nPar\\u00e1grafo 1\\u00ba As multas ter\\u00e3o os valores estabelecidos no Estatuto, revertendo ao Fundo de Reserva de Emerg\\u00eancia.\\r\\nPar\\u00e1grafo 2\\u00ba As penalidades ser\\u00e3o aplicadas pelo Presidente da Administra\\u00e7\\u00e3o, cabendo recurso \\u00e0 Assembleia Geral.\\r\\nArt. 63 Constituem infra\\u00e7\\u00f5es leves:\\r\\nI - n\\u00e3o comunicar visitas ou prestadores de servi\\u00e7os \\u00e0 portaria;\\r\\nII - estacionar irregularmente por curto per\\u00edodo;\\r\\nIII - descumprir hor\\u00e1rios de uso de \\u00e1reas comuns; e\\r\\nIV - outras infra\\u00e7\\u00f5es que n\\u00e3o causem danos ou transtornos significativos.\\r\\nPenalidade: Advert\\u00eancia escrita.\\r\\nArt. 64 Constituem infra\\u00e7\\u00f5es m\\u00e9dias:\\r\\nI - produzir ru\\u00eddos excessivos fora do hor\\u00e1rio de sil\\u00eancio;\\r\\nII - deixar lixo em locais inadequados;\\r\\nIII - realizar obras fora dos hor\\u00e1rios permitidos;\\r\\nIV - n\\u00e3o recolher fezes de animais;\\r\\nV - desrespeitar normas de tr\\u00e2nsito interno;\\r\\nVI - obstruir temporariamente \\u00e1reas comuns; e\\r\\nVII - reincid\\u00eancia em infra\\u00e7\\u00e3o leve.\\r\\nPenalidade: Multa equivalente ao valor da Taxa de Uso do PNR.\\r\\nArt. 65 Constituem infra\\u00e7\\u00f5es graves:\\r\\nI - perturbar o sossego durante o hor\\u00e1rio de sil\\u00eancio;\\r\\nII - manter animais agressivos ou em condi\\u00e7\\u00f5es insalubres;\\r\\nIII - causar danos ao patrim\\u00f4nio comum;\\r\\nIV - realizar obras estruturais sem autoriza\\u00e7\\u00e3o;\\r\\nV - agredir verbal ou fisicamente outros moradores, funcion\\u00e1rios ou visitantes;\\r\\nVI - desrespeitar reiteradamente as normas ap\\u00f3s advert\\u00eancias;\\r\\nVII - praticar atos que comprometam a seguran\\u00e7a do conjunto;\\r\\nVIII - guardar materiais perigosos ou explosivos; e\\r\\nIX - outras infra\\u00e7\\u00f5es que causem danos significativos ou riscos.\\r\\nPenalidade: Multa em dobro e suspens\\u00e3o do uso de \\u00e1reas comuns por at\\u00e9 60 (sessenta) dias.\\r\\nArt. 66 Danos ao patrim\\u00f4nio comum dever\\u00e3o ser reparados pelo respons\\u00e1vel em at\\u00e9 30 (trinta) dias, sob pena de multa adicional de 10% (dez por cento) do valor do reparo.\\r\\nArt. 67 O n\\u00e3o pagamento de multas no prazo de 30 (trinta) dias ensejar\\u00e1:\\r\\nI - cobran\\u00e7a judicial; e\\r\\nII - comunica\\u00e7\\u00e3o ao Elo Executivo para ado\\u00e7\\u00e3o de medidas administrativas cab\\u00edveis.\\r\\n\\r\\nT\\u00cdTULO VI - DISPOSI\\u00c7\\u00d5ES FINAIS\\r\\nCAP\\u00cdTULO XVIII - DAS DISPOSI\\u00c7\\u00d5ES GERAIS\\r\\nArt. 68 Este Regimento Interno poder\\u00e1 ser alterado, a qualquer tempo, por delibera\\u00e7\\u00e3o de maioria absoluta dos compossuidores em Assembleia Geral especificamente convocada para este fim.\\r\\nArt. 69 Os casos omissos ser\\u00e3o resolvidos pelo Presidente da Administra\\u00e7\\u00e3o, com assist\\u00eancia do Conselho Fiscal, em primeira inst\\u00e2ncia, ou pela Assembleia Geral, em segunda inst\\u00e2ncia.\\r\\nArt. 70 Nos casos que envolvam interesse do Elo Executivo ou da Uni\\u00e3o, o Presidente dever\\u00e1 comunicar ao Elo Executivo para manifesta\\u00e7\\u00e3o antes de deliberar.\\r\\nArt. 71 Este Regimento n\\u00e3o poder\\u00e1, em hip\\u00f3tese alguma, contrariar dispositivos do Estatuto da Administra\\u00e7\\u00e3o de Compossuidores ou do M\\u00f3dulo 3 do Manual do SISPNR.\\r\\nArt. 72 C\\u00f3pia deste Regimento dever\\u00e1 ser entregue a todos os novos compossuidores no ato de recebimento das chaves do PNR.\\r\\nArt. 73 O desconhecimento das normas deste Regimento n\\u00e3o exime ningu\\u00e9m de seu cumprimento.\\r\\nArt. 74 A Diretoria poder\\u00e1 expedir orienta\\u00e7\\u00f5es complementares para esclarecimento ou detalhamento de normas deste Regimento, desde que n\\u00e3o contrariem seu conte\\u00fado.\\r\\nArt. 75 Este Regimento Interno entra em vigor na data de sua aprova\\u00e7\\u00e3o em Assembleia Geral.\\r\\n\\r\\n_______________________________\\r\\nPresidente da Administra\\u00e7\\u00e3o de Compossuidores\\r\\nCHAS - Condom\\u00ednio Habitacional Augusto Severo\\r\\n\\r\\nObserva\\u00e7\\u00e3o final: Este Regimento est\\u00e1 em conformidade com o C\\u00f3digo Civil Brasileiro (Lei 10.406\\/2002), Lei do Condom\\u00ednio (Lei 4.591\\/1964), M\\u00f3dulo 3 do Manual do SISPNR e NSCA 12-1, respeitando as peculiaridades de uma Administra\\u00e7\\u00e3o de Compossuidores de Pr\\u00f3prios Nacionais Residenciais da Aeron\\u00e1utica.\",\"assembly_date\":\"2025-11-07 00:00:00\",\"assembly_details\":\"Assembl\\u00e9ia Geral\",\"condominium_id\":1,\"updated_by\":1,\"is_active\":true,\"version\":1,\"id\":1}', 'http://192.168.0.7:8000/internal-regulations', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 20:36:10', '2025-11-08 20:36:10'),
(13, 'App\\Models\\User', 1, 'created', 'App\\Models\\Pet', 1, '[]', '{\"unit_id\":\"219\",\"owner_id\":\"1\",\"name\":\"Rambo\",\"type\":\"dog\",\"breed\":\"Pitbull\",\"color\":\"Marrom\",\"size\":\"large\",\"observations\":null,\"photo\":\"pets\\/fzYqDRIJmn7SE519cjqu9CIFbAelKIsPHv3BJF6y.jpg\",\"condominium_id\":1,\"qr_code\":\"PET-LFFOYGYR5U-1762623600\",\"id\":1}', 'http://192.168.0.7:8000/pets', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-11-08 20:40:00', '2025-11-08 20:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `bank_statements`
--

CREATE TABLE `bank_statements` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `uploaded_by` bigint UNSIGNED NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `storage_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statement_date` date NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` enum('pending','processing','reconciled','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `total_transactions` int NOT NULL DEFAULT '0',
  `reconciled_transactions` int NOT NULL DEFAULT '0',
  `unmatched_items` json DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('condocenter-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:58:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:19:\"manage_condominiums\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:17:\"view_condominiums\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:10:\"view_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:5;i:3;i:6;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:20:\"manage_sindico_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:21:\"manage_conselho_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:17:\"view_user_history\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:19:\"export_user_history\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:12:\"manage_units\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:10:\"view_units\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:5;i:3;i:6;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"create_units\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:10:\"edit_units\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:12:\"delete_units\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:19:\"manage_transactions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:17:\"view_transactions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;i:4;i:6;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:19:\"create_transactions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:17:\"edit_transactions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:19:\"delete_transactions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:14:\"manage_charges\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:12:\"view_charges\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;i:4;i:6;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:16:\"approve_expenses\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:22:\"view_financial_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:24:\"export_financial_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:22:\"manage_bank_statements\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:5;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:20:\"view_bank_statements\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:24:\"view_bank_reconciliation\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:27:\"view_accountability_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:29:\"export_accountability_reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:12:\"view_revenue\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:13:\"view_expenses\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:12:\"view_balance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:3;i:2;i:5;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:18:\"view_own_financial\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:13:\"manage_spaces\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:11:\"view_spaces\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:7;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:17:\"make_reservations\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:19:\"manage_reservations\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:20:\"approve_reservations\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:17:\"view_reservations\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:6;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:24:\"create_marketplace_items\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:24:\"manage_marketplace_items\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:16:\"view_marketplace\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:7;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:16:\"register_entries\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:17:\"register_packages\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:4;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:12:\"view_entries\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:6;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:13:\"view_packages\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:6;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:13:\"register_pets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:9:\"view_pets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:6;i:5;i:7;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:17:\"create_assemblies\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:17:\"manage_assemblies\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:15:\"vote_assemblies\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:15:\"view_assemblies\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;i:4;i:6;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:18:\"send_announcements\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:15:\"contact_sindico\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:16:\"send_panic_alert\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:19:\"manage_panic_alerts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:13:\"view_messages\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:5;i:4;i:6;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:20:\"manage_notifications\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:18:\"view_notifications\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:6;i:5;i:7;}}}s:5:\"roles\";a:7:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"Administrador\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:8:\"Síndico\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:15:\"Conselho Fiscal\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:10:\"Secretaria\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:7:\"Morador\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:7;s:1:\"b\";s:8:\"Agregado\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:8:\"Porteiro\";s:1:\"c\";s:3:\"web\";}}}', 1762705935);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `charges`
--

CREATE TABLE `charges` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amount` decimal(15,2) NOT NULL,
  `due_date` date NOT NULL,
  `fine_percentage` decimal(5,2) NOT NULL DEFAULT '2.00',
  `interest_rate` decimal(5,2) NOT NULL DEFAULT '1.00',
  `status` enum('pending','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `type` enum('regular','extra') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regular',
  `asaas_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `boleto_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pix_qrcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `condominiums`
--

CREATE TABLE `condominiums` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cnpj` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `marketplace_allow_agregados` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `condominiums`
--

INSERT INTO `condominiums` (`id`, `name`, `cnpj`, `address`, `city`, `state`, `zip_code`, `phone`, `email`, `description`, `is_active`, `marketplace_allow_agregados`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Conjunto Habitacional Augusto Severo', NULL, 'Rua das Palmeiras, 100', 'Natal', 'RN', '59000-000', NULL, NULL, NULL, 1, 0, '2025-11-08 19:35:37', '2025-11-08 19:35:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `entries`
--

CREATE TABLE `entries` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED DEFAULT NULL,
  `registered_by` bigint UNSIGNED NOT NULL,
  `type` enum('resident','visitor','service_provider','delivery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'visitor',
  `visitor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visitor_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visitor_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_plate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entry_type` enum('entry','exit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'entry',
  `entry_time` timestamp NULL DEFAULT NULL,
  `exit_time` timestamp NULL DEFAULT NULL,
  `authorized` tinyint(1) NOT NULL DEFAULT '0',
  `authorized_by` bigint UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `internal_regulations`
--

CREATE TABLE `internal_regulations` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `assembly_date` date DEFAULT NULL COMMENT 'Data da assembleia de aprovação',
  `assembly_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Detalhes da assembleia',
  `version` int NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `internal_regulations`
--

INSERT INTO `internal_regulations` (`id`, `condominium_id`, `content`, `assembly_date`, `assembly_details`, `version`, `is_active`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'REGIMENTO INTERNO DO CONDOMÍNIO HABITACIONAL AUGUSTO SEVERO (CHAS)\r\nTÍTULO I - DISPOSIÇÕES PRELIMINARES\r\nCAPÍTULO I - DA FINALIDADE E APLICAÇÃO\r\nArt. 1º Este Regimento Interno tem por finalidade regulamentar o uso e a convivência nas áreas comuns e privativas do Condomínio Habitacional Augusto Severo (CHAS), complementando o Estatuto da Administração de Compossuidores.\r\nArt. 2º O CHAS é constituído por 120 (cento e vinte) casas e 144 (cento e quarenta e quatro) apartamentos, distribuídos em 3 (três) torres com 48 (quarenta e oito) unidades cada, destinados à moradia de militares, suboficiais, sargentos da Força Aérea Brasileira e seus dependentes.\r\nArt. 3º Este Regimento aplica-se a todos os compossuidores, seus dependentes, visitantes, prestadores de serviços e demais pessoas que transitarem ou permanecerem no conjunto habitacional.\r\nArt. 4º Este Regimento observa subsidiariamente a Lei nº 4.591/64, o Código Civil Brasileiro, o Módulo 3 do Manual do SISPNR e demais legislações aplicáveis.\r\nArt. 5º Em caso de conflito entre este Regimento e o Estatuto da Administração de Compossuidores, prevalecerá o Estatuto.\r\n\r\nTÍTULO II - DAS ÁREAS COMUNS E PRIVATIVAS\r\nCAPÍTULO II - DA DEFINIÇÃO DAS ÁREAS\r\nArt. 6º São consideradas áreas comuns:\r\nI - vias internas de circulação de veículos e pedestres;\r\nII - áreas verdes, jardins e praças;\r\nIII - playground, quadras esportivas e áreas de lazer;\r\nIV - salão de festas e churrasqueiras;\r\nV - portarias e guaritas;\r\nVI - estacionamentos de visitantes;\r\nVII - casa de máquinas, reservatórios de água e instalações técnicas;\r\nVIII - halls de entrada, corredores, escadas e elevadores dos edifícios;\r\nIX - sistema de coleta de lixo;\r\nX - rede elétrica, hidráulica e de esgoto até os pontos de ligação com as unidades;\r\nXI - muros, grades e cercas perimetrais;\r\nXII - telhados e lajes de cobertura; e\r\nXIII - demais áreas não individualizadas como privativas.\r\nArt. 7º São consideradas áreas privativas as unidades habitacionais (casas e apartamentos) e suas respectivas vagas de garagem quando individualizadas.\r\n\r\nTÍTULO III - DO USO DAS ÁREAS COMUNS\r\nCAPÍTULO III - DAS NORMAS GERAIS DE CONVIVÊNCIA\r\nArt. 8º O uso das áreas comuns deve observar os princípios da boa-fé, respeito mútuo e finalidade social, vedando-se qualquer utilização que:\r\nI - prejudique o sossego, a salubridade ou a segurança dos demais moradores;\r\nII - cause danos ao patrimônio comum;\r\nIII - impeça ou dificulte o uso pelos demais moradores; ou\r\nIV - descaracterize a função residencial do conjunto habitacional.\r\nArt. 9º É vedado obstruir, ainda que temporariamente, vias de circulação, escadas, corredores, halls, saídas de emergência e demais áreas de uso comum.\r\nArt. 10 É proibido depositar entulhos, móveis, materiais de construção ou quaisquer objetos nas áreas comuns, salvo autorização prévia e por prazo determinado pela Administração.\r\n\r\nCAPÍTULO IV - DO SILÊNCIO E SOSSEGO\r\nArt. 11 Fica estabelecido o horário de silêncio das 22h às 6h, diariamente, devendo os moradores, seus dependentes e visitantes abster-se de produzir ruídos que ultrapassem os níveis aceitáveis de tolerância.\r\nParágrafo 1º Durante o horário de silêncio, são vedados:\r\nI - som alto de aparelhos eletrônicos, instrumentos musicais ou similares;\r\nII - obras, reformas ou atividades ruidosas;\r\nIII - festas ou eventos sem autorização prévia;\r\nIV - uso de ferramentas elétricas, furadeiras, serras ou equipamentos ruidosos; e\r\nV - gritos, discussões ou qualquer comportamento que perturbe o sossego.\r\nParágrafo 2º Fora do horário de silêncio, os níveis de ruído devem permanecer dentro dos limites razoáveis, observando-se a legislação municipal aplicável.\r\nArt. 12 Reformas e obras que gerem ruído somente poderão ser realizadas:\r\nI - de segunda a sexta-feira, das 8h às 17h;\r\nII - aos sábados, das 9h às 13h; e\r\nIII - vedadas aos domingos e feriados.\r\nParágrafo único Em situações excepcionais e emergenciais, a Diretoria poderá autorizar obras fora dos horários estabelecidos.\r\n\r\nCAPÍTULO V - DOS ANIMAIS DOMÉSTICOS\r\nArt. 13 É permitida a permanência de animais domésticos nas unidades habitacionais, desde que observadas as condições deste Regimento.\r\nArt. 14 Os proprietários de animais devem:\r\nI - manter o animal vacinado conforme legislação sanitária vigente;\r\nII - impedir que o animal circule desacompanhado pelas áreas comuns;\r\nIII - conduzir o animal sempre com guia/coleira nas áreas comuns;\r\nIV - recolher imediatamente as fezes do animal, descartando-as em local apropriado;\r\nV - impedir que o animal defeque ou urine em áreas comuns, jardins, playgrounds ou locais de circulação;\r\nVI - evitar latidos, miados ou sons excessivos que perturbem os vizinhos;\r\nVII - responsabilizar-se por quaisquer danos causados pelo animal; e\r\nVIII - impedir a permanência do animal em locais de uso coletivo como salão de festas, playground e quadras esportivas.\r\nArt. 15 É vedado manter animais em estado de abandono, maus-tratos ou condições insalubres.\r\nArt. 16 Animais com doenças contagiosas não devem circular pelas áreas comuns até a completa recuperação.\r\nArt. 17 É proibida a criação de animais para fins comerciais ou a manutenção de número excessivo de animais que caracterize canil ou gatil.\r\nParágrafo único Considera-se número excessivo a quantidade superior a:\r\nI - 3 (três) animais de médio/grande porte; ou\r\nII - 5 (cinco) animais de pequeno porte.\r\n\r\nCAPÍTULO VI - DA GARAGEM E ESTACIONAMENTO\r\nSeção I - Disposições Gerais\r\nArt. 18 Cada unidade habitacional tem direito a, no mínimo, 1 (uma) vaga de estacionamento, conforme especificado no Estatuto.\r\nArt. 19 As vagas de garagem são privativas e vinculadas às respectivas unidades habitacionais, não podendo ser locadas ou cedidas a terceiros não moradores.\r\nArt. 20 As vagas excedentes ou descobertas poderão ser locadas pela Administração aos compossuidores interessados, mediante sorteio ou critérios definidos em Assembleia Geral.\r\nParágrafo único Os valores arrecadados com vagas excedentes reverterão ao Fundo de Reserva de Emergência, conforme item 3.6.6, alínea \"c\" do Módulo 3.\r\nSeção II - Normas de Utilização\r\nArt. 21 Nas áreas de garagem e estacionamento, devem ser observadas as seguintes regras:\r\nI - velocidade máxima de 10 km/h;\r\nII - proibido estacionar em locais não demarcados, em frente a outras vagas ou obstruindo a circulação;\r\nIII - proibido lavar veículos nas áreas de garagem, salvo em local específico quando existente;\r\nIV - proibido fazer reparos mecânicos que sujem ou atrapalhem os demais usuários;\r\nV - proibido armazenar combustíveis, produtos inflamáveis ou corrosivos;\r\nVI - manter os veículos em condições adequadas de conservação, sem vazamentos; e\r\nVII - respeitar as sinalizações e áreas reservadas a manobras.\r\nArt. 22 Veículos abandonados, sem condições de uso ou sem documentação há mais de 60 (sessenta) dias poderão ser removidos pela Administração, após notificação ao proprietário.\r\nArt. 23 Nas vias internas do conjunto habitacional, a velocidade máxima é de 20 km/h.\r\nSeção III - Estacionamento de Visitantes\r\nArt. 24 O estacionamento de visitantes é destinado exclusivamente a veículos de pessoas em visita aos moradores, pelo período máximo de 12 (doze) horas.\r\nParágrafo único Veículos que permanecerem além do prazo estabelecido poderão ser notificados e, persistindo a irregularidade, removidos por conta e risco do proprietário.\r\n\r\nCAPÍTULO VII - DAS MUDANÇAS\r\nArt. 25 Mudanças somente poderão ser realizadas mediante comunicação prévia ao Presidente da Administração, com antecedência mínima de 48 (quarenta e oito) horas.\r\nArt. 26 Os horários permitidos para mudanças são:\r\nI - segunda a sexta-feira: das 8h às 18h;\r\nII - sábados: das 9h às 17h; e\r\nIII - domingos e feriados: vedados, salvo autorização excepcional.\r\nArt. 27 Durante a mudança, o morador responsável deve:\r\nI - providenciar proteções nos elevadores (nos edifícios), paredes e pisos das áreas comuns;\r\nII - garantir que os transportadores não obstruam vias de circulação;\r\nIII - providenciar a limpeza imediata de sujeiras decorrentes da mudança;\r\nIV - responsabilizar-se por danos causados às áreas comuns; e\r\nV - zelar para que não haja perturbação aos demais moradores.\r\nArt. 28 O acesso de caminhões de mudança deve ser previamente coordenado com a portaria para evitar congestionamentos.\r\n\r\nCAPÍTULO VIII - DOS ELEVADORES (ESPECÍFICO PARA APARTAMENTOS)\r\nArt. 29 O uso dos elevadores deve observar as seguintes normas:\r\nI - dar preferência a idosos, gestantes, pessoas com mobilidade reduzida e crianças de colo;\r\nII - não sobrecarregar além da capacidade indicada;\r\nIII - não obstruir as portas impedindo o fechamento automático;\r\nIV - não permitir que crianças desacompanhadas operem os elevadores;\r\nV - em caso de emergência, aguardar socorro sem tentar sair sozinho;\r\nVI - proibido fumar, cuspir ou sujar o interior do elevador; e\r\nVII - proibido transportar materiais que danifiquem ou sujem o equipamento sem proteção adequada.\r\nArt. 30 Para transporte de materiais de construção, mudanças ou objetos volumosos, deverá ser utilizado preferencialmente o elevador de serviço, quando existente, ou elevador social com proteções adequadas.\r\nArt. 31 Em caso de mau funcionamento, os usuários devem comunicar imediatamente à Administração e aguardar o atendimento técnico.\r\n\r\nCAPÍTULO IX - DAS OBRAS E REFORMAS\r\nSeção I - Autorizações\r\nArt. 32 Qualquer obra ou reforma nas unidades habitacionais, mesmo que interna, deve ser previamente comunicada à Administração de Compossuidores.\r\nArt. 33 Obras estruturais ou que alterem a fachada externa dependem de autorização prévia e formal do Elo Executivo, conforme Módulo 3, item 3.3.7, alínea \"c\" e \"p\".\r\nParágrafo único São consideradas obras estruturais aquelas que envolvam:\r\nI - remoção ou alteração de paredes mestras;\r\nII - alteração de estrutura de concreto, vigas ou pilares;\r\nIII - modificação da fachada, cores externas ou elementos arquitetônicos;\r\nIV - alteração do layout original das casas sem autorização; e\r\nV - ampliações ou construções adicionais.\r\nArt. 34 Para casas, é vedado:\r\nI - construir além dos limites do terreno da unidade;\r\nII - alterar o gabarito (altura) sem autorização;\r\nIII - realizar modificações que prejudiquem a drenagem ou áreas verdes comuns; e\r\nIV - construir muros, grades ou cercas fora dos padrões estabelecidos.\r\nSeção II - Normas de Execução\r\nArt. 35 Durante a execução de obras e reformas, o morador deve:\r\nI - observar os horários estabelecidos no Art. 12;\r\nII - manter os acessos e áreas comuns limpos e desobstruídos;\r\nIII - providenciar caçambas ou recipientes adequados para entulhos;\r\nIV - retirar os entulhos em até 48 (quarenta e oito) horas após o término da obra;\r\nV - evitar poeira excessiva, molhando os entulhos quando necessário;\r\nVI - impedir que materiais caiam em unidades vizinhas ou áreas comuns;\r\nVII - garantir que os operários utilizem os banheiros da unidade em reforma; e\r\nVIII - responsabilizar-se por danos causados a terceiros ou áreas comuns.\r\nArt. 36 É vedado:\r\nI - depositar entulhos nas áreas comuns além do prazo estabelecido;\r\nII - realizar obras que comprometam a segurança da edificação;\r\nIII - alterar ou interferir em instalações elétricas, hidráulicas ou de gás das áreas comuns;\r\nIV - obstruir caixas de inspeção, hidrantes ou equipamentos de segurança; e\r\nV - realizar obras sem a devida Anotação de Responsabilidade Técnica (ART) quando exigível.\r\n\r\nCAPÍTULO X - DO SALÃO DE FESTAS E ÁREAS DE LAZER\r\nArt. 37 O salão de festas, churrasqueiras e demais áreas de lazer são de uso comum e podem ser reservados pelos compossuidores.\r\nArt. 38 A reserva deve ser feita com antecedência mínima de 7 (sete) dias e máxima de 60 (sessenta) dias junto à Administração.\r\nParágrafo 1º Cada compossuidor poderá reservar o salão no máximo 1 (uma) vez por mês.\r\nParágrafo 2º Em caso de múltiplos interessados na mesma data, terá preferência quem solicitar primeiro.\r\nArt. 39 O uso do salão de festas está condicionado:\r\nI - ao pagamento de taxa de utilização, conforme valor estabelecido em Assembleia Geral;\r\nII - ao depósito caução reembolsável, para cobertura de eventuais danos;\r\nIII - à entrega do espaço nas mesmas condições recebidas, limpo e organizado;\r\nIV - ao respeito aos horários: término obrigatório às 23h; e\r\nV - à responsabilidade por danos causados durante o evento.\r\nArt. 40 É vedado no salão de festas:\r\nI - realização de eventos com fins lucrativos ou comerciais;\r\nII - número de pessoas acima da capacidade máxima estabelecida;\r\nIII - uso de som em volume que perturbe os moradores;\r\nIV - consumo de drogas ilícitas;\r\nV - jogos de azar; e\r\nVI - comportamentos que atentem contra a moral e os bons costumes.\r\nArt. 41 A locação do salão para terceiros não compossuidores somente será permitida mediante autorização formal da Diretoria e contrapartida financeira superior à cobrada dos moradores, conforme item 3.3.7, alínea \"s\" do Módulo 3.\r\n\r\nCAPÍTULO XI - DOS VISITANTES\r\nArt. 42 A identificação de visitantes é obrigatória, mediante apresentação de documento oficial com foto na portaria.\r\nArt. 43 O acesso de visitantes está condicionado à autorização do morador visitado.\r\nArt. 44 O morador é responsável pelos atos de seus visitantes, inclusive por danos causados ao patrimônio ou a terceiros.\r\nArt. 45 Visitantes que permanecerem por período superior a 7 (sete) dias consecutivos devem ser cadastrados na Administração.\r\nArt. 46 A permanência de visitantes por período superior a 30 (trinta) dias caracteriza ocupação irregular e deve ser comunicada à Administração.\r\n\r\nCAPÍTULO XII - DAS ENTREGAS E PRESTADORES DE SERVIÇOS\r\nArt. 47 Entregadores e prestadores de serviços devem ser identificados na portaria, informando:\r\nI - nome completo;\r\nII - documento de identificação;\r\nIII - empresa ou finalidade da visita; e\r\nIV - unidade de destino.\r\nArt. 48 É responsabilidade do morador informar a portaria sobre a expectativa de recebimento de entregas ou serviços.\r\nArt. 49 Prestadores de serviços que executem atividades nas unidades devem:\r\nI - portar identificação visível;\r\nII - utilizar os acessos de serviço quando existentes;\r\nIII - observar os horários comerciais; e\r\nIV - recolher e remover os resíduos gerados.\r\nArt. 50 A Administração não se responsabiliza por encomendas, correspondências ou objetos deixados com porteiros ou funcionários.\r\nParágrafo único Encomendas devem ser entregues diretamente aos moradores ou em local específico designado pela Administração.\r\n\r\nCAPÍTULO XIII - DA PORTARIA E SEGURANÇA\r\nArt. 51 A portaria funciona 24 (vinte e quatro) horas por dia, sendo responsável por:\r\nI - controlar o acesso de pessoas e veículos;\r\nII - registrar visitantes, entregadores e prestadores de serviços;\r\nIII - acionar os moradores para autorização de acesso;\r\nIV - zelar pela segurança das áreas comuns;\r\nV - comunicar à Administração situações anormais ou emergenciais; e\r\nVI - orientar visitantes e prestadores sobre as normas do condomínio.\r\nArt. 52 Os moradores devem tratar os porteiros e funcionários com respeito e cordialidade.\r\nArt. 53 É vedado aos moradores:\r\nI - solicitar aos porteiros serviços estranhos às suas funções;\r\nII - interferir nas atividades de segurança; ou\r\nIII - permitir acesso não autorizado de pessoas.\r\nArt. 54 Em casos de suspeita de atividade ilícita ou comportamento ameaçador, os porteiros devem acionar imediatamente as autoridades competentes.\r\n\r\nCAPÍTULO XIV - DO LIXO E RESÍDUOS\r\nArt. 55 O descarte de lixo deve ser realizado em local e horários apropriados, observando-se a coleta municipal.\r\nArt. 56 Os moradores devem:\r\nI - acondicionar o lixo em sacos plásticos fechados;\r\nII - separar resíduos recicláveis quando houver coleta seletiva;\r\nIII - não depositar lixo fora dos recipientes destinados para este fim;\r\nIV - descartar entulhos de obras em caçambas próprias;\r\nV - não descartar materiais perigosos, tóxicos ou hospitalares no lixo comum; e\r\nVI - recolher as fezes de animais conforme Art. 14.\r\nArt. 57 É vedado:\r\nI - deixar sacos de lixo em corredores, halls ou áreas comuns;\r\nII - jogar lixo ou objetos pelas janelas;\r\nIII - depositar lixo nos horários de silêncio, evitando ruídos excessivos; e\r\nIV - utilizar as lixeiras comuns para descarte de fezes de animais.\r\n\r\nCAPÍTULO XV - DOS FUNCIONÁRIOS\r\nArt. 58 A Administração de Compossuidores poderá contratar funcionários para zeladoria, limpeza, manutenção, segurança e demais serviços necessários.\r\nArt. 59 Os funcionários devem:\r\nI - portar identificação visível durante o expediente;\r\nII - cumprir as normas de conduta estabelecidas pela Administração;\r\nIII - tratar os moradores e visitantes com respeito e cordialidade; e\r\nIV - zelar pelo patrimônio comum.\r\nArt. 60 É vedado aos moradores:\r\nI - contratar funcionários da Administração para serviços particulares durante o expediente;\r\nII - solicitar serviços não previstos nas atribuições do funcionário; ou\r\nIII - oferecer gorjetas ou vantagens que comprometam a imparcialidade do serviço.\r\n\r\nTÍTULO IV - DAS PROIBIÇÕES GERAIS\r\nCAPÍTULO XVI - DAS VEDAÇÕES\r\nArt. 61 É expressamente vedado no conjunto habitacional:\r\nI - utilizar áreas comuns para fins comerciais, propagandas ou atividades lucrativas não autorizadas;\r\nII - realizar reuniões ou eventos políticos, religiosos ou ideológicos que causem transtornos;\r\nIII - afixar cartazes, faixas ou publicidade sem autorização da Administração;\r\nIV - guardar ou manipular explosivos, combustíveis, materiais corrosivos ou perigosos;\r\nV - remover ou danificar equipamentos de segurança, hidrantes ou extintores;\r\nVI - alterar ou desrespeitar sinalizações de trânsito e segurança;\r\nVII - colocar objetos nas janelas, varandas ou peitoris que possam cair;\r\nVIII - estender roupas em locais visíveis externamente nos apartamentos;\r\nIX - cultivar plantas em locais que prejudiquem a estrutura ou escorram água em unidades vizinhas;\r\nX - praticar jogos ou esportes que causem transtornos ou danos;\r\nXI - usar linguagem ofensiva, comportamento agressivo ou desrespeitoso;\r\nXII - portar armas de fogo sem autorização legal nas áreas comuns;\r\nXIII - dedetizar áreas comuns sem coordenação prévia com a Administração; e\r\nXIV - instalar antenas, aparelhos de ar-condicionado ou equipamentos externos sem autorização prévia.\r\n\r\nTÍTULO V - DAS SANÇÕES\r\nCAPÍTULO XVII - DAS PENALIDADES\r\nArt. 62 O descumprimento das normas deste Regimento sujeitará o infrator às seguintes penalidades:\r\nI - Advertência por escrito - para infrações leves ou de primeira ocorrência;\r\nII - Multa - para infrações médias, reincidências ou infrações graves; e\r\nIII - Suspensão do uso de áreas comuns - para infrações reiteradas ou graves relacionadas ao uso inadequado das áreas de lazer.\r\nParágrafo 1º As multas terão os valores estabelecidos no Estatuto, revertendo ao Fundo de Reserva de Emergência.\r\nParágrafo 2º As penalidades serão aplicadas pelo Presidente da Administração, cabendo recurso à Assembleia Geral.\r\nArt. 63 Constituem infrações leves:\r\nI - não comunicar visitas ou prestadores de serviços à portaria;\r\nII - estacionar irregularmente por curto período;\r\nIII - descumprir horários de uso de áreas comuns; e\r\nIV - outras infrações que não causem danos ou transtornos significativos.\r\nPenalidade: Advertência escrita.\r\nArt. 64 Constituem infrações médias:\r\nI - produzir ruídos excessivos fora do horário de silêncio;\r\nII - deixar lixo em locais inadequados;\r\nIII - realizar obras fora dos horários permitidos;\r\nIV - não recolher fezes de animais;\r\nV - desrespeitar normas de trânsito interno;\r\nVI - obstruir temporariamente áreas comuns; e\r\nVII - reincidência em infração leve.\r\nPenalidade: Multa equivalente ao valor da Taxa de Uso do PNR.\r\nArt. 65 Constituem infrações graves:\r\nI - perturbar o sossego durante o horário de silêncio;\r\nII - manter animais agressivos ou em condições insalubres;\r\nIII - causar danos ao patrimônio comum;\r\nIV - realizar obras estruturais sem autorização;\r\nV - agredir verbal ou fisicamente outros moradores, funcionários ou visitantes;\r\nVI - desrespeitar reiteradamente as normas após advertências;\r\nVII - praticar atos que comprometam a segurança do conjunto;\r\nVIII - guardar materiais perigosos ou explosivos; e\r\nIX - outras infrações que causem danos significativos ou riscos.\r\nPenalidade: Multa em dobro e suspensão do uso de áreas comuns por até 60 (sessenta) dias.\r\nArt. 66 Danos ao patrimônio comum deverão ser reparados pelo responsável em até 30 (trinta) dias, sob pena de multa adicional de 10% (dez por cento) do valor do reparo.\r\nArt. 67 O não pagamento de multas no prazo de 30 (trinta) dias ensejará:\r\nI - cobrança judicial; e\r\nII - comunicação ao Elo Executivo para adoção de medidas administrativas cabíveis.\r\n\r\nTÍTULO VI - DISPOSIÇÕES FINAIS\r\nCAPÍTULO XVIII - DAS DISPOSIÇÕES GERAIS\r\nArt. 68 Este Regimento Interno poderá ser alterado, a qualquer tempo, por deliberação de maioria absoluta dos compossuidores em Assembleia Geral especificamente convocada para este fim.\r\nArt. 69 Os casos omissos serão resolvidos pelo Presidente da Administração, com assistência do Conselho Fiscal, em primeira instância, ou pela Assembleia Geral, em segunda instância.\r\nArt. 70 Nos casos que envolvam interesse do Elo Executivo ou da União, o Presidente deverá comunicar ao Elo Executivo para manifestação antes de deliberar.\r\nArt. 71 Este Regimento não poderá, em hipótese alguma, contrariar dispositivos do Estatuto da Administração de Compossuidores ou do Módulo 3 do Manual do SISPNR.\r\nArt. 72 Cópia deste Regimento deverá ser entregue a todos os novos compossuidores no ato de recebimento das chaves do PNR.\r\nArt. 73 O desconhecimento das normas deste Regimento não exime ninguém de seu cumprimento.\r\nArt. 74 A Diretoria poderá expedir orientações complementares para esclarecimento ou detalhamento de normas deste Regimento, desde que não contrariem seu conteúdo.\r\nArt. 75 Este Regimento Interno entra em vigor na data de sua aprovação em Assembleia Geral.\r\n\r\n_______________________________\r\nPresidente da Administração de Compossuidores\r\nCHAS - Condomínio Habitacional Augusto Severo\r\n\r\nObservação final: Este Regimento está em conformidade com o Código Civil Brasileiro (Lei 10.406/2002), Lei do Condomínio (Lei 4.591/1964), Módulo 3 do Manual do SISPNR e NSCA 12-1, respeitando as peculiaridades de uma Administração de Compossuidores de Próprios Nacionais Residenciais da Aeronáutica.', '2025-11-07', 'Assembléia Geral', 1, 1, 1, '2025-11-08 20:36:10', '2025-11-08 20:36:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `internal_regulation_history`
--

CREATE TABLE `internal_regulation_history` (
  `id` bigint UNSIGNED NOT NULL,
  `internal_regulation_id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Conteúdo anterior',
  `changes_summary` text COLLATE utf8mb4_unicode_ci COMMENT 'Resumo das alterações realizadas',
  `assembly_date` date DEFAULT NULL COMMENT 'Data da assembleia de aprovação',
  `assembly_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Detalhes da assembleia',
  `version` int NOT NULL COMMENT 'Versão do regimento',
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `changed_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"c8a14691-7cca-4029-b651-ffb04d0bca50\",\"displayName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"command\":\"O:32:\\\"App\\\\Jobs\\\\SendPackageNotification\\\":2:{s:7:\\\"package\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Package\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:7:\\\"arrived\\\";}\"},\"createdAt\":1762622725,\"delay\":null}', 0, NULL, 1762622725, 1762622725),
(2, 'default', '{\"uuid\":\"840ec622-b631-4b25-8f82-08650f48ac3d\",\"displayName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"command\":\"O:32:\\\"App\\\\Jobs\\\\SendPackageNotification\\\":2:{s:7:\\\"package\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Package\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:7:\\\"arrived\\\";}\"},\"createdAt\":1762622998,\"delay\":null}', 0, NULL, 1762622998, 1762622998),
(3, 'default', '{\"uuid\":\"3a32fa18-5194-4ec3-ab7d-09974865b08d\",\"displayName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"command\":\"O:32:\\\"App\\\\Jobs\\\\SendPackageNotification\\\":2:{s:7:\\\"package\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Package\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"collected\\\";}\"},\"createdAt\":1762623008,\"delay\":null}', 0, NULL, 1762623008, 1762623008),
(4, 'default', '{\"uuid\":\"916ff1c2-e532-4222-9771-2599e24fe788\",\"displayName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"command\":\"O:32:\\\"App\\\\Jobs\\\\SendPackageNotification\\\":2:{s:7:\\\"package\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Package\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"collected\\\";}\"},\"createdAt\":1762623012,\"delay\":null}', 0, NULL, 1762623012, 1762623012),
(5, 'default', '{\"uuid\":\"5f74c88f-3100-4918-afd5-f54cdd399e59\",\"displayName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"command\":\"O:32:\\\"App\\\\Jobs\\\\SendPackageNotification\\\":2:{s:7:\\\"package\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Package\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:7:\\\"arrived\\\";}\"},\"createdAt\":1762623905,\"delay\":null}', 0, NULL, 1762623905, 1762623905),
(6, 'default', '{\"uuid\":\"ec07ee4d-294f-4d6f-bc90-2312760be5fd\",\"displayName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendPackageNotification\",\"command\":\"O:32:\\\"App\\\\Jobs\\\\SendPackageNotification\\\":2:{s:7:\\\"package\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Package\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:9:\\\"collected\\\";}\"},\"createdAt\":1762623956,\"delay\":null}', 0, NULL, 1762623956, 1762623956);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marketplace_items`
--

CREATE TABLE `marketplace_items` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `category` enum('products','services','jobs','real_estate','vehicles','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'products',
  `condition` enum('new','used','refurbished','not_applicable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_applicable',
  `whatsapp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` json DEFAULT NULL,
  `status` enum('active','sold','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `views` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `marketplace_items`
--

INSERT INTO `marketplace_items` (`id`, `condominium_id`, `seller_id`, `unit_id`, `title`, `description`, `price`, `category`, `condition`, `whatsapp`, `images`, `status`, `views`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 219, 'Bicicleta Elétrica V8 MAX', '1000w | Pedal assistido | NFC | Alarme', 7760.00, 'vehicles', 'new', '67991224547', '[\"marketplace/1/nNJTZoKILM4h80TlxI0j9mYBnlNmsDMKvJrZKSV9.jpg\", \"marketplace/1/1V2xeWl7TBj9MPypJlg822Bp4OgimpZIqSyNnOnM.png\", \"marketplace/1/erdhngquqVJ9Id5wTYoRnZ0kIyyCsyRxBL43j31s.jpg\"]', 'active', 2, '2025-11-08 20:42:05', '2025-11-08 20:42:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `from_user_id` bigint UNSIGNED NOT NULL,
  `to_user_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('announcement','sindico_message','marketplace_inquiry','panic_alert') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'announcement',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `related_item_id` bigint UNSIGNED DEFAULT NULL,
  `related_item_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_10_07_011107_create_condominiums_table', 1),
(2, '0001_01_01_000000_create_users_table', 2),
(3, '0001_01_01_000001_create_cache_table', 2),
(4, '0001_01_01_000002_create_jobs_table', 2),
(5, '2025_10_07_011128_create_spaces_table', 3),
(6, '2024_01_15_000000_create_recurring_reservations_table', 4),
(7, '2025_10_07_011112_create_units_table', 5),
(8, '2025_10_07_011129_create_reservations_table', 6),
(9, '2024_01_15_000001_add_recurring_fields_to_reservations_table', 7),
(10, '2025_10_07_010953_create_permission_tables', 7),
(11, '2025_10_07_011003_create_audits_table', 7),
(12, '2025_10_07_011009_create_personal_access_tokens_table', 7),
(13, '2025_10_07_011118_add_condominium_fields_to_users_table', 7),
(14, '2025_10_07_011124_create_transactions_table', 7),
(15, '2025_10_07_011125_create_receipts_table', 7),
(16, '2025_10_07_011126_create_charges_table', 7),
(17, '2025_10_07_011127_create_payments_table', 7),
(18, '2025_10_07_011131_create_marketplace_items_table', 7),
(19, '2025_10_07_011131_create_pets_table', 7),
(20, '2025_10_07_011132_create_entries_table', 7),
(21, '2025_10_07_011133_create_packages_table', 7),
(22, '2025_10_07_011134_create_assemblies_table', 7),
(23, '2025_10_07_011135_create_votes_table', 7),
(24, '2025_10_07_011137_create_messages_table', 7),
(25, '2025_10_07_011138_create_notifications_table', 7),
(26, '2025_10_07_011139_create_bank_statements_table', 7),
(27, '2025_10_07_030649_add_cancellation_fields_to_reservations_table', 7),
(28, '2025_10_07_032904_create_user_credits_table', 7),
(29, '2025_10_07_035022_add_reservation_mode_to_spaces_table', 7),
(30, '2025_10_09_164927_add_prereservation_fields_to_spaces_table', 7),
(31, '2025_10_09_164944_add_prereservation_fields_to_reservations_table', 7),
(32, '2025_10_09_170802_add_photo_field_to_spaces_table', 7),
(33, '2025_10_09_200000_add_extended_fields_to_units_table', 7),
(34, '2025_10_09_200001_add_extended_fields_to_users_table', 7),
(35, '2025_10_09_200002_create_user_activity_logs_table', 7),
(36, '2025_10_09_200003_create_profile_selections_table', 7),
(37, '2025_10_09_225046_create_agregado_permissions_table', 7),
(38, '2025_10_09_230131_add_permission_level_to_agregado_permissions_table', 7),
(39, '2025_10_09_240000_rename_reservation_limit_field', 7),
(40, '2025_10_14_231400_create_panic_alerts_table', 7),
(41, '2025_10_15_010136_add_fcm_fields_to_users_table', 7),
(42, '2025_10_31_011206_add_missing_columns_to_pets_table', 7),
(43, '2025_10_31_011602_create_internal_regulations_table', 7),
(44, '2025_10_31_011607_create_internal_regulation_history_table', 7),
(45, '2025_11_07_193705_add_marketplace_allow_agregados_to_condominiums_table', 7),
(46, '2025_11_07_205928_add_whatsapp_to_marketplace_items_table', 7),
(47, '2025_11_08_000100_add_type_to_packages_table', 7),
(48, '2025_11_08_120500_update_assemblies_for_voting_workflow', 7),
(49, '2025_11_08_120600_create_assembly_related_tables', 7),
(50, '2025_11_08_160000_add_results_visibility_to_assemblies', 7),
(51, '2025_11_08_121500_add_condominium_id_to_pets_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(3, 'App\\Models\\User', 1),
(3, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(4, 'App\\Models\\User', 4),
(2, 'App\\Models\\User', 5),
(7, 'App\\Models\\User', 6);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `channel` enum('database','email','push','sms','whatsapp') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'database',
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `registered_by` bigint UNSIGNED NOT NULL,
  `type` enum('leve','pesado','caixa_grande','fragil') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'leve',
  `sender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `received_at` timestamp NOT NULL,
  `collected_at` timestamp NULL DEFAULT NULL,
  `collected_by` bigint UNSIGNED DEFAULT NULL,
  `status` enum('pending','collected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `notification_sent` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `condominium_id`, `unit_id`, `registered_by`, `type`, `sender`, `tracking_code`, `description`, `received_at`, `collected_at`, `collected_by`, `status`, `notes`, `notification_sent`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 219, 1, 'pesado', NULL, NULL, NULL, '2025-11-08 20:25:25', '2025-11-08 20:30:08', 1, 'collected', NULL, 0, '2025-11-08 20:25:25', '2025-11-08 20:30:08', NULL),
(2, 1, 219, 1, 'leve', NULL, NULL, NULL, '2025-11-08 20:29:58', '2025-11-08 20:30:12', 1, 'collected', NULL, 0, '2025-11-08 20:29:58', '2025-11-08 20:30:12', NULL),
(3, 1, 219, 1, 'pesado', NULL, NULL, NULL, '2025-11-08 20:45:05', '2025-11-08 20:45:56', 1, 'collected', NULL, 0, '2025-11-08 20:45:05', '2025-11-08 20:45:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `panic_alerts`
--

CREATE TABLE `panic_alerts` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `alert_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'panic',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `severity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'high',
  `status` enum('active','resolved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `resolved_by` bigint UNSIGNED DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `charge_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `amount_paid` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','pix','bank_transfer','credit_card','debit_card','boleto','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `asaas_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage_condominiums', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(2, 'view_condominiums', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(3, 'manage_users', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(4, 'view_users', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(5, 'manage_sindico_users', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(6, 'manage_conselho_users', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(7, 'view_user_history', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(8, 'export_user_history', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(9, 'manage_units', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(10, 'view_units', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(11, 'create_units', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(12, 'edit_units', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(13, 'delete_units', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(14, 'manage_transactions', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(15, 'view_transactions', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(16, 'create_transactions', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(17, 'edit_transactions', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(18, 'delete_transactions', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(19, 'manage_charges', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(20, 'view_charges', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(21, 'approve_expenses', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(22, 'view_financial_reports', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(23, 'export_financial_reports', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(24, 'manage_bank_statements', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(25, 'view_bank_statements', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(26, 'view_bank_reconciliation', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(27, 'view_accountability_reports', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(28, 'export_accountability_reports', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(29, 'view_revenue', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(30, 'view_expenses', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(31, 'view_balance', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(32, 'view_own_financial', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(33, 'manage_spaces', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(34, 'view_spaces', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(35, 'make_reservations', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(36, 'manage_reservations', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(37, 'approve_reservations', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(38, 'view_reservations', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(39, 'create_marketplace_items', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(40, 'manage_marketplace_items', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(41, 'view_marketplace', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(42, 'register_entries', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(43, 'register_packages', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(44, 'view_entries', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(45, 'view_packages', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(46, 'register_pets', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(47, 'view_pets', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(48, 'create_assemblies', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(49, 'manage_assemblies', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(50, 'vote_assemblies', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(51, 'view_assemblies', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(52, 'send_announcements', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(53, 'contact_sindico', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(54, 'send_panic_alert', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(55, 'manage_panic_alerts', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(56, 'view_messages', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(57, 'manage_notifications', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(58, 'view_notifications', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `owner_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('dog','cat','bird','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dog',
  `breed` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `size` enum('small','medium','large') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observations` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `condominium_id`, `unit_id`, `owner_id`, `name`, `type`, `breed`, `color`, `birth_date`, `size`, `photo`, `qr_code`, `observations`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 219, 1, 'Rambo', 'dog', 'Pitbull', 'Marrom', NULL, 'large', 'pets/fzYqDRIJmn7SE519cjqu9CIFbAelKIsPHv3BJF6y.jpg', 'PET-LFFOYGYR5U-1762623600', NULL, 1, '2025-11-08 20:40:00', '2025-11-08 20:40:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `profile_selections`
--

CREATE TABLE `profile_selections` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `role_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `selected_at` timestamp NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profile_selections`
--

INSERT INTO `profile_selections` (`id`, `user_id`, `role_name`, `selected_at`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrador', '2025-11-08 20:13:03', '192.168.0.7', '2025-11-08 20:13:03', '2025-11-08 20:13:03'),
(2, 1, 'Administrador', '2025-11-08 20:28:00', '192.168.0.7', '2025-11-08 20:28:00', '2025-11-08 20:28:00'),
(3, 1, 'Administrador', '2025-11-08 20:29:07', '192.168.0.7', '2025-11-08 20:29:07', '2025-11-08 20:29:07'),
(4, 1, 'Administrador', '2025-11-08 20:45:39', '192.168.0.7', '2025-11-08 20:45:39', '2025-11-08 20:45:39');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` bigint UNSIGNED NOT NULL,
  `transaction_id` bigint UNSIGNED NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `storage_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint UNSIGNED NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recurring_reservations`
--

CREATE TABLE `recurring_reservations` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `space_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `days_of_week` json NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recurring_reservations`
--

INSERT INTO `recurring_reservations` (`id`, `condominium_id`, `space_id`, `created_by`, `title`, `description`, `days_of_week`, `start_time`, `end_time`, `start_date`, `end_date`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 'Vôlei de areia - Aulas', 'Aulas regulares de volei INFANTIL', '[\"2\", \"4\"]', '18:00:00', '19:00:00', '2025-11-08', '2026-02-08', 'active', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20'),
(2, 1, 2, 1, 'Futvolei - Recreativo', 'Futvolei adulto recreativo', '[\"2\", \"4\"]', '19:00:00', '22:00:00', '2025-11-08', '2026-02-08', 'active', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58'),
(3, 1, 2, 1, 'Vôlei recreativo adulto', 'Vôlei recreativo adulto', '[\"1\", \"3\", \"5\"]', '19:00:00', '21:00:00', '2025-11-08', '2026-05-08', 'active', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` bigint UNSIGNED NOT NULL,
  `space_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rejected','cancelled','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `prereservation_status` enum('pending_payment','paid','expired','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Status da pré-reserva: pending_payment, paid, expired, cancelled',
  `payment_deadline` timestamp NULL DEFAULT NULL COMMENT 'Data limite para pagamento da pré-reserva',
  `payment_completed_at` timestamp NULL DEFAULT NULL COMMENT 'Data em que o pagamento foi realizado',
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Referência do pagamento (PIX, boleto, etc)',
  `prereservation_amount` decimal(10,2) DEFAULT NULL COMMENT 'Valor a ser pago para confirmar a pré-reserva',
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint UNSIGNED DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `recurring_reservation_id` bigint UNSIGNED DEFAULT NULL,
  `admin_action` enum('created','edited','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_reason` text COLLATE utf8mb4_unicode_ci,
  `admin_action_by` bigint UNSIGNED DEFAULT NULL,
  `admin_action_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `space_id`, `unit_id`, `user_id`, `reservation_date`, `start_time`, `end_time`, `status`, `prereservation_status`, `payment_deadline`, `payment_completed_at`, `payment_reference`, `prereservation_amount`, `approved_by`, `approved_at`, `cancelled_by`, `cancelled_at`, `cancellation_reason`, `notes`, `rejection_reason`, `created_at`, `updated_at`, `deleted_at`, `recurring_reservation_id`, `admin_action`, `admin_reason`, `admin_action_by`, `admin_action_at`) VALUES
(1, 2, 219, 1, '2025-11-11', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(2, 2, 219, 1, '2025-11-13', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(3, 2, 219, 1, '2025-11-18', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(4, 2, 219, 1, '2025-11-20', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(5, 2, 219, 1, '2025-11-25', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(6, 2, 219, 1, '2025-11-27', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(7, 2, 219, 1, '2025-12-02', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(8, 2, 219, 1, '2025-12-04', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(9, 2, 219, 1, '2025-12-09', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(10, 2, 219, 1, '2025-12-11', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(11, 2, 219, 1, '2025-12-16', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(12, 2, 219, 1, '2025-12-18', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(13, 2, 219, 1, '2025-12-23', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(14, 2, 219, 1, '2025-12-25', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(15, 2, 219, 1, '2025-12-30', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(16, 2, 219, 1, '2026-01-01', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(17, 2, 219, 1, '2026-01-06', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(18, 2, 219, 1, '2026-01-08', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(19, 2, 219, 1, '2026-01-13', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(20, 2, 219, 1, '2026-01-15', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(21, 2, 219, 1, '2026-01-20', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(22, 2, 219, 1, '2026-01-22', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(23, 2, 219, 1, '2026-01-27', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(24, 2, 219, 1, '2026-01-29', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(25, 2, 219, 1, '2026-02-03', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(26, 2, 219, 1, '2026-02-05', '18:00:00', '19:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei de areia - Aulas - Aulas regulares de volei INFANTIL', NULL, '2025-11-08 20:08:20', '2025-11-08 20:08:20', NULL, 1, NULL, NULL, NULL, NULL),
(27, 2, 219, 1, '2025-11-11', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(28, 2, 219, 1, '2025-11-13', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(29, 2, 219, 1, '2025-11-18', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(30, 2, 219, 1, '2025-11-20', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(31, 2, 219, 1, '2025-11-25', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(32, 2, 219, 1, '2025-11-27', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(33, 2, 219, 1, '2025-12-02', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(34, 2, 219, 1, '2025-12-04', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(35, 2, 219, 1, '2025-12-09', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(36, 2, 219, 1, '2025-12-11', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(37, 2, 219, 1, '2025-12-16', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(38, 2, 219, 1, '2025-12-18', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(39, 2, 219, 1, '2025-12-23', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(40, 2, 219, 1, '2025-12-25', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(41, 2, 219, 1, '2025-12-30', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(42, 2, 219, 1, '2026-01-01', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(43, 2, 219, 1, '2026-01-06', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(44, 2, 219, 1, '2026-01-08', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(45, 2, 219, 1, '2026-01-13', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(46, 2, 219, 1, '2026-01-15', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(47, 2, 219, 1, '2026-01-20', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(48, 2, 219, 1, '2026-01-22', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(49, 2, 219, 1, '2026-01-27', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(50, 2, 219, 1, '2026-01-29', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(51, 2, 219, 1, '2026-02-03', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(52, 2, 219, 1, '2026-02-05', '19:00:00', '22:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Futvolei - Recreativo - Futvolei adulto recreativo', NULL, '2025-11-08 20:08:58', '2025-11-08 20:08:58', NULL, 2, NULL, NULL, NULL, NULL),
(53, 2, 219, 1, '2025-11-10', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(54, 2, 219, 1, '2025-11-12', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(55, 2, 219, 1, '2025-11-14', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(56, 2, 219, 1, '2025-11-17', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(57, 2, 219, 1, '2025-11-19', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(58, 2, 219, 1, '2025-11-21', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(59, 2, 219, 1, '2025-11-24', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(60, 2, 219, 1, '2025-11-26', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(61, 2, 219, 1, '2025-11-28', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(62, 2, 219, 1, '2025-12-01', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(63, 2, 219, 1, '2025-12-03', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(64, 2, 219, 1, '2025-12-05', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(65, 2, 219, 1, '2025-12-08', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(66, 2, 219, 1, '2025-12-10', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(67, 2, 219, 1, '2025-12-12', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(68, 2, 219, 1, '2025-12-15', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(69, 2, 219, 1, '2025-12-17', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(70, 2, 219, 1, '2025-12-19', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(71, 2, 219, 1, '2025-12-22', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(72, 2, 219, 1, '2025-12-24', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(73, 2, 219, 1, '2025-12-26', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(74, 2, 219, 1, '2025-12-29', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(75, 2, 219, 1, '2025-12-31', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(76, 2, 219, 1, '2026-01-02', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(77, 2, 219, 1, '2026-01-05', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(78, 2, 219, 1, '2026-01-07', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(79, 2, 219, 1, '2026-01-09', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(80, 2, 219, 1, '2026-01-12', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(81, 2, 219, 1, '2026-01-14', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(82, 2, 219, 1, '2026-01-16', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(83, 2, 219, 1, '2026-01-19', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(84, 2, 219, 1, '2026-01-21', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(85, 2, 219, 1, '2026-01-23', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(86, 2, 219, 1, '2026-01-26', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(87, 2, 219, 1, '2026-01-28', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(88, 2, 219, 1, '2026-01-30', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(89, 2, 219, 1, '2026-02-02', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(90, 2, 219, 1, '2026-02-04', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(91, 2, 219, 1, '2026-02-06', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(92, 2, 219, 1, '2026-02-09', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(93, 2, 219, 1, '2026-02-11', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(94, 2, 219, 1, '2026-02-13', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(95, 2, 219, 1, '2026-02-16', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(96, 2, 219, 1, '2026-02-18', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(97, 2, 219, 1, '2026-02-20', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(98, 2, 219, 1, '2026-02-23', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(99, 2, 219, 1, '2026-02-25', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(100, 2, 219, 1, '2026-02-27', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(101, 2, 219, 1, '2026-03-02', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(102, 2, 219, 1, '2026-03-04', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(103, 2, 219, 1, '2026-03-06', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(104, 2, 219, 1, '2026-03-09', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(105, 2, 219, 1, '2026-03-11', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(106, 2, 219, 1, '2026-03-13', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:35', '2025-11-08 20:09:35', NULL, 3, NULL, NULL, NULL, NULL),
(107, 2, 219, 1, '2026-03-16', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(108, 2, 219, 1, '2026-03-18', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(109, 2, 219, 1, '2026-03-20', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(110, 2, 219, 1, '2026-03-23', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(111, 2, 219, 1, '2026-03-25', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(112, 2, 219, 1, '2026-03-27', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(113, 2, 219, 1, '2026-03-30', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(114, 2, 219, 1, '2026-04-01', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(115, 2, 219, 1, '2026-04-03', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(116, 2, 219, 1, '2026-04-06', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(117, 2, 219, 1, '2026-04-08', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(118, 2, 219, 1, '2026-04-10', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(119, 2, 219, 1, '2026-04-13', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(120, 2, 219, 1, '2026-04-15', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(121, 2, 219, 1, '2026-04-17', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(122, 2, 219, 1, '2026-04-20', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(123, 2, 219, 1, '2026-04-22', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(124, 2, 219, 1, '2026-04-24', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(125, 2, 219, 1, '2026-04-27', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(126, 2, 219, 1, '2026-04-29', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(127, 2, 219, 1, '2026-05-01', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(128, 2, 219, 1, '2026-05-04', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(129, 2, 219, 1, '2026-05-06', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL),
(130, 2, 219, 1, '2026-05-08', '19:00:00', '21:00:00', 'approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Vôlei recreativo adulto - Vôlei recreativo adulto', NULL, '2025-11-08 20:09:36', '2025-11-08 20:09:36', NULL, 3, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(2, 'Síndico', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(3, 'Morador', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(4, 'Porteiro', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(5, 'Conselho Fiscal', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(6, 'Secretaria', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57'),
(7, 'Agregado', 'web', '2025-11-08 19:29:57', '2025-11-08 19:29:57');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(2, 2),
(3, 2),
(4, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2),
(15, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(24, 2),
(33, 2),
(34, 2),
(36, 2),
(37, 2),
(38, 2),
(40, 2),
(41, 2),
(44, 2),
(45, 2),
(47, 2),
(48, 2),
(49, 2),
(51, 2),
(52, 2),
(55, 2),
(56, 2),
(57, 2),
(58, 2),
(15, 3),
(20, 3),
(22, 3),
(23, 3),
(25, 3),
(26, 3),
(27, 3),
(28, 3),
(29, 3),
(30, 3),
(31, 3),
(32, 3),
(34, 3),
(35, 3),
(38, 3),
(39, 3),
(41, 3),
(46, 3),
(47, 3),
(50, 3),
(51, 3),
(53, 3),
(54, 3),
(56, 3),
(58, 3),
(42, 4),
(43, 4),
(44, 4),
(45, 4),
(47, 4),
(58, 4),
(4, 5),
(10, 5),
(15, 5),
(20, 5),
(21, 5),
(22, 5),
(23, 5),
(24, 5),
(25, 5),
(26, 5),
(27, 5),
(28, 5),
(29, 5),
(30, 5),
(31, 5),
(51, 5),
(56, 5),
(4, 6),
(10, 6),
(15, 6),
(20, 6),
(38, 6),
(44, 6),
(45, 6),
(47, 6),
(51, 6),
(52, 6),
(56, 6),
(58, 6),
(34, 7),
(41, 7),
(47, 7),
(58, 7);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('UqSmbX3FwnSvtC5aCNgjY4qQo75bPjvK6RF9682z', 1, '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiT05ERm5GcmNQOFA4VVAwQzZvN0J3OUFHYUVoa1NWWHo5MnREQ3NzViI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xOTIuMTY4LjAuNzo4MDAwL3BhbmljL2NoZWNrIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjExOiJhY3RpdmVfcm9sZSI7czoxMzoiQWRtaW5pc3RyYWRvciI7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjA6IiQyeSQxMiRKdFRKZ1ZRdXV0RGt3QWxMNGZELnUuTDRqQ01HdkFLVUc5NHVmeTIwbzMyc2haLm9qWFlSMiI7fQ==', 1762624908);

-- --------------------------------------------------------

--
-- Table structure for table `spaces`
--

CREATE TABLE `spaces` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Caminho para a foto do espaço',
  `type` enum('party_hall','bbq','pool','sports_court','gym','meeting_room','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `reservation_mode` enum('full_day','hourly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'full_day',
  `capacity` int DEFAULT NULL,
  `price_per_hour` decimal(10,2) NOT NULL DEFAULT '0.00',
  `requires_approval` tinyint(1) NOT NULL DEFAULT '0',
  `approval_type` enum('automatic','manual','prereservation') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'automatic',
  `prereservation_payment_hours` int DEFAULT NULL COMMENT 'Horas para pagamento da pré-reserva (24, 48, 72)',
  `prereservation_auto_cancel` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Se deve cancelar automaticamente se não pagar',
  `prereservation_instructions` text COLLATE utf8mb4_unicode_ci COMMENT 'Instruções para pagamento da pré-reserva',
  `max_hours_per_reservation` int NOT NULL DEFAULT '4',
  `min_hours_per_reservation` int NOT NULL DEFAULT '1',
  `interval_between_reservations` int NOT NULL DEFAULT '0',
  `max_reservations_per_month_per_user` int NOT NULL DEFAULT '1',
  `available_from` time NOT NULL DEFAULT '08:00:00',
  `available_until` time NOT NULL DEFAULT '22:00:00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `rules` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `spaces`
--

INSERT INTO `spaces` (`id`, `condominium_id`, `name`, `description`, `photo_path`, `type`, `reservation_mode`, `capacity`, `price_per_hour`, `requires_approval`, `approval_type`, `prereservation_payment_hours`, `prereservation_auto_cancel`, `prereservation_instructions`, `max_hours_per_reservation`, `min_hours_per_reservation`, `interval_between_reservations`, `max_reservations_per_month_per_user`, `available_from`, `available_until`, `is_active`, `rules`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Churrasqueira 1', 'Churrasqueira com 10 jogos de mesa e cadeiras', 'spaces/photos/TjVrCli5vYD7M4mHg5dKo46eyHMHlnmbnNUhtnjr.jpg', 'bbq', 'full_day', 40, 0.00, 0, 'automatic', 24, 1, NULL, 4, 1, 0, 31, '08:00:00', '22:00:00', 1, 'Proibido som alto a partir das 22h;\r\nNão retirar mesas e cadeiras do local;\r\nO lixo deve ser colocado na lixeira dentro de sacos.', '2025-11-08 20:06:03', '2025-11-08 20:06:03', NULL),
(2, 1, 'Quadra de Areia', 'Quadra de areia', 'spaces/photos/zRml0Xqvny1yP3OCgZlRYHdZypj0OxJd5MHUwS4J.jpg', 'sports_court', 'hourly', 30, 0.00, 0, 'automatic', 24, 1, NULL, 2, 1, 0, 31, '08:00:00', '22:00:00', 1, 'Proibida a retirada de areia;\r\nNão colocar animais na areia.', '2025-11-08 20:07:17', '2025-11-08 20:07:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subcategory` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` enum('cash','pix','bank_transfer','credit_card','debit_card','check','boleto','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `recurrence_period` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_transaction_id` bigint UNSIGNED DEFAULT NULL,
  `tags` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `block` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('residential','commercial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'residential',
  `situacao` enum('habitado','fechado','indisponivel','em_obra') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'habitado',
  `cep` varchar(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logradouro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ideal_fraction` decimal(8,4) NOT NULL DEFAULT '1.0000',
  `area` decimal(10,2) DEFAULT NULL,
  `num_quartos` int DEFAULT NULL,
  `num_banheiros` int DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `possui_dividas` tinyint(1) NOT NULL DEFAULT '0',
  `floor` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `condominium_id`, `number`, `block`, `type`, `situacao`, `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `ideal_fraction`, `area`, `num_quartos`, `num_banheiros`, `foto`, `possui_dividas`, `floor`, `notes`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '102', 'Bloco 3', 'residential', 'habitado', '59140-840', 'Avenida Professor Clementino Câmara', '186', NULL, 'Cohabinal', 'Parnamirim', 'RN', 1.0000, 100.00, 3, 2, NULL, 0, 1, NULL, 1, '2025-11-08 19:47:39', '2025-11-08 19:59:43', '2025-11-08 19:59:43'),
(2, 1, 'Casa 01', 'Bloco A', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(3, 1, 'Casa 02', 'Bloco A', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(4, 1, 'Casa 03', 'Bloco A', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(5, 1, 'Casa 04', 'Bloco A', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(6, 1, 'Casa 05', 'Bloco A', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(7, 1, 'Casa 06', 'Bloco A', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(8, 1, 'Casa 07', 'Bloco A', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(9, 1, 'Casa 08', 'Bloco A', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(10, 1, 'Casa 09', 'Bloco A', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(11, 1, 'Casa 10', 'Bloco A', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(12, 1, 'Casa 01', 'Bloco B', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(13, 1, 'Casa 02', 'Bloco B', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(14, 1, 'Casa 03', 'Bloco B', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(15, 1, 'Casa 04', 'Bloco B', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(16, 1, 'Casa 05', 'Bloco B', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(17, 1, 'Casa 06', 'Bloco B', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(18, 1, 'Casa 07', 'Bloco B', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(19, 1, 'Casa 08', 'Bloco B', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(20, 1, 'Casa 09', 'Bloco B', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(21, 1, 'Casa 10', 'Bloco B', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(22, 1, 'Casa 01', 'Bloco C', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:39', '2025-11-08 19:54:39', NULL),
(23, 1, 'Casa 02', 'Bloco C', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(24, 1, 'Casa 03', 'Bloco C', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(25, 1, 'Casa 04', 'Bloco C', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(26, 1, 'Casa 05', 'Bloco C', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(27, 1, 'Casa 06', 'Bloco C', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(28, 1, 'Casa 07', 'Bloco C', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(29, 1, 'Casa 08', 'Bloco C', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(30, 1, 'Casa 09', 'Bloco C', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(31, 1, 'Casa 10', 'Bloco C', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(32, 1, 'Casa 01', 'Bloco D', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(33, 1, 'Casa 02', 'Bloco D', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(34, 1, 'Casa 03', 'Bloco D', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(35, 1, 'Casa 04', 'Bloco D', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(36, 1, 'Casa 05', 'Bloco D', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(37, 1, 'Casa 06', 'Bloco D', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(38, 1, 'Casa 07', 'Bloco D', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(39, 1, 'Casa 08', 'Bloco D', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(40, 1, 'Casa 09', 'Bloco D', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(41, 1, 'Casa 10', 'Bloco D', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(42, 1, 'Casa 01', 'Bloco E', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(43, 1, 'Casa 02', 'Bloco E', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(44, 1, 'Casa 03', 'Bloco E', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(45, 1, 'Casa 04', 'Bloco E', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(46, 1, 'Casa 05', 'Bloco E', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(47, 1, 'Casa 06', 'Bloco E', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(48, 1, 'Casa 07', 'Bloco E', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(49, 1, 'Casa 08', 'Bloco E', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(50, 1, 'Casa 09', 'Bloco E', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(51, 1, 'Casa 10', 'Bloco E', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(52, 1, 'Casa 01', 'Bloco F', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(53, 1, 'Casa 02', 'Bloco F', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(54, 1, 'Casa 03', 'Bloco F', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(55, 1, 'Casa 04', 'Bloco F', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(56, 1, 'Casa 05', 'Bloco F', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(57, 1, 'Casa 06', 'Bloco F', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(58, 1, 'Casa 07', 'Bloco F', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(59, 1, 'Casa 08', 'Bloco F', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(60, 1, 'Casa 09', 'Bloco F', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(61, 1, 'Casa 10', 'Bloco F', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(62, 1, 'Casa 01', 'Bloco G', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(63, 1, 'Casa 02', 'Bloco G', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(64, 1, 'Casa 03', 'Bloco G', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(65, 1, 'Casa 04', 'Bloco G', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(66, 1, 'Casa 05', 'Bloco G', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(67, 1, 'Casa 06', 'Bloco G', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(68, 1, 'Casa 07', 'Bloco G', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(69, 1, 'Casa 08', 'Bloco G', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(70, 1, 'Casa 09', 'Bloco G', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(71, 1, 'Casa 10', 'Bloco G', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(72, 1, 'Casa 01', 'Bloco H', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(73, 1, 'Casa 02', 'Bloco H', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(74, 1, 'Casa 03', 'Bloco H', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(75, 1, 'Casa 04', 'Bloco H', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(76, 1, 'Casa 05', 'Bloco H', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(77, 1, 'Casa 06', 'Bloco H', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(78, 1, 'Casa 07', 'Bloco H', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(79, 1, 'Casa 08', 'Bloco H', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(80, 1, 'Casa 09', 'Bloco H', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(81, 1, 'Casa 10', 'Bloco H', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(82, 1, 'Casa 01', 'Bloco I', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(83, 1, 'Casa 02', 'Bloco I', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(84, 1, 'Casa 03', 'Bloco I', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(85, 1, 'Casa 04', 'Bloco I', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(86, 1, 'Casa 05', 'Bloco I', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(87, 1, 'Casa 06', 'Bloco I', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(88, 1, 'Casa 07', 'Bloco I', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(89, 1, 'Casa 08', 'Bloco I', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(90, 1, 'Casa 09', 'Bloco I', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(91, 1, 'Casa 10', 'Bloco I', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(92, 1, 'Casa 01', 'Bloco J', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(93, 1, 'Casa 02', 'Bloco J', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(94, 1, 'Casa 03', 'Bloco J', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(95, 1, 'Casa 04', 'Bloco J', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(96, 1, 'Casa 05', 'Bloco J', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(97, 1, 'Casa 06', 'Bloco J', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(98, 1, 'Casa 07', 'Bloco J', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(99, 1, 'Casa 08', 'Bloco J', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(100, 1, 'Casa 09', 'Bloco J', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(101, 1, 'Casa 10', 'Bloco J', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(102, 1, 'Casa 01', 'Bloco K', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(103, 1, 'Casa 02', 'Bloco K', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(104, 1, 'Casa 03', 'Bloco K', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(105, 1, 'Casa 04', 'Bloco K', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(106, 1, 'Casa 05', 'Bloco K', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(107, 1, 'Casa 06', 'Bloco K', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(108, 1, 'Casa 07', 'Bloco K', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(109, 1, 'Casa 08', 'Bloco K', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(110, 1, 'Casa 09', 'Bloco K', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(111, 1, 'Casa 10', 'Bloco K', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(112, 1, 'Casa 01', 'Bloco L', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(113, 1, 'Casa 02', 'Bloco L', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(114, 1, 'Casa 03', 'Bloco L', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(115, 1, 'Casa 04', 'Bloco L', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(116, 1, 'Casa 05', 'Bloco L', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(117, 1, 'Casa 06', 'Bloco L', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(118, 1, 'Casa 07', 'Bloco L', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(119, 1, 'Casa 08', 'Bloco L', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(120, 1, 'Casa 09', 'Bloco L', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(121, 1, 'Casa 10', 'Bloco L', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, '2025-11-08 19:54:40', '2025-11-08 19:54:40', NULL),
(122, 1, 'Ap 101', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(123, 1, 'Ap 102', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(124, 1, 'Ap 103', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(125, 1, 'Ap 104', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(126, 1, 'Ap 105', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(127, 1, 'Ap 106', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(128, 1, 'Ap 107', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(129, 1, 'Ap 108', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(130, 1, 'Ap 201', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(131, 1, 'Ap 202', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(132, 1, 'Ap 203', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(133, 1, 'Ap 204', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(134, 1, 'Ap 205', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(135, 1, 'Ap 206', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(136, 1, 'Ap 207', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(137, 1, 'Ap 208', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(138, 1, 'Ap 301', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(139, 1, 'Ap 302', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(140, 1, 'Ap 303', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(141, 1, 'Ap 304', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(142, 1, 'Ap 305', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(143, 1, 'Ap 306', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(144, 1, 'Ap 307', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(145, 1, 'Ap 308', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(146, 1, 'Ap 401', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(147, 1, 'Ap 402', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(148, 1, 'Ap 403', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(149, 1, 'Ap 404', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(150, 1, 'Ap 405', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(151, 1, 'Ap 406', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(152, 1, 'Ap 407', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(153, 1, 'Ap 408', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(154, 1, 'Ap 501', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(155, 1, 'Ap 502', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(156, 1, 'Ap 503', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(157, 1, 'Ap 504', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(158, 1, 'Ap 505', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(159, 1, 'Ap 506', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(160, 1, 'Ap 507', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(161, 1, 'Ap 508', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(162, 1, 'Ap 601', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(163, 1, 'Ap 602', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(164, 1, 'Ap 603', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(165, 1, 'Ap 604', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(166, 1, 'Ap 605', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(167, 1, 'Ap 606', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(168, 1, 'Ap 607', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(169, 1, 'Ap 608', 'Bloco 1', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(170, 1, 'Ap 101', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(171, 1, 'Ap 102', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(172, 1, 'Ap 103', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(173, 1, 'Ap 104', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(174, 1, 'Ap 105', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(175, 1, 'Ap 106', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(176, 1, 'Ap 107', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(177, 1, 'Ap 108', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(178, 1, 'Ap 201', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(179, 1, 'Ap 202', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(180, 1, 'Ap 203', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(181, 1, 'Ap 204', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(182, 1, 'Ap 205', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(183, 1, 'Ap 206', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(184, 1, 'Ap 207', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(185, 1, 'Ap 208', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(186, 1, 'Ap 301', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(187, 1, 'Ap 302', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(188, 1, 'Ap 303', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(189, 1, 'Ap 304', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(190, 1, 'Ap 305', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(191, 1, 'Ap 306', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(192, 1, 'Ap 307', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(193, 1, 'Ap 308', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(194, 1, 'Ap 401', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(195, 1, 'Ap 402', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(196, 1, 'Ap 403', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(197, 1, 'Ap 404', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(198, 1, 'Ap 405', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(199, 1, 'Ap 406', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(200, 1, 'Ap 407', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(201, 1, 'Ap 408', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(202, 1, 'Ap 501', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(203, 1, 'Ap 502', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(204, 1, 'Ap 503', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(205, 1, 'Ap 504', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(206, 1, 'Ap 505', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(207, 1, 'Ap 506', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(208, 1, 'Ap 507', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(209, 1, 'Ap 508', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(210, 1, 'Ap 601', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(211, 1, 'Ap 602', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(212, 1, 'Ap 603', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(213, 1, 'Ap 604', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(214, 1, 'Ap 605', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(215, 1, 'Ap 606', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(216, 1, 'Ap 607', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(217, 1, 'Ap 608', 'Bloco 2', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(218, 1, 'Ap 101', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(219, 1, 'Ap 102', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(220, 1, 'Ap 103', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(221, 1, 'Ap 104', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(222, 1, 'Ap 105', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:16', '2025-11-08 19:55:16', NULL),
(223, 1, 'Ap 106', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(224, 1, 'Ap 107', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(225, 1, 'Ap 108', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 1, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(226, 1, 'Ap 201', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(227, 1, 'Ap 202', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(228, 1, 'Ap 203', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(229, 1, 'Ap 204', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(230, 1, 'Ap 205', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(231, 1, 'Ap 206', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(232, 1, 'Ap 207', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(233, 1, 'Ap 208', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 2, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(234, 1, 'Ap 301', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(235, 1, 'Ap 302', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(236, 1, 'Ap 303', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(237, 1, 'Ap 304', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(238, 1, 'Ap 305', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(239, 1, 'Ap 306', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(240, 1, 'Ap 307', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(241, 1, 'Ap 308', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 3, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(242, 1, 'Ap 401', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(243, 1, 'Ap 402', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(244, 1, 'Ap 403', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(245, 1, 'Ap 404', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(246, 1, 'Ap 405', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(247, 1, 'Ap 406', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(248, 1, 'Ap 407', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(249, 1, 'Ap 408', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 4, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL);
INSERT INTO `units` (`id`, `condominium_id`, `number`, `block`, `type`, `situacao`, `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `ideal_fraction`, `area`, `num_quartos`, `num_banheiros`, `foto`, `possui_dividas`, `floor`, `notes`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(250, 1, 'Ap 501', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(251, 1, 'Ap 502', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(252, 1, 'Ap 503', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(253, 1, 'Ap 504', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(254, 1, 'Ap 505', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(255, 1, 'Ap 506', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(256, 1, 'Ap 507', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(257, 1, 'Ap 508', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 5, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(258, 1, 'Ap 601', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(259, 1, 'Ap 602', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(260, 1, 'Ap 603', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(261, 1, 'Ap 604', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(262, 1, 'Ap 605', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(263, 1, 'Ap 606', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(264, 1, 'Ap 607', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL),
(265, 1, 'Ap 608', 'Bloco 3', 'residential', 'habitado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.0000, NULL, NULL, NULL, NULL, 0, 6, NULL, 1, '2025-11-08 19:55:17', '2025-11-08 19:55:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED DEFAULT NULL,
  `unit_id` bigint UNSIGNED DEFAULT NULL,
  `morador_vinculado_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone_residencial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone_celular` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone_comercial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `data_entrada` date DEFAULT NULL,
  `data_saida` date DEFAULT NULL,
  `necessita_cuidados_especiais` tinyint(1) NOT NULL DEFAULT '0',
  `descricao_cuidados_especiais` text COLLATE utf8mb4_unicode_ci,
  `local_trabalho` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contato_comercial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `possui_dividas` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha_temporaria` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_token` text COLLATE utf8mb4_unicode_ci,
  `fcm_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `fcm_topics` json DEFAULT NULL,
  `fcm_token_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `condominium_id`, `unit_id`, `morador_vinculado_id`, `name`, `email`, `phone`, `telefone_residencial`, `telefone_celular`, `telefone_comercial`, `cpf`, `cnh`, `data_nascimento`, `data_entrada`, `data_saida`, `necessita_cuidados_especiais`, `descricao_cuidados_especiais`, `local_trabalho`, `contato_comercial`, `photo`, `qr_code`, `is_active`, `possui_dividas`, `email_verified_at`, `password`, `senha_temporaria`, `remember_token`, `fcm_token`, `fcm_enabled`, `fcm_topics`, `fcm_token_updated_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 219, NULL, 'Denis Vieira Vanoni', 'dex.vanoni@gmail.com', NULL, NULL, '67991224547', NULL, '004.701.621-39', NULL, '1985-10-15', NULL, NULL, 0, NULL, 'BANT', NULL, 'photos/users/user_1_1762622323_VtxcddulwA.jpg', NULL, 1, 0, NULL, '$2y$12$JtTJgVQuutDkwAlL4fD.u.L4jCMGvAKUG94ufy20o32shZ.ojXYR2', 0, NULL, NULL, 1, NULL, NULL, '2025-11-08 19:30:49', '2025-11-08 20:18:44', NULL),
(2, 1, 218, NULL, 'Carlos Henrique', 'carlos.henrique@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '$2y$12$HKlE/O13N6bGIFkqT75Ja.RpJ17qzXmT0kbk8W3KbbxIQFyEX/bhu', 0, NULL, NULL, 1, NULL, NULL, '2025-11-08 20:06:12', '2025-11-08 20:06:12', NULL),
(3, 1, 170, NULL, 'Juliana Souza', 'juliana.souza@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '$2y$12$p36lqTd6SZlaYWtxo0ivSe1Y0HvtL6E12uEc3LPgz3qqaWT4pLi/G', 0, NULL, NULL, 1, NULL, NULL, '2025-11-08 20:06:12', '2025-11-08 20:06:12', NULL),
(4, 1, NULL, NULL, 'Marcos Silva', 'marcos.silva@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '$2y$12$y9Rh6KPfTA1xU7Tt7Og.3OW/HAbUi7BC7KUuJcEg74HjiAsIdyto.', 0, NULL, NULL, 1, NULL, NULL, '2025-11-08 20:06:13', '2025-11-08 20:06:13', NULL),
(5, 1, NULL, NULL, 'Fernanda Ribeiro', 'fernanda.ribeiro@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, '$2y$12$hU9uKTwNnGtWt2BzOuZ1TOrPD9mqfO5bWjsN51x6eP7DtiDXQvVX6', 0, NULL, NULL, 1, NULL, NULL, '2025-11-08 20:06:13', '2025-11-08 20:06:13', NULL),
(6, 1, 219, 1, 'Fabiana Bezerra de Souza Vanoni', 'fabianartv@gmail.com', '67993100550', NULL, '67993100550', NULL, '004.690.481-66', NULL, '1985-10-15', '2025-11-08', NULL, 0, NULL, NULL, NULL, 'photos/users/user_6_1762622304_ZnMBgRNchI.jpg', 'QR-690f7a8a3d92d3.13830873', 1, 0, NULL, '$2y$12$i8qKU/Krx3fuAIJWNbygNeBx3lvIdOrWfiLwJno.hnmaJ.AORu9p6', 0, NULL, NULL, 1, NULL, NULL, '2025-11-08 20:14:50', '2025-11-08 20:26:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_activity_logs`
--

INSERT INTO `user_activity_logs` (`id`, `user_id`, `condominium_id`, `action`, `module`, `description`, `metadata`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'update', 'users', 'Atualizou o usuário Denis Vieira Vanoni', '{\"user_id\": 1}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 19:46:57', '2025-11-08 19:46:57'),
(2, 1, 1, 'create', 'units', 'Criou a unidade 3 - 102', '{\"unit_id\": 1}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 19:47:39', '2025-11-08 19:47:39'),
(3, 1, 1, 'update', 'users', 'Atualizou o usuário Denis Vieira Vanoni', '{\"user_id\": 1}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 19:47:56', '2025-11-08 19:47:56'),
(4, 1, 1, 'update', 'units', 'Atualizou a unidade Bloco 3 - 102', '{\"unit_id\": 1}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 19:55:34', '2025-11-08 19:55:34'),
(5, 1, 1, 'delete', 'units', 'Excluiu a unidade Bloco 3 - 102', '{\"unit_number\": \"102\"}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 19:59:43', '2025-11-08 19:59:43'),
(6, 1, 1, 'update', 'users', 'Atualizou o usuário Denis Vieira Vanoni', '{\"user_id\": 1}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:00:25', '2025-11-08 20:00:25'),
(7, 1, 1, 'update', 'users', 'Atualizou o usuário Denis Vieira Vanoni', '{\"user_id\": 1}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:13:00', '2025-11-08 20:13:00'),
(8, 1, 1, 'select_profile', 'authentication', 'Selecionou o perfil: Administrador', '{\"role\": \"Administrador\"}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:13:03', '2025-11-08 20:13:03'),
(9, 1, 1, 'create', 'users', 'Criou o usuário Fabiana Bezerra de Souza Vanoni', '{\"roles\": [\"Agregado\"], \"user_id\": 6}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:14:50', '2025-11-08 20:14:50'),
(10, 1, 1, 'update', 'users', 'Atualizou o usuário Fabiana Bezerra de Souza Vanoni', '{\"user_id\": 6}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:18:25', '2025-11-08 20:18:25'),
(11, 1, 1, 'update', 'users', 'Atualizou o usuário Denis Vieira Vanoni', '{\"user_id\": 1}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:18:44', '2025-11-08 20:18:44'),
(12, 6, 1, 'change_password', 'authentication', 'Alterou sua senha', '[]', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:26:14', '2025-11-08 20:26:14'),
(13, 1, 1, 'select_profile', 'authentication', 'Selecionou o perfil: Administrador', '{\"role\": \"Administrador\"}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:28:00', '2025-11-08 20:28:00'),
(14, 1, 1, 'update', 'users', 'Atualizou o usuário Fabiana Bezerra de Souza Vanoni', '{\"user_id\": 6}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:28:36', '2025-11-08 20:28:36'),
(15, 1, 1, 'select_profile', 'authentication', 'Selecionou o perfil: Administrador', '{\"role\": \"Administrador\"}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:29:07', '2025-11-08 20:29:07'),
(16, 1, 1, 'create', 'internal_regulations', 'Criou o regimento interno', '{\"regulation_id\": 1}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:36:10', '2025-11-08 20:36:10'),
(17, 1, 1, 'select_profile', 'authentication', 'Selecionou o perfil: Administrador', '{\"role\": \"Administrador\"}', '192.168.0.7', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 20:45:39', '2025-11-08 20:45:39');

-- --------------------------------------------------------

--
-- Table structure for table `user_credits`
--

CREATE TABLE `user_credits` (
  `id` bigint UNSIGNED NOT NULL,
  `condominium_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('refund','bonus','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'refund',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reservation_id` bigint UNSIGNED DEFAULT NULL,
  `charge_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('available','used','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `used_in_reservation_id` bigint UNSIGNED DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` bigint UNSIGNED NOT NULL,
  `assembly_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `agenda_item` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vote` enum('yes','no','abstain') COLLATE utf8mb4_unicode_ci NOT NULL,
  `encrypted_vote` text COLLATE utf8mb4_unicode_ci,
  `delegated_from` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agregado_permissions`
--
ALTER TABLE `agregado_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `agregado_permissions_user_id_permission_key_unique` (`user_id`,`permission_key`),
  ADD KEY `agregado_permissions_granted_by_foreign` (`granted_by`),
  ADD KEY `agregado_permissions_user_id_is_granted_index` (`user_id`,`is_granted`);

--
-- Indexes for table `assemblies`
--
ALTER TABLE `assemblies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assemblies_created_by_foreign` (`created_by`),
  ADD KEY `assemblies_condominium_id_index` (`condominium_id`),
  ADD KEY `assemblies_scheduled_at_index` (`scheduled_at`),
  ADD KEY `assemblies_status_index` (`status`);

--
-- Indexes for table `assembly_allowed_roles`
--
ALTER TABLE `assembly_allowed_roles`
  ADD PRIMARY KEY (`assembly_id`,`role_id`),
  ADD KEY `assembly_allowed_roles_role_id_foreign` (`role_id`);

--
-- Indexes for table `assembly_attachments`
--
ALTER TABLE `assembly_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assembly_attachments_uploaded_by_foreign` (`uploaded_by`),
  ADD KEY `assembly_attachments_assembly_id_collection_index` (`assembly_id`,`collection`);

--
-- Indexes for table `assembly_items`
--
ALTER TABLE `assembly_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assembly_items_assembly_id_status_index` (`assembly_id`,`status`),
  ADD KEY `assembly_items_position_index` (`position`);

--
-- Indexes for table `assembly_status_logs`
--
ALTER TABLE `assembly_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assembly_status_logs_changed_by_foreign` (`changed_by`),
  ADD KEY `assembly_status_logs_assembly_id_created_at_index` (`assembly_id`,`created_at`);

--
-- Indexes for table `assembly_votes`
--
ALTER TABLE `assembly_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assembly_item_voter_unique` (`assembly_item_id`,`voter_id`),
  ADD KEY `assembly_votes_voter_id_foreign` (`voter_id`),
  ADD KEY `assembly_votes_unit_id_foreign` (`unit_id`),
  ADD KEY `assembly_votes_assembly_id_assembly_item_id_index` (`assembly_id`,`assembly_item_id`);

--
-- Indexes for table `audits`
--
ALTER TABLE `audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audits_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  ADD KEY `audits_user_id_user_type_index` (`user_id`,`user_type`);

--
-- Indexes for table `bank_statements`
--
ALTER TABLE `bank_statements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bank_statements_uploaded_by_foreign` (`uploaded_by`),
  ADD KEY `bank_statements_condominium_id_index` (`condominium_id`),
  ADD KEY `bank_statements_statement_date_index` (`statement_date`),
  ADD KEY `bank_statements_status_index` (`status`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `charges`
--
ALTER TABLE `charges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `charges_asaas_payment_id_unique` (`asaas_payment_id`),
  ADD KEY `charges_condominium_id_index` (`condominium_id`),
  ADD KEY `charges_unit_id_index` (`unit_id`),
  ADD KEY `charges_due_date_index` (`due_date`),
  ADD KEY `charges_status_index` (`status`),
  ADD KEY `charges_condominium_id_status_index` (`condominium_id`,`status`);

--
-- Indexes for table `condominiums`
--
ALTER TABLE `condominiums`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `condominiums_cnpj_unique` (`cnpj`),
  ADD KEY `condominiums_city_state_index` (`city`,`state`);

--
-- Indexes for table `entries`
--
ALTER TABLE `entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entries_registered_by_foreign` (`registered_by`),
  ADD KEY `entries_authorized_by_foreign` (`authorized_by`),
  ADD KEY `entries_condominium_id_index` (`condominium_id`),
  ADD KEY `entries_unit_id_index` (`unit_id`),
  ADD KEY `entries_entry_time_index` (`entry_time`),
  ADD KEY `entries_condominium_id_entry_type_entry_time_index` (`condominium_id`,`entry_type`,`entry_time`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `internal_regulations`
--
ALTER TABLE `internal_regulations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `internal_regulations_updated_by_foreign` (`updated_by`),
  ADD KEY `internal_regulations_condominium_id_index` (`condominium_id`),
  ADD KEY `internal_regulations_is_active_index` (`is_active`);

--
-- Indexes for table `internal_regulation_history`
--
ALTER TABLE `internal_regulation_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `internal_regulation_history_updated_by_foreign` (`updated_by`),
  ADD KEY `internal_regulation_history_internal_regulation_id_index` (`internal_regulation_id`),
  ADD KEY `internal_regulation_history_condominium_id_index` (`condominium_id`),
  ADD KEY `internal_regulation_history_version_index` (`version`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketplace_items`
--
ALTER TABLE `marketplace_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marketplace_items_unit_id_foreign` (`unit_id`),
  ADD KEY `marketplace_items_condominium_id_index` (`condominium_id`),
  ADD KEY `marketplace_items_seller_id_index` (`seller_id`),
  ADD KEY `marketplace_items_category_index` (`category`),
  ADD KEY `marketplace_items_status_index` (`status`),
  ADD KEY `marketplace_items_condominium_id_status_category_index` (`condominium_id`,`status`,`category`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_condominium_id_index` (`condominium_id`),
  ADD KEY `messages_from_user_id_index` (`from_user_id`),
  ADD KEY `messages_to_user_id_index` (`to_user_id`),
  ADD KEY `messages_type_index` (`type`),
  ADD KEY `messages_condominium_id_type_created_at_index` (`condominium_id`,`type`,`created_at`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_condominium_id_index` (`condominium_id`),
  ADD KEY `notifications_user_id_index` (`user_id`),
  ADD KEY `notifications_type_index` (`type`),
  ADD KEY `notifications_is_read_index` (`is_read`),
  ADD KEY `notifications_user_id_is_read_created_at_index` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `packages_registered_by_foreign` (`registered_by`),
  ADD KEY `packages_collected_by_foreign` (`collected_by`),
  ADD KEY `packages_condominium_id_index` (`condominium_id`),
  ADD KEY `packages_unit_id_index` (`unit_id`),
  ADD KEY `packages_status_index` (`status`),
  ADD KEY `packages_received_at_index` (`received_at`);

--
-- Indexes for table `panic_alerts`
--
ALTER TABLE `panic_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `panic_alerts_user_id_foreign` (`user_id`),
  ADD KEY `panic_alerts_resolved_by_foreign` (`resolved_by`),
  ADD KEY `panic_alerts_condominium_id_status_index` (`condominium_id`,`status`),
  ADD KEY `panic_alerts_status_created_at_index` (`status`,`created_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_user_id_foreign` (`user_id`),
  ADD KEY `payments_charge_id_index` (`charge_id`),
  ADD KEY `payments_payment_date_index` (`payment_date`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pets_qr_code_unique` (`qr_code`),
  ADD KEY `pets_unit_id_index` (`unit_id`),
  ADD KEY `pets_owner_id_index` (`owner_id`),
  ADD KEY `pets_qr_code_index` (`qr_code`);

--
-- Indexes for table `profile_selections`
--
ALTER TABLE `profile_selections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_selections_user_id_selected_at_index` (`user_id`,`selected_at`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipts_transaction_id_index` (`transaction_id`);

--
-- Indexes for table `recurring_reservations`
--
ALTER TABLE `recurring_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recurring_reservations_condominium_id_foreign` (`condominium_id`),
  ADD KEY `recurring_reservations_created_by_foreign` (`created_by`),
  ADD KEY `recurring_reservations_space_id_start_date_end_date_index` (`space_id`,`start_date`,`end_date`),
  ADD KEY `recurring_reservations_status_start_date_end_date_index` (`status`,`start_date`,`end_date`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservations_unit_id_foreign` (`unit_id`),
  ADD KEY `reservations_user_id_foreign` (`user_id`),
  ADD KEY `reservations_approved_by_foreign` (`approved_by`),
  ADD KEY `reservations_space_id_index` (`space_id`),
  ADD KEY `reservations_reservation_date_index` (`reservation_date`),
  ADD KEY `reservations_status_index` (`status`),
  ADD KEY `reservations_space_id_reservation_date_status_index` (`space_id`,`reservation_date`,`status`),
  ADD KEY `reservations_recurring_reservation_id_foreign` (`recurring_reservation_id`),
  ADD KEY `reservations_admin_action_by_foreign` (`admin_action_by`),
  ADD KEY `reservations_cancelled_by_foreign` (`cancelled_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `spaces`
--
ALTER TABLE `spaces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spaces_condominium_id_index` (`condominium_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_unit_id_foreign` (`unit_id`),
  ADD KEY `transactions_user_id_foreign` (`user_id`),
  ADD KEY `transactions_parent_transaction_id_foreign` (`parent_transaction_id`),
  ADD KEY `transactions_condominium_id_index` (`condominium_id`),
  ADD KEY `transactions_transaction_date_index` (`transaction_date`),
  ADD KEY `transactions_status_index` (`status`),
  ADD KEY `transactions_condominium_id_type_status_index` (`condominium_id`,`type`,`status`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `units_condominium_id_number_block_unique` (`condominium_id`,`number`,`block`),
  ADD KEY `units_condominium_id_index` (`condominium_id`),
  ADD KEY `units_cep_index` (`cep`),
  ADD KEY `units_situacao_index` (`situacao`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_cpf_unique` (`cpf`),
  ADD UNIQUE KEY `users_qr_code_unique` (`qr_code`),
  ADD KEY `users_condominium_id_index` (`condominium_id`),
  ADD KEY `users_unit_id_index` (`unit_id`),
  ADD KEY `users_morador_vinculado_id_index` (`morador_vinculado_id`),
  ADD KEY `users_data_nascimento_index` (`data_nascimento`),
  ADD KEY `users_data_entrada_index` (`data_entrada`);

--
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_activity_logs_condominium_id_foreign` (`condominium_id`),
  ADD KEY `user_activity_logs_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `user_activity_logs_module_action_index` (`module`,`action`);

--
-- Indexes for table `user_credits`
--
ALTER TABLE `user_credits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_credits_reservation_id_foreign` (`reservation_id`),
  ADD KEY `user_credits_charge_id_foreign` (`charge_id`),
  ADD KEY `user_credits_used_in_reservation_id_foreign` (`used_in_reservation_id`),
  ADD KEY `user_credits_user_id_status_index` (`user_id`,`status`),
  ADD KEY `user_credits_condominium_id_status_index` (`condominium_id`,`status`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `votes_assembly_id_user_id_agenda_item_unique` (`assembly_id`,`user_id`,`agenda_item`),
  ADD KEY `votes_unit_id_foreign` (`unit_id`),
  ADD KEY `votes_delegated_from_foreign` (`delegated_from`),
  ADD KEY `votes_assembly_id_index` (`assembly_id`),
  ADD KEY `votes_user_id_index` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agregado_permissions`
--
ALTER TABLE `agregado_permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `assemblies`
--
ALTER TABLE `assemblies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assembly_attachments`
--
ALTER TABLE `assembly_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assembly_items`
--
ALTER TABLE `assembly_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assembly_status_logs`
--
ALTER TABLE `assembly_status_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assembly_votes`
--
ALTER TABLE `assembly_votes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audits`
--
ALTER TABLE `audits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `bank_statements`
--
ALTER TABLE `bank_statements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `charges`
--
ALTER TABLE `charges`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `condominiums`
--
ALTER TABLE `condominiums`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `entries`
--
ALTER TABLE `entries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `internal_regulations`
--
ALTER TABLE `internal_regulations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `internal_regulation_history`
--
ALTER TABLE `internal_regulation_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `marketplace_items`
--
ALTER TABLE `marketplace_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `panic_alerts`
--
ALTER TABLE `panic_alerts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `profile_selections`
--
ALTER TABLE `profile_selections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recurring_reservations`
--
ALTER TABLE `recurring_reservations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `spaces`
--
ALTER TABLE `spaces`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=266;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_credits`
--
ALTER TABLE `user_credits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agregado_permissions`
--
ALTER TABLE `agregado_permissions`
  ADD CONSTRAINT `agregado_permissions_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agregado_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assemblies`
--
ALTER TABLE `assemblies`
  ADD CONSTRAINT `assemblies_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assemblies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assembly_allowed_roles`
--
ALTER TABLE `assembly_allowed_roles`
  ADD CONSTRAINT `assembly_allowed_roles_assembly_id_foreign` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assembly_allowed_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assembly_attachments`
--
ALTER TABLE `assembly_attachments`
  ADD CONSTRAINT `assembly_attachments_assembly_id_foreign` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assembly_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assembly_items`
--
ALTER TABLE `assembly_items`
  ADD CONSTRAINT `assembly_items_assembly_id_foreign` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assembly_status_logs`
--
ALTER TABLE `assembly_status_logs`
  ADD CONSTRAINT `assembly_status_logs_assembly_id_foreign` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assembly_status_logs_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `assembly_votes`
--
ALTER TABLE `assembly_votes`
  ADD CONSTRAINT `assembly_votes_assembly_id_foreign` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assembly_votes_assembly_item_id_foreign` FOREIGN KEY (`assembly_item_id`) REFERENCES `assembly_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assembly_votes_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assembly_votes_voter_id_foreign` FOREIGN KEY (`voter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bank_statements`
--
ALTER TABLE `bank_statements`
  ADD CONSTRAINT `bank_statements_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bank_statements_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `charges`
--
ALTER TABLE `charges`
  ADD CONSTRAINT `charges_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `charges_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `entries`
--
ALTER TABLE `entries`
  ADD CONSTRAINT `entries_authorized_by_foreign` FOREIGN KEY (`authorized_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `entries_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entries_registered_by_foreign` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entries_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `internal_regulations`
--
ALTER TABLE `internal_regulations`
  ADD CONSTRAINT `internal_regulations_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internal_regulations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `internal_regulation_history`
--
ALTER TABLE `internal_regulation_history`
  ADD CONSTRAINT `internal_regulation_history_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internal_regulation_history_internal_regulation_id_foreign` FOREIGN KEY (`internal_regulation_id`) REFERENCES `internal_regulations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internal_regulation_history_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `marketplace_items`
--
ALTER TABLE `marketplace_items`
  ADD CONSTRAINT `marketplace_items_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marketplace_items_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marketplace_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_from_user_id_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_to_user_id_foreign` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `packages_collected_by_foreign` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `packages_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `packages_registered_by_foreign` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `packages_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `panic_alerts`
--
ALTER TABLE `panic_alerts`
  ADD CONSTRAINT `panic_alerts_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `panic_alerts_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `panic_alerts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_charge_id_foreign` FOREIGN KEY (`charge_id`) REFERENCES `charges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pets_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profile_selections`
--
ALTER TABLE `profile_selections`
  ADD CONSTRAINT `profile_selections_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recurring_reservations`
--
ALTER TABLE `recurring_reservations`
  ADD CONSTRAINT `recurring_reservations_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recurring_reservations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recurring_reservations_space_id_foreign` FOREIGN KEY (`space_id`) REFERENCES `spaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_admin_action_by_foreign` FOREIGN KEY (`admin_action_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reservations_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reservations_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reservations_recurring_reservation_id_foreign` FOREIGN KEY (`recurring_reservation_id`) REFERENCES `recurring_reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_space_id_foreign` FOREIGN KEY (`space_id`) REFERENCES `spaces` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spaces`
--
ALTER TABLE `spaces`
  ADD CONSTRAINT `spaces_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_parent_transaction_id_foreign` FOREIGN KEY (`parent_transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `units_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_morador_vinculado_id_foreign` FOREIGN KEY (`morador_vinculado_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD CONSTRAINT `user_activity_logs_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_credits`
--
ALTER TABLE `user_credits`
  ADD CONSTRAINT `user_credits_charge_id_foreign` FOREIGN KEY (`charge_id`) REFERENCES `charges` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_credits_condominium_id_foreign` FOREIGN KEY (`condominium_id`) REFERENCES `condominiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_credits_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_credits_used_in_reservation_id_foreign` FOREIGN KEY (`used_in_reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_credits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `votes`
--
ALTER TABLE `votes`
  ADD CONSTRAINT `votes_assembly_id_foreign` FOREIGN KEY (`assembly_id`) REFERENCES `assemblies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_delegated_from_foreign` FOREIGN KEY (`delegated_from`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `votes_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
