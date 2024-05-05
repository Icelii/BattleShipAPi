<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use App\Mail\ActivationLinkEmail;
use Illuminate\Support\Facades\URL;
use PhpParser\Node\Stmt\TryCatch;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();

        if ($users->count() === 0) {
            return response()->json([
                'result' => false,
                "msg" => "No se encontraron usuarios.",
            ], 404);
        }

        return response()->json([
            'result' => true,
            'msg' => "Datos encontrados",
            'data' => $users
        ], 200);
    }

    public function userInfo()
    {
        if (JWTAuth::user()) {
            $user = JWTAuth::user();
            return response()->json([
                'result' => true,
                'msg' => 'Informacion del usuario.',
                'data' => $user,
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'msg' => 'Usuario no autenticado.',
            ], 401);
        }
    }

    public function show(int $id){
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                "msg" => "Usuario no encontrado.",
            ], 404);
        }

        return response()->json([
            "msg" => "Datos del usuario",
            'data' => $user
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100|string',
            'email' => 'required|max:255|string|email|unique:' . User::class,
            'password' => 'required|max:100|string',
            'isActive' => 'max:100|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'isActive' => $request->isActive ?? 0
        ]);

        $activationLink = URL::temporarySignedRoute('activarUsuario', now()->addHours(24), ['user_id' => $user->id]);
        
        Mail::send('emails.mailactivacion', ['user'=> $user, 'activationLink'=> $activationLink], function ($message)use ($request){ $message->to($request->email)->subject('Activar Cuenta');});

        return response()->json([
            'msg' => 'Se ha registrado',
            'data' => [
                'user' => $user,
            ]
        ]);
    }

    public function login(Request $request)
    {
    
        $user = User::where('email', $request->email)->first();
        if(!$user)
        {
            return response()->json(['status'=>'error', 'error'=>'Usuario no registrado'],400);
        }
        $isActiver = $user->isActive;
    
        if ($isActiver == 1) {
            $codigo = Str::random(6);
            $user->codigo = $codigo;
            $user->save();
            $contenidoCorreo = "Su código de verificación es: $codigo";
    
            Mail::raw($contenidoCorreo, function ($message) use ($request) {
                $message->to($request->email)->subject('Código de verificación');
            });
            
            return response()->json([
                'status' => 'success',
                'msg' => 'Correo electrónico enviado',
                'user' => $user
            ]);
        }
        else
        {
            return response()->json([
                'status' => 'error',
                'error' => 'Cuenta no activada',
            ]);
        }
    }    

    public function codeCheck(Request $request)
    {
        try {
            $authenticatedUser = User::where('email', $request->email)->first();
    
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'codigo' => 'required|size:6|string',
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
    
            $codigo = $authenticatedUser->codigo;
            $codigoSolicitud = $request->codigo;
    
            if ($codigo != $codigoSolicitud) {
                return response()->json([
                    'status' => 'error',
                    'msg' => 'El código ingresado no coincide con el código del usuario'
                ], 400);
            } else {
                $authenticatedUser->codigo = null;
                $authenticatedUser->save();
                $token = JWTAuth::fromUser($authenticatedUser);
                
                return response()->json([
                    'status' => 'success',
                    'msg' => 'Login exitoso',
                    'data' => [   
                        'user' => $authenticatedUser,
                    ],
                    'token' => $token
                ], 200);
            }
            
        } catch (\Exception $e) {
    
            return response()->json([
                'status' => 'error',
                'msg' => 'Error en la verificación del código.',
                'error' => $e->getMessage()
            ], 418);
        }
    }

    public function linkac(Request $request)
    {
        $token= Str::random(60);
        $user =User::where('email', $request->email)->first();
        $user->remember_token=$token;
        $user->save();

        $activationLink=URL::temporarySignedRoute('activarUsuario', now()->addHours(24),['user_id' => $user->id]);
        Mail::raw($activationLink, function ($message) use($request){
            $message->to($request->email)->subject('Activar cuenta');
        });
    }

    public function activaruser($user_id)
    {
        $user= User::where('id', $user_id)->first();
        if($user)
        {
            $isActiver= $user->isActive;
            if($isActiver==1)
            {
                return view('emails.useractive')->with('usuario', $user);
            }
            $user->isActive = 1;
            $user->save();
            return view('emails.activation_link')->with('usuario', $user);
        }
        else
        {
            return view('emails.badactivation')->with('usuario', $user);
        }
    }

    public function logout(Request $request)
    {
        try{
            $user= JWTAuth::user();
            JWTAuth::parseToken()->invalidate();
            return response()->json(['satus'=>'succes','msg'=>'session cerrada correctamente'],200);
        }
        catch(JWTException $e)
        {
            return response()->json(['satus'=>'error','msg'=>'Error al cerrar session'],400);
        }
    }
}
