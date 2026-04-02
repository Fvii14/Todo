/* =======================================================
   📁 JS PARA MANEJO DE FIRMA EN CANVAS
   - Dibujo de la firma
   - Limpieza del canvas
   - Validaciones
   ======================================================= */

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById("firma-canvas");
    if (!canvas) return; // Evitamos errores si no existe el canvas en la página

    const ctx = canvas.getContext("2d");
    canvas.width = 400;
    canvas.height = 200;

    let drawing = false;

    function getPosition(e) {
        const rect = canvas.getBoundingClientRect();
        const x = (e.clientX || e.touches?.[0]?.clientX) - rect.left;
        const y = (e.clientY || e.touches?.[0]?.clientY) - rect.top;
        return { x, y };
    }

    function startDrawing(e) {
        drawing = true;
        ctx.beginPath();
        const pos = getPosition(e);
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!drawing) return;
        const pos = getPosition(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function stopDrawing() {
        drawing = false;
    }

    // ✅ Función global para limpiar el canvas de la firma
    window.clearFirmaCanvas = function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const inputHidden = document.getElementById('firma_base64');
        if (inputHidden) inputHidden.value = '';
    };

    // ✅ Eventos de ratón
    canvas.addEventListener("mousedown", startDrawing);
    canvas.addEventListener("mousemove", draw);
    canvas.addEventListener("mouseup", stopDrawing);
    canvas.addEventListener("mouseleave", stopDrawing);

    // ✅ Eventos táctiles
    canvas.addEventListener("touchstart", startDrawing);
    canvas.addEventListener("touchmove", draw);
    canvas.addEventListener("touchend", stopDrawing);

    // ✅ Eliminar borde de error si el usuario empieza a dibujar
    canvas.addEventListener("mousedown", () => {
        canvas.classList.remove('border-error');
        document.getElementById('firma-error')?.classList.add('d-none');
    });

    canvas.addEventListener("touchstart", () => {
        canvas.classList.remove('border-error');
        document.getElementById('firma-error')?.classList.add('d-none');
    });
});
