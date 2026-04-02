# Arrendatario

El modelo `Arrendatario` conserva la relación de los arrendatarios con los [`User`](/laravel/modelos/User) de nuestra plataforma.

## Tabla

El modelo utiliza la tabla `arrendatarios` en la base de datos.

## Propiedades

### Fillable

- `user_id`: ID del [`User`](/laravel/modelos/User) al que pertenece dicho arrendatario
- `index`: Index del arrendatario, usado en caso en el que hayan varios arrendatarios para el mismo [`User`](/laravel/modelos/User)

## Relaciones

### `belongsTo`

- `user()`: Relación con el modelo `User` a través de `user_id`

### `hasMany`

- `answers()`: Relación con el modelo [`Answer`](/laravel/modelos/Answer) para conservar las respuestas que son del `Arrendatario`
