<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tablero extends Model
{
    use HasFactory;
    protected $fillable=['rgistro_id','user_id','estado'];
    public function juego()
    {
        return $this->belongsTo(Partida::class);
    }

    public function player()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getBoardStateAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setBoardStateAttribute($value)
    {
        $this->attributes['tablero_estado'] = json_encode($value);
    }
}
