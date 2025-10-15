@extends('layouts.app')

@section('title', 'Novo Espaço')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Cadastrar Novo Espaço</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('spaces.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nome do Espaço *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name') }}" required
                               placeholder="Ex: Churrasqueira 1, Salão de Festas">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="3"
                                  placeholder="Descreva o espaço, comodidades, etc">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Upload de Foto -->
                    <div class="mb-3">
                        <label class="form-label">Foto do Espaço</label>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                               name="photo" id="photoInput" accept="image/*" onchange="previewPhoto()">
                        <small class="text-muted">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 5MB</small>
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <!-- Preview da foto -->
                        <div id="photoPreview" class="mt-3" style="display: none;">
                            <img id="previewImage" src="" alt="Preview" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removePhoto()">
                                <i class="bi bi-trash"></i> Remover Foto
                            </button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                                    <option value="">Selecione...</option>
                                    <option value="bbq" {{ old('type') == 'bbq' ? 'selected' : '' }}>Churrasqueira</option>
                                    <option value="party_hall" {{ old('type') == 'party_hall' ? 'selected' : '' }}>Salão de Festas</option>
                                    <option value="pool" {{ old('type') == 'pool' ? 'selected' : '' }}>Piscina</option>
                                    <option value="sports_court" {{ old('type') == 'sports_court' ? 'selected' : '' }}>Quadra Poliesportiva</option>
                                    <option value="gym" {{ old('type') == 'gym' ? 'selected' : '' }}>Academia</option>
                                    <option value="meeting_room" {{ old('type') == 'meeting_room' ? 'selected' : '' }}>Sala de Reunião</option>
                                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Outro</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Capacidade (pessoas)</label>
                                <input type="number" class="form-control @error('capacity') is-invalid @enderror" 
                                       name="capacity" value="{{ old('capacity') }}" min="1"
                                       placeholder="Ex: 20">
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Modo de Reserva *</label>
                                <select class="form-select @error('reservation_mode') is-invalid @enderror" 
                                        name="reservation_mode" required id="reservationMode" onchange="toggleReservationMode()">
                                    <option value="full_day" {{ old('reservation_mode', 'full_day') == 'full_day' ? 'selected' : '' }}>
                                        📅 Dia Inteiro (1 reserva por dia)
                                    </option>
                                    <option value="hourly" {{ old('reservation_mode') == 'hourly' ? 'selected' : '' }}>
                                        ⏰ Por Horário (múltiplas reservas por dia)
                                    </option>
                                </select>
                                <small class="text-muted">
                                    Dia Inteiro: Uma pessoa reserva o dia todo | Por Horário: Várias pessoas podem reservar horários diferentes no mesmo dia
                                </small>
                                @error('reservation_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row" id="hourlySettings" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Duração Mínima (horas)</label>
                                <input type="number" class="form-control" name="min_hours_per_reservation" 
                                       value="{{ old('min_hours_per_reservation', 1) }}" min="1" step="0.5">
                                <small class="text-muted">Ex: 1 hora mínima</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Duração Máxima (horas)</label>
                                <input type="number" class="form-control" name="max_hours_per_reservation" 
                                       value="{{ old('max_hours_per_reservation', 4) }}" min="1">
                                <small class="text-muted">Ex: 4 horas máximas</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Taxa de Reserva (R$) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" class="form-control @error('price_per_reservation') is-invalid @enderror" 
                                           name="price_per_reservation" value="{{ old('price_per_reservation', 0) }}" 
                                           step="0.01" min="0" required>
                                </div>
                                <small class="text-muted">Digite 0 se for gratuito</small>
                                @error('price_per_reservation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Limite de Reservas por Mês *</label>
                                <input type="number" class="form-control @error('max_reservations_per_month_per_user') is-invalid @enderror" 
                                       name="max_reservations_per_month_per_user" 
                                       value="{{ old('max_reservations_per_month_per_user', 1) }}" 
                                       min="1" required>
                                <small class="text-muted">Por usuário</small>
                                @error('max_reservations_per_month_per_user')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Disponível das *</label>
                                <input type="time" class="form-control @error('available_from') is-invalid @enderror" 
                                       name="available_from" value="{{ old('available_from', '08:00') }}" required>
                                @error('available_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Até *</label>
                                <input type="time" class="form-control @error('available_until') is-invalid @enderror" 
                                       name="available_until" value="{{ old('available_until', '22:00') }}" required>
                                @error('available_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Regras de Uso</label>
                        <textarea class="form-control @error('rules') is-invalid @enderror" 
                                  name="rules" rows="4"
                                  placeholder="Ex: Proibido som alto após 22h, limite de 50 pessoas, etc">{{ old('rules') }}</textarea>
                        @error('rules')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Configurações de Aprovação -->
                    <div class="card bg-light mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bi bi-gear"></i> Configurações de Aprovação</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Aprovação *</label>
                                <select class="form-select @error('approval_type') is-invalid @enderror" 
                                        name="approval_type" required id="approvalType" onchange="toggleApprovalType()">
                                    <option value="automatic" {{ old('approval_type', 'automatic') == 'automatic' ? 'selected' : '' }}>
                                        ✅ Aprovação Automática
                                    </option>
                                    <option value="manual" {{ old('approval_type') == 'manual' ? 'selected' : '' }}>
                                        👤 Aprovação Manual (Síndico)
                                    </option>
                                    <option value="prereservation" {{ old('approval_type') == 'prereservation' ? 'selected' : '' }}>
                                        💳 Pré-reserva com Pagamento
                                    </option>
                                </select>
                                <small class="text-muted">
                                    <strong>Automática:</strong> Reserva aprovada imediatamente<br>
                                    <strong>Manual:</strong> Síndico precisa aprovar cada reserva<br>
                                    <strong>Pré-reserva:</strong> Usuário paga para confirmar a reserva
                                </small>
                                @error('approval_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Configurações de Pré-reserva -->
                            <div id="prereservationSettings" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Prazo para Pagamento *</label>
                                            <select class="form-select @error('prereservation_payment_hours') is-invalid @enderror" 
                                                    name="prereservation_payment_hours" id="paymentHours">
                                                <option value="24" {{ old('prereservation_payment_hours', '24') == '24' ? 'selected' : '' }}>
                                                    🕐 24 horas
                                                </option>
                                                <option value="48" {{ old('prereservation_payment_hours') == '48' ? 'selected' : '' }}>
                                                    🕐 48 horas
                                                </option>
                                                <option value="72" {{ old('prereservation_payment_hours') == '72' ? 'selected' : '' }}>
                                                    🕐 72 horas
                                                </option>
                                            </select>
                                            <small class="text-muted">Tempo que o usuário tem para pagar após fazer a pré-reserva</small>
                                            @error('prereservation_payment_hours')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" name="prereservation_auto_cancel" 
                                                       value="1" id="autoCancel" {{ old('prereservation_auto_cancel', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="autoCancel">
                                                    <strong>Cancelar automaticamente</strong> se não pagar
                                                </label>
                                            </div>
                                            <small class="text-muted">Se marcado, a pré-reserva será cancelada automaticamente após o prazo</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Instruções de Pagamento</label>
                                    <textarea class="form-control @error('prereservation_instructions') is-invalid @enderror" 
                                              name="prereservation_instructions" rows="3"
                                              placeholder="Ex: PIX: condominio@email.com, Boleto disponível no app, etc">{{ old('prereservation_instructions') }}</textarea>
                                    <small class="text-muted">Instruções que aparecerão para o usuário sobre como realizar o pagamento</small>
                                    @error('prereservation_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Atenção:</strong> Com pré-reserva ativada, o usuário terá que pagar o valor do espaço para confirmar a reserva. 
                                    Se não pagar no prazo estipulado, a vaga será liberada para outros moradores.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info" id="approvalInfo">
                        <i class="bi bi-info-circle"></i>
                        <strong>Informações sobre o tipo de aprovação:</strong>
                        <div id="automaticInfo" style="display: none;">
                        <ul class="mb-0">
                            <li>As reservas serão aprovadas <strong>automaticamente</strong></li>
                            <li>Se houver taxa, será gerada cobrança via <strong>Asaas (PIX/Cartão)</strong></li>
                        </ul>
                        </div>
                        <div id="manualInfo" style="display: none;">
                            <ul class="mb-0">
                                <li>As reservas precisarão ser <strong>aprovadas manualmente</strong> pelo síndico</li>
                                <li>O morador receberá notificação quando aprovado/rejeitado</li>
                                <li>Se houver taxa, será gerada cobrança via <strong>Asaas (PIX/Cartão)</strong> após aprovação</li>
                            </ul>
                        </div>
                        <div id="prereservationInfo" style="display: none;">
                            <ul class="mb-0">
                                <li>O morador fará uma <strong>pré-reserva</strong> e terá <strong id="paymentDeadlineText">24 horas</strong> para pagar</li>
                                <li>Após o pagamento, a reserva será <strong>confirmada automaticamente</strong></li>
                                <li>Se não pagar no prazo, a vaga será <strong>liberada para outros</strong></li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                            <i class="bi bi-check-circle"></i> Cadastrar Espaço
                        </button>
                        <a href="{{ route('spaces.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Coluna lateral para balanceamento -->
    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-header bg-info text-white py-3">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informações</h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <h6 class="text-primary">💡 Dicas para um bom cadastro:</h6>
                    <ul class="small mb-0">
                        <li>Use nomes claros e descritivos</li>
                        <li>Defina horários realistas de funcionamento</li>
                        <li>Estabeleça regras claras de uso</li>
                        <li>Configure o tipo de aprovação adequado</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-success">✅ Tipos de Aprovação:</h6>
                    <ul class="small mb-0">
                        <li><strong>Automática:</strong> Aprova imediatamente</li>
                        <li><strong>Manual:</strong> Síndico aprova cada reserva</li>
                        <li><strong>Pré-reserva:</strong> Usuário paga para confirmar</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning py-2 mb-0 small">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Importante:</strong> Após criar o espaço, você poderá editá-lo a qualquer momento.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleReservationMode() {
        const mode = document.getElementById('reservationMode').value;
        const hourlySettings = document.getElementById('hourlySettings');
        
        if (mode === 'hourly') {
            hourlySettings.style.display = 'block';
        } else {
            hourlySettings.style.display = 'none';
        }
    }

    function toggleApprovalType() {
        const approvalType = document.getElementById('approvalType').value;
        const prereservationSettings = document.getElementById('prereservationSettings');
        const paymentHours = document.getElementById('paymentHours');
        
        // Esconder todas as informações primeiro
        document.getElementById('automaticInfo').style.display = 'none';
        document.getElementById('manualInfo').style.display = 'none';
        document.getElementById('prereservationInfo').style.display = 'none';
        
        if (approvalType === 'prereservation') {
            prereservationSettings.style.display = 'block';
            document.getElementById('prereservationInfo').style.display = 'block';
            updatePaymentDeadlineText();
        } else {
            prereservationSettings.style.display = 'none';
            if (approvalType === 'automatic') {
                document.getElementById('automaticInfo').style.display = 'block';
            } else if (approvalType === 'manual') {
                document.getElementById('manualInfo').style.display = 'block';
            }
        }
    }

    function updatePaymentDeadlineText() {
        const hours = document.getElementById('paymentHours').value;
        const textElement = document.getElementById('paymentDeadlineText');
        
        if (hours === '24') {
            textElement.textContent = '24 horas';
        } else if (hours === '48') {
            textElement.textContent = '48 horas';
        } else if (hours === '72') {
            textElement.textContent = '72 horas';
        }
    }
    
    // Inicializar ao carregar
    document.addEventListener('DOMContentLoaded', () => {
        toggleReservationMode();
        toggleApprovalType();
        
        // Adicionar listener para mudança no prazo de pagamento
        document.getElementById('paymentHours').addEventListener('change', updatePaymentDeadlineText);
    });

    // Funções para upload de foto
    function previewPhoto() {
        const input = document.getElementById('photoInput');
        const preview = document.getElementById('photoPreview');
        const previewImage = document.getElementById('previewImage');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                preview.style.display = 'block';
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removePhoto() {
        const input = document.getElementById('photoInput');
        const preview = document.getElementById('photoPreview');
        
        input.value = '';
        preview.style.display = 'none';
    }
</script>
@endpush
@endsection

