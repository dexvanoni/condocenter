<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CondoCenter - Sistema Completo de Gestão Condominial</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-color: #f8f9fa;
            --text-dark: #2d3748;
            --text-light: #718096;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #f56565;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            color: white;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            animation: fadeInUp 1s ease-out;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 300;
            margin-bottom: 2rem;
            opacity: 0.95;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .hero-description {
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            top: 60%;
            right: 15%;
            animation-delay: 5s;
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 20%;
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Section Styles */
        .section {
            padding: 80px 0;
            position: relative;
            z-index: 2;
            background: white;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 3rem;
        }

        /* Problem Cards */
        .problem-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            border-left: 4px solid var(--danger-color);
        }

        .problem-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .problem-icon {
            font-size: 3rem;
            color: var(--danger-color);
            margin-bottom: 1rem;
        }

        /* Solution Cards */
        .solution-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            border-top: 4px solid transparent;
        }

        .solution-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-top-color: #667eea;
        }

        .solution-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            animation: pulse 2s infinite;
        }

        /* Module Section */
        .module-section {
            background: var(--secondary-color);
            padding: 80px 0;
            position: relative;
            z-index: 2;
        }

        .module-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--primary-gradient);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .module-card:hover::before {
            transform: scaleX(1);
        }

        .module-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.2);
        }

        .module-icon {
            font-size: 4rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
        }

        .feature-list li {
            padding: 0.75rem 0;
            padding-left: 2rem;
            position: relative;
        }

        .feature-list li::before {
            content: '\f26a';
            font-family: 'bootstrap-icons';
            position: absolute;
            left: 0;
            color: var(--success-color);
            font-size: 1.2rem;
        }

        /* Stats Section */
        .stats-section {
            background: var(--primary-gradient);
            color: white;
            padding: 60px 0;
            position: relative;
            z-index: 2;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta-section {
            background: var(--primary-gradient);
            color: white;
            padding: 80px 0;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .btn-cta {
            background: white;
            color: #667eea;
            padding: 1rem 3rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            color: #764ba2;
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
        }

        .slide-in-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .slide-in-left.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .slide-in-right {
            opacity: 0;
            transform: translateX(50px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .slide-in-right.visible {
            opacity: 1;
            transform: translateX(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .stat-number {
                font-size: 2.5rem;
            }
        }

        /* Highlight Box */
        .highlight-box {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            border-left: 4px solid #667eea;
            padding: 2rem;
            border-radius: 10px;
            margin: 2rem 0;
        }

        .badge-module {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 hero-content">
                    <h1 class="hero-title">CondoCenter</h1>
                    <p class="hero-subtitle">A Solução Completa para Gestão Condominial</p>
                    <p class="hero-description">
                        Sistema moderno e intuitivo desenvolvido especialmente para administradores e síndicos 
                        que buscam eficiência, transparência e controle total na gestão de seus condomínios.
                    </p>
                    <a href="#modulos" class="btn btn-light btn-lg px-5 py-3 rounded-pill shadow-lg">
                        <i class="bi bi-arrow-down-circle me-2"></i>Conheça os Módulos
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Problems Section -->
    <section class="section" id="problemas">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Problemas que Resolvemos</h2>
                <p class="section-subtitle">Entendemos as principais dores na gestão condominial e oferecemos soluções inteligentes</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="problem-card fade-in">
                        <div class="problem-icon">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <h4>Falta de Transparência Financeira</h4>
                        <p>Moradores sem acesso às movimentações financeiras, gerando desconfiança e questionamentos constantes sobre a gestão.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="problem-card fade-in">
                        <div class="problem-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h4>Prestação de Contas Manual</h4>
                        <p>Processo trabalhoso e propenso a erros na geração de relatórios e prestação de contas mensais.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="problem-card fade-in">
                        <div class="problem-icon">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                        <h4>Conflitos em Reservas</h4>
                        <p>Dupla reserva de espaços, falta de controle de horários e ausência de histórico organizado.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="problem-card fade-in">
                        <div class="problem-icon">
                            <i class="bi bi-envelope-x"></i>
                        </div>
                        <h4>Comunicação Ineficiente</h4>
                        <p>Dificuldade em comunicar eventos, avisos e informações importantes para todos os moradores de forma organizada.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="problem-card fade-in">
                        <div class="problem-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h4>Controle de Encomendas</h4>
                        <p>Falta de rastreamento e notificação automática quando encomendas chegam no condomínio.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="problem-card fade-in">
                        <div class="problem-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <h4>Gestão de Pagamentos Complexa</h4>
                        <p>Processo manual de cobrança e controle de inadimplência ineficiente, sem rastreamento adequado de pagamentos.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Solutions Section -->
    <section class="module-section" id="solucoes">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Nossas Soluções</h2>
                <p class="section-subtitle">Tecnologia avançada para transformar a gestão do seu condomínio</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card slide-in-left">
                        <div class="solution-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4>Transparência Total</h4>
                        <p>Moradores têm acesso completo a todas as movimentações financeiras em tempo real, com histórico detalhado e comprovantes digitais.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card slide-in-left">
                        <div class="solution-icon">
                            <i class="bi bi-robot"></i>
                        </div>
                        <h4>Automação Inteligente</h4>
                        <p>Prestação de contas gerada automaticamente, cobranças recorrentes configuradas e relatórios prontos para impressão.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card slide-in-left">
                        <div class="solution-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h4>Reservas Inteligentes</h4>
                        <p>Sistema de calendário visual que previne conflitos, controla limites e gerencia aprovações de forma automática ou manual.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card slide-in-right">
                        <div class="solution-icon">
                            <i class="bi bi-bell-fill"></i>
                        </div>
                        <h4>Notificações Automáticas</h4>
                        <p>Comunicação instantânea via email e notificações push para manter todos informados sobre eventos importantes.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card slide-in-right">
                        <div class="solution-icon">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                        <h4>Controle Digital</h4>
                        <p>Rastreamento completo de encomendas e histórico organizado de todas as movimentações do condomínio.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card slide-in-right">
                        <div class="solution-icon">
                            <i class="bi bi-credit-card-2-front"></i>
                        </div>
                        <h4>Gestão de Pagamentos</h4>
                        <p>Controle completo de cobranças e pagamentos, com confirmação manual pelos administradores e síndicos, garantindo total controle sobre as transações.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modules Section -->
    <section class="section" id="modulos">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Módulos Completos</h2>
                <p class="section-subtitle">Sistema modular e completo para todas as necessidades do condomínio</p>
            </div>

            <!-- Gestão Financeira -->
            <div class="module-card mb-5 fade-in">
                <div class="row align-items-center">
                    <div class="col-lg-2 text-center mb-4 mb-lg-0">
                        <div class="module-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    <div class="col-lg-10">
                        <h3 class="mb-3">💰 Gestão Financeira Completa</h3>
                        <p class="lead mb-4">
                            Controle total e transparente de todas as movimentações financeiras do condomínio, 
                            com acesso completo para moradores e ferramentas avançadas para administradores.
                        </p>
                        
                        <div class="highlight-box">
                            <h5 class="mb-3"><i class="bi bi-star-fill text-warning me-2"></i>Transparência Total</h5>
                            <p class="mb-0">
                                Todos os moradores têm acesso em tempo real a todas as movimentações contábeis, 
                                entradas e saídas, pagamentos e recebimentos de taxas. Nada fica oculto - 
                                transparência completa gera confiança e reduz questionamentos.
                            </p>
                        </div>

                        <div class="row g-3 mt-4">
                            <div class="col-md-6">
                                <ul class="feature-list">
                                    <li>Lançamento de receitas e despesas com categorização</li>
                                    <li>Upload obrigatório de comprovantes (PDF/Imagem)</li>
                                    <li>Histórico completo de todas as transações</li>
                                    <li>Filtros avançados por período, categoria e tipo</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="feature-list">
                                    <li>Lançamentos recorrentes automáticos</li>
                                    <li>Conciliação bancária com upload de extratos</li>
                                    <li>Exportação para Excel e PDF</li>
                                    <li>Auditoria completa de todas as operações</li>
                                </ul>
                            </div>
                        </div>

                        <div class="highlight-box mt-4">
                            <h5 class="mb-3"><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Prestação de Contas Automatizada</h5>
                            <p>
                                Geração automática de prestação de contas mensal com todos os detalhes financeiros, 
                                categorização de despesas, comprovantes anexados e relatórios prontos para impressão 
                                ou envio digital. Economize horas de trabalho manual.
                            </p>
                        </div>

                        <div class="row g-3 mt-4">
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Comprovantes Online</h5>
                                <p>
                                    Todos os comprovantes de pagamento e recebimento ficam armazenados digitalmente 
                                    e acessíveis online. Moradores podem visualizar e baixar comprovantes a qualquer momento, 
                                    eliminando a necessidade de arquivos físicos.
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-graph-up-arrow text-success me-2"></i>Relatórios Financeiros</h5>
                                <p>
                                    Relatório Financeiro completo e Relatório de Inadimplência - todos gerados automaticamente 
                                    com dados atualizados em tempo real, prontos para análise e tomada de decisão.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="badge-module">Cobranças Automáticas</span>
                            <span class="badge-module">Confirmação Manual de Pagamentos</span>
                            <span class="badge-module">Cálculo de Multa e Juros</span>
                            <span class="badge-module">Lembretes Automáticos</span>
                            <span class="badge-module">Controle de Inadimplência</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sistema de Reservas -->
            <div class="module-card mb-5 fade-in">
                <div class="row align-items-center">
                    <div class="col-lg-2 text-center mb-4 mb-lg-0">
                        <div class="module-icon">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                    </div>
                    <div class="col-lg-10">
                        <h3 class="mb-3">📅 Sistema de Agendamentos e Reservas</h3>
                        <p class="lead mb-4">
                            Gerencie todos os espaços comuns do condomínio de forma inteligente, 
                            evitando conflitos e garantindo organização total.
                        </p>

                        <div class="highlight-box">
                            <h5 class="mb-3"><i class="bi bi-calendar-check-fill text-success me-2"></i>Calendário Visual Intuitivo</h5>
                            <p class="mb-3">
                                Interface moderna com calendário visual que mostra todas as reservas de forma clara e organizada. 
                                Moradores podem ver disponibilidade em tempo real e fazer reservas com apenas alguns cliques.
                            </p>
                            <ul class="feature-list mb-0">
                                <li>Visualização mensal e semanal de reservas</li>
                                <li>Indicadores visuais de disponibilidade (verde/vermelho)</li>
                                <li>Filtros por tipo de espaço e data</li>
                                <li>Histórico completo de reservas anteriores</li>
                            </ul>
                        </div>

                        <div class="row g-4 mt-4">
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Prevenção de Conflitos</h5>
                                <p>
                                    Sistema inteligente que <strong>impede automaticamente</strong> reservas duplicadas para o mesmo espaço 
                                    na mesma data. Validação em tempo real antes de confirmar a reserva, garantindo que não haverá conflitos.
                                </p>
                                <ul class="feature-list">
                                    <li>Verificação automática de disponibilidade</li>
                                    <li>Bloqueio de datas já reservadas</li>
                                    <li>Notificação imediata de conflitos</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-sliders text-warning me-2"></i>Controles e Limites</h5>
                                <p>
                                    Configure limites personalizados para cada espaço: número máximo de reservas por mês por unidade, 
                                    horários de funcionamento, e regras específicas de uso.
                                </p>
                                <ul class="feature-list">
                                    <li>Limite de reservas por mês</li>
                                    <li>Horários de funcionamento configuráveis</li>
                                    <li>Regras de uso personalizadas</li>
                                    <li>Capacidade máxima por espaço</li>
                                </ul>
                            </div>
                        </div>

                        <div class="highlight-box mt-4">
                            <h5 class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i>Tipos de Aprovação</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <strong>Aprovação Automática</strong>
                                    <p class="small mb-0">Reserva confirmada imediatamente após solicitação</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Aprovação Manual</strong>
                                    <p class="small mb-0">Síndico revisa e aprova cada reserva</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Pré-Reserva com Pagamento</strong>
                                    <p class="small mb-0">Reserva confirmada após pagamento da taxa</p>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-4">
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-credit-card text-primary me-2"></i>Cobrança de Taxas</h5>
                                <p>
                                    Configure taxas de reserva para espaços específicos. O sistema registra a cobrança 
                                    e permite que administradores e síndicos confirmem o pagamento manualmente, 
                                    garantindo controle total sobre as transações.
                                </p>
                                <ul class="feature-list">
                                    <li>Taxa por hora ou taxa fixa</li>
                                    <li>Registro automático de cobrança</li>
                                    <li>Confirmação manual de pagamento</li>
                                    <li>Histórico completo de transações</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-bell-fill text-warning me-2"></i>Notificações Automáticas</h5>
                                <p>
                                    Sistema envia notificações automáticas para confirmar reservas, lembrar de reservas próximas, 
                                    e notificar sobre cancelamentos ou alterações.
                                </p>
                                <ul class="feature-list">
                                    <li>Confirmação imediata de reserva</li>
                                    <li>Lembrete 24h antes da reserva</li>
                                    <li>Notificação de cancelamentos</li>
                                    <li>Email e notificação no sistema</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="badge-module">7 Tipos de Espaços</span>
                            <span class="badge-module">Calendário Visual</span>
                            <span class="badge-module">Reservas Recorrentes</span>
                            <span class="badge-module">Histórico Completo</span>
                            <span class="badge-module">Relatórios de Uso</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Marketplace -->
            <div class="module-card mb-5 fade-in">
                <div class="row align-items-center">
                    <div class="col-lg-2 text-center mb-4 mb-lg-0">
                        <div class="module-icon">
                            <i class="bi bi-shop"></i>
                        </div>
                    </div>
                    <div class="col-lg-10">
                        <h3 class="mb-3">🛒 Marketplace Interno</h3>
                        <p class="lead mb-4">
                            Plataforma de compra e venda exclusiva para moradores do condomínio, 
                            promovendo economia circular e fortalecendo a comunidade.
                        </p>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-images text-primary me-2"></i>Anúncios Completos</h5>
                                <p>
                                    Crie anúncios profissionais com até 3 imagens, descrição detalhada, 
                                    preço e informações de contato. Sistema de categorização facilita a busca.
                                </p>
                                <ul class="feature-list">
                                    <li>Upload de até 3 imagens por anúncio</li>
                                    <li>6 categorias: Produtos, Serviços, Empregos, Imóveis, Veículos, Outros</li>
                                    <li>Estado do produto (Novo, Usado, Recondicionado)</li>
                                    <li>Contador de visualizações</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-search text-success me-2"></i>Busca e Filtros</h5>
                                <p>
                                    Sistema de busca inteligente e filtros avançados para encontrar exatamente 
                                    o que você procura rapidamente.
                                </p>
                                <ul class="feature-list">
                                    <li>Busca por palavras-chave</li>
                                    <li>Filtro por categoria</li>
                                    <li>Filtro por vendedor</li>
                                    <li>Ordenação por data ou preço</li>
                                </ul>
                            </div>
                        </div>

                        <div class="highlight-box mt-4">
                            <h5 class="mb-3"><i class="bi bi-people-fill text-primary me-2"></i>Vantagens do Marketplace</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>💰 Economia Circular</strong>
                                    <p class="small mb-0">Moradores compram e vendem entre si, gerando economia e sustentabilidade</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>🤝 Fortalecimento da Comunidade</strong>
                                    <p class="small mb-0">Promove interação e relacionamento entre os moradores</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>🔒 Ambiente Seguro</strong>
                                    <p class="small mb-0">Apenas moradores cadastrados podem anunciar e comprar</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>📱 Acesso Fácil</strong>
                                    <p class="small mb-0">Disponível 24/7 através do sistema web e mobile</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="badge-module">Sistema de Mensagens</span>
                            <span class="badge-module">Controle de Status</span>
                            <span class="badge-module">Histórico de Vendas</span>
                            <span class="badge-module">Moderação Administrativa</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Encomendas -->
            <div class="module-card mb-5 fade-in">
                <div class="row align-items-center">
                    <div class="col-lg-2 text-center mb-4 mb-lg-0">
                        <div class="module-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                    <div class="col-lg-10">
                        <h3 class="mb-3">📦 Gerenciamento de Encomendas</h3>
                        <p class="lead mb-4">
                            Controle total sobre encomendas recebidas no condomínio, com notificações automáticas 
                            e rastreamento completo do processo.
                        </p>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-clipboard-check text-primary me-2"></i>Registro Completo</h5>
                                <p>
                                    Registro de encomenda com todas as informações: destinatário, 
                                    transportadora, código de rastreamento e observações.
                                </p>
                                <ul class="feature-list">
                                    <li>Registro rápido e intuitivo</li>
                                    <li>Busca por unidade ou morador</li>
                                    <li>Histórico completo de encomendas</li>
                                    <li>Status em tempo real</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-bell-fill text-warning me-2"></i>Notificação Automática</h5>
                                <p>
                                    Assim que a encomenda é registrada, o morador recebe notificação imediata 
                                    via email e no sistema, com todas as informações necessárias.
                                </p>
                                <ul class="feature-list">
                                    <li>Email automático ao morador</li>
                                    <li>Notificação no dashboard</li>
                                    <li>Informações da encomenda</li>
                                    <li>Link para visualizar detalhes</li>
                                </ul>
                            </div>
                        </div>

                        <div class="highlight-box mt-4">
                            <h5 class="mb-3"><i class="bi bi-check2-circle text-success me-2"></i>Controle de Entrega</h5>
                            <p>
                                Sistema de confirmação de retirada com registro de data e hora. 
                                Morador confirma retirada e encomenda é marcada como entregue automaticamente.
                            </p>
                        </div>

                        <div class="mt-4">
                            <span class="badge-module">Notificação Imediata</span>
                            <span class="badge-module">Rastreamento Completo</span>
                            <span class="badge-module">Histórico Indefinido</span>
                            <span class="badge-module">Relatórios de Entregas</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controle de Pets -->
            <div class="module-card mb-5 fade-in">
                <div class="row align-items-center">
                    <div class="col-lg-2 text-center mb-4 mb-lg-0">
                        <div class="module-icon">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                    </div>
                    <div class="col-lg-10">
                        <h3 class="mb-3">🐾 Controle de Pets</h3>
                        <p class="lead mb-4">
                            Cadastro completo e organizado de todos os animais de estimação do condomínio, 
                            facilitando gestão e segurança.
                        </p>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-file-earmark-person text-primary me-2"></i>Cadastro Detalhado</h5>
                                <p>
                                    Registre informações completas de cada pet: nome, raça, porte, cor, 
                                    foto e dados do responsável.
                                </p>
                                <ul class="feature-list">
                                    <li>Upload de foto do animal</li>
                                    <li>Informações completas do pet</li>
                                    <li>Vinculação com unidade e morador</li>
                                    <li>Histórico de cadastros</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="bi bi-shield-check text-success me-2"></i>Gestão e Segurança</h5>
                                <p>
                                    Controle total sobre animais cadastrados no condomínio, facilitando 
                                    identificação e gestão de regras específicas.
                                </p>
                                <ul class="feature-list">
                                    <li>Lista completa de pets por unidade</li>
                                    <li>Busca rápida por nome ou unidade</li>
                                    <li>Relatórios de cadastros</li>
                                    <li>Exportação de dados</li>
                                </ul>
                            </div>
                        </div>

                        <div class="highlight-box mt-4">
                            <h5 class="mb-3"><i class="bi bi-info-circle-fill text-info me-2"></i>Vantagens</h5>
                            <p class="mb-0">
                                Facilita o cumprimento de regras condominiais relacionadas a animais, 
                                permite identificação rápida em caso de necessidade e mantém registro 
                                organizado e acessível para administração e segurança.
                            </p>
                        </div>

                        <div class="mt-4">
                            <span class="badge-module">Cadastro Completo</span>
                            <span class="badge-module">Fotos dos Animais</span>
                            <span class="badge-module">Busca Avançada</span>
                            <span class="badge-module">Relatórios</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outros Módulos -->
            <div class="row g-4 mt-4">
                <div class="col-md-6">
                    <div class="module-card fade-in">
                        <div class="module-icon text-center mb-3">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4>👥 Assembleias Online</h4>
                        <p>Sistema completo de assembleias com votação online, delegação de votos, geração automática de atas e histórico completo.</p>
                        <ul class="feature-list">
                            <li>Votação aberta ou secreta</li>
                            <li>Delegação de votos</li>
                            <li>Geração automática de atas</li>
                            <li>Histórico completo</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="module-card fade-in">
                        <div class="module-icon text-center mb-3">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h4>💬 Comunicação Interna</h4>
                        <p>Sistema de mensagens entre moradores e administração, notificações em tempo real e alertas importantes.</p>
                        <ul class="feature-list">
                            <li>Mensagens diretas</li>
                            <li>Notificações em tempo real</li>
                            <li>Alertas de emergência</li>
                            <li>Histórico de conversas</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="module-card fade-in">
                        <div class="module-icon text-center mb-3">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <h4>🚨 Sistema de Pânico</h4>
                        <p>Botão de emergência exclusivo que notifica todos os moradores e administração em caso de situação crítica.</p>
                        <ul class="feature-list">
                            <li>7 tipos de emergência</li>
                            <li>Notificação imediata</li>
                            <li>Email urgente</li>
                            <li>Registro completo</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number" data-count="17">0</div>
                        <div class="stat-label">Módulos Completos</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number" data-count="40">0</div>
                        <div class="stat-label">Permissões Granulares</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number" data-count="6">0</div>
                        <div class="stat-label">Perfis de Usuário</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item">
                        <div class="stat-number" data-count="100">0</div>
                        <div class="stat-label">% Funcional</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="module-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Vantagens e Potenciais</h2>
                <p class="section-subtitle">Por que escolher o CondoCenter para seu condomínio?</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card">
                        <div class="solution-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <h4>Eficiência Operacional</h4>
                        <p>Reduza em até 80% o tempo gasto com tarefas administrativas manuais. Automação inteligente libera tempo para gestão estratégica.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card">
                        <div class="solution-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h4>Segurança e Confiabilidade</h4>
                        <p>Dados protegidos com criptografia, backups automáticos e auditoria completa de todas as operações críticas.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card">
                        <div class="solution-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h4>Multiplataforma</h4>
                        <p>Acesse de qualquer dispositivo - computador, tablet ou smartphone. Interface responsiva e otimizada para todos os tamanhos de tela.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card">
                        <div class="solution-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h4>Escalabilidade</h4>
                        <p>Sistema preparado para crescer com seu condomínio. Suporta desde pequenos até grandes complexos residenciais.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card">
                        <div class="solution-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h4>Suporte Completo</h4>
                        <p>Documentação detalhada, treinamento para equipe administrativa e suporte técnico especializado quando necessário.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="solution-card">
                        <div class="solution-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <h4>Economia de Custos</h4>
                        <p>Reduza custos com papel, impressão, tempo de trabalho manual e processos ineficientes. ROI comprovado.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Implementation Section -->
    <section class="section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Facilidade de Implementação</h2>
                <p class="section-subtitle">Processos bem definidos e estruturados para uma implementação rápida e eficiente</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="module-card text-center">
                        <div class="solution-icon mx-auto mb-3">
                            <i class="bi bi-1-circle-fill"></i>
                        </div>
                        <h4>Configuração Inicial</h4>
                        <p>Setup rápido com assistência completa. Cadastro inicial de condomínio, unidades e usuários em poucos minutos.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="module-card text-center">
                        <div class="solution-icon mx-auto mb-3">
                            <i class="bi bi-2-circle-fill"></i>
                        </div>
                        <h4>Treinamento da Equipe</h4>
                        <p>Treinamento completo para administradores, síndicos e equipe financeira. Documentação detalhada e suporte durante a transição.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="module-card text-center">
                        <div class="solution-icon mx-auto mb-3">
                            <i class="bi bi-3-circle-fill"></i>
                        </div>
                        <h4>Migração de Dados</h4>
                        <p>Importação facilitada de dados existentes. Suporte para migração de planilhas e sistemas anteriores.</p>
                    </div>
                </div>
            </div>
            <div class="highlight-box mt-5">
                <h5 class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i>Rotinas e Processos Estruturados</h5>
                <p class="mb-3">
                    O sistema foi desenvolvido com base em melhores práticas de gestão condominial. 
                    Todas as rotinas e processos estão bem definidos e estruturados, facilitando 
                    a manipulação pela equipe administrativa e financeira, mesmo sem conhecimento técnico avançado.
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <ul class="feature-list">
                            <li>Interface intuitiva e autoexplicativa</li>
                            <li>Fluxos de trabalho otimizados</li>
                            <li>Validações automáticas que previnem erros</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="feature-list">
                            <li>Relatórios prontos para uso</li>
                            <li>Documentação completa de cada módulo</li>
                            <li>Suporte durante todo o processo</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="cta-title">Pronto para Transformar a Gestão do Seu Condomínio?</h2>
            <p class="lead mb-4">Entre em contato e descubra como o CondoCenter pode revolucionar a administração do seu condomínio</p>
            <button class="btn btn-cta">
                <i class="bi bi-envelope-fill me-2"></i>Solicitar Demonstração
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> CondoCenter. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for Animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right').forEach(el => {
            observer.observe(el);
        });

        // Counter Animation
        function animateCounter(element) {
            const target = parseInt(element.getAttribute('data-count'));
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 16);
        }

        // Observe stats section
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.querySelectorAll('.stat-number').forEach(stat => {
                        if (!stat.classList.contains('counted')) {
                            stat.classList.add('counted');
                            animateCounter(stat);
                        }
                    });
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.stats-section');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }

        // Parallax Effect for Hero (removido para evitar sobreposição)
        // O efeito de parallax estava causando sobreposição do conteúdo

        // Add hover effect to module cards
        document.querySelectorAll('.module-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.02)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>

