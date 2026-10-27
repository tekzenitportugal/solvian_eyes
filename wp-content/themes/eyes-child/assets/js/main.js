
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop,
                    behavior: 'smooth',
                });

                if (history.pushState) {
                    history.pushState(null, null, '#' + targetId);
                } else {
                    window.location.hash = '#' + targetId;
                }
            }
        });
    });

    const archive = document.querySelector('[data-concursos-archive]:not([data-dashboard-workspace])');

    if (!archive) {
        return;
    }

    const viewToggle = archive.querySelector('[data-view-toggle]');
    const resultsContainer = archive.querySelector('[data-results]');

    if (!viewToggle || !resultsContainer) {
        return;
    }

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

            resultsContainer.dataset.viewMode = viewMode;
            resultsContainer.classList.toggle('concursos-results--list', viewMode === 'list');
        });
    });
   

});

document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', function (e) {

        const toggle = e.target.closest('.password-toggle');
    
        if (!toggle) return;
    
        const input = toggle.parentElement.querySelector('input');
    
        if (!input) return;
    
        input.type = input.type === 'password' ? 'text' : 'password';
    
        toggle.setAttribute(
            'aria-label',
            input.type === 'password'
                ? 'Mostrar password'
                : 'Ocultar password'
        );
    
    });

});