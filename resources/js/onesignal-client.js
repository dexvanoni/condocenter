const bootstrapOneSignal = () => {
    if (typeof window === 'undefined') {
        return;
    }

    const runtime = window.AppOneSignal || {};
    if (!runtime.enabled || !runtime.userId || !window.OneSignal) {
        return;
    }

    window.OneSignal.push(async () => {
        try {
            const externalId = String(runtime.userId);
            await window.OneSignal.login(externalId);

            if (runtime.tags && typeof runtime.tags === 'object') {
                await window.OneSignal.sendTags(runtime.tags);
            }

            if (runtime.meta && runtime.meta.prompt) {
                const permission = await window.OneSignal.User.PushSubscription.getOptedIn();
                if (!permission) {
                    await window.OneSignal.Slidedown.promptPush(runtime.meta.prompt);
                }
            }
        } catch (error) {
            console.error('[OneSignal] Erro ao inicializar usuário:', error);
        }
    });
};

if (document.readyState === 'complete' || document.readyState === 'interactive') {
    bootstrapOneSignal();
} else {
    document.addEventListener('DOMContentLoaded', bootstrapOneSignal);
}

