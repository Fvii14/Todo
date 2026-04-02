@php
    $docsSubsanacion = collect($ayudaSolicitada->subsanacionDocumentos ?? [])->filter(
        fn($doc) => $doc->estado !== 'validado'
    );

    $hayRecibosSubsanacion = $docsSubsanacion->contains(
        fn($doc) => Str::contains($doc->nombre ?? '', 'Recibo') || Str::contains($doc->slug ?? '', 'recibo')
    );
@endphp

@if ($docsSubsanacion->isNotEmpty())
    {{-- 📆 Mostrar recibos --}}
    @if ($hayRecibosSubsanacion)
        <x-ayuda-card.subsanacion-recibos :ayudaSolicitada="$ayudaSolicitada" :docs="$docsSubsanacion->filter(
            fn($doc) => Str::contains($doc->nombre ?? '', 'Recibo') || Str::contains($doc->slug ?? '', 'recibo')
        )" />
    @endif

    {{-- 📄 Mostrar otros documentos --}}
    <x-ayuda-card.subsanacion-otros-documentos :ayudaSolicitada="$ayudaSolicitada" :docs="$docsSubsanacion->filter(
        fn($doc) => !Str::contains($doc->nombre ?? '', 'Recibo') && !Str::contains($doc->slug ?? '', 'recibo')
    )" />
@else
    <div class="alert alert-info text-center my-3">
        ✅ No hay documentos de subsanación requeridos en este momento.
    </div>
@endif
