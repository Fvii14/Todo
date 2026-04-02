<style>
    .hidden-by-js {
        display: none !important;
    }
</style>


<div>
    <div class="alert alert-info text-center my-2 py-2">
        ℹ️ No has registrado ningún conviviente para esta ayuda. Si necesitas añadirlos, por favor haz click en el botón de abajo.
    </div>

    <div class="mt-3">

        <form id="formPreFormConviviente-{{ $ayudaSolicitada->id }}" method="POST"
            action="{{ route('storeQuestionPreConviviente') }}">

            @csrf

            <input type="hidden" name="ayuda_solicitada_id" value="{{ $ayudaSolicitada->id }}">

            <div class="row g-3">

                @foreach ($preguntasPreForm as $index => $question)
                    <div class="row g-3 question-item" data-id="{{ $question['id'] }}"
                        id="question-{{ $ayudaSolicitada->id }}-{{ $index }}">


                        <x-form-question :question="$question" />
                    </div>
                @endforeach

            </div>

            <button type="submit" class="btn btn-primary mt-2">Guardar</button>
        </form>

        <div id="respuestaConviviente-{{ $ayudaSolicitada->id }}" class="mt-3"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const prefix = "{{ $ayudaSolicitada->id }}";
        const q0 = document.querySelector('#question-' + prefix + '-0');
        const q1 = document.querySelector('#question-' + prefix + '-1');
        const form = document.querySelector('#formPreFormConviviente-' + prefix);
        const respuestaDiv = document.querySelector('#respuestaConviviente-' + prefix);
        const submitButton = form.querySelector('button[type="submit"]');

        if (!q0 || !form || !submitButton) return;

        // Ocultar segunda pregunta por defecto
        if (q1) q1.classList.add('hidden-by-js');

        const inputs = q0.querySelectorAll('input, select, textarea');

        function getValue() {
            for (const input of inputs) {
                if (input.type === "radio" && !input.checked) continue;
                if (input.value !== "") return parseInt(input.value) || 0;
            }
            return 0;
        }

        function actualizarEstado() {
            const valor = getValue();

            // Mostrar u ocultar segunda pregunta
            if (q1) {
                if (valor > 1) q1.classList.remove('hidden-by-js');
                else q1.classList.add('hidden-by-js');
            }

            // Deshabilitar o habilitar submit
            if (valor > 1) {
                submitButton.disabled = false;
                respuestaDiv.innerHTML = '';
                inputs.forEach(input => input.classList.remove('is-invalid'));
            } else {
                submitButton.disabled = true;
                respuestaDiv.innerHTML = `
                <div class="alert alert-danger mt-2">
                    ⚠️ Has indicado que no vives solo, por favor indica un número de convivientes mayor a 1.
                </div>
            `;
            }
        }

        // Escuchar cambios en los inputs
        inputs.forEach(input => {
            input.addEventListener('input', actualizarEstado);
            input.addEventListener('change', actualizarEstado);
        });

        actualizarEstado(); // estado inicial

        form.addEventListener('submit', function(e) {
            const valor = getValue();
            if (valor <= 1) {
                e.preventDefault();
                inputs.forEach(input => input.classList.add('is-invalid'));
                inputs[0].focus();
                return false;
            }
        });
    });
</script>
