# Alerta

El modelo `Alerta` representa las alertas del sistema.

## Tabla

El modelo utiliza la tabla `alertas` en la base de datos.

## Propiedades

### Fillable

- `ayuda_id`: ID de la ayuda relacionada
- `contratacion_id`: ID de la [`Contratacion`](/laravel/modelos/Contratacion) relacionada
- `tipo_plazo`: Tipo de plazo de la alerta
- `fecha_inicio`: Fecha de inicio de la alerta
- `fecha_fin`: Fecha de fin de la alerta
- `tipo_alerta`: Tipo de alerta
- `descripcion`: Descripción de la alerta

### Casts

- `fecha_inicio`: Se convierte a tipo `date`
- `fecha_fin`: Se convierte a tipo `date`

## Constantes

### Tipo de Plazo

- `TIPO_PLAZO_MENSUAL = 'mensual'`: Plazo mensual
- `TIPO_PLAZO_PERSONALIZADO = 'personalizado'`: Plazo personalizado

### Tipo de Alerta

- `TIPO_ALERTA_JUSTIFICACION = 'justificacion'`: Alerta de justificación
- `TIPO_ALERTA_SUBSANACION = 'subsanacion'`: Alerta de subsanación
- `TIPO_ALERTA_APERTURA = 'apertura'`: Alerta de apertura

## Relaciones

### belongsTo

- `ayuda()`: Relación con el modelo [`Ayuda`](/laravel/modelos/Ayuda) a través de `ayuda_id`
- `contratacion()`: Relación con el modelo [`Contratacion`](/laravel/modelos/Contratacion) a través de `contratacion_id`

## Scopes

### scopeDeTipoPlazo

Filtra las alertas por tipo de plazo.

```php
Alerta::deTipoPlazo('mensual')->get();
```

### scopeDeTipoAlerta

Filtra las alertas por tipo de alerta.

```php
Alerta::deTipoAlerta('justificacion')->get();
```
