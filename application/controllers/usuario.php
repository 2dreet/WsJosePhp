<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Usuario extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('usuario_dao');
    }

    public function logar() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = "";
        if (isset($data['dados']) && $data['dados'] != null) {
            $retorno = $this->usuario_dao->logarSistema($data);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo ($retorno);
    }

    public function getUsuario() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = "";
        if ($data['token'] != null && jwt_validate($data['token'])) {
            $retorno = $this->usuario_dao->getUsuarioById($data);
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
