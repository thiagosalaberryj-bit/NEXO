/**
 * Subir Historia – Wizard multi-paso
 * Solo visual / UI, sin conexión con backend
 */

document.addEventListener('DOMContentLoaded', () => {
    const TOTAL = 5;
    let current = 1;

    // Elementos
    const panels    = document.querySelectorAll('.sh-panel');
    const steps     = document.querySelectorAll('.sh-step');
    const lines     = document.querySelectorAll('.sh-step-line');
    const btnPrev   = document.getElementById('btn-prev');
    const btnNext   = document.getElementById('btn-next');

    // ───────────── Navegación entre pasos ──────────────
    function goTo(step) {
        if (step < 1 || step > TOTAL) return;
        current = step;
        updateUI();
    }

    function updateUI() {
        // Paneles
        panels.forEach(p => p.classList.remove('active'));
        document.getElementById(`panel-${current}`).classList.add('active');

        // Stepper circles & labels
        steps.forEach(s => {
            const n = +s.dataset.step;
            s.classList.remove('active', 'done');
            if (n === current) s.classList.add('active');
            else if (n < current) s.classList.add('done');
        });

        // Líneas entre pasos
        lines.forEach((line, i) => {
            line.classList.toggle('done', i + 1 < current);
        });

        // Botones
        btnPrev.disabled = current === 1;

        if (current === TOTAL) {
            btnNext.style.display = 'none';
            // Paso 5: Verificar colaboradores
            const btnPublish = document.getElementById('btn-publish');
            const collabMessage = document.getElementById('collab-publish-message');
            if (collaborators.length > 0) {
                btnPublish.disabled = true;
                btnPublish.textContent = 'Publicar (Bloqueado)';
                if (collabMessage) {
                    collabMessage.classList.remove('sh-hidden');
                }
            } else {
                btnPublish.disabled = false;
                btnPublish.textContent = 'Publicar historia';
                if (collabMessage) {
                    collabMessage.classList.add('sh-hidden');
                }
            }
        } else {
            btnNext.style.display = '';
        }

        // Actualizar vista previa si estamos en paso 4
        if (current === 4) refreshPreview();
    }

    // ───────────── Validación de pasos ──────────────────
    function validateCurrentStep() {
        switch (current) {
            case 1:
                return validateStep1();
            case 2:
                return validateStep2();
            case 3:
                return true; // Paso opcional
            case 4:
                return true; // Vista previa
            case 5:
                return true; // Publicar
            default:
                return false;
        }
    }

    function validateStep1() {
        // Limpiar errores previos
        clearFieldErrors();

        const title = titleInput.value.trim();
        const desc = descInput.value.trim();
        let genre = genreSelect.value;

        if (genre === 'otro') {
            genre = document.getElementById('custom-genre').value.trim();
        }

        let hasErrors = false;

        if (!title) {
            addFieldError(titleInput, 'El título es requerido');
            hasErrors = true;
        } else if (title.length > 100) {
            addFieldError(titleInput, 'El título no puede exceder 100 caracteres');
            hasErrors = true;
        }

        if (!desc) {
            addFieldError(descInput, 'La descripción es requerida');
            hasErrors = true;
        } else if (desc.length > 500) {
            addFieldError(descInput, 'La descripción no puede exceder 500 caracteres');
            hasErrors = true;
        }

        if (!genre) {
            addFieldError(genreSelect, 'El género es requerido');
            hasErrors = true;
        }

        if (hasErrors) {
            showNotification('error', 'Completa todos los campos requeridos antes de continuar.');
            return false;
        }

        return true;
    }

    function validateStep2() {
        // Limpiar errores previos
        clearFieldErrors();

        const htmlFile = htmlInput.files[0];
        const coverFile = coverInput.files[0];

        let hasErrors = false;

        if (!htmlFile) {
            addFieldError(htmlInput, 'Debes seleccionar un archivo HTML');
            hasErrors = true;
        }

        if (!coverFile) {
            addFieldError(coverInput, 'Debes seleccionar una imagen de portada');
            hasErrors = true;
        }

        // Verificar tamaño de recursos
        const hasOversized = resourceFiles.some(item => item.overSize);
        if (hasOversized) {
            showNotification('error', 'Algunos archivos de recursos exceden el límite de 5 MB.');
            hasErrors = true;
        }

        if (hasErrors) {
            return false;
        }

        return true;
    }

    btnNext.addEventListener('click', () => {
        if (validateCurrentStep()) {
            goTo(current + 1);
        }
    });
    btnPrev.addEventListener('click', () => goTo(current - 1));

    // ───────────── Paso 1: Datos básicos ───────────────
    const titleInput    = document.getElementById('story-title');
    const descInput     = document.getElementById('story-desc');
    const genreSelect   = document.getElementById('story-genre');
    const customField   = document.getElementById('custom-genre-field');
    const titleCount    = document.getElementById('title-count');
    const descCount     = document.getElementById('desc-count');

    titleInput.addEventListener('input', () => {
        titleCount.textContent = titleInput.value.length;
        clearFieldError(titleInput);
    });

    descInput.addEventListener('input', () => {
        descCount.textContent = descInput.value.length;
        clearFieldError(descInput);
    });

    genreSelect.addEventListener('change', () => {
        if (genreSelect.value === 'otro') {
            customField.classList.remove('sh-hidden');
        } else {
            customField.classList.add('sh-hidden');
        }
        clearFieldError(genreSelect);
    });

    // Limpiar error del campo custom genre cuando se escribe
    document.getElementById('custom-genre').addEventListener('input', () => {
        clearFieldError(document.getElementById('custom-genre'));
    });

    // ───────────── Paso 2: Archivos ────────────────────
    // HTML file
    const htmlInput   = document.getElementById('story-html');
    const htmlInfo    = document.getElementById('html-info');
    const dropHtml    = document.getElementById('drop-html');

    setupDropzone(dropHtml, htmlInput);

    htmlInput.addEventListener('change', () => {
        const file = htmlInput.files[0];
        if (file) {
            htmlInfo.classList.remove('sh-hidden');
            htmlInfo.innerHTML = `
                <i class="fas fa-file-code"></i>
                <span class="sh-file-name">${file.name}</span>
                <span class="sh-file-size">${formatSize(file.size)}</span>
            `;
        }
        clearFieldError(htmlInput);
    });

    // Cover image
    const coverInput    = document.getElementById('story-cover');
    const coverBox      = document.getElementById('cover-preview-box');
    const coverImg      = document.getElementById('cover-preview-img');
    const removeCover   = document.getElementById('remove-cover');
    const dropCover     = document.getElementById('drop-cover');

    setupDropzone(dropCover, coverInput);

    coverInput.addEventListener('change', () => {
        const file = coverInput.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                coverImg.src = e.target.result;
                coverBox.classList.remove('sh-hidden');
            };
            reader.readAsDataURL(file);
        }
        clearFieldError(coverInput);
    });

    removeCover.addEventListener('click', () => {
        coverInput.value = '';
        coverImg.src = '';
        coverBox.classList.add('sh-hidden');
    });

    // Resources
    const resInput      = document.getElementById('story-resources');
    const resList       = document.getElementById('resources-list');
    const dropRes       = document.getElementById('drop-resources');
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB
    let resourceFiles   = [];

    setupDropzone(dropRes, resInput);

    resInput.addEventListener('change', () => {
        Array.from(resInput.files).forEach(file => {
            addResource(file);
        });
        resInput.value = '';
    });

    function addResource(file) {
        const overSize = file.size > MAX_FILE_SIZE;
        const isImage = file.type.startsWith('image/');

        // Separar nombre y extensión
        const lastDotIndex = file.name.lastIndexOf('.');
        const nameWithoutExt = lastDotIndex > 0 ? file.name.substring(0, lastDotIndex) : file.name;
        const extension = lastDotIndex > 0 ? file.name.substring(lastDotIndex) : '';

        if (isImage) {
            // Crear thumbnail para imágenes
            const reader = new FileReader();
            reader.onload = (e) => {
                const thumbnailUrl = e.target.result;
                resourceFiles.push({
                    file,
                    overSize,
                    isImage,
                    thumbnailUrl,
                    customName: nameWithoutExt,
                    extension
                });
                renderResources();
            };
            reader.readAsDataURL(file);
        } else {
            // Para archivos no imagen, agregar directamente
            resourceFiles.push({
                file,
                overSize,
                isImage: false,
                thumbnailUrl: null,
                customName: nameWithoutExt,
                extension
            });
            renderResources();
        }
    }

    function renderResources() {
        resList.innerHTML = '';
        resourceFiles.forEach((item, idx) => {
            const li = document.createElement('li');
            if (item.overSize) li.classList.add('sh-error');

            // Compatibilidad hacia atrás: si no tiene customName/extension, crearlos
            if (item.customName === undefined) {
                const lastDotIndex = item.file.name.lastIndexOf('.');
                item.customName = lastDotIndex > 0 ? item.file.name.substring(0, lastDotIndex) : item.file.name;
                item.extension = lastDotIndex > 0 ? item.file.name.substring(lastDotIndex) : '';
            }

            const displayName = (item.customName || 'archivo') + (item.extension || '');

            let previewElement = '';
            if (item.isImage && item.thumbnailUrl) {
                previewElement = `<img src="${item.thumbnailUrl}" alt="Preview" class="sh-res-thumbnail">`;
            } else {
                const icon = getFileIcon(item.file.type);
                previewElement = `<i class="${icon}"></i>`;
            }

            li.innerHTML = `
                <div class="sh-res-preview">
                    ${previewElement}
                </div>
                <div class="sh-res-info">
                    <div class="sh-res-name-container">
                        <input type="text" class="sh-res-name-input" value="${item.customName || 'archivo'}" data-idx="${idx}" placeholder="Nombre del archivo">
                        <span class="sh-res-extension">${item.extension || ''}</span>
                    </div>
                </div>
                <div class="sh-res-actions">
                    <span class="sh-res-size">${formatSize(item.file.size)}${item.overSize ? ' — Excede 5 MB' : ''}</span>
                    ${item.isImage ? `<button type="button" class="sh-res-view" data-idx="${idx}" title="Ver imagen completa"><i class="fas fa-eye"></i></button>` : ''}
                    <button type="button" class="sh-res-remove" data-idx="${idx}" title="Eliminar"><i class="fas fa-times"></i></button>
                </div>
            `;
            resList.appendChild(li);
        });

        // Event listeners para inputs de nombre
        resList.querySelectorAll('.sh-res-name-input').forEach(input => {
            input.addEventListener('input', (e) => {
                const idx = +e.target.dataset.idx;
                resourceFiles[idx].customName = e.target.value.trim() || 'archivo_sin_nombre';
            });
        });

        // Event listeners para botones de vista
        resList.querySelectorAll('.sh-res-view').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const idx = +e.target.closest('.sh-res-view').dataset.idx;
                const item = resourceFiles[idx];
                if (item.thumbnailUrl) {
                    showImageModal(item.thumbnailUrl, item.customName + item.extension);
                }
            });
        });

        // Remove buttons
        resList.querySelectorAll('.sh-res-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                resourceFiles.splice(+btn.dataset.idx, 1);
                renderResources();
            });
        });
    }

    // ───────────── Paso 3: Colaboradores ───────────────
    const collabSearch  = document.getElementById('collab-search');
    const searchResults = document.getElementById('search-results');
    const collabList    = document.getElementById('collab-list');
    const collabEmpty   = document.getElementById('collab-empty');
    const collabCount   = document.getElementById('collab-count');
    let collaborators   = [];

    collabSearch.addEventListener('input', debounce(() => {
        const q = collabSearch.value.trim();
        if (q.length < 2) {
            searchResults.classList.add('sh-hidden');
            return;
        }

        // Buscar usuarios via AJAX
        apiFetch('/backend/subir_historia/buscar_usuarios.php?q=' + encodeURIComponent(q), {
            method: 'GET'
        }).then(response => response.json()).then(data => {
            if (data.success) {
                const users = data.users.filter(u => !collaborators.some(c => c.user === u.username)); // Excluir ya agregados

                if (users.length === 0) {
                    searchResults.classList.add('sh-hidden');
                    return;
                }

                searchResults.innerHTML = users.map(u => `
                    <div class="sh-search-result" data-user='${JSON.stringify({ name: u.name, user: u.username })}'>
                        <div class="sh-result-avatar">${u.name.charAt(0)}</div>
                        <div class="sh-result-info">
                            <div class="sh-result-name">${u.name}</div>
                            <div class="sh-result-user">@${u.username}</div>
                        </div>
                        <button type="button" class="sh-result-add"><i class="fas fa-plus"></i> Agregar</button>
                    </div>
                `).join('');

                searchResults.classList.remove('sh-hidden');

                // Add handlers
                searchResults.querySelectorAll('.sh-search-result').forEach(el => {
                    el.addEventListener('click', () => {
                        const userData = JSON.parse(el.dataset.user);
                        collaborators.push(userData);
                        renderCollaborators();
                        collabSearch.value = '';
                        searchResults.classList.add('sh-hidden');
                    });
                });
            } else {
                searchResults.classList.add('sh-hidden');
            }
        }).catch(() => {
            searchResults.classList.add('sh-hidden');
        });
    }, 250));

    function renderCollaborators() {
        collabCount.textContent = `(${collaborators.length})`;
        if (collaborators.length === 0) {
            collabEmpty.classList.remove('sh-hidden');
            collabList.innerHTML = '';
            return;
        }
        collabEmpty.classList.add('sh-hidden');
        collabList.innerHTML = collaborators.map((c, idx) => `
            <li class="sh-collab-item">
                <div class="sh-collab-avatar">${c.name.charAt(0)}</div>
                <div class="sh-collab-info">
                    <div class="sh-collab-name">${c.name}</div>
                    <div class="sh-collab-user">${c.user}</div>
                </div>
                <button type="button" class="sh-collab-remove" data-idx="${idx}"><i class="fas fa-times"></i> Quitar</button>
            </li>
        `).join('');

        collabList.querySelectorAll('.sh-collab-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                collaborators.splice(+btn.dataset.idx, 1);
                renderCollaborators();
            });
        });
    }

    // ───────────── Paso 4: Vista previa ────────────────
    function refreshPreview() {
        // Título
        const title = titleInput.value.trim() || 'Título de tu historia';
        document.getElementById('preview-title').textContent = title;

        // Descripción
        const desc = descInput.value.trim() || 'La descripción de tu historia aparecerá aquí...';
        document.getElementById('preview-desc').textContent = desc;

        // Género
        let genre = 'Género';
        if (genreSelect.value === 'otro') {
            const custom = document.getElementById('custom-genre');
            genre = custom.value.trim() || 'Otro';
        } else if (genreSelect.value) {
            genre = genreSelect.options[genreSelect.selectedIndex].text;
        }
        document.getElementById('preview-genre').textContent = genre;

        // Cover
        if (coverImg.src && !coverImg.src.includes('placeholder')) {
            document.getElementById('preview-cover').src = coverImg.src;
        }
    }

    // ───────────── Paso 5: Publicar / Borrador ─────────
    document.getElementById('btn-publish').addEventListener('click', () => {
        submitHistoria('publicada');
    });

    document.getElementById('btn-draft').addEventListener('click', () => {
        submitHistoria('borrador');
    });

    function submitHistoria(estado) {
        // Verificar colaboradores para publicar
        if (estado === 'publicada' && collaborators.length > 0) {
            showNotification('error', 'No puedes publicar la historia hasta que todos los colaboradores acepten la invitación. Guarda como borrador.');
            return;
        }

        // Validar que todos los pasos estén completos
        if (!validateStep1() || !validateStep2()) {
            showNotification('error', 'Completa todos los campos requeridos antes de continuar.');
            return;
        }

        // Recopilar datos
        const formData = new FormData();
        formData.append('action', 'subir_historia');
        formData.append('titulo', titleInput.value.trim());
        formData.append('descripcion', descInput.value.trim());
        let genre = genreSelect.value;
        if (genre === 'otro') {
            genre = document.getElementById('custom-genre').value.trim();
        }
        formData.append('genero', genre);
        formData.append('estado', estado);

        // Carpeta contenido (opcional)
        const carpetaInput = document.getElementById('story-folder');
        if (carpetaInput && carpetaInput.value.trim()) {
            formData.append('carpeta_contenido', carpetaInput.value.trim());
        }

        // Archivos
        if (htmlInput.files[0]) {
            formData.append('archivo_html', htmlInput.files[0]);
        }
        if (coverInput.files[0]) {
            formData.append('portada', coverInput.files[0]);
        }

        // Recursos
        resourceFiles.forEach(item => {
            formData.append('recursos[]', item.file);
        });

        // Colaboradores
        const colaboradoresUsernames = collaborators.map(c => c.user);
        formData.append('colaboradores', JSON.stringify(colaboradoresUsernames));

        // Enviar via AJAX
        apiFetch('/backend/subir_historia/subir.php', {
            method: 'POST',
            body: formData
        }).then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        }).then(data => {
            console.log('Response data:', data);
            if (data.success) {
                showNotification('success', data.message);
                // Redirigir a explorar o perfil después de un delay
                setTimeout(() => {
                    window.location.href = getBaseUrl() + '/frontend/explorar.php';
                }, 2000);
            } else {
                showNotification('error', data.message);
            }
        }).catch(error => {
            console.error('Fetch error:', error);
            showNotification('error', 'Error al subir la historia: ' + error.message);
        });
    }

    // ───────────── Utilidades ──────────────────────────
    function setupDropzone(zone, input) {
        ['dragenter', 'dragover'].forEach(evt => {
            zone.addEventListener(evt, e => {
                e.preventDefault();
                zone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            zone.addEventListener(evt, e => {
                e.preventDefault();
                zone.classList.remove('dragover');
            });
        });

        zone.addEventListener('drop', e => {
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });

        zone.addEventListener('click', e => {
            if (e.target.tagName !== 'BUTTON') input.click();
        });
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function getFileIcon(type) {
        if (type.startsWith('video/')) return 'fas fa-video';
        if (type === 'image/gif') return 'fas fa-film';
        if (type.startsWith('image/')) return 'fas fa-image';
        return 'fas fa-file';
    }

    function debounce(fn, ms) {
        let timer;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    function clearFieldError(inputElement) {
        const field = inputElement.closest('.sh-field');
        if (field && field.classList.contains('error')) {
            field.classList.remove('error');
            const errorMsg = field.querySelector('.sh-field-error');
            if (errorMsg) errorMsg.remove();
        }
    }

    function clearFieldErrors() {
        document.querySelectorAll('.sh-field.error').forEach(field => {
            field.classList.remove('error');
            const errorMsg = field.querySelector('.sh-field-error');
            if (errorMsg) errorMsg.remove();
        });
    }

    function addFieldError(inputElement, message) {
        const field = inputElement.closest('.sh-field');
        if (field) {
            field.classList.add('error');
            // Agregar mensaje de error si no existe
            if (!field.querySelector('.sh-field-error')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'sh-field-error';
                errorDiv.textContent = message;
                field.appendChild(errorDiv);
            }
        }
    }

    function showImageModal(imageSrc, title) {
        const modal = document.getElementById('image-modal');
        const modalImg = document.getElementById('image-modal-img');
        const modalTitle = document.getElementById('image-modal-title');
        const overlay = document.getElementById('image-modal-overlay');
        const closeBtn = document.getElementById('image-modal-close');

        if (!modal || !modalImg || !modalTitle || !overlay || !closeBtn) {
            console.error('Modal elements not found');
            return;
        }

        modalImg.src = imageSrc;
        modalTitle.textContent = title || 'Vista previa de imagen';

        modal.classList.add('active');

        // Función para cerrar modal
        const closeModal = () => {
            modal.classList.remove('active');
            // Limpiar event listeners después de la transición
            setTimeout(() => {
                overlay.removeEventListener('click', closeClickHandler);
                closeBtn.removeEventListener('click', closeClickHandler);
                document.removeEventListener('keydown', escapeHandler);
            }, 300);
        };

        // Event listeners para cerrar
        const closeClickHandler = (e) => {
            e.stopPropagation();
            closeModal();
        };

        const escapeHandler = (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeModal();
            }
        };

        // Agregar event listeners
        overlay.addEventListener('click', closeClickHandler);
        closeBtn.addEventListener('click', closeClickHandler);
        document.addEventListener('keydown', escapeHandler);
    }

    // Init
    updateUI();
});
