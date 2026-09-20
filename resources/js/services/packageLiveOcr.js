let worker = null;
let readyPromise = null;

function getWorker() {
    if (!worker) {
        worker = new Worker(
            new URL('../workers/package-live-ocr.worker.js', import.meta.url),
            { type: 'module' },
        );
    }

    return worker;
}

export function initPackageLiveOcr(onProgress) {
    if (!readyPromise) {
        const w = getWorker();
        readyPromise = new Promise((resolve, reject) => {
            const handler = (event) => {
                const { type, done, total, message } = event.data || {};
                if (type === 'progress' && onProgress) {
                    onProgress({ done, total });
                }
                if (type === 'ready') {
                    w.removeEventListener('message', handler);
                    resolve(w);
                }
                if (type === 'error') {
                    w.removeEventListener('message', handler);
                    reject(new Error(message || 'Falha ao iniciar PaddleOCR.js'));
                }
            };
            w.addEventListener('message', handler);
            w.postMessage({ type: 'init' });
        });
    }

    return readyPromise;
}

export function recognizeLabelFrame(canvas) {
    return readyPromise.then((w) => new Promise((resolve, reject) => {
        const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
        const ctx = canvas.getContext('2d');
        const { width, height } = canvas;
        const imageData = ctx.getImageData(0, 0, width, height);
        const buffer = imageData.data.buffer.slice(0);

        const handler = (event) => {
            const data = event.data || {};
            if (data.type === 'result' && data.id === id) {
                w.removeEventListener('message', handler);
                resolve({
                    text: data.text || '',
                    confidence: data.confidence ?? null,
                });
            }
            if (data.type === 'error') {
                w.removeEventListener('message', handler);
                reject(new Error(data.message || 'Falha na leitura do frame.'));
            }
        };

        w.addEventListener('message', handler);
        w.postMessage({
            type: 'recognize',
            id,
            width,
            height,
            buffer,
        }, [buffer]);
    }));
}

export function destroyPackageLiveOcr() {
    if (worker) {
        worker.terminate();
        worker = null;
        readyPromise = null;
    }
}
