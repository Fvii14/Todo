/* =======================================================
   📁 JS GLOBAL PARA COMPONENTE <x-ayuda-card />
   - Manejo de modal de subida de documentos
   - Slider de recibos
   - Actualización dinámica de tarjetas de ayuda
   - Enlace de convivientes
   ======================================================= */

/* ================================
   1️⃣ MODAL DE SUBIDA DE DOCUMENTOS
   ================================ */
let selectedFiles = [];

function clearModalState() {
    const singleFileInput = document.querySelector('input[name="file"]');
    if (singleFileInput) singleFileInput.value = "";

    const multiFileInput = document.getElementById("multi-upload-input");
    if (multiFileInput) multiFileInput.value = "";

    selectedFiles = [];
    document.getElementById("multi-upload-preview").innerHTML = "";
    document.getElementById("single-upload-preview").innerHTML = "";
    document.getElementById("multi-upload-counter").innerText = "";

    const firmaInput = document.getElementById("firma_base64");
    if (firmaInput) firmaInput.value = "";

    const canvas = document.getElementById("firma-canvas");
    const ctx = canvas?.getContext("2d");
    if (canvas && ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);

    const errorMsg = document.getElementById("upload-error");
    if (errorMsg) {
        errorMsg.classList.add("d-none");
        errorMsg.innerText = "";
    }

    const firmaError = document.getElementById("firma-error");
    if (firmaError) firmaError.classList.add("d-none");
    
    // Limpiar conviviente_index
    const convivienteIndexInput = document.getElementById("modal-conviviente-index");
    if (convivienteIndexInput) convivienteIndexInput.value = "";
    // Habilitar botón al limpiar el estado
    setUploadButtonEnabled(true);
}

// Función específica para abrir modal de documentos de convivientes
function openModalConviviente(
    documentId,
    documentName,
    slug = "",
    multiUpload = false,
    description = "",
    convivienteId = null,
    convivienteIndex = null,
    ayudaSolicitadaId = null,
) {
    openModal(documentId, documentName, slug, multiUpload, description, ayudaSolicitadaId, false, null, convivienteIndex);
}

function openModal(
    documentId,
    documentName,
    slug = "",
    multiUpload = false,
    description = "",
    ayudaSolicitadaId = null,
    isSubsanacion = false,
    subsanacionDocId = null,
    convivienteIndex = null,
) {
    const modal = document.getElementById("uploadModal");
    const form = document.getElementById("uploadForm");

    clearModalState();

    // Siempre usar la ruta normal de subida de documentos
    form.action = `/subir-documento`;

    document.getElementById("modal-doc-description").innerHTML = (
        description || ""
    ).replace(/\n/g, "<br>");

    form.querySelector('input[name="document_id"]').value = documentId;
    form.querySelector('input[name="nombre_personalizado"]').value =
        documentName;
    form.querySelector('input[name="slug"]').value = slug;
    
    // Añadir conviviente_index si se proporciona
    const convivienteIndexInput = document.getElementById("modal-conviviente-index");
    if (convivienteIndexInput) {
        convivienteIndexInput.value = convivienteIndex !== null && convivienteIndex !== undefined ? convivienteIndex : '';
    }

    document.getElementById("modal-document-id").value = documentId;
    document.getElementById("modal-doc-name").innerText = documentName;

    // Mostrar los bloques correctos (firma, multi, single)
    const canvasFirma = document.getElementById("firma-canvas-container");
    const singleContainer = document.getElementById("single-upload-container");
    const multiContainer = document.getElementById("multi-upload-container");

    if (documentName.toLowerCase().includes("firma")) {
        canvasFirma.classList.remove("d-none");
        singleContainer.classList.add("d-none");
        multiContainer.classList.add("d-none");
    } else if (multiUpload) {
        canvasFirma.classList.add("d-none");
        singleContainer.classList.add("d-none");
        multiContainer.classList.remove("d-none");
    } else {
        canvasFirma.classList.add("d-none");
        singleContainer.classList.remove("d-none");
        multiContainer.classList.add("d-none");
    }

    modal.classList.remove("hidden");

    if (ayudaSolicitadaId) {
        modal.setAttribute("data-ayuda-id", ayudaSolicitadaId);
    }
    // Asegurar que el botón esté habilitado al abrir el modal
    setUploadButtonEnabled(true);
}

function closeModal() {
    const modal = document.getElementById("uploadModal");
    clearModalState();
    modal.classList.add("hidden");
}

// Constantes de validación (deben coincidir con el backend)
// Usar window para evitar conflictos si se carga múltiples veces
if (typeof window.UPLOAD_DOCUMENTS_CONFIG === 'undefined') {
    window.UPLOAD_DOCUMENTS_CONFIG = {
        MAX_FILE_SIZE: 10 * 1024 * 1024, // 10MB en bytes (10240 KB)
        ALLOWED_TYPES: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'],
        ALLOWED_EXTENSIONS: ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.pdf']
    };
}

// Usar directamente el objeto window para evitar conflictos de redeclaración
// No declarar const aquí para evitar errores cuando ambos archivos se cargan

/* ✅ Previews (single/multi) */
function handleSinglePhoto(input) {
    const files = input.files;
    const previewContainer = document.getElementById("single-upload-preview");
    const counter = document.getElementById("multi-upload-counter");
    const file = files[0];

    previewContainer.innerHTML = "";
    counter.innerText = "";

    if (!file) {
        clearUploadError();
        setUploadButtonEnabled(true);
        return;
    }

    // Validar tipo MIME
    if (!window.UPLOAD_DOCUMENTS_CONFIG.ALLOWED_TYPES.includes(file.type)) {
        const extension = file.name.substring(file.name.lastIndexOf(".")).toLowerCase();
        showUploadError(`❌ Formato no válido. El archivo "${file.name}" no está permitido. Formatos permitidos: JPG, PNG, GIF, WEBP, PDF.`);
        input.value = "";
        setUploadButtonEnabled(false);
        return;
    }

    // Validar extensión
    const extension = file.name.substring(file.name.lastIndexOf(".")).toLowerCase();
    if (!window.UPLOAD_DOCUMENTS_CONFIG.ALLOWED_EXTENSIONS.includes(extension)) {
        showUploadError(`❌ Extensión no válida. El archivo "${file.name}" tiene una extensión no permitida. Formatos permitidos: JPG, PNG, GIF, WEBP, PDF.`);
        input.value = "";
        setUploadButtonEnabled(false);
        return;
    }

    // Validar tamaño
    if (file.size > window.UPLOAD_DOCUMENTS_CONFIG.MAX_FILE_SIZE) {
        const maxSizeMB = (window.UPLOAD_DOCUMENTS_CONFIG.MAX_FILE_SIZE / (1024 * 1024)).toFixed(0);
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        showUploadError(`❌ Archivo demasiado grande. El archivo "${file.name}" (${fileSizeMB} MB) supera el tamaño máximo permitido de ${maxSizeMB} MB.`);
        input.value = "";
        setUploadButtonEnabled(false);
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        const previewDiv = createPreviewBox(file, e.target.result);
        previewContainer.appendChild(previewDiv);
        counter.innerText = `1 archivo seleccionado`;
    };
    reader.readAsDataURL(file);

    clearUploadError();
    setUploadButtonEnabled(true);
}

function handleNewPhoto() {
    const input = document.getElementById("multi-upload-input");
    const files = input.files;
    let hasErrors = false;
    let errorMessages = [];

    if (files && files.length > 0) {
        for (let file of files) {
            // Validar tipo MIME
            if (!window.UPLOAD_DOCUMENTS_CONFIG.ALLOWED_TYPES.includes(file.type)) {
                const extension = file.name.substring(file.name.lastIndexOf(".")).toLowerCase();
                errorMessages.push(`El archivo "${file.name}" tiene un formato no permitido.`);
                hasErrors = true;
                continue;
            }

            // Validar extensión
            const extension = file.name.substring(file.name.lastIndexOf(".")).toLowerCase();
            if (!window.UPLOAD_DOCUMENTS_CONFIG.ALLOWED_EXTENSIONS.includes(extension)) {
                errorMessages.push(`El archivo "${file.name}" tiene una extensión no permitida.`);
                hasErrors = true;
                continue;
            }

            // Validar tamaño
            if (file.size > window.UPLOAD_DOCUMENTS_CONFIG.MAX_FILE_SIZE) {
                const maxSizeMB = (window.UPLOAD_DOCUMENTS_CONFIG.MAX_FILE_SIZE / (1024 * 1024)).toFixed(0);
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                errorMessages.push(`El archivo "${file.name}" (${fileSizeMB} MB) supera el tamaño máximo de ${maxSizeMB} MB.`);
                hasErrors = true;
                continue;
            }

            const yaExiste = selectedFiles.some(
                (f) => f.name === file.name && f.size === file.size,
            );
            if (yaExiste) continue;

            selectedFiles.push(file);

            const reader = new FileReader();
            reader.onload = function (e) {
                const previewDiv = createPreviewBox(
                    file,
                    e.target.result,
                    true,
                );
                document
                    .getElementById("multi-upload-preview")
                    .appendChild(previewDiv);
                document.getElementById("multi-upload-counter").innerText =
                    `${selectedFiles.length} archivo(s) seleccionado(s)`;
            };
            reader.readAsDataURL(file);
        }
        // Mostrar errores si los hay
        if (hasErrors) {
            const errorMsg = `❌ ${errorMessages.join(" ")} Formatos permitidos: JPG, PNG, GIF, WEBP, PDF. Tamaño máximo: ${(window.UPLOAD_DOCUMENTS_CONFIG.MAX_FILE_SIZE / (1024 * 1024)).toFixed(0)} MB.`;
            showUploadError(errorMsg);
            setUploadButtonEnabled(false);
        } else {
            clearUploadError();
            setUploadButtonEnabled(selectedFiles.length > 0);
        }
    } else {
        clearUploadError();
        setUploadButtonEnabled(selectedFiles.length > 0);
    }

    input.value = "";
}

/* ✅ Creación de cajitas de preview */
function createPreviewBox(file, imageUrl, isMulti = false) {
    const previewDiv = document.createElement("div");
    previewDiv.style.position = "relative";
    previewDiv.style.width = "100px";
    previewDiv.style.height = "100px";
    previewDiv.style.border = "1px solid #ccc";
    previewDiv.style.borderRadius = "5px";
    previewDiv.style.margin = "5px";

    if (file.type === "application/pdf") {
        previewDiv.style.backgroundImage = "url(/imagenes/icono-pdf.png)";
        previewDiv.style.backgroundSize = "contain";
        previewDiv.style.backgroundRepeat = "no-repeat";
        previewDiv.style.backgroundPosition = "center";
    } else {
        previewDiv.style.backgroundImage = `url(${imageUrl})`;
        previewDiv.style.backgroundSize = "cover";
        previewDiv.style.backgroundPosition = "center";
    }

    const closeButton = document.createElement("button");
    closeButton.innerHTML = "❌";
    closeButton.style.position = "absolute";
    closeButton.style.top = "2px";
    closeButton.style.right = "2px";
    closeButton.style.background = "rgba(0,0,0,0.6)";
    closeButton.style.color = "white";
    closeButton.style.border = "none";
    closeButton.style.borderRadius = "50%";
    closeButton.style.cursor = "pointer";
    closeButton.style.width = "20px";
    closeButton.style.height = "20px";
    closeButton.style.fontSize = "14px";

    closeButton.addEventListener("click", function () {
        previewDiv.remove();
        if (isMulti) {
            selectedFiles = selectedFiles.filter((f) => f !== file);
            document.getElementById("multi-upload-counter").innerText =
                `${selectedFiles.length} archivo(s) seleccionado(s)`;
        }
        clearUploadError();
        setUploadButtonEnabled(selectedFiles.length > 0 || document.querySelector('input[name="file"]')?.files?.length > 0);
    });

    previewDiv.appendChild(closeButton);
    return previewDiv;
}

/* ✅ Errores */
function showUploadError(msg) {
    const errorMsg = document.getElementById("upload-error");
    if (errorMsg) {
        errorMsg.innerText = msg;
        errorMsg.classList.remove("d-none");
    }
}

function clearUploadError() {
    const errorMsg = document.getElementById("upload-error");
    if (errorMsg) {
        errorMsg.innerText = "";
        errorMsg.classList.add("d-none");
    }
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

/* ================================
   2️⃣ SLIDER DE RECIBOS
   ================================ */
function scrollToCard(direction, sliderId) {
    const slider = document.getElementById(sliderId);
    if (!slider) return;

    const cards = slider.querySelectorAll(".card-recibo");
    if (!cards.length) return;

    const scrollLeft = slider.scrollLeft;
    const containerWidth = slider.offsetWidth;

    let closestCard = null;
    let minDiff = Infinity;

    cards.forEach((card) => {
        const cardCenter = card.offsetLeft + card.offsetWidth / 2;
        const sliderCenter = scrollLeft + containerWidth / 2;
        const diff = Math.abs(cardCenter - sliderCenter);

        if (diff < minDiff) {
            minDiff = diff;
            closestCard = card;
        }
    });

    const currentIndex = Array.from(cards).indexOf(closestCard);
    const newIndex =
        direction === "right"
            ? Math.min(currentIndex + 1, cards.length - 1)
            : Math.max(currentIndex - 1, 0);

    const targetCard = cards[newIndex];
    const targetScroll =
        targetCard.offsetLeft -
        (containerWidth / 2 - targetCard.offsetWidth / 2);

    slider.scrollTo({
        left: targetScroll,
        behavior: "smooth",
    });
}

/* ================================
   3️⃣ ACTUALIZACIÓN DE TARJETAS (AJAX)
   ================================ */
function updateAyudaSolicitadaCard(ayudaSolicitadaId) {
    fetch(`/ayudas-solicitadas/${ayudaSolicitadaId}`)
        .then((response) => response.json())
        .then((data) => {
            const ayudaSolicitada = data.ayudaSolicitada;
            const cardElement = document.querySelector(
                `[data-ayuda-id="${ayudaSolicitadaId}"]`,
            );
            if (!cardElement) return;

            // 🟢 Actualizar estado y barra (como ya tienes)
            const estadoElement = cardElement.querySelector(".fs-5");
            if (estadoElement) {
                estadoElement.textContent =
                    ayudaSolicitada.estado.charAt(0).toUpperCase() +
                    ayudaSolicitada.estado.slice(1);
                estadoElement.className =
                    "text-center px-2 py-1 rounded inline-block fs-5";
            }

            const progressBar = cardElement.querySelector(".progress-bar");
            if (progressBar) {
                const porcentaje = getPorcentajeEstado(ayudaSolicitada.estado);
                progressBar.style.width = porcentaje + "%";
            }

            updateDocumentosSection(cardElement, ayudaSolicitada);

            updateSubsanacionSection(cardElement, ayudaSolicitada.id);
        })
        .catch((error) =>
            console.error("Error al actualizar la tarjeta:", error),
        );
}

function getPorcentajeEstado(estado) {
    switch (estado) {
        case "concedida":
        case "rechazada":
            return 100;
        case "tramitada":
            return 75;
        case "tramitación":
            return 50;
        case "documentación":
            return 25;
        default:
            return 0;
    }
}

function updateDocumentosSection(cardElement, ayudaSolicitada) {
    const documentosContainer = cardElement.querySelector(
        "#docs-" + ayudaSolicitada.id,
    );
    if (!documentosContainer) return;

    documentosContainer.innerHTML = "";

    const faltantes = ayudaSolicitada.documentos_faltantes || [];
    const subidos = {};
    if (Array.isArray(ayudaSolicitada.user_documents)) {
        ayudaSolicitada.user_documents.forEach((doc) => {
            if (doc.slug) {
                subidos[doc.slug] = doc;
            }
        });
    }

    const hayRecibosFaltantes = faltantes.some(
        (doc) => doc.name?.includes("Recibo") || doc.slug?.includes("recibo"),
    );
    const hayRecibosSubidos = Object.values(subidos).some((doc) =>
        doc.slug?.includes("recibo"),
    );

    if (hayRecibosFaltantes || hayRecibosSubidos) {
        const recibosSection = createRecibosSection(ayudaSolicitada);
        documentosContainer.appendChild(recibosSection);
    }

    const otrosDocs = faltantes.filter((doc) => {
        const esRecibo =
            doc.name?.toLowerCase().includes("recibo") ||
            doc.slug?.toLowerCase().includes("recibo");
        const yaSubido = subidos[doc.slug] !== undefined;
        //console.log("Ya subido:", yaSubido, " - Documento:", doc.slug, " - Es recibo:", esRecibo);
        return !esRecibo && !yaSubido;
    });

    // ✅ Llamamos SIEMPRE a createOtrosDocumentosList (aunque no haya faltantes)
    const otrosDocsList = createOtrosDocumentosList(
        faltantes,
        subidos,
        ayudaSolicitada.id,
        ayudaSolicitada,
    );
    if (otrosDocsList && otrosDocsList.children.length > 0) {
        documentosContainer.appendChild(otrosDocsList);
    }

    if (
        faltantes.length === 0 &&
        !hayRecibosFaltantes &&
        !hayRecibosSubidos &&
        !pendientesList
    ) {
        const noDocsMessage = document.createElement("p");
        noDocsMessage.className = "text-success fw-bold";
        noDocsMessage.textContent =
            "✅ Todos los documentos han sido subidos correctamente";
        documentosContainer.appendChild(noDocsMessage);
    }
    
    setupComoConseguirloButtons(documentosContainer);
}

function setupComoConseguirloButtons(container) {
    const buttons = container.querySelectorAll('.btn-como-conseguirlo');
    buttons.forEach(button => {
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        newButton.addEventListener('click', function() {
            if (window.showInformativeDocSidebar) {
                const header = decodeHtmlAttribute(newButton.getAttribute('data-header') || '');
                const text = decodeHtmlAttribute(newButton.getAttribute('data-text') || '');
                const link = decodeHtmlAttribute(newButton.getAttribute('data-link') || '');
                const linkText = decodeHtmlAttribute(newButton.getAttribute('data-link-text') || '');
                const docId = parseInt(newButton.getAttribute('data-doc-id')) || 0;
                const docName = decodeHtmlAttribute(newButton.getAttribute('data-doc-name') || '');
                const docSlug = decodeHtmlAttribute(newButton.getAttribute('data-doc-slug') || '');
                const multiUpload = newButton.getAttribute('data-multi-upload') === 'true';
                
                window.showInformativeDocSidebar(header, text, link, linkText, docId, docName, docSlug, multiUpload);
            } else {
                console.error('showInformativeDocSidebar not found');
            }
        });
    });
}

function decodeHtmlAttribute(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.innerHTML = str.replace(/&#39;/g, "'").replace(/&quot;/g, '"').replace(/&amp;/g, '&');
    return div.textContent || div.innerText || '';
}

/* ================================
   4️⃣ ENLACE DE CONVIVIENTES
   ================================ */
function generarEnlaceConviviente(index, questionnaireId) {
    fetch("/conviviente/generar-enlace", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
        body: JSON.stringify({
            index: index,
            questionnaire_id: questionnaireId,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.url) {
                mostrarModalConEnlace(data.url);
            } else {
                alert("❌ Error al generar el enlace.");
            }
        });
}

function mostrarModalConEnlace(url) {
    document.getElementById("referralLink").value = url;

    document.getElementById("shareWhatsapp").href =
        `https://wa.me/?text=${encodeURIComponent("Completa tu parte del formulario aquí: " + url)}`;
    document.getElementById("shareTelegram").href =
        `https://t.me/share/url?url=${encodeURIComponent(url)}&text=Completa tu parte del formulario aquí`;
    document.getElementById("shareX").href =
        `https://twitter.com/intent/tweet?text=${encodeURIComponent("Completa tu parte del formulario aquí: " + url)}`;
    document.getElementById("shareEmail").href =
        `mailto:?subject=Formulario de ayuda&body=Rellena tu parte aquí: ${url}`;

    document.getElementById("referralModal").classList.remove("hidden");
}

function closeReferralModal() {
    document.getElementById("referralModal").classList.add("hidden");
}

// --- FUNCIÓN AUXILIAR PARA RECIBOS ---
function extraerMesAnioDesdeNombre(name) {
    if (!name) return "unknown";
    const pattern =
        /(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\s+(\d{4})/i;
    const match = name.match(pattern);
    if (match) {
        const mesNombre = match[1].toLowerCase();
        const anio = match[2];
        const meses = {
            enero: "01",
            febrero: "02",
            marzo: "03",
            abril: "04",
            mayo: "05",
            junio: "06",
            julio: "07",
            agosto: "08",
            septiembre: "09",
            octubre: "10",
            noviembre: "11",
            diciembre: "12",
        };
        const mes = meses[mesNombre] || "00";
        return `${anio}_${mes}`;
    }
    return "unknown";
}

// Alias para updateCard que llama a la función existente
function updateCard(ayudaSolicitada) {
    if (ayudaSolicitada && ayudaSolicitada.id) {
        updateAyudaSolicitadaCard(ayudaSolicitada.id);
    }
}

function escapeHtmlAttribute(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

// Función para crear la sección de recibos
function createRecibosSection(ayudaSolicitada) {
    const section = document.createElement("div");
    section.className = "accordion mb-4";
    section.id = "accordionRecibos";

    const accordionItem = document.createElement("div");
    accordionItem.className = "accordion-item";

    let docReciboBase = window.reciboDocInfo || {
        id: 7,
        name: "Recibos mensuales del alquiler",
        slug: "justificantes-pago-alquiler",
        informative_header_text: "",
        informative_clickable_text: "",
        informative_link: "",
        informative_link_text: "",
    };

    // Obtener recibos faltantes (excluyendo los que ya están subidos)
    const recibosFaltantes = ayudaSolicitada.documentos_faltantes.filter(
        (doc) => {
            // Solo incluir si es un recibo
            const esRecibo =
                doc.name &&
                (doc.name.includes("Recibo") ||
                    (doc.slug && doc.slug.includes("recibo")));
            if (!esRecibo) return false;

            // Excluir si ya está subido
            const yaSubido =
                ayudaSolicitada.recibos_subidos &&
                ayudaSolicitada.recibos_subidos[doc.slug];

            return !yaSubido;
        },
    );

    // Obtener recibos subidos (ahora es un objeto keyed por slug)
    const recibosSubidos = ayudaSolicitada.recibos_subidos || {};

    // Crear un mapa de todos los recibos (faltantes + subidos)
    const todosLosRecibos = new Map();

    // Añadir recibos faltantes
    recibosFaltantes.forEach((doc) => {
        todosLosRecibos.set(doc.slug, {
            ...doc,
            estado: "faltante",
        });
    });

    // Añadir recibos subidos SOLO si están en estado 'pendiente' o 'rechazado'
    Object.keys(recibosSubidos).forEach((slug) => {
        const doc = recibosSubidos[slug];
        if (
            doc &&
            doc.slug &&
            (doc.estado === "pendiente" || doc.estado === "rechazado")
        ) {
            // Generar el nombre del recibo desde el slug si no tiene name
            let nombreRecibo = doc.name;
            if (!nombreRecibo && doc.slug) {
                const match = doc.slug.match(/recibo_(\d{4})_(\d{2})/);
                if (match) {
                    const anio = match[1];
                    const mes = match[2];
                    const meses = {
                        "01": "enero",
                        "02": "febrero",
                        "03": "marzo",
                        "04": "abril",
                        "05": "mayo",
                        "06": "junio",
                        "07": "julio",
                        "08": "agosto",
                        "09": "septiembre",
                        10: "octubre",
                        11: "noviembre",
                        12: "diciembre",
                    };
                    nombreRecibo = `Recibo de alquiler de ${meses[mes]} ${anio}`;
                }
            }
            todosLosRecibos.set(doc.slug, {
                ...doc,
                name: nombreRecibo,
                estado: doc.estado || "subido",
            });
        }
    });

    let accordionContent = `
            <p class="fw-bold text-dark mb-3">📆 Recibos mensuales del alquiler:</p>
                <div id="collapseRecibos-${ayudaSolicitada.id}"
                    class="accordion-collapse collapse show"
                    aria-labelledby="headingRecibos-${ayudaSolicitada.id}"
                    data-bs-parent="#accordionRecibos">
                    <div class="accordion-body d-flex justify-content-center align-items-center position-relative"
                    style="background-color: #cfe2ff; padding: 20px; min-height: 300px;">

                        <button
                            onclick="scrollToCard('left', 'reciboSlider-${ayudaSolicitada.id}')"
                            class="btn btn-light position-absolute top-50 start-0 translate-middle-y shadow"
                            style="z-index: 10; margin-left: -15px; background-color: #6495ED; border: none;">
                            ‹
                        </button>

                        <button
                            onclick="scrollToCard('right', 'reciboSlider-${ayudaSolicitada.id}')"
                            class="btn btn-light position-absolute top-50 end-0 translate-middle-y shadow"
                            style="z-index: 10; margin-right: -15px; background-color: #6495ED; border: none;">
                            ›
                        </button>

                        <div id="reciboSlider-${ayudaSolicitada.id}"
                            class="d-flex gap-3 px-3 mx-auto"
                            style="max-width: 960px; overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none; min-height: 250px; padding-bottom: 10px;">
    `;

    todosLosRecibos.forEach((doc, slug) => {
        // console.log('Procesando recibo:', {
        //     doc,
        //     slug
        // });
        const mesAnio = doc.name
            ? extraerMesAnioDesdeNombre(doc.name)
            : "unknown";
        const slugConMes = doc.slug;
        const docSubido = doc.estado !== "faltante" ? doc : null;

        let background = "white";
        if (docSubido?.estado === "pendiente") {
            background = "bg-warning-subtle";
        } else if (docSubido?.estado === "rechazado") {
            background = "bg-danger-subtle";
        }

        accordionContent += `
        <div class="card card-recibo p-3 shadow-sm text-center ${background}" 
            style="min-width: 180px; max-width: 180px; height: 230px; display: flex; flex-direction: column; justify-content: space-between;">
            
            <div class="d-flex justify-content-center mb-2">
                <img src="/imagenes/—Pngtree—danger sign flat icon vector_9133214.png" 
                    alt="Falta documento" style="width: 20px; height: 20px;">
            </div>

            <div class="fw-bold medium">
                ${doc.name}
            </div>
        `;

        if (docSubido && docSubido.estado === "pendiente") {
            accordionContent += `
                <div class="text-muted medium">⏳ En revisión por nuestro equipo</div>
            `;
        } else if (docSubido && docSubido.estado === "rechazado") {
            const botonComoConseguirlo = (docReciboBase && docReciboBase.informative_clickable_text) ? `
                <button type="button" class="btn btn-sm btn-outline-secondary btn-como-conseguirlo" 
                    data-header="${escapeHtmlAttribute(docReciboBase.informative_header_text || '')}"
                    data-text="${escapeHtmlAttribute(docReciboBase.informative_clickable_text || '')}"
                    data-link="${escapeHtmlAttribute(docReciboBase.informative_link || '')}"
                    data-link-text="${escapeHtmlAttribute(docReciboBase.informative_link_text || '')}"
                    data-doc-id="${doc.id}"
                    data-doc-name="${escapeHtmlAttribute(doc.name || '')}"
                    data-doc-slug="${escapeHtmlAttribute(slugConMes || '')}"
                    data-multi-upload="false">
                    <i class="fas fa-question-circle me-1"></i>¿Cómo conseguirlo?
                </button>
            ` : '';
            
            accordionContent += `
                <div class="w-100 d-flex flex-column align-items-center align-items-md-start">
                    <div class="text-danger medium mb-2 text-center text-md-start">❌ Rechazado, vuelve a subirlo</div>
                    <div class="w-100 d-flex flex-column gap-2 align-items-center">
                        <button class="btn btn-sm btn-warning btn-secondary" onclick="openModal(${doc.id}, '${doc.name}', '${slugConMes}', false, '', ${ayudaSolicitada.id})" style="display: block !important; visibility: visible !important;">
                            Reintentar subida
                        </button>
                        ${botonComoConseguirlo}
                    </div>
                </div>
            `;
        } else {
            const botonComoConseguirlo = (docReciboBase && docReciboBase.informative_clickable_text) ? `
                <button type="button" class="btn btn-sm btn-outline-secondary btn-como-conseguirlo" 
                    data-header="${escapeHtmlAttribute(docReciboBase.informative_header_text || '')}"
                    data-text="${escapeHtmlAttribute(docReciboBase.informative_clickable_text || '')}"
                    data-link="${escapeHtmlAttribute(docReciboBase.informative_link || '')}"
                    data-link-text="${escapeHtmlAttribute(docReciboBase.informative_link_text || '')}"
                    data-doc-id="${doc.id}"
                    data-doc-name="${escapeHtmlAttribute(doc.name || '')}"
                    data-doc-slug="${escapeHtmlAttribute(slugConMes || '')}"
                    data-multi-upload="false">
                    <i class="fas fa-question-circle me-1"></i>¿Cómo conseguirlo?
                </button>
            ` : '';
            
            accordionContent += `
                <div class="d-flex flex-column gap-2">
                    <button class="btn btn-sm btn-primary" onclick="openModal(${doc.id}, '${doc.name}', '${slugConMes}', false, '', ${ayudaSolicitada.id})" style="display: block !important; visibility: visible !important;">
                        Subir ahora
                    </button>
                    ${botonComoConseguirlo}
                </div>
            `;
        }

        accordionContent += "</div>";
    });

    accordionContent += `
        <style>
        .card-recibo {
            visibility: visible !important;
            opacity: 1 !important;
        }
    </style>
                </div>
            </div>
    `;

    accordionItem.innerHTML = accordionContent;
    section.appendChild(accordionItem);

    // Forzar que se vea la sección de recibos
    section.style.display = "block";
    section.style.visibility = "visible";
    section.style.opacity = "1";
    
    setTimeout(() => {
        setupComoConseguirloButtons(section);
    }, 50);

    return section;
}

function createOtrosDocumentosList(
    documentosFaltantes,
    documentosSubidos,
    ayudaSolicitadaId,
    ayudaSolicitada = null,
) {
    const ul = document.createElement("ul");
    ul.className = "list-unstyled mt-2";

    // Crear un mapa para evitar duplicados
    const vistos = new Set();

    // 1. Documentos faltantes (excepto recibos)
    documentosFaltantes.forEach((doc) => {
        if (doc.name?.toLowerCase().includes("recibo")) return; // Saltar recibos

        vistos.add(doc.slug);

        const subido = documentosSubidos[doc.slug]; // Busca si ya está subido con estado

        let background = "bg-white";
        let estadoHtml = "";
        
        let docInfo = doc;
        if (!doc.informative_clickable_text && ayudaSolicitada && ayudaSolicitada.documentos_configurados) {
            docInfo = ayudaSolicitada.documentos_configurados.find(
                (d) => d.slug === doc.slug || d.id === doc.id
            ) || doc;
        }
        
        const tieneInfoInformativa = docInfo && (docInfo.informative_clickable_text || docInfo.informative_header_text);
        const botonComoConseguirlo = tieneInfoInformativa ? `
            <button type="button" class="btn btn-sm btn-outline-secondary btn-como-conseguirlo" 
                data-header="${escapeHtmlAttribute(docInfo.informative_header_text || '')}"
                data-text="${escapeHtmlAttribute(docInfo.informative_clickable_text || '')}"
                data-link="${escapeHtmlAttribute(docInfo.informative_link || '')}"
                data-link-text="${escapeHtmlAttribute(docInfo.informative_link_text || '')}"
                data-doc-id="${doc.id}"
                data-doc-name="${escapeHtmlAttribute(doc.name || 'Documento sin nombre')}"
                data-doc-slug="${escapeHtmlAttribute(doc.slug || '')}"
                data-multi-upload="${doc.multi_upload || false}">
                <i class="fas fa-question-circle me-1"></i>¿Cómo conseguirlo?
            </button>
        ` : '';
        
        let botonHtml = `
    <div class="d-flex flex-row gap-2 align-items-center justify-content-end">
        ${botonComoConseguirlo}
        <button class="btn btn-sm btn-primary" onclick="openModal(${doc.id}, '${doc.name || "Documento sin nombre"}', '${doc.slug}', ${doc.multi_upload || false}, '${doc.description || ""}', ${ayudaSolicitadaId})">
            Subir ahora
        </button>
    </div>`;

        if (subido?.estado === "pendiente") {
            background = "bg-warning-subtle";
            estadoHtml =
                '<div class="text-muted medium">⏳ En revisión por nuestro equipo</div>';
            botonHtml = ""; // No hace falta subir de nuevo
        }

        if (subido?.estado === "rechazado") {
            background = "bg-danger-subtle";
            estadoHtml =
                '<div class="text-danger medium">❌ Rechazado, vuelve a subirlo</div>';
            botonHtml = `
    <div class="d-flex flex-row gap-2 align-items-center justify-content-end">
        ${botonComoConseguirlo}
        <button class="btn btn-sm btn-warning" onclick="openModal(${doc.id}, '${doc.name || "Documento sin nombre"}', '${doc.slug}', ${doc.multi_upload || false}, '${doc.description || ""}', ${ayudaSolicitadaId})">
            Reintentar subida
        </button>
    </div>`;
        }

        const li = document.createElement("li");
        li.className = `border rounded p-3 mb-2 shadow-sm ${background}`;
        li.innerHTML = `
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2 text-start w-100 w-md-auto">
            <img src="${
                !subido
                    ? "/imagenes/—Pngtree—danger sign flat icon vector_9133214.png" // Faltante
                    : subido.estado === "rechazado"
                      ? "/imagenes/32px-Cross_red_circle.svg.png" // Rechazado
                      : "/imagenes/—Pngtree—magnifying glass retriever png_4525348.png" // Pendiente
            }" alt="${subido?.estado || "faltante"}" style="width: 24px; height: 24px;">

            <span class="fw-semibold text-dark">${doc.name || doc.slug}</span>
        </div>
        <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end justify-content-end gap-2 w-100 w-md-auto">
            ${estadoHtml}
            ${botonHtml}
        </div>
    </div>`;

        ul.appendChild(li);
    });
    
    setTimeout(() => {
        setupComoConseguirloButtons(ul);
    }, 50);

    // 2. Documentos en estado 'pendiente' o 'rechazado' que no están en faltantes (y no son recibos)
    Object.keys(documentosSubidos || {}).forEach((slug) => {
        if (vistos.has(slug)) return; // Ya mostrado
        const doc = documentosSubidos[slug];
        if (!doc || !doc.slug || doc.slug.toLowerCase().includes("recibo"))
            return;

        const estado = doc.estado;
        if (!["pendiente", "rechazado"].includes(estado)) return;

        let backgroundClass = "bg-warning-subtle";
        if (estado === "rechazado") backgroundClass = "bg-danger-subtle";

        let docInfo = null;
        if (ayudaSolicitada && ayudaSolicitada.documentos_configurados) {
            docInfo = ayudaSolicitada.documentos_configurados.find(
                (d) => d.slug === slug || d.id === doc.document_id
            );
        }
        
        const tieneInfoInformativa = docInfo && (docInfo.informative_clickable_text || docInfo.informative_header_text);
        const botonComoConseguirlo = tieneInfoInformativa ? `
            <button type="button" class="btn btn-sm btn-outline-secondary mb-2 btn-como-conseguirlo" 
                data-header="${escapeHtmlAttribute(docInfo.informative_header_text || '')}"
                data-text="${escapeHtmlAttribute(docInfo.informative_clickable_text || '')}"
                data-link="${escapeHtmlAttribute(docInfo.informative_link || '')}"
                data-link-text="${escapeHtmlAttribute(docInfo.informative_link_text || '')}"
                data-doc-id="${doc.document_id}"
                data-doc-name="${escapeHtmlAttribute(doc.nombre_personalizado || doc.slug || '')}"
                data-doc-slug="${escapeHtmlAttribute(slug || '')}"
                data-multi-upload="${doc.multi_upload || false}">
                <i class="fas fa-question-circle me-1"></i>¿Cómo conseguirlo?
            </button>
        ` : '';

        const li = document.createElement("li");
        li.className = `border rounded p-3 mb-2 shadow-sm ${backgroundClass}`;

        li.innerHTML = `
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 text-start w-100 w-md-auto">
                <img src="${estado === "pendiente" ? "/imagenes/—Pngtree—magnifying glass retriever png_4525348.png" : "/imagenes/32px-Cross_red_circle.svg.png"}" alt="${estado}" style="width: 24px; height: 24px;">
                <span class="fw-semibold text-dark">${doc.nombre_personalizado || doc.slug}</span>
            </div>
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-end justify-content-end gap-2 w-100 w-md-auto">
                ${
                    estado === "pendiente"
                        ? `
                                                                <div class="text-muted medium">⏳ En revisión por nuestro equipo</div>
                                                            `
                        : `
                                                                <div class="text-danger medium">❌ Rechazado, vuelve a subirlo</div>
                                                                <div class="d-flex flex-row gap-2 align-items-center justify-content-end">
                                                                    ${botonComoConseguirlo}
                                                                    <button class="btn btn-sm btn-warning btn-secondary"
                                                                        onclick="openModal(
                                                                            ${doc.document_id},
                                                                            '${doc.nombre_personalizado || doc.slug}',
                                                                            '${slug}',
                                                                            ${doc.multi_upload || false},
                                                                            '${doc.description || ""}',
                                                                            ${ayudaSolicitadaId}
                                                                        )">
                                                                        Reintentar subida
                                                                    </button>
                                                                </div>

                                                            `
                }
            </div>
        </div>
        `;

        ul.appendChild(li);
    });
    
    setTimeout(() => {
        setupComoConseguirloButtons(ul);
    }, 50);

    return ul;
}

// ====================
// 🎨 Inicialización de UI
// ====================
document.addEventListener("DOMContentLoaded", () => {
    // Inicializar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]'),
    );
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Animar las barras de progreso
    document
        .querySelectorAll(".progress[data-estado]")
        .forEach((progressEl) => {
            const estado = progressEl.getAttribute("data-estado")?.trim();
            let targetPercentage = 0;
            switch (estado) {
                case "concedida":
                case "rechazada":
                    targetPercentage = 100;
                    break;
                case "tramitada":
                    targetPercentage = 50;
                    break;
                case "tramitación":
                    targetPercentage = 30;
                    break;
                case "documentación":
                    targetPercentage = 15;
                    break;
                default:
                    targetPercentage = 0;
            }
            const bar = progressEl.querySelector(".progress-bar");
            if (bar) {
                anime({
                    targets: bar,
                    width: targetPercentage + "%",
                    easing: "easeInOutQuad",
                    duration: 1200,
                });
            }
        });

    // Animar entrada de cards
    anime({
        targets: ".card",
        opacity: [0, 1],
        translateY: [30, 0],
        delay: anime.stagger(150),
        duration: 700,
        easing: "easeOutExpo",
    });
});

/**
 * 🎯 Script para tooltips personalizados
 * Activa tooltips hechos a mano (clase .tooltip-trigger)
 * con soporte para hover y click.
 */
document.addEventListener("DOMContentLoaded", function () {
    // 1️⃣ Buscar todos los elementos con la clase .tooltip-trigger
    const tooltipTriggers = document.querySelectorAll(".tooltip-trigger");

    tooltipTriggers.forEach((trigger) => {
        // 2️⃣ Dentro de cada tooltip-trigger buscar el icono y el contenido del tooltip
        const icon = trigger.querySelector("i"); // el icono de info o ayuda
        const tooltip = trigger.querySelector(".tooltip-content"); // el texto que se muestra al pasar el ratón

        if (!icon || !tooltip) return; // si no hay icono o tooltip, no hacemos nada

        // --- 🎭 COMPORTAMIENTO DEL TOOLTIP ---

        // 👆 Mostrar tooltip cuando el usuario pasa el ratón por encima del icono
        icon.addEventListener("mouseenter", () =>
            tooltip.classList.remove("hidden"),
        );
        // 👇 Ocultar tooltip cuando el ratón sale del icono
        icon.addEventListener("mouseleave", () =>
            tooltip.classList.add("hidden"),
        );

        // 🖱️ Click en el icono → alternar visibilidad del tooltip
        icon.addEventListener("click", (e) => {
            e.stopPropagation(); // Evita que el click cierre el tooltip inmediatamente
            tooltip.classList.toggle("hidden");
        });

        // 👆 También mostrar/ocultar si el usuario pasa el ratón por todo el bloque trigger
        trigger.addEventListener("mouseenter", () =>
            tooltip.classList.remove("hidden"),
        );
        trigger.addEventListener("mouseleave", () =>
            tooltip.classList.add("hidden"),
        );

        // ❌ Click en cualquier otra parte de la página → cerrar tooltip
        document.addEventListener("click", (e) => {
            if (!trigger.contains(e.target)) {
                tooltip.classList.add("hidden");
            }
        });
    });
});

function initCalculadorasEnModalDirecto(modalElement) {
    if (!window.calculadoras) {
        window.calculadoras = {};
    }
    
    const modalBody = modalElement.querySelector('#convivienteModalBody');
    if (!modalBody) return;
    
    const containers = modalBody.querySelectorAll('.calculadora-container');
    if (containers.length === 0) return;
    
    containers.forEach(container => {
        const questionId = container.getAttribute('data-question-id');
        if (!questionId) return;
        
        if (window.calculadoras[questionId] && container.innerHTML.trim() !== '' && 
            container.innerHTML.trim() !== '<!-- La calculadora se renderizará aquí con JavaScript -->') {
            return;
        }
        
        const hiddenInput = document.getElementById(`calculadora-${questionId}-data`);
        let initialData = null;
        
        if (hiddenInput && hiddenInput.value) {
            try {
                initialData = JSON.parse(hiddenInput.value);
            } catch (e) {
                console.error('Error parsing calculadora data:', e);
            }
        }
        
        initCalculadoraModal(questionId, initialData, container);
    });
}

function initCalculadoraModal(questionId, initialData, container) {
    if (!container) {
        container = document.getElementById(`calculadora-${questionId}`);
        if (!container) return;
    }
    
    const incomes = (initialData?.incomes || []).map(inc => ({ ...inc }));
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'EUR',
            minimumFractionDigits: 2
        }).format(amount || 0);
    }
    
    function calculateTotals() {
        const totalMonths = incomes.reduce((sum, inc) => sum + (inc.months || 0), 0);
        const totalGrossIncome = incomes.reduce((sum, inc) => sum + (inc.annual || 0), 0);
        const estimatedDeductions = totalGrossIncome * 0.15;
        const netIncome = totalGrossIncome - estimatedDeductions;
        return { totalMonths, totalGrossIncome, estimatedDeductions, netIncome };
    }
    
    function render() {
        const { totalMonths, totalGrossIncome, estimatedDeductions, netIncome } = calculateTotals();
        const remainingMonths = Math.max(0, 12 - totalMonths);
        
        container.innerHTML = `
            <div class="bg-white rounded-lg p-4 mb-6 border border-gray-200">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-sm text-gray-700">Meses utilizados (últimos 12)</span>
                        <span class="text-sm fw-medium ${remainingMonths === 0 ? 'text-danger' : 'text-dark'}">${totalMonths} / 12</span>
                    </div>
                    <div class="w-100" style="height: 8px; background-color: #e9ecef; border-radius: 4px; overflow: hidden;">
                        <div class="h-100 transition-all ${totalMonths < 9 ? 'bg-success' : (totalMonths < 12 ? 'bg-warning' : 'bg-danger')}" 
                             style="width: ${Math.min(100, Math.round((totalMonths/12)*100))}%"></div>
                    </div>
                    <div class="mt-1 text-xs ${remainingMonths === 0 ? 'text-danger' : 'text-muted'}">
                        ${remainingMonths === 0 ? 'Has alcanzado el límite de 12 meses' : `Te quedan ${remainingMonths} mes(es) disponibles`}
                    </div>
                </div>
                
                <div class="mb-4 p-3 bg-info bg-opacity-10 border border-info rounded">
                    <p class="text-sm text-info">
                        Introduce tus ingresos brutos de los <strong>últimos 12 meses</strong>. Puedes repartirlos por tipo (Trabajo, Pensión, Prestación, etc.), pero el <strong>total de meses no puede superar 12</strong>.
                    </p>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label small fw-medium text-gray-700">Tipo de ingreso</label>
                        <select id="calc-type-${questionId}" class="form-select form-select-sm">
                            <option value="">Seleccionar...</option>
                            <option value="Trabajo">Trabajo</option>
                            <option value="Pensión">Pensión</option>
                            <option value="Prestación">Prestación</option>
                            <option value="Renta">Renta</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-medium text-gray-700">Meses percibidos</label>
                        <input type="number" id="calc-months-${questionId}" min="1" ${remainingMonths > 0 ? `max="${remainingMonths}"` : ''} 
                               class="form-control form-control-sm"
                               placeholder="12" ${remainingMonths === 0 ? 'disabled' : ''}>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-medium text-gray-700">Importe medio</label>
                        <input type="number" id="calc-amount-${questionId}" step="0.01" min="0" 
                               class="form-control form-control-sm"
                               placeholder="1200">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-medium text-gray-700 d-block">&nbsp;</label>
                        <button onclick="addIncomeModal(${questionId})" 
                                ${remainingMonths === 0 ? 'disabled' : ''}
                                class="btn btn-success btn-sm w-100 ${remainingMonths === 0 ? 'disabled' : ''}">
                            ➕ Añadir ingreso
                        </button>
                    </div>
                </div>
                
                ${incomes.length > 0 ? `
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <div class="px-4 py-3 bg-light border-bottom">
                            <div class="row g-3 text-sm fw-medium text-gray-700">
                                <div class="col-3">Tipo</div>
                                <div class="col-2">Meses</div>
                                <div class="col-3">Importe medio</div>
                                <div class="col-3">Importe anual</div>
                                <div class="col-1"></div>
                            </div>
                        </div>
                        <div class="border-top">
                            ${incomes.map((income, index) => `
                                <div class="px-4 py-3 border-bottom">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-3">
                                            <span class="badge bg-primary">${income.type}</span>
                                        </div>
                                        <div class="col-2">
                                            <span class="badge bg-secondary">${income.months} meses</span>
                                        </div>
                                        <div class="col-3 text-sm">${formatCurrency(income.amount)}</div>
                                        <div class="col-3 text-sm fw-medium">${formatCurrency(income.annual)}</div>
                                        <div class="col-1 text-end">
                                            <button onclick="removeIncomeModal(${questionId}, ${index})" class="btn btn-sm btn-link text-danger p-0">
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                            <div class="px-4 py-3 bg-light d-flex justify-content-end align-items-center gap-4">
                                <div class="text-sm text-gray-700">
                                    Total anual: <span class="fw-semibold">${formatCurrency(totalGrossIncome)}</span>
                                </div>
                                <div class="text-sm text-muted">Deducciones estimadas: ${formatCurrency(estimatedDeductions)}</div>
                                <div class="text-sm fw-semibold">Neto estimado: ${formatCurrency(netIncome)}</div>
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    }
    
    function addIncome(questionId) {
        const type = document.getElementById(`calc-type-${questionId}`).value;
        const months = parseInt(document.getElementById(`calc-months-${questionId}`).value);
        const amount = parseFloat(document.getElementById(`calc-amount-${questionId}`).value);
        
        if (!type || !months || !amount) {
            alert('⚠️ Por favor, completa todos los campos del ingreso.');
            return;
        }
        
        const { totalMonths } = calculateTotals();
        if (totalMonths + months > 12) {
            alert('⚠️ El total de meses no puede superar 12.');
            return;
        }
        
        incomes.push({
            type: type,
            months: months,
            amount: amount,
            annual: amount * months
        });
        
        document.getElementById(`calc-type-${questionId}`).value = '';
        document.getElementById(`calc-months-${questionId}`).value = '';
        document.getElementById(`calc-amount-${questionId}`).value = '';
        
        render();
        updateHiddenInput(questionId);
    }
    
    function removeIncome(questionId, index) {
        incomes.splice(index, 1);
        render();
        updateHiddenInput(questionId);
    }
    
    function updateHiddenInput(questionId) {
        const data = {
            incomes: incomes,
            totalGrossIncome: calculateTotals().totalGrossIncome,
            estimatedDeductions: calculateTotals().estimatedDeductions,
            netIncome: calculateTotals().netIncome
        };
        const hiddenInput = document.getElementById(`calculadora-${questionId}-data`);
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(data);
        }
    }
    
    window.calculadoras[questionId] = {
        incomes: incomes,
        addIncome: (qId) => addIncome(qId),
        removeIncome: (qId, idx) => removeIncome(qId, idx),
        getData: () => {
            const { totalGrossIncome, estimatedDeductions, netIncome } = calculateTotals();
            return {
                incomes: incomes,
                totalGrossIncome: totalGrossIncome,
                estimatedDeductions: estimatedDeductions,
                netIncome: netIncome
            };
        }
    };

    window.addIncomeModal = function(qId) {
        if (window.calculadoras[qId]) {
            window.calculadoras[qId].addIncome(qId);
        }
    };
    
    window.removeIncomeModal = function(qId, index) {
        if (window.calculadoras[qId]) {
            window.calculadoras[qId].removeIncome(qId, index);
        }
    };
    
    render();
    updateHiddenInput(questionId);
}

// Función que abre el formulario de conviviente en un modal
function openConvivienteForm(ayudaId, questionnaireId, index) {
    // Primero verificar si hay preguntas builder
    fetch(`/api/conviviente-builder-form/${questionnaireId}/${index}`)
        .then((response) => response.json())
        .then((data) => {
            // Verificar si hay preguntas builder
            const hasBuilders = data.questions && data.questions.some(q => q.type === 'builder');
            
            if (hasBuilders && typeof window.openConvivienteBuilderModal === 'function') {
                // Usar modal Vue con BuilderQuestion
                // Obtener nombre del conviviente si existe
                const convivienteNombre = data.convivienteNombre || null;
                window.openConvivienteBuilderModal(questionnaireId, index, convivienteNombre);
            } else {
                // Usar modal tradicional Blade
                fetch(`/form-conviviente/${questionnaireId}/${index}`)
                    .then((response) => response.text())
                    .then((html) => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, "text/html");
                        const modal = doc.querySelector("#convivienteModal");

                        if (modal) {
                            const container = document.getElementById(
                                "modalConvivienteContainer",
                            );
                            container.innerHTML = "";
                            container.appendChild(modal);

                            const scripts = modal.querySelectorAll('script');
                            scripts.forEach(oldScript => {
                                const newScript = document.createElement('script');
                                Array.from(oldScript.attributes).forEach(attr => {
                                    newScript.setAttribute(attr.name, attr.value);
                                });
                                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            });

                            const bootstrapModal = new bootstrap.Modal(modal);

                            setTimeout(function() {
                                initCalculadorasEnModalDirecto(modal);
                            }, 200);

                            modal.addEventListener('shown.bs.modal', function() {
                                setTimeout(function() {
                                    initCalculadorasEnModalDirecto(modal);
                                }, 300);
                            });

                            // Interceptar el envío del formulario para evitar recargar la página
                            const convivienteForm = modal.querySelector("#convivienteForm");
                            if (convivienteForm) {
                                convivienteForm.addEventListener("submit", function (e) {
                                    e.preventDefault();

                                    const formData = new FormData(convivienteForm);
                                    const action = convivienteForm.getAttribute("action") || window.location.href;

                                    fetch(action, {
                                        method: "POST",
                                        body: formData,
                                        headers: {
                                            "X-Requested-With": "XMLHttpRequest",
                                        },
                                    })
                                        .then((response) => {
                                            if (response.ok) {
                                                // Cerrar el modal sin recargar la página
                                                bootstrapModal.hide();
                                            } else {
                                                console.error('Error al enviar el formulario de conviviente:', response);
                                            }
                                        })
                                        .catch(() => {
                                            console.error('Error al enviar el formulario de conviviente:', error);
                                        });
                                });
                            }

                            modal.addEventListener("shown.bs.modal", () => {
                                // 1️⃣ Seleccionamos las conditions para ese questionnaireId
                                // Convertir a string para asegurar que coincida con las claves del objeto
                                const questionnaireIdStr = String(questionnaireId);
                                const myConditions =
                                    window.convivienteConditions?.[questionnaireIdStr] ||
                                    window.convivienteConditions?.[questionnaireId]; // Fallback por si acaso

                                if (!myConditions) {
                                    return;
                                }

                                if (typeof window.initModalConditions === "function") {
                                    window.initModalConditions(myConditions);
                                }
                            });

                            bootstrapModal.show();
                        } else {
                            //console.error('❌ No se encontró el modal en el HTML recibido');
                        }
                    })
                    .catch((error) => {
                        // console.error('Error cargando el formulario de conviviente:', error);
                    });
            }
        })
        .catch((error) => {
            console.error('Error verificando builders:', error);
            // Fallback al modal tradicional si hay error
            fetch(`/form-conviviente/${questionnaireId}/${index}`)
                .then((response) => response.text())
                .then((html) => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, "text/html");
                    const modal = doc.querySelector("#convivienteModal");

                    if (modal) {
                        const container = document.getElementById("modalConvivienteContainer");
                        container.innerHTML = "";
                        container.appendChild(modal);
                        const bootstrapModal = new bootstrap.Modal(modal);
                        bootstrapModal.show();
                    }
                });
        });
}

function updateSubsanacionSection(cardElement, ayudaSolicitadaId) {
    console.log("📥 updateSubsanacionSection con id:", ayudaSolicitadaId);
    if (
        typeof ayudaSolicitadaId !== "number" &&
        typeof ayudaSolicitadaId !== "string"
    ) {
        console.error(
            "❌ ID inválido para updateSubsanacionSection:",
            ayudaSolicitadaId,
        );
        return;
    }

    fetch(`/ayudas-solicitadas/${ayudaSolicitadaId}/subsanacion-view`)
        .then((res) => {
            return res.text();
        })
        .then((html) => {
            // ✅ buscar el div correcto
            const subsanacionSection = cardElement.querySelector(
                `#subsanacion-section-${ayudaSolicitadaId}`,
            );

            if (subsanacionSection) {
                subsanacionSection.innerHTML = html;
            }
        })
        .catch((err) => {
            console.error("❌ Error al actualizar subsanación:", err);
        });
}
