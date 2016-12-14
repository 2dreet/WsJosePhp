<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Fornecedor extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('fornecedor_dao');
    }

    public function getAllfornecedor() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) && isset($data['pagina']) && $data['pagina'] >= 0) {
            $retorno = $this->fornecedor_dao->getListafornecedor($data, getDadosTokenJson($data['token'])->id);
            $retorno["token"] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }
    
    public function enviarFornecedor() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            if ($data['tipoFuncao'] == 'inserir') {
                $retorno = $this->fornecedor_dao->inserirFornecedor($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else if ($data['tipoFuncao'] == 'alterar') {
                $retorno = $this->fornecedor_dao->alterarFornecedor($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else if ($data['tipoFuncao'] == 'deletar') {
                $retorno = $this->fornecedor_dao->deletarFornecedor($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            }
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    

}
