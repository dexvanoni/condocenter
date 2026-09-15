<template>
  <section class="pet-verify">
    <div v-if="step === 'scan'" class="pet-verify__scan">
      <div class="pet-verify__camera rounded-3 overflow-hidden bg-dark mb-3">
        <video ref="video" class="w-100" playsinline muted autoplay></video>
      </div>
      <p v-if="status" class="text-center small text-muted mb-2">{{ status }}</p>
      <div v-if="loading" class="text-center py-2">
        <span class="spinner-border spinner-border-sm text-primary me-2"></span>
        Identificando pet...
      </div>
      <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>
    </div>

    <div v-else-if="step === 'result' && pet" class="pet-verify__result">
      <div class="alert alert-success mb-3">
        <h5 class="alert-heading mb-0">
          <i class="bi bi-check-circle-fill"></i> Pet encontrado!
        </h5>
      </div>

      <div class="pet-info-card">
        <img :src="pet.photo" :alt="pet.name" class="pet-photo-result">

        <h4 class="mb-3">
          <i class="bi bi-hearts"></i> {{ pet.name }}
          <span class="badge bg-primary ms-2">{{ pet.type }}</span>
          <span class="badge bg-info ms-1">{{ pet.size }}</span>
        </h4>

        <p v-if="pet.breed" class="mb-2"><strong>Raça:</strong> {{ pet.breed }}</p>
        <p v-if="pet.color" class="mb-2"><strong>Cor:</strong> {{ pet.color }}</p>
        <p v-if="pet.description" class="mb-2"><strong>Descrição:</strong> {{ pet.description }}</p>

        <hr>
        <h5 class="mb-3"><i class="bi bi-person-circle"></i> Informações do dono</h5>
        <p class="mb-2"><strong>Nome:</strong> {{ pet.owner.name }}</p>
        <p class="mb-2"><strong>Telefone:</strong> {{ pet.owner.phone }}</p>
        <p class="mb-2"><strong>Unidade:</strong> {{ pet.unit.identifier }}</p>
        <p v-if="pet.condominium?.name" class="mb-2">
          <strong>Condomínio:</strong> {{ pet.condominium.name }}
        </p>

        <div class="d-grid gap-2 mt-4">
          <button
            type="button"
            class="btn btn-success btn-lg py-3"
            :disabled="notifying"
            @click="callOwner"
          >
            <span v-if="notifying" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else class="bi bi-whatsapp me-2"></i>
            Chamar o DONO
          </button>
          <button type="button" class="btn btn-outline-primary" @click="restartScan">
            <i class="bi bi-qr-code-scan me-1"></i> Nova leitura
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { BrowserMultiFormatReader } from '@zxing/library';

export default {
  name: 'PetVerifyApp',
  props: {
    csrfToken: { type: String, required: true },
    verifyUrl: { type: String, required: true },
  },
  data() {
    return {
      step: 'scan',
      pet: null,
      loading: false,
      notifying: false,
      error: null,
      status: 'Iniciando câmera...',
      stream: null,
      reader: null,
      scanLock: false,
    };
  },
  mounted() {
    this.startScanner();
  },
  beforeUnmount() {
    this.stopScanner();
  },
  methods: {
    async startScanner() {
      this.error = null;
      this.status = 'Solicitando câmera...';
      this.scanLock = false;

      try {
        if (!navigator.mediaDevices?.getUserMedia) {
          throw new Error('Câmera indisponível. Use HTTPS ou um navegador compatível.');
        }

        this.stream = await navigator.mediaDevices.getUserMedia({
          audio: false,
          video: { facingMode: { ideal: 'environment' } },
        });

        const video = this.$refs.video;
        video.srcObject = this.stream;
        await video.play();

        this.status = 'Aponte para o QR Code da coleira do pet';
        this.reader = new BrowserMultiFormatReader();
        this.reader.decodeFromVideoDevice(undefined, video, (result) => {
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
      this.status = 'Identificando pet...';

      try {
        const data = await this.postJson(this.verifyUrl, { qr_code: qrData });

        if (!data.success || !data.pet) {
          throw new Error(data.message || 'Pet não encontrado.');
        }

        this.stopScanner();
        this.pet = data.pet;
        this.step = 'result';
      } catch (e) {
        this.error = e.message;
        this.scanLock = false;
        this.status = 'Aponte para o QR Code da coleira do pet';
      } finally {
        this.loading = false;
      }
    },
    async callOwner() {
      if (!this.pet?.notify_url) {
        window.open(this.pet.owner.whatsapp_link, '_blank');
        return;
      }

      this.notifying = true;
      this.error = null;

      try {
        const data = await this.postJson(this.pet.notify_url, {});
        const whatsappLink = data.whatsapp_link || this.pet.owner.whatsapp_link;
        window.open(whatsappLink, '_blank');
      } catch (e) {
        this.error = e.message;
      } finally {
        this.notifying = false;
      }
    },
    restartScan() {
      this.pet = null;
      this.error = null;
      this.step = 'scan';
      this.$nextTick(() => this.startScanner());
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
        throw new Error(
          data.message || Object.values(data.errors || {})[0]?.[0] || 'Não foi possível processar a solicitação.'
        );
      }

      return data;
    },
  },
};
</script>

<style scoped>
.pet-verify__camera {
  min-height: 280px;
  border: 3px solid #0d6efd;
}

.pet-info-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 15px;
}

.pet-photo-result {
  width: 100%;
  max-height: 280px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 15px;
}
</style>
