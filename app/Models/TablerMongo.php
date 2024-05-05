<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Tablero extends Model
{
    use HasFactory;
    protected $connection='mongodb';
    protected $coleccion='tablero';
    
    protected $fillable=['registro_id','user_id','tablero_estado'];
    public function juego()
    {
        return $this->belongsTo(Partida::class);
    }

    public function player()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function obtenercelda($x, $y)
    {
        return $this->tablero_estado[$x][$y];
    }
    public function enviarcelda($x, $y, $state)
    {
        $this->tablero_estado[$x][$y]= $state;
        $this->save();
    }
}
