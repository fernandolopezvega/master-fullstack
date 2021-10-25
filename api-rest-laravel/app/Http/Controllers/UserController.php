<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
   $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'Error al subir imagen.'
                    );    

use App\User;
class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Accion de prubas de UserController";
    }

    public function register(Request $request){

      /*   $name = $request->input('name');
        $surname = $request->input('surname');
        return "Accion de registro de usuario: $name $surname"; */

/* ***************************************** */

            //Recoger los datos del usuario por post
            $json = $request->input('json', null);
            //Decodificar json para
            $params =  json_decode($json);  // saca un objeto
            $params_array = json_decode($json, true); //saca un array

            if(!empty($params) && !empty($params_array)){          
                //Limpiar datos
                    $params_array = array_map('trim', $params_array);
                    //Validar datos del usuario 
                    // Laravel Validator
                    $validate = \Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users',
                        'password'=> 'required'
                    ]);
                    if($validate->fails()){
                        //La validacion ha fallado
                        $data = array(
                            'status' => 'error',
                            'code' => 404,
                            'message' => 'El usuario no se ha creado',
                            'errors' => $validate->errors()
                        );                       
                    }else{
                        //Validacion pasada correctamente
                        //Cifrar la contrase;a
                       //$pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);
                       $pwd = hash('sha256', $params->password);
                       //Comprobar si el usuario ya existe (esta duplicado)

                        //Crear usuario
                        $user = new User();
                        $user->name = $params_array['name'];
                        $user->surname = $params_array['surname'];
                        $user->email = $params_array['email'];
                        $user->password = $pwd;
                        $user->role = 'ROLE_USER';
                        
                        // var_dump($user);
                        // die();

                        //Guardar el usuario
                        $user->save();

                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'El usuario se ha creado correctacmente',
                            'user' => $user
                        );
                    }
                }else{
                    $data = array(
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'Los datos enviados no son correctos'
                    ); 
                }
                                

                    return response()->json($data, $data['code']);
           

        }

            public function login(Request $request){
                
                $jwtAuth = new \JwtAuth();

                //Recibir los datos por Post
                $json = $request->input('json', null, true);
                $params = json_decode($json);
                $params_array = json_decode($json, true);

                //Validar esos datos
                $validate = \Validator::make($params_array, [
                    'email' => 'required|email',
                    'password'=> 'required'
                ]);

                if($validate->fails()){
                    //La validacion ha fallado
                    $signup = array(
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'El usuario no se ha logeado',
                        'errors' => $validate->errors()
                    );                       
                }else{
                    //Cifrar la password
                    $pwd = hash('sha256', $params->password);
                    //Devolver token o datos
                    $signup = $jwtAuth->signup($params->email, $pwd);
                    //si recibo un parametro que se llama gettoken
                    if(!empty($params->gettoken)){
                        //true para que devuelva los datos decodificados
                        $signup = $jwtAuth->signup($params->email, $pwd, true);
                    }


                }
                           

                return response()->json($signup, 200);   
           
            }

            public function update(Request $request){
                //Comprobar si el usuario esta identificado
                $token = $request->header('Authorization');
                $jwtAuth = new \JwtAuth();
                $checkToken = $jwtAuth->checkToken($token);

                 //Recoger los datos por post
                  $json = $request->input('json', null);
                 //Decodifico el json para que sea una objeto de PHP
                  $params_array = json_decode($json, true);

                if($checkToken && !empty($params_array)){
                   // echo "<h1>Login Correcto</h1>";
                   //Actualizar usuario                    

                    //Sacar usuario identificado
                    $user = $jwtAuth->checkToken($token, true);
                    //var_dump($user);
                    //die();
                   //Validar los datos
                    $validate = \Validator::make($params_array,[
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users'.$user->sub
                    ]);

                   //Quitar los campos que no quiero actualizar
                    unset($params_array['id']);
                    unset($params_array['role']);
                    unset($params_array['password']);
                    unset($params_array['created_at']);
                    unset($params_array['remember_token']);

                   //Actualizar usuario en DB
                    $user_update = User::where('id', $user->sub)->update($params_array);

                   //Devolver array con resultado
                    $data = array(
                        'code' => 200,
                        'status' => 'success',
                        'user' => $user,
                        'changes' => $params_array
                    );

                }else{                    
                    //echo "<h1>Login InCorrecto</h1>";
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'El usuario no esta identificado'
                    );
                }

               // die();
               return response()->json($data, $data['code']);
            }

            public function upload(Request $request){
                //Recojer datos de peticion
                $image = $request->file('file0');

                //Validacion de imagen
                $validate = \Validator::make($request->all(), [
                  'file0'  => 'required|image|mimes:jpg,jpeg,png,gif'
                ]);
              
                //Guardar imagen
                if(!$image || $validate->fails()){                    
                        $data = array(
                            'code' => 400,
                            'status' => 'error',
                            'message' => 'Error al subir imagen.'
                        );    
                }else{
                    
                    $image_name = time().$image->getClientOriginalName();
                   \Storage::disk('users')->put($image_name, \File::get($image));

                    $data = array(
                        'code' => 200,
                        'status' => 'success',
                        'image' => $image_name
                    );            

                 
                }
               
                //return response($data, $data['code'])->header('Content-Type', 'text/plain');
                return response()->json($data, $data['code']);

            }

            public function getImage($filename){
                $isset = \Storage::disk('users')->exists($filename);
                if($isset){
                    $file = \Storage::disk('users')->get($filename);
                    return new Response($file, 200);
                }else{
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'LA Imgen no existe.'
                    );  

                    return response()->json($data, $data['code']);
                }
            }

            public function detail($id){
                $user = User::find($id);

                if(is_object($user)){
                    $data = array(
                        'code' => 200,
                        'status' => 'successs',
                        'user' => $user
                    );
                }else{
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'El usuario no existe.'
                    );  
                }

                return response()->json($data, $data['code']);
            }
}
