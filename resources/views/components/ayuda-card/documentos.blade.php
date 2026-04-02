@props(['ayudaSolicitada'])

@php
    $faltantes = collect($ayudaSolicitada->documentos_faltantes ?? []);
    $configurados = collect($ayudaSolicitada->documentos_configurados ?? []);
    $hayRecibosFaltantes = $faltantes->contains(fn($doc) => Str::contains($doc['name'], 'Recibo') || Str::contains($doc['slug'] ?? '', 'recibo'));
    $hayRecibosPendientes = collect($ayudaSolicitada->documentos_subidos ?? [])->contains(fn($doc, $slug) => Str::contains($slug, 'recibo') && ($doc['estado'] ?? null) === 'pendiente');
    $ayudaId = $ayudaSolicitada->id ?? $ayudaSolicitada->ayuda_id ?? null;
@endphp

<div id="documentos-component-{{ $ayudaId }}" data-ayuda-id="{{ $ayudaId }}">
    @if ($faltantes->isNotEmpty() || $configurados->isNotEmpty() || $hayRecibosPendientes)
        {{-- 📆 Subcomponente Recibos --}}
        <x-ayuda-card.recibos :ayudaSolicitada="$ayudaSolicitada" />

        {{-- 📄 Subcomponente Otros Documentos --}}
        <x-ayuda-card.otros-documentos :ayudaSolicitada="$ayudaSolicitada" />
    @else
        <div class="alert alert-info text-center my-3">
            ✅ No hay documentos requeridos para esta ayuda.
        </div>
    @endif
</div>

