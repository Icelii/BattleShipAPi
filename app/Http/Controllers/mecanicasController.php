<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use App\Models\Tablero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class mecanicasController extends Controller
{

    public function show($registroid) {
        $user = JWTAuth::parseToken()->authenticate();

        $registro = Partida::findOrFail($registroid);

        if($registro->estado == 'enCurso' || $registro->estado == 'terminada')
        {
            if($registro->user_id === $user->id || $registro->enemy_id === $user->id)
            {
                $tablero = Tablero::where('user_id', $user->id)->where('registro_id', $registroid)->first();
                return response()->json(['tablero' => $tablero]);
            }
            else{
                return response()->json(['error' => 'Error al mostrar el tablero: no tienes permiso'],400);
            }
        }
    }

    public function vertableronemy($registroid)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $registro = Partida::findOrFail($registroid);

        if($registro->estado == 'enCurso' || $registro->estado == 'terminada'){

            if($registro->user_id == $user->id || $registro->enemy_id == $user->id)
            {
                $enemyid = ($registro->user_id == $user->id) ? $registro->enemy_id : $registro->user_id;
                $tableroenmy = Tablero::where('user_id', $enemyid)->where('registro_id', $registroid)->first();
                
                return response()->json(['tablero' => $tableroenmy]);
            }
            else{
                return response()->json(['error' => 'Error al mostrar el tablero: no tienes permiso'],400);
            }
        }
        else {
            return response()->json(['error' => 'El juego no esta en curso o finalizo'],404);
        }
    }
}
