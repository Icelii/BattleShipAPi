<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partida extends Model
{
    use HasFactory;
    
    protected $table = 'registro';

    protected $fillable = [
        'user_id', 'enemy_id', 'ganador', 'estado', 'turno'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function recipient()
    {
        return $this->belongsTo(User::class, 'enemy_id');
    }
    public function tablero()
    {
        return $this->hasMany(Tablero::class);
    }
}
