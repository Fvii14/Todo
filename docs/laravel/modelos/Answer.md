# Answer

El modelo `Answer` representa las respuestas de los usuarios a las [`Question`](/laravel/modelos/Question)

## Tabla

El modelo utiliza la tabla `answers` en la base de datos.

## Propiedades

### Fillable

- `answer`: Contenido de la respuesta (puede ser texto, número, JSON, etc.)
- `user_id`: ID del [`User`](/laravel/modelos/User) que proporciona la respuesta
- `question_id`: ID de la [`Question`](/laravel/modelos/Question) a la que corresponde la respuesta
- `conviviente_id`: ID del conviviente relacionado (opcional)
- `arrendador_id`: ID del arrendador relacionado (opcional)
- `onboarder_id`: ID del onboarder relacionado (opcional)
- `user_conviviente_id`: ID del usuario conviviente relacionado (opcional)

## Relaciones

### belongsTo

- `user()`: Relación con el modelo `User` a través de `user_id`
- `question()`: Relación con el modelo `Question` a través de `question_id`
- `conviviente()`: Relación con el modelo `Conviviente` a través de `conviviente_id`
- `arrendador()`: Relación con el modelo `Arrendatario` a través de `arrendador_id`
- `onboarder()`: Relación con el modelo `Onboarder` a través de `onboarder_id`
- `userConviviente()`: Relación con el modelo `UserConviviente` a través de `user_conviviente_id`

::: warning Deprecated
La relación `onboarder()` y el campo `onboarder_id` están marcados como deprecated. Se recomienda no usar esta funcionalidad en nuevos desarrollos, ya que será eliminada en futuras versiones.
:::

## Métodos

### getFormattedAnswer()

Formatea la respuesta según el tipo de pregunta y su configuración. Devuelve una representación legible de la respuesta.

**Formateo por slug de pregunta:**

- `comunidad_autonoma`: Busca el nombre de la comunidad autónoma en la tabla `Ccaa`
- `provincia`: Busca el nombre de la provincia en la tabla `Provincia`
- `municipio`: Busca el nombre del municipio en la tabla `Municipio`
- `estado_civil`: Convierte códigos numéricos a texto (1=Soltero/a, 2=Casado/a, 3=Viudo/a, 4=Divorciado/a)
- `sexo`: Convierte códigos a texto ('H'=Hombre, 'M'=Mujer)

**Formateo por tipo de pregunta:**

- `boolean`: Convierte '1' o 'true' a 'Sí', cualquier otro valor a 'No'
- `select`: Busca el valor en las opciones de la pregunta y devuelve su etiqueta
- `multiple`: Separa valores múltiples por comas y formatea cada uno según las opciones

**Ejemplo de uso:**

```php
$answer = Answer::find(1);
$formatted = $answer->getFormattedAnswer();
// Devuelve: "Soltero/a" en lugar de "1" para estado_civil
```

### getColectionAnswersQuestions()

Método estático que devuelve una colección de respuestas de un usuario en formato clave-valor (`answer`, `question_id`).

**Parámetros:**

- `int $userId`: ID del usuario

**Retorna:**

- `Collection`: Colección con `question_id` como clave y `answer` como valor

**Características:**

- Si la respuesta es un JSON válido, lo decodifica automáticamente
- Si el JSON está mal formado, registra un error en el log y devuelve el valor original
- Si la respuesta no es un string, la devuelve tal cual

**Ejemplo de uso:**

```php
$answers = Answer::getColectionAnswersQuestions(123);
// Retorna: Collection(['1' => 'valor1', '2' => 'valor2', ...])
```

## Scopes

### scopeByUser

Filtra las respuestas por usuario.

```php
Answer::byUser(123)->get();
```

### scopeByQuestion

Filtra las respuestas por una pregunta específica.

```php
Answer::byQuestion(5)->get();
```

### scopeByQuestions

Filtra las respuestas por múltiples preguntas.

```php
Answer::byQuestions([1, 2, 3, 4])->get();
```

### scopeWithoutConviviente

Excluye las respuestas relacionadas con convivientes (solo respuestas del usuario principal).

```php
Answer::byUser(123)->withoutConviviente()->get();
```

### scopeByConviviente

Filtra las respuestas por un conviviente específico.

```php
Answer::byConviviente(10)->get();
```

### scopeForQuestionnaire

Filtra las respuestas que pertenecen a un cuestionario específico.

```php
Answer::forQuestionnaire(2)->get();
```

Este scope utiliza una subconsulta para obtener todas las preguntas asociadas al cuestionario a través de la tabla `questionnaire_questions`.
