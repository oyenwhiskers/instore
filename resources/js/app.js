import './bootstrap';

const copyTimers = new WeakMap();

document.addEventListener('click', async (event) => {
	const target = event.target;
	if (!(target instanceof Element)) {
		return;
	}

	const button = target.closest('[data-copy]');
	if (!(button instanceof HTMLElement)) {
		return;
	}

	const text = button.getAttribute('data-copy');
	if (!text) {
		return;
	}

	const originalLabel = button.getAttribute('data-copy-label') || 'Copy';
	const copiedLabel = button.getAttribute('data-copied-label') || 'Copied';
	const existingTimer = copyTimers.get(button);
	if (existingTimer) {
		clearTimeout(existingTimer);
	}

	button.classList.remove('is-copied');
	void button.offsetWidth;
	button.classList.add('is-copied');
	button.setAttribute('title', copiedLabel);
	button.setAttribute('aria-label', copiedLabel);
	button.setAttribute('aria-live', 'polite');

	const attemptClipboard = async () => {
		if (!navigator.clipboard || !navigator.clipboard.writeText) {
			throw new Error('Clipboard API unavailable');
		}

		const timeout = new Promise((_, reject) => {
			setTimeout(() => reject(new Error('Clipboard timeout')), 1200);
		});

		await Promise.race([navigator.clipboard.writeText(text), timeout]);
	};

	const fallbackCopy = () => {
		const textarea = document.createElement('textarea');
		textarea.value = text;
		textarea.setAttribute('readonly', '');
		textarea.style.position = 'absolute';
		textarea.style.left = '-9999px';
		document.body.appendChild(textarea);
		textarea.select();
		document.execCommand('copy');
		document.body.removeChild(textarea);
	};

	attemptClipboard().catch(() => fallbackCopy());
	const timerId = setTimeout(() => {
		button.classList.remove('is-copied');
		button.setAttribute('title', originalLabel);
		button.setAttribute('aria-label', originalLabel);
		button.removeAttribute('aria-live');
		copyTimers.delete(button);
	}, 1200);
	copyTimers.set(button, timerId);
});

const initLocationMap = () => {
	if (!window.google || !window.google.maps) {
		return;
	}

	const mapElements = document.querySelectorAll('[data-map="location"]');
	if (!mapElements.length) {
		return;
	}

	mapElements.forEach((mapElement) => {
		if (!(mapElement instanceof HTMLElement)) {
			return;
		}

		const form = mapElement.closest('form');
		if (!form) {
			return;
		}

		const latField = mapElement.dataset.latField;
		const lngField = mapElement.dataset.lngField;
		const addressField = mapElement.dataset.addressField;
		const searchInputId = mapElement.dataset.searchInput;
		const radiusField = mapElement.dataset.radiusField;

		const latInput = latField ? form.querySelector(`[name="${latField}"]`) : null;
		const lngInput = lngField ? form.querySelector(`[name="${lngField}"]`) : null;
		const addressInput = addressField ? form.querySelector(`[name="${addressField}"]`) : null;
		const searchInput = searchInputId ? document.getElementById(searchInputId) : null;
		const radiusInput = radiusField ? form.querySelector(`[name="${radiusField}"]`) : null;
		const countryInput = form.querySelector('[name="country"]');
		const stateInput = form.querySelector('[name="state"]');
		const districtInput = form.querySelector('[name="district"]');

		const defaultLat = parseFloat(mapElement.dataset.lat || '3.1390');
		const defaultLng = parseFloat(mapElement.dataset.lng || '101.6869');
		const hasCoords = !Number.isNaN(defaultLat) && !Number.isNaN(defaultLng);

		const map = new window.google.maps.Map(mapElement, {
			center: { lat: defaultLat, lng: defaultLng },
			zoom: hasCoords ? 15 : 12,
			mapTypeControl: true,
			streetViewControl: false,
			fullscreenControl: false,
		});

		const marker = new window.google.maps.Marker({
			map,
			position: { lat: defaultLat, lng: defaultLng },
			draggable: true,
			visible: hasCoords,
		});

		const circle = new window.google.maps.Circle({
			map,
			center: { lat: defaultLat, lng: defaultLng },
			radius: radiusInput?.value ? Number(radiusInput.value) : 0,
			fillColor: '#1e4f8f',
			fillOpacity: 0.15,
			strokeColor: '#1e4f8f',
			strokeOpacity: 0.5,
			strokeWeight: 1,
			visible: !!radiusInput?.value,
		});

		const geocoder = new window.google.maps.Geocoder();

		const setAutofillValue = (input, value) => {
			if (!input || !value || input.dataset.autofill === 'off') {
				return;
			}
			input.value = value;
		};

		const attachAutofillGuard = (input) => {
			if (!input) {
				return;
			}
			input.dataset.autofill = 'on';
			input.addEventListener('input', () => {
				input.dataset.autofill = 'off';
			});
		};

		const findComponent = (components, types) => {
			if (!components || !components.length) {
				return null;
			}
			return components.find((component) => types.some((type) => component.types.includes(type))) || null;
		};

		const applyRegionFields = (components) => {
			const countryValue = findComponent(components, ['country'])?.long_name || null;
			const stateValue = findComponent(components, ['administrative_area_level_1'])?.long_name || null;
			let districtValue = findComponent(components, ['administrative_area_level_2'])?.long_name || null;
			if (!districtValue) {
				districtValue = findComponent(components, [
					'locality',
					'postal_town',
				])?.long_name || null;
			}
			if (!districtValue && countryValue !== 'Malaysia') {
				districtValue = findComponent(components, [
					'sublocality',
					'sublocality_level_1',
				])?.long_name || null;
			}

			setAutofillValue(countryInput, countryValue);
			setAutofillValue(stateInput, stateValue);
			setAutofillValue(districtInput, districtValue);
		};

		const updateFields = (location, address, nameValue, components) => {
			if (latInput) {
				latInput.value = location.lat().toFixed(6);
			}
			if (lngInput) {
				lngInput.value = location.lng().toFixed(6);
			}
			if (addressInput && address) {
				addressInput.value = address;
			}
			const nameInput = form.querySelector('[name="name"]');
			if (nameInput) {
				setAutofillValue(nameInput, nameValue);
			}
			if (components) {
				applyRegionFields(components);
			}
			circle.setCenter(location);
		};

		const nameInput = form.querySelector('[name="name"]');
		attachAutofillGuard(nameInput);
		attachAutofillGuard(countryInput);
		attachAutofillGuard(stateInput);
		attachAutofillGuard(districtInput);

		const reverseGeocode = (location) => {
			geocoder.geocode({ location }, (results, status) => {
				if (status === 'OK' && results && results[0]) {
					const placeName = results[0].address_components?.[0]?.long_name || null;
					updateFields(location, results[0].formatted_address, placeName, results[0].address_components || null);
				} else {
					updateFields(location, null, null, null);
				}
			});
		};

		map.addListener('click', (event) => {
			if (!event.latLng) {
				return;
			}
			marker.setPosition(event.latLng);
			marker.setVisible(true);
			reverseGeocode(event.latLng);
		});

		marker.addListener('dragend', () => {
			const position = marker.getPosition();
			if (!position) {
				return;
			}
			reverseGeocode(position);
		});

		if (radiusInput) {
			const applyRadius = () => {
				const radiusValue = Number(radiusInput.value || 0);
				circle.setRadius(radiusValue);
				circle.setVisible(radiusValue > 0);
			};
			radiusInput.addEventListener('input', applyRadius);
			radiusInput.addEventListener('change', applyRadius);
		}

		if (searchInput) {
			const searchBox = new window.google.maps.places.SearchBox(searchInput);
			map.addListener('bounds_changed', () => {
				searchBox.setBounds(map.getBounds());
			});

			searchBox.addListener('places_changed', () => {
				const places = searchBox.getPlaces();
				if (!places || !places.length) {
					return;
				}

				const place = places[0];
				if (!place.geometry || !place.geometry.location) {
					return;
				}

				const location = place.geometry.location;
				marker.setPosition(location);
				marker.setVisible(true);
				map.panTo(location);
				map.setZoom(16);
				updateFields(location, place.formatted_address || null, place.name || null, place.address_components || null);
				if (!place.address_components) {
					reverseGeocode(location);
				}
			});
		}
	});
};

const initTabs = () => {
	const tabGroups = document.querySelectorAll('[data-tabs]');
	if (!tabGroups.length) {
		return;
	}

	tabGroups.forEach((group) => {
		const buttons = group.querySelectorAll('[data-tab-target]');
		const groupId = group.dataset.tabGroup;
		const panels = groupId
			? document.querySelectorAll(`[data-tab-panel][data-tab-group="${groupId}"]`)
			: group.querySelectorAll('[data-tab-panel]');
		if (!buttons.length || !panels.length) {
			return;
		}

		const activateTab = (target) => {
			buttons.forEach((button) => {
				button.classList.toggle('is-active', button.dataset.tabTarget === target);
				button.setAttribute('aria-selected', button.dataset.tabTarget === target ? 'true' : 'false');
			});
			panels.forEach((panel) => {
				panel.classList.toggle('is-hidden', panel.dataset.tabPanel !== target);
			});

			if (groupId) {
				document.querySelectorAll(`[data-tab-store="${groupId}"]`).forEach((input) => {
					input.value = target;
				});
			}

			if (groupId === 'product-views') {
				const actions = group.closest('.filters-actions');
				if (actions) {
					actions.classList.toggle('is-manage', target === 'manage');
				}
			}
		};

		buttons.forEach((button) => {
			button.addEventListener('click', () => activateTab(button.dataset.tabTarget));
		});

		const defaultTarget = group.dataset.defaultTab || buttons[0].dataset.tabTarget;
		activateTab(defaultTarget);
	});
};

const initAutoFilters = () => {
	const forms = document.querySelectorAll('[data-auto-filter]');
	if (!forms.length) {
		return;
	}

	forms.forEach((form) => {
		const submitAjax = () => {
			if (form.dataset.loading === 'true') {
				return;
			}

			const targetSelector = form.dataset.filterTarget;
			if (!targetSelector) {
				if (typeof form.requestSubmit === 'function') {
					form.requestSubmit();
					return;
				}
				form.submit();
				return;
			}

			const url = new URL(form.action, window.location.origin);
			const formData = new FormData(form);
			formData.forEach((value, key) => {
				if (value === null || value === '') {
					url.searchParams.delete(key);
					return;
				}
				url.searchParams.set(key, value);
			});

			form.dataset.loading = 'true';
			fetch(url.toString(), {
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
				},
			})
				.then((response) => response.text())
				.then((html) => {
					const doc = new DOMParser().parseFromString(html, 'text/html');
					const nextTarget = doc.querySelector(targetSelector);
					const currentTarget = document.querySelector(targetSelector);
					if (nextTarget && currentTarget) {
						currentTarget.outerHTML = nextTarget.outerHTML;
					}
					window.history.replaceState({}, '', url);
					initTabs();
					initAutoFilters();
				})
				.catch(() => {
					if (typeof form.requestSubmit === 'function') {
						form.requestSubmit();
						return;
					}
					form.submit();
				})
				.finally(() => {
					form.dataset.loading = 'false';
				});
		};

		let timerId;
		const submitForm = () => {
			if (timerId) {
				clearTimeout(timerId);
			}
			submitAjax();
		};

		form.addEventListener('submit', (event) => {
			event.preventDefault();
			submitAjax();
		});

		form.querySelectorAll('input, select').forEach((field) => {
			const tag = field.tagName.toLowerCase();
			const type = field.getAttribute('type') || '';
			const isText = tag === 'input' && (type === 'text' || type === 'search' || type === '');

			if (isText) {
				field.addEventListener('input', () => {
					if (timerId) {
						clearTimeout(timerId);
					}
					timerId = setTimeout(submitForm, 350);
				});
				return;
			}

			field.addEventListener('change', submitForm);
		});
	});
};

const initKpiEditing = () => {
	document.addEventListener('click', (event) => {
		const target = event.target;
		if (!(target instanceof Element)) {
			return;
		}

		const editBtn = target.closest('[data-kpi-edit]');
		const cancelBtn = target.closest('[data-kpi-cancel]');

		if (editBtn) {
			const row = editBtn.closest('[data-kpi-row]');
			if (!row) {
				return;
			}

			const cells = row.querySelectorAll('[data-kpi-cell]');
			const cancel = row.querySelector('[data-kpi-cancel]');

			// Switch to edit mode: hide spans, show inputs
			cells.forEach((cell) => {
				const viewSpan = cell.querySelector('[data-kpi-view]');
				const input = cell.querySelector('[data-kpi-input]');
				
				if (viewSpan && input) {
					// Store original value
					input.dataset.originalValue = input.value;
					
					// Toggle visibility
					viewSpan.style.display = 'none';
					input.style.display = 'block';
					input.focus();
				}
			});

			// Toggle buttons
			editBtn.style.display = 'none';
			if (cancel) {
				cancel.style.display = 'inline-block';
			}
		}

		if (cancelBtn) {
			const row = cancelBtn.closest('[data-kpi-row]');
			if (!row) {
				return;
			}

			const cells = row.querySelectorAll('[data-kpi-cell]');
			const edit = row.querySelector('[data-kpi-edit]');

			// Switch to view mode: show spans, hide inputs, restore values
			cells.forEach((cell) => {
				const viewSpan = cell.querySelector('[data-kpi-view]');
				const input = cell.querySelector('[data-kpi-input]');
				
				if (viewSpan && input) {
					// Restore original value
					if (input.dataset.originalValue !== undefined) {
						input.value = input.dataset.originalValue;
						viewSpan.textContent = input.dataset.originalValue || '-';
						delete input.dataset.originalValue;
					}
					
					// Toggle visibility
					viewSpan.style.display = 'block';
					input.style.display = 'none';
				}
			});

			// Toggle buttons
			cancelBtn.style.display = 'none';
			if (edit) {
				edit.style.display = 'inline-block';
			}
		}
	});
};

window.__initLocationMap = initLocationMap;
if (window.__mapsReady) {
	window.__initLocationMap();
}

document.addEventListener('DOMContentLoaded', () => {
	if (window.google && window.google.maps) {
		initLocationMap();
	}
	initTabs();
	initAutoFilters();
	initKpiEditing();
});
