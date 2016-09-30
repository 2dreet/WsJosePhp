<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Produto extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('produto_dao');
    }

    public function getAllproduto() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) && isset($data['pagina']) && $data['pagina'] >= 0) {
            $retorno = $this->produto_dao->getListaproduto($data, getDadosTokenJson($data['token'])->id);
            $retorno["token"] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo (json_encode($retorno));
    }

    public function getMovimentacaoProduto() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            if ($data['id'] != null && isset($data['data_inicial']) && isset($data['data_final']) && ( strlen($data['data_inicial']) >= 10 && strlen($data['data_final']) >= 10)) {
                $retorno = $this->produto_dao->getMovimentacaoProdutoByIdProduto($data, getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else {
                $retorno = array('token' => $data['token'], 'dados' => null);
            }
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo (json_encode($retorno));
    }

    public function getProdutoImagem($idProduto, $token) {
        if (isset($token) && $token != null && jwt_validate($token)) {
            if (isset($idProduto) && $idProduto != null) {
                $this->verificaLocalImagens($idProduto, getDadosTokenJson($token)->id);
                $diretorio = 'imagem/' . getDadosTokenJson($token)->id . '/' . $idProduto . '.png';
                header('Content-Type: image/gif'); 
                if (is_file($diretorio)) {
                    echo file_get_contents($diretorio); 
                } else {
                    echo file_get_contents("no-image.png"); 
                }
            }
        }
    }

    public function getProduto() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) && isset($data['idProduto']) && $data['idProduto'] > 0) {
            $retorno = array('token' => $data['token'], 'produto' => $this->produto_dao->getProdutoByIdProduto($data['idProduto'], getDadosTokenJson($data['token'])->id));
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function enviarProduto() {
        $dados = json_decode($_POST['dados']);
        $token = $_POST['token'];
        $funcao = $_POST['tipoFuncao'];
        $imagem = false;
        if ($token != null && jwt_validate($token)) {
            if ($funcao == 'inserir') {
                $retorno = $this->produto_dao->insertProduto($dados, getDadosTokenJson($token)->id);
                $retorno["token"] = $token;
                if (isset($_FILES['imagem']['tmp_name'])) {
                    $this->salvarImagem($retorno['produto']['id'], getDadosTokenJson($token)->id, $_FILES['imagem']['tmp_name']);
                }
            } else if ($funcao == 'alterar') {
                $retorno = $this->produto_dao->updatetProduto($dados, getDadosTokenJson($token)->id);
                $retorno["token"] = $token;
                if (isset($_FILES['imagem']['tmp_name'])) {
                    $this->salvarImagem($retorno['produto']['id'], getDadosTokenJson($token)->id, $_FILES['imagem']['tmp_name']);
                }
            } else if ($funcao == 'deletar') {
                $retorno = $this->produto_dao->deleteProduto($dados, getDadosTokenJson($token)->id);
                $retorno["token"] = $token;
            }
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    private function verificaLocalImagens($produtoID, $usuarioID) {
        if (!is_dir('imagem/')) {
            mkdir('imagem/');
        }
        if (!is_dir('imagem/' . $usuarioID . '/')) {
            mkdir('imagem/' . $usuarioID . '/');
        }
    }

    private function salvarImagem($produtoID, $usuarioID, $file) {
        $this->verificaLocalImagens($produtoID, $usuarioID);
        $uploadfile = 'imagem/' . $usuarioID . '/' . $produtoID . '.png';
        move_uploaded_file($file, $uploadfile);
    }

    public function movimentarProduto() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if ($data['token'] != null && jwt_validate($data['token'])) {
            if (isset($data['dados']) && $data['dados'] != null) {
                $retorno = $this->produto_dao->movimentarProdutoByIdProduto($data, getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else {
                $retorno = array('token' => $data['token'], 'sucesso' => false);
            }
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
