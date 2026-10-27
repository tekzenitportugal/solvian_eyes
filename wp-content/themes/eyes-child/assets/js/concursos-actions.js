(function () {
    'use strict';

    if (typeof eyesAuth === 'undefined') {
        return;
    }

    document.addEventListener('click', (event) => {
        const favouriteBtn = event.target.closest('[data-favourite-toggle]');

        if (favouriteBtn) {
            event.preventDefault();
            event.stopPropagation();
            handleFavouriteToggle(favouriteBtn);
            return;
        }

        const applyLink = event.target.closest('[data-mark-applied]');

        if (applyLink) {
            handleApplyClick(event, applyLink);
            return;
        }

        const consultBtn = event.target.closest('[data-consult-request]');

        if (consultBtn) {
            event.preventDefault();
            event.stopPropagation();
            handleConsultRequest(consultBtn);
        }
    });

    function handleFavouriteToggle(button) {
        if (!eyesAuth.isLoggedIn) {
            window.location.href = button.dataset.loginUrl || eyesAuth.loginUrl;
            return;
        }

        if (button.dataset.loading === 'true') {
            return;
        }

        const concursoId = button.dataset.concursoId;

        if (!concursoId) {
            return;
        }

        button.dataset.loading = 'true';

        const formData = new FormData();
        formData.append('action', 'eyes_toggle_favourite');
        formData.append('nonce', eyesAuth.nonce);
        formData.append('concurso_id', concursoId);

        fetch(eyesAuth.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
            .then((response) => response.json())
            .then((response) => {
                if (!response.success) {
                    throw new Error(response.data?.message || 'Favourite toggle failed');
                }

                const saved = Boolean(response.data.saved);
                updateFavouriteButton(button, saved);

                if (Array.isArray(response.data.favourites)) {
                    eyesAuth.favourites = response.data.favourites;
                }

                if (typeof response.data.saved_count === 'number') {
                    updateDashboardSavedCount(response.data.saved_count);
                }

                document.dispatchEvent(
                    new CustomEvent('eyes:favourite-toggled', {
                        detail: {
                            concursoId: Number(concursoId),
                            saved,
                        },
                    })
                );
            })
            .catch((error) => {
                console.error('Favourite toggle error:', error);
                window.alert(eyesAuth.i18n?.saveError || 'Não foi possível guardar. Tente novamente.');
            })
            .finally(() => {
                delete button.dataset.loading;
            });
    }

    function updateFavouriteButton(button, saved) {
        const icon = button.querySelector('img');

        button.classList.toggle('is-favourite', saved);
        button.setAttribute('aria-pressed', saved ? 'true' : 'false');

        if (icon) {
            icon.src = saved ? eyesAuth.starIconActive : eyesAuth.starIcon;
        }
    }

    function updateDashboardSavedCount(count) {
        const savedStat = document.querySelector('[data-dashboard-stat-saved]');

        if (savedStat) {
            savedStat.textContent = String(count);
        }

        const guardadasTab = document.querySelector('.dashboard-tabs__btn[data-tab="guardadas"]');

        if (guardadasTab) {
            const template = eyesAuth.i18n?.tabCount || '%d encontradas';
            guardadasTab.dataset.panelCount = template.replace('%d', String(count));
        }
    }

    function handleApplyClick(event, link) {
        if (!eyesAuth.isLoggedIn) {
            return;
        }

        if (link.dataset.applied === 'true') {
            return;
        }

        const concursoId = link.dataset.concursoId;
        const applyUrl = link.getAttribute('href');

        if (!concursoId || !applyUrl || applyUrl === '#') {
            return;
        }

        event.preventDefault();

        if (link.dataset.loading === 'true') {
            return;
        }

        link.dataset.loading = 'true';

        const formData = new FormData();
        formData.append('action', 'eyes_mark_concurso_applied');
        formData.append('nonce', eyesAuth.actionsNonce);
        formData.append('concurso_id', concursoId);

        fetch(eyesAuth.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
            .then((response) => response.json())
            .then((response) => {
                if (!response.success) {
                    throw new Error(response.data?.message || 'Apply mark failed');
                }

                link.textContent = eyesAuth.i18n?.appliedLabel || 'Candidatura registada';
                link.dataset.applied = 'true';
                window.open(applyUrl, '_blank', 'noopener,noreferrer');
            })
            .catch((error) => {
                console.error('Apply mark error:', error);
                window.alert(eyesAuth.i18n?.applyError || 'Não foi possível registar a candidatura. Tente novamente.');
                window.open(applyUrl, '_blank', 'noopener,noreferrer');
            })
            .finally(() => {
                delete link.dataset.loading;
            });
    }

    function handleConsultRequest(button) {
        if (!eyesAuth.isLoggedIn) {
            window.location.href = button.dataset.loginUrl || eyesAuth.loginUrl;
            return;
        }

        if (button.disabled || button.dataset.loading === 'true') {
            return;
        }

        const concursoId = button.dataset.concursoId;

        if (!concursoId) {
            return;
        }

        button.dataset.loading = 'true';

        const formData = new FormData();
        formData.append('action', 'eyes_submit_consult_request');
        formData.append('nonce', eyesAuth.actionsNonce);
        formData.append('concurso_id', concursoId);

        fetch(eyesAuth.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
            .then((response) => response.json())
            .then((response) => {
                if (!response.success) {
                    throw new Error(response.data?.message || 'Consult request failed');
                }

                button.disabled = true;
                button.setAttribute('aria-disabled', 'true');

                const label = button.querySelector('.concurso-detail-consult__btn-label');

                if (label) {
                    label.textContent = eyesAuth.i18n?.consultSentLabel || 'Pedido enviado';
                }

                const banner = document.querySelector('[data-consult-success-banner]');

                if (banner) {
                    banner.removeAttribute('hidden');
                }
            })
            .catch((error) => {
                console.error('Consult request error:', error);
                window.alert(eyesAuth.i18n?.consultError || 'Não foi possível enviar o pedido. Tente novamente.');
            })
            .finally(() => {
                delete button.dataset.loading;
            });
    }
})();
