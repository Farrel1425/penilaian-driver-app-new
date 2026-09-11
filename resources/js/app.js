import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import lottie from 'lottie-web/build/player/lottie_light';
import QrScanner from 'qr-scanner';
import '../css/passenger.css';

document.addEventListener('DOMContentLoaded', () => {
    const categorySelect = document.querySelector('[data-employee-category]');
    const simSection = document.querySelector('[data-employee-sim-section]');

    if (categorySelect instanceof HTMLSelectElement && simSection instanceof HTMLElement) {
        const syncSimSection = () => {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            simSection.hidden = selectedOption?.dataset.requiresSim !== 'true';
        };

        categorySelect.addEventListener('change', syncSimSection);
        syncSimSection();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-app-loading-animation]').forEach((container) => {
        lottie.loadAnimation({
            autoplay: true,
            container,
            loop: true,
            path: container.dataset.appLoadingAnimation,
            renderer: 'svg',
            rendererSettings: {
                preserveAspectRatio: 'xMidYMid meet',
            },
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const sidebarCollapse = document.querySelector('[data-sidebar-collapse]');
    const adminFrame = document.querySelector('.admin-frame');
    const passwordToggle = document.querySelector('[data-password-toggle]');
    const passwordInput = document.querySelector('[data-password-input]');
    const profileMenu = document.querySelector('[data-profile-menu]');
    const profileTrigger = document.querySelector('[data-profile-trigger]');
    const notificationMenu = document.querySelector('[data-notification-menu]');

    toggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('is-open');
    });

    const setSidebarCollapsed = (isCollapsed) => {
        adminFrame?.classList.toggle('is-sidebar-collapsed', isCollapsed);
        sidebarCollapse?.setAttribute('aria-expanded', String(!isCollapsed));
        sidebarCollapse?.setAttribute('aria-label', isCollapsed ? 'Buka navigasi samping' : 'Tutup navigasi samping');
        sidebarCollapse?.setAttribute('title', isCollapsed ? 'Buka navigasi samping' : 'Tutup navigasi samping');
        window.localStorage.setItem('admin-sidebar-collapsed', String(isCollapsed));
    };

    if (window.matchMedia('(min-width: 1024px)').matches) {
        setSidebarCollapsed(window.localStorage.getItem('admin-sidebar-collapsed') === 'true');
    }

    sidebarCollapse?.addEventListener('click', () => {
        setSidebarCollapsed(!adminFrame?.classList.contains('is-sidebar-collapsed'));
    });

    passwordToggle?.addEventListener('click', () => {
        const isVisible = passwordInput?.type === 'text';

        if (!passwordInput) {
            return;
        }

        passwordInput.type = isVisible ? 'password' : 'text';
        passwordToggle.classList.toggle('is-visible', !isVisible);
        passwordToggle.setAttribute('aria-pressed', String(!isVisible));
        passwordToggle.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
    });

    profileMenu?.addEventListener('toggle', () => {
        const isOpen = profileMenu.open;
        profileMenu.classList.toggle('is-open', isOpen);
        profileTrigger?.setAttribute('aria-expanded', String(isOpen));
    });

    notificationMenu?.addEventListener('toggle', () => {
        if (!notificationMenu.open || !notificationMenu.querySelector('[data-notification-indicator]')) {
            return;
        }

        fetch(notificationMenu.dataset.notificationReadUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
        }).then((response) => {
            if (!response.ok) {
                return;
            }

            notificationMenu.querySelector('[data-notification-indicator]')?.remove();
            const count = notificationMenu.querySelector('[data-notification-count]');
            if (count) {
                count.textContent = 'Sudah dibaca';
            }
        });
    });

    const dashboardFilterForm = document.querySelector('[data-dashboard-filter-form]');
    dashboardFilterForm?.querySelectorAll('[data-dashboard-filter]').forEach((control) => {
        control.addEventListener('change', () => dashboardFilterForm.requestSubmit());
    });

    const dashboardSearchForm = document.querySelector('[data-dashboard-search-form]');
    const dashboardSearch = dashboardSearchForm?.querySelector('[data-dashboard-search]');
    let dashboardSearchTimeout;

    dashboardSearch?.addEventListener('input', () => {
        window.clearTimeout(dashboardSearchTimeout);
        dashboardSearchTimeout = window.setTimeout(() => dashboardSearchForm.requestSubmit(), 500);
    });

    const driverFilterForm = document.querySelector('[data-driver-filter-form]');
    driverFilterForm?.querySelectorAll('[data-driver-filter]').forEach((control) => {
        control.addEventListener('change', () => driverFilterForm.requestSubmit());
    });

    const driverSearchForm = document.querySelector('[data-driver-search-form]');
    const driverSearch = driverSearchForm?.querySelector('[data-driver-search]');
    let driverSearchTimeout;

    driverSearch?.addEventListener('input', () => {
        window.clearTimeout(driverSearchTimeout);
        driverSearchTimeout = window.setTimeout(() => driverSearchForm.requestSubmit(), 500);
    });

    const masterSearchForm = document.querySelector('[data-master-search-form]');
    const masterSearch = masterSearchForm?.querySelector('[data-master-search]');
    let masterSearchTimeout;

    masterSearch?.addEventListener('input', () => {
        window.clearTimeout(masterSearchTimeout);
        masterSearchTimeout = window.setTimeout(() => masterSearchForm.requestSubmit(), 500);
    });

    document.addEventListener('click', (event) => {
        if (profileMenu && !profileMenu.contains(event.target)) {
            profileMenu.open = false;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && profileMenu) {
            profileMenu.open = false;
            profileTrigger?.focus();
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const loadingOverlay = document.querySelector('[data-app-loading]');
    let loadingTimer;
    let loadingSafetyTimer;

    if (!loadingOverlay) {
        return;
    }
    const showLoading = () => {
        window.clearTimeout(loadingTimer);
        window.clearTimeout(loadingSafetyTimer);
        loadingTimer = window.setTimeout(() => {
            loadingOverlay.hidden = false;
            loadingOverlay.setAttribute('aria-busy', 'true');
            window.requestAnimationFrame(() => loadingOverlay.classList.add('is-visible'));

            // A canceled navigation must never leave the interface blocked.
            loadingSafetyTimer = window.setTimeout(hideLoading, 7000);
        }, 350);
    };

    const hideLoading = () => {
        window.clearTimeout(loadingTimer);
        window.clearTimeout(loadingSafetyTimer);
        loadingOverlay.classList.remove('is-visible');
        loadingOverlay.setAttribute('aria-busy', 'false');
        loadingOverlay.hidden = true;
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() === 'get' || form.matches('[data-no-loading]') || form.target === '_blank') {
            return;
        }

        showLoading();
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() === 'get' || form.target === '_blank') {
            return;
        }

        if (form.matches('[data-delete-confirm], [data-confirm]') && form.dataset.deleteConfirmed !== 'true') {
            return;
        }

        if (form.dataset.isSubmitting === 'true') {
            event.preventDefault();
            return;
        }

        form.dataset.isSubmitting = 'true';
        form.setAttribute('aria-busy', 'true');
        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
            button.disabled = true;
            button.classList.add('is-submitting');

            if (button instanceof HTMLButtonElement && button.dataset.submittingLabel) {
                button.textContent = button.dataset.submittingLabel;
            }
        });
    }, true);

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a');
        if (!link || link.matches('[data-no-loading], [download]') || link.target === '_blank' || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const href = link.getAttribute('href');
        if (!href || href === '#' || href.startsWith('#') || href.includes('/download')) {
            return;
        }

        const url = new URL(link.href, window.location.href);
        if (url.origin === window.location.origin && url.href !== window.location.href) {
            showLoading();
        }
    }, true);

    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const dismiss = () => {
            toast.classList.add('is-leaving');
            window.setTimeout(() => toast.remove(), 180);
        };

        toast.querySelector('[data-toast-dismiss]')?.addEventListener('click', dismiss);
        window.setTimeout(dismiss, 6000);
    });

    // Cover normal loads, browser back/forward cache restores, and failed navigation.
    hideLoading();
    window.addEventListener('pageshow', hideLoading);
    window.addEventListener('popstate', hideLoading);
    window.addEventListener('focus', hideLoading);
    window.addEventListener('error', hideLoading);
    window.addEventListener('unhandledrejection', hideLoading);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            hideLoading();
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-delete-modal]');
    const title = modal?.querySelector('[data-delete-modal-title]');
    const description = modal?.querySelector('[data-delete-modal-description]');
    const confirm = modal?.querySelector('[data-delete-modal-confirm]');
    let form;
    let trigger;

    if (!modal || !title || !description || !confirm) {
        return;
    }

    const closeModal = () => {
        modal.hidden = true;
        modal.classList.remove('is-status-confirmation');
        modal.classList.remove('is-deactivation-confirmation');
        form = undefined;
        trigger?.focus();
        trigger = undefined;
    };

    const openModal = (submittedForm) => {
        form = submittedForm;
        trigger = document.activeElement instanceof HTMLElement ? document.activeElement : undefined;
        const name = form.dataset.deleteName || 'data ini';
        const isDelete = form.matches('[data-delete-confirm]');
        title.textContent = isDelete ? 'Delete Data' : (form.dataset.confirmTitle || `Hapus ${name}?`);
        description.textContent = isDelete ? 'Apakah anda yakin ingin menghapus data?' : (form.dataset.confirmDescription || form.dataset.deleteDescription || `${name} akan dihapus secara permanen.`);
        const label = isDelete ? 'Hapus Data' : (form.dataset.confirmLabel || 'Hapus');
        confirm.querySelector('[data-delete-modal-confirm-label]').textContent = label;
        const isPrimary = form.dataset.confirmTone === 'primary';
        const isDeactivation = !isDelete && form.dataset.confirmIcon === 'power' && label.toLowerCase().startsWith('nonaktifkan');
        confirm.classList.toggle('primary-button', isPrimary);
        confirm.classList.toggle('danger-button', !isPrimary);
        modal.classList.toggle('is-status-confirmation', form.dataset.confirmIcon === 'power');
        modal.classList.toggle('is-deactivation-confirmation', isDeactivation);
        modal.querySelector('[data-delete-modal-icon]')?.toggleAttribute('hidden', form.dataset.confirmIcon === 'power');
        modal.querySelector('[data-confirm-modal-power-icon]')?.toggleAttribute('hidden', form.dataset.confirmIcon !== 'power');
        confirm.querySelector('[data-delete-modal-confirm-trash]')?.toggleAttribute('hidden', form.dataset.confirmIcon === 'power');
        confirm.querySelector('[data-delete-modal-confirm-power]')?.toggleAttribute('hidden', form.dataset.confirmIcon !== 'power');
        modal.hidden = false;
        confirm.focus();
    };

    document.addEventListener('submit', (event) => {
        const submittedForm = event.target;
        if (!(submittedForm instanceof HTMLFormElement) || !submittedForm.matches('[data-delete-confirm], [data-confirm]') || submittedForm.dataset.deleteConfirmed === 'true') {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        openModal(submittedForm);
    });

    confirm.addEventListener('click', () => {
        if (!form) {
            return;
        }

        const submittedForm = form;
        modal.hidden = true;
        modal.classList.remove('is-status-confirmation');
        modal.classList.remove('is-deactivation-confirmation');
        form = undefined;
        submittedForm.dataset.deleteConfirmed = 'true';
        submittedForm.removeAttribute('data-no-loading');
        submittedForm.requestSubmit();
    });

    modal.querySelectorAll('[data-delete-modal-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        document.querySelectorAll('.vehicle-qr-menu[open]').forEach((menu) => {
            if (!menu.contains(target)) {
                menu.removeAttribute('open');
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.vehicle-qr-menu[open]').forEach((menu) => menu.removeAttribute('open'));
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-vehicle-qr-modal]');
    const image = modal?.querySelector('[data-vehicle-qr-image]');
    const title = modal?.querySelector('[data-vehicle-qr-title]');
    const description = modal?.querySelector('[data-vehicle-qr-description]');
    const download = modal?.querySelector('[data-vehicle-qr-download]');

    if (!modal || !image || !title || !description || !download) {
        return;
    }

    const closeModal = () => {
        modal.hidden = true;
        image.removeAttribute('src');
    };

    document.querySelectorAll('[data-vehicle-qr-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            image.src = trigger.dataset.qrSrc ?? '';
            image.alt = `QR ${trigger.dataset.qrTitle ?? 'Kendaraan'}`;
            title.textContent = trigger.dataset.qrTitle ?? 'QR Kendaraan';
            description.textContent = trigger.dataset.qrDescription ?? '';
            download.href = trigger.dataset.qrDownload ?? '#';
            modal.hidden = false;
            modal.querySelector('[data-vehicle-qr-close]')?.focus();
        });
    });

    modal.querySelectorAll('[data-vehicle-qr-close]').forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const targetInput = document.querySelector('[data-weight-target]');
    const weightInput = document.querySelector('[data-question-weight]');
    const indicatorInput = document.querySelector('[data-indicator-input]');
    const summary = document.querySelector('[data-weight-summary]');

    if (!targetInput || !weightInput || !indicatorInput || !summary) {
        return;
    }

    const used = summary.querySelector('[data-weight-used]');
    const remaining = summary.querySelector('[data-weight-remaining]');

    const renderWeight = () => {
        const isVehicle = targetInput.value === 'vehicle';
        const isFeedback = targetInput.value === 'feedback';
        const baseWeight = Number(isVehicle ? summary.dataset.vehicleBase : summary.dataset.driverBase) || 0;
        const currentWeight = isFeedback ? 0 : Math.max(0, Number(weightInput.value) || 0);
        const totalWeight = baseWeight + currentWeight;
        const remainingWeight = Math.max(0, 100 - totalWeight);

        indicatorInput.readOnly = isVehicle || isFeedback;
        indicatorInput.classList.toggle('is-readonly', isVehicle || isFeedback);
        if (isVehicle) {
            indicatorInput.value = 'Kendaraan';
        } else if (isFeedback) {
            indicatorInput.value = 'Feedback/Keluhan';
        } else if (indicatorInput.value === 'Kendaraan') {
            indicatorInput.value = '';
        }

        weightInput.readOnly = isFeedback;
        weightInput.classList.toggle('is-readonly', isFeedback);
        if (isFeedback) {
            weightInput.value = '0';
        }
        weightInput.max = String(isFeedback ? 0 : Math.max(0, 100 - baseWeight));
        used.textContent = `${totalWeight}%`;
        remaining.textContent = `${remainingWeight}%`;
        summary.hidden = isFeedback;
        summary.classList.toggle('is-complete', totalWeight === 100 && !isFeedback);
        summary.classList.toggle('is-over', totalWeight > 100);
        summary.querySelector('span')?.replaceChildren(`Bobot ${isVehicle ? 'Kendaraan' : 'Driver'}`);
    };

    targetInput.addEventListener('change', renderWeight);
    weightInput.addEventListener('input', renderWeight);
    renderWeight();
});

const renderQuestionPreview = () => {
    const preview = document.querySelector('[data-question-preview]');
    const questionInput = document.querySelector('[data-question-input]');
    const answerTypeInput = document.querySelector('[data-answer-type]');
    const targetInput = document.querySelector('[data-weight-target]');
    const body = document.querySelector('[data-preview-body]');
    const title = document.querySelector('[data-preview-question]');
    const target = document.querySelector('[data-preview-target]');
    const optionBuilder = document.querySelector('[data-option-builder]');
    const instructionInput = document.querySelector('[data-question-instruction]');
    const placeholderInput = document.querySelector('[data-question-placeholder]');
    const ratingMinInput = document.querySelector('[data-question-rating-min]');
    const ratingMaxInput = document.querySelector('[data-question-rating-max]');
    if (!preview || !questionInput || !answerTypeInput || !body || !title) {
        return;
    }

    const instruction = preview.querySelector('[data-preview-instruction]');

    const options = [...document.querySelectorAll('[data-option-text]')]
        .map((input) => input.value.trim())
        .filter(Boolean);

    const optionLabels = options.length ? options : ['Opsi jawaban'];
    const type = answerTypeInput.value;
    const placeholder = placeholderInput?.value.trim() || 'Tulis jawaban Anda...';
    const ratingMin = ratingMinInput?.value.trim() || 'Sangat Buruk';
    const ratingMax = ratingMaxInput?.value.trim() || 'Sangat Baik';
    const escapeHtml = (value) => value.replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[character]));

    title.textContent = questionInput.value.trim() || 'Bagaimana keramahan driver?';
    if (target) {
        target.textContent = targetInput?.value === 'vehicle'
            ? 'Penilaian Kendaraan'
            : targetInput?.value === 'feedback'
                ? 'Feedback / Keluhan'
                : 'Penilaian Driver';
    }
    if (instruction) {
        instruction.textContent = instructionInput?.value.trim() || '';
        instruction.hidden = !instruction.textContent;
    }
    optionBuilder?.classList.toggle('is-hidden', !['multiple_choice', 'checkbox'].includes(type));

    if (type === 'rating') {
        const star = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2Z"></path></svg>';
        body.innerHTML = `<div class="passenger-star-rating question-preview-stars"><span class="is-selected">${star}</span><span class="is-selected">${star}</span><span class="is-selected">${star}</span><span class="is-selected">${star}</span><span>${star}</span></div><div class="passenger-rating-labels"><span>${escapeHtml(ratingMin)}</span><span>${escapeHtml(ratingMax)}</span></div>`;
        return;
    }

    if (type === 'yes_no') {
        body.innerHTML = '<div class="passenger-answer-options passenger-answer-yes-no"><label><input type="radio" disabled><span>Ya</span></label><label><input type="radio" disabled><span>Tidak</span></label></div>';
        return;
    }

    if (type === 'multiple_choice' || type === 'checkbox') {
        const inputType = type === 'multiple_choice' ? 'radio' : 'checkbox';
        body.innerHTML = `<div class="passenger-answer-options">${optionLabels.map((option) => `<label><input type="${inputType}" disabled><span>${escapeHtml(option)}</span></label>`).join('')}</div>`;
        return;
    }

    if (type === 'paragraph') {
        body.innerHTML = `<textarea rows="4" placeholder="${escapeHtml(placeholder)}" disabled></textarea>`;
        return;
    }

    body.innerHTML = `<input type="text" placeholder="${escapeHtml(placeholder)}" disabled>`;
};

document.addEventListener('DOMContentLoaded', () => {
    const optionList = document.querySelector('[data-option-list]');
    const addOption = document.querySelector('[data-add-option]');

    document.querySelector('[data-question-input]')?.addEventListener('input', renderQuestionPreview);
    document.querySelector('[data-answer-type]')?.addEventListener('change', renderQuestionPreview);
    document.querySelector('[data-weight-target]')?.addEventListener('change', renderQuestionPreview);
    document.querySelector('[data-question-instruction]')?.addEventListener('input', renderQuestionPreview);
    document.querySelector('[data-question-placeholder]')?.addEventListener('input', renderQuestionPreview);
    document.querySelector('[data-question-rating-min]')?.addEventListener('input', renderQuestionPreview);
    document.querySelector('[data-question-rating-max]')?.addEventListener('input', renderQuestionPreview);
    optionList?.addEventListener('input', renderQuestionPreview);
    optionList?.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-remove-option]');
        if (!remove) return;
        remove.closest('[data-option-row]')?.remove();
        renderQuestionPreview();
    });
    addOption?.addEventListener('click', () => {
        const index = optionList?.querySelectorAll('[data-option-row]').length ?? 0;
        const row = document.createElement('div');
        row.className = 'option-row';
        row.dataset.optionRow = '';
        row.innerHTML = `<input type="text" name="options[${index}][option_text]" placeholder="Opsi jawaban" data-option-text><input type="number" name="options[${index}][sort_order]" value="${index + 1}" min="0" aria-label="Urutan opsi"><button class="icon-inline-button" type="button" data-remove-option aria-label="Hapus opsi">×</button>`;
        optionList?.appendChild(row);
        row.querySelector('[data-option-text]')?.focus();
        renderQuestionPreview();
    });

    renderQuestionPreview();
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-image-cropper]').forEach((field) => {
        const imageInput = field.querySelector('[data-image-input]');
        const cameraInput = field.querySelector('[data-camera-input]');
        const pickerTrigger = field.querySelector('[data-image-open-picker]');
        const cameraButton = field.querySelector('[data-image-camera]');
        const modal = field.querySelector('[data-image-modal]');
        const cropImage = field.querySelector('[data-cropper-image]');
        const preview = field.querySelector('[data-image-preview]');
        const fileName = field.querySelector('[data-image-file-name]');
        const zoom = field.querySelector('[data-cropper-zoom]');
        const controls = field.querySelector('[data-cropper-controls]');
        const cropperActions = field.querySelector('[data-cropper-actions]');
        const stage = field.querySelector('[data-image-stage]');
        const cameraStage = field.querySelector('[data-camera-stage]');
        const cameraActions = field.querySelector('[data-camera-actions]');
        const cameraVideo = field.querySelector('[data-camera-video]');
        const cameraMessage = field.querySelector('[data-camera-message]');
        const sourcePicker = field.querySelector('[data-image-source-picker]');
        const dropZone = field.querySelector('[data-image-drop-zone]');
        let cropper;
        let stream;

        if (!imageInput || !modal || !cropImage || !preview || !fileName || !zoom) {
            return;
        }

        const stopCamera = () => {
            stream?.getTracks().forEach((track) => track.stop());
            stream = undefined;
            cameraVideo.srcObject = null;
        };

        const closeModal = () => {
            stopCamera();
            cropper?.destroy();
            cropper = undefined;
            modal.hidden = true;
            document.body.classList.remove('image-cropper-is-open');
        };

        const openModal = () => {
            modal.hidden = false;
            document.body.classList.add('image-cropper-is-open');
        };

        const selectedRatio = () => Number(field.dataset.imageCropperRatio) || 1;

        const openPicker = () => {
            stopCamera();
            cropper?.destroy();
            cropper = undefined;
            sourcePicker.hidden = false;
            stage.hidden = true;
            cropImage.hidden = true;
            cameraStage.hidden = true;
            cameraActions.hidden = true;
            controls.hidden = true;
            cropperActions.hidden = true;
            openModal();
        };

        const updatePreview = (file) => {
            const image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            image.alt = 'Preview foto yang dipilih';
            preview.replaceChildren(image);
            fileName.textContent = file.name;
        };

        const writeImageFile = (file) => {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            imageInput.files = transfer.files;
        };

        const openEditor = (file) => {
            if (!file?.type.startsWith('image/')) {
                return;
            }

            stopCamera();
            cropper?.destroy();
            sourcePicker.hidden = true;
            stage.hidden = false;
            cameraStage.hidden = true;
            cameraActions.hidden = true;
            controls.hidden = false;
            cropperActions.hidden = false;
            cropImage.hidden = false;
            zoom.value = '0';
            openModal();

            const reader = new FileReader();
            reader.addEventListener('load', () => {
                cropImage.onload = () => {
                    cropper = new Cropper(cropImage, {
                        aspectRatio: selectedRatio(),
                        autoCropArea: 0.88,
                        background: false,
                        dragMode: 'move',
                        guides: true,
                        movable: true,
                        zoomable: true,
                        responsive: true,
                        viewMode: 1,
                    });
                };
                cropImage.src = reader.result;
            });
            reader.readAsDataURL(file);
        };

        const openCamera = async () => {
            if (!navigator.mediaDevices?.getUserMedia) {
                cameraInput?.click();
                return;
            }

            cropper?.destroy();
            cropper = undefined;
            openModal();
            sourcePicker.hidden = true;
            stage.hidden = false;
            cropImage.hidden = true;
            cameraStage.hidden = false;
            cameraActions.hidden = false;
            controls.hidden = true;
            cropperActions.hidden = true;
            cameraMessage.hidden = false;
            cameraMessage.textContent = 'Kamera sedang disiapkan...';

            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false,
                });
                cameraVideo.srcObject = stream;
                await cameraVideo.play();
                cameraMessage.hidden = true;
            } catch {
                cameraMessage.textContent = 'Kamera tidak tersedia. Pilih foto dari perangkat Anda.';
            }
        };

        pickerTrigger?.addEventListener('click', openPicker);
        pickerTrigger?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openPicker();
            }
        });
        dropZone?.addEventListener('click', () => imageInput.click());
        dropZone?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                imageInput.click();
            }
        });
        ['dragenter', 'dragover'].forEach((eventName) => {
            dropZone?.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropZone.classList.add('is-dragging');
            });
        });
        ['dragleave', 'drop'].forEach((eventName) => {
            dropZone?.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropZone.classList.remove('is-dragging');
            });
        });
        dropZone?.addEventListener('drop', (event) => {
            openEditor(event.dataTransfer?.files?.[0]);
        });
        cameraButton?.addEventListener('click', openCamera);
        imageInput.addEventListener('change', () => openEditor(imageInput.files?.[0]));
        cameraInput?.addEventListener('change', () => openEditor(cameraInput.files?.[0]));

        field.querySelectorAll('[data-image-close]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        field.querySelector('[data-camera-retry]')?.addEventListener('click', () => {
            stopCamera();
            openCamera();
        });

        field.querySelector('[data-camera-capture]')?.addEventListener('click', () => {
            if (!cameraVideo.videoWidth || !cameraVideo.videoHeight) {
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = cameraVideo.videoWidth;
            canvas.height = cameraVideo.videoHeight;
            canvas.getContext('2d')?.drawImage(cameraVideo, 0, 0);

            canvas.toBlob((blob) => {
                if (!blob) return;
                openEditor(new File([blob], 'foto-' + Date.now() + '.jpg', { type: 'image/jpeg' }));
            }, 'image/jpeg', 0.92);
        });

        zoom.addEventListener('input', () => cropper?.zoomTo(1 + Number(zoom.value)));
        field.querySelector('[data-cropper-reset]')?.addEventListener('click', () => {
            cropper?.reset();
            cropper?.setAspectRatio(selectedRatio());
            zoom.value = '0';
        });

        field.querySelector('[data-cropper-apply]')?.addEventListener('click', () => {
            const canvas = cropper?.getCroppedCanvas({
                fillColor: '#ffffff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
                maxHeight: 1600,
                maxWidth: 1600,
            });

            canvas?.toBlob((blob) => {
                if (!blob) return;
                const file = new File([blob], 'foto-crop-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                writeImageFile(file);
                updatePreview(file);
                closeModal();
            }, 'image/jpeg', 0.9);
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-star-rating]').forEach((rating) => {
        const updateStars = (value) => {
            rating.querySelectorAll('label').forEach((label, index) => {
                label.classList.toggle('is-selected', index < Number(value));
            });
        };

        rating.addEventListener('change', (event) => {
            if (event.target instanceof HTMLInputElement) {
                updateStars(event.target.value);
            }
        });

        const selected = rating.querySelector('input:checked');
        if (selected instanceof HTMLInputElement) {
            updateStars(selected.value);
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const scanner = document.querySelector('[data-passenger-qr-scanner]');
    const openButton = document.querySelector('[data-passenger-qr-scanner-open]');
    const video = scanner?.querySelector('[data-passenger-qr-scanner-video]');
    const status = scanner?.querySelector('[data-passenger-qr-scanner-status]');
    const retryButton = scanner?.querySelector('[data-passenger-qr-scanner-retry]');

    if (!scanner || !openButton || !(video instanceof HTMLVideoElement) || !status || !retryButton) {
        return;
    }

    let qrScanner;

    const stopCamera = () => {
        qrScanner?.stop();
    };

    const closeScanner = () => {
        stopCamera();
        scanner.hidden = true;
        document.body.classList.remove('is-passenger-scanner-open');
        retryButton.hidden = true;
        openButton.focus();
    };

    const setFailure = (message) => {
        stopCamera();
        status.textContent = message;
        retryButton.hidden = false;
    };

    const openRating = (rawValue) => {
        try {
            const url = new URL(rawValue, window.location.origin);
            const routeMatch = url.pathname.match(/^\/rating\/([a-zA-Z0-9]{40})(?:\/|$)/);

            if (!routeMatch) {
                setFailure('QR ini bukan QR kendaraan yang valid. Arahkan kamera ke QR kendaraan yang benar.');

                return;
            }

            stopCamera();
            window.location.assign(`${window.location.origin}/rating/${routeMatch[1]}`);
        } catch {
            setFailure('QR ini tidak dapat dibaca. Coba arahkan kamera kembali ke QR kendaraan.');
        }
    };

    const startScanner = async () => {
        retryButton.hidden = true;
        status.textContent = 'Meminta akses kamera...';

        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            setFailure('Kamera memerlukan koneksi HTTPS. Buka halaman ini melalui domain HTTPS lalu coba kembali.');

            return;
        }

        try {
            qrScanner ??= new QrScanner(
                video,
                (result) => openRating(result.data),
                {
                    highlightCodeOutline: true,
                    highlightScanRegion: true,
                    preferredCamera: 'environment',
                    returnDetailedScanResult: true,
                },
            );

            await qrScanner.start();
            status.textContent = 'Arahkan QR kendaraan ke dalam kotak pemindai.';
        } catch {
            setFailure('Kamera tidak dapat digunakan. Izinkan akses kamera lalu coba kembali.');
        }
    };

    openButton.addEventListener('click', () => {
        scanner.hidden = false;
        document.body.classList.add('is-passenger-scanner-open');
        startScanner();
    });
    scanner.querySelectorAll('[data-passenger-qr-scanner-close]').forEach((button) => button.addEventListener('click', closeScanner));
    retryButton.addEventListener('click', startScanner);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !scanner.hidden) {
            closeScanner();
        }
    });
    window.addEventListener('pagehide', () => qrScanner?.destroy(), { once: true });
});

document.addEventListener('DOMContentLoaded', () => {
    const list = document.querySelector('[data-question-reorder-list]');
    const form = document.querySelector('[data-question-reorder-form]');
    const inputs = document.querySelector('[data-question-order-inputs]');

    if (!list || !form || !inputs) {
        return;
    }

    let draggedRow;

    const rows = () => [...list.querySelectorAll('[data-question-row]')];

    const positions = () => new Map(rows().map((row) => [row, row.getBoundingClientRect()]));

    const animateReposition = (before) => {
        rows().forEach((row) => {
            const previous = before.get(row);
            const current = row.getBoundingClientRect();
            const distance = previous ? previous.top - current.top : 0;

            if (Math.abs(distance) < 1) {
                return;
            }

            row.querySelectorAll('td').forEach((cell) => {
                cell.animate([
                    { transform: `translateY(${distance}px)` },
                    { transform: 'translateY(0)' },
                ], {
                    duration: 260,
                    easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                });
            });
        });
    };

    const syncOrder = () => {
        inputs.replaceChildren();

        rows().forEach((row, index) => {
            const position = String(index + 1);
            row.querySelector('[data-question-position]')?.replaceChildren(position);
            row.querySelector('[data-question-sort-order]')?.replaceChildren(position);

            const input = document.createElement('input');
            input.name = 'order[]';
            input.type = 'hidden';
            input.value = row.dataset.questionId ?? '';
            inputs.appendChild(input);
        });
    };

    list.addEventListener('dragstart', (event) => {
        const row = event.target.closest('[data-question-row]');
        if (!(row instanceof HTMLTableRowElement)) {
            return;
        }

        draggedRow = row;
        row.classList.add('is-dragging');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', row.dataset.questionId ?? '');
    });

    list.addEventListener('dragover', (event) => {
        event.preventDefault();
        const target = event.target.closest('[data-question-row]');
        if (!draggedRow || !(target instanceof HTMLTableRowElement) || target === draggedRow) {
            return;
        }

        rows().forEach((row) => row.classList.remove('is-drag-over'));
        target.classList.add('is-drag-over');

        const targetBounds = target.getBoundingClientRect();
        const insertAfter = event.clientY > targetBounds.top + targetBounds.height / 2;
        const reference = insertAfter ? target.nextSibling : target;
        if (reference === draggedRow) {
            return;
        }

        const before = positions();
        list.insertBefore(draggedRow, reference);
        animateReposition(before);
        syncOrder();
    });

    list.addEventListener('drop', (event) => {
        event.preventDefault();
        syncOrder();
    });

    list.addEventListener('dragend', () => {
        draggedRow?.classList.remove('is-dragging');
        rows().forEach((row) => row.classList.remove('is-drag-over'));
        draggedRow = undefined;
        syncOrder();
    });

    form.addEventListener('submit', syncOrder);
    syncOrder();
});

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const timeout = Number(body.dataset.adminSessionTimeout);
    const pingUrl = body.dataset.adminSessionPing;
    const logoutUrl = body.dataset.adminLogout;
    const loginUrl = body.dataset.adminLogin;

    if (!timeout || !pingUrl || !logoutUrl || !loginUrl) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let lastInteraction = Date.now();
    let lastPing = Date.now();
    let timer;
    let hasExpired = false;

    const expire = () => {
        if (hasExpired) return;
        hasExpired = true;
        window.fetch(`${logoutUrl}?reason=idle`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken ?? '', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            keepalive: true,
        }).finally(() => window.location.assign(`${loginUrl}?expired=1`));
    };

    const schedule = () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(expire, timeout);
    };

    const ping = () => {
        lastPing = Date.now();
        window.fetch(pingUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken ?? '', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            keepalive: true,
        });
    };

    const registerActivity = () => {
        if (hasExpired) return;
        const now = Date.now();
        if (now - lastInteraction >= timeout) {
            expire();
            return;
        }
        lastInteraction = now;
        if (now - lastPing >= 60000) ping();
        schedule();
    };

    ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach((eventName) => {
        window.addEventListener(eventName, registerActivity, { passive: true });
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') registerActivity();
    });
    schedule();
});
// Open native date pickers from the whole input area, not only the calendar icon.
document.addEventListener('click', (event) => {
    const input = event.target instanceof Element ? event.target.closest('input[type="date"]') : null;

    if (!(input instanceof HTMLInputElement) || input.disabled || input.readOnly) {
        return;
    }

    input.focus({ preventScroll: true });

    if (typeof input.showPicker === 'function') {
        try {
            input.showPicker();
        } catch {
            // Browsers without picker support still retain their normal focused date input behavior.
        }
    }
});
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-auto-filter-form]').forEach((form) => {
        let filterTimer;
        const submit = () => {
            window.clearTimeout(filterTimer);
            filterTimer = window.setTimeout(() => form.requestSubmit(), 350);
        };

        form.querySelectorAll('input[type="date"], select').forEach((field) => {
            field.addEventListener('change', submit);
        });
    });
});

// Keep each sidebar group open or closed across page changes.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sidebar-group]').forEach((group) => {
        const storageKey = `sidebar-group-${group.dataset.sidebarGroup}-open`;
        const savedState = window.localStorage.getItem(storageKey);
        const hasActivePage = group.querySelector('.sidebar-nav-submenu .is-active') !== null;

        if (hasActivePage) {
            group.open = true;
            window.localStorage.setItem(storageKey, 'true');
        } else if (savedState !== null) {
            group.open = savedState === 'true';
        } else {
            window.localStorage.setItem(storageKey, String(group.open));
        }
    });
});

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-sidebar-group-toggle]');
    if (!toggle) return;

    const group = toggle.closest('[data-sidebar-group]');
    if (!group) return;

    const adminFrame = document.querySelector('.admin-frame');
    if (adminFrame?.classList.contains('is-sidebar-collapsed')) {
        event.preventDefault();
        adminFrame.classList.remove('is-sidebar-collapsed');
        window.localStorage.setItem('admin-sidebar-collapsed', 'false');
        document.querySelector('[data-sidebar-collapse]')?.setAttribute('aria-expanded', 'true');
        document.querySelector('[data-sidebar-collapse]')?.setAttribute('aria-label', 'Tutup navigasi samping');
        document.querySelector('[data-sidebar-collapse]')?.setAttribute('title', 'Tutup navigasi samping');
        return;
    }

    event.preventDefault();
    group.open = !group.open;
    window.localStorage.setItem(`sidebar-group-${group.dataset.sidebarGroup}-open`, String(group.open));
});
