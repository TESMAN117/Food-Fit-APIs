<?php
require __DIR__.'/../classes/JwtHandler.php';
class Auth extends JwtHandler{

    protected $db;
    protected $headers;
    protected $token;
    public $idusuario;
    public function __construct($db,$headers) {
        parent::__construct();
        $this->db = $db;
        $this->headers = $headers;
    }

    public function isAuth(){
        if(array_key_exists('Authorization',$this->headers) && !empty(trim($this->headers['Authorization']))):
            $this->token = explode(" ", trim($this->headers['Authorization']));
            if(isset($this->token[1]) && !empty(trim($this->token[1]))):
                
                $data = $this->_jwt_decode_data($this->token[1]);

                if(isset($data['auth']) && isset($data['data']->user_id) && $data['auth']):
                    $user = $this->fetchUser($data['data']->user_id);
                    
                    return $user;

                else:
                    return null;

                endif; // End of isset($this->token[1]) && !empty(trim($this->token[1]))
                
            else:
                return null;

            endif;// End of isset($this->token[1]) && !empty(trim($this->token[1]))

        else:
            return null;

        endif;
    }

    protected function fetchUser($user_id){
        try{
            $fetch_user_by_id = "SELECT
            usuarios.id
            ,usuarios.vch_Nick
            ,usuarios.vch_Password
            ,usuarios.vch_Email
            ,usuarios.CLV_Persona
            ,personas.id AS idpersona
            ,personas.vch_Nombres
            ,personas.vch_A_Paterno
            ,personas.vch_A_Materno
            ,personas.vch_Direccion
            ,personas.vch_Telefono
            FROM usuarios
            INNER JOIN personas ON usuarios.CLV_Persona = personas.id
            WHERE usuarios.id =:id";
            
            $query_stmt = $this->db->prepare($fetch_user_by_id);
            $query_stmt->bindValue(':id', $user_id,PDO::PARAM_INT);
            $query_stmt->execute();
            $this->idusuario = $user_id;
            
           
            if($query_stmt->rowCount()):
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                
                return [
                    'success' => 1,
                    'status' => 200,
                    'user' => $row
                ];
            else:
                return null;
            endif;
        }
        catch(PDOException $e){
            return null;
        }
    }

    public function isId(){
        echo  $this->idusuario;
    }
}