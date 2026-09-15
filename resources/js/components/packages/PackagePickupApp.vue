<template>
  <div class="pickup-app">
    <div v-if="step === 'code'" class="text-center">
      <div class="pickup-icon mx-auto mb-3"><i class="bi bi-box-arrow-up"></i></div>
      <h2 class="mb-2">Retirada de encomenda</h2>
      <p class="text-muted mb-4">Digite a senha de 4 dígitos informada pelo retirante.</p>

      <form @submit.prevent="findPackage">
        <label for="pickupCode" class="form-label fw-semibold">Senha de retirada</label>
        <input
          id="pickupCode"
          ref="codeInput"
          v-model="code"
          type="tel"
          inputmode="numeric"
          autocomplete="one-time-code"
          maxlength="4"
          pattern="[0-9]{4}"
          class="form-control pickup-code text-center mb-3"
          placeholder="••••"
          @input="sanitizeCode"
        >
        <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>
        <div class="d-grid gap-2">
          <button class="btn btn-primary btn-lg py-3" :disabled="loading || code.length !== 4">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            LOCALIZAR ENCOMENDA
          </button>
          <a href="/packages" class="btn btn-link text-muted">Cancelar</a>
        </div>
      </form>
    </div>

    <div v-else-if="step === 'confirm'">
      <div class="card shadow-sm border-0 pickup-card mb-3">
        <div class="card-body">
          <div class="text-success fw-bold mb-2">
            <i class="bi bi-check-circle-fill me-1"></i> Encomenda localizada
          </div>
          <h3 class="mb-1">{{ residentNames }}</h3>
          <div class="fs-5 text-muted mb-3">
            {{ unitLabel }}
          </div>
          <div class="row g-2 small">
            <div class="col-6">
              <span class="text-muted d-block">Tipo</span>
              <strong>{{ packageData.type_label }}</strong>
            </div>
            <div class="col-6">
              <span class="text-muted d-block">Recebida em</span>
              <strong>{{ receivedAt }}</strong>
            </div>
            <div v-if="packageData.sender" class="col-12">
              <span class="text-muted d-block">Remetente</span>
              <strong>{{ packageData.sender }}</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label for="pickedUpBy" class="form-label fw-semibold">
          Nome de quem está retirando <span class="text-muted fw-normal">(opcional)</span>
        </label>
        <input
          id="pickedUpBy"
          v-model.trim="pickedUpByName"
          type="text"
          maxlength="150"
          autocomplete="name"
          class="form-control form-control-lg"
          placeholder="Ex.: Tayna Fernandes"
        >
      </div>

      <div class="alert alert-warning py-2 small">
        Confirme o destinatário e entregue somente após tocar em <strong>RETIRADO!</strong>
      </div>
      <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>

      <div class="d-grid gap-2">
        <button class="btn btn-success btn-lg py-3" :disabled="loading" @click="collect">
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          <i v-else class="bi bi-check2-circle me-2"></i>
          RETIRADO!
        </button>
        <button class="btn btn-outline-secondary" :disabled="loading" @click="reset">
          Corrigir senha
        </button>
      </div>
    </div>

    <div v-else class="text-center py-4">
      <div class="display-3 text-success mb-3"><i class="bi bi-check-circle-fill"></i></div>
      <h2>Retirada registrada</h2>
      <p class="text-muted">{{ successMessage }}</p>
      <div class="d-grid gap-2 mt-4">
        <button class="btn btn-primary btn-lg py-3" @click="reset">NOVA RETIRADA</button>
        <a href="/packages" class="btn btn-outline-secondary">Voltar ao painel</a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PackagePickupApp',
  data() {
    return {
      step: 'code',
      code: '',
      packageData: null,
      pickedUpByName: '',
      loading: false,
      error: null,
      successMessage: '',
    };
  },
  mounted() {
    this.focusCode();
  },
  computed: {
    residentNames() {
      const names = (this.packageData?.residents || []).map((resident) => resident.name);
      return names.length ? names.join(' / ') : 'Morador da unidade';
    },
    unitLabel() {
      const unit = this.packageData?.unit;
      if (!unit) return '';
      return `${unit.block ? `Bloco ${unit.block} — ` : ''}Apartamento ${unit.number}`;
    },
    receivedAt() {
      if (!this.packageData?.received_at) return '—';
      return new Date(this.packageData.received_at).toLocaleString('pt-BR', {
        dateStyle: 'short',
        timeStyle: 'short',
      });
    },
  },
  methods: {
    sanitizeCode(event) {
      this.code = event.target.value.replace(/\D/g, '').slice(0, 4);
      this.error = null;
      if (this.code.length === 4) {
        this.findPackage();
      }
    },
    focusCode() {
      this.$nextTick(() => this.$refs.codeInput?.focus());
    },
    async findPackage() {
      if (this.loading || this.code.length !== 4) return;
      this.loading = true;
      this.error = null;

      try {
        const { data } = await window.axios.post('/api/packages/pickup/find', {
          pickup_code: this.code,
        });
        this.packageData = data.package;
        this.pickedUpByName = '';
        this.step = 'confirm';
      } catch (error) {
        this.error = this.firstError(error, 'Encomenda não encontrada.');
        this.code = '';
        this.focusCode();
      } finally {
        this.loading = false;
      }
    },
    async collect() {
      if (!this.packageData || this.loading) return;
      this.loading = true;
      this.error = null;

      try {
        const { data } = await window.axios.post(
          `/api/packages/${this.packageData.id}/collect`,
          {
            pickup_code: this.code,
            picked_up_by_name: this.pickedUpByName || null,
          },
        );
        this.successMessage = data.message || 'Data, hora e responsável foram registrados.';
        this.step = 'success';
      } catch (error) {
        this.error = this.firstError(error, 'Não foi possível registrar a retirada.');
      } finally {
        this.loading = false;
      }
    },
    reset() {
      this.step = 'code';
      this.code = '';
      this.packageData = null;
      this.pickedUpByName = '';
      this.error = null;
      this.successMessage = '';
      this.focusCode();
    },
    firstError(error, fallback) {
      const errors = error.response?.data?.errors || {};
      const first = Object.values(errors)[0];
      return error.response?.data?.error
        || (Array.isArray(first) ? first[0] : first)
        || fallback;
    },
  },
};
</script>

<style scoped>
.pickup-app {
  min-height: 70vh;
}
.pickup-icon {
  width: 72px;
  height: 72px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  color: #0d6efd;
  background: rgba(13, 110, 253, 0.12);
  font-size: 2rem;
}
.pickup-code {
  height: 76px;
  font-size: 2.25rem;
  font-weight: 700;
  letter-spacing: 0.75rem;
  padding-left: 1.25rem;
}
.pickup-card {
  border-radius: 18px;
}
</style>
