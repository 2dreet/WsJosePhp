<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Despesa extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('despesas_dao');
    }
    
    public function getAllDespesa() {
        $data = json_decode(file_get_contents('php://input'), true);
        $buscaAvancada = null;
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) && isset($data['pagina']) && $data['pagina'] >= 0) {
            $retorno = $this->despesas_dao->getListaDespesa($data, getDadosTokenJson($data['token'])->id);
            $retorno["token"] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function enviarDespesa() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            if ($data['tipoFuncao'] == 'inserir') {
                $retorno = $this->despesas_dao->inserirDespesa($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else if ($data['tipoFuncao'] == 'alterar') {
                $retorno = $this->despesas_dao->alterarDespesa($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else if ($data['tipoFuncao'] == 'deletar') {
                $retorno = $this->despesas_dao->deletarDespesa($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            }
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }
}
