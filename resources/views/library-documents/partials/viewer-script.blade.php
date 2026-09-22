<script>
(function () {
    const payloadEl = document.getElementById('libraryDocPayload');
    const viewer = document.getElementById('docViewerText');
    const input = document.getElementById('docTextSearch');
    if (!payloadEl || !viewer || !input) {
        return;
    }

    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    let payload = { plainText: '', displayHtml: null };
    try {
        payload = JSON.parse(payloadEl.textContent || '{}');
    } catch (e) {
        return;
    }

    const plainText = (payload.plainText || '').toString();
    const baseHtml = payload.displayHtml
        ? payload.displayHtml
        : escapeHtml(plainText).replace(/\n/g, '<br>');

    viewer.dataset.baseHtml = baseHtml;
    viewer.innerHTML = baseHtml;

    const statusEl = document.getElementById('docSearchStatus');
    const countEl = document.getElementById('docSearchCount');
    const btnPrev = document.getElementById('docSearchPrev');
    const btnNext = document.getElementById('docSearchNext');

    let matchIndexes = [];
    let activeMatch = -1;
    let debounceTimer = null;

    function normalizeForSearch(str) {
        return str
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    function findMatches(query) {
        const q = normalizeForSearch(query.trim());
        if (!q || q.length < 2) {
            return [];
        }
        const hay = normalizeForSearch(plainText);
        const indexes = [];
        let pos = 0;
        while (pos < hay.length) {
            const idx = hay.indexOf(q, pos);
            if (idx === -1) {
                break;
            }
            indexes.push(idx);
            pos = idx + 1;
        }
        return indexes;
    }

    function renderHighlights(query) {
        if (!query || query.trim().length < 2) {
            viewer.innerHTML = viewer.dataset.baseHtml;
            matchIndexes = [];
            activeMatch = -1;
            updateUi();
            return;
        }

        const q = query.trim();
        const qNorm = normalizeForSearch(q);
        const hayNorm = normalizeForSearch(plainText);
        let html = '';
        let last = 0;
        let pos = 0;
        const indexes = [];

        while (pos < hayNorm.length) {
            const idx = hayNorm.indexOf(qNorm, pos);
            if (idx === -1) {
                break;
            }
            indexes.push(idx);
            html += escapeHtml(plainText.slice(last, idx)).replace(/\n/g, '<br>');
            html += '<mark class="doc-search-hit">' + escapeHtml(plainText.slice(idx, idx + q.length)) + '</mark>';
            last = idx + q.length;
            pos = idx + 1;
        }
        html += escapeHtml(plainText.slice(last)).replace(/\n/g, '<br>');
        viewer.innerHTML = html || viewer.dataset.baseHtml;
        matchIndexes = indexes;
        activeMatch = indexes.length ? 0 : -1;
        scrollToActive();
        updateUi();
    }

    function scrollToActive() {
        const marks = viewer.querySelectorAll('mark.doc-search-hit');
        if (activeMatch < 0 || !marks.length) {
            return;
        }
        const el = marks[activeMatch];
        if (el) {
            marks.forEach((m, i) => m.classList.toggle('doc-search-hit-active', i === activeMatch));
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function updateUi() {
        const total = matchIndexes.length;
        if (!input.value.trim() || input.value.trim().length < 2) {
            statusEl.textContent = 'Digite ao menos 2 caracteres para buscar.';
            countEl.textContent = '';
        } else if (total === 0) {
            statusEl.textContent = 'Nenhuma ocorrência encontrada.';
            countEl.textContent = '0 resultados';
        } else {
            statusEl.textContent = 'Use as setas para navegar entre as ocorrências.';
            countEl.textContent = (activeMatch + 1) + ' de ' + total;
        }
        const enabled = total > 0;
        btnPrev.disabled = !enabled;
        btnNext.disabled = !enabled;
    }

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            renderHighlights(input.value);
        }, 200);
    });

    btnPrev.addEventListener('click', function () {
        if (!matchIndexes.length) return;
        activeMatch = (activeMatch - 1 + matchIndexes.length) % matchIndexes.length;
        scrollToActive();
        updateUi();
    });

    btnNext.addEventListener('click', function () {
        if (!matchIndexes.length) return;
        activeMatch = (activeMatch + 1) % matchIndexes.length;
        scrollToActive();
        updateUi();
    });
})();
</script>
