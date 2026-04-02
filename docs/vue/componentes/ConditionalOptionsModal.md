# ConditionalOptionsModal

El componente `ConditionalOptionsModal` es un modal de Vue que permite configurar opciones condicionadas para preguntas en un [`Questionnaire`](/laravel/modelos/Questionnaire). Este componente permite definir qué opciones mostrar en una pregunta según las respuestas de otras preguntas del cuestionario.

Es llamado principalmente en el wizard de creación de ayudas.

## Ubicación

```
resources/js/components/ConditionalOptionsModal.vue
```

## Propósito

Este componente se utiliza en los wizards de creación y edición de cuestionarios para configurar la lógica condicional de opciones. Permite crear reglas del tipo: "Si la pregunta X tiene el valor Y, entonces mostrar solo estas opciones en la pregunta actual".

## Props

### `show` (Boolean, default: `false`)

Controla la visibilidad del modal.

### `question` (Object, default: `null`)

Objeto que representa la pregunta para la cual se están configurando las opciones condicionadas. Debe contener:

- `id`: ID de la pregunta
- `text`: Texto de la pregunta
- `conditionalOptions`: (Opcional) Configuración existente de opciones condicionadas

### `availableOptions` (Array, default: `[]`)

Array con todas las opciones disponibles para la pregunta. Cada opción puede ser:

- Un string simple
- Un objeto con `value` y `text`

### `availableQuestions` (Array, default: `[]`)

Array con todas las preguntas disponibles del cuestionario que pueden usarse como condiciones. Cada pregunta debe contener:

- `id`: ID de la pregunta
- `text`: Texto de la pregunta
- `slug`: Slug de la pregunta (usado para opciones dinámicas)
- `options`: Opciones de la pregunta (si aplica)
- `sectionName`: Nombre de la sección (opcional)
- `sectionIndex`: Índice de la sección (opcional)

## Events

### `@close`

Se emite cuando el usuario cierra el modal sin guardar.

### `@save`

Se emite cuando el usuario guarda la configuración. El payload es un objeto con la siguiente estructura:

```javascript
{
  defaultOptions: Array,        // Opciones por defecto (todas las disponibles)
  conditionalConfigs: Array     // Configuraciones condicionadas
}
```

O `null` si no hay configuraciones válidas.

**Estructura de `conditionalConfigs`:**

```javascript
;[
    {
        dependsOnQuestionId: Number, // ID de la pregunta de la que depende
        conditionType: String, // Tipo de condición: 'equals', 'not_equals', 'contains', 'not_contains'
        expectedValue: String, // Valor esperado para la condición
        options: Array, // Opciones a mostrar cuando se cumple la condición
    },
]
```

## Funcionalidades

### Configuración de Condiciones

El componente permite crear múltiples configuraciones condicionadas. Cada configuración define:

1. **Pregunta de dependencia**: La pregunta cuya respuesta activará la condición
2. **Tipo de condición**:
    - `equals`: Es igual a
    - `not_equals`: No es igual a
    - `contains`: Contiene
    - `not_contains`: No contiene
3. **Valor esperado**: El valor que debe tener la pregunta de dependencia
4. **Opciones a mostrar**: Las opciones que se mostrarán cuando se cumpla la condición

### Opciones Dinámicas

El componente soporta carga dinámica de opciones para preguntas especiales:

- **Comunidades Autónomas** (`comunidad_autonoma`): Carga desde `/admin/searchCCAA`
- **Provincias** (`provincia`): Carga desde `/admin/searchProvincias` (filtradas por CCAA si hay una condición previa)
- **Municipios** (`municipio`): Carga desde `/admin/searchMunicipios` (filtrados por provincia si hay una condición previa)

### Agrupación de Preguntas

Las preguntas disponibles se agrupan por sección (`sectionName`) para facilitar la selección en el dropdown.

## Uso

### Ejemplo Básico

```vue
<template>
    <ConditionalOptionsModal
        :show="showModal"
        :question="currentQuestion"
        :available-options="questionOptions"
        :available-questions="allQuestions"
        @close="closeModal"
        @save="handleSave"
    />
</template>

<script setup>
import { ref } from 'vue'
import ConditionalOptionsModal from '@/components/ConditionalOptionsModal.vue'

const showModal = ref(false)
const currentQuestion = ref(null)
const questionOptions = ref([
  { value: '1', text: 'Opción 1' },
  { value: '2', text: 'Opción 2' },
  { value: '3', text: 'Opción 3' }
])
const allQuestions = ref([
  { id: 1, text: '¿Cuál es tu estado civil?', slug: 'estado_civil', options: [...] },
  { id: 2, text: '¿Tienes hijos?', slug: 'tiene_hijos', options: [...] }
])

const closeModal = () => {
  showModal.value = false
}

const handleSave = (configuration) => {
  if (configuration) {
    // Guardar la configuración
    console.log('Configuración guardada:', configuration)
  }
  closeModal()
}
</script>
```

### Integración en Wizard

El componente se usa típicamente en wizards de cuestionarios como `WizardCollectorStep2` y `WizardCollectorStep3`:

```vue
<ConditionalOptionsModal
    :show="showConditionalOptionsModal"
    :question="conditionalOptionsQuestion"
    :available-options="conditionalOptionsAvailableOptions"
    :available-questions="conditionalOptionsAvailableQuestions"
    @close="closeConditionalOptionsModal"
    @save="saveConditionalOptions"
/>
```

## Estructura de Datos

### Configuración de Opciones Condicionadas

Cuando una pregunta tiene opciones condicionadas configuradas, el objeto `question.conditionalOptions` tiene esta estructura:

```javascript
{
  defaultOptions: ['1', '2', '3'],  // Todas las opciones por defecto
  conditionalConfigs: [
    {
      dependsOnQuestionId: 5,
      conditionType: 'equals',
      expectedValue: 'soltero',
      options: ['1', '2']  // Solo estas opciones si la pregunta 5 es 'soltero'
    },
    {
      dependsOnQuestionId: 5,
      conditionType: 'equals',
      expectedValue: 'casado',
      options: ['2', '3']  // Solo estas opciones si la pregunta 5 es 'casado'
    }
  ]
}
```

## Comportamiento

### Carga de Opciones Dinámicas

Cuando se selecciona una pregunta de dependencia que es de tipo `comunidad_autonoma`, `provincia` o `municipio`, el componente:

1. Muestra un estado de carga (`loadingOptions`)
2. Realiza una petición HTTP al endpoint correspondiente
3. Para provincias y municipios, incluye parámetros de filtrado basados en condiciones previas
4. Transforma la respuesta en un formato estándar `{ value, text }`

### Validación

Al guardar, el componente valida que cada configuración tenga:

- `dependsOnQuestionId` definido
- `conditionType` definido
- `expectedValue` definido
- Al menos una opción seleccionada en `options`

Solo las configuraciones válidas se incluyen en el evento `@save`.

## Notas Técnicas

- El componente usa `watch` con `deep: true` para detectar cambios en las configuraciones condicionadas
- Las opciones dinámicas se cargan automáticamente cuando cambia la pregunta de dependencia
- El componente excluye la pregunta actual de la lista de preguntas disponibles para evitar dependencias circulares
- Se usa `computed` para agrupar las preguntas por sección de forma reactiva

## Relación con Backend

Este componente trabaja en conjunto con el modelo [`Questionnaire`](/laravel/modelos/Questionnaire) y las [`Question`](/laravel/modelos/Question) asociadas. La configuración guardada se almacena en [`QuestionCondition`](/laravel/modelos/QuestionCondition), permitiendo que el sistema evalúe estas condiciones en tiempo de ejecución cuando un usuario completa el cuestionario.
