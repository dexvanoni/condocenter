function switchProfile(roleName) {
            fetch('https://192.168.0.4:8000/profile/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ role: roleName }),
                credentials: 'same-origin',
            })
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : {};

                if (!response.ok) {
                    throw new Error(data.message || 'Erro ao trocar perfil');
                }

                if (data.success) {
                    window.location.href = data.redirect || 'https://192.168.0.4:8000/dashboard';
                    return;
                }

                throw new Error(data.message || 'Erro ao trocar perfil');
            })
            .catch(error => {
                console.error('Erro:', error);
                alert(error.message || 'Erro ao trocar perfil');
            });
        }