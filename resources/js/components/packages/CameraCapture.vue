<template>
  <div class="camera-capture">
    <h5 class="mb-3 text-center">Aponte para a etiqueta</h5>

    <div class="camera-frame position-relative mb-3 bg-dark rounded-3 overflow-hidden">
      <video ref="video" class="w-100" playsinline muted autoplay></video>
      <div ref="guide" class="guide-overlay" :class="{ 'guide-overlay--active': scanning && !loadingOcr }">
        <span>ETIQUETA</span>
      </div>
      <canvas ref="canvas" class="d-none"></canvas>
    </div>

    <p class="text-center small text-muted mb-2">
      Mantenha o celular firme na área tracejada.
    </p>

    <div v-if="loadingOcr" class="mb-3 px-1">
      <div class="d-flex justify-content-between small text-muted mb-1">
        <span>Preparando leitura automática</span>
        <span>{{ loadProgress }}%</span>
      </div>
      <div class="progress progress-thick" role="progressbar" :aria-valuenow="loadProgress" aria-valuemin="0" aria-valuemax="100">
        <div
          class="progress-bar progress-bar-striped progress-bar-animated"
          :style="{ width: `${loadProgress}%` }"
        ></div>
      </div>
    </div>

    <p v-if="useTesseractFallback" class="alert alert-info py-2 small mb-2">
      A leitura automática não está disponível neste aparelho. Toque em <strong>Capturar</strong> para enviar a foto ao servidor.
    </p>
    <p v-if="error" class="alert alert-warning py-2">{{ error }}</p>

    <div class="d-grid gap-2">
      <button
        v-if="useTesseractFallback"
        class="btn btn-primary btn-lg"
        :disabled="!streamReady || capturing"
        @click="captureForServer"
      >
        <i class="bi bi-camera"></i> CAPTURAR
      </button>
      <button class="btn btn-outline-secondary" @click="$emit('cancel')">CANCELAR</button>
    </div>
  </div>
</template>

<script>
import { BrowserMultiFormatReader } from '@zxing/library';
import {
    destroyPackageLiveOcr,
    initPackageLiveOcr,
    recognizeLabelFrame,
} from '../../services/packageLiveOcr.js';

export default {
  name: 'CameraCapture',
  emits: ['recognized', 'captured', 'cancel'],
  data() {
    return {
      stream: null,
      streamReady: false,
      capturing: false,
      scanning: false,
      error: null,
      loadingOcr: true,
      loadProgress: 5,
      progressTimer: null,
      useTesseractFallback: false,
      scanTimer: null,
      matchInFlight: false,
      lastMatchFingerprint: '',
      lastOcrFingerprint: '',
      ocrBusy: false,
    };
  },
  async mounted() {
    this.startProgressTicker();
    await this.startCamera();
    await this.startLiveOcr();
  },
  beforeUnmount() {
    this.stopProgressTicker();
    this.stopScanLoop();
    this.stopCamera();
    destroyPackageLiveOcr();
  },
  methods: {
    startProgressTicker() {
      this.stopProgressTicker();
      this.progressTimer = setInterval(() => {
        if (!this.loadingOcr || this.loadProgress >= 95) {
          return;
        }
        this.loadProgress = Math.min(95, this.loadProgress + 2);
      }, 400);
    },
    stopProgressTicker() {
      if (this.progressTimer) {
        clearInterval(this.progressTimer);
        this.progressTimer = null;
      }
    },
    handleOcrProgress({ done, total }) {
      if (typeof total === 'number' && total > 0 && typeof done === 'number') {
        this.loadProgress = Math.min(99, Math.round((done / total) * 100));
        return;
      }
      if (typeof done === 'number' && done > 0 && done <= 1) {
        this.loadProgress = Math.min(99, Math.round(done * 100));
      }
    },
    finishOcrLoading() {
      this.loadProgress = 100;
      this.loadingOcr = false;
      this.stopProgressTicker();
    },
    enableTesseractFallback(message) {
      this.useTesseractFallback = true;
      this.finishOcrLoading();
      this.stopScanLoop();
      destroyPackageLiveOcr();
      if (message) {
        this.error = message;
      }
    },
    async startCamera() {
      this.error = null;
      try {
        if (!navigator.mediaDevices?.getUserMedia) {
          throw new Error('Câmera não disponível. Use HTTPS ou localhost.');
        }

        this.stream = await navigator.mediaDevices.getUserMedia({
          audio: false,
          video: {
            facingMode: { ideal: 'environment' },
            width: { ideal: 1920, min: 1280 },
            height: { ideal: 1080, min: 720 },
          },
        });

        const video = this.$refs.video;
        video.srcObject = this.stream;
        await video.play();
        this.streamReady = true;
      } catch (e) {
        this.error = e.message || 'Não foi possível acessar a câmera.';
        this.finishOcrLoading();
      }
    },
    async startLiveOcr() {
      try {
        await initPackageLiveOcr((progress) => this.handleOcrProgress(progress));
        this.finishOcrLoading();
        this.startScanLoop();
      } catch (e) {
        this.enableTesseractFallback(
          e.message || 'Não foi possível iniciar a leitura automática.',
        );
      }
    },
    stopScanLoop() {
      if (this.scanTimer) {
        clearInterval(this.scanTimer);
        this.scanTimer = null;
      }
    },
    startScanLoop() {
      if (this.useTesseractFallback) {
        return;
      }
      this.stopScanLoop();
      this.scanTimer = setInterval(() => {
        this.scanFrame();
      }, 1800);
    },
    stopCamera() {
      if (this.stream) {
        this.stream.getTracks().forEach((t) => t.stop());
        this.stream = null;
      }
      this.streamReady = false;
    },
    getGuideCropCanvas() {
      const video = this.$refs.video;
      const guide = this.$refs.guide;
      const canvas = this.$refs.canvas;
      if (!video?.videoWidth || !guide) {
        return null;
      }

      const videoRect = video.getBoundingClientRect();
      const guideRect = guide.getBoundingClientRect();
      const scaleX = video.videoWidth / videoRect.width;
      const scaleY = video.videoHeight / videoRect.height;
      const padX = Math.round(guideRect.width * 0.06 * scaleX);
      const padY = Math.round(guideRect.height * 0.06 * scaleY);
      const sx = Math.max(0, Math.round((guideRect.left - videoRect.left) * scaleX) - padX);
      const sy = Math.max(0, Math.round((guideRect.top - videoRect.top) * scaleY) - padY);
      const sw = Math.min(video.videoWidth - sx, Math.round(guideRect.width * scaleX) + padX * 2);
      const sh = Math.min(video.videoHeight - sy, Math.round(guideRect.height * scaleY) + padY * 2);
      const maxSide = 1280;
      const outputScale = Math.min(1, maxSide / Math.max(sw, sh));
      const w = Math.max(1, Math.round(sw * outputScale));
      const h = Math.max(1, Math.round(sh * outputScale));

      canvas.width = w;
      canvas.height = h;
      const ctx = canvas.getContext('2d', { willReadFrequently: true });
      ctx.filter = 'grayscale(1) contrast(1.2) brightness(1.05)';
      ctx.drawImage(video, sx, sy, sw, sh, 0, 0, w, h);
      ctx.filter = 'none';

      return canvas;
    },
    normalizeText(text) {
      return (text || '').replace(/\s+/g, ' ').trim().toUpperCase();
    },
    textLooksPromising(text) {
      const normalized = this.normalizeText(text);
      if (normalized.length < 18) {
        return false;
      }
      if (!/[A-ZÁÉÍÓÚÃÕÂÊÔÇ]{3,}/i.test(normalized)) {
        return false;
      }
      const words = normalized.split(' ').filter((w) => w.length >= 3);
      return words.length >= 2;
    },
    async tryReadBarcode(canvas) {
      try {
        const reader = new BrowserMultiFormatReader();
        const result = await reader.decodeFromCanvas(canvas);
        return result?.getText?.() || null;
      } catch {
        return null;
      }
    },
    async scanFrame() {
      if (this.useTesseractFallback || !this.streamReady || this.capturing || this.ocrBusy || this.matchInFlight) {
        return;
      }

      const canvas = this.getGuideCropCanvas();
      if (!canvas) {
        return;
      }

      this.ocrBusy = true;
      this.scanning = true;
      try {
        const { text, confidence } = await recognizeLabelFrame(canvas);
        const fingerprint = this.normalizeText(text).slice(0, 220);
        if (!this.textLooksPromising(text) || fingerprint === this.lastOcrFingerprint) {
          return;
        }

        this.lastOcrFingerprint = fingerprint;

        if (fingerprint === this.lastMatchFingerprint || this.matchInFlight) {
          return;
        }

        this.matchInFlight = true;

        const { data } = await window.axios.post('/api/packages/label/match-text', {
          ocr_text: text,
          ocr_confidence: confidence,
          ocr_engine: 'paddle-js-v6',
        });

        const match = data.match || {};
        const level = match.level || 'low';
        const matchConfidence = Number(match.confidence || 0);

        if (!['high', 'medium'].includes(level) || matchConfidence < 0.5) {
          return;
        }

        this.lastMatchFingerprint = fingerprint;
        const barcode = await this.tryReadBarcode(canvas);
        const blob = await new Promise((resolve) => {
          canvas.toBlob((b) => resolve(b), 'image/jpeg', 0.92);
        });

        if (!blob) {
          throw new Error('Falha ao capturar imagem da etiqueta.');
        }

        this.stopScanLoop();
        this.stopCamera();
        this.$emit('recognized', {
          blob,
          barcode,
          ocrText: text,
          ocrConfidence: confidence,
          matchPreview: data,
        });
      } catch (e) {
        if (!e.response || e.response.status >= 500) {
          this.error = e.message || 'Falha na leitura em tempo real.';
        }
      } finally {
        this.ocrBusy = false;
        this.scanning = false;
        this.matchInFlight = false;
      }
    },
    async captureForServer() {
      if (!this.streamReady || this.capturing) {
        return;
      }
      this.capturing = true;
      this.stopScanLoop();

      try {
        const canvas = this.getGuideCropCanvas();
        if (!canvas) {
          throw new Error('Não foi possível capturar o recorte da etiqueta.');
        }

        const barcode = await this.tryReadBarcode(canvas);
        const blob = await new Promise((resolve) => {
          canvas.toBlob((b) => resolve(b), 'image/jpeg', 0.92);
        });

        if (!blob) {
          throw new Error('Falha ao capturar imagem.');
        }

        this.stopCamera();
        this.$emit('captured', { blob, barcode });
      } catch (e) {
        this.error = e.message || 'Erro ao capturar.';
        this.capturing = false;
      }
    },
  },
};
</script>

<style scoped>
.camera-frame {
  aspect-ratio: 3 / 4;
  max-height: 60vh;
}
.camera-frame video {
  object-fit: cover;
  height: 100%;
  min-height: 280px;
}
.guide-overlay {
  pointer-events: none;
  position: absolute;
  inset: 6% 8%;
  border: 2px dashed rgba(255, 255, 255, 0.85);
  border-radius: 12px;
  box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.35);
  transition: border-color 0.2s ease;
}
.guide-overlay--active {
  border-color: rgba(13, 110, 253, 0.95);
}
.guide-overlay span {
  position: absolute;
  top: 0.5rem;
  left: 50%;
  transform: translateX(-50%);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-shadow: 0 1px 4px #000;
}
.progress-thick {
  height: 10px;
  border-radius: 999px;
}
.progress-thick .progress-bar {
  border-radius: 999px;
}
</style>
