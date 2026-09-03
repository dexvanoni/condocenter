@php
    $targetInputId = $targetInputId ?? null;
    $listRoute = $listRoute ?? '';
@endphp

<div class="card shadow-sm mb-4" id="whatsappGroupsCard">
    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0"><i class="bi bi-people text-success"></i> Grupos WhatsApp da instância</h5>
        <button type="button" class="btn btn-sm btn-outline-success" id="btnListWhatsAppGroups">
            <i class="bi bi-arrow-repeat"></i> Listar grupos
        </button>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Busca na Evolution API os grupos em que o número conectado participa.
            @if($targetInputId)
                Clique em <strong>Usar</strong> para preencher o ID do grupo de avisos.
            @else
                Copie o ID (<code>@g.us</code>) para usar em integrações.
            @endif
        </p>

        <div id="whatsappGroupsAlert" class="alert d-none small mb-3" role="alert"></div>

        <div id="whatsappGroupsLoading" class="text-center text-muted py-4 d-none">
            <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
            Consultando grupos na Evolution API...
        </div>

        <div id="whatsappGroupsEmpty" class="text-center text-muted py-4 d-none">
            <i class="bi bi-chat-left-dots display-6 d-block mb-2"></i>
            <p class="mb-0">Nenhum grupo listado ainda.</p>
            <p class="small">Clique em <strong>Listar grupos</strong> após conectar a instância.</p>
        </div>

        <div class="table-responsive d-none" id="whatsappGroupsTableWrap">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome do grupo</th>
                        <th class="text-center">Membros</th>
                        <th>ID (JID)</th>
                        <th class="text-end" style="width: 140px;">Ações</th>
                    </tr>
                </thead>
                <tbody id="whatsappGroupsTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    const initWhatsAppGroupsPicker = (config) => {
        const btn = document.getElementById('btnListWhatsAppGroups');
        const alertEl = document.getElementById('whatsappGroupsAlert');
        const loadingEl = document.getElementById('whatsappGroupsLoading');
        const emptyEl = document.getElementById('whatsappGroupsEmpty');
        const tableWrap = document.getElementById('whatsappGroupsTableWrap');
        const tableBody = document.getElementById('whatsappGroupsTableBody');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        if (!btn || !config?.listRoute) {
            return;
        }

        const showAlert = (message, type = 'info') => {
            alertEl.className = `alert alert-${type} small mb-3`;
            alertEl.textContent = message;
            alertEl.classList.remove('d-none');
        };

        const hideAlert = () => alertEl.classList.add('d-none');

        const setLoading = (loading) => {
            btn.disabled = loading;
            loadingEl.classList.toggle('d-none', !loading);
        };

        const renderGroups = (groups) => {
            tableBody.innerHTML = '';

            if (!groups?.length) {
                tableWrap.classList.add('d-none');
                emptyEl.classList.remove('d-none');
                return;
            }

            emptyEl.classList.add('d-none');
            tableWrap.classList.remove('d-none');

            groups.forEach((group) => {
                const row = document.createElement('tr');

                const nameCell = document.createElement('td');
                nameCell.innerHTML = `<span class="fw-semibold">${escapeHtml(group.name)}</span>`;

                const sizeCell = document.createElement('td');
                sizeCell.className = 'text-center';
                sizeCell.textContent = group.participants ?? '—';

                const idCell = document.createElement('td');
                idCell.innerHTML = `<code class="small user-select-all">${escapeHtml(group.id)}</code>`;

                const actionsCell = document.createElement('td');
                actionsCell.className = 'text-end';

                const copyBtn = document.createElement('button');
                copyBtn.type = 'button';
                copyBtn.className = 'btn btn-sm btn-outline-secondary me-1';
                copyBtn.innerHTML = '<i class="bi bi-clipboard"></i>';
                copyBtn.title = 'Copiar ID';
                copyBtn.addEventListener('click', () => copyText(group.id, copyBtn));

                actionsCell.appendChild(copyBtn);

                if (config.targetInputId) {
                    const useBtn = document.createElement('button');
                    useBtn.type = 'button';
                    useBtn.className = 'btn btn-sm btn-success';
                    useBtn.textContent = 'Usar';
                    useBtn.title = 'Preencher campo do grupo de avisos';
                    useBtn.addEventListener('click', () => {
                        const input = document.getElementById(config.targetInputId);
                        if (input) {
                            input.value = group.id;
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                            showAlert(`Grupo "${group.name}" selecionado. Salve as configurações para aplicar.`, 'success');
                        }
                    });
                    actionsCell.appendChild(useBtn);
                }

                row.append(nameCell, sizeCell, idCell, actionsCell);
                tableBody.appendChild(row);
            });
        };

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');

        const copyText = async (text, button) => {
            try {
                await navigator.clipboard.writeText(text);
                const original = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check2"></i>';
                setTimeout(() => { button.innerHTML = original; }, 1500);
            } catch (e) {
                showAlert('Não foi possível copiar. Selecione o ID manualmente.', 'warning');
            }
        };

        const readFormCredentials = () => ({
            api_url: document.querySelector('[name="api_url"]')?.value || '',
            instance: document.querySelector('[name="instance"]')?.value || '',
            api_key: document.querySelector('[name="api_key"]')?.value || '',
        });

        btn.addEventListener('click', async () => {
            hideAlert();
            setLoading(true);

            try {
                const res = await fetch(config.listRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(readFormCredentials()),
                });

                const data = await res.json();

                if (!data.ok) {
                    renderGroups([]);
                    showAlert(data.message || 'Não foi possível listar os grupos.', 'danger');
                    return;
                }

                renderGroups(data.groups || []);
                showAlert(data.message, data.groups?.length ? 'success' : 'warning');
            } catch (e) {
                renderGroups([]);
                showAlert('Erro: ' + e.message, 'danger');
            } finally {
                setLoading(false);
            }
        });

        emptyEl.classList.remove('d-none');
    };

    window.initWhatsAppGroupsPicker = initWhatsAppGroupsPicker;
})();
</script>
@endpush
@endonce
