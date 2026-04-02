<?php

namespace App\Helpers;

use App\Models\Answer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SimulationHelper
{
    public static function getCurrentUser(): ?User
    {
        if (self::isSimulating()) {
            $simulatedUserId = Session::get('simulating_user_id');

            return $simulatedUserId ? User::find($simulatedUserId) : null;
        }

        return Auth::user();
    }

    public static function getCurrentUserId(): ?int
    {
        $user = self::getCurrentUser();

        return $user ? $user->id : null;
    }

    public static function isSimulating(): bool
    {
        return Session::get('is_simulating', false);
    }

    public static function getOriginalAdmin(): ?User
    {
        if (self::isSimulating()) {
            $originalAdminId = Session::get('original_admin_id');

            return $originalAdminId ? User::find($originalAdminId) : null;
        }

        return null;
    }

    public static function getCurrentUserAnswers($convivienteId = null)
    {
        $userId = self::getCurrentUserId();
        if (! $userId) {
            return collect();
        }

        $query = Answer::where('user_id', $userId);

        if ($convivienteId !== null) {
            $query->where('conviviente_id', $convivienteId);
        } else {
            $query->whereNull('conviviente_id');
        }

        return $query->get();
    }

    public static function getCurrentUserAnswer($questionId, $convivienteId = null)
    {
        $userId = self::getCurrentUserId();
        if (! $userId) {
            return null;
        }

        $query = Answer::where('user_id', $userId)
            ->where('question_id', $questionId);

        if ($convivienteId !== null) {
            $query->where('conviviente_id', $convivienteId);
        } else {
            $query->whereNull('conviviente_id');
        }

        return $query->first();
    }
}
