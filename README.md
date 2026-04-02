# CollectorBackend 🚀

Repositorio para el backend del Collector, utilizando Laravel para almacenar toda la información que obtenemos del scrapeo del [CollectorBackend](https://github.com/TuTramiteFacil/CollectorBackend).

## 📥 Instalación

1. Clona el repositorio:
    ```bash
    git clone https://github.com/TuTramiteFacil/CollectorBackend
    cd CollectorBackend
    ```
2. Instala las dependencias:
    ```bash
    composer install
    ```
3. Ejecuta las migraciones y los seeders:
    ```bash
    php artisan migrate
    php artisan db:seed
    ```

## ▶️ Ejecución

Para iniciar el servidor en local, usa el siguiente comando:

```
php artisan serve
```

Por defecto, el servidor se ejecutará en el puerto 8000. Puedes acceder desde tu navegador en:

```
http://localhost:8000/
```

## 🛠️ Uso de la API

La información básica del usuario se almacena en la tabla `users` mientras que la información que scrapeamos se almacena en la tabla `user_tax_info`.

## Documentación

Existe documentación dentro del repositorio. En el directorio `/docs` hay un proyecto vitepress en el que poco a poco el equipo de tech vamos actualizando con parte del código.

Para levantarlo en local, desde la raíz escribe `npm run docs:dev`
