<div id="uploadModal" class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
        <h3 class="text-lg font-bold mb-2">
            Subir documento: <span id="modal-doc-name" class="font-normal"></span>
        </h3>
        <p id="modal-doc-description" class="text-muted text-sm mt-1"></p>

        <form id="uploadForm" method="POST" enctype="multipart/form-data" action="/subir-documento">

            @csrf
            <input type="hidden" name="document_id" id="modal-document-id">
            <input type="hidden" name="nombre_personalizado" id="input-nombre-personalizado" value="">
            <input type="hidden" name="slug" id="modalSlug">
            <input type="hidden" name="conviviente_index" id="modal-conviviente-index" value="">

            {{-- SINGLE UPLOAD --}}
            <div id="single-upload-container" class="mb-3 d-none">
                <input type="file" name="file" accept="image/*,application/pdf"
                    class="w-full border border-gray-300 p-2 rounded" onchange="handleSinglePhoto(this)">
                <div id="single-upload-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>

            {{-- MULTI UPLOAD --}}
            <div id="multi-upload-container" class="mb-3 d-none">
                <input type="file" id="multi-upload-input" name="files[]" accept="image/*,application/pdf"
                    multiple onchange="handleNewPhoto(); clearUploadError();">
                <div id="multi-upload-counter" class="fw-semibold mb-2 text-center text-muted"></div>
                <div id="multi-upload-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
            </div>

            {{-- CANVAS FIRMA --}}
            <div id="firma-canvas-container" class="d-none mt-3">
                <p class="text-muted">Firma con el ratón o el dedo si estás en móvil.</p>
                <canvas id="firma-canvas" width="400" height="200"
                    style="border:1px solid #54debd; border-radius: 5px; background-color: #f8f9fa;touch-action: none; max-width: 100%;"></canvas>
                <div id="firma-error" class="text-danger mt-2 d-none">
                    ⚠️ Por favor, realiza tu firma antes de continuar.
                </div>
                <input type="hidden" name="firma_base64" id="firma_base64">
                <div class="mt-2">
                    <button type="button" style="background-color: #dbb4ff91 !important;color: black"
                        class="btn btn-sm btn-secondary" onclick="clearFirmaCanvas()">Borrar firma</button>
                </div>
            </div>

            {{-- CONTENIDO FORMULARIO --}}
            <div id="upload-form-content">
                <div class="flex justify-end gap-2 mt-3">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="btn text-white" style="background-color: #54debd;">
                        Subir
                    </button>
                </div>
            </div>

            {{-- MENSAJE DE ERROR --}}
            <div id="upload-error" class="text-danger mt-3 d-none fw-semibold text-center"></div>

            {{-- BARRA DE PROGRESO --}}
            <div id="upload-progress" class="progress d-none mt-3">
                <div class="progress-bar" role="progressbar" style="width: 0%" id="upload-progress-bar">0%</div>
            </div>

            {{-- SPINNER --}}
            <div id="upload-spinner"
                class="d-none d-flex flex-column align-items-center justify-content-center mt-4"
                style="min-height: 150px;">
                <img src="https://tutramitefacil.es/wp-content/uploads/2025/03/cargando.gif" alt="Subiendo..."
                    style="width: 64px; height: 64px;">
                <p class="mt-3 mb-0 text-success fw-semibold fs-5">Subiendo documento...</p>
            </div>
        </form>
    </div>
</div>
