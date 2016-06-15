<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Fornecedor extends CI_Controller {

    public function getAllfornecedor($token) {
        $jwtUtil = new JwtUtil();
        $retorno = null;
        if (isset($token) && $token != null && $jwtUtil->validate($token)) {
            $listaFornecedor = null;
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $query = $this->db->query("SELECT * FROM fornecedor where ativo = true and id_usuario = " . $dadosToken->id);
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
            $listaRetorno[] = array('token' => $token);
            $retorno = $listaRetorno;
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function getFornecedor() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $retorno = null;

        $token = $data['token'];
        $valorBusca = $data['valor_busca'];
        if ($token != null && $jwtUtil->validate($token)) {
            $listaFornecedor = null;
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $sql = "SELECT * FROM fornecedor where ativo = true and id_usuario = " . $dadosToken->id;
            if ($valorBusca != null) {
                $valorBusca = str_replace("%20", " ", $valorBusca);
                if (trim($valorBusca) != "") {
                    $sql .= " AND (";
                    $sql .= " descricao like '%" . $valorBusca . "%' ";
                    $sql .= " OR email like '%" . $valorBusca . "%' ";
                    $sql .= " OR telefone like '%" . $valorBusca . "%' ";
                    $sql .= " )";
                }
            }
            $query = $this->db->query($sql);
            foreach ($query->result() as $row) {
                $fornecedor = array('id' => $row->id, 'descricao' => $row->descricao, 'email' => $row->email, 'telefone' => $row->telefone);
                $listaFornecedor[] = $fornecedor;
            }
            header('Content-Type: application/json; charset=utf-8');
            $listaRetorno[] = array('dados' => $listaFornecedor);
            $listaRetorno[] = array('token' => $token);
            $retorno = $listaRetorno;
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function updateFornecedor() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $fornecedor = array('descricao' => $dados['descricao'], 'email' => $dados['email'], 'telefone' => $dados['telefone']);
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $this->db->where('id', $dados['id']);
            $this->db->where('id_usuario', $dadosToken->id);
            $this->db->update('fornecedor', $fornecedor);
            $retorno = array('token' => $token);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function insertFornecedor() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $dadosToken = json_decode($jwtUtil->decode($token));
            $fornecedor = array('descricao' => $dados['descricao'], 'email' => $dados['email'], 'telefone' => $dados['telefone'], 'id_usuario' => $dadosToken->id, 'ativo' => '1');
            $this->load->database();
            $this->db->insert('fornecedor', $fornecedor);
            $retorno = array('token' => $token);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function deleteFornecedor() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $fornecedor = array('ativo' => '0');
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $this->db->where('id', $dados['id']);
            $this->db->where('id_usuario', $dadosToken->id);
            $this->db->update('fornecedor', $fornecedor);
            $retorno = array('token' => $token);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
