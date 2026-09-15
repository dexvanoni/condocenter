<template>
  <div class="match-result">
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <template v-if="level === 'high'">
      <div class="alert alert-success">
        <strong>MORADOR IDENTIFICADO</strong>
        <div class="fs-5 mt-2">👤 {{ selected?.name }}</div>
        <div class="text-muted">🏢 {{ selected?.unit_label || `${selected?.block} — Apt. ${selected?.number}` }}</div>
        <div v-if="confidencePct" class="small mt-1">Confiança: {{ confidencePct }}%</div>
      </div>
    </template>

    <template v-else-if="level === 'medium'">
      <h5 class="mb-3">Encontramos possíveis destinatários</h5>
      <div class="list-group mb-3">
        <label
          v-for="c in candidates"
          :key="c.resident_id"
          class="list-group-item list-group-item-action"
        >
          <input
            type="radio"
            class="form-check-input me-2"
            :value="c.resident_id"
            :checked="selected?.resident_id === c.resident_id"
            @change="$emit('update:selectedCandidate', c)"
          >
          {{ c.name }} — {{ c.block }}/{{ c.number }}
          <span class="badge bg-secondary float-end">{{ Math.round(c.confidence * 100) }}%</span>
        </label>
      </div>
    </template>

    <template v-else>
      <div class="alert alert-warning">
        <strong>Não foi possível identificar o destinatário.</strong>
        <p class="mb-0 small mt-1">Tente novamente ou registre manualmente.</p>
      </div>
      <div class="d-grid gap-2 mb-3">
        <button class="btn btn-primary" @click="$emit('retry')">TENTAR NOVAMENTE</button>
        <button class="btn btn-outline-secondary" @click="$emit('manual')">REGISTRAR MANUALMENTE</button>
        <button class="btn btn-link" @click="$emit('back')">Voltar</button>
      </div>
    </template>

    <template v-if="level === 'high' || level === 'medium'">
      <div class="mb-3">
        <label class="form-label fw-semibold">📦 Remetente</label>
        <input
          type="text"
          class="form-control form-control-lg"
          :value="sender"
          placeholder="Remetente não identificado"
          @input="$emit('update:sender', $event.target.value)"
        >
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Tipo</label>
        <select
          class="form-select form-select-lg"
          :value="packageType"
          @change="$emit('update:packageType', $event.target.value)"
        >
          <option value="leve">Leve</option>
          <option value="pesado">Pesado</option>
          <option value="caixa_grande">Caixa Grande</option>
          <option value="fragil">Frágil</option>
        </select>
      </div>

      <div v-if="preview?.tracking_code || preview?.ocr?.tracking_code" class="mb-3 text-muted small">
        Rastreio: {{ preview.tracking_code || preview.ocr.tracking_code }}
      </div>

      <div class="d-grid gap-2">
        <button
          class="btn btn-success btn-lg py-3"
          :disabled="!selected"
          @click="$emit('confirm')"
        >
          CHEGOU ENCOMENDA
        </button>
        <button class="btn btn-outline-secondary" @click="$emit('retry')">CORRIGIR / NOVA FOTO</button>
        <button class="btn btn-link" @click="$emit('manual')">Registro manual</button>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'MatchResult',
  props: {
    preview: { type: Object, default: null },
    selectedCandidate: { type: Object, default: null },
    packageType: { type: String, default: 'leve' },
    sender: { type: String, default: '' },
    error: { type: String, default: null },
  },
  emits: [
    'update:selectedCandidate',
    'update:packageType',
    'update:sender',
    'confirm',
    'retry',
    'manual',
    'back',
  ],
  computed: {
    match() {
      return this.preview?.match || {};
    },
    level() {
      return this.match.level || 'low';
    },
    candidates() {
      return this.match.candidates || [];
    },
    selected() {
      return this.selectedCandidate;
    },
    confidencePct() {
      const c = this.selected?.confidence ?? this.match.confidence;
      return c != null ? Math.round(c * 100) : null;
    },
  },
};
</script>
