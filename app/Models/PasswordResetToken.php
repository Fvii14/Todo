<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';

    public $timestamps = false;

    protected $primaryKey = 'email'; // 👈 Esto es lo importante

    public $incrementing = false;

    protected $fillable = ['email', 'user_id', 'token', 'created_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
