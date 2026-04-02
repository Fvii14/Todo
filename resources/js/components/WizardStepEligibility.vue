<template>
  <div class="w-full min-h-[600px] relative">
    <!-- Header con información -->
    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold">
          🎯
        </div>
        <div>
          <h3 class="text-lg font-bold text-green-900">Lógica de Elegibilidad</h3>
          <p class="text-sm text-green-700">Define los requisitos que debe cumplir el usuario para ser beneficiario</p>
        </div>
      </div>
    </div>

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
            👤
          </div>
          <h4 class="font-semibold text-blue-900">Requisitos del Solicitante</h4>
        </div>
        <p class="text-sm text-blue-700">
          Se evalúan únicamente las respuestas del solicitante principal. 
          Perfecto para requisitos personales como edad, ingresos, estado civil, etc.
        </p>
      </div>
      
      <div class="p-4 bg-purple-50 rounded-xl border border-purple-200">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-6 h-6 bg-purple-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
            🏠
          </div>
          <h4 class="font-semibold text-purple-900">Requisitos de Unidad de Convivencia</h4>
        </div>
        <p class="text-sm text-purple-700">
          Se evalúan TODAS las respuestas de la unidad (solicitante + convivientes). 
          Ideal para requisitos familiares como ingresos totales, número de miembros, etc.
        </p>
      </div>
    </div>

    <!-- Formulario para añadir requisitos -->
    <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm">
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
            +
          </div>
          <div>
            <h4 class="font-semibold text-gray-800">Añadir nuevo requisito</h4>
            <p class="text-sm text-gray-600">Configura los criterios de elegibilidad para esta ayuda</p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <div class="mb-6">
          <h5 class="text-sm font-medium text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-cog text-blue-500"></i>
            Configuración básica
          </h5>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Tipo de requisito</label>
              <select v-model="newRequirement.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                <option value="simple">Requisito simple (una pregunta)</option>
                <option value="group">Grupo de requisitos (múltiples preguntas)</option>
              </select>
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Aplicar a</label>
              <select v-model="newRequirement.personType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                <option value="solicitante">Solicitante</option>
                <option value="unidad_convivencia_completa">Unidad de Convivencia (Completa)</option>
                <option value="unidad_convivencia_sin_solicitante">Unidad de Convivencia (Sin Solicitante)</option>
                <option value="unidad_familiar_completa">Unidad Familiar (Completa)</option>
                <option value="unidad_familiar_sin_solicitante">Unidad Familiar (Sin Solicitante)</option>
                <option value="conviviente">Conviviente Específico</option>
                <option value="any_conviviente">Cualquier Conviviente</option>
                <option value="any_familiar">Cualquier Familiar</option>
                <option value="any_persona_unidad">Cualquier Persona de la Unidad</option>
              </select>
              <p class="text-xs text-gray-500 leading-relaxed">
                {{ getPersonTypeDescription(newRequirement.personType) }}
              </p>
            </div>
          </div>
        </div>

        <div v-if="newRequirement.type === 'simple'" class="space-y-6">
          <div>
            <h5 class="text-sm font-medium text-gray-700 mb-4 flex items-center gap-2">
              <i class="fas fa-list-ul text-green-500"></i>
              Requisito simple
            </h5>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Descripción del requisito</label>
              <input 
                v-model="newRequirement.description" 
                placeholder="Ej: Tener ingresos superiores a 1000€" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
              />
            </div>
            
            <div v-if="newRequirement.personType === 'conviviente'" class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de conviviente</label>
              <select v-model="newRequirement.convivienteType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                <option value="">Selecciona el tipo de conviviente</option>
                <option value="conyuge">Cónyuge</option>
                <option value="hijo">Hijo/a</option>
                <option value="padre">Padre/Madre</option>
                <option value="otro">Otro familiar</option>
                <option value="no_familiar">No familiar</option>
              </select>
            </div>
        
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Pregunta a evaluar</label>
              <div class="relative dropdown-container">
                <div 
                  @click="toggleQuestionSearch('simple')"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors"
                >
                  <span v-if="newRequirement.question_id" class="text-gray-900 truncate">
                    {{ getQuestionText(newRequirement.question_id) }}
                  </span>
                  <span v-else class="text-gray-500">Selecciona una pregunta</span>
                  <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                </div>
                
                <div v-if="showQuestionSearch === 'simple'" class="dropdown-menu">
                  <div class="p-3 border-b border-gray-200 bg-gray-50">
                    <input 
                      v-model="questionSearchTerm"
                      placeholder="Buscar pregunta..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      @input="filterQuestions"
                    />
                  </div>
                  <div class="max-h-48 overflow-y-auto">
                    <div 
                      v-for="question in filteredQuestions" 
                      :key="question.id"
                      @click="selectQuestion(question.id, 'simple')"
                      class="px-3 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-b-0 transition-colors"
                    >
                      <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                          <div class="font-medium text-gray-900 truncate">{{ question.text }}</div>
                          <div class="text-xs text-gray-500 mt-1">
                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                              {{ question.type }}
                            </span>
                          </div>
                        </div>
                        <div v-if="isCurrentQuestion(question.id)" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-2">
                          Actual
                        </div>
                      </div>
                    </div>
                    <div v-if="filteredQuestions.length === 0" class="px-3 py-4 text-sm text-gray-500 text-center">
                      No se encontraron preguntas
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Operador</label>
                <select 
                  v-model="newRequirement.operator" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                  <option v-for="op in getAvailableOperators()" :key="op.value" :value="op.value">
                    {{ op.label }}
                  </option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Valor esperado</label>
                
                <div v-if="getQuestionType() === 'date'" class="space-y-3">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de valor</label>
                    <div class="grid grid-cols-2 gap-2">
                      <label class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input 
                          v-model="newRequirement.valueType" 
                          type="radio" 
                          value="exact" 
                          name="valueType"
                          class="mr-2"
                        />
                        <span class="text-sm">Fecha exacta</span>
                      </label>
                      <label class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input 
                          v-model="newRequirement.valueType" 
                          type="radio" 
                          value="age_minimum" 
                          name="valueType"
                          class="mr-2"
                        />
                        <span class="text-sm">Edad mínima</span>
                      </label>
                      <label class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input 
                          v-model="newRequirement.valueType" 
                          type="radio" 
                          value="age_maximum" 
                          name="valueType"
                          class="mr-2"
                        />
                        <span class="text-sm">Edad máxima</span>
                      </label>
                      <label class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                        <input 
                          v-model="newRequirement.valueType" 
                          type="radio" 
                          value="age_range" 
                          name="valueType"
                          class="mr-2"
                        />
                        <span class="text-sm">Rango de edad</span>
                      </label>
                    </div>
                  </div>
                  
                  <div v-if="newRequirement.valueType === 'exact'">
                    <input 
                      v-model="newRequirement.value" 
                      type="date"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    />
                  </div>
                  
                  <div v-else-if="newRequirement.valueType === 'between'" class="flex gap-2">
                    <input 
                      v-model="newRequirement.value" 
                      type="date"
                      placeholder="Fecha inicio"
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    />
                    <span class="flex items-center text-gray-500 text-sm">y</span>
                    <input 
                      v-model="newRequirement.value2" 
                      type="date"
                      placeholder="Fecha fin"
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    />
                  </div>
                  
                  <div v-else-if="newRequirement.valueType === 'age_minimum'" class="grid grid-cols-3 gap-2">
                    <input 
                      v-model="newRequirement.value" 
                      type="number" 
                      min="0" 
                      placeholder="Edad"
                      class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    />
                    <select v-model="newRequirement.ageUnit" class="col-span-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                      <option value="years">años</option>
                      <option value="months">meses</option>
                      <option value="days">días</option>
                    </select>
                  </div>
                  
                  <div v-else-if="newRequirement.valueType === 'age_maximum'" class="grid grid-cols-3 gap-2">
                    <input 
                      v-model="newRequirement.value" 
                      type="number" 
                      min="0" 
                      placeholder="Edad"
                      class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    />
                    <select v-model="newRequirement.ageUnit" class="col-span-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                      <option value="years">años</option>
                      <option value="months">meses</option>
                      <option value="days">días</option>
                    </select>
                  </div>
                  
                  <div v-else-if="newRequirement.valueType === 'age_range'" class="space-y-2">
                    <div class="grid grid-cols-3 gap-2">
                      <input 
                        v-model="newRequirement.value" 
                        type="number" 
                        min="0" 
                        placeholder="Edad mínima"
                        class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                      />
                      <select v-model="newRequirement.ageUnit" class="col-span-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option value="years">años</option>
                        <option value="months">meses</option>
                        <option value="days">días</option>
                      </select>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                      <input 
                        v-model="newRequirement.value2" 
                        type="number" 
                        min="0" 
                        placeholder="Edad máxima"
                        class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                      />
                      <div class="col-span-1 px-3 py-2 text-sm text-gray-500 flex items-center">
                        {{ getAgeUnitText(newRequirement.ageUnit) }}
                      </div>
                    </div>
                  </div>
                  
                  <!-- Texto de ayuda -->
                  <p class="text-xs text-gray-500">
                    <span v-if="newRequirement.valueType === 'exact'">Se evaluará la fecha exacta</span>
                    <span v-else-if="newRequirement.valueType === 'age_minimum'">Se evaluará si la edad es mayor a {{ newRequirement.value || 'X' }} {{ getAgeUnitText(newRequirement.ageUnit) }}</span>
                    <span v-else-if="newRequirement.valueType === 'age_maximum'">Se evaluará si la edad es menor a {{ newRequirement.value || 'X' }} {{ getAgeUnitText(newRequirement.ageUnit) }}</span>
                    <span v-else-if="newRequirement.valueType === 'age_range'">Se evaluará si la edad está entre {{ newRequirement.value || 'X' }} y {{ newRequirement.value2 || 'Y' }} {{ getAgeUnitText(newRequirement.ageUnit) }}</span>
                  </p>
                </div>
                
                <input 
                  v-else-if="getQuestionType() === 'text' || getQuestionType() === 'number' || getQuestionType() === 'date'" 
                  v-model="newRequirement.value" 
                  :type="getQuestionType() === 'number' ? 'number' : getQuestionType() === 'date' ? 'date' : 'text'"
                  :placeholder="getQuestionType() === 'number' ? 'Ingresa un número' : getQuestionType() === 'date' ? 'Selecciona una fecha' : 'Ingresa texto'"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                />
                
                <div v-else-if="getQuestionType() === 'select' && isMunicipioQuestion()" class="relative dropdown-container">
                  <div 
                    @click="toggleValueSearch('simple')"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors text-sm"
                  >
                    <span v-if="newRequirement.value" class="text-gray-900 truncate">
                      {{ newRequirement.value }}
                    </span>
                    <span v-else class="text-gray-500">Selecciona una opción</span>
                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                  </div>
                  
                  <div v-if="showValueSearch === 'simple'" class="dropdown-menu">
                    <div class="p-3 border-b border-gray-200 bg-gray-50">
                      <input 
                        v-model="valueSearchTerm"
                        placeholder="Buscar opción..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @input="filterValueOptions"
                      />
                    </div>
                    <div class="max-h-48 overflow-y-auto">
                      <div 
                        v-for="(option, index) in filteredValueOptions" 
                        :key="index"
                        @click="selectValue(option, 'simple')"
                        class="px-3 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-b-0 transition-colors"
                      >
                        <div class="font-medium text-gray-900">{{ option }}</div>
                      </div>
                      <div v-if="filteredValueOptions.length === 0" class="px-3 py-4 text-sm text-gray-500 text-center">
                        No se encontraron opciones
                      </div>
                    </div>
                  </div>
                </div>
                
                <select 
                  v-else-if="getQuestionType() === 'select'" 
                  v-model="newRequirement.value" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                  <option value="">Selecciona una opción</option>
                  <option v-for="(option, index) in dynamicOptions" :key="index" :value="option">
                    {{ option }}
                  </option>
                </select>
                
                <select 
                  v-else-if="getQuestionType() === 'multiple'" 
                  v-model="newRequirement.value" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                  <option value="">Selecciona una opción</option>
                  <option value="[null]">No seleccionar ninguna opción</option>
                  <option v-for="(option, index) in dynamicOptions" :key="index" :value="option">
                    {{ option }}
                  </option>
                </select>
                
                <select 
                  v-else-if="getQuestionType() === 'boolean'" 
                  v-model="newRequirement.value" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                >
                  <option value="">Selecciona una opción</option>
                  <option value="1">Sí</option>
                  <option value="0">No</option>
                </select>
                
                <input 
                  v-else 
                  v-model="newRequirement.value" 
                  placeholder="Ingresa el valor" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                />
              </div>
            </div>
            
            <div class="flex justify-end pt-4">
              <button 
                @click="addRequirement" 
                :disabled="!canAddSimpleRequirement"
                class="inline-flex items-center gap-2 bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
              >
                <i class="fas fa-plus text-xs"></i>
                Añadir requisito
              </button>
            </div>
          </div>
        </div>

        <div v-if="newRequirement.type === 'group'" class="space-y-6">
          <div>
            <h5 class="text-sm font-medium text-gray-700 mb-4 flex items-center gap-2">
              <i class="fas fa-layer-group text-orange-500"></i>
              Grupo de requisitos
            </h5>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción del grupo</label>
                <input 
                  v-model="newRequirement.description" 
                  placeholder="Ej: Requisitos de ingresos familiares" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Lógica del grupo</label>
                <select v-model="newRequirement.groupLogic" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                  <option value="AND">TODOS deben cumplirse (AND)</option>
                  <option value="OR">AL MENOS UNO debe cumplirse (OR)</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                  {{ newRequirement.groupLogic === 'AND' ? 'Todas las reglas deben cumplirse simultáneamente' : 'Al menos una regla debe cumplirse' }}
                </p>
              </div>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de elementos del grupo</label>
              <div class="grid grid-cols-2 gap-2">
                <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                  <input 
                    v-model="newRequirement.groupType" 
                    type="radio" 
                    value="rules" 
                    name="groupType"
                    class="mr-3"
                  />
                  <div>
                    <div class="text-sm font-medium">Solo reglas simples</div>
                    <div class="text-xs text-gray-500">Preguntas individuales con operadores</div>
                  </div>
                </label>
                <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                  <input 
                    v-model="newRequirement.groupType" 
                    type="radio" 
                    value="mixed" 
                    name="groupType"
                    class="mr-3"
                  />
                  <div>
                    <div class="text-sm font-medium">Reglas y subgrupos</div>
                    <div class="text-xs text-gray-500">Puede contener reglas simples y otros grupos</div>
                  </div>
                </label>
              </div>
            </div>
            
            <div v-if="newRequirement.personType === 'conviviente'" class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de conviviente</label>
              <select v-model="newRequirement.convivienteType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                <option value="">Selecciona el tipo de conviviente</option>
                <option value="conyuge">Cónyuge</option>
                <option value="hijo">Hijo/a</option>
                <option value="padre">Padre/Madre</option>
                <option value="otro">Otro familiar</option>
                <option value="no_familiar">No familiar</option>
              </select>
            </div>
        
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
              <div class="flex items-center justify-between mb-4">
                <h6 class="text-sm font-medium text-gray-800 flex items-center gap-2">
                  <i class="fas fa-list text-gray-500"></i>
                  Elementos del grupo
                </h6>
                <span class="text-xs text-gray-500">
                  {{ newRequirement.rules.length + newRequirement.subgroups.length }} elemento{{ (newRequirement.rules.length + newRequirement.subgroups.length) !== 1 ? 's' : '' }}
                </span>
              </div>
              
              <div v-if="newRequirement.rules.length === 0 && newRequirement.subgroups.length === 0" class="text-center py-6 text-gray-500">
                <i class="fas fa-plus-circle text-2xl mb-2 text-gray-300"></i>
                <p class="text-sm">Añade al menos un elemento al grupo</p>
              </div>
              
              <div v-else class="space-y-3 mb-4">
                <div 
                  v-for="(rule, ruleIndex) in newRequirement.rules" 
                  :key="`rule-${ruleIndex}`"
                  class="bg-white rounded-lg border border-gray-200 p-4"
                >
                  <div class="grid grid-cols-12 gap-3 items-start">
                    <div class="col-span-1 flex justify-center">
                      <span class="text-xs font-medium text-gray-500 bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        R{{ ruleIndex + 1 }}
                      </span>
                    </div>

                    <div class="col-span-5 relative dropdown-container">
                      <label class="block text-xs font-medium text-gray-600 mb-1">Pregunta</label>
                      <div 
                        @click="toggleQuestionSearch(`group-${ruleIndex}`)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors min-h-[38px]"
                      >
                        <span v-if="rule.question_id" class="text-gray-900 text-sm line-clamp-2 flex-1 min-w-0">
                          {{ getQuestionText(rule.question_id) }}
                        </span>
                        <span v-else class="text-gray-500 text-sm">Selecciona pregunta</span>
                        <i class="fas fa-chevron-down text-gray-400 text-xs ml-2 flex-shrink-0"></i>
                      </div>
                      
                      <div v-if="showQuestionSearch === `group-${ruleIndex}`" class="dropdown-menu">
                        <div class="p-3 border-b border-gray-200 bg-gray-50">
                          <input 
                            v-model="questionSearchTerm"
                            placeholder="Buscar pregunta..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @input="filterQuestions"
                          />
                        </div>
                        <div class="max-h-48 overflow-y-auto">
                          <div 
                            v-for="question in filteredQuestions" 
                            :key="question.id"
                            @click="selectQuestion(question.id, `group-${ruleIndex}`)"
                            class="px-3 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-b-0 transition-colors"
                          >
                            <div class="flex items-center justify-between">
                              <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 line-clamp-2">{{ question.text }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                  <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                                    {{ question.type }}
                                  </span>
                                </div>
                              </div>
                              <div v-if="isCurrentQuestion(question.id)" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-2 flex-shrink-0">
                                Actual
                              </div>
                            </div>
                          </div>
                          <div v-if="filteredQuestions.length === 0" class="px-3 py-4 text-sm text-gray-500 text-center">
                            No se encontraron preguntas
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Operador -->
                    <div class="col-span-2">
                      <label class="block text-xs font-medium text-gray-600 mb-1">Operador</label>
                      <select 
                        v-model="rule.operator" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      >
                        <option v-for="op in getAvailableOperatorsForRule(rule)" :key="op.value" :value="op.value">
                          {{ op.label }}
                        </option>
                      </select>
                    </div>
                    
                    <!-- Valor -->
                    <div class="col-span-3">
                      <label class="block text-xs font-medium text-gray-600 mb-1">Valor</label>

                      <div v-if="getQuestionTypeForRule(rule) === 'date'" class="space-y-2">
                        <div>
                          <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de valor</label>
                          <div class="grid grid-cols-2 gap-1">
                            <label class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                              <input 
                                v-model="rule.valueType" 
                                type="radio" 
                                value="exact" 
                                :name="`rule-${ruleIndex}-valueType`"
                                class="mr-1 text-xs"
                              />
                              <span class="text-xs">Exacta</span>
                            </label>
                            <label class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                              <input 
                                v-model="rule.valueType" 
                                type="radio" 
                                value="age_minimum" 
                                :name="`rule-${ruleIndex}-valueType`"
                                class="mr-1 text-xs"
                              />
                              <span class="text-xs">Edad mín.</span>
                            </label>
                            <label class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                              <input 
                                v-model="rule.valueType" 
                                type="radio" 
                                value="age_maximum" 
                                :name="`rule-${ruleIndex}-valueType`"
                                class="mr-1 text-xs"
                              />
                              <span class="text-xs">Edad máx.</span>
                            </label>
                            <label class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                              <input 
                                v-model="rule.valueType" 
                                type="radio" 
                                value="age_range" 
                                :name="`rule-${ruleIndex}-valueType`"
                                class="mr-1 text-xs"
                              />
                              <span class="text-xs">Rango</span>
                            </label>
                          </div>
                        </div>
                        
                        <div v-if="rule.valueType === 'exact'">
                          <input 
                            v-model="rule.value" 
                            type="date"
                            class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                          />
                        </div>
                        
                        <div v-else-if="rule.valueType === 'age_minimum'" class="grid grid-cols-3 gap-1">
                          <input 
                            v-model="rule.value" 
                            type="number" 
                            min="0" 
                            placeholder="Edad"
                            class="col-span-2 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                          />
                          <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                            <option value="years">años</option>
                            <option value="months">meses</option>
                            <option value="days">días</option>
                          </select>
                        </div>
                        
                        <div v-else-if="rule.valueType === 'age_maximum'" class="grid grid-cols-3 gap-1">
                          <input 
                            v-model="rule.value" 
                            type="number" 
                            min="0" 
                            placeholder="Edad"
                            class="col-span-2 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                          />
                          <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                            <option value="years">años</option>
                            <option value="months">meses</option>
                            <option value="days">días</option>
                          </select>
                        </div>
                        
                        <div v-else-if="rule.valueType === 'age_range'" class="space-y-1">
                          <div class="grid grid-cols-3 gap-1">
                            <input 
                              v-model="rule.value" 
                              type="number" 
                              min="0" 
                              placeholder="Mín."
                              class="col-span-2 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                            />
                            <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                              <option value="years">años</option>
                              <option value="months">meses</option>
                              <option value="days">días</option>
                            </select>
                          </div>
                          <div class="grid grid-cols-3 gap-1">
                            <input 
                              v-model="rule.value2" 
                              type="number" 
                              min="0" 
                              placeholder="Máx."
                              class="col-span-2 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                            />
                            <div class="col-span-1 px-2 py-1 text-xs text-gray-500 flex items-center">
                              {{ getAgeUnitText(rule.ageUnit) }}
                            </div>
                          </div>
                        </div>
                      </div>

                      <input 
                        v-else-if="getQuestionTypeForRule(rule) === 'text' || getQuestionTypeForRule(rule) === 'number'" 
                        v-model="rule.value" 
                        :type="getQuestionTypeForRule(rule) === 'number' ? 'number' : 'text'"
                        :placeholder="getQuestionTypeForRule(rule) === 'number' ? 'Número' : 'Texto'"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                      />
                      <div v-else-if="getQuestionTypeForRule(rule) === 'select' && isMunicipioQuestionForRule(rule)" class="relative dropdown-container">
                        <div 
                          @click="toggleValueSearch(`group-${ruleIndex}`)"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors text-sm"
                        >
                          <span v-if="rule.value" class="text-gray-900 truncate">
                            {{ rule.value }}
                          </span>
                          <span v-else class="text-gray-500">Selecciona opción</span>
                          <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                        
                        <div v-if="showValueSearch === `group-${ruleIndex}`" class="dropdown-menu">
                          <div class="p-3 border-b border-gray-200 bg-gray-50">
                            <input 
                              v-model="valueSearchTerm"
                              placeholder="Buscar opción..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              @input="filterValueOptions"
                            />
                          </div>
                          <div class="max-h-48 overflow-y-auto">
                            <div 
                              v-for="(option, index) in getFilteredValueOptionsForRule(rule)" 
                              :key="index"
                              @click="selectValueForRule(option, `group-${ruleIndex}`)"
                              class="px-3 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-b-0 transition-colors"
                            >
                              <div class="font-medium text-gray-900">{{ option }}</div>
                            </div>
                            <div v-if="getFilteredValueOptionsForRule(rule).length === 0" class="px-3 py-4 text-sm text-gray-500 text-center">
                              No se encontraron opciones
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <select 
                        v-else-if="getQuestionTypeForRule(rule) === 'select'" 
                        v-model="rule.value" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                      >
                        <option value="">Selecciona opción</option>
                        <option v-for="(option, index) in getDynamicOptionsForRule(rule)" :key="index" :value="option">
                          {{ option }}
                        </option>
                      </select>
                      <select 
                        v-else-if="getQuestionTypeForRule(rule) === 'multiple'" 
                        v-model="rule.value" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                      >
                        <option value="">Selecciona opción</option>
                        <option value="[null]">No seleccionar ninguna opción</option>
                        <option v-for="(option, index) in getDynamicOptionsForRule(rule)" :key="index" :value="option">
                          {{ option }}
                        </option>
                      </select>
                      <select 
                        v-else-if="getQuestionTypeForRule(rule) === 'boolean'" 
                        v-model="rule.value" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                      >
                        <option value="">Selecciona</option>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                      </select>
                      <input 
                        v-else 
                        v-model="rule.value" 
                        placeholder="Valor" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                      />
                    </div>
                    
                    <!-- Botón eliminar -->
                    <div class="col-span-1 flex justify-center items-end">
                      <button 
                        @click="removeRuleFromGroup(ruleIndex)"
                        class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-lg transition-colors"
                        title="Eliminar regla"
                      >
                        <i class="fas fa-trash text-sm"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <div 
                  v-for="(subgroup, subgroupIndex) in newRequirement.subgroups" 
                  :key="`subgroup-${subgroupIndex}`"
                  class="bg-orange-50 rounded-lg border border-orange-200 p-4"
                >
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                      <span class="text-xs font-medium text-orange-800 bg-orange-200 px-2 py-1 rounded-full">
                        G{{ subgroupIndex + 1 }}
                      </span>
                      <input 
                        v-model="subgroup.description"
                        placeholder="Descripción del subgrupo"
                        class="text-sm font-medium text-orange-900 bg-transparent border-none outline-none flex-1"
                      />
                      <select v-model="subgroup.groupLogic" class="text-xs text-orange-600 bg-orange-100 border border-orange-300 rounded px-2 py-1">
                        <option value="AND">AND</option>
                        <option value="OR">OR</option>
                      </select>
                      <select v-model="subgroup.nestedSubgroupsLogic" class="text-xs text-orange-600 bg-orange-100 border border-orange-300 rounded px-2 py-1 ml-2">
                        <option value="">Sin anidados</option>
                        <option value="AND">Anidados (AND)</option>
                        <option value="OR">Anidados (OR)</option>
                      </select>
                    </div>
                    <button 
                      @click="removeSubgroupFromGroup(subgroupIndex)"
                      class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded-lg transition-colors"
                      title="Eliminar subgrupo"
                    >
                      <i class="fas fa-trash text-sm"></i>
                    </button>
              </div>
              
                  <div class="ml-4 space-y-3">
                    <div v-if="subgroup.rules.length === 0" class="text-xs text-orange-600 italic">
                      Sin reglas configuradas
                    </div>
                    <div v-else class="space-y-2">
                      <div 
                        v-for="(rule, ruleIndex) in subgroup.rules" 
                        :key="`subrule-${ruleIndex}`"
                        class="bg-white rounded-lg border border-orange-200 p-3"
                      >
                        <div class="grid grid-cols-12 gap-2 items-start">
                          <div class="col-span-1 flex justify-center">
                            <span class="text-xs font-medium text-orange-500 bg-orange-100 px-2 py-1 rounded-full">
                              {{ ruleIndex + 1 }}
                            </span>
                          </div>

                          <div class="col-span-4 relative dropdown-container">
                            <label class="block text-xs font-medium text-orange-600 mb-1">Pregunta</label>
                            <div 
                              @click="toggleQuestionSearch(`subgroup-${subgroupIndex}-${ruleIndex}`)"
                              class="w-full px-2 py-1 border border-orange-300 rounded cursor-pointer bg-white flex items-center justify-between hover:border-orange-400 transition-colors min-h-[32px]"
                            >
                              <span v-if="rule.question_id" class="text-orange-900 text-xs line-clamp-1 flex-1 min-w-0">
                                {{ getQuestionText(rule.question_id) }}
                              </span>
                              <span v-else class="text-orange-500 text-xs">Selecciona</span>
                              <i class="fas fa-chevron-down text-orange-400 text-xs ml-1 flex-shrink-0"></i>
                            </div>
                            
                            <div v-if="showQuestionSearch === `subgroup-${subgroupIndex}-${ruleIndex}`" class="dropdown-menu">
                              <div class="p-2 border-b border-gray-200 bg-gray-50">
                                <input 
                                  v-model="questionSearchTerm"
                                  placeholder="Buscar pregunta..."
                                  class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                  @input="filterQuestions"
                                />
                              </div>
                              <div class="max-h-32 overflow-y-auto">
                                <div 
                                  v-for="question in filteredQuestions" 
                                  :key="question.id"
                                  @click="selectQuestionForSubgroup(question.id, subgroupIndex, ruleIndex)"
                                  class="px-2 py-2 hover:bg-orange-50 cursor-pointer text-xs border-b border-gray-100 last:border-b-0 transition-colors"
                                >
                                  <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                      <div class="font-medium text-gray-900 line-clamp-1">{{ question.text }}</div>
                                      <div class="text-xs text-gray-500 mt-1">
                                        <span class="inline-flex items-center px-1 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                          {{ question.type }}
                                        </span>
                                      </div>
                                    </div>
                                    <div v-if="isCurrentQuestion(question.id)" class="text-xs bg-orange-100 text-orange-800 px-1 py-0.5 rounded-full ml-1 flex-shrink-0">
                                      Actual
                                    </div>
                                  </div>
                                </div>
                                <div v-if="filteredQuestions.length === 0" class="px-2 py-2 text-xs text-gray-500 text-center">
                                  No se encontraron preguntas
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="col-span-2">
                            <label class="block text-xs font-medium text-orange-600 mb-1">Operador</label>
                            <select 
                              v-model="rule.operator" 
                              class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                            >
                              <option v-for="op in getAvailableOperatorsForRule(rule)" :key="op.value" :value="op.value">
                                {{ op.label }}
                              </option>
                            </select>
                          </div>

                          <div class="col-span-4">
                            <label class="block text-xs font-medium text-orange-600 mb-1">Valor</label>

                            <div v-if="getQuestionTypeForRule(rule) === 'date'" class="space-y-1">
                              <div>
                                <label class="block text-xs font-medium text-orange-600 mb-1">Tipo</label>
                                <div class="grid grid-cols-2 gap-1">
                                  <label class="flex items-center p-1 border border-orange-300 rounded cursor-pointer hover:bg-orange-50">
                                    <input 
                                      v-model="rule.valueType" 
                                      type="radio" 
                                      value="exact" 
                                      :name="`subrule-${subgroupIndex}-${ruleIndex}-valueType`"
                                      class="mr-1 text-xs"
                                    />
                                    <span class="text-xs">Exacta</span>
                                  </label>
                                  <label class="flex items-center p-1 border border-orange-300 rounded cursor-pointer hover:bg-orange-50">
                                    <input 
                                      v-model="rule.valueType" 
                                      type="radio" 
                                      value="age_minimum" 
                                      :name="`subrule-${subgroupIndex}-${ruleIndex}-valueType`"
                                      class="mr-1 text-xs"
                                    />
                                    <span class="text-xs">Edad mín.</span>
                                  </label>
                                  <label class="flex items-center p-1 border border-orange-300 rounded cursor-pointer hover:bg-orange-50">
                                    <input 
                                      v-model="rule.valueType" 
                                      type="radio" 
                                      value="age_maximum" 
                                      :name="`subrule-${subgroupIndex}-${ruleIndex}-valueType`"
                                      class="mr-1 text-xs"
                                    />
                                    <span class="text-xs">Edad máx.</span>
                                  </label>
                                  <label class="flex items-center p-1 border border-orange-300 rounded cursor-pointer hover:bg-orange-50">
                                    <input 
                                      v-model="rule.valueType" 
                                      type="radio" 
                                      value="age_range" 
                                      :name="`subrule-${subgroupIndex}-${ruleIndex}-valueType`"
                                      class="mr-1 text-xs"
                                    />
                                    <span class="text-xs">Rango</span>
                                  </label>
                                </div>
                              </div>
                              
                              <div v-if="rule.valueType === 'exact'">
                                <input 
                                  v-model="rule.value" 
                                  type="date"
                                  class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                />
                              </div>
                              
                              <div v-else-if="rule.valueType === 'age_minimum'" class="grid grid-cols-3 gap-1">
                                <input 
                                  v-model="rule.value" 
                                  type="number" 
                                  min="0" 
                                  placeholder="Edad"
                                  class="col-span-2 px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                />
                                <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent">
                                  <option value="years">años</option>
                                  <option value="months">meses</option>
                                  <option value="days">días</option>
                                </select>
                              </div>
                              
                              <div v-else-if="rule.valueType === 'age_maximum'" class="grid grid-cols-3 gap-1">
                                <input 
                                  v-model="rule.value" 
                                  type="number" 
                                  min="0" 
                                  placeholder="Edad"
                                  class="col-span-2 px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                />
                                <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent">
                                  <option value="years">años</option>
                                  <option value="months">meses</option>
                                  <option value="days">días</option>
                                </select>
                              </div>
                              
                              <div v-else-if="rule.valueType === 'age_range'" class="space-y-1">
                                <div class="grid grid-cols-3 gap-1">
                                  <input 
                                    v-model="rule.value" 
                                    type="number" 
                                    min="0" 
                                    placeholder="Mín."
                                    class="col-span-2 px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                  />
                                  <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent">
                                    <option value="years">años</option>
                                    <option value="months">meses</option>
                                    <option value="days">días</option>
                                  </select>
                                </div>
                                <div class="grid grid-cols-3 gap-1">
                                  <input 
                                    v-model="rule.value2" 
                                    type="number" 
                                    min="0" 
                                    placeholder="Máx."
                                    class="col-span-2 px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                  />
                                  <div class="col-span-1 px-2 py-1 text-xs text-orange-500 flex items-center">
                                    {{ getAgeUnitText(rule.ageUnit) }}
                                  </div>
                                </div>
                              </div>
                            </div>

                            <input 
                              v-else-if="getQuestionTypeForRule(rule) === 'text' || getQuestionTypeForRule(rule) === 'number'" 
                              v-model="rule.value" 
                              :type="getQuestionTypeForRule(rule) === 'number' ? 'number' : 'text'"
                              :placeholder="getQuestionTypeForRule(rule) === 'number' ? 'Número' : 'Texto'"
                              class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                            />
                            <div v-else-if="getQuestionTypeForRule(rule) === 'select' && isMunicipioQuestionForRule(rule)" class="relative dropdown-container">
                              <div 
                                @click="toggleValueSearch(`subgroup-${subgroupIndex}-${ruleIndex}`)"
                                class="w-full px-2 py-1 border border-orange-300 rounded cursor-pointer bg-white flex items-center justify-between hover:border-orange-400 transition-colors text-xs"
                              >
                                <span v-if="rule.value" class="text-orange-900 truncate">
                                  {{ rule.value }}
                                </span>
                                <span v-else class="text-orange-500">Selecciona</span>
                                <i class="fas fa-chevron-down text-orange-400 text-xs"></i>
                              </div>
                              
                              <div v-if="showValueSearch === `subgroup-${subgroupIndex}-${ruleIndex}`" class="dropdown-menu">
                                <div class="p-2 border-b border-gray-200 bg-gray-50">
                                  <input 
                                    v-model="valueSearchTerm"
                                    placeholder="Buscar opción..."
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                    @input="filterValueOptions"
                                  />
                                </div>
                                <div class="max-h-32 overflow-y-auto">
                                  <div 
                                    v-for="(option, index) in getFilteredValueOptionsForRule(rule)" 
                                    :key="index"
                                    @click="selectValueForRule(option, `subgroup-${subgroupIndex}-${ruleIndex}`)"
                                    class="px-2 py-2 hover:bg-orange-50 cursor-pointer text-xs border-b border-gray-100 last:border-b-0 transition-colors"
                                  >
                                    <div class="font-medium text-gray-900">{{ option }}</div>
                                  </div>
                                  <div v-if="getFilteredValueOptionsForRule(rule).length === 0" class="px-2 py-2 text-xs text-gray-500 text-center">
                                    No se encontraron opciones
                                  </div>
                                </div>
                              </div>
                            </div>
                            
                            <select 
                              v-else-if="getQuestionTypeForRule(rule) === 'select'" 
                              v-model="rule.value" 
                              class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                            >
                              <option value="">Selecciona</option>
                              <option v-for="(option, index) in getDynamicOptionsForRule(rule)" :key="index" :value="option">
                                {{ option }}
                              </option>
                            </select>
                            <select 
                              v-else-if="getQuestionTypeForRule(rule) === 'multiple'" 
                              v-model="rule.value" 
                              class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                            >
                              <option value="">Selecciona opción</option>
                              <option value="[null]">No seleccionar ninguna opción</option>
                              <option v-for="(option, index) in getDynamicOptionsForRule(rule)" :key="index" :value="option">
                                {{ option }}
                              </option>
                            </select>
                            <select 
                              v-else-if="getQuestionTypeForRule(rule) === 'boolean'" 
                              v-model="rule.value" 
                              class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                            >
                              <option value="">Selecciona</option>
                              <option value="1">Sí</option>
                              <option value="0">No</option>
                            </select>
                            <input 
                              v-else 
                              v-model="rule.value" 
                              placeholder="Valor" 
                              class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                            />
                          </div>

                          <div class="col-span-1 flex justify-center items-end">
                            <button 
                              @click="removeRuleFromSubgroup(subgroupIndex, ruleIndex)"
                              class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded transition-colors"
                              title="Eliminar regla"
                            >
                              <i class="fas fa-trash text-xs"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <button 
                      @click="addRuleToSubgroup(subgroupIndex)"
                      class="w-full bg-orange-100 text-orange-600 px-3 py-2 rounded text-xs font-medium hover:bg-orange-200 transition-colors border border-orange-300"
                    >
                      <i class="fas fa-plus text-xs mr-1"></i>
                      Añadir regla al subgrupo
                    </button>
                    
                    <button 
                      v-if="subgroup.nestedSubgroupsLogic"
                      @click="addNestedSubgroup(groupIndex, subgroupIndex)"
                      class="w-full bg-blue-100 text-blue-600 px-3 py-2 rounded text-xs font-medium hover:bg-blue-200 transition-colors border border-blue-300 mt-2"
                    >
                      <i class="fas fa-plus text-xs mr-1"></i>
                      Añadir subgrupo anidado
                    </button>
                  </div>
                  <div v-if="subgroup.nestedSubgroupsLogic && subgroup.subgroups && subgroup.subgroups.length > 0" class="mt-4 space-y-3">
                    <div class="text-xs font-medium text-orange-700 mb-2">
                      Subgrupos anidados ({{ subgroup.nestedSubgroupsLogic }}):
                    </div>
                    <div 
                      v-for="(nestedSubgroup, nestedSubgroupIndex) in subgroup.subgroups" 
                      :key="`nested-subgroup-${subgroupIndex}-${nestedSubgroupIndex}`"
                      class="bg-blue-50 rounded-lg border border-blue-200 p-3 ml-4"
                    >
                      <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                          <span class="text-xs font-medium text-blue-800 bg-blue-200 px-2 py-1 rounded-full">
                            G{{ subgroupIndex + 1 }}.{{ nestedSubgroupIndex + 1 }}
                          </span>
                          <input 
                            v-model="nestedSubgroup.description"
                            placeholder="Descripción del subgrupo anidado"
                            class="text-sm font-medium text-blue-900 bg-transparent border-none outline-none flex-1"
                          />
                          <select v-model="nestedSubgroup.groupLogic" class="text-xs text-blue-600 bg-blue-100 border border-blue-300 rounded px-2 py-1">
                            <option value="AND">AND</option>
                            <option value="OR">OR</option>
                          </select>
                        </div>
                        <button 
                          @click="removeNestedSubgroup(groupIndex, subgroupIndex, nestedSubgroupIndex)"
                          class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded-lg transition-colors"
                          title="Eliminar subgrupo anidado"
                        >
                          <i class="fas fa-trash text-sm"></i>
                        </button>
                      </div>
                      
                      <div class="ml-4 space-y-2">
                        <div v-if="nestedSubgroup.rules.length === 0" class="text-xs text-blue-600 italic">
                          Sin reglas configuradas
                        </div>
                        <div v-else class="space-y-2">
                          <div 
                            v-for="(rule, ruleIndex) in nestedSubgroup.rules" 
                            :key="`nested-rule-${ruleIndex}`"
                            class="bg-white rounded-lg border border-blue-200 p-2"
                          >
                            <div class="grid grid-cols-12 gap-2 items-center">
                              <div class="col-span-1 flex justify-center">
                                <span class="text-xs font-medium text-blue-500 bg-blue-100 px-2 py-1 rounded-full">
                                  {{ ruleIndex + 1 }}
                                </span>
                              </div>
                              <div class="col-span-3">
                                <select v-model="rule.question_id" class="w-full px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                                  <option value="">Seleccionar pregunta</option>
                                  <option v-for="question in allQuestions" :key="question.id" :value="question.id">
                                    {{ question.text }}
                                  </option>
                                </select>
                              </div>
                              <div class="col-span-2">
                                <select v-model="rule.operator" class="w-full px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                                  <option value="==">Igual a</option>
                                  <option value="!=">Distinto de</option>
                                  <option value="<">Menor que</option>
                                  <option value="<=">Menor o igual</option>
                                  <option value=">">Mayor que</option>
                                  <option value=">=">Mayor o igual</option>
                                  <option value="between">Entre</option>
                                </select>
                              </div>
                              <div class="col-span-4">
                                <input 
                                  v-model="rule.value" 
                                  type="text" 
                                  placeholder="Valor"
                                  class="w-full px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                />
                              </div>
                              <div class="col-span-2 flex justify-center">
                                <button 
                                  @click="removeRuleFromNestedSubgroup(groupIndex, subgroupIndex, nestedSubgroupIndex, ruleIndex)"
                                  class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded-lg transition-colors"
                                  title="Eliminar regla"
                                >
                                  <i class="fas fa-trash text-xs"></i>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                        
                        <button 
                          @click="addRuleToNestedSubgroup(groupIndex, subgroupIndex)"
                          class="w-full bg-blue-100 text-blue-600 px-3 py-2 rounded text-xs font-medium hover:bg-blue-200 transition-colors border border-blue-300"
                        >
                          <i class="fas fa-plus text-xs mr-1"></i>
                          Añadir regla al subgrupo anidado
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2">
              <button 
                @click="addRuleToGroup"
                  class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors border border-blue-200"
              >
                <i class="fas fa-plus text-xs mr-2"></i>
                  Añadir regla
                </button>
                <button 
                  v-if="newRequirement.groupType === 'mixed'"
                  @click="addSubgroupToGroup"
                  class="bg-orange-50 text-orange-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-100 transition-colors border border-orange-200"
                >
                  <i class="fas fa-layer-group text-xs mr-2"></i>
                  Añadir subgrupo
              </button>
              </div>
            </div>
            
            <!-- Botón añadir grupo -->
            <div class="flex justify-end pt-4">
              <button 
                @click="addRequirement" 
                :disabled="!canAddGroupRequirement"
                class="inline-flex items-center gap-2 bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
              >
                <i class="fas fa-layer-group text-xs"></i>
                Añadir grupo de requisitos
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lista de requisitos -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="font-semibold text-gray-800">Requisitos configurados</h4>
            <p class="text-sm text-gray-600">Todos estos requisitos deben cumplirse para ser beneficiario</p>
          </div>
          <div class="flex gap-2 text-xs flex-wrap">
            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
              Solicitante: {{ requirements.filter(r => r.personType === 'solicitante').length }}
            </span>
            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">
              Unidad: {{ requirements.filter(r => r.personType.includes('unidad')).length }}
            </span>
            <span class="bg-indigo-100 text-indigo-800 px-2 py-1 rounded">
              Familia: {{ requirements.filter(r => r.personType.includes('familiar')).length }}
            </span>
            <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded">
              Conviviente: {{ requirements.filter(r => r.personType === 'conviviente').length }}
            </span>
            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
              Cualquiera: {{ requirements.filter(r => r.personType.includes('any')).length }}
            </span>
          </div>
        </div>
      </div>
      
      <div v-if="requirements.length === 0" class="p-8 text-center text-gray-500">
        <div class="text-4xl mb-2">📋</div>
        <p>No hay requisitos configurados</p>
        <p class="text-sm">Añade el primer requisito usando el formulario de arriba</p>
      </div>
      
      <div v-else class="divide-y divide-gray-200">
        <div 
          v-for="(requirement, index) in requirements" 
          :key="index"
          draggable="true"
          @dragstart="handleDragStart($event, index)"
          @dragend="handleDragEnd"
          @dragover.prevent="handleDragOver($event, index)"
          @drop.prevent="handleDrop($event, index)"
          @dragleave="handleDragLeave"
          :class="[
            'p-4 hover:bg-gray-50 transition-all cursor-move',
            draggedIndex === index ? 'opacity-50 bg-blue-50' : '',
            dragOverIndex === index ? 'bg-blue-100 border-t-4 border-blue-500' : ''
          ]"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 flex-1">
              <div class="flex flex-col gap-1 cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-400">
                <div class="w-1 h-1 bg-current rounded-full"></div>
                <div class="w-1 h-1 bg-current rounded-full"></div>
                <div class="w-1 h-1 bg-current rounded-full"></div>
                <div class="w-1 h-1 bg-current rounded-full"></div>
                <div class="w-1 h-1 bg-current rounded-full"></div>
                <div class="w-1 h-1 bg-current rounded-full"></div>
              </div>

              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <span class="text-sm font-medium text-gray-900">{{ requirement.description }}</span>
                  <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                    {{ requirement.type === 'simple' ? 'Requisito' : 'Grupo' }} {{ index + 1 }}
                  </span>
                  <span 
                    :class="[
                      'text-xs px-2 py-1 rounded font-medium',
                      getPersonTypeClass(requirement.personType)
                    ]"
                  >
                    {{ getPersonTypeText(requirement) }}
                  </span>
                  <span v-if="requirement.type === 'group'" class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">
                    {{ requirement.groupLogic }}
                  </span>
                </div>

                <div v-if="requirement.type === 'simple'" class="text-sm text-gray-600">
                  <span class="font-medium">{{ getQuestionText(requirement.question_id) }}</span>
                  <span class="mx-2">{{ getOperatorText(requirement.operator) }}</span>
                  <span class="font-medium text-green-700">{{ formatValue(requirement) }}</span>
                </div>
                
                <div v-if="requirement.type === 'group'" class="text-sm text-gray-600">
                  <div class="space-y-2">
                    <div v-if="requirement.rules && requirement.rules.length > 0" class="space-y-1">
                      <div v-for="(rule, ruleIndex) in requirement.rules" :key="`rule-${ruleIndex}`" class="flex items-center gap-2">
                        <span class="text-blue-500">•</span>
                        <span class="font-medium">{{ getQuestionText(rule.question_id) }}</span>
                        <span>{{ getOperatorText(rule.operator) }}</span>
                        <span class="font-medium text-green-700">{{ formatRuleValue(rule) }}</span>
                      </div>
                    </div>
                    
                    <div v-if="requirement.subgroups && requirement.subgroups.length > 0" class="space-y-2">
                      <div v-for="(subgroup, subgroupIndex) in requirement.subgroups" :key="`subgroup-${subgroupIndex}`" class="bg-orange-50 rounded-lg p-3 border border-orange-200">
                        <div class="flex items-center gap-2 mb-2">
                          <span class="text-xs font-medium text-orange-800 bg-orange-200 px-2 py-1 rounded-full">
                            G{{ subgroupIndex + 1 }}
                          </span>
                          <span class="font-medium text-orange-900">{{ subgroup.description || 'Subgrupo sin nombre' }}</span>
                          <span class="text-xs text-orange-600">({{ subgroup.groupLogic }})</span>
                          <button 
                            @click="addNestedSubgroup(index, subgroupIndex)"
                            class="text-blue-500 hover:text-blue-700 p-1 ml-2"
                            title="Añadir subgrupo anidado"
                          >
                            <i class="fas fa-plus text-xs"></i>
                          </button>
                          <button 
                            @click="duplicateSubgroup(index, subgroupIndex)"
                            class="text-green-500 hover:text-green-700 p-1 ml-2"
                            title="Duplicar subgrupo"
                          >
                            <i class="fas fa-copy text-xs"></i>
                          </button>
                        </div>
                        <div class="ml-4 space-y-1">
                          <div v-if="subgroup.rules && subgroup.rules.length > 0">
                            <div v-for="(rule, ruleIndex) in subgroup.rules" :key="`subrule-${ruleIndex}`" class="flex items-center gap-2 text-xs">
                              <span class="text-orange-500">•</span>
                              <span class="font-medium">{{ getQuestionText(rule.question_id) }}</span>
                              <span>{{ getOperatorText(rule.operator) }}</span>
                              <span class="font-medium text-green-700">{{ formatRuleValue(rule) }}</span>
                            </div>
                          </div>
                          <div v-else class="text-xs text-orange-600 italic">
                            Sin reglas configuradas
                          </div>

                          <div v-if="subgroup.subgroups && subgroup.subgroups.length > 0" class="mt-3 space-y-2">
                            <div class="text-xs font-medium text-orange-700 mb-2">Subgrupos anidados:</div>
                            <div 
                              v-for="(nestedSubgroup, nestedSubgroupIndex) in subgroup.subgroups" 
                              :key="`nested-subgroup-${subgroupIndex}-${nestedSubgroupIndex}`"
                              class="bg-blue-50 rounded-lg p-2 border border-blue-200 ml-4"
                            >
                              <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-medium text-blue-800 bg-blue-200 px-2 py-1 rounded-full">
                                  G{{ subgroupIndex + 1 }}.{{ nestedSubgroupIndex + 1 }}
                                </span>
                                <span class="font-medium text-blue-900">{{ nestedSubgroup.description || 'Subgrupo anidado sin nombre' }}</span>
                                <span class="text-xs text-blue-600">({{ nestedSubgroup.groupLogic }})</span>
                                <button 
                                  @click="removeNestedSubgroup(index, subgroupIndex, nestedSubgroupIndex)"
                                  class="text-red-500 hover:text-red-700 p-1 ml-2"
                                  title="Eliminar subgrupo anidado"
                                >
                                  <i class="fas fa-trash text-xs"></i>
                                </button>
                              </div>
                              <div class="ml-4 space-y-1">
                                <div v-if="nestedSubgroup.rules && nestedSubgroup.rules.length > 0">
                                  <div v-for="(rule, ruleIndex) in nestedSubgroup.rules" :key="`nested-rule-${ruleIndex}`" class="flex items-center gap-2 text-xs">
                                    <span class="text-blue-500">•</span>
                                    <span class="font-medium">{{ getQuestionText(rule.question_id) }}</span>
                                    <span>{{ getOperatorText(rule.operator) }}</span>
                                    <span class="font-medium text-green-700">{{ formatRuleValue(rule) }}</span>
                                  </div>
                                </div>
                                <div v-else class="text-xs text-blue-600 italic">
                                  Sin reglas configuradas
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div v-if="(!requirement.rules || requirement.rules.length === 0) && (!requirement.subgroups || requirement.subgroups.length === 0)" class="text-gray-500 italic">
                      Grupo sin elementos configurados
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="flex gap-2 ml-4">
              <button 
                @click="editRequirement(index)"
                class="text-blue-500 hover:text-blue-700 p-1"
                title="Editar requisito"
              >
                <i class="fas fa-edit"></i>
              </button>
              <button 
                @click="removeRequirement(index)"
                class="text-red-500 hover:text-red-700 p-1"
                title="Eliminar requisito"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                <i class="fas fa-edit"></i>
              </div>
              <div>
                <h4 class="font-semibold text-gray-800">Editar requisito</h4>
                <p class="text-sm text-gray-600">Modifica los criterios de elegibilidad</p>
              </div>
            </div>
            <button 
              @click="closeEditModal"
              class="text-gray-400 hover:text-gray-600 p-2"
            >
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <div v-if="editingRequirement" class="space-y-6">
            <div class="mb-6">
              <h5 class="text-sm font-medium text-gray-700 mb-4 flex items-center gap-2">
                <i class="fas fa-cog text-blue-500"></i>
                Configuración básica
              </h5>
              
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700">Tipo de requisito</label>
                  <select v-model="editingRequirement.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="simple">Requisito simple (una pregunta)</option>
                    <option value="group">Grupo de requisitos (múltiples preguntas)</option>
                  </select>
                </div>
                
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700">Aplicar a</label>
                  <select v-model="editingRequirement.personType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="solicitante">Solicitante</option>
                    <option value="unidad_convivencia_completa">Unidad de Convivencia (Completa)</option>
                    <option value="unidad_convivencia_sin_solicitante">Unidad de Convivencia (Sin Solicitante)</option>
                    <option value="unidad_familiar_completa">Unidad Familiar (Completa)</option>
                    <option value="unidad_familiar_sin_solicitante">Unidad Familiar (Sin Solicitante)</option>
                    <option value="conviviente">Conviviente Específico</option>
                    <option value="any_conviviente">Cualquier Conviviente</option>
                    <option value="any_familiar">Cualquier Familiar</option>
                    <option value="any_persona_unidad">Cualquier Persona de la Unidad</option>
                  </select>
                  <p class="text-xs text-gray-500 leading-relaxed">
                    {{ getPersonTypeDescription(editingRequirement.personType) }}
                  </p>
                </div>
              </div>
            </div>

            <div v-if="editingRequirement.type === 'simple'">
              <div>
                <h5 class="text-sm font-medium text-gray-700 mb-4 flex items-center gap-2">
                  <i class="fas fa-list-ul text-green-500"></i>
                  Requisito simple
                </h5>
                
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Descripción del requisito</label>
                  <input 
                    v-model="editingRequirement.description" 
                    placeholder="Ej: Tener ingresos superiores a 1000€" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                  />
                </div>
                
                <div v-if="editingRequirement.personType === 'conviviente'" class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de conviviente</label>
                  <select v-model="editingRequirement.convivienteType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="">Selecciona el tipo de conviviente</option>
                    <option value="conyuge">Cónyuge</option>
                    <option value="hijo">Hijo/a</option>
                    <option value="padre">Padre/Madre</option>
                    <option value="otro">Otro familiar</option>
                    <option value="no_familiar">No familiar</option>
                  </select>
                </div>
            
                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Pregunta a evaluar</label>
                  <div class="relative dropdown-container">
                    <div 
                      @click="toggleQuestionSearch('edit-simple')"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors"
                    >
                      <span v-if="editingRequirement.question_id" class="text-gray-900 truncate">
                        {{ getQuestionText(editingRequirement.question_id) }}
                      </span>
                      <span v-else class="text-gray-500">Selecciona una pregunta</span>
                      <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                    </div>
                    
                    <div v-if="showQuestionSearch === 'edit-simple'" class="dropdown-menu">
                      <div class="p-3 border-b border-gray-200 bg-gray-50">
                        <input 
                          v-model="questionSearchTerm"
                          placeholder="Buscar pregunta..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          @input="filterQuestions"
                        />
                      </div>
                      <div class="max-h-48 overflow-y-auto">
                        <div 
                          v-for="question in filteredQuestions" 
                          :key="question.id"
                          @click="selectQuestionForEditing(question.id)"
                          class="px-3 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-b-0 transition-colors"
                        >
                          <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                              <div class="font-medium text-gray-900 truncate">{{ question.text }}</div>
                              <div class="text-xs text-gray-500 mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                                  {{ question.type }}
                                </span>
                              </div>
                            </div>
                            <div v-if="isCurrentQuestion(question.id)" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-2">
                              Actual
                            </div>
                          </div>
                        </div>
                        <div v-if="filteredQuestions.length === 0" class="px-3 py-4 text-sm text-gray-500 text-center">
                          No se encontraron preguntas
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Operador</label>
                    <select 
                      v-model="editingRequirement.operator" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    >
                      <option v-for="op in getAvailableOperatorsForEditing()" :key="op.value" :value="op.value">
                        {{ op.label }}
                      </option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor esperado</label>
                    
                    <div v-if="getQuestionTypeForEditing() === 'date'" class="space-y-3">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de valor</label>
                        <div class="grid grid-cols-2 gap-2">
                          <label class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input 
                              v-model="editingRequirement.valueType" 
                              type="radio" 
                              value="exact" 
                              name="editValueType"
                              class="mr-2"
                            />
                            <span class="text-sm">Fecha exacta</span>
                          </label>
                          <label class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input 
                              v-model="editingRequirement.valueType" 
                              type="radio" 
                              value="age_minimum" 
                              name="editValueType"
                              class="mr-2"
                            />
                            <span class="text-sm">Edad mínima</span>
                          </label>
                          <label class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input 
                              v-model="editingRequirement.valueType" 
                              type="radio" 
                              value="age_maximum" 
                              name="editValueType"
                              class="mr-2"
                            />
                            <span class="text-sm">Edad máxima</span>
                          </label>
                          <label class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input 
                              v-model="editingRequirement.valueType" 
                              type="radio" 
                              value="age_range" 
                              name="editValueType"
                              class="mr-2"
                            />
                            <span class="text-sm">Rango de edad</span>
                          </label>
                        </div>
                      </div>
                      
                      <div v-if="editingRequirement.valueType === 'exact'">
                        <input 
                          v-model="editingRequirement.value" 
                          type="date"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        />
                      </div>
                      
                      <div v-else-if="editingRequirement.valueType === 'between'" class="flex gap-2">
                        <input 
                          v-model="editingRequirement.value" 
                          type="date"
                          placeholder="Fecha inicio"
                          class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        />
                        <span class="flex items-center text-gray-500 text-sm">y</span>
                        <input 
                          v-model="editingRequirement.value2" 
                          type="date"
                          placeholder="Fecha fin"
                          class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        />
                      </div>
                      
                      <div v-else-if="editingRequirement.valueType === 'age_minimum'" class="grid grid-cols-3 gap-2">
                        <input 
                          v-model="editingRequirement.value" 
                          type="number" 
                          min="0" 
                          placeholder="Edad"
                          class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        />
                        <select v-model="editingRequirement.ageUnit" class="col-span-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                          <option value="years">años</option>
                          <option value="months">meses</option>
                          <option value="days">días</option>
                        </select>
                      </div>
                      
                      <div v-else-if="editingRequirement.valueType === 'age_maximum'" class="grid grid-cols-3 gap-2">
                        <input 
                          v-model="editingRequirement.value" 
                          type="number" 
                          min="0" 
                          placeholder="Edad"
                          class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        />
                        <select v-model="editingRequirement.ageUnit" class="col-span-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                          <option value="years">años</option>
                          <option value="months">meses</option>
                          <option value="days">días</option>
                        </select>
                      </div>
                      
                      <div v-else-if="editingRequirement.valueType === 'age_range'" class="space-y-2">
                        <div class="grid grid-cols-3 gap-2">
                          <input 
                            v-model="editingRequirement.value" 
                            type="number" 
                            min="0" 
                            placeholder="Edad mínima"
                            class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                          />
                          <select v-model="editingRequirement.ageUnit" class="col-span-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option value="years">años</option>
                            <option value="months">meses</option>
                            <option value="days">días</option>
                          </select>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                          <input 
                            v-model="editingRequirement.value2" 
                            type="number" 
                            min="0" 
                            placeholder="Edad máxima"
                            class="col-span-2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                          />
                          <div class="col-span-1 px-3 py-2 text-sm text-gray-500 flex items-center">
                            {{ getAgeUnitText(editingRequirement.ageUnit) }}
                          </div>
                        </div>
                      </div>
                      
                      <!-- Texto de ayuda -->
                      <p class="text-xs text-gray-500">
                        <span v-if="editingRequirement.valueType === 'exact'">Se evaluará la fecha exacta</span>
                        <span v-else-if="editingRequirement.valueType === 'age_minimum'">Se evaluará si la edad es mayor a {{ editingRequirement.value || 'X' }} {{ getAgeUnitText(editingRequirement.ageUnit) }}</span>
                        <span v-else-if="editingRequirement.valueType === 'age_maximum'">Se evaluará si la edad es menor a {{ editingRequirement.value || 'X' }} {{ getAgeUnitText(editingRequirement.ageUnit) }}</span>
                        <span v-else-if="editingRequirement.valueType === 'age_range'">Se evaluará si la edad está entre {{ editingRequirement.value || 'X' }} y {{ editingRequirement.value2 || 'Y' }} {{ getAgeUnitText(editingRequirement.ageUnit) }}</span>
                      </p>
                    </div>
                    
                    <input 
                      v-else-if="getQuestionTypeForEditing() === 'text' || getQuestionTypeForEditing() === 'number' || getQuestionTypeForEditing() === 'date'" 
                      v-model="editingRequirement.value" 
                      :type="getQuestionTypeForEditing() === 'number' ? 'number' : getQuestionTypeForEditing() === 'date' ? 'date' : 'text'"
                      :placeholder="getQuestionTypeForEditing() === 'number' ? 'Ingresa un número' : getQuestionTypeForEditing() === 'date' ? 'Selecciona una fecha' : 'Ingresa texto'"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    />
                    
                    <div v-else-if="getQuestionTypeForEditing() === 'select' && isMunicipioQuestionForEditing()" class="relative dropdown-container">
                      <div 
                        @click="toggleValueSearch('edit-simple')"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors text-sm"
                      >
                        <span v-if="editingRequirement.value" class="text-gray-900 truncate">
                          {{ editingRequirement.value }}
                        </span>
                        <span v-else class="text-gray-500">Selecciona una opción</span>
                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                      </div>
                      
                      <div v-if="showValueSearch === 'edit-simple'" class="dropdown-menu">
                        <div class="p-3 border-b border-gray-200 bg-gray-50">
                          <input 
                            v-model="valueSearchTerm"
                            placeholder="Buscar opción..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @input="filterValueOptions"
                          />
                        </div>
                        <div class="max-h-48 overflow-y-auto">
                          <div 
                            v-for="(option, index) in getFilteredValueOptionsForEditing()" 
                            :key="index"
                            @click="selectValueForEditing(option)"
                            class="px-3 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-b-0 transition-colors"
                          >
                            <div class="font-medium text-gray-900">{{ option }}</div>
                          </div>
                          <div v-if="getFilteredValueOptionsForEditing().length === 0" class="px-3 py-4 text-sm text-gray-500 text-center">
                            No se encontraron opciones
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <select 
                      v-else-if="getQuestionTypeForEditing() === 'select'" 
                      v-model="editingRequirement.value" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    >
                      <option value="">Selecciona una opción</option>
                      <option v-for="(option, index) in getQuestionOptionsForEditing()" :key="index" :value="option">
                        {{ option }}
                      </option>
                    </select>
                    
                    <select 
                      v-else-if="getQuestionTypeForEditing() === 'multiple'" 
                      v-model="editingRequirement.value" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    >
                      <option value="">Selecciona una opción</option>
                      <option v-for="(option, index) in getQuestionOptionsForEditing()" :key="index" :value="option">
                        {{ option }}
                      </option>
                    </select>
                    
                    <select 
                      v-else-if="getQuestionTypeForEditing() === 'boolean'" 
                      v-model="editingRequirement.value" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    >
                      <option value="">Selecciona una opción</option>
                      <option value="1">Sí</option>
                      <option value="0">No</option>
                    </select>
                    
                    <input 
                      v-else 
                      v-model="editingRequirement.value" 
                      placeholder="Ingresa el valor" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div v-if="editingRequirement.type === 'group'">
              <div>
                <h5 class="text-sm font-medium text-gray-700 mb-4 flex items-center gap-2">
                  <i class="fas fa-layer-group text-orange-500"></i>
                  Editar grupo de requisitos
                </h5>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción del grupo</label>
                    <input 
                      v-model="editingRequirement.description" 
                      placeholder="Ej: Requisitos de ingresos familiares" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                    />
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lógica del grupo</label>
                    <select v-model="editingRequirement.groupLogic" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                      <option value="AND">TODOS deben cumplirse (AND)</option>
                      <option value="OR">AL MENOS UNO debe cumplirse (OR)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ editingRequirement.groupLogic === 'AND' ? 'Todas las reglas deben cumplirse simultáneamente' : 'Al menos una regla debe cumplirse' }}
                    </p>
                  </div>
                </div>

                <div class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de elementos del grupo</label>
                  <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                      <input 
                        v-model="editingRequirement.groupType" 
                        type="radio" 
                        value="rules" 
                        name="editGroupType"
                        class="mr-3"
                      />
                      <div>
                        <div class="text-sm font-medium">Solo reglas simples</div>
                        <div class="text-xs text-gray-500">Preguntas individuales con operadores</div>
                      </div>
                    </label>
                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                      <input 
                        v-model="editingRequirement.groupType" 
                        type="radio" 
                        value="mixed" 
                        name="editGroupType"
                        class="mr-3"
                      />
                      <div>
                        <div class="text-sm font-medium">Reglas y subgrupos</div>
                        <div class="text-xs text-gray-500">Puede contener reglas simples y otros grupos</div>
                      </div>
                    </label>
                  </div>
                </div>

                <div v-if="editingRequirement.personType === 'conviviente'" class="mb-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de conviviente</label>
                  <select v-model="editingRequirement.convivienteType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="">Selecciona el tipo de conviviente</option>
                    <option value="conyuge">Cónyuge</option>
                    <option value="hijo">Hijo/a</option>
                    <option value="padre">Padre/Madre</option>
                    <option value="otro">Otro familiar</option>
                    <option value="no_familiar">No familiar</option>
                  </select>
                </div>

                <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                  <div class="flex items-center justify-between mb-4">
                    <h6 class="text-sm font-medium text-gray-800 flex items-center gap-2">
                      <i class="fas fa-list text-gray-500"></i>
                      Elementos del grupo
                    </h6>
                    <span class="text-xs text-gray-500">
                      {{ (editingRequirement.rules?.length || 0) + (editingRequirement.subgroups?.length || 0) }} elemento{{ (((editingRequirement.rules?.length || 0) + (editingRequirement.subgroups?.length || 0)) !== 1) ? 's' : '' }}
                    </span>
                  </div>

                  <div v-if="(!editingRequirement.rules || editingRequirement.rules.length === 0) && (!editingRequirement.subgroups || editingRequirement.subgroups.length === 0)" class="text-center py-6 text-gray-500">
                    <i class="fas fa-plus-circle text-2xl mb-2 text-gray-300"></i>
                    <p class="text-sm">Añade al menos un elemento al grupo</p>
                  </div>

                  <div v-else class="space-y-3 mb-4">
                    <div 
                      v-for="(rule, ruleIndex) in editingRequirement.rules" 
                      :key="`edit-rule-${ruleIndex}`"
                      class="bg-white rounded-lg border border-gray-200 p-4"
                    >
                      <div class="grid grid-cols-12 gap-3 items-start">
                        <div class="col-span-1 flex justify-center">
                          <span class="text-xs font-medium text-gray-500 bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                            R{{ ruleIndex + 1 }}
                          </span>
                        </div>

                        <div class="col-span-5 relative dropdown-container">
                          <label class="block text-xs font-medium text-gray-600 mb-1">Pregunta</label>
                          <div 
                            @click="toggleQuestionSearch(`edit-group-${ruleIndex}`)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors min-h-[38px]"
                          >
                            <span v-if="rule.question_id" class="text-gray-900 text-sm line-clamp-2 flex-1 min-w-0">
                              {{ getQuestionText(rule.question_id) }}
                            </span>
                            <span v-else class="text-gray-500 text-sm">Selecciona pregunta</span>
                            <i class="fas fa-chevron-down text-gray-400 text-xs ml-2 flex-shrink-0"></i>
                          </div>
                          
                          <div v-if="showQuestionSearch === `edit-group-${ruleIndex}`" class="dropdown-menu">
                            <div class="p-3 border-b border-gray-200 bg-gray-50">
                              <input 
                                v-model="questionSearchTerm"
                                placeholder="Buscar pregunta..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                @input="filterQuestions"
                              />
                            </div>
                            <div class="max-h-48 overflow-y-auto">
                              <div 
                                v-for="question in filteredQuestions" 
                                :key="question.id"
                                @click="selectQuestionForGroupEditing(question.id, `edit-group-${ruleIndex}`)"
                                class="px-3 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-b-0 transition-colors"
                              >
                                <div class="flex items-center justify-between">
                                  <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900 line-clamp-2">{{ question.text }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                      <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                                        {{ question.type }}
                                      </span>
                                    </div>
                                  </div>
                                  <div v-if="isCurrentQuestion(question.id)" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-2 flex-shrink-0">
                                    Actual
                                  </div>
                                </div>
                              </div>
                              <div v-if="filteredQuestions.length === 0" class="px-3 py-4 text-sm text-gray-500 text-center">
                                No se encontraron preguntas
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-span-2">
                          <label class="block text-xs font-medium text-gray-600 mb-1">Operador</label>
                          <select 
                            v-model="rule.operator" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          >
                            <option v-for="op in getAvailableOperatorsForRule(rule)" :key="op.value" :value="op.value">
                              {{ op.label }}
                            </option>
                          </select>
                        </div>

                        <div class="col-span-3">
                          <label class="block text-xs font-medium text-gray-600 mb-1">Valor</label>

                          <div v-if="getQuestionTypeForRule(rule) === 'date'" class="space-y-2">
                            <div>
                              <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de valor</label>
                              <div class="grid grid-cols-2 gap-1">
                                <label class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                                  <input 
                                    v-model="rule.valueType" 
                                    type="radio" 
                                    value="exact" 
                                    :name="`edit-rule-${ruleIndex}-valueType`"
                                    class="mr-1 text-xs"
                                  />
                                  <span class="text-xs">Exacta</span>
                                </label>
                                <label class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                                  <input 
                                    v-model="rule.valueType" 
                                    type="radio" 
                                    value="age_minimum" 
                                    :name="`edit-rule-${ruleIndex}-valueType`"
                                    class="mr-1 text-xs"
                                  />
                                  <span class="text-xs">Edad mín.</span>
                                </label>
                                <label class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                                  <input 
                                    v-model="rule.valueType" 
                                    type="radio" 
                                    value="age_maximum" 
                                    :name="`edit-rule-${ruleIndex}-valueType`"
                                    class="mr-1 text-xs"
                                  />
                                  <span class="text-xs">Edad máx.</span>
                                </label>
                                <label class="flex items-center p-1 border border-gray-300 rounded cursor-pointer hover:bg-gray-50">
                                  <input 
                                    v-model="rule.valueType" 
                                    type="radio" 
                                    value="age_range" 
                                    :name="`edit-rule-${ruleIndex}-valueType`"
                                    class="mr-1 text-xs"
                                  />
                                  <span class="text-xs">Rango</span>
                                </label>
                              </div>
                            </div>
                            
                            <div v-if="rule.valueType === 'exact'">
                              <input 
                                v-model="rule.value" 
                                type="date"
                                class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                              />
                            </div>
                            
                            <div v-else-if="rule.valueType === 'age_minimum'" class="grid grid-cols-3 gap-1">
                              <input 
                                v-model="rule.value" 
                                type="number" 
                                min="0" 
                                placeholder="Edad"
                                class="col-span-2 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                              />
                              <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                                <option value="years">años</option>
                                <option value="months">meses</option>
                                <option value="days">días</option>
                              </select>
                            </div>
                            
                            <div v-else-if="rule.valueType === 'age_maximum'" class="grid grid-cols-3 gap-1">
                              <input 
                                v-model="rule.value" 
                                type="number" 
                                min="0" 
                                placeholder="Edad"
                                class="col-span-2 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                              />
                              <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                                <option value="years">años</option>
                                <option value="months">meses</option>
                                <option value="days">días</option>
                              </select>
                            </div>
                            
                            <div v-else-if="rule.valueType === 'age_range'" class="space-y-1">
                              <div class="grid grid-cols-3 gap-1">
                                <input 
                                  v-model="rule.value" 
                                  type="number" 
                                  min="0" 
                                  placeholder="Mín."
                                  class="col-span-2 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                />
                                <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                                  <option value="years">años</option>
                                  <option value="months">meses</option>
                                  <option value="days">días</option>
                                </select>
                              </div>
                              <div class="grid grid-cols-3 gap-1">
                                <input 
                                  v-model="rule.value2" 
                                  type="number" 
                                  min="0" 
                                  placeholder="Máx."
                                  class="col-span-2 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                />
                                <div class="col-span-1 px-2 py-1 text-xs text-gray-500 flex items-center">
                                  {{ getAgeUnitText(rule.ageUnit) }}
                                </div>
                              </div>
                            </div>
                          </div>

                          <input 
                            v-else-if="getQuestionTypeForRule(rule) === 'text' || getQuestionTypeForRule(rule) === 'number'" 
                            v-model="rule.value" 
                            :type="getQuestionTypeForRule(rule) === 'number' ? 'number' : 'text'"
                            :placeholder="getQuestionTypeForRule(rule) === 'number' ? 'Número' : 'Texto'"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                          />
                          <div v-else-if="getQuestionTypeForRule(rule) === 'select' && isMunicipioQuestionForRule(rule)" class="relative dropdown-container">
                            <div 
                              @click="toggleValueSearch(`edit-group-${ruleIndex}`)"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors text-sm"
                            >
                              <span v-if="rule.value" class="text-gray-900 truncate">
                                {{ rule.value }}
                              </span>
                              <span v-else class="text-gray-500">Selecciona opción</span>
                              <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                            
                            <div v-if="showValueSearch === `edit-group-${ruleIndex}`" class="dropdown-menu">
                              <div class="p-3 border-b border-gray-200 bg-gray-50">
                                <input 
                                  v-model="valueSearchTerm"
                                  placeholder="Buscar opción..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  @input="filterValueOptions"
                                />
                              </div>
                              <div class="max-h-48 overflow-y-auto">
                                <div 
                                  v-for="(option, index) in getFilteredValueOptionsForRule(rule)" 
                                  :key="index"
                                  @click="selectValueForRule(option, `edit-group-${ruleIndex}`)"
                                  class="px-3 py-3 hover:bg-blue-50 cursor-pointer text-sm border-b border-gray-100 last:border-b-0 transition-colors"
                                >
                                  <div class="font-medium text-gray-900">{{ option }}</div>
                                </div>
                                <div v-if="getFilteredValueOptionsForRule(rule).length === 0" class="px-3 py-4 text-sm text-gray-500 text-center">
                                  No se encontraron opciones
                                </div>
                              </div>
                            </div>
                          </div>
                          
                          <select 
                            v-else-if="getQuestionTypeForRule(rule) === 'select'" 
                            v-model="rule.value" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                          >
                            <option value="">Selecciona opción</option>
                            <option v-for="(option, index) in getDynamicOptionsForRule(rule)" :key="index" :value="option">
                              {{ option }}
                            </option>
                          </select>
                          <select 
                            v-else-if="getQuestionTypeForRule(rule) === 'multiple'" 
                            v-model="rule.value" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                          >
                            <option value="">Selecciona opción</option>
                            <option value="[null]">No seleccionar ninguna opción</option>
                            <option v-for="(option, index) in getDynamicOptionsForRule(rule)" :key="index" :value="option">
                              {{ option }}
                            </option>
                          </select>
                          <select 
                            v-else-if="getQuestionTypeForRule(rule) === 'boolean'" 
                            v-model="rule.value" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                          >
                            <option value="">Selecciona</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                          </select>
                          <input 
                            v-else 
                            v-model="rule.value" 
                            placeholder="Valor" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                          />
                        </div>

                        <div class="col-span-1 flex justify-center items-end">
                          <button 
                            @click="removeRuleFromGroupForEditing(ruleIndex)"
                            class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 rounded-lg transition-colors"
                            title="Eliminar regla"
                          >
                            <i class="fas fa-trash text-sm"></i>
                          </button>
                        </div>
                      </div>
                    </div>

                    <div 
                      v-for="(subgroup, subgroupIndex) in (editingRequirement.subgroups || [])" 
                      :key="`edit-subgroup-${subgroupIndex}`"
                      class="bg-orange-50 rounded-lg border border-orange-200 p-4"
                    >
                      <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                          <span class="text-xs font-medium text-orange-800 bg-orange-200 px-2 py-1 rounded-full">
                            G{{ subgroupIndex + 1 }}
                          </span>
                          <input 
                            v-model="subgroup.description"
                            placeholder="Descripción del subgrupo"
                            class="text-sm font-medium text-orange-900 bg-transparent border-none outline-none flex-1"
                          />
                          <select v-model="subgroup.groupLogic" class="text-xs text-orange-600 bg-orange-100 border border-orange-300 rounded px-2 py-1">
                            <option value="AND">AND</option>
                            <option value="OR">OR</option>
                          </select>
                          <select v-model="subgroup.nestedSubgroupsLogic" class="text-xs text-orange-600 bg-orange-100 border border-orange-300 rounded px-2 py-1 ml-2">
                            <option value="">Sin anidados</option>
                            <option value="AND">Anidados (AND)</option>
                            <option value="OR">Anidados (OR)</option>
                          </select>
                          <button 
                            v-if="subgroup.nestedSubgroupsLogic"
                            @click="addNestedSubgroupForEditing(subgroupIndex)"
                            class="text-blue-500 hover:text-blue-700 p-1 ml-2"
                            title="Añadir subgrupo anidado"
                          >
                            <i class="fas fa-plus text-xs"></i>
                          </button>
                          <button 
                            @click="duplicateSubgroupFromModal(subgroupIndex)"
                            class="text-green-500 hover:text-green-700 p-1 ml-2"
                            title="Duplicar subgrupo"
                          >
                            <i class="fas fa-copy text-xs"></i>
                          </button>
                        </div>
                        <button 
                          @click="removeSubgroupFromGroupForEditing(subgroupIndex)"
                          class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded-lg transition-colors"
                          title="Eliminar subgrupo"
                        >
                          <i class="fas fa-trash text-sm"></i>
                        </button>
                      </div>
                      
                      <div class="ml-4 space-y-3">
                        <div v-if="!subgroup.rules || subgroup.rules.length === 0" class="text-xs text-orange-600 italic">
                          Sin reglas configuradas
                        </div>
                        <div v-else class="space-y-2">
                          <div 
                            v-for="(rule, ruleIndex) in subgroup.rules" 
                            :key="`edit-subrule-${subgroupIndex}-${ruleIndex}`"
                            class="bg-white rounded-lg border border-orange-200 p-3"
                          >
                            <div class="grid grid-cols-12 gap-2 items-start">
                              <div class="col-span-1 flex justify-center">
                                <span class="text-xs font-medium text-orange-500 bg-orange-100 px-2 py-1 rounded-full">
                                  {{ ruleIndex + 1 }}
                                </span>
                              </div>

                              <div class="col-span-4 relative dropdown-container">
                                <label class="block text-xs font-medium text-orange-600 mb-1">Pregunta</label>
                                <div 
                                  @click="toggleQuestionSearch(`edit-subgroup-${subgroupIndex}-${ruleIndex}`)"
                                  class="w-full px-2 py-1 border border-orange-300 rounded cursor-pointer bg-white flex items-center justify-between hover:border-orange-400 transition-colors min-h-[32px]"
                                >
                                  <span v-if="rule.question_id" class="text-orange-900 text-xs line-clamp-1 flex-1 min-w-0">
                                    {{ getQuestionText(rule.question_id) }}
                                  </span>
                                  <span v-else class="text-orange-500 text-xs">Selecciona</span>
                                  <i class="fas fa-chevron-down text-orange-400 text-xs ml-1 flex-shrink-0"></i>
                                </div>
                                
                                <div v-if="showQuestionSearch === `edit-subgroup-${subgroupIndex}-${ruleIndex}`" class="dropdown-menu">
                                  <div class="p-2 border-b border-gray-200 bg-gray-50">
                                    <input 
                                      v-model="questionSearchTerm"
                                      placeholder="Buscar pregunta..."
                                      class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                      @input="filterQuestions"
                                    />
                                  </div>
                                  <div class="max-h-32 overflow-y-auto">
                                    <div 
                                      v-for="question in filteredQuestions" 
                                      :key="question.id"
                                      @click="selectQuestionForGroupEditing(question.id, `edit-subgroup-${subgroupIndex}-${ruleIndex}`)"
                                      class="px-2 py-2 hover:bg-orange-50 cursor-pointer text-xs border-b border-gray-100 last:border-b-0 transition-colors"
                                    >
                                      <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                          <div class="font-medium text-gray-900 line-clamp-1">{{ question.text }}</div>
                                          <div class="text-xs text-gray-500 mt-1">
                                            <span class="inline-flex items-center px-1 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                              {{ question.type }}
                                            </span>
                                          </div>
                                        </div>
                                        <div v-if="isCurrentQuestion(question.id)" class="text-xs bg-orange-100 text-orange-800 px-1 py-0.5 rounded-full ml-1 flex-shrink-0">
                                          Actual
                                        </div>
                                      </div>
                                    </div>
                                    <div v-if="filteredQuestions.length === 0" class="px-2 py-2 text-xs text-gray-500 text-center">
                                      No se encontraron preguntas
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <div class="col-span-2">
                                <label class="block text-xs font-medium text-orange-600 mb-1">Operador</label>
                                <select 
                                  v-model="rule.operator" 
                                  class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                >
                                  <option v-for="op in getAvailableOperatorsForRule(rule)" :key="op.value" :value="op.value">
                                    {{ op.label }}
                                  </option>
                                </select>
                              </div>

                              <div class="col-span-4">
                                <label class="block text-xs font-medium text-orange-600 mb-1">Valor</label>

                                <div v-if="getQuestionTypeForRule(rule) === 'date'" class="space-y-1">
                                  <div>
                                    <label class="block text-xs font-medium text-orange-600 mb-1">Tipo</label>
                                    <div class="grid grid-cols-2 gap-1">
                                      <label class="flex items-center p-1 border border-orange-300 rounded cursor-pointer hover:bg-orange-50">
                                        <input 
                                          v-model="rule.valueType" 
                                          type="radio" 
                                          value="exact" 
                                          :name="`edit-subrule-${subgroupIndex}-${ruleIndex}-valueType`"
                                          class="mr-1 text-xs"
                                        />
                                        <span class="text-xs">Exacta</span>
                                      </label>
                                      <label class="flex items-center p-1 border border-orange-300 rounded cursor-pointer hover:bg-orange-50">
                                        <input 
                                          v-model="rule.valueType" 
                                          type="radio" 
                                          value="age_minimum" 
                                          :name="`edit-subrule-${subgroupIndex}-${ruleIndex}-valueType`"
                                          class="mr-1 text-xs"
                                        />
                                        <span class="text-xs">Edad mín.</span>
                                      </label>
                                      <label class="flex items-center p-1 border border-orange-300 rounded cursor-pointer hover:bg-orange-50">
                                        <input 
                                          v-model="rule.valueType" 
                                          type="radio" 
                                          value="age_maximum" 
                                          :name="`edit-subrule-${subgroupIndex}-${ruleIndex}-valueType`"
                                          class="mr-1 text-xs"
                                        />
                                        <span class="text-xs">Edad máx.</span>
                                      </label>
                                      <label class="flex items-center p-1 border border-orange-300 rounded cursor-pointer hover:bg-orange-50">
                                        <input 
                                          v-model="rule.valueType" 
                                          type="radio" 
                                          value="age_range" 
                                          :name="`edit-subrule-${subgroupIndex}-${ruleIndex}-valueType`"
                                          class="mr-1 text-xs"
                                        />
                                        <span class="text-xs">Rango</span>
                                      </label>
                                    </div>
                                  </div>
                                  
                                  <div v-if="rule.valueType === 'exact'">
                                    <input 
                                      v-model="rule.value" 
                                      type="date"
                                      class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                    />
                                  </div>
                                  
                                  <div v-else-if="rule.valueType === 'age_minimum'" class="grid grid-cols-3 gap-1">
                                    <input 
                                      v-model="rule.value" 
                                      type="number" 
                                      min="0" 
                                      placeholder="Edad"
                                      class="col-span-2 px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                    />
                                    <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent">
                                      <option value="years">años</option>
                                      <option value="months">meses</option>
                                      <option value="days">días</option>
                                    </select>
                                  </div>
                                  
                                  <div v-else-if="rule.valueType === 'age_maximum'" class="grid grid-cols-3 gap-1">
                                    <input 
                                      v-model="rule.value" 
                                      type="number" 
                                      min="0" 
                                      placeholder="Edad"
                                      class="col-span-2 px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                    />
                                    <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent">
                                      <option value="years">años</option>
                                      <option value="months">meses</option>
                                      <option value="days">días</option>
                                    </select>
                                  </div>
                                  
                                  <div v-else-if="rule.valueType === 'age_range'" class="space-y-1">
                                    <div class="grid grid-cols-3 gap-1">
                                      <input 
                                        v-model="rule.value" 
                                        type="number" 
                                        min="0" 
                                        placeholder="Mín."
                                        class="col-span-2 px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                      />
                                      <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent">
                                        <option value="years">años</option>
                                        <option value="months">meses</option>
                                        <option value="days">días</option>
                                      </select>
                                    </div>
                                    <div class="grid grid-cols-3 gap-1">
                                      <input 
                                        v-model="rule.value2" 
                                        type="number" 
                                        min="0" 
                                        placeholder="Máx."
                                        class="col-span-2 px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                      />
                                      <div class="col-span-1 px-2 py-1 text-xs text-orange-500 flex items-center">
                                        {{ getAgeUnitText(rule.ageUnit) }}
                                      </div>
                                    </div>
                                  </div>
                                </div>

                                <input 
                                  v-else-if="getQuestionTypeForRule(rule) === 'text' || getQuestionTypeForRule(rule) === 'number'" 
                                  v-model="rule.value" 
                                  :type="getQuestionTypeForRule(rule) === 'number' ? 'number' : 'text'"
                                  :placeholder="getQuestionTypeForRule(rule) === 'number' ? 'Número' : 'Texto'"
                                  class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                />
                                <div v-else-if="getQuestionTypeForRule(rule) === 'select' && isMunicipioQuestionForRule(rule)" class="relative dropdown-container">
                                  <div 
                                    @click="toggleValueSearch(`edit-subgroup-${subgroupIndex}-${ruleIndex}`)"
                                    class="w-full px-2 py-1 border border-orange-300 rounded cursor-pointer bg-white flex items-center justify-between hover:border-orange-400 transition-colors text-xs"
                                  >
                                    <span v-if="rule.value" class="text-orange-900 truncate">
                                      {{ rule.value }}
                                    </span>
                                    <span v-else class="text-orange-500">Selecciona</span>
                                    <i class="fas fa-chevron-down text-orange-400 text-xs"></i>
                                  </div>
                                  
                                  <div v-if="showValueSearch === `edit-subgroup-${subgroupIndex}-${ruleIndex}`" class="dropdown-menu">
                                    <div class="p-2 border-b border-gray-200 bg-gray-50">
                                      <input 
                                        v-model="valueSearchTerm"
                                        placeholder="Buscar opción..."
                                        class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                        @input="filterValueOptions"
                                      />
                                    </div>
                                    <div class="max-h-32 overflow-y-auto">
                                      <div 
                                        v-for="(option, index) in getFilteredValueOptionsForRule(rule)" 
                                        :key="index"
                                        @click="selectValueForRule(option, `edit-subgroup-${subgroupIndex}-${ruleIndex}`)"
                                        class="px-2 py-2 hover:bg-orange-50 cursor-pointer text-xs border-b border-gray-100 last:border-b-0 transition-colors"
                                      >
                                        <div class="font-medium text-gray-900">{{ option }}</div>
                                      </div>
                                      <div v-if="getFilteredValueOptionsForRule(rule).length === 0" class="px-2 py-2 text-xs text-gray-500 text-center">
                                        No se encontraron opciones
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                
                                <select 
                                  v-else-if="getQuestionTypeForRule(rule) === 'select'" 
                                  v-model="rule.value" 
                                  class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                >
                                  <option value="">Selecciona</option>
                                  <option v-for="(option, index) in getDynamicOptionsForRule(rule)" :key="index" :value="option">
                                    {{ option }}
                                  </option>
                                </select>
                                <select 
                                  v-else-if="getQuestionTypeForRule(rule) === 'multiple'" 
                                  v-model="rule.value" 
                                  class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                >
                                  <option value="">Selecciona opción</option>
                                  <option value="[null]">No seleccionar ninguna opción</option>
                                  <option v-for="(option, index) in getDynamicOptionsForRule(rule)" :key="index" :value="option">
                                    {{ option }}
                                  </option>
                                </select>
                                <select 
                                  v-else-if="getQuestionTypeForRule(rule) === 'boolean'" 
                                  v-model="rule.value" 
                                  class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                >
                                  <option value="">Selecciona</option>
                                  <option value="1">Sí</option>
                                  <option value="0">No</option>
                                </select>
                                <input 
                                  v-else 
                                  v-model="rule.value" 
                                  placeholder="Valor" 
                                  class="w-full px-2 py-1 border border-orange-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-transparent"
                                />
                              </div>
                              
                              <div class="col-span-1 flex justify-center items-end">
                                <button 
                                  @click="removeRuleFromSubgroupForEditing(subgroupIndex, ruleIndex)"
                                  class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded transition-colors"
                                  title="Eliminar regla"
                                >
                                  <i class="fas fa-trash text-xs"></i>
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>

                        <button 
                          @click="addRuleToSubgroupForEditing(subgroupIndex)"
                          class="w-full bg-orange-100 text-orange-600 px-3 py-2 rounded text-xs font-medium hover:bg-orange-200 transition-colors border border-orange-300"
                        >
                          <i class="fas fa-plus text-xs mr-1"></i>
                          Añadir regla al subgrupo
                        </button>
                        <div v-if="subgroup.nestedSubgroupsLogic && subgroup.subgroups && subgroup.subgroups.length > 0" class="mt-4 space-y-3">
                          <div class="text-xs font-medium text-orange-700 mb-2">
                            Subgrupos anidados ({{ subgroup.nestedSubgroupsLogic }}):
                          </div>
                          <div 
                            v-for="(nestedSubgroup, nestedSubgroupIndex) in subgroup.subgroups" 
                            :key="`nested-subgroup-${subgroupIndex}-${nestedSubgroupIndex}`"
                            class="bg-blue-50 rounded-lg border border-blue-200 p-3 ml-4"
                          >
                            <div class="flex items-center justify-between mb-3">
                              <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-blue-800 bg-blue-200 px-2 py-1 rounded-full">
                                  G{{ subgroupIndex + 1 }}.{{ nestedSubgroupIndex + 1 }}
                                </span>
                                <input 
                                  v-model="nestedSubgroup.description"
                                  placeholder="Descripción del subgrupo anidado"
                                  class="text-sm font-medium text-blue-900 bg-transparent border-none outline-none flex-1"
                                />
                                <select v-model="nestedSubgroup.groupLogic" class="text-xs text-blue-600 bg-blue-100 border border-blue-300 rounded px-2 py-1">
                                  <option value="OR">OR</option>
                                  <option value="AND">AND</option>
                                </select>
                              </div>
                              <button 
                                @click="removeNestedSubgroupForEditing(subgroupIndex, nestedSubgroupIndex)"
                                class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded-lg transition-colors"
                                title="Eliminar subgrupo anidado"
                              >
                                <i class="fas fa-trash text-sm"></i>
                              </button>
                            </div>
                            
                            <div class="ml-4 space-y-3">
                              <div v-if="!nestedSubgroup.rules || nestedSubgroup.rules.length === 0" class="text-xs text-blue-600 italic">
                                Sin reglas configuradas
                              </div>
                              <div v-else class="space-y-2">
                                <div 
                                  v-for="(rule, ruleIndex) in nestedSubgroup.rules" 
                                  :key="`nested-rule-${subgroupIndex}-${nestedSubgroupIndex}-${ruleIndex}`"
                                  class="bg-white rounded-lg border border-blue-200 p-3"
                                >
                                  <div class="grid grid-cols-12 gap-2 items-start">
                                    <div class="col-span-1 flex justify-center">
                                      <span class="text-xs font-medium text-blue-500 bg-blue-100 px-2 py-1 rounded-full">
                                        {{ ruleIndex + 1 }}
                                      </span>
                                    </div>

                                    <div class="col-span-4 relative dropdown-container">
                                      <label class="block text-xs font-medium text-blue-600 mb-1">Pregunta</label>
                                      <div 
                                        @click="toggleQuestionSearch(`edit-nested-subgroup-${subgroupIndex}-${nestedSubgroupIndex}-${ruleIndex}`)"
                                        class="w-full px-2 py-1 border border-blue-300 rounded cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors min-h-[32px]"
                                      >
                                        <span v-if="rule.question_id" class="text-blue-900 text-xs line-clamp-1 flex-1 min-w-0">
                                          {{ getQuestionText(rule.question_id) }}
                                        </span>
                                        <span v-else class="text-blue-500 text-xs">Selecciona</span>
                                        <i class="fas fa-chevron-down text-blue-400 text-xs ml-1 flex-shrink-0"></i>
                                      </div>
                                      
                                      <div v-if="showQuestionSearch === `edit-nested-subgroup-${subgroupIndex}-${nestedSubgroupIndex}-${ruleIndex}`" class="dropdown-menu">
                                        <div class="p-2 border-b border-gray-200 bg-gray-50">
                                          <input 
                                            v-model="questionSearchTerm"
                                            placeholder="Buscar pregunta..."
                                            class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                            @input="filterQuestions"
                                          />
                                        </div>
                                        <div class="max-h-32 overflow-y-auto">
                                          <div 
                                            v-for="question in filteredQuestions" 
                                            :key="question.id"
                                            @click="selectQuestionForNestedSubgroupEditing(question.id, subgroupIndex, nestedSubgroupIndex, ruleIndex)"
                                            class="px-2 py-2 hover:bg-blue-50 cursor-pointer text-xs border-b border-gray-100 last:border-b-0 transition-colors"
                                          >
                                            <div class="flex items-center justify-between">
                                              <div class="flex-1 min-w-0">
                                                <div class="font-medium text-gray-900 line-clamp-1">{{ question.text }}</div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                  <span class="inline-flex items-center px-1 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                                    {{ question.type }}
                                                  </span>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div v-if="filteredQuestions.length === 0" class="px-2 py-2 text-xs text-gray-500 text-center">
                                            No se encontraron preguntas
                                          </div>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="col-span-2">
                                      <label class="block text-xs font-medium text-blue-600 mb-1">Operador</label>
                                      <select 
                                        v-model="rule.operator" 
                                        class="w-full px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                      >
                                        <option v-for="op in getAvailableOperatorsForRule(rule, true)" :key="op.value" :value="op.value">
                                          {{ op.label }}
                                        </option>
                                      </select>
                                    </div>

                                    <div class="col-span-4">
                                      <label class="block text-xs font-medium text-blue-600 mb-1">Valor</label>
                                      <div v-if="getQuestionTypeForRule(rule) === 'date' && rule.valueType" class="space-y-2">
                                        <div v-if="rule.valueType === 'age_minimum' || rule.valueType === 'age_maximum'" class="grid grid-cols-2 gap-1">
                                          <input 
                                            v-model="rule.value" 
                                            type="number" 
                                            min="0" 
                                            :placeholder="rule.valueType === 'age_minimum' ? 'Mín.' : 'Máx.'"
                                            class="col-span-1 px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                          />
                                          <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                                            <option value="years">años</option>
                                            <option value="months">meses</option>
                                            <option value="days">días</option>
                                          </select>
                                        </div>
                                        <div v-if="rule.valueType === 'age_range'" class="space-y-1">
                                          <div class="grid grid-cols-2 gap-1">
                                            <input 
                                              v-model="rule.value" 
                                              type="number" 
                                              min="0" 
                                              placeholder="Mín."
                                              class="col-span-1 px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                            />
                                            <select v-model="rule.ageUnit" class="col-span-1 px-1 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                                              <option value="years">años</option>
                                              <option value="months">meses</option>
                                              <option value="days">días</option>
                                            </select>
                                          </div>
                                          <div class="grid grid-cols-2 gap-1">
                                            <input 
                                              v-model="rule.value2" 
                                              type="number" 
                                              min="0" 
                                              placeholder="Máx."
                                              class="col-span-1 px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                            />
                                            <div class="col-span-1 px-2 py-1 text-xs text-gray-500 flex items-center">
                                              {{ getAgeUnitText(rule.ageUnit) }}
                                            </div>
                                          </div>
                                        </div>
                                      </div>

                                      <!-- Inputs de texto y número -->
                                      <input 
                                        v-else-if="getQuestionTypeForRule(rule) === 'text' || getQuestionTypeForRule(rule) === 'number'" 
                                        v-model="rule.value" 
                                        :type="getQuestionTypeForRule(rule) === 'number' ? 'number' : 'text'"
                                        :placeholder="getQuestionTypeForRule(rule) === 'number' ? 'Número' : 'Texto'"
                                        class="w-full px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                      />
                                      
                                      <!-- Selector de municipio con búsqueda -->
                                      <div v-else-if="getQuestionTypeForRule(rule) === 'select' && isMunicipioQuestionForRule(rule)" class="relative dropdown-container">
                                        <div 
                                          @click="toggleValueSearch(`edit-nested-subgroup-${subgroupIndex}-${nestedSubgroupIndex}-${ruleIndex}`)"
                                          class="w-full px-2 py-1 border border-blue-300 rounded cursor-pointer bg-white flex items-center justify-between hover:border-blue-400 transition-colors text-xs"
                                        >
                                          <span v-if="rule.value" class="text-blue-900 truncate">
                                            {{ rule.value }}
                                          </span>
                                          <span v-else class="text-blue-500">Selecciona opción</span>
                                          <i class="fas fa-chevron-down text-blue-400 text-xs"></i>
                                        </div>
                                        
                                        <div v-if="showValueSearch === `edit-nested-subgroup-${subgroupIndex}-${nestedSubgroupIndex}-${ruleIndex}`" class="dropdown-menu">
                                          <div class="p-2 border-b border-gray-200 bg-gray-50">
                                            <input 
                                              v-model="valueSearchTerm"
                                              placeholder="Buscar opción..."
                                              class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                              @input="filterValueOptions"
                                            />
                                          </div>
                                          <div class="max-h-32 overflow-y-auto">
                                            <div 
                                              v-for="(option, index) in getFilteredValueOptionsForRule(rule)" 
                                              :key="index"
                                              @click="selectValueForRule(option, `edit-nested-subgroup-${subgroupIndex}-${nestedSubgroupIndex}-${ruleIndex}`)"
                                              class="px-2 py-2 hover:bg-blue-50 cursor-pointer text-xs border-b border-gray-100 last:border-b-0 transition-colors"
                                            >
                                              <div class="font-medium text-gray-900">{{ option }}</div>
                                            </div>
                                            <div v-if="getFilteredValueOptionsForRule(rule).length === 0" class="px-2 py-2 text-xs text-gray-500 text-center">
                                              No se encontraron opciones
                                            </div>
                                          </div>
                                        </div>
                                      </div>

                                      <select 
                                        v-else-if="getQuestionTypeForRule(rule) === 'select'" 
                                        v-model="rule.value" 
                                        class="w-full px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                      >
                                        <option value="">Selecciona opción</option>
                                        <option v-for="(option, index) in getDynamicOptionsForRule(rule)" :key="index" :value="option">
                                          {{ option }}
                                        </option>
                                      </select>

                                      <select 
                                        v-else-if="getQuestionTypeForRule(rule) === 'multiple'" 
                                        v-model="rule.value" 
                                        class="w-full px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                      >
                                        <option value="">Selecciona opción</option>
                                        <option value="[null]">No seleccionar ninguna opción</option>
                                        <option v-for="(option, index) in getDynamicOptionsForRule(rule)" :key="index" :value="option">
                                          {{ option }}
                                        </option>
                                      </select>

                                      <select 
                                        v-else-if="getQuestionTypeForRule(rule) === 'boolean'" 
                                        v-model="rule.value" 
                                        class="w-full px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                      >
                                        <option value="">Selecciona</option>
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                      </select>

                                      <input 
                                        v-else 
                                        v-model="rule.value" 
                                        placeholder="Valor" 
                                        class="w-full px-2 py-1 border border-blue-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent"
                                      />
                                    </div>

                                    <div class="col-span-1 flex justify-center">
                                      <button 
                                        @click="removeRuleFromNestedSubgroupForEditing(subgroupIndex, nestedSubgroupIndex, ruleIndex)"
                                        class="text-red-500 hover:text-red-700 p-1 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Eliminar regla"
                                      >
                                        <i class="fas fa-trash text-xs"></i>
                                      </button>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <button 
                                @click="addRuleToNestedSubgroupForEditing(subgroupIndex, nestedSubgroupIndex)"
                                class="w-full bg-blue-100 text-blue-600 px-3 py-2 rounded text-xs font-medium hover:bg-blue-200 transition-colors border border-blue-300"
                              >
                                <i class="fas fa-plus text-xs mr-1"></i>
                                Añadir regla al subgrupo anidado
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-2">
                    <button 
                      @click="addRuleToGroupForEditing"
                      class="bg-blue-50 text-blue-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors border border-blue-200"
                    >
                      <i class="fas fa-plus text-xs mr-2"></i>
                      Añadir regla
                    </button>
                    <button 
                      v-if="editingRequirement.groupType === 'mixed'"
                      @click="addSubgroupToGroupForEditing"
                      class="bg-orange-50 text-orange-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-100 transition-colors border border-orange-200"
                    >
                      <i class="fas fa-layer-group text-xs mr-2"></i>
                      Añadir subgrupo
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Botones de acción -->
          <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
            <button 
              @click="closeEditModal"
              class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
            <button 
              @click="saveEditedRequirement"
              :disabled="editingRequirement?.type === 'simple' ? !canEditSimpleRequirement : !canEditGroupRequirement"
              class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
            >
              Guardar cambios
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toasts -->
    <transition name="fade">
      <div v-if="toast.show" class="fixed bottom-6 right-6 z-50">
        <div :class="['px-4 py-3 rounded shadow-lg text-white', toast.type === 'success' ? 'bg-green-500' : 'bg-red-500']">
          {{ toast.message }}
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  questions: {
    type: Array,
    default: () => []
  },
  allQuestions: {
    type: Array,
    default: () => []
  },
  csrf: {
    type: String,
    required: true
  },
  data: {
    type: Object,
    default: () => ({})
  },
  wizard: {
    type: Object,
    default: () => ({})
  },
  eligibilityLogic: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:requirements', 'save-requirements']);

const requirements = ref([]);
const isSyncingFromProps = ref(false);
const toast = reactive({ show: false, message: '', type: 'success' });
const allQuestions = computed(() => props.allQuestions); // Todas las preguntas del sistema
const filteredQuestions = ref([]); // Preguntas filtradas para búsqueda
const questionSearchTerm = ref(''); // Término de búsqueda
const showQuestionSearch = ref(null); // Qué dropdown está abierto
const dynamicOptions = ref([]);
const showEditModal = ref(false);
const editingRequirement = ref(null);
const editingIndex = ref(-1);

const showValueSearch = ref(null);
const valueSearchTerm = ref('');
const filteredValueOptions = ref([]);

const newRequirement = reactive({
  type: 'simple',
  personType: 'solicitante',
  convivienteType: null,
  description: '',
  question_id: '',
  operator: '==',
  value: '',
  value2: '', // Para rangos de fechas
  valueType: 'exact',
  ageUnit: 'years',
  groupLogic: 'AND',
  groupType: 'rules',
  rules: [],
  subgroups: [],
});

// Computed properties
const canAddSimpleRequirement = computed(() => {
  const hasBasicFields = newRequirement.description.trim() && 
                        newRequirement.question_id && 
                        newRequirement.operator;

  if (newRequirement.personType === 'conviviente' && !newRequirement.convivienteType) {
    return false;
  }
  
  
  // Para fechas con rango, necesitamos ambos valores
  if (getQuestionType() === 'date' && newRequirement.operator === 'between') {
    return hasBasicFields && newRequirement.value !== '' && newRequirement.value2 !== '';
  }
  
  // Para otros casos, solo necesitamos value
  return hasBasicFields && newRequirement.value !== '';
});

const canAddGroupRequirement = computed(() => {
  if (newRequirement.personType === 'conviviente' && !newRequirement.convivienteType) {
    return false;
  }
  
  const hasDescription = newRequirement.description.trim();
  const hasElements = newRequirement.rules.length > 0 || newRequirement.subgroups.length > 0;

  const rulesValid = newRequirement.rules.every(rule => {
    const hasBasicFields = rule.question_id && rule.operator;
    
    if (getQuestionTypeForRule(rule) === 'date' && rule.valueType) {
      if (rule.valueType === 'age_range') {
        return hasBasicFields && rule.value !== '' && rule.value2 !== '';
      }
      return hasBasicFields && rule.value !== '';
    }
    
    return hasBasicFields && rule.value !== '';
  });

  const subgroupsValid = newRequirement.subgroups.every(subgroup => 
    subgroup.description?.trim() && 
    subgroup.rules?.length > 0 &&
    subgroup.rules.every(rule => {
      const hasBasicFields = rule.question_id && rule.operator;
      
      if (getQuestionTypeForRule(rule) === 'date' && rule.valueType) {
        if (rule.valueType === 'age_range') {
          return hasBasicFields && rule.value !== '' && rule.value2 !== '';
        }
        return hasBasicFields && rule.value !== '';
      }
      
      return hasBasicFields && rule.value !== '';
    })
  );
  
  return hasDescription && hasElements && rulesValid && subgroupsValid;
});

const canEditSimpleRequirement = computed(() => {
  if (!editingRequirement.value) return false;
  
  const hasBasicFields = editingRequirement.value.description?.trim() && 
                        editingRequirement.value.question_id && 
                        editingRequirement.value.operator;

  if (editingRequirement.value.personType === 'conviviente' && !editingRequirement.value.convivienteType) {
    return false;
  }
  
  if (getQuestionTypeForEditing() === 'date' && editingRequirement.value.operator === 'between') {
    return hasBasicFields && editingRequirement.value.value !== '' && editingRequirement.value.value2 !== '';
  }
  
  return hasBasicFields && editingRequirement.value.value !== '';
});

const canEditGroupRequirement = computed(() => {
  if (!editingRequirement.value) return false;
  
  if (editingRequirement.value.personType === 'conviviente' && !editingRequirement.value.convivienteType) {
    return false;
  }
  
  const hasDescription = editingRequirement.value.description?.trim();
  const hasElements = (editingRequirement.value.rules?.length > 0) || (editingRequirement.value.subgroups?.length > 0);

  const rulesValid = !editingRequirement.value.rules || editingRequirement.value.rules.every(rule => {
    const hasBasicFields = rule.question_id && rule.operator;
    
    if (getQuestionTypeForRule(rule) === 'date' && rule.valueType) {
      if (rule.valueType === 'age_range') {
        return hasBasicFields && rule.value !== '' && rule.value2 !== '';
      }
      return hasBasicFields && rule.value !== '';
    }
    
    return hasBasicFields && rule.value !== '';
  });

  const subgroupsValid = !editingRequirement.value.subgroups || editingRequirement.value.subgroups.every(subgroup => 
    subgroup.description?.trim() && 
    subgroup.rules?.length > 0 &&
    subgroup.rules.every(rule => {
      const hasBasicFields = rule.question_id && rule.operator;
      
      if (getQuestionTypeForRule(rule) === 'date' && rule.valueType) {
        if (rule.valueType === 'age_range') {
          return hasBasicFields && rule.value !== '' && rule.value2 !== '';
        }
        return hasBasicFields && rule.value !== '';
      }
      
      return hasBasicFields && rule.value !== '';
    })
  );
  
  return hasDescription && hasElements && rulesValid && subgroupsValid;
});

const jsonPreview = computed(() => {
  if (requirements.value.length === 0) {
    return '{}';
  }
  
  const jsonData = {
    descripcion: 'Requisitos de elegibilidad',
    json_regla: {
      condition: 'AND',
      rules: requirements.value.map(req => {
        if (req.type === 'simple') {
          const rule = {
            type: 'simple',
            personType: req.personType || 'solicitante', // Incluir tipo de persona
            convivienteType: req.convivienteType || null,
            question_id: req.question_id,
            operator: req.operator,
            value: req.value,
            value2: req.value2,
            valueType: req.valueType || 'exact',
            ageUnit: req.ageUnit || 'years'
          };
          
          return rule;
        } else {
          return {
            type: 'group',
            personType: req.personType || 'solicitante', // Incluir tipo de persona
            convivienteType: req.convivienteType || null,
            condition: req.groupLogic,
            groupType: req.groupType || 'rules',
            rules: req.rules?.map(rule => ({
              question_id: rule.question_id,
              operator: rule.operator,
              value: rule.value
            })) || [],
            subgroups: req.subgroups?.map(subgroup => ({
              description: subgroup.description,
              condition: subgroup.groupLogic,
              rules: subgroup.rules?.map(rule => ({
                question_id: rule.question_id,
                operator: rule.operator,
                value: rule.value
              })) || []
            })) || []
          };
        }
      })
    }
  };
  
  return JSON.stringify(jsonData, null, 2);
});

// Methods
function showToast(message, type = 'success') {
  toast.message = message;
  toast.type = type;
  toast.show = true;
  setTimeout(() => (toast.show = false), 3000);
}

function loadAllQuestions() {
  // Organizar preguntas: primero las del cuestionario actual, luego todas las demás
  const currentQuestionIds = props.questions.map(q => q.id);
  const currentQuestions = props.allQuestions.filter(q => currentQuestionIds.includes(q.id));
  const otherQuestions = props.allQuestions.filter(q => !currentQuestionIds.includes(q.id));
  
  // Añadir información sobre el tipo de persona para cada pregunta
  const questionsWithPersonType = [...currentQuestions, ...otherQuestions].map(question => ({
    ...question,
    personType: getQuestionPersonType(question)
  }));
  
  filteredQuestions.value = questionsWithPersonType;
}

function getQuestionPersonType(question) {
  // Las preguntas pueden ser usadas tanto para solicitante como para convivencia
  // El personType se determina por el contexto de uso, no por la pregunta
  return 'both'; // Todas las preguntas pueden usarse en ambos contextos
}

function getPersonTypeDescription(personType) {
  const descriptions = {
    'solicitante': 'Se evaluará solo en las respuestas del solicitante',
    'unidad_convivencia_completa': 'Se evaluará en TODAS las respuestas de la unidad de convivencia (incluyendo solicitante)',
    'unidad_convivencia_sin_solicitante': 'Se evaluará en las respuestas de los convivientes (sin solicitante)',
    'unidad_familiar_completa': 'Se evaluará en las respuestas de la unidad familiar (conyuge, hijo, padre, otro)',
    'unidad_familiar_sin_solicitante': 'Se evaluará en las respuestas de la unidad familiar (sin solicitante)',
    'conviviente': 'Se evaluará en un conviviente específico (selecciona el tipo)',
    'any_conviviente': 'Se evaluará en cualquier conviviente que cumpla la condición',
    'any_familiar': 'Se evaluará en cualquier familiar que cumpla la condición',
    'any_persona_unidad': 'Se evaluará en cualquier persona de la unidad que cumpla la condición'
  };
  return descriptions[personType] || 'Tipo de persona no válido';
}

function getPersonTypeText(requirement) {
  const baseTexts = {
    'solicitante': 'Solicitante',
    'unidad_convivencia_completa': 'Unidad Completa',
    'unidad_convivencia_sin_solicitante': 'Unidad Sin Solicitante',
    'unidad_familiar_completa': 'Familia Completa',
    'unidad_familiar_sin_solicitante': 'Familia Sin Solicitante',
    'conviviente': 'Conviviente Específico',
    'any_conviviente': 'Cualquier Conviviente',
    'any_familiar': 'Cualquier Familiar',
    'any_persona_unidad': 'Cualquier Persona'
  };
  
  let text = baseTexts[requirement.personType] || requirement.personType;
  
  if (requirement.personType === 'conviviente' && requirement.convivienteType) {
    const convivienteTexts = {
      'conyuge': 'Cónyuge',
      'hijo': 'Hijo/a',
      'padre': 'Padre/Madre',
      'otro': 'Otro familiar',
      'no_familiar': 'No familiar'
    };
    text += ` (${convivienteTexts[requirement.convivienteType] || requirement.convivienteType})`;
  }
  
  return text;
}

function getPersonTypeClass(personType) {
  const classes = {
    'solicitante': 'bg-green-100 text-green-800',
    'unidad_convivencia_completa': 'bg-purple-100 text-purple-800',
    'unidad_convivencia_sin_solicitante': 'bg-purple-100 text-purple-800',
    'unidad_familiar_completa': 'bg-indigo-100 text-indigo-800',
    'unidad_familiar_sin_solicitante': 'bg-indigo-100 text-indigo-800',
    'conviviente': 'bg-orange-100 text-orange-800',
    'any_conviviente': 'bg-yellow-100 text-yellow-800',
    'any_familiar': 'bg-yellow-100 text-yellow-800',
    'any_persona_unidad': 'bg-yellow-100 text-yellow-800'
  };
  return classes[personType] || 'bg-gray-100 text-gray-800';
}

function getAgeUnitText(unit) {
  const unitMap = {
    'years': 'años',
    'months': 'meses',
    'days': 'días'
  };
  return unitMap[unit] || unit;
}

function toggleQuestionSearch(type) {
  if (showQuestionSearch.value === type) {
    showQuestionSearch.value = null;
  } else {
    showQuestionSearch.value = type;
    questionSearchTerm.value = '';
    // Aplicar filtros inmediatamente al abrir
    filterQuestions();
  }
}

function toggleValueSearch(type) {
  if (showValueSearch.value === type) {
    showValueSearch.value = null;
  } else {
    showValueSearch.value = type;
    valueSearchTerm.value = '';
    // Aplicar filtros inmediatamente al abrir
    filterValueOptions();
  }
}

function filterQuestions() {  
  let questions = [...allQuestions.value]; // Crear copia para no modificar el original
  
  // Aplicar filtro de búsqueda si hay término
  if (questionSearchTerm.value.trim()) {
    const term = questionSearchTerm.value.toLowerCase();
    questions = questions.filter(question => 
      question.text.toLowerCase().includes(term) ||
      question.type.toLowerCase().includes(term)
    );
  }
  
  // Añadir información de tipo de persona a todas las preguntas (todas pueden usarse en ambos contextos)
  filteredQuestions.value = questions.map(question => ({
    ...question,
    personType: 'both' // Todas las preguntas pueden usarse para solicitante o convivencia
  }));
}

function filterValueOptions() {
  let options = [...dynamicOptions.value]; // Crear copia para no modificar el original
  
  // Aplicar filtro de búsqueda si hay término
  if (valueSearchTerm.value.trim()) {
    const term = valueSearchTerm.value.toLowerCase();
    options = options.filter(option => 
      option.toLowerCase().includes(term)
    );
  }
  
  filteredValueOptions.value = options;
}

function selectQuestion(questionId, type) {
  if (type === 'simple') {
    newRequirement.question_id = questionId;
  } else if (type.startsWith('group-')) {
    const ruleIndex = parseInt(type.split('-')[1]);
    newRequirement.rules[ruleIndex].question_id = questionId;
  }
  
  showQuestionSearch.value = null;
  questionSearchTerm.value = '';
}

function selectValue(value, type) {
  if (type === 'simple') {
    newRequirement.value = value;
  }
  
  showValueSearch.value = null;
  valueSearchTerm.value = '';
}

function selectValueForRule(value, type) {
  if (type.startsWith('group-')) {
    const ruleIndex = parseInt(type.split('-')[1]);
    newRequirement.rules[ruleIndex].value = value;
  } else if (type.startsWith('subgroup-')) {
    const parts = type.split('-');
    const subgroupIndex = parseInt(parts[1]);
    const ruleIndex = parseInt(parts[2]);
    newRequirement.subgroups[subgroupIndex].rules[ruleIndex].value = value;
  } else if (type.startsWith('edit-group-')) {
    const ruleIndex = parseInt(type.split('-')[2]);
    editingRequirement.value.rules[ruleIndex].value = value;
  } else if (type.startsWith('edit-subgroup-')) {
    const parts = type.split('-');
    const subgroupIndex = parseInt(parts[2]);
    const ruleIndex = parseInt(parts[3]);
    editingRequirement.value.subgroups[subgroupIndex].rules[ruleIndex].value = value;
  }
  
  showValueSearch.value = null;
  valueSearchTerm.value = '';
}

function selectValueForEditing(value) {
  if (editingRequirement.value) {
    editingRequirement.value.value = value;
  }
  
  showValueSearch.value = null;
  valueSearchTerm.value = '';
}

function selectQuestionForEditing(questionId) {
  if (editingRequirement.value) {
    editingRequirement.value.question_id = questionId;
  }
  
  showQuestionSearch.value = null;
  questionSearchTerm.value = '';
}

function handleClickOutside(event) {
  if (!event.target.closest('.relative')) {
    showQuestionSearch.value = null;
    showValueSearch.value = null;
  }
}

function isCurrentQuestion(questionId) {
  return props.questions.some(q => q.id == questionId);
}

function getAvailableOperators() {
  const type = getQuestionType();
  return getOperatorsForType(type);
}

function getAvailableOperatorsForRule(rule) {
  const question = allQuestions.value.find(q => q.id == rule.question_id);
  const type = question ? question.type : 'text';
  return getOperatorsForType(type);
}

function getOperatorsForType(type) {
  const operators = {
    string: [
      { value: '==', label: 'Igual a' },
      { value: '!=', label: 'Distinto de' },
      { value: 'contains', label: 'Contiene' },
      { value: 'starts_with', label: 'Empieza por' },
      { value: 'ends_with', label: 'Termina por' }
    ],
    integer: [
      { value: '==', label: 'Igual a' },
      { value: '!=', label: 'Distinto de' },
      { value: '>', label: 'Mayor que' },
      { value: '>=', label: 'Mayor o igual que' },
      { value: '<', label: 'Menor que' },
      { value: '<=', label: 'Menor o igual que' }
    ],
    boolean: [
      { value: '==', label: 'Igual a' }
    ],
    select: [
      { value: '==', label: 'Igual a' },
      { value: '!=', label: 'Distinto de' }
    ],
    multiple: [
      { value: '==', label: 'Igual a' },
      { value: '!=', label: 'Distinto de' }
    ],
    date: [
      { value: '==', label: 'Igual a' },
      { value: '>', label: 'Después de' },
      { value: '<', label: 'Antes de' },
      { value: '>=', label: 'Desde' },
      { value: '<=', label: 'Hasta' },
      { value: 'between', label: 'Entre fechas' }
    ]
  };
  return operators[type] || operators.text;
}

function getQuestionType() {
  if (!newRequirement.question_id) return 'text';
  const question = allQuestions.value.find(q => q.id == newRequirement.question_id);
  return question ? question.type : 'text';
}

function getQuestionTypeForRule(rule) {
  if (!rule.question_id) return 'text';
  const question = allQuestions.value.find(q => q.id == rule.question_id);
  return question ? question.type : 'text';
}

function getQuestionTypeForEditing() {
  if (!editingRequirement.value?.question_id) return 'text';
  const question = allQuestions.value.find(q => q.id == editingRequirement.value.question_id);
  return question ? question.type : 'text';
}

function isMunicipioQuestion() {
  if (!newRequirement.question_id) return false;
  const question = allQuestions.value.find(q => q.id == newRequirement.question_id);
  return question && question.slug === 'municipio';
}

function isMunicipioQuestionForRule(rule) {
  if (!rule.question_id) return false;
  const question = allQuestions.value.find(q => q.id == rule.question_id);
  return question && question.slug === 'municipio';
}

function isMunicipioQuestionForEditing() {
  if (!editingRequirement.value?.question_id) return false;
  const question = allQuestions.value.find(q => q.id == editingRequirement.value.question_id);
  return question && question.slug === 'municipio';
}

function getQuestionOptionsForEditing() {
  if (!editingRequirement.value?.question_id) return [];
  const question = allQuestions.value.find(q => q.id == editingRequirement.value.question_id);

  if (!question) return [];

  if (question.options) return question.options;

  return [];
}

function getAvailableOperatorsForEditing() {
  const type = getQuestionTypeForEditing();
  return getOperatorsForType(type);
}

function addRuleToGroupForEditing() {
  if (!editingRequirement.value.rules) {
    editingRequirement.value.rules = [];
  }
  editingRequirement.value.rules.push({
    question_id: '',
    operator: '==',
    value: '',
    value2: '',
    valueType: 'exact',
    ageUnit: 'years'
  });
}

function removeRuleFromGroupForEditing(index) {
  if (editingRequirement.value.rules) {
    editingRequirement.value.rules.splice(index, 1);
  }
}

function addSubgroupToGroupForEditing() {
  if (!editingRequirement.value.subgroups) {
    editingRequirement.value.subgroups = [];
  }
  editingRequirement.value.subgroups.push({
    description: '',
    groupLogic: 'AND',
    rules: [],
    subgroups: [],
    nestedSubgroupsLogic: ''
  });
}

function removeSubgroupFromGroupForEditing(index) {
  if (editingRequirement.value.subgroups) {
    editingRequirement.value.subgroups.splice(index, 1);
  }
}

function addNestedSubgroupForEditing(parentSubgroupIndex) {
  if (!editingRequirement.value.subgroups[parentSubgroupIndex].subgroups) {
    editingRequirement.value.subgroups[parentSubgroupIndex].subgroups = [];
  }
  editingRequirement.value.subgroups[parentSubgroupIndex].subgroups.push({
    description: '',
    groupLogic: 'AND',
    rules: [],
    subgroups: []
  });
}

function removeNestedSubgroupForEditing(parentSubgroupIndex, nestedSubgroupIndex) {
  if (editingRequirement.value.subgroups[parentSubgroupIndex].subgroups) {
    editingRequirement.value.subgroups[parentSubgroupIndex].subgroups.splice(nestedSubgroupIndex, 1);
  }
}

function addRuleToNestedSubgroupForEditing(parentSubgroupIndex, nestedSubgroupIndex) {
  if (!editingRequirement.value.subgroups[parentSubgroupIndex].subgroups[nestedSubgroupIndex].rules) {
    editingRequirement.value.subgroups[parentSubgroupIndex].subgroups[nestedSubgroupIndex].rules = [];
  }
  editingRequirement.value.subgroups[parentSubgroupIndex].subgroups[nestedSubgroupIndex].rules.push({
    question_id: '',
    operator: '==',
    value: '',
    value2: '',
    valueType: 'exact',
    ageUnit: 'years',
    discounts: {}
  });
}

function removeRuleFromNestedSubgroupForEditing(parentSubgroupIndex, nestedSubgroupIndex, ruleIndex) {
  if (editingRequirement.value.subgroups[parentSubgroupIndex].subgroups[nestedSubgroupIndex].rules) {
    editingRequirement.value.subgroups[parentSubgroupIndex].subgroups[nestedSubgroupIndex].rules.splice(ruleIndex, 1);
  }
}

function addRuleToSubgroupForEditing(subgroupIndex) {
  if (!editingRequirement.value.subgroups[subgroupIndex].rules) {
    editingRequirement.value.subgroups[subgroupIndex].rules = [];
  }
  editingRequirement.value.subgroups[subgroupIndex].rules.push({
    question_id: '',
    operator: '==',
    value: '',
    value2: '',
    valueType: 'exact',
    ageUnit: 'years'
  });
}

function removeRuleFromSubgroupForEditing(subgroupIndex, ruleIndex) {
  if (editingRequirement.value.subgroups[subgroupIndex].rules) {
    editingRequirement.value.subgroups[subgroupIndex].rules.splice(ruleIndex, 1);
  }
}

async function selectQuestionForGroupEditing(questionId, type) {
  if (type.startsWith('edit-group-')) {
    const ruleIndex = parseInt(type.split('-')[2]);
    if (editingRequirement.value.rules[ruleIndex]) {
      editingRequirement.value.rules[ruleIndex].question_id = questionId;
    }
  } else if (type.startsWith('edit-subgroup-')) {
    const parts = type.split('-');
    const subgroupIndex = parseInt(parts[2]);
    const ruleIndex = parseInt(parts[3]);
    if (editingRequirement.value.subgroups[subgroupIndex] && 
        editingRequirement.value.subgroups[subgroupIndex].rules[ruleIndex]) {
      editingRequirement.value.subgroups[subgroupIndex].rules[ruleIndex].question_id = questionId;
    }
  }

  const question = allQuestions.value.find(q => q.id == questionId);
  if (question && (question.slug === 'provincia' || question.slug === 'municipio' || question.slug === 'comunidad_autonoma')) {
    await loadDynamicOptionsForGroupEditing(question);
  }
  
  showQuestionSearch.value = null;
  questionSearchTerm.value = '';
}

async function selectQuestionForNestedSubgroupEditing(questionId, subgroupIndex, nestedSubgroupIndex, ruleIndex) {
  if (editingRequirement.value.subgroups[subgroupIndex] && 
      editingRequirement.value.subgroups[subgroupIndex].subgroups[nestedSubgroupIndex] &&
      editingRequirement.value.subgroups[subgroupIndex].subgroups[nestedSubgroupIndex].rules[ruleIndex]) {
    editingRequirement.value.subgroups[subgroupIndex].subgroups[nestedSubgroupIndex].rules[ruleIndex].question_id = questionId;
    const question = allQuestions.value.find(q => q.id == questionId);
    if (question && question.type === 'multiple') {
      editingRequirement.value.subgroups[subgroupIndex].subgroups[nestedSubgroupIndex].groupLogic = 'OR';
      editingRequirement.value.subgroups[subgroupIndex].subgroups[nestedSubgroupIndex].rules.forEach(rule => {
        rule.operator = 'contains';
      });
    }

    if (question && (question.slug === 'provincia' || question.slug === 'municipio' || question.slug === 'comunidad_autonoma')) {
      await loadDynamicOptionsForGroupEditing(question);
    }
  }
  
  showQuestionSearch.value = null;
  questionSearchTerm.value = '';
}

function getQuestionOptions() {
  if (!newRequirement.question_id) return [];
  const question = allQuestions.value.find(q => q.id == newRequirement.question_id);

  if (!question) return [];

  if (question.options) return question.options;

  return [];
}

async function loadDynamicOptions() {
  if (!newRequirement.question_id) return;
  
  const question = allQuestions.value.find(q => q.id == newRequirement.question_id);
  if (!question) return;

  if (question.slug === 'comunidad_autonoma') {
    try {
      const response = await fetch('/admin/searchCCAA');
      const data = await response.json();
      dynamicOptions.value = data;
    } catch (error) {
      dynamicOptions.value = [];
    }
  } else if (question.slug === 'provincia') {
    try {
      const ccaaRequisito = requirements.value.find(req => {
        if (req.type === 'simple') {
          const reqQuestion = allQuestions.value.find(q => q.id == req.question_id);
          return reqQuestion && reqQuestion.slug === 'comunidad_autonoma';
        }
        return false;
      });
      
      let url = '/admin/searchProvincias';
      if (ccaaRequisito) {
        url += `?ccaa=${encodeURIComponent(ccaaRequisito.value)}`;
      }
      
      const response = await fetch(url);
      const data = await response.json();
      dynamicOptions.value = data;
    } catch (error) {
      dynamicOptions.value = [];
    }
  } else if (question.slug === 'municipio') {
    try {
      const provinciaRequisito = requirements.value.find(req => {
        if (req.type === 'simple') {
          const reqQuestion = allQuestions.value.find(q => q.id == req.question_id);
          return reqQuestion && reqQuestion.slug === 'provincia';
        }
        return false;
      });
      
      let url = '/admin/searchMunicipios';
      if (provinciaRequisito) {
        url += `?provincia=${encodeURIComponent(provinciaRequisito.value)}`;
      }
      
      const response = await fetch(url);
      const data = await response.json();
      dynamicOptions.value = data;
    } catch (error) {
      dynamicOptions.value = [];
    }
  } else if (question.options) {
    dynamicOptions.value = question.options;
    return;
  } else {
    dynamicOptions.value = [];
  }
}

function findValueInRequirement(requirement, targetSlug) {
  if (!requirement) return null;

  if (requirement.rules) {
    for (const rule of requirement.rules) {
      const question = allQuestions.value.find(q => q.id == rule.question_id);
      if (question && question.slug === targetSlug) {
        return rule.value;
      }
    }
  }

  if (requirement.subgroups) {
    for (const subgroup of requirement.subgroups) {
      if (subgroup.rules) {
        for (const rule of subgroup.rules) {
          const question = allQuestions.value.find(q => q.id == rule.question_id);
          if (question && question.slug === targetSlug) {
            return rule.value;
          }
        }
      }

      if (subgroup.subgroups) {
        for (const nestedSubgroup of subgroup.subgroups) {
          if (nestedSubgroup.rules) {
            for (const rule of nestedSubgroup.rules) {
              const question = allQuestions.value.find(q => q.id == rule.question_id);
              if (question && question.slug === targetSlug) {
                return rule.value;
              }
            }
          }
        }
      }
    }
  }
  
  return null;
}

async function loadDynamicOptionsForGroupEditing(question) {
  
  if (question.slug === 'comunidad_autonoma') {
    try {
      const response = await fetch('/admin/searchCCAA');
      const data = await response.json();
      dynamicOptions.value = data;
    } catch (error) {
      dynamicOptions.value = [];
    }
  } else if (question.slug === 'provincia') {
    try {
      let ccaaValue = findValueInRequirement(editingRequirement.value, 'comunidad_autonoma');
      
      let url = '/admin/searchProvincias';
      if (ccaaValue) {
        url += `?ccaa=${encodeURIComponent(ccaaValue)}`;
      }
      
      const response = await fetch(url);
      const data = await response.json();
      dynamicOptions.value = data;
    } catch (error) {
      dynamicOptions.value = [];
    }
  } else if (question.slug === 'municipio') {
    try {
      let provinciaValue = findValueInRequirement(editingRequirement.value, 'provincia');
      
      let url = '/admin/searchMunicipios';
      if (provinciaValue) {
        url += `?provincia=${encodeURIComponent(provinciaValue)}`;
      }
      
      const response = await fetch(url);
      const data = await response.json();
      dynamicOptions.value = data;
    } catch (error) {
      dynamicOptions.value = [];
    }
  } else if (question.options) {
    dynamicOptions.value = question.options;
    return;
  } else {
    dynamicOptions.value = [];
  }
}

async function loadDynamicOptionsForEditing() {
  if (!editingRequirement.value?.question_id) {
    return;
  }
  
  const question = allQuestions.value.find(q => q.id == editingRequirement.value.question_id);
  if (!question) return;

  if (question.slug === 'comunidad_autonoma') {
    try {
      const response = await fetch('/admin/searchCCAA');
      const data = await response.json();
      dynamicOptions.value = data;
    } catch (error) {
      dynamicOptions.value = [];
    }
  } else if (question.slug === 'provincia') {
    try {
      let ccaaValue = null;
      const ccaaRequisito = requirements.value.find(req => {
        if (req.type === 'simple') {
          const reqQuestion = allQuestions.value.find(q => q.id == req.question_id);
          return reqQuestion && reqQuestion.slug === 'comunidad_autonoma';
        }
        return false;
      });
      
      if (ccaaRequisito) {
        ccaaValue = ccaaRequisito.value;
      } else {
        for (const req of requirements.value) {
          if (req.type === 'group') {
            ccaaValue = findValueInRequirement(req, 'comunidad_autonoma');
            if (ccaaValue) break;
          }
        }
      }
      
      let url = '/admin/searchProvincias';
      if (ccaaValue) {
        url += `?ccaa=${encodeURIComponent(ccaaValue)}`;
      }
      
      const response = await fetch(url);
      const data = await response.json();
      dynamicOptions.value = data;
    } catch (error) {
      dynamicOptions.value = [];
    }
  } else if (question.slug === 'municipio') {
    try {
      let provinciaValue = null;
      const provinciaRequisito = requirements.value.find(req => {
        if (req.type === 'simple') {
          const reqQuestion = allQuestions.value.find(q => q.id == req.question_id);
          return reqQuestion && reqQuestion.slug === 'provincia';
        }
        return false;
      });
      
      if (provinciaRequisito) {
        provinciaValue = provinciaRequisito.value;
      } else {
        for (const req of requirements.value) {
          if (req.type === 'group') {
            provinciaValue = findValueInRequirement(req, 'provincia');
            if (provinciaValue) break;
          }
        }
      }
      
      let url = '/admin/searchMunicipios';
      if (provinciaValue) {
        url += `?provincia=${encodeURIComponent(provinciaValue)}`;
      }
      
      const response = await fetch(url);
      const data = await response.json();
      dynamicOptions.value = data;
    } catch (error) {
      dynamicOptions.value = [];
    }
  } else if (question.options) {
    dynamicOptions.value = question.options;
    return;
  } else {
    dynamicOptions.value = [];
  }
}

function getDynamicOptionsForRule(rule) {
  if (!rule.question_id) return [];
  
  const question = allQuestions.value.find(q => q.id == rule.question_id);
  if (!question) return [];

  if (question.slug === 'comunidad_autonoma') {
    return dynamicOptions.value;
  } else if (question.slug === 'provincia') {
    const ccaaRequisito = requirements.value.find(req => {
      if (req.type === 'simple') {
        const reqQuestion = allQuestions.value.find(q => q.id == req.question_id);
        return reqQuestion && reqQuestion.slug === 'comunidad_autonoma';
      } else if (req.type === 'group') {
        return req.rules.some(rule => {
          const ruleQuestion = allQuestions.value.find(q => q.id == rule.question_id);
          return ruleQuestion && ruleQuestion.slug === 'comunidad_autonoma';
        });
      }
      return false;
    });
    if (!ccaaRequisito) return [];
    return dynamicOptions.value;
  } else if (question.slug === 'municipio') {
    return dynamicOptions.value;
  } else if (question.options && Array.isArray(question.options)) {
    return question.options;
  }
  
  return [];
}

function getFilteredValueOptionsForRule(rule) {
  let options = getDynamicOptionsForRule(rule);
  
  // Aplicar filtro de búsqueda si hay término
  if (valueSearchTerm.value.trim()) {
    const term = valueSearchTerm.value.toLowerCase();
    options = options.filter(option => 
      option.toLowerCase().includes(term)
    );
  }
  
  return options;
}

function getFilteredValueOptionsForEditing() {
  let options = getQuestionOptionsForEditing();
  
  // Aplicar filtro de búsqueda si hay término
  if (valueSearchTerm.value.trim()) {
    const term = valueSearchTerm.value.toLowerCase();
    options = options.filter(option => 
      option.toLowerCase().includes(term)
    );
  }
  
  return options;
}

function getQuestionText(questionId) {
  const question = allQuestions.value.find(q => q.id == questionId);
  return question ? question.text : 'Pregunta no encontrada';
}

function getOperatorText(operator) {
  const operatorMap = {
    '==': 'igual a',
    '!=': 'distinto de',
    '>': 'mayor que',
    '>=': 'mayor o igual que',
    '<': 'menor que',
    '<=': 'menor o igual que',
    'contains': 'contiene',
    'not_contains': 'no contiene',
    'starts_with': 'empieza por',
    'ends_with': 'termina por'
  };
  return operatorMap[operator] || operator;
}

function formatValue(requirement) {
  const question = allQuestions.value.find(q => q.id == requirement.question_id);
  if (!question) return requirement.value;
  
  if (question.type === 'boolean') {
    return requirement.value === '1' ? 'Sí' : 'No';
  }
  
  if (question.type === 'select' && question.options) {
    const index = question.options.indexOf(requirement.value);
    return index >= 0 ? question.options[index] : requirement.value;
  }

  if (question.type === 'date' && requirement.valueType) {
    if (requirement.valueType === 'age_minimum') {
      const unit = getAgeUnitText(requirement.ageUnit || 'years');
      return `Mayor de ${requirement.value} ${unit}`;
    }
    if (requirement.valueType === 'age_maximum') {
      const unit = getAgeUnitText(requirement.ageUnit || 'years');
      return `Menor de ${requirement.value} ${unit}`;
    }
    if (requirement.valueType === 'age_range') {
      const unit = getAgeUnitText(requirement.ageUnit || 'years');
      return `Entre ${requirement.value} y ${requirement.value2} ${unit}`;
    }
  }
  
  return requirement.value;
}

function formatRuleValue(rule) {
  const question = allQuestions.value.find(q => q.id == rule.question_id);
  if (!question) return rule.value;
  
  if (question.type === 'boolean') {
    return rule.value === '1' ? 'Sí' : 'No';
  }
  
  if (question.type === 'select' && question.options) {
    const index = question.options.indexOf(rule.value);
    return index >= 0 ? question.options[index] : rule.value;
  }
  
  if (question.type === 'multiple') {
    if (rule.value === '[null]') {
      return 'No seleccionar ninguna opción';
    }
    if (question.options) {
      const index = question.options.indexOf(rule.value);
      return index >= 0 ? question.options[index] : rule.value;
    }
  }

  if (question.type === 'date' && rule.valueType) {
    if (rule.valueType === 'age_minimum') {
      const unit = getAgeUnitText(rule.ageUnit || 'years');
      return `Mayor de ${rule.value} ${unit}`;
    }
    if (rule.valueType === 'age_maximum') {
      const unit = getAgeUnitText(rule.ageUnit || 'years');
      return `Menor de ${rule.value} ${unit}`;
    }
    if (rule.valueType === 'age_range') {
      const unit = getAgeUnitText(rule.ageUnit || 'years');
      return `Entre ${rule.value} y ${rule.value2} ${unit}`;
    }
    if (rule.valueType === 'exact') {
      return rule.value;
    }
  }

  
  return rule.value;
}

function addRuleToGroup() {
  newRequirement.rules.push({
    question_id: '',
    operator: '==',
    value: '',
    value2: '',
    valueType: 'exact',
    ageUnit: 'years'
  });
}

function removeRuleFromGroup(index) {
  newRequirement.rules.splice(index, 1);
}

function addSubgroupToGroup() {
  newRequirement.subgroups.push({
    description: '',
    groupLogic: 'AND',
    rules: [],
    subgroups: [],
    nestedSubgroupsLogic: ''
  });
}

function removeSubgroupFromGroup(index) {
  newRequirement.subgroups.splice(index, 1);
}

function addNestedSubgroup(groupIndex, parentSubgroupIndex) {
  if (!newRequirement.subgroups[parentSubgroupIndex].subgroups) {
    newRequirement.subgroups[parentSubgroupIndex].subgroups = [];
  }
  newRequirement.subgroups[parentSubgroupIndex].subgroups.push({
    description: '',
    groupLogic: 'AND',
    rules: [],
    subgroups: []
  });
}

function removeNestedSubgroup(groupIndex, parentSubgroupIndex, nestedSubgroupIndex) {
  if (newRequirement.subgroups[parentSubgroupIndex].subgroups) {
    newRequirement.subgroups[parentSubgroupIndex].subgroups.splice(nestedSubgroupIndex, 1);
  }
}

function addRuleToNestedSubgroup(groupIndex, parentSubgroupIndex, nestedSubgroupIndex) {
  if (!newRequirement.subgroups[groupIndex].subgroups[parentSubgroupIndex].subgroups[nestedSubgroupIndex].rules) {
    newRequirement.subgroups[groupIndex].subgroups[parentSubgroupIndex].subgroups[nestedSubgroupIndex].rules = [];
  }
  newRequirement.subgroups[groupIndex].subgroups[parentSubgroupIndex].subgroups[nestedSubgroupIndex].rules.push({
    question_id: '',
    operator: '==',
    value: '',
    value2: '',
    valueType: 'exact',
    ageUnit: 'years',
    discounts: {}
  });
}

function removeRuleFromNestedSubgroup(groupIndex, parentSubgroupIndex, nestedSubgroupIndex, ruleIndex) {
  if (newRequirement.subgroups[groupIndex].subgroups[parentSubgroupIndex].subgroups[nestedSubgroupIndex].rules) {
    newRequirement.subgroups[groupIndex].subgroups[parentSubgroupIndex].subgroups[nestedSubgroupIndex].rules.splice(ruleIndex, 1);
  }
}

function addRuleToSubgroup(subgroupIndex) {
  if (!newRequirement.subgroups[subgroupIndex].rules) {
    newRequirement.subgroups[subgroupIndex].rules = [];
  }
  newRequirement.subgroups[subgroupIndex].rules.push({
    question_id: '',
    operator: '==',
    value: '',
    value2: '',
    valueType: 'exact',
    ageUnit: 'years'
  });
}

function removeRuleFromSubgroup(subgroupIndex, ruleIndex) {
  if (newRequirement.subgroups[subgroupIndex].rules) {
    newRequirement.subgroups[subgroupIndex].rules.splice(ruleIndex, 1);
  }
}

function selectQuestionForSubgroup(questionId, subgroupIndex, ruleIndex) {
  if (newRequirement.subgroups[subgroupIndex].rules[ruleIndex]) {
    newRequirement.subgroups[subgroupIndex].rules[ruleIndex].question_id = questionId;
  }
  
  showQuestionSearch.value = null;
  questionSearchTerm.value = '';
}

function cleanEligibilityData(data) {
  const cleaned = { ...data };
  
  if (!['age_minimum', 'age_maximum', 'age_range'].includes(cleaned.valueType)) {
    cleaned.ageUnit = null;
  }
  
  if (cleaned.operator !== 'between') {
    cleaned.value2 = null;
  }

  if (cleaned.question_id) {
    const question = allQuestions.value.find(q => q.id == cleaned.question_id);
    if (question) {
      if (question.type === 'select' && question.options && Array.isArray(question.options)) {
        const optionIndex = parseInt(cleaned.value);
        if (!isNaN(optionIndex) && question.options[optionIndex]) {
          cleaned.value = question.options[optionIndex];
        }
      } else if (question.type === 'multiple' && question.options && Array.isArray(question.options)) {
        let values = cleaned.value;
        if (typeof values === 'string') {
          try {
            values = JSON.parse(values);
          } catch (e) {
          }
        }
        if (Array.isArray(values)) {
          cleaned.value = JSON.stringify(values.map(val => {
            const optionIndex = parseInt(val);
            if (!isNaN(optionIndex) && question.options[optionIndex]) {
              return question.options[optionIndex];
            }
            return val;
          }));
        }
      }
    }
  }


  if (cleaned.rules && Array.isArray(cleaned.rules)) {
    cleaned.rules = cleaned.rules.map(rule => cleanRuleData(rule));
  }

  if (cleaned.subgroups && Array.isArray(cleaned.subgroups)) {
    cleaned.subgroups = cleaned.subgroups.map(subgroup => ({
      ...subgroup,
      rules: subgroup.rules ? subgroup.rules.map(rule => cleanRuleData(rule)) : [],
      subgroups: subgroup.subgroups ? subgroup.subgroups.map(nestedSubgroup => ({
        ...nestedSubgroup,
        rules: nestedSubgroup.rules ? nestedSubgroup.rules.map(rule => cleanRuleData(rule)) : []
      })) : []
    }));
  }
  
  return cleaned;
}

function cleanRuleData(rule) {
  const cleaned = { ...rule };
  
  if (!['age_minimum', 'age_maximum', 'age_range'].includes(cleaned.valueType)) {
    cleaned.ageUnit = null;
  }
  
  if (cleaned.operator !== 'between') {
    cleaned.value2 = null;
  }

  if (cleaned.question_id) {
    const question = allQuestions.value.find(q => q.id == cleaned.question_id);
    if (question) {
      if (question.type === 'select' && question.options && Array.isArray(question.options)) {
        const optionIndex = parseInt(cleaned.value);
        if (!isNaN(optionIndex) && question.options[optionIndex]) {
          cleaned.value = question.options[optionIndex];
        }
      } else if (question.type === 'multiple' && question.options && Array.isArray(question.options)) {
        let values = cleaned.value;
        if (typeof values === 'string') {
          try {
            values = JSON.parse(values);
          } catch (e) {
          }
        }
        if (Array.isArray(values)) {
          cleaned.value = JSON.stringify(values.map(val => {
            const optionIndex = parseInt(val);
            if (!isNaN(optionIndex) && question.options[optionIndex]) {
              return question.options[optionIndex];
            }
            return val;
          }));
        }
      }
    }
  }
  
  return cleaned;
}

function addRequirement() {
  if (newRequirement.type === 'simple') {
    if (!canAddSimpleRequirement.value) {
      showToast('Por favor completa todos los campos', 'error');
      return;
    }
    
    const cleanedData = cleanEligibilityData({
      type: 'simple',
      personType: newRequirement.personType,
      convivienteType: newRequirement.convivienteType,
      description: newRequirement.description,
      question_id: newRequirement.question_id,
      operator: newRequirement.operator,
      value: newRequirement.value,
      value2: newRequirement.value2,
      valueType: newRequirement.valueType,
      ageUnit: newRequirement.ageUnit,
    });
    requirements.value.push(cleanedData);
  } else {
    if (!canAddGroupRequirement.value) {
      showToast('Por favor completa todos los campos del grupo', 'error');
      return;
    }
    
    requirements.value.push({
      type: 'group',
      personType: newRequirement.personType,
      convivienteType: newRequirement.convivienteType,
      description: newRequirement.description,
      groupLogic: newRequirement.groupLogic,
      groupType: newRequirement.groupType,
      rules: [...newRequirement.rules],
      subgroups: [...newRequirement.subgroups]
    });
  }
  
  // Reset form
  newRequirement.type = 'simple';
  newRequirement.personType = 'solicitante';
  newRequirement.convivienteType = null;
  newRequirement.description = '';
  newRequirement.question_id = '';
  newRequirement.operator = '==';
  newRequirement.value = '';
  newRequirement.value2 = '';
  newRequirement.valueType = 'exact';
  newRequirement.ageUnit = 'years';
  newRequirement.groupLogic = 'AND';
  newRequirement.groupType = 'rules';
  newRequirement.rules = [];
  newRequirement.subgroups = [];
  
  showToast('Requisito añadido correctamente', 'success');
  if (!isSyncingFromProps.value) emit('update:requirements', requirements.value);
}

function removeRequirement(index) {
  requirements.value.splice(index, 1);
  showToast('Requisito eliminado', 'success');
  if (!isSyncingFromProps.value) emit('update:requirements', requirements.value);
}

function duplicateSubgroup(groupIndex, subgroupIndex) {
  const group = requirements.value[groupIndex];
  if (!group.subgroups || !group.subgroups[subgroupIndex]) {
    showToast('No se pudo duplicar el subgrupo', 'error');
    return;
  }
  
  const originalSubgroup = group.subgroups[subgroupIndex];
  const duplicatedSubgroup = {
    description: `${originalSubgroup.description} (Copia)`,
    groupLogic: originalSubgroup.groupLogic,
    rules: originalSubgroup.rules ? originalSubgroup.rules.map(rule => ({
      question_id: rule.question_id,
      operator: rule.operator,
      value: rule.value,
      value2: rule.value2,
      valueType: rule.valueType,
      ageUnit: rule.ageUnit,
      discounts: rule.discounts ? { ...rule.discounts } : {}
    })) : [],
    subgroups: originalSubgroup.subgroups ? originalSubgroup.subgroups.map(nestedSubgroup => ({
      description: nestedSubgroup.description,
      groupLogic: nestedSubgroup.groupLogic,
      rules: nestedSubgroup.rules ? nestedSubgroup.rules.map(rule => ({
        question_id: rule.question_id,
        operator: rule.operator,
        value: rule.value,
        value2: rule.value2,
        valueType: rule.valueType,
        ageUnit: rule.ageUnit,
        discounts: rule.discounts ? { ...rule.discounts } : {}
      })) : [],
      subgroups: []
    })) : []
  };
  
  group.subgroups.splice(subgroupIndex + 1, 0, duplicatedSubgroup);
  showToast('Subgrupo duplicado correctamente', 'success');
  if (!isSyncingFromProps.value) emit('update:requirements', requirements.value);
}

function duplicateSubgroupFromModal(subgroupIndex) {
  if (!editingRequirement.value.subgroups || !editingRequirement.value.subgroups[subgroupIndex]) {
    showToast('No se pudo duplicar el subgrupo', 'error');
    return;
  }
  
  const originalSubgroup = editingRequirement.value.subgroups[subgroupIndex];
  const duplicatedSubgroup = {
    description: `${originalSubgroup.description} (Copia)`,
    groupLogic: originalSubgroup.groupLogic,
    rules: originalSubgroup.rules ? originalSubgroup.rules.map(rule => ({
      question_id: rule.question_id,
      operator: rule.operator,
      value: rule.value,
      value2: rule.value2,
      valueType: rule.valueType,
      ageUnit: rule.ageUnit,
      discounts: rule.discounts ? { ...rule.discounts } : {}
    })) : [],
    subgroups: originalSubgroup.subgroups ? originalSubgroup.subgroups.map(nestedSubgroup => ({
      description: nestedSubgroup.description,
      groupLogic: nestedSubgroup.groupLogic,
      rules: nestedSubgroup.rules ? nestedSubgroup.rules.map(rule => ({
        question_id: rule.question_id,
        operator: rule.operator,
        value: rule.value,
        value2: rule.value2,
        valueType: rule.valueType,
        ageUnit: rule.ageUnit,
        discounts: rule.discounts ? { ...rule.discounts } : {}
      })) : [],
      subgroups: []
    })) : []
  };
  
  editingRequirement.value.subgroups.splice(subgroupIndex + 1, 0, duplicatedSubgroup);
  showToast('Subgrupo duplicado correctamente', 'success');
}

async function editRequirement(index) {
  const requirement = requirements.value[index];
  editingIndex.value = index;
  editingRequirement.value = { ...requirement };
  showEditModal.value = true;
  
  // Cargar opciones dinámicas si es necesario
  if (editingRequirement.value.question_id) {
    await loadDynamicOptionsForEditing();
  }
}

function saveEditedRequirement() {
  if (editingIndex.value >= 0 && editingRequirement.value) {
    if (editingRequirement.value.type === 'simple') {
      if (!canEditSimpleRequirement.value) {
        showToast('Por favor completa todos los campos', 'error');
        return;
      }
      
      const cleanedData = cleanEligibilityData({
        type: 'simple',
        personType: editingRequirement.value.personType,
        convivienteType: editingRequirement.value.convivienteType,
        description: editingRequirement.value.description,
        question_id: editingRequirement.value.question_id,
        operator: editingRequirement.value.operator,
        value: editingRequirement.value.value,
        value2: editingRequirement.value.value2,
        valueType: editingRequirement.value.valueType,
        ageUnit: editingRequirement.value.ageUnit
      });
      requirements.value[editingIndex.value] = cleanedData;
    } else {
      if (!canEditGroupRequirement.value) {
        showToast('Por favor completa todos los campos del grupo', 'error');
        return;
      }
      
      requirements.value[editingIndex.value] = {
        type: 'group',
        personType: editingRequirement.value.personType,
        convivienteType: editingRequirement.value.convivienteType,
        description: editingRequirement.value.description,
        groupLogic: editingRequirement.value.groupLogic,
        groupType: editingRequirement.value.groupType,
        rules: [...(editingRequirement.value.rules || [])],
        subgroups: [...(editingRequirement.value.subgroups || [])]
      };
    }
    
    showToast('Requisito actualizado correctamente', 'success');
    if (!isSyncingFromProps.value) emit('update:requirements', requirements.value);
    closeEditModal();
  }
}

function closeEditModal() {
  showEditModal.value = false;
  editingRequirement.value = null;
  editingIndex.value = -1;
}

// Watchers
watch(requirements, (newRequirements) => {
  if (!isSyncingFromProps.value) emit('update:requirements', newRequirements);
}, { deep: true });

watch(() => newRequirement.question_id, async (newQuestionId) => {
  if (newQuestionId) {
    await loadDynamicOptions();
  } else {
    dynamicOptions.value = [];
  }
});

// Watcher para cerrar dropdowns cuando cambie la pregunta
watch(() => newRequirement.question_id, () => {
  showValueSearch.value = null;
  valueSearchTerm.value = '';
  
  newRequirement.valueType = 'exact';
});

// Watcher para cargar opciones dinámicas en el modal de edición
watch(() => editingRequirement.value?.question_id, async (newQuestionId) => {
  if (newQuestionId && editingRequirement.value) {
    await loadDynamicOptionsForEditing();
  } else {
    dynamicOptions.value = [];
  }
});

watch(requirements, async (newRequirements) => {
  if (newRequirement.question_id) {
    const question = allQuestions.value.find(q => q.id == newRequirement.question_id);
    if (question && (question.slug === 'provincia' || question.slug === 'municipio')) {
      await loadDynamicOptions();
    }
  }
}, { deep: true });

watch(() => newRequirement.rules, async (newRules) => {
  for (const rule of newRules) {
    if (rule.question_id) {
      const question = allQuestions.value.find(q => q.id == rule.question_id);
      if (question && (question.slug === 'provincia' || question.slug === 'municipio')) {
        await loadDynamicOptions();
      }
    }
  }
}, { deep: true });

// Watcher para filtrar preguntas cuando cambie el tipo de persona
watch(() => newRequirement.personType, () => {
  if (showQuestionSearch.value) {
    filterQuestions();
  }
});

watch(() => props.data?.eligibilityLogic, (newEligibilityLogic) => {
  loadExistingRequirements();
}, { deep: true });

function loadExistingRequirements() {  
  if (props.data?.eligibilityLogic && Array.isArray(props.data.eligibilityLogic)) {
    isSyncingFromProps.value = true;
    requirements.value = props.data.eligibilityLogic.map(req => ({
      type: req.type || 'simple',
      personType: req.personType || 'solicitante',
      convivienteType: req.convivienteType || null,
      description: req.description || '',
      question_id: req.question_id || '',
      operator: req.operator || '==',
      value: req.value || '',
      value2: req.value2 || '',
      valueType: req.valueType || 'exact',
      ageUnit: req.ageUnit || 'years',
      groupLogic: req.groupLogic || 'AND',
      groupType: req.groupType || 'rules',
      rules: (req.rules || []).map(rule => ({
        question_id: rule.question_id || '',
        operator: rule.operator || '==',
        value: rule.value || '',
        value2: rule.value2 || '',
        valueType: rule.valueType || 'exact',
        ageUnit: rule.ageUnit || 'years'
      })),
      subgroups: (req.subgroups || []).map(subgroup => ({
        description: subgroup.description || '',
        groupLogic: subgroup.groupLogic || 'AND',
        rules: (subgroup.rules || []).map(rule => ({
          question_id: rule.question_id || '',
          operator: rule.operator || '==',
          value: rule.value || '',
          value2: rule.value2 || '',
          valueType: rule.valueType || 'exact',
          ageUnit: rule.ageUnit || 'years',
          discounts: rule.discounts || {}
        })),
        subgroups: (subgroup.subgroups || []).map(nestedSubgroup => ({
          description: nestedSubgroup.description || '',
          groupLogic: nestedSubgroup.groupLogic || 'AND',
          rules: (nestedSubgroup.rules || []).map(rule => ({
            question_id: rule.question_id || '',
            operator: rule.operator || '==',
            value: rule.value || '',
            value2: rule.value2 || '',
            valueType: rule.valueType || 'exact',
            ageUnit: rule.ageUnit || 'years',
            discounts: rule.discounts || {}
          })),
          subgroups: []
        })),
        nestedSubgroupsLogic: subgroup.nestedSubgroupsLogic || ''
      }))
    }));
    nextTick(() => {
      isSyncingFromProps.value = false;
    });
  } else {
    requirements.value = [];
  }
}

const draggedIndex = ref(null);
const dragOverIndex = ref(null);

function handleDragStart(event, index) {
  draggedIndex.value = index;
  event.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd() {
  draggedIndex.value = null;
  dragOverIndex.value = null;
}

function handleDragOver(event, index) {
  event.dataTransfer.dropEffect = 'move';
  dragOverIndex.value = index;
}

function handleDragLeave() {
  dragOverIndex.value = null;
}

function handleDrop(event, targetIndex) {
  event.preventDefault();
  
  if (draggedIndex.value === null || draggedIndex.value === targetIndex) {
    draggedIndex.value = null;
    dragOverIndex.value = null;
    return;
  }

  const draggedItem = requirements.value[draggedIndex.value];
  requirements.value.splice(draggedIndex.value, 1);
  
  if (draggedIndex.value < targetIndex) {
    requirements.value.splice(targetIndex - 1, 0, draggedItem);
  } else {
    requirements.value.splice(targetIndex, 0, draggedItem);
  }

  draggedIndex.value = null;
  dragOverIndex.value = null;
  
  if (!isSyncingFromProps.value) emit('update:requirements', requirements.value);
}


// Lifecycle
onMounted(() => {
  loadAllQuestions();
  loadExistingRequirements();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.relative {
  position: relative;
}

.dropdown-container {
  position: relative;
  z-index: 1;
}

.dropdown-menu {
  position: absolute;
  z-index: 9999;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 0.25rem;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  max-height: 20rem;
  overflow-y: auto;
  min-width: 300px;
}

/* Utilidad para truncar texto a 2 líneas */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  word-break: break-word;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  word-break: break-word;
}
</style> 