<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Conta extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('contas_dao');
    }
    
    public function getAllContas() {
        $data = json_decode(file_get_contents('php://input'), true);
        $buscaAvancada = null;
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) && isset($data['pagina']) && $data['pagina'] >= 0) {
            $retorno = $this->contas_dao->getListaConta($data, getDadosTokenJson($data['token'])->id);
            $retorno["token"] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function enviarContas() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            if ($data['tipoFuncao'] == 'inserir') {
                $retorno = $this->contas_dao->inserirConta($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else if ($data['tipoFuncao'] == 'alterar') {
                $retorno = $this->contas_dao->alterarConta($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else if ($data['tipoFuncao'] == 'deletar') {
                $retorno = $this->contas_dao->deletarConta($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            }
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }
}
