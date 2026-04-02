# Ayuda

El modelo `Ayuda` representa las ayudas o subvenciones que ofrece la plataforma.

Es probablemente el modelo más importante de la plataforma.

## Tabla

El modelo utiliza la tabla `ayudas` en la base de datos.

## Propiedades

### Fillable

- `ccaa_id`: ID de la [`CCAA`](/laravel/modelos/CCAA) relacionada
- `nombre_ayuda`: Nombre de la `Ayuda`
- `slug`: Slug único
- `description`: Descripción de la ayuda
- `sector`: Sector al que pertenece la ayuda (véase [Constantes de sector](#constantes-de-sector))
- `create_time`: Fecha de creación del registro (**Será deprecado pronto**)
- `questionnaire_id`: ID del cuestionario principal relacionado (legacy; ver relación `cuestionarioPrincipal()`)
- `presupuesto`: Presupuesto asignado a la ayuda
- `fecha_inicio`: Fecha de inicio de la ayuda
- `fecha_fin`: Fecha de fin de la ayuda
- `fecha_inicio_periodo`: Inicio del periodo de solicitud
- `fecha_fin_periodo`: Fin del periodo de solicitud
- `organo_id`: ID del órgano gestor (modelo [`Organo`](/laravel/modelos/Organo))
- `cuantia_usuario`: Cuantía por usuario (si aplica)
- `activo`: Indica si la ayuda está activa

### Casts

- `create_time`: Se convierte a tipo `datetime`
- `fecha_inicio`: Se convierte a tipo `date`
- `fecha_fin`: Se convierte a tipo `date`
- `presupuesto`: Se convierte a tipo `float`
- `sector`: Se convierte a tipo `string`

## Constantes

### Sector

Todas las ayudas pertenecen a uno de los siguientes sectores. Se recomienda usar las constantes en lugar de strings literales.

| Constante                       | Valor                      |
| ------------------------------- | -------------------------- |
| `SECTOR_FAMILIA`                | `'familia'`                |
| `SECTOR_TRABAJO`                | `'trabajo'`                |
| `SECTOR_REFORMAS_OBRAS`         | `'Reformas y Obras'`       |
| `SECTOR_DESARROLLO_TECNOLOGICO` | `'Desarrollo Tecnológico'` |
| `SECTOR_VIAJE`                  | `'viaje'`                  |
| `SECTOR_VIVIENDA`               | `'vivienda'`               |
| `SECTOR_IMV`                    | `'imv'`                    |
| `SECTOR_EDUCACION`              | `'educacion'`              |
| `SECTOR_SALUD`                  | `'salud'`                  |
| `SECTOR_EMPLEO`                 | `'empleo'`                 |
| `SECTOR_JOVENES`                | `'jovenes'`                |
| `SECTOR_POBREZA`                | `'pobreza'`                |
| `SECTOR_MUJER`                  | `'mujer'`                  |
| `SECTOR_MEDIO_AMBIENTE`         | `'Medio Ambiente'`         |

## Relaciones

### belongsTo

- `questionnaire()`: Relación con el modelo [`Questionnaire`](/laravel/modelos/Questionnaire) a través de `questionnaire_id` (cuestionario asociado de forma legacy)
- `organo()`: Relación con el modelo `Organo` a través de `organo_id` (órgano gestor de la ayuda)

### hasMany

- `ayudaDocumentos()`: Relación con el modelo `AyudaDocumento` (documentos asociados a la ayuda)
- `questionnaires()`: Relación con el modelo [`Questionnaire`](/laravel/modelos/Questionnaire) a través de `ayuda_id`; una ayuda puede tener varios cuestionarios (pre, conviviente, etc.)
- `datos()`: Relación con el modelo `AyudaDato`
- `documents()`: Relación con el modelo `AyudaDocumento` (alias de la relación de documentos)
- `requisitos()`: Relación con el modelo `AyudaRequisito`
- `products()`: Relación con el modelo `Products` a través de `ayudas_id`
- `ayudaProductos()`: Relación con el modelo `AyudaProducto` (tabla pivote enriquecida)
- `enlaces()`: Relación con el modelo `AyudaEnlace`
- `preRequisitos()`: Relación con el modelo `AyudaPreRequisito`, ordenados
- `preRequisitosActivos()`: Subconjunto de pre-requisitos con `active = true`, ordenados
- `preRequisitosRequeridos()`: Subconjunto de pre-requisitos activos y con `is_required = true`, ordenados
- `motivosSubsanacionAyuda()`: Relación con el modelo `MotivoSubsanacionAyuda`
- `ayudaDocumentosConvivientes()`: Relación con el modelo `AyudaDocumentoConviviente`
- `ayudaRequisitosJson()`: Relación con el modelo `AyudaRequisitoJson`

### hasOne

- `cuestionarioPrincipal()`: Relación con el modelo [`Questionnaire`](/laravel/modelos/Questionnaire) donde `ayuda_id` coincide y `tipo = 'pre'` (cuestionario principal del solicitante)

### belongsToMany

- `documentos()`: Relación many-to-many con el modelo `Document` a través de la tabla pivote `ayuda_documentos` (documentos que debe aportar el solicitante)
- `productos()`: Relación many-to-many con el modelo `Products` a través de `ayuda_producto`, con pivot `recomendado` y timestamps
- `recursos()`: Relación many-to-many con el modelo `Recurso` a través de `ayuda_recurso`, con pivot `orden` y `activo`
- `documentosConvivientes()`: Relación many-to-many con el modelo `Document` a través de `ayuda_documentos_convivientes`, con pivot `es_obligatorio`

## Métodos

### getSectores()

Método estático que devuelve el listado de sectores válidos para una ayuda.

**Retorna:** `array` — Lista de valores de sector (incluye `SECTOR_IMV` dos veces en la implementación actual).

**Ejemplo de uso:**

```php
$sectores = Ayuda::getSectores();
// ['familia', 'trabajo', 'Reformas y Obras', ...]
```

### getDineroFormateado($cantidad, $decimals = 2)

Formatea una cantidad numérica como moneda en formato español (separador de miles `.`, decimal `,`, sufijo `€`).

**Parámetros:**

- `float $cantidad`: Cantidad a formatear
- `int $decimals`: Número de decimales (por defecto `2`)

**Retorna:** `string` — Por ejemplo `"1.234,56€"`

**Ejemplo de uso:**

```php
$ayuda = Ayuda::find(1);
echo $ayuda->getDineroFormateado($ayuda->presupuesto);
// "50.000,00€"
```

### getConvivienteQuestionnaireId(int $ayudaId)

Método estático que devuelve el ID del cuestionario de tipo `conviviente` para una ayuda.

**Parámetros:**

- `int $ayudaId`: ID de la ayuda

**Retorna:** `?int` — ID del cuestionario conviviente o `null` si no existe

**Ejemplo de uso:**

```php
$questionnaireId = Ayuda::getConvivienteQuestionnaireId(5);
if ($questionnaireId) {
    // Redirigir o cargar formulario de convivientes
}
```
