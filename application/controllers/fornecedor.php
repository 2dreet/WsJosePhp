<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Fornecedor extends CI_Controller {

    public function getAllfornecedor() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $pagina = $data['pagina'];
        $limit = $data['limit'];
        $buscaAvancada = null;
        $retorno = null;
        if (isset($token) && $token != null && $jwtUtil->validate($token) && isset($pagina) && $pagina >= 0) {
            if ($pagina > 0) {
                $pagina = $pagina * $limit;
            }
            $where = "";
            if (isset($data['buscaAvancada'])) {
                $buscaAvancada = $data['buscaAvancada'];

                if (isset($buscaAvancada['descricao']) && $buscaAvancada['descricao'] != null && trim($buscaAvancada['descricao']) != "") {
                    $where .= " AND descricao like '%" . $buscaAvancada['descricao'] . "%'";
                }

                if (isset($buscaAvancada['email']) && $buscaAvancada['email'] != null && trim($buscaAvancada['email']) != "") {
                    $where .= " AND email like '%" . $buscaAvancada['email'] . "%'";
                }

                if (isset($buscaAvancada['telefone']) && $buscaAvancada['telefone'] != null && trim($buscaAvancada['telefone']) != "") {
                    $where .= " AND telefone like '%" . $buscaAvancada['telefone'] . "%'";
                }
            }

            if (isset($data['buscaDescricao'])) {
                if (isset($data['buscaDescricao']) && $data['buscaDescricao'] != null && trim($data['buscaDescricao']) != "") {
                    $where .= " AND descricao like '%" . $data['buscaDescricao'] . "%'";
                }
            }
            $listaFornecedor = null;
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $query = $this->db->query("SELECT * FROM fornecedor where ativo = true " . $where . " and id_usuario = " . $dadosToken->id . " LIMIT " . $pagina . "," . $limit);
            foreach ($query->result() as $row) {
                $fornecedor = array('id' => $row->id, 'descricao' => $row->descricao, 'email' => $row->email, 'telefone' => $row->telefone);
                $listaFornecedor[] = $fornecedor;
                unset($fornecedor);
            }

            $totalRegistro = 0;
            $query = $this->db->query("SELECT count(*) as count FROM fornecedor where ativo = true " . $where . " and id_usuario = " . $dadosToken->id);
            foreach ($query->result() as $row) {
                $totalRegistro = $row->count;
            }

            $retorno = array('token' => $token, 'dados' => $listaFornecedor, 'totalRegistro' => $totalRegistro);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function enviarFornecedor() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            if ($data['tipoFuncao'] == 'inserir') {
                $retorno = $this->inserirFornecedor($data['dados'], $token);
            } else if ($data['tipoFuncao'] == 'alterar') {
                $retorno = $this->alterarFornecedor($data['dados'], $token);
            } else if ($data['tipoFuncao'] == 'deletar') {
                $retorno = $this->deletarFornecedor($data['dados'], $token);
            }
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    function deletarFornecedor($dados, $token) {
        $jwtUtil = new JwtUtil();
        $fornecedor = array('ativo' => '0');
        $dadosToken = json_decode($jwtUtil->decode($token));
        $this->load->database();
        $this->db->where('id', $dados['id']);
        $this->db->where('id_usuario', $dadosToken->id);
        $this->db->update('fornecedor', $fornecedor);
        $retorno = array('token' => $token, 'msgRetorno' => 'Deletado com sucesso!');
        return ($retorno);
    }

    function inserirFornecedor($dados, $token) {
        $jwtUtil = new JwtUtil();
        $dadosToken = json_decode($jwtUtil->decode($token));
        $fornecedor = array('descricao' => $dados['descricao'], 'email' => $dados['email'], 'telefone' => $dados['telefone'], 'id_usuario' => $dadosToken->id, 'ativo' => '1');
        $this->load->database();
        $this->db->insert('fornecedor', $fornecedor);
        $retorno = array('token' => $token, 'msgRetorno' => 'Cadastrado com sucesso!');
        return ($retorno);
    }

    function alterarFornecedor($dados, $token) {
        $jwtUtil = new JwtUtil();
        $fornecedor = array('descricao' => $dados['descricao'], 'email' => $dados['email'], 'telefone' => $dados['telefone']);
        $dadosToken = json_decode($jwtUtil->decode($token));
        $this->load->database();
        $this->db->where('id', $dados['id']);
        $this->db->where('id_usuario', $dadosToken->id);
        $this->db->update('fornecedor', $fornecedor);
        $retorno = array('token' => $token, 'msgRetorno' => 'Alterado com sucesso!');
        return ($retorno);
    }

}
