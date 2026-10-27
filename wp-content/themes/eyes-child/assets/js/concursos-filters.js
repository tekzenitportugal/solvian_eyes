const escapeHtml = (value) => {
    const element = document.createElement('div');
    element.textContent = value;
    return element.innerHTML;
};

document.addEventListener('DOMContentLoaded', () => {
    if (typeof eyesConcursos === 'undefined') {
        return;
    }

    document.querySelectorAll('[data-concursos-archive]').forEach((archive) => {
        initConcursosArchive(archive);
    });
});

function initConcursosArchive(archive) {
    const isDashboard = archive.hasAttribute('data-dashboard-workspace');
    const resultsContainer = archive.querySelector('[data-dashboard-results]') || archive.querySelector('[data-results]');
    const paginationNav = archive.querySelector('[data-concursos-pagination]') || archive.querySelector('[data-dashboard-pagination]');
    const countEl = archive.querySelector('[data-concursos-count]');
    const emptyState = archive.querySelector('[data-dashboard-empty]');
    const filterDropdowns = archive.querySelector('[data-filter-dropdowns]');
    const searchInput = archive.querySelector('[data-concursos-search-input]');
    const searchSuggestions = archive.querySelector('[data-concursos-search-suggestions]');
    const sortToggle = archive.querySelector('[data-sort-toggle]');

    if (!resultsContainer) {
        return;
    }

    const state = {
        page: 1,
        sort: 'recent',
        loading: false,
    };

    let filterDebounce = null;
    let suggestDebounce = null;
    let activeRequest = null;

    const getActiveDashboardTab = () => {
        const activeTab = archive.querySelector('.dashboard-tabs__btn.is-active');
        return activeTab?.dataset.tab || 'todos';
    };

    const getCheckedFilterValues = () => {
        const groups = {};

        if (!filterDropdowns) {
            return groups;
        }

        filterDropdowns.querySelectorAll('input[type="checkbox"]:checked').forEach((input) => {
            if (!groups[input.name]) {
                groups[input.name] = [];
            }
            groups[input.name].push(input.value);
        });

        return groups;
    };

    const buildRequestParams = () => {
        const params = new URLSearchParams();

        params.set('action', 'eyes_filter_concursos');

        const query = searchInput?.value.trim() || '';

        if (query) {
            params.set('q', query);
        }

        params.set('sort', state.sort);
        params.set('paged', String(state.page));

        if (isDashboard && eyesConcursos.isLoggedIn) {
            params.set('dashboard_tab', getActiveDashboardTab());
        }

        Object.entries(getCheckedFilterValues()).forEach(([name, values]) => {
            params.set(name, values.join(','));
        });

        return params;
    };

    const updateBrowserUrl = (params) => {
        const url = new URL(window.location.href);
        const preserved = new URLSearchParams();

        if (isDashboard) {
            const dashboardTab = params.get('dashboard_tab');
            if (dashboardTab) {
                preserved.set('dashboard_tab', dashboardTab);
            }
        }

        ['q', 'sort', 'paged'].forEach((key) => {
            const value = params.get(key);
            if (value && ('paged' !== key || value !== '1')) {
                preserved.set(key, value);
            }
        });

        Object.entries(getCheckedFilterValues()).forEach(([name, values]) => {
            if (values.length) {
                preserved.set(name, values.join(','));
            }
        });

        url.search = preserved.toString();
        window.history.replaceState({}, '', url.toString());
    };

    const updateCount = (found) => {
        if (!countEl) {
            return;
        }

        const template = eyesConcursos.i18n?.count || '%d encontradas';
        countEl.textContent = template.replace('%d', String(found));
    };

    const setLoading = (isLoading) => {
        state.loading = isLoading;
        resultsContainer.classList.toggle('is-loading', isLoading);
    };

    const setResults = (html, found, emptyStateData = null) => {
        if (isDashboard && emptyState && emptyStateData) {
            const titleEl = emptyState.querySelector('[data-dashboard-empty-title]');
            const textEl = emptyState.querySelector('[data-dashboard-empty-text]');
            const ctaEl = emptyState.querySelector('[data-dashboard-empty-cta]');

            if (titleEl && emptyStateData.title) {
                titleEl.textContent = emptyStateData.title;
            }

            if (textEl && emptyStateData.text) {
                textEl.textContent = emptyStateData.text;
            }

            if (ctaEl) {
                if (emptyStateData.cta_label) {
                    ctaEl.textContent = emptyStateData.cta_label;
                }
                if (emptyStateData.cta_url) {
                    ctaEl.setAttribute('href', emptyStateData.cta_url);
                }
                if (emptyStateData.type) {
                    emptyState.setAttribute('data-empty-type', emptyStateData.type);
                }
            }
        }

        if (isDashboard && emptyState) {
            if (found === 0) {
                resultsContainer.innerHTML = '';
                resultsContainer.setAttribute('hidden', '');
                emptyState.removeAttribute('hidden');
            } else {
                resultsContainer.innerHTML = html;
                resultsContainer.removeAttribute('hidden');
                emptyState.setAttribute('hidden', '');
            }
        } else {
            resultsContainer.innerHTML = html;
        }
    };

    const fetchResults = () => {
        if (state.loading) {
            return;
        }

        const params = buildRequestParams();

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();
        setLoading(true);

        fetch(`${eyesConcursos.ajaxUrl}?${params.toString()}`, {
            signal: activeRequest.signal,
            credentials: 'same-origin',
        })
            .then((response) => response.json())
            .then((response) => {
                if (!response.success) {
                    throw new Error('Filter request failed');
                }

                const data = response.data;

                setResults(data.html || '', data.found || 0, data.empty_state || null);
                updateCount(data.found || 0);

                if (paginationNav) {
                    if (data.pagination) {
                        paginationNav.innerHTML = data.pagination;
                        paginationNav.removeAttribute('hidden');
                    } else {
                        paginationNav.innerHTML = '';
                        paginationNav.setAttribute('hidden', '');
                    }
                }

                updateBrowserUrl(params);
            })
            .catch((error) => {
                if ('AbortError' === error.name) {
                    return;
                }

                console.error('Concursos filter error:', error);
                setResults(
                    `<p class="concursos-results__empty">${eyesConcursos.i18n?.loadError || 'Não foi possível carregar os concursos. Tente novamente.'}</p>`,
                    0
                );
            })
            .finally(() => {
                setLoading(false);
                activeRequest = null;
            });
    };

    const scheduleFetch = (delay = 0) => {
        window.clearTimeout(filterDebounce);
        filterDebounce = window.setTimeout(() => {
            fetchResults();
        }, delay);
    };

    const syncFiltersFromUrl = () => {
        const urlParams = new URLSearchParams(window.location.search);

        if (!filterDropdowns) {
            return;
        }

        filterDropdowns.querySelectorAll('input[type="checkbox"]').forEach((input) => {
            const raw = urlParams.get(input.name);
            if (!raw) {
                input.checked = false;
                return;
            }

            const values = raw.split(',');
            input.checked = values.includes(input.value);
        });

        if (searchInput) {
            searchInput.value = urlParams.get('q') || '';
        }

        const sort = urlParams.get('sort');
        if (sort && ['recent', 'relevance'].includes(sort)) {
            state.sort = sort;
        }

        const paged = parseInt(urlParams.get('paged') || '1', 10);
        state.page = Number.isNaN(paged) ? 1 : Math.max(1, paged);

        if (sortToggle) {
            sortToggle.querySelectorAll('.sort-toggle__btn').forEach((button) => {
                const isActive = button.dataset.sort === state.sort;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }
    };

    const initFilterDropdowns = () => {
        if (!filterDropdowns) {
            return;
        }

        const dropdowns = filterDropdowns.querySelectorAll('[data-filter-dropdown]');

        const closeAll = () => {
            dropdowns.forEach((dropdown) => {
                const trigger = dropdown.querySelector('.filter-dropdown__trigger');
                const panel = dropdown.querySelector('.filter-dropdown__panel');

                trigger?.classList.remove('is-open');
                trigger?.setAttribute('aria-expanded', 'false');
                panel?.classList.remove('filter-dropdown__panel--drop-up');
                panel?.setAttribute('hidden', '');
            });
        };

        dropdowns.forEach((dropdown) => {
            const trigger = dropdown.querySelector('.filter-dropdown__trigger');
            const panel = dropdown.querySelector('.filter-dropdown__panel');

            if (!trigger || !panel) {
                return;
            }

            trigger.addEventListener('click', (event) => {
                event.stopPropagation();
                const isOpen = !panel.hasAttribute('hidden');
                closeAll();

                if (!isOpen) {
                    panel.removeAttribute('hidden');
                    trigger.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');

                    if (panel.classList.contains('filter-dropdown__panel--cae')) {
                        panel.classList.remove('filter-dropdown__panel--drop-up');

                        window.requestAnimationFrame(() => {
                            const panelRect = panel.getBoundingClientRect();
                            const viewportPadding = 16;

                            if (panelRect.bottom > window.innerHeight - viewportPadding) {
                                panel.classList.add('filter-dropdown__panel--drop-up');
                            }
                        });
                    }
                }
            });
        });

        document.addEventListener('click', (event) => {
            if (!filterDropdowns.contains(event.target)) {
                closeAll();
            }
        });

        document.addEventListener('keydown', (event) => {
            if ('Escape' === event.key) {
                closeAll();
            }
        });
    };

    const fetchSearchSuggestions = (query) => {
        if (!searchSuggestions || !searchInput) {
            return;
        }

        if (!query) {
            searchSuggestions.innerHTML = '';
            searchSuggestions.setAttribute('hidden', '');
            searchInput.setAttribute('aria-expanded', 'false');
            return;
        }

        const params = new URLSearchParams({
            action: 'eyes_concurso_search_suggestions',
            q: query,
        });

        fetch(`${eyesConcursos.ajaxUrl}?${params.toString()}`, {
            credentials: 'same-origin',
        })
            .then((response) => response.json())
            .then((response) => {
                if (!response.success) {
                    return;
                }

                const items = response.data?.items || [];

                if (!items.length) {
                    searchSuggestions.innerHTML = `<p class="concursos-search-suggestions__empty">${eyesConcursos.i18n?.noResults || 'Nenhum concurso encontrado.'}</p>`;
                } else {
                    const listItems = items
                        .map((item) => {
                            const title = escapeHtml(item.title || '');
                            const meta = item.entity
                                ? `<span class="concursos-search-suggestions__meta">${escapeHtml(item.entity)}</span>`
                                : '';

                            return `<li class="concursos-search-suggestions__item" role="presentation">
                                <button type="button" class="concursos-search-suggestions__option" role="option" data-suggestion-title="${encodeURIComponent(item.title || '')}">
                                    <span class="concursos-search-suggestions__title">${title}</span>
                                    ${meta}
                                </button>
                            </li>`;
                        })
                        .join('');

                    searchSuggestions.innerHTML = `<ul class="concursos-search-suggestions__list" role="listbox">${listItems}</ul>`;
                }

                searchSuggestions.removeAttribute('hidden');
                searchInput.setAttribute('aria-expanded', 'true');
            })
            .catch((error) => {
                console.error('Search suggestions error:', error);
            });
    };

    initFilterDropdowns();
    syncFiltersFromUrl();

    if (filterDropdowns) {
        filterDropdowns.addEventListener('change', (event) => {
            if (!event.target.matches('input[type="checkbox"]')) {
                return;
            }

            state.page = 1;
            scheduleFetch();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();

            window.clearTimeout(suggestDebounce);
            suggestDebounce = window.setTimeout(() => {
                fetchSearchSuggestions(query);
            }, 250);

            state.page = 1;
            scheduleFetch(400);
        });

        searchInput.addEventListener('keydown', (event) => {
            if ('Escape' === event.key && searchSuggestions) {
                searchSuggestions.setAttribute('hidden', '');
                searchInput.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if (searchSuggestions) {
        searchSuggestions.addEventListener('click', (event) => {
            const option = event.target.closest('[data-suggestion-title]');

            if (!option || !searchInput) {
                return;
            }

            searchInput.value = decodeURIComponent(option.dataset.suggestionTitle || '');
            searchSuggestions.setAttribute('hidden', '');
            searchInput.setAttribute('aria-expanded', 'false');
            state.page = 1;
            scheduleFetch();
        });
    }

    if (sortToggle) {
        sortToggle.addEventListener('click', (event) => {
            const button = event.target.closest('.sort-toggle__btn');

            if (!button) {
                return;
            }

            const sort = button.dataset.sort;

            if (!sort || sort === state.sort) {
                return;
            }

            state.sort = sort;
            state.page = 1;

            sortToggle.querySelectorAll('.sort-toggle__btn').forEach((btn) => {
                const isActive = btn === button;
                btn.classList.toggle('is-active', isActive);
                btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            scheduleFetch();
        });
    }

    if (paginationNav) {
        paginationNav.addEventListener('click', (event) => {
            const link = event.target.closest('a');

            if (!link) {
                return;
            }

            event.preventDefault();

            let page = 1;
            const linkUrl = new URL(link.href, window.location.origin);
            const pagedParam = linkUrl.searchParams.get('paged');

            if (pagedParam) {
                page = parseInt(pagedParam, 10);
            } else {
                const pathMatch = linkUrl.pathname.match(/\/page\/(\d+)\/?$/);
                if (pathMatch) {
                    page = parseInt(pathMatch[1], 10);
                }
            }

            state.page = Number.isNaN(page) ? 1 : Math.max(1, page);
            scheduleFetch();
            resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    archive.addEventListener('eyes:concursos-refetch', (event) => {
        state.page = event.detail?.page || 1;
        scheduleFetch();
    });

    document.addEventListener('eyes:favourite-toggled', () => {
        if (!isDashboard || getActiveDashboardTab() !== 'guardadas') {
            return;
        }

        state.page = 1;
        scheduleFetch();
    });
}
