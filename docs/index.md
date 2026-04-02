---
# https://vitepress.dev/reference/default-theme-home-page
layout: home

hero:
    name: 'Documentación Tech TTF'
    text: 'Documentación técnica para programadores de Tu Trámite Fácil'
---

<div class="tech-cards-container">
  <TechCard
    icon="🚀"
    title="Laravel"
    details="Laravel es nuestro backend, usamos la versión 12.42.0 y aquí dentro están la gran mayoría de archivos.\n\nIntentamos seguir el patrón MVC dejando exclusivamente al controlador el rol de orquestar según REST y la lógica de negocio está dentro de Servicios. También hacemos uso de Helpers.\n\nIntentamos que todos nuestros comentarios sigan el estándar PHPDoc y también de seguir las reglas del estándar PSR-4"
    link="/laravel"
    link-text="Ver documentación Laravel"
  />
  
  <TechCard
    icon="⚡"
    title="Vue"
    details="Vue es nuestro frontend principal, usamos la versión ^3.5.22.\n\nIntentamos que template, script y estilos estén correctamente separados en composables reutilizables, en la medida de lo posible.\n\nQuedan algunas views hechas en Blade de primeras versiones pero estas son minoritarias, la gran mayoría en desuso y pendientes de cambiar a Vue."
    link="/vue"
    link-text="Ver documentación Vue"
  />
  
  <TechCard
    icon="🐍"
    title="Python"
    details="Python se usa en la versión 3.13.1 y es usado en dos principales puntos de la compañía:\n\nPor un lado, se usa para un API intermedio que efectúa un OCR a la subida de archivos de usuarios\n\nPor otro lado, existen scripts que Operativa usa a menudo y que permiten, de manera sencilla, rellenar PDFs con información que poseemos en la BBDD."
    link="/python"
    link-text="Ver documentación Python"
  />
  
  <TechCard
    icon="🏗️"
    title="Infraestructura"
    details="Nuestra infraestructura tecnológica se basa exclusivamente en GCP (Google Cloud Platform) pero usamos numerosos servicios externos como:\n\nKapso para gestión de Whatsapp, Brevo para mails, Clarity para gestión de eventos y Sentry para monitoreo de errores\n\nActualmente estamos migrando el CRM que hicimos in-house a Hubspot"
    link="/infraestructura"
    link-text="Ver documentación Infraestructura"
  />
</div>

<style scoped>
.tech-cards-container {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
  margin: 4rem 0;
  padding: 0 1rem;
}

@media (max-width: 768px) {
  .tech-cards-container {
    grid-template-columns: 1fr;
  }
}
</style>
