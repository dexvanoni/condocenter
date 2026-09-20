import { PaddleOcrService, V6_TINY_MODEL } from 'ppu-paddle-ocr/web';

let service = null;
let initPromise = null;

async function ensureService() {
    if (!initPromise) {
        initPromise = (async () => {
            service = new PaddleOcrService({
                model: V6_TINY_MODEL,
                recognition: { mainThreadYieldMs: 0 },
            });
            await service.initialize((done, total) => {
                self.postMessage({ type: 'progress', done, total });
            });
        })();
    }

    await initPromise;
}

self.onmessage = async (event) => {
    const { type } = event.data;

    try {
        if (type === 'init') {
            await ensureService();
            self.postMessage({ type: 'ready' });
            return;
        }

        if (type === 'recognize') {
            await ensureService();
            const { id, width, height, buffer } = event.data;
            const canvas = new OffscreenCanvas(width, height);
            const ctx = canvas.getContext('2d');
            const imageData = new ImageData(new Uint8ClampedArray(buffer), width, height);
            ctx.putImageData(imageData, 0, 0);

            const result = await service.recognize(canvas);
            const lines = (result.lines || []).map((line) => line.text).filter(Boolean);
            const text = (result.text || lines.join('\n')).trim();
            const confidence = typeof result.confidence === 'number'
                ? result.confidence
                : (lines.length
                    ? lines.reduce((sum, line) => sum + (line.confidence || 0), 0) / lines.length
                    : null);

            self.postMessage({
                type: 'result',
                id,
                text,
                confidence,
            });
        }
    } catch (error) {
        self.postMessage({
            type: 'error',
            message: error?.message || 'Falha no OCR em tempo real.',
        });
    }
};
