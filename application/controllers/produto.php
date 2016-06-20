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

            $listaRetorno[] = array('dados' => $listaProduto);
            $listaRetorno[] = array('token' => $token);
            $retorno = $listaRetorno;
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
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
//            $produto = array('descricao' => $dados['descricao'], 'email' => $dados['email'], 'telefone' => $dados['telefone']);
            $produto = array('imagem' => $dados);
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
//            $this->db->where('id', $dados['id']);
            $this->db->where('id_usuario', $dadosToken->id);
            $this->db->update('produto', $produto);
            $retorno = array('token' => $token);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
