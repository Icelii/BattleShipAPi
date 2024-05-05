<?php

namespace App\Http\Controllers;

use App\Events\Buscarpartida;
use App\Events\movimiento as EventsMovimiento;
use App\Events\terminarparida;
use App\Events\turno;
use App\Models\Movimiento;
use App\Models\Partida;
use App\Models\Tablero;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


class MatchController extends Controller
{
    public function CrearPartida()
{
    $user = JWTAuth::parseToken()->authenticate();
    $partidaPendiente = Partida::where('user_id', $user->id)
                                ->whereIn('estado', ['esperando', 'enCurso'])
                                ->first();

    if ($partidaPendiente) {
        return response()->json(['message' => 'Ya estás en una partida pendiente', 'game_id' => $partidaPendiente]);
    }

    $partidaPendiente = Partida::where('enemy_id', $user->id)
                                ->where('estado', 'enCurso')
                                ->first();

    if ($partidaPendiente) {
        return response()->json(['message' => 'Ya estás en una partida pendiente', 'game_id' => $partidaPendiente]);
    }

    $partidaPendiente = Partida::where('estado', 'esperando')
                                ->whereNull('enemy_id')
                                ->first();

    if ($partidaPendiente) {
        $partidaPendiente->enemy_id = $user->id;
        $partidaPendiente->estado = 'enCurso';
        $partidaPendiente->turno = $partidaPendiente->enemy_id;
        $partidaPendiente->save();

        $nameTurno = User::find($partidaPendiente->turno);
        event(new Buscarpartida($partidaPendiente->id, $user->id, $nameTurno->name));
        event(new turno($partidaPendiente->turno, $nameTurno->name));

        $tablero = new Tablero();
        $tablero->registro_id = $partidaPendiente->id;
        $tablero->user_id = $user->id;
        $tablero->tablero_estado = json_encode($this->generarbarcos());
        $tablero->save();

        return response()->json(['message' => 'Te has unido a una partida pendiente como jugador 2', 'game_id' => $partidaPendiente, 'turnoName' => $nameTurno]);
    } else {
        $nuevojuego = new Partida();
        $nuevojuego->user_id = $user->id;
        $nuevojuego->estado = 'esperando';
        $nuevojuego->save();

        $tablero = new Tablero();
        $tablero->registro_id = $nuevojuego->id;
        $tablero->user_id = $user->id;
        $tablero->tablero_estado = json_encode($this->generarbarcos());
        $tablero->save();

        return response()->json(['message' => 'Has creado una nueva partida, espera a que alguien se una', 'tablero' => $nuevojuego]);
    }
}
    
    public function movimiento(Request $request, $registroid)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $partida = Partida::findOrFail($registroid);

        if($partida->estado == 'terminada')
        {
            return response()->json(['error'=>'El juego ha finalizado'],400);
        }

        if($partida->turno != $user->id)
        {
            return response()->json(['error'=>'No es tu turno'],403);
        }

        $enemyid = ($partida->user_id == $user->id) ? $partida->enemy_id: $partida->user_id;

        $tableroenemy= Tablero::where('user_id', $enemyid)->where('registro_id', $registroid)->first();

        if(!$tableroenemy)
        {
            return response()->json(['error' => 'No se encontró el tablero del oponente:', 'oponente' => $enemyid, $tableroenemy], 400);
        }

        $estadoTablero=json_decode($tableroenemy->tablero_estado, true);

        $x= $request->input('x');
        $y= $request->input('y');

        if (!isset($estadoTablero[$x][$y])) {
            return response()->json(['error' => 'Coordenadas inválidas'], 400);
        }

        $cellState = $estadoTablero[$x][$y];

        if($cellState == 'F' || $cellState == 'K'){
            return response()->json(['error' => 'Celda ya atacada'], 400);
        } 

        if($this->barcohit($estadoTablero, $x, $y)){
            $estadoTablero[$x][$y] = 'K';
            $message = '¡Has golpeado un barco!';
            event(new EventsMovimiento($registroid, $user->id, $x, $y));
        } else{
            $estadoTablero[$x][$y] = 'F';
            $message = 'Solo hay agua en esta posición.';
            Event(new EventsMovimiento($registroid, $user->id, $x, $y));

            $partida->turno = ($partida->turno == $partida->user_id) ? $partida->enemy_id : $partida->user_id;
            $partida->save();

            $nameTurno = User::find($partida->turno);

            event(new turno($partida->turno, $nameTurno->name));
        }

        $tableroenemy->tablero_estado = json_encode($estadoTablero);
        $tableroenemy->save();

        $user = JWTAuth::parseToken()->authenticate();
        $move = new Movimiento();
        $move->registro_id = $partida->id;
        $move->user_id = $user->id;
        $move->x = $x;
        $move->y = $y;
        $move->save();

        event(new EventsMovimiento($registroid, $user->id, $x, $y));

        if ($this->allShips($estadoTablero)) {
            $ganador = $partida->turno;
            $perdedor = ($ganador == $partida->user_id) ? $partida->enemy_id : $partida->user_id;

            $partida->estado = 'terminada';
            $partida->ganador = $ganador;
            $partida->save();

            $nameTurno = User::find($partida->turno);
            event(new terminarparida($partida->id, $ganador, $nameTurno->name));

            if ($ganador == $user->id) {
                $winMessage = '¡Felicidades! Has hundido todos los barcos del oponente. ¡Has ganado!';
                $loseMessage = '¡El oponente ha hundido todos tus barcos! ¡Has perdido!';
            } else {
                $winMessage = '¡El oponente ha hundido todos tus barcos! ¡Has perdido!';
                $loseMessage = '¡Felicidades! Has hundido todos los barcos del oponente. ¡Has ganado!';
            }

            return response()->json(['message' => $winMessage, 'ganador' => $ganador, 'perdedor' => $perdedor]);
        }

        return response()->json(['message' => $message]);
    }

    private function generarbarcos()
    {
        $rows = 8;
        $cols = 5;

        $estadoTablero = [];
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                $estadoTablero[$i][$j] = 'A';
            }
        }
        $numShips = 15;
        for ($s = 0; $s < $numShips; $s++) {
            $x = rand(0, $rows - 1);
            $y = rand(0, $cols - 1);

            if ($estadoTablero[$x][$y] === 'A') {
                $estadoTablero[$x][$y] = 'B';
            } else {
                $s--;
            }
        }

        return $estadoTablero;
    }

    private function barcohit($estadoTablero, $x, $y)
    {
        return $estadoTablero[$x][$y] === 'B';
    }

    private function allShips($estadoTablero)
    {
        foreach ($estadoTablero as $row) {
            foreach ($row as $cell) {
                if ($cell === 'B') {
                    return false;
                }
            }
        }
        return true;
    }
    
    public function registro()
    {
        $user = JWTAuth::parseToken()->authenticate();
    
        $partidas = Partida::where('estado', 'terminada')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('enemy_id', $user->id);
            })
            ->get();
    
        if ($partidas->isEmpty()) {
            return response()->json(['message' => 'No tienes partidas jugadas']);
        }
    
        $partidasConUsuarios = $partidas->map(function ($partida) {
            $usuario1 = User::find($partida->user_id);
            $usuario2 = User::find($partida->enemy_id);
            $ganador = User::find($partida->ganador);
    
            $fechaFormateada = Carbon::parse($partida->updated_at)->format('Y-m-d H:i:s');
    
            return [
                'jugador_1' => $usuario1 ? $usuario1->name : 'Desconocido',
                'jugador_2' => $usuario2 ? $usuario2->name : 'Desconocido',
                'ganador' => $ganador ? $ganador->name : 'Sin Ganador',
                'estado' => $partida->estado,
                'fecha' => $fechaFormateada
            ];
        });
    
        return response()->json(['partidas' => $partidasConUsuarios]);
    }
}
