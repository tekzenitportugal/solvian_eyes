(function () {
	'use strict';

	var config = window.eyesPlanos || {};
	var root = document.querySelector('[data-planos-root]');

	if (!root) {
		return;
	}

	var currentPlan = root.getAttribute('data-planos-current') || 'guest';
	var billing = root.getAttribute('data-planos-billing') || 'monthly';
	var selectedPlan = '';
	var summary = config.summary || {};

	var plansPanel = root.querySelector('[data-planos-panel="plans"]');
	var checkoutPanel = root.querySelector('[data-planos-panel="checkout"]');
	var successPanel = root.querySelector('[data-planos-panel="success"]');
	var advancedPrice = root.querySelector('[data-planos-advanced-price]');

	function setView(view) {
		root.setAttribute('data-planos-view', view);
	}

	function setBilling(period) {
		billing = period === 'annual' ? 'annual' : 'monthly';
		root.setAttribute('data-planos-billing', billing);

		root.querySelectorAll('[data-planos-billing-toggle]').forEach(function (btn) {
			btn.classList.toggle('is-active', btn.getAttribute('data-planos-billing-toggle') === billing);
		});

		root.querySelectorAll('[data-planos-period-input]').forEach(function (input) {
			input.checked = input.value === billing;
			var card = input.closest('.planos-period-card');
			if (card) {
				card.classList.toggle('is-active', input.checked);
			}
		});

		updateAdvancedPrice();
	}

	function updateAdvancedPrice() {
		if (!advancedPrice) {
			return;
		}

		if (billing === 'annual') {
			advancedPrice.innerHTML =
				'<span class="planos-card__price-value">300</span>' +
				'<span class="planos-card__price-currency">€</span>' +
				'<span class="planos-card__price-period">/ano</span>';
		} else {
			advancedPrice.innerHTML =
				'<span class="planos-card__price-value">30</span>' +
				'<span class="planos-card__price-currency">€</span>' +
				'<span class="planos-card__price-period">/mês</span>';
		}
	}

	function redirect(url) {
		window.location.href = url;
	}

	function handlePlanSelect(plan) {
		if (plan === currentPlan) {
			return;
		}

		if (plan === 'guest') {
			return;
		}

		if (plan === 'free') {
			if (!config.isLoggedIn) {
				redirect(config.registoUrl);
				return;
			}

			if (currentPlan === 'advanced') {
				savePlan('free', billing, true);
			}

			return;
		}

		if (plan === 'advanced') {
			selectedPlan = 'advanced';

			if (!config.isLoggedIn) {
				var returnUrl =
					config.planosUrl +
					'?step=checkout&billing=' +
					encodeURIComponent(billing);
				var loginUrl =
					config.loginUrl +
					(config.loginUrl.indexOf('?') === -1 ? '?' : '&') +
					'redirect_to=' +
					encodeURIComponent(returnUrl);

				redirect(loginUrl);
				return;
			}

			setView('checkout');
		}
	}

	function populateSuccess(data) {
		var fields = {
			'data-planos-success-billing': data.billing_label,
			'data-planos-success-price': data.price_label,
			'data-planos-success-company': data.company,
			'data-planos-success-nif': data.nif,
			'data-planos-success-address': data.address
		};

		Object.keys(fields).forEach(function (attr) {
			var node = root.querySelector('[' + attr + ']');
			if (node) {
				node.textContent = fields[attr] || '—';
			}
		});
	}

	function savePlan(plan, period, reload) {
		if (!config.ajaxurl) {
			window.alert(config.errorMessage);
			return;
		}

		var formData = new FormData();
		formData.append('action', 'eyes_select_plan');
		formData.append('nonce', config.nonce);
		formData.append('plan', plan);
		formData.append('billing', period);

		fetch(config.ajaxurl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (payload) {
				if (!payload || !payload.success) {
					var message =
						payload && payload.data && payload.data.message
							? payload.data.message
							: config.errorMessage;
					window.alert(message);
					return;
				}

				if (reload) {
					window.location.href = config.planosUrl;
					return;
				}

				currentPlan = plan;
				summary = payload.data.summary || summary;
				populateSuccess(summary);
				setView('success');
			})
			.catch(function () {
				window.alert(config.errorMessage);
			});
	}

	root.addEventListener('click', function (event) {
		var billingBtn = event.target.closest('[data-planos-billing-toggle]');
		if (billingBtn) {
			event.preventDefault();
			setBilling(billingBtn.getAttribute('data-planos-billing-toggle'));
			return;
		}

		var selectBtn = event.target.closest('[data-planos-select]');
		if (selectBtn && !selectBtn.disabled) {
			event.preventDefault();
			handlePlanSelect(selectBtn.getAttribute('data-planos-select'));
			return;
		}

		var backBtn = event.target.closest('[data-planos-back]');
		if (backBtn) {
			event.preventDefault();
			setView('plans');
			return;
		}


var confirmBtn = event.target.closest('[data-planos-confirm]');

if (confirmBtn) {
	event.preventDefault();

	var selected = root.querySelector('input[name="planos_period"]:checked');

	if (!selected) {
		console.log('No plan selected');
		return;
	}

	var url = selected.defaultValue;
    
	console.log('Checkout URL:', url);

	if (!url) {
		console.log('No checkout URL found');
		return;
	}
  if(url == 'annual'){
		  window.location.href = 'https://solvian.pt/eyes/membership-checkout/?pmpro_level=3';
	  } else {
		    window.location.href = 'https://solvian.pt/eyes/membership-checkout/?pmpro_level=4';
	  }
	//window.location.href = url;
	return;
}
	});

	root.addEventListener('change', function (event) {
		var periodInput = event.target.closest('[data-planos-period-input]');
		if (periodInput) {
			setBilling(periodInput.value);
		}
	});

	if (config.initialView === 'checkout' && config.isLoggedIn) {
		setView('checkout');
	} else if (config.initialView === 'success' && config.isLoggedIn && currentPlan === 'advanced') {
		populateSuccess(summary);
		setView('success');
	} else {
		setView('plans');
	}

	setBilling(billing);
})();
