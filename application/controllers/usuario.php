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
    
    public function trocaSenha() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retornoWs = "";
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) &&
                isset($data['dados']) && $data['dados'] != null) {
            $dados = $data['dados'];
            if (isset($dados['nova_senha']) && $dados['nova_senha'] != null && trim($dados['nova_senha']) != "") {
                if (isset($dados['nova_senha']) && $dados['nova_senha'] != null && trim($dados['nova_senha']) != "") {
                    if (isset($dados['confirma_nova_senha']) && $dados['confirma_nova_senha'] != null && trim($dados['confirma_nova_senha']) != "") {
                        $retorno = $this->usuario_dao->verificaUsuarioByIdSenha($data);
                        if ($retorno == true) {
                            if ($dados['nova_senha'] == $dados['confirma_nova_senha']) {
                                $retornoWs = $this->usuario_dao->alterarSenhaUsuarioById($data);
                            } else {
                                $retornoWs = array('token' => $data['token'], 'msgErro' => 'Senha não confere!', 'focus' => '#confirma_nova_senha');
                            }
                        } else {
                            $retornoWs = array('token' => $data['token'], 'msgErro' => 'Senha atual está incorreta!', 'focus' => '#senha_atual');
                        }
                    } else {
                        $retornoWs = array('token' => $data['token'], 'msgErro' => 'Informar Confirma Nova senha!', 'focus' => '#confirma_nova_senha');
                    }
                } else {
                    $retornoWs = array('token' => $data['token'], 'msgErro' => 'Informar Nova senha!', 'focus' => '#nova_senha');
                }
            } else {
                $retornoWs = array('token' => $data['token'], 'msgErro' => 'Informar Senha atual!', 'focus' => '#senha_atual');
            }
        } else {
            $retornoWs = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retornoWs);
    }

}
