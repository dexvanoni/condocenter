<!-- Botão para abrir leitor de QR Code de Pets -->
<div class="pet-qr-reader-wrapper">
    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#petQrReaderModal">
        <i class="bi bi-qr-code-scan"></i> Verificar Pet (QR Code)
    </button>
</div>

<!-- Modal do Leitor de QR Code -->
<div class="modal fade" id="petQrReaderModal" tabindex="-1" aria-labelledby="petQrReaderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="petQrReaderModalLabel">
                    <i class="bi bi-qr-code-scan"></i> Verificar Pet por QR Code
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Aponte a câmera para o QR Code na coleira do pet para ver suas informações e contatar o dono.
                </div>

                <!-- Seleção de Câmera -->
                <div class="mb-3">
                    <label for="cameraSelect" class="form-label">Selecionar Câmera:</label>
                    <select id="cameraSelect" class="form-select">
                        <option value="">Carregando câmeras...</option>
                    </select>
                </div>

                <!-- Container do vídeo -->
                <div id="qrReaderContainer" class="text-center mb-3">
                    <video id="qrVideo" style="width: 100%; max-width: 500px; border-radius: 10px;"></video>
                </div>

                <!-- Resultado da leitura -->
                <div id="qrResult" style="display: none;">
                    <div class="alert alert-success">
                        <h5><i class="bi bi-check-circle"></i> Pet Encontrado!</h5>
                        <div id="petInfo"></div>
                    </div>
                </div>

                <!-- Loading -->
                <div id="qrLoading" style="display: none;" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Processando...</span>
                    </div>
                    <p>Processando QR Code...</p>
                </div>

                <!-- Erro -->
                <div id="qrError" style="display: none;" class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span id="qrErrorMessage"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;
let currentCamera = null;

// Inicializar quando o modal for aberto
$('#petQrReaderModal').on('shown.bs.modal', function () {
    initQrReader();
});

// Parar câmera quando o modal for fechado
$('#petQrReaderModal').on('hidden.bs.modal', function () {
    stopQrReader();
});

// Trocar de câmera
$('#cameraSelect').on('change', function() {
    const cameraId = $(this).val();
    if (cameraId && html5QrCode) {
        stopQrReader();
        startCamera(cameraId);
    }
});

function initQrReader() {
    html5QrCode = new Html5Qrcode("qrVideo");
    
    // Resetar visualização
    $('#qrResult').hide();
    $('#qrError').hide();
    $('#qrLoading').hide();
    
    // Listar câmeras disponíveis
    Html5Qrcode.getCameras().then(cameras => {
        if (cameras && cameras.length) {
            let options = '';
            cameras.forEach((camera, index) => {
                const label = camera.label || `Câmera ${index + 1}`;
                const isBack = label.toLowerCase().includes('back') || label.toLowerCase().includes('traseira');
                const selected = isBack ? 'selected' : '';
                options += `<option value="${camera.id}" ${selected}>${label}</option>`;
            });
            $('#cameraSelect').html(options);
            
            // Iniciar com a câmera selecionada (preferencialmente traseira)
            const selectedCamera = $('#cameraSelect').val();
            startCamera(selectedCamera);
        } else {
            $('#qrError').show();
            $('#qrErrorMessage').text('Nenhuma câmera encontrada no dispositivo.');
        }
    }).catch(err => {
        console.error('Erro ao listar câmeras:', err);
        $('#qrError').show();
        $('#qrErrorMessage').text('Erro ao acessar câmeras. Verifique as permissões.');
    });
}

function startCamera(cameraId) {
    currentCamera = cameraId;
    
    const config = { 
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0
    };
    
    html5QrCode.start(
        cameraId,
        config,
        onScanSuccess,
        onScanFailure
    ).catch(err => {
        console.error('Erro ao iniciar câmera:', err);
        $('#qrError').show();
        $('#qrErrorMessage').text('Erro ao iniciar câmera: ' + err);
    });
}

function stopQrReader() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
        }).catch(err => {
            console.error('Erro ao parar scanner:', err);
        });
    }
}

function onScanSuccess(decodedText, decodedResult) {
    console.log('QR Code detectado:', decodedText);
    
    // Parar o scanner
    stopQrReader();
    
    // Mostrar loading
    $('#qrLoading').show();
    $('#qrError').hide();
    
    // Se for uma URL do nosso sistema, extrair o código
    let qrCode = decodedText;
    const urlPattern = /\/pets\/qr\/([^\/]+)/;
    const match = decodedText.match(urlPattern);
    
    if (match) {
        qrCode = match[1];
    }
    
    // Verificar o pet via AJAX
    $.ajax({
        url: '/pets/verify-qr',
        type: 'POST',
        data: {
            qr_code: qrCode,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            $('#qrLoading').hide();
            
            if (response.success) {
                displayPetInfo(response.pet);
            } else {
                $('#qrError').show();
                $('#qrErrorMessage').text(response.message || 'Pet não encontrado.');
            }
        },
        error: function(xhr) {
            $('#qrLoading').hide();
            $('#qrError').show();
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                $('#qrErrorMessage').text(xhr.responseJSON.message);
            } else {
                $('#qrErrorMessage').text('Erro ao verificar QR Code. Tente novamente.');
            }
        }
    });
}

function onScanFailure(error) {
    // Ignorar erros de não encontrar QR Code (muito comum durante a leitura)
    // console.warn('Scan error:', error);
}

function displayPetInfo(pet) {
    const petInfoHtml = `
        <div class="row">
            <div class="col-md-4 text-center">
                <img src="${pet.photo}" alt="${pet.name}" class="img-fluid rounded" style="max-height: 200px;">
            </div>
            <div class="col-md-8">
                <h4>${pet.name}</h4>
                <p>
                    <strong>Tipo:</strong> ${pet.type}<br>
                    ${pet.breed ? '<strong>Raça:</strong> ' + pet.breed + '<br>' : ''}
                    ${pet.color ? '<strong>Cor:</strong> ' + pet.color + '<br>' : ''}
                    <strong>Porte:</strong> ${pet.size}<br>
                    ${pet.description ? '<strong>Observações:</strong> ' + pet.description + '<br>' : ''}
                </p>
                <hr>
                <h5><i class="bi bi-person-circle"></i> Informações do Dono</h5>
                <p>
                    <strong>Nome:</strong> ${pet.owner.name}<br>
                    <strong>Telefone:</strong> ${pet.owner.phone}<br>
                    <strong>Unidade:</strong> ${pet.unit.identifier}
                </p>
                <a href="${pet.owner.whatsapp_link}" target="_blank" class="btn btn-success btn-lg w-100">
                    <i class="bi bi-whatsapp"></i> Contatar Dono pelo WhatsApp
                </a>
            </div>
        </div>
    `;
    
    $('#petInfo').html(petInfoHtml);
    $('#qrResult').show();
}
</script>
@endpush

@push('styles')
<style>
.pet-qr-reader-wrapper {
    margin: 20px 0;
}

#qrVideo {
    border: 3px solid #3866d2;
}
</style>
@endpush

