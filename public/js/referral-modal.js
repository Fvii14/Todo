function copyReferralLink() {
    const input = document.getElementById("referralLink");
    if (!input) return;

    // Selecciona el contenido del input
    input.select();
    input.setSelectionRange(0, 99999); // Para compatibilidad en móviles

    // Copia el texto
    navigator.clipboard.writeText(input.value).then(() => {
        const message = document.getElementById("copy-message");
        if (!message) return;

        // Muestra el mensaje
        message.classList.remove("hidden");

        // Lo oculta tras 700ms
        setTimeout(() => {
            message.classList.add("hidden");
        }, 700);
    }).catch(err => {
        console.error("❌ Error al copiar el enlace:", err);
    });
}
