# Sistema de Condiciones de Formularios

## Índice

1. [Concepto General](#concepto-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Traductor de Saltos a Visibilidad](#traductor-de-saltos-a-visibilidad)
4. [Tipos de Saltos](#tipos-de-saltos)
5. [Implementación en el Wizard](#implementación-en-el-wizard)
6. [Implementación en Formularios Reales](#implementación-en-formularios-reales)
7. [Archivos Involucrados](#archivos-involucrados)
8. [Ejemplos Prácticos](#ejemplos-prácticos)

---

## Concepto General

El sistema de condiciones permite **mostrar u ocultar preguntas** en los formularios de solicitud y convivientes basándose en las respuestas del usuario.

### Modelo Mental: Saltos vs Visibilidad

En el **editor visual (Vue Flow)**, se configuran **"saltos condicionales"** que indican:

- "Si la pregunta A cumple una condición, saltar a la pregunta C"

Estos saltos se **traducen automáticamente** a lógica de visibilidad para los formularios reales:

- "La pregunta B (entre A y C) es visible solo si el salto NO se cumple"
- "La pregunta C (destino del salto) es visible si el salto se cumple"

---

## Arquitectura del Sistema

### Flujo de Datos

```
Vue Flow (Editor Visual)
    ↓
question_conditions (BD)
    ↓
Traductor de Saltos → Lógica de Visibilidad
    ↓
Formularios Reales (Solicitante/Conviviente)
```

### Componentes Principales

1. **Editor Visual (Vue Flow)**: Configuración de saltos condicionales
2. **Traductor de Saltos**: Convierte saltos en lógica de visibilidad
3. **Evaluador de Condiciones**: Evalúa si una condición se cumple
4. **Sistema de Visibilidad**: Muestra/oculta preguntas dinámicamente

---

## Traductor de Saltos a Visibilidad

### Principio Fundamental

**Los saltos configurados en Vue Flow NO son saltos literales en el formulario**, sino que se traducen a reglas de visibilidad:

- **Salto A → C** (saltándose B):
    - B es visible solo si el salto NO se cumple
    - C es visible si el salto se cumple (o siempre si no hay saltos que la condicionen)

### Lógica de Visibilidad

Una pregunta es **visible** si:

1. **No tiene saltos que la afecten** → Siempre visible
2. **Tiene saltos que la "saltan"** → Visible solo si **NINGUNO** de esos saltos se cumple
3. **Tiene saltos directos hacia ella** → **NO condiciona su visibilidad** (solo sirve para saltar preguntas intermedias)
4. **Tiene saltos hacia "FIN del formulario"** → Visible solo si **NINGUNO** de esos saltos se cumple

### Regla Especial: Saltos Directos

**IMPORTANTE**: Si un salto va directamente a una pregunta (A → última pregunta), esa pregunta **NO queda condicionada** por el salto. El salto solo sirve para ocultar las preguntas intermedias.

Solo los saltos hacia **"FIN del formulario"** condicionan la visibilidad de todas las preguntas posteriores.

---

## Tipos de Saltos

### 1. Salto Directo a una Pregunta

**Configuración**: A → B (donde B es una pregunta específica)

**Comportamiento**:

- Las preguntas entre A y B se ocultan cuando el salto se cumple
- La pregunta B **NO queda condicionada** (siempre visible)
- El salto solo afecta a las preguntas intermedias

**Ejemplo**:

```
Preguntas: [1, 2, 3, 4]
Salto: "Si pregunta 1 == 'Sí', saltar a pregunta 4"

Resultado:
- Si 1 == 'Sí': Preguntas 2 y 3 se ocultan, pregunta 4 visible
- Si 1 == 'No': Todas las preguntas visibles
```

### 2. Salto hacia "FIN del Formulario"

**Configuración**: A → FIN (null o 'FIN')

**Comportamiento**:

- Todas las preguntas posteriores a A se ocultan cuando el salto se cumple
- La última pregunta **SÍ queda condicionada** (se oculta si el salto se cumple)

**Ejemplo**:

```
Preguntas: [1, 2, 3, 4]
Salto: "Si pregunta 1 == 'Sí', saltar a FIN"

Resultado:
- Si 1 == 'Sí': Preguntas 2, 3 y 4 se ocultan
- Si 1 == 'No': Todas las preguntas visibles
```

### 3. Múltiples Saltos

**Configuración**: Varios saltos que afectan a la misma pregunta

**Comportamiento**:

- **Saltos que la saltan**: La pregunta es visible solo si **NINGUNO** se cumple (lógica AND NOT)
- **Saltos directos hacia ella**: No condicionan su visibilidad

---

## Implementación en el Wizard

### Componentes Involucrados

1. **WizardStepConditions.vue**: Editor visual de saltos (Vue Flow)
2. **WizardTestConditionsModal.vue**: Modal de prueba de condiciones
3. **WizardAyuda.vue**: Lógica de traducción de saltos

### Flujo en el Wizard

1. **Configuración**: El usuario crea saltos en Vue Flow
2. **Almacenamiento**: Los saltos se guardan en `formData.questionConditions_solicitante` o `formData.questionConditions_conviviente`
3. **Prueba**: El modal "Probar Condiciones" aplica el traductor de saltos
4. **Visualización**: Las preguntas se muestran/ocultan según las respuestas del usuario

### Archivo: `WizardTestConditionsModal.vue`

**Funciones Clave**:

```javascript
// Obtiene el índice de una pregunta en el array
getQuestionIndex(questionId)

// Obtiene saltos que van directamente a una pregunta
getJumpsToQuestion(questionId)

// Obtiene saltos que "saltan sobre" una pregunta
getJumpsSkippingQuestion(questionIndex)

// Traduce saltos a condiciones de visibilidad
getConditionsForQuestion(questionId)

// Evalúa una condición simple
evaluateCondition(condition)

// Evalúa una condición completa (con rules anidados)
evaluateFullCondition(condition)

// Determina si una pregunta es visible
isQuestionVisible(question)
```

**Lógica de Visibilidad**:

```javascript
const isQuestionVisible = (question) => {
    const conditions = getConditionsForQuestion(question.id)

    // Sin condiciones → siempre visible
    if (conditions.length === 0) return true

    const directJumps = conditions.filter((c) => c.isDirectJump)
    const skippingJumps = conditions.filter((c) => !c.isDirectJump)

    // Saltos directos: NO condicionan la visibilidad
    // (solo se usan para saltar preguntas intermedias)

    // Saltos que la saltan: visible solo si NINGUNO se cumple
    if (skippingJumps.length > 0) {
        return skippingJumps.every((jump) => !evaluateFullCondition(jump))
    }

    return true
}
```

---

## Implementación en Formularios Reales

### Formulario de Solicitante

#### Backend: `SolicitudFormularioService.php`

**Función Principal**: `calcularVisibilidadSolicitud()`

```php
protected function calcularVisibilidadSolicitud(
    array $questions,
    array $conditions,
    array $answers
): array
{
    // 1. Construir índice de orden de preguntas
    $order = [];
    foreach ($questions as $index => $q) {
        $order[$q['id']] = $index;
    }

    // 2. Para cada pregunta, calcular visibilidad
    foreach ($questions as $q) {
        $questionId = $q['id'];
        $questionIndex = $order[$questionId];

        // Saltos directos hacia esta pregunta
        $jumpsTo = array_filter($conditions, function ($cond) use ($questionId) {
            return ($cond['next_question_id'] ?? null) === $questionId;
        });

        // Saltos que "saltan sobre" esta pregunta
        $jumpsSkipping = array_filter($conditions, function ($jump) use ($questionIndex, $order, $totalQuestions) {
            return $this->saltoSaltaPreguntaSolicitud($questionIndex, $jump, $order, $totalQuestions);
        });

        // Lógica de visibilidad:
        // - Saltos directos: NO condicionan (solo saltan intermedias)
        // - Saltos que la saltan: visible solo si NINGUNO se cumple
        if (!empty($jumpsSkipping)) {
            $allSkippingInactive = true;
            foreach ($jumpsSkipping as $jump) {
                if ($this->evaluarCondicionSimpleSolicitud($jump, $answers)) {
                    $allSkippingInactive = false;
                    break;
                }
            }
            $visibilityByQuestionId[$questionId] = $allSkippingInactive;
        } else {
            $visibilityByQuestionId[$questionId] = true;
        }
    }

    return $visibilityByQuestionId;
}
```

**Uso en el Controlador**:

```php
// En obtenerDatosSolicitud()
$visibilityByQuestionId = $this->calcularVisibilidadSolicitud(
    $mappedQuestions->all(),
    $conditions,
    $answers->toArray()
);

// Filtrar preguntas visibles
$visibleQuestions = $mappedQuestions->filter(function (array $q) use ($visibilityByQuestionId) {
    return $visibilityByQuestionId[$q['id']] ?? true;
})->values();
```

#### Frontend: `formulario-solicitud-conditions.js`

**Función Principal**: `recalculateSolicitudVisibility()`

```javascript
window.recalculateSolicitudVisibility = function () {
    const conditions = window.solicitudConditions || []
    const answers = window.solicitudAnswers || {}
    const ids = window.solicitudOrder || []

    // Construir índice de orden
    const order = {}
    ids.forEach((id, idx) => {
        order[id] = idx
    })

    const visibility = {}

    ids.forEach((questionId) => {
        const questionIndex = order[questionId] ?? -1

        // Saltos directos hacia esta pregunta
        const jumpsTo = conditions.filter((c) => (c.next_question_id ?? null) == questionId)

        // Saltos que la saltan
        const jumpsSkipping = conditions.filter((jump) =>
            solicitudSaltoSaltaPregunta(questionIndex, jump, order, ids.length),
        )

        // Lógica: solo los saltos que la saltan condicionan visibilidad
        if (jumpsSkipping.length > 0) {
            let allSkippingInactive = true
            for (const jump of jumpsSkipping) {
                if (solicitudEvaluarCondicionSimple(jump, answers)) {
                    allSkippingInactive = false
                    break
                }
            }
            visibility[questionId] = allSkippingInactive
        } else {
            visibility[questionId] = true
        }
    })

    window.solicitudVisibility = visibility
    window.refreshSolicitudVisibleQuestions(window.solicitudFormSelector)
}
```

**Inicialización**:

```javascript
window.initSolicitudConditions = function (myConditions, formSelector) {
    window.solicitudConditions = myConditions
    window.solicitudFormSelector = formSelector
    window.solicitudAnswers = {}

    // Construir orden de preguntas desde el DOM
    const container = document.querySelector(formSelector)
    const questionElements = container.querySelectorAll('.question-item[data-id]')
    window.solicitudOrder = Array.from(questionElements).map((el) =>
        Number(el.getAttribute('data-id')),
    )

    // Añadir listeners a inputs para recalcular visibilidad en tiempo real
    container.querySelectorAll('input, select').forEach((el) => {
        ;['change', 'input'].forEach((eventType) => {
            el.addEventListener(eventType, () => {
                // Actualizar respuesta
                const qId = Number(el.closest('.question-item').getAttribute('data-id'))
                window.solicitudAnswers[qId] = getCurrentAnswer(qId)

                // Recalcular visibilidad
                window.recalculateSolicitudVisibility()
            })
        })
    })

    // Calcular visibilidad inicial
    window.recalculateSolicitudVisibility()
}
```

### Formulario de Convivientes

#### Frontend: `initModalConditions.js`

**Función Principal**: Similar a `formulario-solicitud-conditions.js`, pero adaptado para el modal de convivientes.

**Diferencia Principal**:

- Se ejecuta cuando se abre el modal del conviviente
- Usa `#convivienteModalBody` como contenedor
- Las condiciones se pasan por `window.convivienteConditions[questionnaireId]`

---

## Archivos Involucrados

### Backend (PHP)

1. **`app/Services/SolicitudFormularioService.php`**
    - `calcularVisibilidadSolicitud()`: Calcula visibilidad de preguntas
    - `saltoSaltaPreguntaSolicitud()`: Determina si un salto "salta sobre" una pregunta
    - `evaluarCondicionSimpleSolicitud()`: Evalúa una condición simple

2. **`app/Models/QuestionCondition.php`**
    - `getConditions()`: Obtiene condiciones de un cuestionario desde BD

### Frontend (JavaScript/Vue)

1. **`resources/js/components/WizardTestConditionsModal.vue`**
    - Modal de prueba de condiciones en el wizard
    - Implementa el traductor de saltos a visibilidad
    - Funciones: `isQuestionVisible()`, `getConditionsForQuestion()`, etc.

2. **`resources/js/components/WizardAyuda.vue`**
    - Lógica de traducción de saltos para solicitante y conviviente
    - Funciones: `isQuestionVisibleInTest()`, `isQuestionVisibleInTestConviviente()`

3. **`public/js/formulario-solicitud-conditions.js`**
    - Sistema dinámico de visibilidad para formulario de solicitante
    - Funciones: `initSolicitudConditions()`, `recalculateSolicitudVisibility()`

4. **`public/js/initModalConditions.js`**
    - Sistema dinámico de visibilidad para formulario de convivientes
    - Funciones: `initModalConditions()`, `evaluateAllConditions()`

### Vistas (Blade)

1. **`resources/views/components/ayuda-card/estados/documentacion.blade.php`**
    - Renderiza el formulario de solicitante
    - Inicializa `initSolicitudConditions()` con las condiciones

2. **`resources/views/user/ayuda-solicitada-detalle.blade.php`**
    - Pasa condiciones de convivientes a `window.convivienteConditions`
    - Inicializa el sistema cuando se abre el modal

---

## Ejemplos Prácticos

### Ejemplo 1: Salto Simple

**Configuración**:

- Preguntas: [A, B, C]
- Salto: "Si A == 'Sí', saltar a C"

**Comportamiento**:

- Si A == 'Sí': B se oculta, C visible
- Si A == 'No': Todas visibles

**Código de Condición**:

```json
{
    "question_id": 1,
    "operator": "==",
    "value": "Sí",
    "next_question_id": 3
}
```

### Ejemplo 2: Salto hacia FIN

**Configuración**:

- Preguntas: [A, B, C]
- Salto: "Si A == 'No', saltar a FIN"

**Comportamiento**:

- Si A == 'No': B y C se ocultan
- Si A == 'Sí': Todas visibles

**Código de Condición**:

```json
{
    "question_id": 1,
    "operator": "==",
    "value": "No",
    "next_question_id": null
}
```

### Ejemplo 3: Múltiples Saltos

**Configuración**:

- Preguntas: [A, B, C, D]
- Salto 1: "Si A == 'Sí', saltar a C"
- Salto 2: "Si A == 'No', saltar a D"

**Comportamiento**:

- Si A == 'Sí': B se oculta, C y D visibles
- Si A == 'No': B y C se ocultan, D visible

### Ejemplo 4: Condición con Rules Anidados

**Configuración**:

- Salto con múltiples reglas conectadas con AND/OR

**Código de Condición**:

```json
{
    "question_id": 1,
    "operator": "==",
    "value": "Sí",
    "next_question_id": 3,
    "rules": [
        {
            "question_id": 1,
            "operator": "==",
            "value": "Sí"
        },
        {
            "question_id": 2,
            "operator": ">",
            "value": 18,
            "connector": "AND"
        }
    ]
}
```

**Comportamiento**:

- El salto se cumple solo si pregunta 1 == 'Sí' **Y** pregunta 2 > 18

---

## Normalización de Valores

### Tipos de Preguntas

#### Boolean (Sí/No)

- **Almacenamiento**: `1` (Sí) o `0` (No)
- **Normalización**: Convierte `true`/`false` y `'1'`/`'0'` a números

#### Select

- **Almacenamiento**: Índice de la opción (0, 1, 2...)
- **Comparación**: Numérica

#### Multiple (Checkboxes)

- **Almacenamiento**: Array de índices `[0, 2, 3]`
- **Operadores**: `==` (contiene), `!=` (no contiene)

#### Text/Number

- **Almacenamiento**: String o número
- **Operadores**: `==`, `!=`, `>`, `<`, `>=`, `<=`, `contains`, `not_contains`

---

## Debugging

### Activar Logs en JavaScript

```javascript
// En la consola del navegador
window.DEBUG_SOLICITUD_CONDITIONS = true
```

### Logs en Backend

Los logs se guardan en `storage/logs/laravel.log` con el prefijo `[SolicitudFormulario]`:

- `calcularVisibilidadSolicitud INICIO`: Inicio del cálculo
- `Visibilidad pregunta - saltos encontrados`: Saltos para cada pregunta
- `Evaluar condición SIMPLE`: Evaluación de cada condición
- `calcularVisibilidadSolicitud FIN`: Resultado final de visibilidad

---

## Consideraciones Importantes

### 1. Orden de Preguntas

El orden de las preguntas es **crítico** para determinar qué preguntas son "intermedias" en un salto. El orden se obtiene de:

- **Backend**: `questionnaire_questions.orden`
- **Frontend**: Orden de aparición en el DOM (`.question-item[data-id]`)

### 2. Saltos Directos vs Saltos que Saltan

- **Saltos directos** (A → B): NO condicionan la visibilidad de B
- **Saltos que saltan** (A → C pasando por B): SÍ condicionan la visibilidad de B

### 3. Evaluación en Tiempo Real

En los formularios reales, la visibilidad se recalcula **automáticamente** cuando el usuario cambia una respuesta, sin necesidad de recargar la página.

### 4. Compatibilidad con Formato Antiguo

El sistema soporta tanto el formato nuevo (columnas `operator` y `value`) como el formato antiguo (columna `condition` como JSON).

---

## Resumen de Flujo Completo

1. **Configuración en Wizard**:
    - Usuario crea saltos en Vue Flow
    - Saltos se guardan en `question_conditions` (BD)

2. **Carga en Formulario Real**:
    - Backend carga condiciones desde BD
    - Calcula visibilidad inicial basándose en respuestas guardadas
    - Filtra preguntas visibles antes de renderizar

3. **Interacción del Usuario**:
    - Usuario cambia una respuesta
    - JavaScript recalcula visibilidad en tiempo real
    - Preguntas se muestran/ocultan dinámicamente

4. **Guardado**:
    - Respuestas se guardan normalmente
    - La visibilidad se recalcula en cada carga

---

## Preguntas Frecuentes

### ¿Por qué una pregunta no se oculta cuando debería?

1. Verificar que la condición esté guardada correctamente en BD
2. Verificar que la respuesta del usuario coincida con el valor esperado (normalización)
3. Activar logs de debugging para ver el proceso de evaluación

### ¿Cómo funciona con múltiples saltos?

- Si hay varios saltos que "saltan sobre" una pregunta, la pregunta es visible solo si **NINGUNO** se cumple (lógica AND NOT)
- Si hay saltos directos hacia una pregunta, estos NO condicionan su visibilidad

### ¿Qué pasa si cambio el orden de las preguntas?

El orden afecta qué preguntas son "intermedias" en un salto. Si cambias el orden, los saltos existentes pueden comportarse de manera diferente.

---
