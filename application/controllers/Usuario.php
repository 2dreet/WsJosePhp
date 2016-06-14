<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Usuario extends CI_Controller {
    
    public function logar() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = json_encode(array('id' => 1));
        $token = $jwtUtil->encode($token);
        $token = json_encode(array('token' => $token));
        header('Content-Type: application/json; charset=utf-8');
        echo $token;
    }
}
