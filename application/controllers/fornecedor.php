<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Fornecedor extends CI_Controller {

    public function getAllfornecedor() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();

        if ($data['token'] != null && $jwtUtil->decode($data['token'])) {
            $this->load->database();
            $query = $this->db->query("SELECT * FROM fornecedor where ativo = true and id_usuario = 1");
            foreach ($query->result() as $row) {
                //Obtem o usuario
//            $queryUsuario = $this->db->query("SELECT * FROM usuario where id = " . $row->id_usuario);
//            if ($queryUsuario->num_rows() > 0) {
//                $rowUsuario = $queryUsuario->row();
//            }
                //Obtem a lista de todos os fornecedores
                $fornecedor = array('id' => $row->id, 'descricao' => $row->descricao, 'email' => $row->email, 'telefone' => $row->telefone);
                $listaFornecedor[] = $fornecedor;
            }
            header('Content-Type: application/json; charset=utf-8');
            $listaRetorno[] = array('dados' => $listaFornecedor);
            $listaRetorno[] = array('token' => $data['token']);
            echo json_encode($listaRetorno);
        } else {
            echo json_encode(array('token' => false));
        }
    }

    public function getFornecedor() {
        
    }

    public function newFornecedor() {
        
    }

    public function updateFornecedor() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
//        if ($token != null && $jwtUtil->decode($token)) {
        if ($token != null && $jwtUtil->decode($token)) {
            $fornecedor = array('descricao' => $dados['descricao'], 'email' => $dados['email'], 'telefone' => $dados['telefone']);

            $this->load->database();
            $this->db->where('id', $dados['id']);
            $this->db->update('fornecedor', $fornecedor);

            $retorno = array('token' => $token);
        } else {
            $retorno = array('token' => false);
        }
        
        header('Content-Type: application/json; charset=utf-8');
//        echo json_encode(array('token' => $token));
        echo json_encode($retorno);
    }

    public function removeFornecedor() {
        
    }

}
