@extends('layouts.app')

@section('title', 'Notificações')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Central de Notificações</h2>
            <button class="btn btn-outline-primary" onclick="markAllRead()">
                <i class="bi bi-check-all"></i> Marcar Todas como Lidas
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-body p-0" id="notificationsContainer">
                <!-- Placeholder enquanto carrega -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="text-muted mt-2">Carregando notificações...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function loadNotifications() {
        fetch('/api/notifications')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('notificationsContainer');
                const notifications = data.data || data;
                
                if (notifications.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-1"></i>
                            <p class="mt-3">Nenhuma notificação</p>
                        </div>
                    `;
                    return;
                }
                
                let html = '<div class="list-group list-group-flush">';
                
                notifications.forEach(notif => {
                    const bgClass = notif.is_read ? '' : 'bg-light';
                    const icon = getNotifIcon(notif.type);
                    const date = new Date(notif.created_at).toLocaleString('pt-BR');
                    
                    html += `
                        <div class="list-group-item ${bgClass}">
                            <div class="d-flex">
                                <div class="me-3 fs-3">${icon}</div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1">${notif.title}</h6>
                                        ${!notif.is_read ? '<span class="badge bg-primary">Nova</span>' : ''}
                                    </div>
                                    <p class="mb-1">${notif.message}</p>
                                    <small class="text-muted">${date}</small>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                container.innerHTML = html;
            });
    }
    
    function getNotifIcon(type) {
        const icons = {
            'package_arrived': '📦',
            'payment_overdue': '⚠️',
            'reservation_approved': '✅',
            'panic_alert': '🚨',
        };
        return icons[type] || '📢';
    }
    
    function markAllRead() {
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        })
        .then(() => location.reload());
    }
    
    // Carregar ao iniciar
    loadNotifications();
</script>
@endpush
@endsection

