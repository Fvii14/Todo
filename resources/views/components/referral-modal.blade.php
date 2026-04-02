<!-- Modal compartir referido -->
<div id="referralModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-6 relative animate-zoom">
        <!-- Botón cerrar -->
        <button onclick="closeReferralModal()"
            class="absolute top-2 right-3 text-gray-500 hover:text-red-500 text-xl">&times;</button>

        <!-- Título -->
        <h2 class="text-xl font-semibold text-center mb-4">Pide a tus convivientes que te ayuden</h2>

        <p class="text-gray-700 mb-2 text-center">Comparte este enlace para rellenar el formulario:</p>

        <!-- Campo con enlace -->
        <div class="flex items-center border rounded-lg px-4 py-2 bg-gray-50 mb-4">
            <input id="referralLink" type="text" class="w-full bg-transparent focus:outline-none" readonly
                value="">

        </div>
        <div class="flex justify-center mt-4">
            <button onclick="copyReferralLink()"
                class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 transition">
                Copiar enlace
            </button>
        </div>

        <!-- Botones redes -->
        <div class="grid grid-cols-2 gap-3 text-center text-white text-sm font-medium">
            <a id="shareWhatsapp"
                class="share-btn flex items-center justify-center bg-green-500 py-2 rounded-lg hover:bg-green-600">
                <i class="fab fa-whatsapp mr-2"></i> WhatsApp
            </a>
            <a id="shareTelegram"
                class="share-btn flex items-center justify-center bg-blue-400 py-2 rounded-lg hover:bg-blue-500">
                <i class="fab fa-telegram-plane mr-2"></i> Telegram
            </a>
            <a id="shareX"
                class="share-btn flex items-center justify-center bg-black py-2 rounded-lg hover:bg-gray-800">
                <i class="fab fa-x-twitter mr-2"></i> X (Twitter)
            </a>
            <a id="shareEmail"
                class="share-btn flex items-center justify-center bg-indigo-500 py-2 rounded-lg hover:bg-indigo-600">
                <i class="fas fa-envelope mr-2"></i> Email
            </a>
        </div>
    </div>
    <div id="copy-message"
        class="hidden fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gray-900 text-white text-sm px-6 py-3 rounded-xl shadow-lg z-[999999] transition-opacity duration-300">
        ✅ ¡Enlace copiado!
    </div>

</div>
