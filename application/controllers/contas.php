<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Contas extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('contas_dao');
        $this->load->model('arquivo_dao');
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
        $retorno = null;
        if (isset($_POST['token']) && $_POST['token'] != null && jwt_validate($_POST['token'])) {
            $dados = json_decode($_POST['dados']);
            $funcao = $_POST['tipoFuncao'];
            $token = $_POST['token'];
            $usuarioID = getDadosTokenJson($token)->id;

            if ($funcao == 'inserir') {
                $contaID = $this->contas_dao->inserirConta($dados, $usuarioID);
                $retorno["token"] = $token;

                if (isset($_FILES['arquivo']['tmp_name'])) {
                    $this->arquivo_dao->upload($_FILES['arquivo'], $usuarioID, $contaID);
                }
            } else if ($funcao == 'alterar') {
                $contaID = $this->contas_dao->alterarConta($dados, $usuarioID);
                $retorno["token"] = $token;

                if (isset($_FILES['arquivo']['tmp_name'])) {
                    if(isset($dados['arquivoBanco'])) {
                        $this->arquivo_dao->deletarArquivo($dados['arquivoBanco']['idArquivo'], $usuarioID, $dados['arquivoBanco']['nomeArquivo']);
                    }
                    $this->arquivo_dao->upload($_FILES['arquivo'], $usuarioID, $contaID);
                }
            } else if ($funcao == 'deletar') {
                $retorno = $this->contas_dao->deletarConta($dados, $usuarioID);
                $retorno["token"] = $token;
            }
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
