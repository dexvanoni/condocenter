<template>
  <div class="camera-capture">
    <h5 class="mb-3 text-center">Posicione a etiqueta na área</h5>

    <div class="camera-frame position-relative mb-3 bg-dark rounded-3 overflow-hidden">
      <video ref="video" class="w-100" playsinline muted autoplay></video>
      <div ref="guide" class="guide-overlay">
        <span>ETIQUETA</span>
      </div>
      <canvas ref="canvas" class="d-none"></canvas>
    </div>

    <p v-if="status" class="text-center small text-muted">{{ status }}</p>
    <p class="text-center small text-muted mb-2">
      Preencha o quadro, mantenha o celular paralelo e evite reflexos.
    </p>
    <p v-if="error" class="alert alert-warning py-2">{{ error }}</p>

    <div class="d-grid gap-2">
      <button class="btn btn-primary btn-lg" :disabled="!streamReady || capturing" @click="capture">
        <i class="bi bi-camera"></i> CAPTURAR
      </button>
      <button class="btn btn-outline-secondary" @click="$emit('cancel')">CANCELAR</button>
    </div>
  </div>
</template>

<script>
import { BrowserMultiFormatReader } from '@zxing/library';

export default {
  name: 'CameraCapture',
  emits: ['captured', 'cancel'],
  data() {
    return {
      stream: null,
      streamReady: false,
      capturing: false,
      error: null,
      status: 'Solicitando câmera...',
    };
  },
  async mounted() {
    await this.startCamera();
  },
  beforeUnmount() {
    this.stopCamera();
  },
  methods: {
    async startCamera() {
      this.error = null;
      try {
        if (!navigator.mediaDevices?.getUserMedia) {
          throw new Error('Câmera não disponível neste navegador. Use HTTPS ou localhost.');
        }

        this.stream = await navigator.mediaDevices.getUserMedia({
          audio: false,
          video: {
            facingMode: { ideal: 'environment' },
            width: { ideal: 3840, min: 1280 },
            height: { ideal: 2160, min: 720 },
            aspectRatio: { ideal: 0.75 },
          },
        });

        const video = this.$refs.video;
        video.srcObject = this.stream;
        await video.play();
        this.streamReady = true;
        this.status = 'Aponte para a etiqueta e toque em Capturar';
      } catch (e) {
        this.error = e.message || 'Não foi possível acessar a câmera.';
        this.status = '';
      }
    },
    stopCamera() {
      if (this.stream) {
        this.stream.getTracks().forEach((t) => t.stop());
        this.stream = null;
      }
      this.streamReady = false;
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
    async capture() {
      if (!this.streamReady || this.capturing) return;
      this.capturing = true;

      try {
        const video = this.$refs.video;
        const canvas = this.$refs.canvas;
        const videoRect = video.getBoundingClientRect();
        const guideRect = this.$refs.guide.getBoundingClientRect();
        const scaleX = video.videoWidth / videoRect.width;
        const scaleY = video.videoHeight / videoRect.height;

        // Recorta exatamente a área indicada na tela, reduzindo barras do
        // navegador, fundo e outros textos que confundem o OCR.
        const sx = Math.max(0, Math.round((guideRect.left - videoRect.left) * scaleX));
        const sy = Math.max(0, Math.round((guideRect.top - videoRect.top) * scaleY));
        const sw = Math.min(
          video.videoWidth - sx,
          Math.round(guideRect.width * scaleX),
        );
        const sh = Math.min(
          video.videoHeight - sy,
          Math.round(guideRect.height * scaleY),
        );

        const maxSide = 2600;
        const outputScale = Math.min(1, maxSide / Math.max(sw, sh));
        const w = Math.max(1, Math.round(sw * outputScale));
        const h = Math.max(1, Math.round(sh * outputScale));

        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, sx, sy, sw, sh, 0, 0, w, h);

        const barcode = await this.tryReadBarcode(canvas);

        const blob = await new Promise((resolve) => {
          canvas.toBlob((b) => resolve(b), 'image/jpeg', 0.94);
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
</style>
