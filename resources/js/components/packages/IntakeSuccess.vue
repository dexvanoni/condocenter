<template>
  <div class="text-center py-4">
    <div class="display-4 text-success mb-3">✅</div>
    <h3 class="mb-3">ENCOMENDA REGISTRADA</h3>
    <p class="text-muted">{{ message }}</p>

    <div v-if="whatsappStatus === 'sent'" class="alert alert-success">
      WhatsApp enviado ✓
    </div>
    <div v-else-if="whatsappStatus === 'failed'" class="alert alert-warning">
      Encomenda registrada. WhatsApp pendente / falhou — será reprocessado.
    </div>
    <div v-else class="alert alert-info">
      Encomenda registrada. WhatsApp pendente.
    </div>

    <div class="d-grid gap-2 mt-4">
      <button class="btn btn-primary btn-lg" @click="$emit('again')">NOVA ENCOMENDA</button>
      <button class="btn btn-outline-secondary" @click="$emit('panel')">Ir ao painel</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'IntakeSuccess',
  props: {
    message: { type: String, default: '' },
    whatsappStatus: { type: String, default: 'pending' },
  },
  emits: ['again', 'panel'],
};
</script>
