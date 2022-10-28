<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    function getUsuarios()
    {
        $users = User::all();
        
        if(is_object($users)){
            $data = [
                'code' => 200,
                'status' => 'success',
                'users' => $users,
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Ocurrió un error al realizar la búsqueda.',
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function create(Request $request)
    {        
        $data = [
            'code' => 200,
            'status' => 'success',
        ];
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) 
    {
        //return $request;
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email|min:3',
            'password' => 'required|string|min:8', // |confirmed
        ];
        $this->validate($request, $rules);

        try {
            $user = new User();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->save();
    
            if(is_object($user)) {
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Usuario creado satisfactoriamente',
                    'user' => $user,
                ];
            } else {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Se ha producido un error al guardar.',
                ];
            }
            return response()->json($data, $data['code']);
            
        } catch (\Throwable $th) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Se ha producido un error al guardar.'.$th->getMessage(),
            ];
            return response()->json($data, $data['code']);
        }

    }

    public function edit(Request $request)
    {

        $data = [
            'code' => 200,
            'status' => 'success',
        ];
        return response()->json($data, $data['code']);
    }

    public function update(Request $request)
    {
        //return $request;
        //validar request
        $rules = [
            'name' => 'string|max:255',
            'id' => 'exists:users,id',
            'email' => 'string|min:3|unique:users,email,'.$request->input('id'),
            'password' => 'string|nullable|min:8', // |confirmed
        ];
        $this->validate($request, $rules);

        if($request->input('id'))
            $user = User::where('id',$request->input('id'))->first();

        try {
            if(is_object($user)) {
            
                if ($request->input('name')) $user->name = $request->input('name');
                if ($request->input('email')) $user->email = $request->input('email');    
                $user->save();
                
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Usuario editado.',
                    // 'user' => $user->load('perfil'),
                    'user' => $user,
                ];

                return response()->json($data, $data['code']);
            } else {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Se ha producido un error al actualizar.',
                ];
            }
            return response()->json($data, $data['code']);
        } catch (\Throwable $th) {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Error al actualizar.'.$th->getMessage(),
            ];
            return response()->json($data, $data['code']);
        }
             
    }


    public function destroy($id)
    {
        $user = User::where('id',$id)->first();

        if(is_object($user) && ($user->id != auth()->user()->id )) {
            $user->delete();
            $data = [
                'code' => 200,
                'status' => 'success',
                'message' => 'Usuario eliminado satisfactoriamente',
                'user' => $user,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No se encontró el usuario.',
            ];
        }
        return response()->json($data, $data['code']);
    }
}
