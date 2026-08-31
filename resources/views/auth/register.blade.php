<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Criar Conta - CondoManager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-dark: #0a1b67;
            --brand-light: #3866d2;
            --brand-accent: #14b8a6;
        }
        body {
            background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand-light) 100%);
            min-height: 100vh;
            padding: 1.5rem 0 3rem;
        }
        .register-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.15);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand-light) 100%);
            color: #fff;
            padding: 1.75rem 1.5rem;
            text-align: center;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: .5rem;
            margin-top: 1rem;
        }
        .step-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,.35);
            transition: all .2s ease;
        }
        .step-dot.active { background: #fff; transform: scale(1.15); }
        .step-dot.done { background: var(--brand-accent); }
        .wizard-step { display: none; }
        .wizard-step.active { display: block; }
        .choice-card {
            border: 2px solid #e9ecef;
            border-radius: 16px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all .2s ease;
            height: 100%;
            text-align: center;
        }
        .choice-card:hover, .choice-card.selected {
            border-color: var(--brand-light);
            background: #f0f5ff;
            box-shadow: 0 8px 24px rgba(56,102,210,.12);
        }
        .choice-card .icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: .75rem;
            background: #eef2ff;
            color: var(--brand-dark);
        }
        .choice-card.selected .icon-wrap {
            background: var(--brand-light);
            color: #fff;
        }
        .help-text { color: #6c757d; font-size: .925rem; }
        .condo-badge {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            border-radius: 12px;
            padding: .75rem 1rem;
        }
        .morador-result {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: .75rem 1rem;
            cursor: pointer;
            transition: background .15s ease;
        }
        .morador-result:hover, .morador-result.selected {
            background: #eef2ff;
            border-color: var(--brand-light);
        }
        .form-control, .form-select {
            border-radius: 12px;
            padding: .75rem 1rem;
        }
        .btn-brand {
            background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand-light) 100%);
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: .75rem 1.25rem;
        }
        .btn-brand:hover { color: #fff; opacity: .92; }
        .review-box {
            background: #f8fafc;
            border-radius: 14px;
            padding: 1rem 1.25rem;
        }
        .password-toggle-btn {
            border-radius: 0 12px 12px 0 !important;
        }
        .input-group .form-control.password-field {
            border-right: 0;
            border-radius: 12px 0 0 12px !important;
        }
        .selfie-shell {
            max-width: 320px;
            margin: 0 auto;
        }
        .selfie-frame {
            width: 240px;
            height: 240px;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--brand-light);
            background: #111;
            position: relative;
        }
        .selfie-frame video,
        .selfie-frame img,
        .selfie-frame canvas {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }
        .selfie-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            flex-direction: column;
            gap: .5rem;
            font-size: .9rem;
        }
        .review-photo {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--brand-light);
        }
        @media (max-width: 576px) {
            .register-header h2 { font-size: 1.35rem; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="register-card">
                <div class="register-header">
                    <h2 class="mb-1"><i class="bi bi-person-plus-fill me-2"></i>Criar sua conta</h2>
                    <p class="mb-0 opacity-75">Cadastro simples — a administração aprova antes do primeiro acesso</p>
                    <div class="step-indicator" id="stepIndicator">
                        <span class="step-dot active" data-step="1"></span>
                        <span class="step-dot" data-step="2"></span>
                        <span class="step-dot" data-step="3"></span>
                        <span class="step-dot" data-step="4"></span>
                        <span class="step-dot" data-step="5"></span>
                        <span class="step-dot" data-step="6"></span>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Não foi possível concluir o cadastro:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}" id="registerForm" enctype="multipart/form-data" novalidate>
                        @csrf

                        <!-- Passo 1: Código -->
                        <div class="wizard-step active" data-step="1">
                            <h4 class="fw-bold mb-2">1. Código do condomínio</h4>
                            <p class="help-text mb-4">Peça o código de cadastro na portaria ou administração.</p>
                            <label class="form-label fw-semibold">Código de cadastro</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="text" class="form-control text-uppercase" id="registration_code"
                                       name="registration_code" value="{{ old('registration_code') }}"
                                       placeholder="Ex: ABC12345" autocomplete="off" required>
                                <button type="button" class="btn btn-outline-primary" id="btnValidateCode">Validar</button>
                            </div>
                            <div id="condoInfo" class="condo-badge d-none mb-3">
                                <i class="bi bi-building-check me-2"></i>
                                <strong id="condoName"></strong>
                                <span class="d-block small mt-1" id="condoLocation"></span>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-brand" id="btnStep1Next" disabled>
                                    Continuar <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Passo 2: Tipo -->
                        <div class="wizard-step" data-step="2">
                            <h4 class="fw-bold mb-2">2. Qual é o seu perfil?</h4>
                            <p class="help-text mb-4">Escolha a opção que melhor descreve você no condomínio.</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="choice-card" data-type="compossuidor">
                                        <div class="icon-wrap"><i class="bi bi-house-door"></i></div>
                                        <h5 class="fw-bold mb-2">Compossuidor</h5>
                                        <p class="help-text mb-0">Sou titular/responsável pela unidade.<br><small>Perfil: Morador</small></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="choice-card" data-type="dependente">
                                        <div class="icon-wrap"><i class="bi bi-people"></i></div>
                                        <h5 class="fw-bold mb-2">Dependente</h5>
                                        <p class="help-text mb-0">Moro com um morador responsável.<br><small>Perfil: Agregado</small></p>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="registration_type" id="registration_type" value="{{ old('registration_type') }}">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary btn-prev">Voltar</button>
                                <button type="button" class="btn btn-brand btn-next" data-next="3" disabled>Continuar</button>
                            </div>
                        </div>

                        <!-- Passo 3: Dados pessoais -->
                        <div class="wizard-step" data-step="3">
                            <h4 class="fw-bold mb-2">3. Seus dados</h4>
                            <p class="help-text mb-4">Preencha com calma. Campos com * são obrigatórios.</p>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Nome completo *</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">CPF *</label>
                                    <input type="text" class="form-control" name="cpf" id="cpf" value="{{ old('cpf') }}"
                                           placeholder="000.000.000-00" maxlength="14" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Data de nascimento</label>
                                    <input type="date" class="form-control" name="data_nascimento" value="{{ old('data_nascimento') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Celular *</label>
                                    <input type="text" class="form-control" name="telefone_celular" id="telefone_celular"
                                           value="{{ old('telefone_celular') }}" placeholder="(00) 00000-0000" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">E-mail *</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Senha *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control password-field" id="password"
                                               name="password" minlength="8" required autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary password-toggle-btn toggle-password"
                                                data-target="password" aria-label="Mostrar senha">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Mínimo de 8 caracteres</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Confirmar senha *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control password-field" id="password_confirmation"
                                               name="password_confirmation" minlength="8" required autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary password-toggle-btn toggle-password"
                                                data-target="password_confirmation" aria-label="Mostrar confirmação de senha">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div id="passwordMatchFeedback" class="small mt-2"></div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev">Voltar</button>
                                <button type="button" class="btn btn-brand btn-next" data-next="4">Continuar</button>
                            </div>
                        </div>

                        <!-- Passo 4: Vínculo -->
                        <div class="wizard-step" data-step="4">
                            <h4 class="fw-bold mb-2">4. Vínculo no condomínio</h4>
                            <div id="compossuidorFields" class="d-none">
                                <p class="help-text mb-3">Selecione a unidade à qual você está vinculado.</p>
                                <label class="form-label fw-semibold">Unidade *</label>
                                <select class="form-select mb-3" name="unit_id" id="unit_id">
                                    <option value="">Carregando unidades...</option>
                                </select>
                            </div>
                            <div id="dependenteFields" class="d-none">
                                <p class="help-text mb-3">Busque o morador responsável por você (nome ou CPF).</p>
                                <label class="form-label fw-semibold">Morador responsável *</label>
                                <input type="text" class="form-control mb-2" id="moradorSearch" placeholder="Digite nome ou CPF">
                                <input type="hidden" name="morador_vinculado_id" id="morador_vinculado_id" value="{{ old('morador_vinculado_id') }}">
                                <div id="moradorResults" class="d-flex flex-column gap-2 mb-3"></div>
                                <div id="selectedMorador" class="alert alert-info d-none"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-prev">Voltar</button>
                                <button type="button" class="btn btn-brand btn-next" data-next="5">Continuar</button>
                            </div>
                        </div>

                        <!-- Passo 5: Selfie -->
                        <div class="wizard-step" data-step="5">
                            <h4 class="fw-bold mb-2">5. Sua foto (selfie)</h4>
                            <p class="help-text mb-4 text-center">
                                Precisamos de uma foto do seu rosto para identificação. Use a câmera frontal em um local bem iluminado.
                            </p>

                            <div class="selfie-shell">
                                <div class="selfie-frame mb-3" id="selfieFrame">
                                    <div class="selfie-placeholder" id="selfiePlaceholder">
                                        <i class="bi bi-camera fs-1"></i>
                                        <span>Toque em abrir câmera</span>
                                    </div>
                                    <video id="selfieVideo" class="d-none" playsinline autoplay muted></video>
                                    <img id="selfiePreview" class="d-none" alt="Prévia da selfie">
                                </div>

                                <input type="file" name="photo" id="photoInput" class="d-none" accept="image/jpeg,image/png">

                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-brand" id="btnOpenCamera">
                                        <i class="bi bi-camera-video me-1"></i> Abrir câmera frontal
                                    </button>
                                    <button type="button" class="btn btn-success d-none" id="btnCapturePhoto">
                                        <i class="bi bi-camera-fill me-1"></i> Tirar foto
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary d-none" id="btnRetakePhoto">
                                        <i class="bi bi-arrow-repeat me-1"></i> Tirar outra foto
                                    </button>
                                </div>

                                <div id="selfieStatus" class="alert alert-info mt-3 d-none mb-0">
                                    <i class="bi bi-check-circle me-2"></i>Foto capturada com sucesso!
                                </div>
                                <div id="cameraError" class="alert alert-danger mt-3 d-none mb-0"></div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev">Voltar</button>
                                <button type="button" class="btn btn-brand btn-next" data-next="6">Continuar</button>
                            </div>
                        </div>

                        <!-- Passo 6: Revisão -->
                        <div class="wizard-step" data-step="6">
                            <h4 class="fw-bold mb-2">6. Revisar e enviar</h4>
                            <p class="help-text mb-3">Confira os dados antes de enviar. Seu cadastro ficará pendente até a aprovação.</p>
                            <div class="review-box mb-4" id="reviewBox"></div>
                            <div class="alert alert-warning">
                                <i class="bi bi-hourglass-split me-2"></i>
                                Após o envio, aguarde a aprovação da administração para acessar o sistema.
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary btn-prev">Voltar</button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle me-1"></i> Enviar cadastro
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="text-decoration-none">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Já tenho conta — entrar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let currentStep = 1;
    let codeValidated = false;
    let selectedType = document.getElementById('registration_type').value || '';
    let selectedMorador = null;
    let searchTimer = null;
    let cameraStream = null;
    let selfieCaptured = false;
    let selfiePreviewUrl = null;

    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirmation');
    const passwordMatchFeedback = document.getElementById('passwordMatchFeedback');
    const photoInput = document.getElementById('photoInput');
    const selfieVideo = document.getElementById('selfieVideo');
    const selfiePreview = document.getElementById('selfiePreview');
    const selfiePlaceholder = document.getElementById('selfiePlaceholder');
    const btnOpenCamera = document.getElementById('btnOpenCamera');
    const btnCapturePhoto = document.getElementById('btnCapturePhoto');
    const btnRetakePhoto = document.getElementById('btnRetakePhoto');
    const selfieStatus = document.getElementById('selfieStatus');
    const cameraError = document.getElementById('cameraError');

    const steps = document.querySelectorAll('.wizard-step');
    const dots = document.querySelectorAll('.step-dot');
    const codeInput = document.getElementById('registration_code');
    const btnValidateCode = document.getElementById('btnValidateCode');
    const btnStep1Next = document.getElementById('btnStep1Next');
    const condoInfo = document.getElementById('condoInfo');

    function showStep(step) {
        currentStep = step;
        steps.forEach(el => el.classList.toggle('active', Number(el.dataset.step) === step));
        dots.forEach(dot => {
            const n = Number(dot.dataset.step);
            dot.classList.toggle('active', n === step);
            dot.classList.toggle('done', n < step);
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });

        if (step !== 5) {
            stopCamera();
        }
    }

    function updatePasswordMatch() {
        const password = passwordInput.value;
        const confirmation = passwordConfirmInput.value;

        if (!confirmation) {
            passwordMatchFeedback.innerHTML = '';
            passwordConfirmInput.classList.remove('is-valid', 'is-invalid');
            return false;
        }

        if (password === confirmation && password.length >= 8) {
            passwordMatchFeedback.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Senhas conferem</span>';
            passwordConfirmInput.classList.remove('is-invalid');
            passwordConfirmInput.classList.add('is-valid');
            return true;
        }

        passwordConfirmInput.classList.remove('is-valid');
        passwordConfirmInput.classList.add('is-invalid');

        if (password !== confirmation) {
            passwordMatchFeedback.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>As senhas não conferem</span>';
        } else {
            passwordMatchFeedback.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-circle me-1"></i>A senha precisa ter no mínimo 8 caracteres</span>';
        }

        return false;
    }

    function validatePersonalStep() {
        const form = document.getElementById('registerForm');
        if (!form.reportValidity()) {
            return false;
        }

        if (passwordInput.value.length < 8) {
            alert('A senha deve ter no mínimo 8 caracteres.');
            passwordInput.focus();
            return false;
        }

        if (!updatePasswordMatch()) {
            alert('As senhas informadas não conferem.');
            passwordConfirmInput.focus();
            return false;
        }

        return true;
    }

    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.target;
            const input = document.getElementById(targetId);
            const icon = button.querySelector('i');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !isHidden);
            icon.classList.toggle('bi-eye-slash', isHidden);
        });
    });

    passwordInput?.addEventListener('input', updatePasswordMatch);
    passwordConfirmInput?.addEventListener('input', updatePasswordMatch);

    async function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        if (selfieVideo) {
            selfieVideo.srcObject = null;
            selfieVideo.classList.add('d-none');
        }
    }

    async function openFrontCamera() {
        cameraError.classList.add('d-none');
        cameraError.textContent = '';

        if (!navigator.mediaDevices?.getUserMedia) {
            cameraError.textContent = 'Seu navegador não suporta acesso à câmera. Tente outro dispositivo ou navegador.';
            cameraError.classList.remove('d-none');
            return;
        }

        try {
            await stopCamera();
            selfiePreview.classList.add('d-none');
            selfiePlaceholder.classList.add('d-none');
            selfieStatus.classList.add('d-none');
            selfieCaptured = false;
            photoInput.value = '';

            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'user' },
                    width: { ideal: 720 },
                    height: { ideal: 720 },
                },
                audio: false,
            });

            selfieVideo.srcObject = cameraStream;
            selfieVideo.classList.remove('d-none');
            btnCapturePhoto.classList.remove('d-none');
            btnRetakePhoto.classList.add('d-none');
            btnOpenCamera.innerHTML = '<i class="bi bi-camera-video-fill me-1"></i> Câmera aberta';
        } catch (error) {
            cameraError.textContent = 'Não foi possível acessar a câmera frontal. Verifique as permissões do navegador.';
            cameraError.classList.remove('d-none');
            selfiePlaceholder.classList.remove('d-none');
            btnOpenCamera.innerHTML = '<i class="bi bi-camera-video me-1"></i> Abrir câmera frontal';
        }
    }

    function captureSelfie() {
        if (!cameraStream || selfieVideo.readyState < 2) {
            alert('Aguarde a câmera carregar completamente.');
            return;
        }

        const size = 640;
        const canvas = document.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        const ctx = canvas.getContext('2d');

        const vw = selfieVideo.videoWidth;
        const vh = selfieVideo.videoHeight;
        const minSide = Math.min(vw, vh);
        const sx = (vw - minSide) / 2;
        const sy = (vh - minSide) / 2;

        ctx.translate(size, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(selfieVideo, sx, sy, minSide, minSide, 0, 0, size, size);

        canvas.toBlob((blob) => {
            if (!blob) {
                alert('Não foi possível capturar a foto. Tente novamente.');
                return;
            }

            if (selfiePreviewUrl) {
                URL.revokeObjectURL(selfiePreviewUrl);
            }

            const file = new File([blob], 'selfie.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            photoInput.files = dataTransfer.files;

            selfiePreviewUrl = URL.createObjectURL(blob);
            selfiePreview.src = selfiePreviewUrl;
            selfiePreview.classList.remove('d-none');
            selfieVideo.classList.add('d-none');
            selfieStatus.classList.remove('d-none');
            selfieCaptured = true;

            stopCamera();

            btnCapturePhoto.classList.add('d-none');
            btnRetakePhoto.classList.remove('d-none');
            btnOpenCamera.innerHTML = '<i class="bi bi-camera-video me-1"></i> Abrir câmera frontal';
        }, 'image/jpeg', 0.92);
    }

    async function retakeSelfie() {
        selfieCaptured = false;
        photoInput.value = '';
        selfiePreview.classList.add('d-none');
        selfieStatus.classList.add('d-none');
        btnRetakePhoto.classList.add('d-none');
        await openFrontCamera();
    }

    function validateSelfieStep() {
        if (!selfieCaptured || !photoInput.files.length) {
            alert('Tire uma selfie antes de continuar.');
            return false;
        }
        return true;
    }

    btnOpenCamera?.addEventListener('click', openFrontCamera);
    btnCapturePhoto?.addEventListener('click', captureSelfie);
    btnRetakePhoto?.addEventListener('click', retakeSelfie);

    function maskCpf(value) {
        return value.replace(/\D/g, '')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d)/, '$1.$2')
            .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
            .slice(0, 14);
    }

    function maskPhone(value) {
        value = value.replace(/\D/g, '');
        if (value.length <= 10) {
            return value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3').trim();
        }
        return value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3').trim();
    }

    document.getElementById('cpf')?.addEventListener('input', e => {
        e.target.value = maskCpf(e.target.value);
    });
    document.getElementById('telefone_celular')?.addEventListener('input', e => {
        e.target.value = maskPhone(e.target.value);
    });

    async function validateCode() {
        const code = codeInput.value.trim();
        if (!code) return;
        btnValidateCode.disabled = true;
        btnValidateCode.textContent = 'Validando...';
        try {
            const res = await fetch('{{ route('register.lookup') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ registration_code: code }),
            });
            const data = await res.json();
            if (!res.ok || !data.valid) {
                codeValidated = false;
                btnStep1Next.disabled = true;
                condoInfo.classList.add('d-none');
                alert(data.message || 'Código inválido.');
                return;
            }
            codeValidated = true;
            btnStep1Next.disabled = false;
            document.getElementById('condoName').textContent = data.condominium.name;
            document.getElementById('condoLocation').textContent = data.condominium.location || '';
            condoInfo.classList.remove('d-none');
        } catch {
            alert('Erro ao validar código. Tente novamente.');
        } finally {
            btnValidateCode.disabled = false;
            btnValidateCode.textContent = 'Validar';
        }
    }

    btnValidateCode.addEventListener('click', validateCode);
    btnStep1Next.addEventListener('click', () => showStep(2));

    document.querySelectorAll('.choice-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.choice-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            selectedType = card.dataset.type;
            document.getElementById('registration_type').value = selectedType;
            document.querySelector('[data-step="2"] .btn-next').disabled = false;
        });
    });

    if (selectedType) {
        const card = document.querySelector(`.choice-card[data-type="${selectedType}"]`);
        if (card) {
            card.classList.add('selected');
            document.querySelector('[data-step="2"] .btn-next').disabled = false;
        }
    }

    document.querySelectorAll('.btn-prev').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep === 5) {
                stopCamera();
            }
            showStep(Math.max(1, currentStep - 1));
        });
    });

    document.querySelectorAll('.btn-next').forEach(btn => {
        btn.addEventListener('click', async () => {
            const next = Number(btn.dataset.next);
            if (currentStep === 3 && !validatePersonalStep()) return;
            if (next === 4) await prepareLinkStep();
            if (next === 5 && !validateLinkStep()) return;
            if (next === 6) {
                if (!validateSelfieStep()) return;
                buildReview();
            }
            showStep(next);
        });
    });

    async function prepareLinkStep() {
        const compossuidor = selectedType === 'compossuidor';
        document.getElementById('compossuidorFields').classList.toggle('d-none', !compossuidor);
        document.getElementById('dependenteFields').classList.toggle('d-none', compossuidor);
        if (compossuidor) {
            const select = document.getElementById('unit_id');
            select.innerHTML = '<option value="">Carregando...</option>';
            const res = await fetch(`{{ route('register.units') }}?registration_code=${encodeURIComponent(codeInput.value.trim())}`);
            const data = await res.json();
            select.innerHTML = '<option value="">Selecione sua unidade</option>';
            (data.units || []).forEach(unit => {
                const opt = document.createElement('option');
                opt.value = unit.id;
                opt.textContent = unit.label;
                if (String(unit.id) === '{{ old('unit_id') }}') opt.selected = true;
                select.appendChild(opt);
            });
        }
    }

    function validateLinkStep() {
        if (selectedType === 'compossuidor') {
            if (!document.getElementById('unit_id').value) {
                alert('Selecione sua unidade.');
                return false;
            }
        } else if (!document.getElementById('morador_vinculado_id').value) {
            alert('Selecione o morador responsável.');
            return false;
        }
        return true;
    }

    document.getElementById('moradorSearch')?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const term = document.getElementById('moradorSearch').value.trim();
        const results = document.getElementById('moradorResults');
        if (term.length < 2) {
            results.innerHTML = '';
            return;
        }
        searchTimer = setTimeout(async () => {
            const res = await fetch(`{{ route('register.moradores') }}?registration_code=${encodeURIComponent(codeInput.value.trim())}&term=${encodeURIComponent(term)}`);
            const data = await res.json();
            results.innerHTML = '';
            (data.moradores || []).forEach(m => {
                const div = document.createElement('div');
                div.className = 'morador-result';
                div.innerHTML = `<strong>${m.name}</strong><div class="small text-muted">${m.cpf || ''} ${m.unit ? '• Unidade ' + m.unit : ''}</div>`;
                div.onclick = () => {
                    selectedMorador = m;
                    document.getElementById('morador_vinculado_id').value = m.id;
                    document.querySelectorAll('.morador-result').forEach(el => el.classList.remove('selected'));
                    div.classList.add('selected');
                    const box = document.getElementById('selectedMorador');
                    box.classList.remove('d-none');
                    box.innerHTML = `<i class="bi bi-check-circle me-2"></i>Selecionado: <strong>${m.name}</strong>`;
                };
                results.appendChild(div);
            });
        }, 300);
    });

    function buildReview() {
        const typeLabel = selectedType === 'compossuidor' ? 'Compossuidor (Morador)' : 'Dependente (Agregado)';
        const unitSelect = document.getElementById('unit_id');
        let linkInfo = '-';
        if (selectedType === 'compossuidor') {
            linkInfo = unitSelect.options[unitSelect.selectedIndex]?.text || '-';
        } else if (selectedMorador) {
            linkInfo = `${selectedMorador.name} (${selectedMorador.cpf || 'CPF não informado'})`;
        }
        document.getElementById('reviewBox').innerHTML = `
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <img src="${selfiePreviewUrl || ''}" alt="Selfie" class="review-photo">
                </div>
                <div class="col">
                    <div class="small text-muted">Foto de identificação capturada</div>
                </div>
            </div>
            <hr>
            <div class="row g-2">
                <div class="col-sm-6"><strong>Condomínio:</strong><br>${document.getElementById('condoName').textContent}</div>
                <div class="col-sm-6"><strong>Perfil:</strong><br>${typeLabel}</div>
                <div class="col-sm-6"><strong>Nome:</strong><br>${document.querySelector('[name=name]').value}</div>
                <div class="col-sm-6"><strong>CPF:</strong><br>${document.querySelector('[name=cpf]').value}</div>
                <div class="col-sm-6"><strong>E-mail:</strong><br>${document.querySelector('[name=email]').value}</div>
                <div class="col-sm-6"><strong>Celular:</strong><br>${document.querySelector('[name=telefone_celular]').value}</div>
                <div class="col-12"><strong>Vínculo:</strong><br>${linkInfo}</div>
            </div>`;
    }

    window.addEventListener('beforeunload', stopCamera);

    @if(old('registration_code'))
        validateCode();
    @endif
    @if($errors->any())
        showStep({{ old('registration_type') ? 3 : 1 }});
    @endif
})();
</script>
</body>
</html>
