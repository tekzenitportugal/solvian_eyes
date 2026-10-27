document.addEventListener('DOMContentLoaded', () => {
    const dashboardWorkspace = document.querySelector('[data-dashboard-workspace]');
    if (!dashboardWorkspace) return;

    const tabButtons = dashboardWorkspace.querySelectorAll('.dashboard-tabs__btn');
    const panelTitle = dashboardWorkspace.querySelector('[data-dashboard-panel-title]');
    const panelCount = dashboardWorkspace.querySelector('[data-dashboard-panel-count]');
    const panelLead = dashboardWorkspace.querySelector('[data-dashboard-panel-lead]');
    const resultsPanel = dashboardWorkspace.querySelector('[data-dashboard-results-panel]');
    const resultsContainer = dashboardWorkspace.querySelector('[data-dashboard-results]');
    const dashboardTerritorioMap = dashboardWorkspace.querySelector('[data-dashboard-map]');
    const dashboardTerritorioList = dashboardWorkspace.querySelector('[data-dashboard-territorio-list]');
    const viewToggle = dashboardWorkspace.querySelector('[data-view-toggle]');

    const applyViewMode = (viewMode) => {
        if (!resultsContainer || dashboardWorkspace.dataset.layout === 'territorio') {
            return;
        }

        resultsContainer.dataset.viewMode = viewMode;
        resultsContainer.classList.toggle('concursos-results--list', viewMode === 'list');
    };

    const updateResultsLayout = (layout) => {
        if (!resultsContainer) {
            return;
        }

        const isTerritorio = layout === 'territorio';

        resultsContainer.classList.toggle('dashboard-results--territorio', isTerritorio);

        if (isTerritorio) {
            resultsContainer.classList.add('concursos-results--list');
            resultsContainer.dataset.viewMode = 'list';
            return;
        }

        const activeViewButton = viewToggle
            ? viewToggle.querySelector('.view-toggle__btn.is-active')
            : null;
        const viewMode = activeViewButton?.dataset.view || resultsContainer.dataset.viewMode || 'grid';

        applyViewMode(viewMode);
    };

    const requestConcursosRefetch = (page = 1) => {
        dashboardWorkspace.dispatchEvent(
            new CustomEvent('eyes:concursos-refetch', {
                detail: { page },
            })
        );
    };

    tabButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            const clickedButton = event.currentTarget;
            const tabId = clickedButton.dataset.tab;
            const newPanelTitle = clickedButton.dataset.panelTitle;
            const newPanelCount = clickedButton.dataset.panelCount;
            const newPanelLead = clickedButton.dataset.panelLead || '';
            const newLayout = clickedButton.dataset.layout || 'default';

            tabButtons.forEach((btn) => {
                btn.classList.remove('is-active');
                btn.removeAttribute('aria-current');
            });

            clickedButton.classList.add('is-active');
            clickedButton.setAttribute('aria-current', 'page');

            if (panelTitle) {
                panelTitle.textContent = newPanelTitle;
            }
            if (panelCount) {
                panelCount.textContent = newPanelCount;
            }
            if (panelLead) {
                panelLead.textContent = newPanelLead;
                if (newPanelLead) {
                    panelLead.removeAttribute('hidden');
                } else {
                    panelLead.setAttribute('hidden', '');
                }
            }

            dashboardWorkspace.dataset.layout = newLayout;

            if (resultsPanel) {
                resultsPanel.dataset.layout = newLayout;
            }

            const isTerritorioLayout = newLayout === 'territorio';

            if (dashboardTerritorioMap) {
                if (isTerritorioLayout) {
                    dashboardTerritorioMap.removeAttribute('hidden');
                    dashboardTerritorioMap.setAttribute('aria-hidden', 'false');
                } else {
                    dashboardTerritorioMap.setAttribute('hidden', '');
                    dashboardTerritorioMap.setAttribute('aria-hidden', 'true');
                }
            }

            if (dashboardTerritorioList) {
                dashboardTerritorioList.style.display = '';
            }

            updateResultsLayout(newLayout);
            requestConcursosRefetch(1);

            const url = new URL(window.location);
            url.searchParams.set('dashboard_tab', tabId);
            window.history.pushState({}, '', url.toString());
        });
    });

    if (viewToggle && resultsContainer) {
        const viewToggleButtons = viewToggle.querySelectorAll('.view-toggle__btn');

        viewToggleButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                const clickedButton = event.currentTarget;
                const viewMode = clickedButton.dataset.view;

                viewToggleButtons.forEach((btn) => {
                    btn.classList.remove('is-active');
                    btn.setAttribute('aria-pressed', 'false');
                });

                clickedButton.classList.add('is-active');
                clickedButton.setAttribute('aria-pressed', 'true');

                applyViewMode(viewMode);
            });
        });
    }

});
