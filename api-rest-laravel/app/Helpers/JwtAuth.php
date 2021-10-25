<?php
namespace App\Helpers;
use Firebase\JWT\JWT;

//Utilizamos la libreria de base de datos de laravel para hacer llamadas a la base de datos y consultas con el query builder
use Illuminate\Support\Facades\DB;
//Incluimos el Modelo y poder trabajar con el ORM
use App\User;

class JwtAuth{

    public $key;
    public function __construct(){
        $this->key = 'esto_es_una_clave_super_sercreta-99887766';
    }

    public function signup($email, $password, $getToken = null){
        //Buscar si existe el usuario con sus credenciales
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();
        //Comprobar si son correctos (objeto)
        $signup = false;
        if(is_object($user)){
            $signup = true;
        }
        //Generar el token con los datos del usuario identificado
        if($signup){
            $token = array(
                'sub'       =>  $user->id,
                'email'     =>  $user->email,
                'name'      =>  $user->name,
                'surname'   =>  $user->surname,
                'iat'       =>  time(),
                'exp'       =>  time() + (7 * 24 * 60 * 60)
            );
            //Metodo estatico encode   HS256 es el algoritmo de codificacion
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

             //Devolver los datos decodificados o el token, en funcion de un parametro
             if(is_null($getToken)){
                $data =  $jwt;
             }else{
                $data =  $decoded;
             }
        }else{
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrecto.'
            );
        }

        return $data;
    }

    public function checkToken($jwt, $getIdentity=false){
        $auth = false;
       // $decoded = false;
        try{
           $jwt = str_replace('"', '', $jwt);

           $decoded = JWT::decode($jwt, $this->key, ['HS256']);
         /*   var_dump($decoded);
           die(); */
           
        }catch(\UnexpectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }
        

        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            /* var_dump($decoded->sub);
            die(); */
            $auth = true;
          //  $getIdentity=true;
           /*  var_dump($getIdentity);
            die(); */
           
              

        }else{
           
            $auth = false;
        }


        if($getIdentity){ 
            /*   var_dump($decoded->sub);
              die(); */
            return $decoded;
          }
          return $auth;
        
     /*    var_dump($decoded);
    die(); */
      
       
       
         
           
    }
}
