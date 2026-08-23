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

    profileTrigger?.addEventListener('click', () => {
        const isOpen = profileMenu?.classList.toggle('is-open') ?? false;
        profileTrigger.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', (event) => {
        if (profileMenu && !profileMenu.contains(event.target)) {
            profileMenu.classList.remove('is-open');
            profileTrigger?.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            profileMenu?.classList.remove('is-open');
            profileTrigger?.setAttribute('aria-expanded', 'false');
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const loadingOverlay = document.querySelector('[data-app-loading]');

    if (!loadingOverlay) {
        return;
    }

    const showLoading = () => {
        loadingOverlay.hidden = false;
        window.requestAnimationFrame(() => loadingOverlay.classList.add('is-visible'));
    };

    const hideLoading = () => {
        loadingOverlay.classList.remove('is-visible');
        window.setTimeout(() => {
            loadingOverlay.hidden = true;
        }, 160);
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() === 'get' || form.matches('[data-no-loading]') || form.target === '_blank') {
            return;
        }

        showLoading();
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

    window.addEventListener('pageshow', hideLoading);
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
        const baseWeight = Number(isVehicle ? summary.dataset.vehicleBase : summary.dataset.driverBase) || 0;
        const currentWeight = Math.max(0, Number(weightInput.value) || 0);
        const totalWeight = baseWeight + currentWeight;
        const remainingWeight = Math.max(0, 100 - totalWeight);

        indicatorInput.readOnly = isVehicle;
        indicatorInput.classList.toggle('is-readonly', isVehicle);
        if (isVehicle) {
            indicatorInput.value = 'Kendaraan';
        } else if (indicatorInput.value === 'Kendaraan') {
            indicatorInput.value = '';
        }

        weightInput.max = String(Math.max(0, 100 - baseWeight));
        used.textContent = `${totalWeight}%`;
        remaining.textContent = `${remainingWeight}%`;
        summary.classList.toggle('is-complete', totalWeight === 100);
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
    const body = document.querySelector('[data-preview-body]');
    const title = document.querySelector('[data-preview-question]');
    const optionBuilder = document.querySelector('[data-option-builder]');

    if (!preview || !questionInput || !answerTypeInput || !body || !title) {
        return;
    }

    const options = [...document.querySelectorAll('[data-option-text]')]
        .map((input) => input.value.trim())
        .filter(Boolean);

    const optionLabels = options.length ? options : ['Opsi jawaban'];
    const type = answerTypeInput.value;

    title.textContent = questionInput.value.trim() || 'Bagaimana keramahan driver?';
    optionBuilder?.classList.toggle('is-hidden', !['multiple_choice', 'checkbox'].includes(type));

    if (type === 'rating') {
        body.innerHTML = '<div class="rating-preview"><span>1</span><span>2</span><span>3</span><span>4</span><span>5</span></div>';
        return;
    }

    if (type === 'yes_no') {
        body.innerHTML = '<div class="choice-preview"><label><input type="radio" disabled> Ya</label><label><input type="radio" disabled> Tidak</label></div>';
        return;
    }

    if (type === 'multiple_choice' || type === 'checkbox') {
        const inputType = type === 'multiple_choice' ? 'radio' : 'checkbox';
        body.innerHTML = `<div class="choice-preview">${optionLabels.map((option) => `<label><input type="${inputType}" disabled> ${option}</label>`).join('')}</div>`;
        return;
    }

    if (type === 'paragraph') {
        body.innerHTML = '<textarea class="preview-input" rows="4" placeholder="Jawaban paragraf" disabled></textarea>';
        return;
    }

    body.innerHTML = '<input class="preview-input" type="text" placeholder="Jawaban singkat" disabled>';
};

document.addEventListener('DOMContentLoaded', () => {
    const optionList = document.querySelector('[data-option-list]');
    const addOption = document.querySelector('[data-add-option]');

    document.querySelector('[data-question-input]')?.addEventListener('input', renderQuestionPreview);
    document.querySelector('[data-answer-type]')?.addEventListener('change', renderQuestionPreview);
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

        const selectedRatio = () => 1;

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
