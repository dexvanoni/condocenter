@extends('layouts.app')

@section('title', 'Caronas')

@section('content')
@php
    $currentUser = auth()->user();
    $canOffer = \App\Helpers\SidebarHelper::canOfferRides($currentUser);
    $canBook = \App\Helpers\SidebarHelper::canBookRides($currentUser);
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h1 class="mb-1"><i class="bi bi-car-front"></i> Caronas</h1>
        <p class="text-muted mb-0">Ofereça ou encontre caronas dentro do condomínio.</p>
    </div>
    @if($canOffer)
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRideModal">
        <i class="bi bi-plus-circle"></i> Oferecer carona
    </button>
    @endif
</div>

<div id="ridesAlert"></div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Filtrar</label>
                <select id="ridesFilter" class="form-select">
                    <option value="all">Caronas disponíveis</option>
                    <option value="mine">Minhas caronas</option>
                    <option value="bookings">Onde reservei</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-outline-primary" id="btnReloadRides">
                    <i class="bi bi-arrow-clockwise"></i> Atualizar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Destino</th>
                        <th>Partida</th>
                        <th>Motorista</th>
                        <th>Vagas</th>
                        <th>Retorno</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th width="160">Ações</th>
                    </tr>
                </thead>
                <tbody id="ridesTableBody">
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <div class="spinner-border spinner-border-sm me-2"></div> Carregando caronas...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($canOffer)
<div class="modal fade" id="createRideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-car-front me-2"></i>Oferecer carona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createRideForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Destino *</label>
                            <input type="text" name="destination" class="form-control" required maxlength="255" placeholder="Ex.: Shopping Center, Centro de São Paulo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Vagas no veículo *</label>
                            <input type="number" name="seats_total" class="form-control" min="1" max="8" value="3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Data/hora de partida *</label>
                            <input type="datetime-local" name="departure_at" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo</label>
                            <div class="d-flex gap-3 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_free" id="rideFree" value="1" checked>
                                    <label class="form-check-label" for="rideFree">Grátis</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="is_free" id="ridePaid" value="0">
                                    <label class="form-check-label" for="ridePaid">Paga</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-none" id="priceWrap">
                            <label class="form-label fw-semibold">Valor por lugar (R$)</label>
                            <input type="number" name="price_per_seat" class="form-control" min="0" step="0.01" placeholder="0,00">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="has_return" value="1" id="hasReturn">
                                <label class="form-check-label" for="hasReturn">Previsão de retorno</label>
                            </div>
                        </div>
                        <div class="col-md-4 d-none" id="returnWrap">
                            <label class="form-label fw-semibold">Retorno previsto</label>
                            <input type="datetime-local" name="return_at" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observações</label>
                            <textarea name="notes" class="form-control" rows="2" maxlength="1000" placeholder="Ponto de encontro, bagagem, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Publicar carona</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($canBook)
<div class="modal fade" id="bookRideModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-check me-2"></i>Reservar carona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bookRideForm">
                <div class="modal-body">
                    <input type="hidden" id="bookRideId">
                    <p class="mb-2"><strong id="bookRideDestination"></strong></p>
                    <p class="text-muted small mb-3" id="bookRideMeta"></p>
                    <label class="form-label fw-semibold">Quantos lugares você vai usar? *</label>
                    <input type="number" id="bookSeats" class="form-control" min="1" max="8" value="1" required>
                    <div class="form-text">A reserva é confirmada na hora. A carona fica indisponível quando as vagas acabarem.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Confirmar reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const currentUserId = Number(@json(auth()->id()));
    const canOffer = @json($canOffer);
    const canBook = @json($canBook);

    const ridesTableBody = document.getElementById('ridesTableBody');
    const ridesFilter = document.getElementById('ridesFilter');
    const ridesAlert = document.getElementById('ridesAlert');
    const createRideForm = document.getElementById('createRideForm');
    const bookRideForm = document.getElementById('bookRideForm');
    const createRideModal = document.getElementById('createRideModal');
    const bookRideModal = document.getElementById('bookRideModal');

    let ridesCache = [];

    function showAlert(message, type = 'success') {
        ridesAlert.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
    }

    function formatDateTime(value) {
        if (!value) return '-';
        return new Date(value).toLocaleString('pt-BR', {
            day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
        });
    }

    function statusBadge(ride) {
        const map = {
            open: ['success', 'Disponível'],
            full: ['secondary', 'Lotada'],
            cancelled: ['danger', 'Cancelada'],
            completed: ['dark', 'Concluída'],
        };
        const [cls, label] = map[ride.status] || ['secondary', ride.status];
        return `<span class="badge bg-${cls}">${label}</span>`;
    }

    function priceLabel(ride) {
        if (ride.is_free) return '<span class="badge bg-success">Grátis</span>';
        const price = Number(ride.price_per_seat || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        return `<span class="badge bg-warning text-dark">Paga · ${price}/lugar</span>`;
    }

    function myBooking(ride) {
        return (ride.active_bookings || []).find(b => Number(b.passenger_id) === currentUserId);
    }

    function renderRides(rides) {
        if (!rides.length) {
            ridesTableBody.innerHTML = `
                <tr><td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-car-front fs-1 d-block mb-2"></i>
                    Nenhuma carona encontrada.
                </td></tr>`;
            return;
        }

        ridesTableBody.innerHTML = rides.map(ride => {
            const isDriver = Number(ride.driver_id) === currentUserId;
            const booking = myBooking(ride);
            const canReserve = canBook && ride.status === 'open' && ride.seats_available > 0 && !isDriver && !booking;
            const actions = [];

            if (canReserve) {
                actions.push(`<button type="button" class="btn btn-sm btn-success btn-book" data-id="${ride.id}"><i class="bi bi-check2"></i> Reservar</button>`);
            }
            if (booking) {
                actions.push(`<button type="button" class="btn btn-sm btn-outline-danger btn-cancel-booking" data-id="${booking.id}"><i class="bi bi-x"></i> Cancelar (${booking.seats_booked})</button>`);
            }
            if (isDriver && ride.status !== 'cancelled') {
                actions.push(`<button type="button" class="btn btn-sm btn-outline-danger btn-cancel-ride" data-id="${ride.id}"><i class="bi bi-trash"></i></button>`);
            }

            const passengers = (ride.active_bookings || [])
                .map(b => `${escapeHtml(b.passenger?.name || 'Morador')} (${b.seats_booked})`)
                .join(', ');

            return `
                <tr>
                    <td>
                        <strong>${escapeHtml(ride.destination)}</strong>
                        ${ride.notes ? `<br><small class="text-muted">${escapeHtml(ride.notes)}</small>` : ''}
                        ${passengers ? `<br><small class="text-primary">Passageiros: ${passengers}</small>` : ''}
                    </td>
                    <td>${formatDateTime(ride.departure_at)}</td>
                    <td>${escapeHtml(ride.driver?.name || '-')}</td>
                    <td><span class="badge bg-light text-dark">${ride.seats_available}/${ride.seats_total}</span></td>
                    <td>${ride.has_return ? (ride.return_at ? formatDateTime(ride.return_at) : 'Sim') : 'Não'}</td>
                    <td>${priceLabel(ride)}</td>
                    <td>${statusBadge(ride)}</td>
                    <td><div class="d-flex flex-wrap gap-1">${actions.join('') || '-'}</div></td>
                </tr>`;
        }).join('');
    }

    function escapeHtml(str) {
        return (str ?? '').replace(/[&<>"']/g, m => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        })[m]);
    }

    function openRideFromQuery() {
        const rideId = new URLSearchParams(window.location.search).get('carona');
        if (!rideId || !bookRideModal) {
            return;
        }

        const ride = ridesCache.find((entry) => Number(entry.id) === Number(rideId));
        if (!ride) {
            return;
        }

        document.getElementById('bookRideId').value = ride.id;
        document.getElementById('bookRideDestination').textContent = ride.destination;
        document.getElementById('bookRideMeta').textContent =
            `${formatDateTime(ride.departure_at)} · ${ride.seats_available} vaga(s) · ${ride.driver?.name || ''}`;
        document.getElementById('bookSeats').max = ride.seats_available;
        document.getElementById('bookSeats').value = 1;
        bootstrap.Modal.getOrCreateInstance(bookRideModal).show();
    }

    async function loadRides() {
        ridesTableBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2"></div> Carregando...</td></tr>`;
        const url = new URL('/api/rides', window.location.origin);
        const filter = ridesFilter.value;
        if (filter === 'mine') url.searchParams.set('scope', 'mine');
        else if (filter === 'bookings') url.searchParams.set('scope', 'bookings');
        else url.searchParams.set('available_only', '0');

        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });

        if (!res.ok) {
            ridesTableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Erro ao carregar caronas.</td></tr>`;
            return;
        }

        const data = await res.json();
        ridesCache = data.data || [];
        renderRides(ridesCache);
        openRideFromQuery();
    }

    ridesTableBody.addEventListener('click', (e) => {
        const bookBtn = e.target.closest('.btn-book');
        if (bookBtn) {
            const ride = ridesCache.find(r => Number(r.id) === Number(bookBtn.dataset.id));
            if (!ride || !bookRideModal) return;
            document.getElementById('bookRideId').value = ride.id;
            document.getElementById('bookRideDestination').textContent = ride.destination;
            document.getElementById('bookRideMeta').textContent =
                `${formatDateTime(ride.departure_at)} · ${ride.seats_available} vaga(s) · ${ride.driver?.name || ''}`;
            document.getElementById('bookSeats').max = ride.seats_available;
            document.getElementById('bookSeats').value = 1;
            bootstrap.Modal.getOrCreateInstance(bookRideModal).show();
            return;
        }

        const cancelBookingBtn = e.target.closest('.btn-cancel-booking');
        if (cancelBookingBtn) {
            cancelBooking(cancelBookingBtn.dataset.id);
            return;
        }

        const cancelRideBtn = e.target.closest('.btn-cancel-ride');
        if (cancelRideBtn) {
            cancelRide(cancelRideBtn.dataset.id);
        }
    });

    async function cancelBooking(id) {
        if (!confirm('Cancelar sua reserva nesta carona?')) return;
        const res = await fetch(`/api/ride-bookings/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            showAlert(json.error || 'Erro ao cancelar reserva.', 'danger');
            return;
        }
        showAlert(json.message || 'Reserva cancelada.');
        loadRides();
    }

    async function cancelRide(id) {
        if (!confirm('Cancelar esta carona? Todas as reservas serão notificadas.')) return;
        const res = await fetch(`/api/rides/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            showAlert(json.error || 'Erro ao cancelar carona.', 'danger');
            return;
        }
        showAlert(json.message || 'Carona cancelada.');
        loadRides();
    }

    document.getElementById('btnReloadRides')?.addEventListener('click', loadRides);
    ridesFilter?.addEventListener('change', loadRides);

    createRideForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(createRideForm);
        const payload = {
            destination: fd.get('destination'),
            departure_at: fd.get('departure_at'),
            seats_total: Number(fd.get('seats_total')),
            has_return: fd.get('has_return') === '1',
            return_at: fd.get('has_return') === '1' ? fd.get('return_at') : null,
            is_free: fd.get('is_free') === '1',
            price_per_seat: fd.get('is_free') === '1' ? null : fd.get('price_per_seat'),
            notes: fd.get('notes') || null,
        };

        const res = await fetch('/api/rides', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin',
        });

        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            const err = json.error || Object.values(json.errors || {})[0]?.[0] || 'Erro ao publicar carona.';
            showAlert(err, 'danger');
            return;
        }

        bootstrap.Modal.getInstance(createRideModal)?.hide();
        createRideForm.reset();
        showAlert('Carona publicada com sucesso!');
        loadRides();
    });

    bookRideForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const rideId = document.getElementById('bookRideId').value;
        const seats = Number(document.getElementById('bookSeats').value);

        const res = await fetch(`/api/rides/${rideId}/bookings`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ seats_booked: seats }),
            credentials: 'same-origin',
        });

        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            showAlert(json.error || 'Erro ao reservar carona.', 'danger');
            return;
        }

        bootstrap.Modal.getInstance(bookRideModal)?.hide();
        showAlert(json.message || 'Reserva confirmada!');
        loadRides();
    });

    document.querySelectorAll('input[name="is_free"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.getElementById('priceWrap')?.classList.toggle('d-none', document.getElementById('rideFree')?.checked);
        });
    });

    document.getElementById('hasReturn')?.addEventListener('change', (e) => {
        document.getElementById('returnWrap')?.classList.toggle('d-none', !e.target.checked);
    });

    loadRides();
})();
</script>
@endpush
@endsection
