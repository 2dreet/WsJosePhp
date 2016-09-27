<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Inicio extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('inicio_dao');
    }

    public function getDados() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = "";
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            $retorno = $this->inicio_dao->getDadosIniciais($data['dados'], getDadosTokenJson($data['token'])->id);
            $retorno["token"] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
