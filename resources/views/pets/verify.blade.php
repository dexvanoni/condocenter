@extends('layouts.app')

@section('title', 'Verificar QR Code - Pet')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-qr-code-scan"></i> Verificar QR Code de Pet
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Como usar:</strong> Aponte a câmera para o QR Code da coleira do pet para identificá-lo.
                    </div>

                    <!-- Área de Scanner -->
                    <div id="scanner-container" class="mb-4">
                        <div id="qr-reader" style="width: 100%; border-radius: 10px; overflow: hidden;"></div>
                    </div>

                    <!-- Botões de Controle -->
                    <div class="d-flex gap-2 justify-content-center mb-4">
                        <button id="start-scan-btn" class="btn btn-success">
                            <i class="bi bi-camera-video"></i> Iniciar Scanner
                        </button>
                        <button id="stop-scan-btn" class="btn btn-danger" style="display: none;">
                            <i class="bi bi-stop-circle"></i> Parar Scanner
                        </button>
                        <a href="{{ route('pets.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar
                        </a>
                    </div>

                    <!-- Entrada Manual de QR Code -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Ou digite o código manualmente:</h6>
                            <form id="manual-qr-form" class="row g-2">
                                <div class="col-md-8">
                                    <input type="text" 
                                           class="form-control" 
                                           id="manual-qr-input" 
                                           placeholder="Cole ou digite o código QR aqui"
                                           required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Verificar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Área de Resultado -->
                    <div id="result-container" class="mt-4" style="display: none;">
                        <div class="alert alert-success" id="success-result">
                            <h5 class="alert-heading">
                                <i class="bi bi-check-circle-fill"></i> Pet Encontrado!
                            </h5>
                            <div id="pet-details"></div>
                        </div>
                    </div>

                    <!-- Área de Erro -->
                    <div id="error-container" class="mt-4" style="display: none;">
                        <div class="alert alert-danger" id="error-result">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span id="error-message"></span>
                        </div>
                    </div>

                    <!-- Status do Scanner -->
                    <div id="scanner-status" class="text-center text-muted small mt-3" style="display: none;">
                        <i class="bi bi-camera"></i> Scanner ativo - aproxime o QR Code da câmera
                    </div>
                </div>
            </div>

            <!-- Instruções -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-question-circle"></i> Instruções</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Clique em "Iniciar Scanner" para ativar a câmera</li>
                        <li>Permita o acesso à câmera quando solicitado pelo navegador</li>
                        <li>Aponte a câmera para o QR Code da coleira do pet</li>
                        <li>O sistema identificará automaticamente o pet e exibirá suas informações</li>
                        <li>Você pode contatar o dono diretamente pelo WhatsApp</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #qr-reader {
        border: 3px solid #0d6efd;
        min-height: 300px;
        position: relative;
    }

    #qr-reader video {
        width: 100% !important;
        height: auto !important;
        border-radius: 10px;
    }

    #qr-reader__dashboard {
        display: none !important;
    }

    .pet-info-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-top: 15px;
    }

    .pet-photo-result {
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .whatsapp-contact-btn {
        background: #25D366;
        color: white;
        padding: 12px 25px;
        border-radius: 50px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: bold;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
    }

    .whatsapp-contact-btn:hover {
        background: #20ba5a;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.5);
        color: white;
    }

    #scanner-status {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endpush

@push('scripts')
<!-- HTML5 QR Code Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    let html5QrCode = null;
    let isScanning = false;

    document.addEventListener('DOMContentLoaded', function() {
        const startBtn = document.getElementById('start-scan-btn');
        const stopBtn = document.getElementById('stop-scan-btn');
        const manualForm = document.getElementById('manual-qr-form');
        const scannerStatus = document.getElementById('scanner-status');

        // Iniciar Scanner
        startBtn.addEventListener('click', function() {
            startScanning();
        });

        // Parar Scanner
        stopBtn.addEventListener('click', function() {
            stopScanning();
        });

        // Verificação Manual
        manualForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const qrCode = document.getElementById('manual-qr-input').value.trim();
            if (qrCode) {
                verifyQrCode(qrCode);
            }
        });

        function startScanning() {
            if (isScanning) return;

            html5QrCode = new Html5Qrcode("qr-reader");
            
            const config = { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            html5QrCode.start(
                { facingMode: "environment" }, // Câmera traseira em dispositivos móveis
                config,
                (decodedText, decodedResult) => {
                    // QR Code detectado
                    console.log(`QR Code detectado: ${decodedText}`);
                    verifyQrCode(decodedText);
                    stopScanning(); // Para o scanner após detectar
                },
                (errorMessage) => {
                    // Erro ao escanear (normal durante o processo)
                    // console.log(errorMessage);
                }
            ).then(() => {
                isScanning = true;
                startBtn.style.display = 'none';
                stopBtn.style.display = 'inline-block';
                scannerStatus.style.display = 'block';
                hideMessages();
            }).catch((err) => {
                console.error(`Erro ao iniciar scanner: ${err}`);
                showError(`Erro ao acessar câmera: ${err}. Verifique as permissões do navegador.`);
            });
        }

        function stopScanning() {
            if (!isScanning || !html5QrCode) return;

            html5QrCode.stop().then(() => {
                isScanning = false;
                startBtn.style.display = 'inline-block';
                stopBtn.style.display = 'none';
                scannerStatus.style.display = 'none';
                html5QrCode = null;
            }).catch((err) => {
                console.error(`Erro ao parar scanner: ${err}`);
            });
        }

        function verifyQrCode(qrCode) {
            // Limpar mensagens anteriores
            hideMessages();

            // Mostrar loading
            showLoading();

            // Fazer requisição AJAX
            fetch('{{ route('pets.verify-qr') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ qr_code: qrCode })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success && data.pet) {
                    showPetDetails(data.pet);
                } else {
                    showError(data.message || 'Pet não encontrado. Verifique o código QR.');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Erro:', error);
                showError('Erro ao verificar QR Code. Tente novamente.');
            });
        }

        function showPetDetails(pet) {
            const resultContainer = document.getElementById('result-container');
            const petDetails = document.getElementById('pet-details');

            let html = `
                <div class="pet-info-card">
                    <img src="${pet.photo}" alt="${pet.name}" class="pet-photo-result">
                    
                    <h4 class="mb-3">
                        <i class="bi bi-hearts"></i> ${pet.name}
                        <span class="badge bg-primary ms-2">${pet.type}</span>
                        <span class="badge bg-info ms-1">${pet.size}</span>
                    </h4>
            `;

            if (pet.breed) {
                html += `<p class="mb-2"><strong>Raça:</strong> ${pet.breed}</p>`;
            }

            if (pet.color) {
                html += `<p class="mb-2"><strong>Cor:</strong> ${pet.color}</p>`;
            }

            if (pet.description) {
                html += `<p class="mb-2"><strong>Descrição:</strong> ${pet.description}</p>`;
            }

            html += `
                    <hr>
                    <h5 class="mb-3"><i class="bi bi-person-circle"></i> Informações do Dono</h5>
                    <p class="mb-2"><strong>Nome:</strong> ${pet.owner.name}</p>
                    <p class="mb-2"><strong>Telefone:</strong> ${pet.owner.phone}</p>
                    <p class="mb-2"><strong>Unidade:</strong> ${pet.unit.identifier}</p>
            `;

            if (pet.condominium) {
                html += `<p class="mb-2"><strong>Condomínio:</strong> ${pet.condominium.name}</p>`;
            }

            html += `
                    <div class="text-center mt-4">
                        <a href="${pet.owner.whatsapp_link}" 
                           class="whatsapp-contact-btn" 
                           target="_blank">
                            <i class="bi bi-whatsapp"></i>
                            Contatar Dono pelo WhatsApp
                        </a>
                    </div>
                </div>
            `;

            petDetails.innerHTML = html;
            resultContainer.style.display = 'block';

            // Rolar suavemente até o resultado
            resultContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function showError(message) {
            const errorContainer = document.getElementById('error-container');
            const errorMessage = document.getElementById('error-message');
            
            errorMessage.textContent = message;
            errorContainer.style.display = 'block';

            // Esconder após 5 segundos
            setTimeout(() => {
                errorContainer.style.display = 'none';
            }, 5000);
        }

        function hideMessages() {
            document.getElementById('result-container').style.display = 'none';
            document.getElementById('error-container').style.display = 'none';
        }

        function showLoading() {
            const resultContainer = document.getElementById('result-container');
            const petDetails = document.getElementById('pet-details');
            
            petDetails.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-3">Verificando QR Code...</p>
                </div>
            `;
            resultContainer.style.display = 'block';
        }

        function hideLoading() {
            // Função auxiliar - o conteúdo será substituído
        }

        // Limpar ao sair da página
        window.addEventListener('beforeunload', function() {
            if (isScanning) {
                stopScanning();
            }
        });
    });
</script>
@endpush
@endsection

