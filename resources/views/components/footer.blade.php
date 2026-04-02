<link href="https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

<footer id="ft" class="flex flex-col lg:flex-row justify-around text-left lg:text-left items-center lg:items-start px-4 py-8 bg-[#3c3a60] space-y-6 lg:space-y-0 z-9">
    <!-- Info de contacto -->
    <div class="order-0 mt-4 text-center lg:text-left">
        <ul class="space-y-3 text-sm text-white lg:text-left">

            <li>Dirección: Avinguda de la Universitat d'Elx, sin número</li>
            <li>03202 Elx, Alicante, España</li>
            <li>Soporte: <a href="tel:+3460236800" class="hover:no-underline" style="color:#54debd" onmouseover="this.style.color='#ff3392'" onmouseout="this.style.color='#54debd'">603 236 800</a></li>
            <li>Horario: Lun–Vie: 08:00–18:30 | Sab: 10:00–13:00</li>
            <li>E-mail: <a href="mailto:info@tutramitefacil.es" class="hover:no-underline" style="color:#54debd" onmouseover="this.style.color='#ff3392'" onmouseout="this.style.color='#54debd'">info@tutramitefacil.es</a></li>
        </ul>
    </div>

    <!-- Logo (entre las dos listas) -->

    <div class="order-1 lg:order-1 mt-2 lg:mt-0 hidden lg:block">
        <img src="{{ asset('imagenes/logo-ttf-2024.svg') }}" alt="Logo" class="logo-footer">
    </div>

    <!-- Redes sociales y enlaces legales -->

<div class="order-2 lg:order-2 mt-3">
    <ul class="space-y-3 text-sm text-white text-center xl:text-left">
        <!-- Redes sociales -->
        <li class="flex justify-center xl:justify-start space-x-4 text-xl text-gray-300 mt-2 mb-6 lg:mb-2 lg:ml-7">

            <a href="https://www.instagram.com/Tutramitefacil.es/#" target="_blank" class="hover:text-pink-500 no-underline"><i class="fab fa-instagram"></i></a>
            <a href="https://www.tiktok.com/@tutramitefacil.es" target="_blank" class="hover:text-black no-underline"><i class="fab fa-tiktok"></i></a>
            <a href="https://www.youtube.com/@TuTramiteFacil" target="_blank" class="hover:text-red-600 no-underline"><i class="fab fa-youtube"></i></a>
        </li>

        <!-- Espacio extra -->
        <li class="my-6"></li>

        <!-- Enlaces legales -->
        <li><a href="https://tutramitefacil.es/aviso-legal/" class="hover:no-underline" onmouseover="this.style.color='#ff3392'" onmouseout="this.style.color='#ffffff'">Aviso Legal</a></li>
        <li><a href="https://tutramitefacil.es/politica-de-cookies/" class="hover:no-underline" onmouseover="this.style.color='#ff3392'" onmouseout="this.style.color='#ffffff'">Política de cookies</a></li>
        <li><a href="https://tutramitefacil.es/terminos-y-condiciones/" class="hover:no-underline" onmouseover="this.style.color='#ff3392'" onmouseout="this.style.color='#ffffff'">Términos y condiciones</a></li>
        <li><a href="https://tutramitefacil.es/politica-de-privacidad/" class="hover:no-underline" onmouseover="this.style.color='#ff3392'" onmouseout="this.style.color='#ffffff'">Política de privacidad</a></li>
    </ul>
</div>
</footer>

<style>
  #ft {
    background-color: #3c3a60;
    color: white;
  }
  li {
    color: white;
  }
  img {
    width: 100%;
    height: auto;
  }

  .logo-footer {
    width: 200px;
    height: auto;
  }
</style>
