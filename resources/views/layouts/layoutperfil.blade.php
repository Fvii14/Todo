<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tu Trámite Fácil</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" href="{{ asset('imagenes/cropped-ttflogo_back-192x192-4.png') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
  * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Ubuntu', sans-serif;
      list-style: none;
    }

    body {
      background-color: #fff;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.75rem 1rem;
      background-color: white;
      border-bottom: 1px solid #ccc;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .logo-header {
      width: 50px;
      height: 50px;
    }

    #hamburguesa {
      display: none;
    }

    .menu {
      display: flex;
      flex-direction: column;
      position: absolute;
      top: 4.5rem;
      left: 0;
      width: 100%;
      background-color: white;
      transform: translateX(-150%);
      transition: transform 0.4s ease;
    }

    nav:has(#hamburguesa:checked) .menu {
      transform: translateX(0);
    }

    .menu-item {
      border-bottom: 1px solid #eee;
      text-align: center;
      padding: 1rem;
    }

    .menu-item a {
      text-decoration: none;
      color: #3c3a60;
      font-size: 1.1rem;
      font-weight: bold;
    }

    #icono {
      font-size: 1.5rem;
      margin: 20px;
      margin-right: 0;
      margin-left: 25px;
      margin-top: 24px;
      cursor: pointer;
      color: #3c3a60;
    }

    .icono-usuario {
      padding-left: 1rem;
      font-size: 1.4rem;
      color: #3c3a60;
    }

    .main {
      min-height: 100vh;
      padding: 2rem 1rem;
      text-align: center;
    }

    .footer {
      background-color: #3c3a60;
      color: white;
      padding: 2rem 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
      text-align: center;
    }

    .footer .info li,
    .footer a {
      font-size: 0.9rem;
      color: inherit;
      text-decoration: none;
      margin: 12px;
    }

    .footer .info2 li,
    .footer a {
      font-size: 0.9rem;
      color: inherit;
      text-decoration: none;
      margin: 15px;
    }

    .footer .contactos {
      color: #54debd;
    }

    .footer .contactos:hover {
      color: #ff3392;
    }

    .social-icon {
      color: #54debd;
      font-size: 1rem;
      padding: 0.5rem;
    }

    .logo-footer {
      width: 200px;
      height: auto;
    }

    .logo-footer-container {
        display: none;
    }

    .solo-movil{
        display: block;
    }

    @media(min-width: 768px) {
      .header {
        padding: 1rem 2rem;
      }

        .nav-container {
        flex: 1;
        display: flex;
        justify-content: flex-end;
        }

      .menu {
        position: static;
        transform: none;
        flex-direction: row;
        justify-content: flex-end;
        background-color: white;
        width: auto;
      }

      .menu li > a:hover{
        color: #54debd;
    }

      .menu-item {
        border-bottom: none;
        padding: 0;
        margin: 0 1rem;
      }

      #icono {
        display: none;
      }

      .footer {
        flex-direction: row;
        justify-content: space-around;
        text-align: left;
      }

      .info {
        order: 0;
      }

      .logo-footer-container {
        display: flex;
        order: 1;
      }

      .info2 {
        order: 2;
      }

      .solo-movil{
        display:none;
      }
    }

    @media(max-width: 768px) {
      .footer {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }

      .info {
        order: 0;
        margin: 0;
      }

      .info2 {
        order: 1;
      }

      .logo-footer-container {
        order: 2;
        margin-top: 1rem;
        display: none;
      }
    }
  </style>

<header class="header">
    <img src="{{ asset('imagenes/cropped-ttflogo_back-192x192-4.png') }}" alt="Logo" class="logo-header" />

    <nav class="nav-container">
  <input type="checkbox" id="hamburguesa" />
  <ul class="menu">
    <li class="menu-item"><a href="{{ route('user.home') }}">Mis Ayudas</a></li>
    <li class="menu-item"><a href="{{ route('user.consultas') }}">Consultas</a></li>
    <li class="menu-item"><a href="{{ route('user.perfil-area') }}">Mi Cuenta</a></li>
    <li class="menu-item"><a href="{{ route('user.profile-update') }}" class="solo-movil">Editar Perfil</a>
    <li class="menu-item" ><a href="{{ route('user.historial-pagos') }}" class="solo-movil">Historial de Pagos</a></li>
    <li class="menu-item"><a href="#" class="solo-movil">Método de Pago</a></li>
  </ul>
</nav>

    <div style="display: flex; align-items: center;">
      <label for="hamburguesa" id="icono"><i class="fa fa-bars"></i></label>
    </div>
  </header>
<main class="main">
    @yield('content')
  </main>

  <div class="footer">
    <div class="info">
      <ul>
        <li>Dirección: Avinguda de la Universitat d&apos;Elx, sin número</li>
        <li>03202 Elx, Alicante, España</li>
        <li>Soporte: <a class="contactos" href="tel:+3460236800">603 236 800</a></li>
        <li>Horario: Lun–Vie: 08:00 18:30 Sab: 10:00 a 13:00</li>
        <li>E-mail: <a class="contactos" href="mailto:info@tutramitefacil.es">info@tutramitefacil.es</a></li>
      </ul>
    </div>

    <div class="logo-footer-container">
      <img src="{{ asset('imagenes/logo-ttf-2024.svg') }}" alt="Logo" class="logo-footer" />
    </div>

    <div class="info2">
      <ul>
        <li>
          <a href="https://www.instagram.com/Tutramitefacil.es/#" target="_blank"><i class="fab fa-instagram social-icon"></i></a>
          <a href="https://www.tiktok.com/@tutramitefacil.es" target="_blank"><i class="fab fa-tiktok social-icon"></i></a>
          <a href="https://www.youtube.com/@TuTramiteFacil" target="_blank"><i class="fab fa-youtube social-icon"></i></a>
        </li>
        <li><a href="https://tutramitefacil.es/aviso-legal/">Aviso Legal</a></li>
        <li><a href="https://tutramitefacil.es/politica-de-cookies/">Política de cookies</a></li>
        <li><a href="https://tutramitefacil.es/terminos-y-condiciones/">Términos y condiciones</a></li>
        <li><a href="https://tutramitefacil.es/politica-de-privacidad/">Política de privacidad</a></li>
      </ul>
    </div>
  </div>
  @yield('scripts')
</body>
</html>
