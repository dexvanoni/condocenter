<template>
  <section class="access-checkin">
    <div v-if="step === 'menu'" class="access-checkin__menu">
      <button type="button" class="access-checkin__btn access-checkin__btn--scan" @click="openScanner">
        <i class="bi bi-qr-code-scan"></i>
        <span>Escanear QR</span>
        <small>Aponte para o código do visitante</small>
      </button>
      <button type="button" class="access-checkin__btn access-checkin__btn--pin" @click="openPin">
        <i class="bi bi-key-fill"></i>
        <span>Digitar senha</span>
        <small>Senha de 4 dígitos do visitante</small>
      </button>
    </div>

    <div v-else-if="step === 'pin'" class="access-checkin__panel text-center">
      <button type="button" class="btn btn-link text-muted mb-2" @click="reset"><i class="bi bi-arrow-left"></i> Voltar</button>
      <h2 class="h4 mb-1">Senha do visitante</h2>
      <p class="text-muted mb-3">Digite os 4 dígitos informados pelo morador.</p>
      <input
        ref="pinInput"
        v-model="pin"
        type="tel"
        inputmode="numeric"
        autocomplete="one-time-code"
        maxlength="4"
        pattern="[0-9]{4}"
        class="form-control access-checkin__pin text-center mb-3"
        placeholder="••••"
        @input="sanitizePin"
      >
      <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>
      <button class="btn btn-success btn-lg w-100 py-3" :disabled="loading || pin.length !== 4" @click="submitPin">
        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
        LIBERAR ENTRADA
      </button>
    </div>

    <div v-else-if="step === 'scan'" class="access-checkin__panel">
      <button type="button" class="btn btn-link text-muted mb-2" @click="reset"><i class="bi bi-arrow-left"></i> Voltar</button>
      <h2 class="h4 text-center mb-2">Escanear QR Code</h2>
      <div class="access-checkin__camera rounded-3 overflow-hidden bg-dark mb-3">
        <video ref="video" class="w-100" playsinline muted autoplay></video>
      </div>
      <p v-if="status" class="text-center small text-muted mb-2">{{ status }}</p>
      <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>
    </div>

    <div v-else-if="step === 'success'" class="access-checkin__success text-center">
      <div class="access-checkin__success-icon mb-3"><i class="bi bi-check-circle-fill"></i></div>
      <h2 class="h3 text-success mb-1">Portão liberado</h2>
      <p class="fs-4 fw-bold mb-1">{{ visitorName }}</p>
      <p class="text-muted mb-1">{{ unitLabel }}</p>
      <p class="small text-muted mb-4">O morador foi notificado no WhatsApp.</p>
      <div class="d-grid gap-2">
        <button class="btn btn-primary btn-lg py-3" @click="reset">NOVA LIBERAÇÃO</button>
      </div>
    </div>
  </section>
</template>

<script>
import { BrowserMultiFormatReader } from '@zxing/library';

export default {
  name: 'AccessCheckinApp',
  props: {
    csrfToken: { type: String, required: true },
  },
  emits: ['checked-in'],
  data() {
    return {
      step: 'menu',
      pin: '',
      loading: false,
      error: null,
      status: '',
      stream: null,
      reader: null,
      scanLock: false,
      visitorName: '',
      unitLabel: '',
    };
  },
  beforeUnmount() {
    this.stopScanner();
  },
  methods: {
    openPin() {
      this.resetState();
      this.step = 'pin';
      this.$nextTick(() => this.$refs.pinInput?.focus());
    },
    async openScanner() {
      this.resetState();
      this.step = 'scan';
      await this.$nextTick();
      await this.startScanner();
    },
    resetState() {
      this.error = null;
      this.status = '';
      this.pin = '';
      this.loading = false;
      this.scanLock = false;
    },
    reset() {
      this.stopScanner();
      this.resetState();
      this.step = 'menu';
    },
    sanitizePin() {
      this.pin = this.pin.replace(/\D/g, '').slice(0, 4);
      this.error = null;
    },
    async submitPin() {
      if (this.pin.length !== 4) return;
      this.loading = true;
      this.error = null;
      try {
        const data = await this.postJson('/api/access-control/check-in/pin', { access_pin: this.pin });
        this.showSuccess(data.authorization);
      } catch (e) {
        this.error = e.message;
      } finally {
        this.loading = false;
      }
    },
    async startScanner() {
      this.status = 'Solicitando câmera...';
      try {
        if (!navigator.mediaDevices?.getUserMedia) {
          throw new Error('Câmera indisponível. Use HTTPS ou digite a senha.');
        }
        this.stream = await navigator.mediaDevices.getUserMedia({
          audio: false,
          video: { facingMode: { ideal: 'environment' } },
        });
        const video = this.$refs.video;
        video.srcObject = this.stream;
        await video.play();
        this.status = 'Aponte para o QR Code do visitante';
        this.reader = new BrowserMultiFormatReader();
        this.reader.decodeFromVideoDevice(undefined, video, (result, err) => {
          if (result && !this.scanLock) {
            this.scanLock = true;
            this.handleQr(result.getText());
          }
        });
      } catch (e) {
        this.error = e.message || 'Não foi possível acessar a câmera.';
        this.status = '';
      }
    },
    stopScanner() {
      if (this.reader) {
        this.reader.reset();
        this.reader = null;
      }
      if (this.stream) {
        this.stream.getTracks().forEach((track) => track.stop());
        this.stream = null;
      }
    },
    async handleQr(qrData) {
      this.loading = true;
      this.error = null;
      this.status = 'Validando...';
      try {
        const data = await this.postJson('/api/access-control/check-in/qr', { qr_data: qrData });
        this.stopScanner();
        this.showSuccess(data.authorization);
      } catch (e) {
        this.error = e.message;
        this.scanLock = false;
        this.status = 'Aponte para o QR Code do visitante';
      } finally {
        this.loading = false;
      }
    },
    showSuccess(authorization) {
      this.visitorName = authorization?.visitor_name || 'Visitante';
      this.unitLabel = authorization?.unit?.full_identifier
        ? `Unidade ${authorization.unit.full_identifier}`
        : '';
      this.step = 'success';
      this.$emit('checked-in', authorization);
      window.dispatchEvent(new CustomEvent('access-checkin:success', { detail: authorization }));
    },
    async postJson(url, body) {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': this.csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
      });
      const data = await res.json();
      if (!res.ok) {
        throw new Error(data.error || Object.values(data.errors || {})[0]?.[0] || 'Não foi possível liberar o visitante.');
      }
      return data;
    },
  },
};
</script>

<style scoped>
.access-checkin__menu {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}
.access-checkin__btn {
  border: 0;
  border-radius: 16px;
  padding: 1rem 0.85rem;
  min-height: 110px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  color: #fff;
  font-weight: 700;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
}
.access-checkin__btn i {
  font-size: 1.75rem;
}
.access-checkin__btn small {
  font-weight: 500;
  opacity: 0.9;
}
.access-checkin__btn--scan {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
}
.access-checkin__btn--pin {
  background: linear-gradient(135deg, #059669, #047857);
}
.access-checkin__pin {
  font-size: 2rem;
  letter-spacing: 0.5rem;
  font-weight: 700;
  max-width: 220px;
  margin-inline: auto;
}
.access-checkin__camera {
  min-height: 240px;
}
.access-checkin__success-icon {
  font-size: 4rem;
  color: #16a34a;
}
@media (max-width: 767px) {
  .access-checkin__menu {
    grid-template-columns: 1fr;
  }
}
</style>
