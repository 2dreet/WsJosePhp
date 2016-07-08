<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Cliente extends CI_Controller {

    public function getAllCliente() {
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

    public function insertCliente() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $dadosToken = json_decode($jwtUtil->decode($token));

            $this->load->database();
            $pessoa = array('nome' => $dados['nome'], 'sobre_nome' => $dados['sobre_nome'], 'sexo' => $dados['sexo'], 'data_cadastro' => date("Y-m-d"), 'data_nascimento' => $dados['data_nascimento'], 'id_usuario' => $dadosToken->id, 'ativo' => '1');
            $this->db->insert('pessoa', $pessoa);

            $pessoa_id = 0;
            $query = $this->db->query("SELECT LAST_INSERT_ID() as id FROM produto WHERE ativo = true AND id_usuario = " . $dadosToken->id . " LIMIT 1");
            foreach ($query->result() as $row) {
                $pessoa_id = $row->id;
            }
            
            $cliente = array('cpf' => $dados['cpf'], 'rg' => $dados['rg'], 'email' => $dados['email'], 'id_pessoa' => $pessoa_id, 'id_usuario' => $dadosToken->id, 'ativo' => '1');
            $this->db->insert('cliente', $cliente);
            
            $telefone = array('telefone' => $dados['telefone'], 'tipo_telefone' => $dados['tipo_telefone'], 'id_pessoa' => $pessoa_id, 'id_usuario' => $dadosToken->id, 'ativo' => '1');
            $this->db->insert('pessoa_telefone', $telefone);
            
            $endereco = array('rua' => $dados['rua'], 'complemento' => $dados['complemento'], 'bairro' => $dados['bairro'],
                'cidade' => $dados['cidade'], 'estado' => $dados['estado'], 'cep' => $dados['cep'],
                'id_usuario' => $dadosToken->id, 'ativo' => '1');
            $this->db->insert('pessoa_endereco', $endereco);

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
