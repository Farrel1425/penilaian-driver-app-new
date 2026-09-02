import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const passwordToggle = document.querySelector('[data-password-toggle]');
    const passwordInput = document.querySelector('[data-password-input]');
    const profileMenu = document.querySelector('[data-profile-menu]');
    const profileTrigger = document.querySelector('[data-profile-trigger]');

    toggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('is-open');
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

    document.addEventListener('click', (event) => {
        if (profileMenu && !profileMenu.contains(event.target)) {
            profileMenu.open = false;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
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
        form = undefined;
        trigger?.focus();
        trigger = undefined;
    };

    const openModal = (submittedForm) => {
        form = submittedForm;
        trigger = document.activeElement instanceof HTMLElement ? document.activeElement : undefined;
        const name = form.dataset.deleteName || 'data ini';
        title.textContent = form.dataset.confirmTitle || `Hapus ${name}?`;
        description.textContent = form.dataset.confirmDescription || form.dataset.deleteDescription || `${name} akan dihapus secara permanen.`;
        const label = form.dataset.confirmLabel || 'Hapus';
        confirm.querySelector('[data-delete-modal-confirm-label]').textContent = label;
        const isPrimary = form.dataset.confirmTone === 'primary';
        confirm.classList.toggle('primary-button', isPrimary);
        confirm.classList.toggle('danger-button', !isPrimary);
        modal.classList.toggle('is-status-confirmation', form.dataset.confirmIcon === 'power');
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
    document.querySelectorAll('[data-debounced-search-form]').forEach((form) => {
        const input = form.querySelector('[data-debounced-search]');
        if (!input) return;

        const focusKey = `${window.location.pathname}:debounced-search-focus`;
        let searchTimeout;

        const restoreFocus = () => {
            const focusState = window.sessionStorage.getItem(focusKey);
            if (!focusState) return;

            window.sessionStorage.removeItem(focusKey);
            input.focus();
            const cursorPosition = Math.min(Number(focusState) || input.value.length, input.value.length);
            input.setSelectionRange(cursorPosition, cursorPosition);
        };

        input.addEventListener('input', () => {
            window.clearTimeout(searchTimeout);
            searchTimeout = window.setTimeout(() => {
                window.sessionStorage.setItem(focusKey, String(input.selectionStart ?? input.value.length));
                form.requestSubmit();
            }, 1000);
        });

        form.addEventListener('submit', () => window.clearTimeout(searchTimeout));
        restoreFocus();
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

    let stream;
    let detector;
    let scanTimer;

    const stopCamera = () => {
        window.clearTimeout(scanTimer);
        stream?.getTracks().forEach((track) => track.stop());
        stream = undefined;
        video.srcObject = null;
    };

    const closeScanner = () => {
        stopCamera();
        scanner.hidden = true;
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
            const routePrefix = '/rating/';

            if (url.origin !== window.location.origin || !url.pathname.startsWith(routePrefix) || url.pathname.slice(routePrefix.length).split('/')[0] === '') {
                setFailure('QR ini bukan QR kendaraan yang valid. Arahkan kamera ke QR kendaraan yang benar.');

                return;
            }

            stopCamera();
            window.location.assign(`${url.origin}${url.pathname}`);
        } catch {
            setFailure('QR ini tidak dapat dibaca. Coba arahkan kamera kembali ke QR kendaraan.');
        }
    };

    const detect = async () => {
        if (!stream || video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
            scanTimer = window.setTimeout(detect, 200);

            return;
        }

        try {
            const codes = await detector.detect(video);
            const code = codes.find((item) => item.rawValue);

            if (code?.rawValue) {
                openRating(code.rawValue);

                return;
            }
        } catch {
            // The next frame can still be decoded normally.
        }

        scanTimer = window.setTimeout(detect, 200);
    };

    const startScanner = async () => {
        retryButton.hidden = true;
        status.textContent = 'Meminta akses kamera...';

        if (!('BarcodeDetector' in window) || !navigator.mediaDevices?.getUserMedia) {
            setFailure('Browser ini belum mendukung pemindai QR. Gunakan Chrome versi terbaru atau buka QR melalui kamera perangkat Anda.');

            return;
        }

        try {
            detector = new window.BarcodeDetector({ formats: ['qr_code'] });
            stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: { facingMode: { ideal: 'environment' } },
            });
            video.srcObject = stream;
            await video.play();
            status.textContent = 'Arahkan QR kendaraan ke dalam kotak pemindai.';
            detect();
        } catch {
            setFailure('Kamera tidak dapat digunakan. Izinkan akses kamera lalu coba kembali.');
        }
    };

    openButton.addEventListener('click', () => {
        scanner.hidden = false;
        startScanner();
    });
    scanner.querySelectorAll('[data-passenger-qr-scanner-close]').forEach((button) => button.addEventListener('click', closeScanner));
    retryButton.addEventListener('click', startScanner);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !scanner.hidden) {
            closeScanner();
        }
    });
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
    document.querySelectorAll('[data-chart-controls]').forEach((controls) => {
        const card = controls.closest('.dashboard-trend-card');
        const canvas = card?.querySelector('[data-chart-canvas]');
        const scrollArea = card?.querySelector('[data-chart-scroll]');
        const label = controls.querySelector('[data-chart-zoom-label]');

        if (!(canvas instanceof HTMLElement) || !(scrollArea instanceof HTMLElement) || !label) {
            return;
        }

        const baseWidth = Number(canvas.dataset.chartBaseWidth) || 760;
        let zoom = 1;

        const scaledWidth = (scale = zoom) => Math.round(baseWidth * scale);

        const renderZoom = () => {
            const width = scaledWidth();
            canvas.style.minWidth = `${width}px`;
            canvas.style.width = `${width}px`;
            label.textContent = `${Math.round(zoom * 100)}%`;
        };

        const changeZoom = (nextZoom, focalX = scrollArea.clientWidth / 2) => {
            if (nextZoom === zoom) {
                return;
            }

            const currentWidth = scaledWidth();
            const focalPoint = Math.min(Math.max((scrollArea.scrollLeft + focalX) / currentWidth, 0), 1);

            zoom = nextZoom;
            renderZoom();

            const targetScrollLeft = (focalPoint * scaledWidth()) - focalX;
            const maxScrollLeft = Math.max(0, scrollArea.scrollWidth - scrollArea.clientWidth);
            scrollArea.scrollLeft = Math.min(Math.max(targetScrollLeft, 0), maxScrollLeft);
        };

        controls.querySelector('[data-chart-zoom-in]')?.addEventListener('click', () => {
            changeZoom(Math.min(1.8, zoom + 0.2));
        });

        controls.querySelector('[data-chart-zoom-out]')?.addEventListener('click', () => {
            changeZoom(Math.max(0.7, zoom - 0.2));
        });

        scrollArea.addEventListener('wheel', (event) => {
            if (event.ctrlKey || event.metaKey) {
                event.preventDefault();

                const bounds = scrollArea.getBoundingClientRect();
                const focalX = Math.min(Math.max(event.clientX - bounds.left, 0), scrollArea.clientWidth);
                const step = event.deltaY < 0 ? 0.1 : -0.1;
                changeZoom(Math.min(1.8, Math.max(0.7, zoom + step)), focalX);
                return;
            }

            if (event.shiftKey) {
                event.preventDefault();
                scrollArea.scrollLeft += event.deltaY;
            }
        }, { passive: false });

        renderZoom();
    });
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
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-dashboard-auto-filter]').forEach((form) => {
        let filterTimer;
        const submit = () => {
            window.clearTimeout(filterTimer);
            filterTimer = window.setTimeout(() => form.requestSubmit(), 500);
        };

        form.querySelectorAll('input[type="date"], select').forEach((field) => {
            field.addEventListener('change', submit);
        });
    });
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
document.addEventListener('DOMContentLoaded', () => {
    const wizard = document.querySelector('[data-assessment-wizard]');

    if (!wizard) {
        return;
    }

    const steps = Array.from(wizard.querySelectorAll('[data-assessment-step]'));
    const previousButton = wizard.querySelector('[data-wizard-previous]');
    const nextButton = wizard.querySelector('[data-wizard-next]');
    const submitButton = wizard.querySelector('[data-wizard-submit]');
    const currentPage = wizard.querySelector('[data-wizard-current-page]');
    const progress = wizard.querySelector('[data-wizard-progress]');
    const totalPages = Number(wizard.dataset.totalPages || steps.length);
    let page = Math.max(0, steps.findIndex((step) => step.querySelector('[data-wizard-server-error]')));

    const showPage = (nextPage, shouldFocus = false) => {
        page = Math.min(Math.max(nextPage, 0), steps.length - 1);
        steps.forEach((step, index) => {
            step.hidden = index !== page;
        });

        if (currentPage) {
            currentPage.textContent = String(page + 1);
        }

        if (progress) {
            progress.style.width = `${((page + 1) / totalPages) * 100}%`;
        }

        if (previousButton instanceof HTMLButtonElement) {
            previousButton.hidden = page === 0;
        }

        if (nextButton instanceof HTMLButtonElement) {
            nextButton.hidden = page === steps.length - 1;
        }

        if (submitButton instanceof HTMLButtonElement) {
            submitButton.hidden = page !== steps.length - 1;
        }

        if (shouldFocus) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    const hasAnswer = (question) => {
        if (question.hidden || question.dataset.required !== 'true') {
            return true;
        }

        const controls = Array.from(question.querySelectorAll('input, textarea'));

        return controls.some((control) => {
            if (control instanceof HTMLInputElement && (control.type === 'radio' || control.type === 'checkbox')) {
                return control.checked;
            }

            return control.value.trim() !== '';
        });
    };

    const validatePage = () => {
        const activeStep = steps[page];
        const questions = Array.from(activeStep.querySelectorAll('[data-wizard-question]'));
        let firstInvalid;

        questions.forEach((question) => {
            const error = question.querySelector('[data-wizard-error]');
            const isAnswered = hasAnswer(question);

            if (error) {
                error.hidden = isAnswered;
            }

            if (!isAnswered && !firstInvalid) {
                firstInvalid = question;
            }
        });

        firstInvalid?.scrollIntoView({ behavior: 'smooth', block: 'center' });

        return !firstInvalid;
    };

    nextButton?.addEventListener('click', () => {
        if (validatePage()) {
            showPage(page + 1, true);
        }
    });

    previousButton?.addEventListener('click', () => showPage(page - 1, true));

    wizard.addEventListener('input', (event) => {
        const question = event.target instanceof Element ? event.target.closest('[data-wizard-question]') : null;
        const error = question?.querySelector('[data-wizard-error]');

        if (error && hasAnswer(question)) {
            error.hidden = true;
        }
    });

    const syncFeedbackFollowUp = (feedbackChoice) => {
        const followUp = wizard.querySelector('[data-feedback-followup]');
        if (followUp && feedbackChoice) {
            followUp.hidden = feedbackChoice.dataset.feedbackEmpty === 'true';
        }
    };

    wizard.addEventListener('change', (event) => {
        const feedbackChoice = event.target instanceof HTMLInputElement && event.target.matches('[data-feedback-choice]') ? event.target : null;
        syncFeedbackFollowUp(feedbackChoice);
    });

    syncFeedbackFollowUp(wizard.querySelector('[data-feedback-choice]:checked'));
    showPage(page);
});