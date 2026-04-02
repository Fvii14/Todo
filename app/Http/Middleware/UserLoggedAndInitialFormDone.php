<?php

namespace App\Http\Middleware;

use App\Helpers\SimulationHelper;
use App\Models\Question;
use Closure;
use Illuminate\Http\Request;

class UserLoggedAndInitialFormDone
{
    public function handle(Request $request, Closure $next)
    {
        $user = SimulationHelper::getCurrentUser();

        if (! $user) {
            return redirect()->route('login');
        }

        if (SimulationHelper::isSimulating()) {
            return $next($request);
        }

        $question = cache()->rememberForever('slug_fecha_formulario_inicial', function () {
            return Question::where('slug', 'fecha_formulario_inicial')->first();
        });

        $hasAnswer = $user->answers()->where('question_id', $question->id)->exists();

        if (! $hasAnswer) {
            return redirect()->route('registercollector');
        }

        return $next($request);
    }
}
