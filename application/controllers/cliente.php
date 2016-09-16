<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cliente extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('cliente_dao');
    }

    public function getAllCliente() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) && isset($data['pagina']) && $data['pagina'] >= 0) {
            $retorno = $this->cliente_dao->getListaCliente($data, getDadosTokenJson($data['token'])->id);
            $retorno["token"] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function getCliente() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) && isset($data['idCliente']) && $data['idCliente'] > 0) {
            $retorno = array('token' => $data['token'], 'cliente' => $this->cliente_dao->getClienteByClienteId($data['idCliente'], getDadosTokenJson($data['token'])->id));
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function enviarCliente() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            if ($data['tipoFuncao'] == 'inserir') {
                $retorno = $this->cliente_dao->insertCliente($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else if ($data['tipoFuncao'] == 'alterar') {
                $retorno = $this->cliente_dao->updateCliente($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else if ($data['tipoFuncao'] == 'deletar') {
                $retorno = $this->cliente_dao->deleteCliente($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            }
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
