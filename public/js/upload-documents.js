// Constantes de validación (se inicializan en ayuda-card.js que se carga primero)
// Si ayuda-card.js no se ha cargado, inicializar aquí como fallback
if (typeof window.UPLOAD_DOCUMENTS_CONFIG === 'undefined') {
    window.UPLOAD_DOCUMENTS_CONFIG = {
        MAX_FILE_SIZE: 10 * 1024 * 1024, // 10MB en bytes (10240 KB)
        ALLOWED_TYPES: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'],
        ALLOWED_EXTENSIONS: ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.pdf']
    };
}

// Usar directamente el objeto window para evitar conflictos de redeclaración
// No declarar const aquí para evitar errores cuando ambos archivos se cargan

function validateFile(file) {
    // Validar tipo MIME
    if (!window.UPLOAD_DOCUMENTS_CONFIG.ALLOWED_TYPES.includes(file.type)) {
        const extension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
        return {
            valid: false,
            error: `❌ Formato no válido. El archivo "${file.name}" no está permitido. Formatos permitidos: JPG, PNG, GIF, WEBP, PDF.`
        };
    }

    // Validar extensión
    const extension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();
    if (!window.UPLOAD_DOCUMENTS_CONFIG.ALLOWED_EXTENSIONS.includes(extension)) {
        return {
            valid: false,
            error: `❌ Extensión no válida. El archivo "${file.name}" tiene una extensión no permitida. Formatos permitidos: JPG, PNG, GIF, WEBP, PDF.`
        };
    }

    // Validar tamaño
    if (file.size > window.UPLOAD_DOCUMENTS_CONFIG.MAX_FILE_SIZE) {
        const maxSizeMB = (window.UPLOAD_DOCUMENTS_CONFIG.MAX_FILE_SIZE / (1024 * 1024)).toFixed(0);
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        return {
            valid: false,
            error: `❌ Archivo demasiado grande. El archivo "${file.name}" (${fileSizeMB} MB) supera el tamaño máximo permitido de ${maxSizeMB} MB.`
        };
    }

    return { valid: true, error: null };
}

function validateFiles(files) {
    if (!files || files.length === 0) {
        return { valid: false, error: '❌ Debes seleccionar al menos un archivo.' };
    }

    for (let file of files) {
        const validation = validateFile(file);
        if (!validation.valid) {
            return validation;
        }
    }

    return { valid: true, error: null };
}

function setUploadButtonEnabled(enabled) {
    const submitButton = document.querySelector('#uploadForm button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = !enabled;
        if (!enabled) {
            submitButton.style.opacity = '0.6';
            submitButton.style.cursor = 'not-allowed';
        } else {
            submitButton.style.opacity = '1';
            submitButton.style.cursor = 'pointer';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('uploadForm');
    if (!uploadForm) return;

    uploadForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const spinner = document.getElementById('upload-spinner');
        const formContent = document.getElementById('upload-form-content');
        const progress = document.getElementById('upload-progress');
        const progressBar = document.getElementById('upload-progress-bar');
        const singleContainer = document.getElementById('single-upload-container');
        const multiContainer = document.getElementById('multi-upload-container');
        const canvasFirma = document.getElementById('firma-canvas-container');

        // ✅ UI inicial
        clearUploadError();
        setUploadButtonEnabled(false); // Deshabilitar botón durante la subida
        
        let formData = new FormData(this);
        let gradualTimer = null;
        let filesToValidate = [];

        // ✅ Validar archivos antes de subir
        // SINGLE upload
        if (!singleContainer.classList.contains('d-none')) {
            const singleInput = this.querySelector('input[name="file"]');
            if (singleInput && singleInput.files && singleInput.files.length > 0) {
                filesToValidate = Array.from(singleInput.files);
            }
        }

        // MULTI upload
        if (!multiContainer.classList.contains('d-none')) {
            if (selectedFiles.length === 0) {
                showUploadError('❌ Debes añadir al menos un archivo.');
                setUploadButtonEnabled(true);
                return;
            }
            filesToValidate = selectedFiles;
        }

        // Validar archivos
        if (filesToValidate.length > 0) {
            const validation = validateFiles(filesToValidate);
            if (!validation.valid) {
                showUploadError(validation.error);
                setUploadButtonEnabled(true);
                return;
            }
        }

        formContent.classList.add('d-none');
        spinner.classList.remove('d-none');
        progress.classList.remove('d-none');
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';

        // ✅ MULTI upload → sobrescribir files
        if (!multiContainer.classList.contains('d-none')) {
            formData = new FormData();
            formData.append('document_id', this.querySelector('input[name="document_id"]').value);
            formData.append('nombre_personalizado', this.querySelector('input[name="nombre_personalizado"]').value);
            formData.append('slug', this.querySelector('input[name="slug"]').value);
            const convivienteIndexInput = this.querySelector('input[name="conviviente_index"]');
            if (convivienteIndexInput && convivienteIndexInput.value) {
                formData.append('conviviente_index', convivienteIndexInput.value);
            }
            selectedFiles.forEach(file => {
                formData.append('files[]', file);
            });
        }

        // ✅ Firma → capturar canvas
        if (!canvasFirma.classList.contains('d-none')) {
            const canvas = document.getElementById('firma-canvas');
            const blankCanvas = document.createElement('canvas');
            blankCanvas.width = canvas.width;
            blankCanvas.height = canvas.height;
            if (canvas.toDataURL() === blankCanvas.toDataURL()) {
                showUploadError('❌ Por favor, realiza tu firma antes de continuar.');
                resetUI();
                return;
            }
            formData.set('firma_base64', canvas.toDataURL('image/png'));
        }

        // ✅ Envío con AXIOS
        axios.post(this.action, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: function (progressEvent) {
                let percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);

                if (percent < 95) {
                    progressBar.style.width = percent + '%';
                    progressBar.textContent = percent + '%';
                } else {
                    if (!gradualTimer) {
                        let simulated = 95;
                        progressBar.style.width = simulated + '%';
                        progressBar.textContent = simulated + '%';

                        gradualTimer = setInterval(() => {
                            simulated++;
                            if (simulated <= 99) {
                                progressBar.style.width = simulated + '%';
                                progressBar.textContent = simulated + '%';
                            }
                            if (simulated >= 99) {
                                clearInterval(gradualTimer);
                            }
                        }, 250);
                    }
                }
            }
        })
        .then((response) => {
            try {
                clearInterval(gradualTimer);

                console.log("📌 Respuesta del servidor:", response.data);

                // ✅ Comprobamos que el backend dice success
                if (response.data && response.data.success) {
                    progressBar.style.width = '100%';
                    progressBar.textContent = '✅ Documento subido';
                    progressBar.classList.add('bg-success');

                    // 🔄 ACTUALIZAR COMPONENTE DOCUMENTOS
                    console.log("🔄 Actualizando componente documentos...");
                    
                    // Obtener el ID de la contratación desde múltiples fuentes posibles
                    const modal = document.getElementById('uploadModal');
                    const contratacionId = response.data.contratacion_id || 
                        uploadForm.querySelector('input[name="ayuda_solicitada_id"]')?.value ||
                        (modal ? modal.getAttribute('data-ayuda-id') : null) ||
                        uploadForm.closest('[data-ayuda-id]')?.getAttribute('data-ayuda-id');
                    
                    if (contratacionId) {
                        console.log(`✅ Usando contratacion_id: ${contratacionId}`);
                        updateDocumentosComponent(contratacionId);
                        updateDocumentosEstadisticasComponent(contratacionId);
                    } else {
                        // Si no hay contratacion_id, actualizar todas las cards como fallback
                        console.log("⚠️ No se encontró contratacion_id, actualizando todas las cards...");
                        document.querySelectorAll('[data-ayuda-id]').forEach(card => {
                            const id = card.getAttribute('data-ayuda-id');
                            updateDocumentosComponent(id);
                            updateDocumentosEstadisticasComponent(id);
                        });
                    }

                    // 🔄 Reset modal & UI
                    setTimeout(() => {
                        closeModal();
                        resetUI();
                        uploadForm.reset();
                        selectedFiles = [];
                        document.getElementById('single-upload-preview').innerHTML = '';
                        document.getElementById('multi-upload-preview').innerHTML = '';
                        document.getElementById('multi-upload-counter').innerText = '';
                        setUploadButtonEnabled(true);
                    }, 1200);
                } else {
                    console.warn("⚠️ Respuesta inesperada del servidor:", response.data);
                    showUploadError('❌ Error inesperado en la subida.');
                    resetUI();
                    setUploadButtonEnabled(true);
                }
            } catch (e) {
                console.error("❌ Error en el bloque .then():", e);
                resetUI();
                setUploadButtonEnabled(true);
            }
        })
        .catch((error) => {
            clearInterval(gradualTimer);
            console.error("❌ Error en la subida:", error);
            
            let errorMessage = '❌ Error al subir el archivo. Por favor, inténtalo de nuevo.';
            
            // Manejar errores de validación del servidor (422)
            if (error.response && error.response.status === 422) {
                const errors = error.response.data.errors;
                if (errors && errors['files.0']) {
                    // Error específico del primer archivo
                    errorMessage = `❌ ${errors['files.0'][0]}`;
                } else if (errors && errors.files) {
                    // Error general de archivos
                    errorMessage = `❌ ${Array.isArray(errors.files) ? errors.files[0] : errors.files}`;
                } else if (error.response.data.message) {
                    errorMessage = `❌ ${error.response.data.message}`;
                } else if (error.response.data.error) {
                    errorMessage = `❌ ${error.response.data.error}`;
                }
            } else if (error.response && error.response.data && error.response.data.message) {
                errorMessage = `❌ ${error.response.data.message}`;
            } else if (error.message) {
                errorMessage = `❌ ${error.message}`;
            }
            
            showUploadError(errorMessage);
            resetUI();
            setUploadButtonEnabled(true);
        });

// .then((response) => {
//     try {
//         clearInterval(gradualTimer);

//         // ✅ Comprobamos que el backend dice success
//         if (response.data && response.data.success) {
//             progressBar.style.width = '100%';
//             progressBar.textContent = '✅ Documento subido';
//             progressBar.classList.add('bg-success');

//             // 🔄 Buscar la card y refrescar la sección de subsanación
//             const ayudaId = response.data.contratacion_id;
//             const cardElement = document.querySelector(`[data-ayuda-id="${ayudaId}"]`);
//             if (cardElement) {
//                 updateSubsanacionSection(cardElement, ayudaId);
//             }

//             // 🔄 Reset modal & UI
//             setTimeout(() => {
//                 closeModal();
//                 resetUI();
//                 uploadForm.reset();
//                 selectedFiles = [];
//                 document.getElementById('single-upload-preview').innerHTML = '';
//                 document.getElementById('multi-upload-preview').innerHTML = '';
//                 document.getElementById('multi-upload-counter').innerText = '';
//             }, 1200);
//         } else {
//             showUploadError('❌ Error inesperado en la subida.');
//             resetUI();
//         }
//     } catch (e) {
//         resetUI();
//     }
// })



        function resetUI() {
            spinner.classList.add('d-none');
            formContent.classList.remove('d-none');
            progress.classList.add('d-none');
            progressBar.style.width = '0%';
            progressBar.textContent = '0%';
            progressBar.classList.remove('bg-success');
        }
    });

    // ✅ Pop-up de éxito si existe window.showUploadPopup
    if (window.showUploadPopup) {
        const popup = document.getElementById('popup-success');
        if (popup) {
            popup.classList.remove('d-none');
            popup.style.opacity = 1;

            setTimeout(() => {
                popup.style.opacity = 0;
                setTimeout(() => popup.classList.add('d-none'), 500);
            }, 2000);
        }
    }
});

/**
 * Actualiza solo el componente documentos sin recargar la página
 */
function updateDocumentosComponent(ayudaSolicitadaId) {
    if (!ayudaSolicitadaId) {
        console.error('❌ No se proporcionó ayudaSolicitadaId para actualizar documentos');
        return;
    }

    console.log(`🔄 Actualizando componente documentos para ayuda_id: ${ayudaSolicitadaId}`);
    
    fetch(`/ayudas-solicitadas/${ayudaSolicitadaId}/documentos-view`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            // Extraer el contenido interno del HTML recibido (el servidor devuelve el div completo)
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const innerContent = tempDiv.querySelector('[id^="documentos-component-"]')?.innerHTML || html;
            
            // Buscar el contenedor del componente documentos
            const documentosContainer = document.querySelector(`#documentos-component-${ayudaSolicitadaId}`);
            
            if (documentosContainer) {
                // Actualizar el contenido interno
                documentosContainer.innerHTML = innerContent;
                console.log('✅ Componente documentos actualizado correctamente');
            } else {
                // Si no se encuentra el contenedor específico, buscar dentro de la card
                const cardElement = document.querySelector(`[data-ayuda-id="${ayudaSolicitadaId}"]`);
                if (cardElement) {
                    const documentosContainerInCard = cardElement.querySelector('[id^="documentos-component-"]');
                    if (documentosContainerInCard) {
                        documentosContainerInCard.innerHTML = innerContent;
                        console.log('✅ Componente documentos actualizado correctamente (dentro de card)');
                    } else {
                        // Buscar el contenedor docs-{id} como fallback
                        const docsContainer = cardElement.querySelector(`#docs-${ayudaSolicitadaId}`);
                        if (docsContainer) {
                            docsContainer.innerHTML = innerContent;
                            console.log('✅ Componente documentos actualizado correctamente (fallback)');
                        } else {
                            console.warn('⚠️ No se encontró el contenedor del componente documentos');
                        }
                    }
                } else {
                    console.warn(`⚠️ No se encontró la card con ayuda_id: ${ayudaSolicitadaId}`);
                }
            }
        })
        .catch(error => {
            console.error('❌ Error al actualizar el componente documentos:', error);
        });
}

/**
 * Actualiza solo el componente documentos-estadisticas sin recargar la página
 */
function updateDocumentosEstadisticasComponent(ayudaSolicitadaId) {
    if (!ayudaSolicitadaId) {
        console.error('❌ No se proporcionó ayudaSolicitadaId para actualizar estadísticas de documentos');
        return;
    }

    console.log(`🔄 Actualizando componente documentos-estadisticas para ayuda_id: ${ayudaSolicitadaId}`);
    
    fetch(`/ayudas-solicitadas/${ayudaSolicitadaId}/documentos-estadisticas-view`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            // Extraer el contenido interno del HTML recibido (el servidor devuelve el div completo)
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            const innerContent = tempDiv.querySelector('[id^="documentos-estadisticas-component-"]')?.innerHTML || html;
            
            // Buscar el contenedor del componente documentos-estadisticas
            const estadisticasContainer = document.querySelector(`#documentos-estadisticas-component-${ayudaSolicitadaId}`);
            
            if (estadisticasContainer) {
                // Actualizar el contenido interno
                estadisticasContainer.innerHTML = innerContent;
                console.log('✅ Componente documentos-estadisticas actualizado correctamente');
            } else {
                // Si no se encuentra el contenedor específico, buscar dentro de la página
                const estadisticasContainerInPage = document.querySelector('[id^="documentos-estadisticas-component-"]');
                if (estadisticasContainerInPage) {
                    estadisticasContainerInPage.innerHTML = innerContent;
                    console.log('✅ Componente documentos-estadisticas actualizado correctamente (búsqueda alternativa)');
                } else {
                    console.warn('⚠️ No se encontró el contenedor del componente documentos-estadisticas');
                }
            }
        })
        .catch(error => {
            console.error('❌ Error al actualizar el componente documentos-estadisticas:', error);
        });
}
