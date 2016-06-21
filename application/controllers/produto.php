<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Produto extends CI_Controller {

    public function getAllproduto($token) {
        $jwtUtil = new JwtUtil();
        $retorno = null;
        if (isset($token) && $token != null && $jwtUtil->validate($token)) {
            $listaProduto = null;
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $query = $this->db->query("SELECT * FROM produto where ativo = true and id_usuario = " . $dadosToken->id);
            foreach ($query->result() as $row) {
                $produto = array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'observacao' => $row->observacao, 'estoque' => $row->estoque);
                $listaProduto[] = ($produto);
            }

            $retorno = array('token' => $token, 'dados' => $listaProduto);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo (json_encode($retorno));
    }

    public function getProdutoImagem($idProduto, $token) {
        $jwtUtil = new JwtUtil();
        if (isset($token) && $token != null && $jwtUtil->validate($token)) {
            if (isset($idProduto) && $idProduto != null) {
                $dadosToken = json_decode($jwtUtil->decode($token));
                $imagem = "";
                $this->load->database();
                $query = $this->db->query("SELECT imagem FROM produto where ativo = true and id = " . $idProduto . " and id_usuario = " . $dadosToken->id . " limit 1");
                foreach ($query->result() as $row) {
                    $imagem = ($row->imagem);
                }
                header("Content-Type: image/gif;");
                echo ($imagem);
            }
        }
    }

    public function updateProduto() {
        $jwtUtil = new JwtUtil();
        $imagem = file_get_contents($_FILES['file']['tmp_name']);
        $dados = json_decode($_POST['dados']);
        $token = $_POST['token'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            if ($dados != null) {

                $produto = array('descricao' => $dados->descricao, 'valor' => $dados->valor, 'observacao' => $dados->observacao,
                    'estoque' => $dados->estoque, 'id_fornecedor' => $dados->id_fornecedor, 'imagem' => $imagem);

                $dadosToken = json_decode($jwtUtil->decode($token));
                $this->load->database();
                $this->db->where('id', $dados->id);
                $this->db->where('id_usuario', $dadosToken->id);
                $this->db->update('produto', $produto);

                $retorno = array('token' => $token, 'sucesso' => true);
            } else {
                $retorno = array('token' => $token, 'sucesso' => false);
            }
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
