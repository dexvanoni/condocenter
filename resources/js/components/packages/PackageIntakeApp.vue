<template>
  <div class="package-intake">
    <!-- ETAPA 1: Entrada -->
    <div v-if="step === 'home'" class="text-center">
      <h2 class="mb-2">
        <i class="bi bi-box-seam"></i> Entrada de Encomendas
      </h2>
      <p class="text-muted mb-4">Fluxo rápido para a portaria</p>

      <div class="d-grid gap-3">
        <button class="btn btn-primary btn-lg py-3" @click="startCamera">
          <i class="bi bi-camera-fill me-2"></i> LER ETIQUETA
        </button>
        <a :href="manualUrl" class="btn btn-outline-secondary btn-lg py-3">
          <i class="bi bi-pencil-square me-2"></i> REGISTRAR MANUALMENTE
        </a>
        <a :href="panelUrl" class="btn btn-link text-muted">
          Ver painel de encomendas
        </a>
      </div>
    </div>

    <!-- ETAPA 2: Câmera -->
    <CameraCapture
      v-else-if="step === 'camera'"
      @recognized="onRecognized"
      @captured="onServerCapture"
      @cancel="resetToHome"
    />

    <!-- ETAPA 3: Processando -->
    <div v-else-if="step === 'processing'" class="text-center py-5">
      <div class="spinner-border text-primary mb-3" role="status"></div>
      <h5>🔎 Lendo etiqueta...</h5>
      <p class="text-muted mb-1">Aguarde um instante.</p>
      <p class="text-muted small">Na primeira vez do dia pode demorar um pouco mais.</p>
    </div>

    <!-- ETAPA 4: Resultado -->
    <MatchResult
      v-else-if="step === 'result'"
      :preview="preview"
      :selected-candidate="selectedCandidate"
      :package-type="packageType"
      :sender="sender"
      :error="error"
      @update:selectedCandidate="selectedCandidate = $event"
      @update:packageType="packageType = $event"
      @update:sender="sender = $event"
      @confirm="confirmPackage"
      @retry="startCamera"
      @manual="goManual"
      @back="resetToHome"
    />

    <!-- ETAPA 5: Sucesso -->
    <IntakeSuccess
      v-else-if="step === 'success'"
      :message="successMessage"
      :whatsapp-status="whatsappStatus"
      @again="resetToHome"
      @panel="goPanel"
    />
  </div>
</template>

<script>
import CameraCapture from './CameraCapture.vue';
import MatchResult from './MatchResult.vue';
import IntakeSuccess from './IntakeSuccess.vue';

export default {
  name: 'PackageIntakeApp',
  components: { CameraCapture, MatchResult, IntakeSuccess },
  data() {
    return {
      step: 'home',
      preview: null,
      selectedCandidate: null,
      packageType: 'leve',
      sender: '',
      barcodeValue: null,
      error: null,
      successMessage: '',
      whatsappStatus: 'pending',
      manualUrl: '/packages',
      panelUrl: '/packages',
    };
  },
  methods: {
    startCamera() {
      this.error = null;
      this.preview = null;
      this.selectedCandidate = null;
      this.barcodeValue = null;
      this.step = 'camera';
    },
    resetToHome() {
      this.step = 'home';
      this.error = null;
      this.preview = null;
    },
    goManual() {
      window.location.href = this.manualUrl;
    },
    goPanel() {
      window.location.href = this.panelUrl;
    },
    async onServerCapture({ blob, barcode }) {
      this.barcodeValue = barcode || null;
      this.step = 'processing';
      try {
        const form = new FormData();
        form.append('image', blob, 'label.jpg');
        if (this.barcodeValue) {
          form.append('barcode_value', this.barcodeValue);
        }

        const { data } = await window.axios.post('/api/packages/label/preview', form, {
          headers: { 'Content-Type': 'multipart/form-data' },
          timeout: 300000,
        });

        this.applyPreviewResponse(data);
      } catch (e) {
        this.handlePreviewError(e);
      }
    },
    async onRecognized({ blob, barcode, ocrText, ocrConfidence, matchPreview }) {
      this.barcodeValue = barcode || null;
      this.step = 'processing';
      try {
        const form = new FormData();
        form.append('image', blob, 'label.jpg');
        form.append('ocr_text', ocrText || '');
        if (ocrConfidence != null) {
          form.append('ocr_confidence', String(ocrConfidence));
        }
        form.append('ocr_engine', 'paddle-js-v6');
        if (this.barcodeValue) {
          form.append('barcode_value', this.barcodeValue);
        }

        const { data } = await window.axios.post('/api/packages/label/preview-client', form, {
          headers: { 'Content-Type': 'multipart/form-data' },
          timeout: 120000,
        });

        this.applyPreviewResponse(data, matchPreview);
      } catch (e) {
        this.handlePreviewError(e);
      }
    },
    applyPreviewResponse(data, matchPreview = null) {
      this.preview = data;
      if (matchPreview?.match && (!data.match || data.match.level === 'low')) {
        this.preview = { ...data, match: matchPreview.match, ocr: matchPreview.ocr || data.ocr };
      }
      this.sender = data.sender || '';
      this.packageType = 'leve';
      this.error = data.error || data.message || null;

      const match = this.preview.match || {};
      if (match.level === 'high' && match.candidates?.length) {
        this.selectedCandidate = match.candidates[0];
      } else if (match.level === 'medium' && match.candidates?.length) {
        this.selectedCandidate = match.candidates[0];
      } else {
        this.selectedCandidate = null;
      }

      this.step = 'result';
    },
    handlePreviewError(e) {
      const status = e.response?.status;
      if (status === 524 || status === 504 || e.code === 'ECONNABORTED') {
        this.error = 'A leitura demorou mais que o limite do servidor. Tente novamente em instantes.';
      } else {
        this.error = e.response?.data?.error
          || e.response?.data?.message
          || e.response?.data?.ocr?.extra?.error
          || e.message
          || 'Falha ao ler a etiqueta.';
      }
      this.preview = {
        match: { level: 'low', candidates: [], confidence: 0 },
        ocr: {},
        error: this.error,
        message: this.error,
      };
      this.step = 'result';
    },
    async confirmPackage() {
      if (!this.selectedCandidate?.unit_id) {
        this.error = 'Selecione o destinatário antes de confirmar.';
        return;
      }

      this.error = null;
      this.step = 'processing';

      try {
        const payload = {
          unit_id: this.selectedCandidate.unit_id,
          resident_id: this.selectedCandidate.resident_id || null,
          type: this.packageType,
          sender: this.sender || null,
          tracking_code: this.preview?.tracking_code || this.preview?.ocr?.tracking_code || null,
          label_image_path: this.preview?.label_image_path || null,
          ocr_text: this.preview?.ocr?.raw_text || null,
          ocr_confidence: this.preview?.ocr?.confidence ?? null,
          identification_method: this.preview?.identification_method || 'ocr',
          identification_confidence: this.selectedCandidate.confidence ?? this.preview?.match?.confidence ?? null,
        };

        const { data } = await window.axios.post('/api/packages/label/confirm', payload);
        this.successMessage = data.message || 'Encomenda registrada.';
        this.whatsappStatus = data.whatsapp_status || 'pending';
        this.step = 'success';
      } catch (e) {
        this.error = e.response?.data?.error
          || Object.values(e.response?.data?.errors || {})[0]?.[0]
          || e.message
          || 'Erro ao registrar encomenda.';
        this.step = 'result';
      }
    },
  },
};
</script>

<style scoped>
.package-intake {
  min-height: 70vh;
}
</style>
