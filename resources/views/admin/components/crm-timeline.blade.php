{{-- Componente de línea temporal CRM horizontal --}}
<div class="crm-timeline">
    <h5 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
        <i class="fas fa-clock text-blue-500 mr-2"></i>
        Historial CRM
    </h5>
    
    @if($crmHistory->count() > 0)
        <div class="relative">
            {{-- Línea horizontal ultra-delgada --}}
            <div class="absolute top-4 left-0 right-0 h-px bg-gray-300"></div>
            
            <div class="flex space-x-4 overflow-x-auto pb-4">
                @foreach($crmHistory as $index => $history)
                    <div class="timeline-item relative flex-shrink-0">
                        {{-- Punto de la timeline mínimo --}}
                        <div class="absolute top-3 left-1/2 transform -translate-x-1/2 w-3 h-3 bg-blue-400 rounded-full border-2 border-white shadow-sm"></div>
                        
                        {{-- Contenido ultra-compacto --}}
                        <div class="mt-6 bg-white border border-gray-200 rounded-md p-3 shadow-sm hover:shadow-md transition-all duration-200 min-w-48 max-w-56">
                            {{-- Header compacto en una sola línea --}}
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    @switch($history->event)
                                        @case('user_registered')
                                            <i class="fas fa-user-plus text-emerald-600 text-sm"></i>
                                            <span class="text-sm font-medium text-gray-900">Usuario Registrado</span>
                                            @break
                                        @case('generic_form_completed')
                                            <i class="fas fa-clipboard-check text-blue-600 text-sm"></i>
                                            <span class="text-sm font-medium text-gray-900">Formulario Completado</span>
                                            @break
                                        @case('beneficiary_confirmed')
                                            <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
                                            <span class="text-sm font-medium text-gray-900">Beneficiario Confirmado</span>
                                            @break
                                        @case('contract_created')
                                            <i class="fas fa-file-contract text-violet-600 text-sm"></i>
                                            <span class="text-sm font-medium text-gray-900">Contrato Creado</span>
                                            @break
                                        @case('helps_detected')
                                            <i class="fas fa-search-dollar text-emerald-600 text-sm"></i>
                                            <span class="text-sm font-medium text-gray-900">Ayuda Detectada</span>
                                            @break
                                        @case('no_helps_detected')
                                            <i class="fas fa-exclamation-triangle text-amber-600 text-sm"></i>
                                            <span class="text-sm font-medium text-gray-900">No Se Detectaron Ayudas</span>
                                            @break
                                        @case('non_beneficiary')
                                            <i class="fas fa-user-times text-red-600 text-sm"></i>
                                            <span class="text-sm font-medium text-gray-900">No Es Beneficiario</span>
                                            @break
                                        @default
                                            <i class="fas fa-info-circle text-gray-600 text-sm"></i>
                                            <span class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $history->event)) }}</span>
                                    @endswitch
                                </div>
                            </div>
                            
                            {{-- Fecha en la parte inferior --}}
                            <div class="text-center mb-2">
                                <span class="text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded">
                                    {{ \Carbon\Carbon::parse($history->created_at)->format('d/m H:i') }}
                                </span>
                            </div>
                            
                            {{-- Transición de estados ultra-compacta --}}
                            @if($history->from_stage || $history->to_stage)
                                <div class="flex items-center justify-center space-x-2 mb-2 p-2 bg-blue-50 border border-blue-200 rounded text-xs">
                                    @if($history->from_stage !== null)
                                        <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded">{{ $history->from_stage }}</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded">?</span>
                                    @endif
                                    
                                    @if($history->from_stage && $history->to_stage)
                                        <i class="fas fa-arrow-right text-blue-500"></i>
                                    @endif
                                    
                                    @if($history->to_stage)
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded font-medium">{{ $history->to_stage }}</span>
                                    @endif
                                </div>
                            @endif
                            
                            {{-- Cambio de temperatura ultra-compacto con colores específicos --}}
                            @if($history->from_temp || $history->to_temp)
                                <div class="flex items-center justify-center space-x-2 mb-2 p-2 bg-gray-50 border border-gray-200 rounded text-xs">
                                    @if($history->from_temp !== null)
                                        @php
                                            $fromColor = match($history->from_temp) {
                                                'caliente' => 'bg-red-100 text-red-800 border-red-200',
                                                'tibio' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                'frio' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'congelado' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                                default => 'bg-gray-100 text-gray-700 border-gray-200'
                                            };
                                        @endphp
                                        <span class="px-2 py-1 rounded border {{ $fromColor }}">
                                            <i class="fas fa-thermometer-half mr-1"></i>{{ ucfirst($history->from_temp) }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded border bg-gray-100 text-gray-700 border-gray-200">
                                            <i class="fas fa-thermometer-half mr-1"></i>?
                                        </span>
                                    @endif
                                    
                                    @if($history->from_temp && $history->to_temp)
                                        <i class="fas fa-arrow-right text-gray-500"></i>
                                    @endif
                                    
                                    @if($history->to_temp)
                                        @php
                                            $toColor = match($history->to_temp) {
                                                'caliente' => 'bg-red-100 text-red-800 border-red-200',
                                                'tibio' => 'bg-orange-100 text-orange-800 border-orange-200',
                                                'frio' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'congelado' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                                default => 'bg-gray-100 text-gray-700 border-gray-200'
                                            };
                                        @endphp
                                        <span class="px-2 py-1 rounded border font-medium {{ $toColor }}">
                                            <i class="fas fa-thermometer-half mr-1"></i>{{ ucfirst($history->to_temp) }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                            
                            {{-- Metadatos en línea horizontal --}}
                            @if($history->meta && is_array($history->meta))
                                <div class="flex flex-wrap gap-1 text-xs">
                                    @foreach($history->meta as $key => $value)
                                        @if(is_string($value) && !empty($value) && $value !== 'null')
                                            <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                                <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> {{ $value }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            
                            {{-- Usuario que realizó el cambio --}}
                            @if($history->change_by && $history->changedBy)
                                <div class="mt-2 pt-2 border-t border-gray-200 text-xs text-gray-500 text-center">
                                    <i class="fas fa-user-edit text-blue-500 mr-1"></i>
                                    <span class="font-medium text-gray-700">{{ $history->changedBy->name ?? 'Usuario' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center py-6 text-gray-500">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-history text-xl text-gray-300"></i>
            </div>
            <p class="text-sm font-medium">No hay historial CRM</p>
        </div>
    @endif
    
</div>
