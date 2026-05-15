<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de Logs de Auditoría.
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'accion',
        'modulo',
        'detalles',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
