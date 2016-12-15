<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Fornecedor_dao extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function getListafornecedor($data, $idUsuario) {
        $pagina = $data['pagina'];
        $limit = $data['limit'];
        $buscaAvancada = null;
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
        $query = $this->db->query("SELECT * FROM fornecedor where ativo = true " . $where . " and id_usuario = " . $idUsuario . " LIMIT " . $pagina . "," . $limit);
        foreach ($query->result() as $row) {
            $fornecedor = array('id' => $row->id, 'descricao' => $row->descricao, 'email' => $row->email, 'telefone' => $row->telefone, 'porcentagem' => $row->porcentagem);
            $listaFornecedor[] = $fornecedor;
            unset($fornecedor);
        }

        $totalRegistro = 0;
        $query = $this->db->query("SELECT count(*) as count FROM fornecedor where ativo = true " . $where . " and id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $totalRegistro = $row->count;
        }
        return array('dados' => $listaFornecedor, 'totalRegistro' => $totalRegistro);
    }

    public function getPorcentagem($fornecedorID) {
        $query = $this->db->query("SELECT * FROM fornecedor where id = " . $fornecedorID);
        foreach ($query->result() as $row) {
            return $row->porcentagem;
        }
        return 0;
    }
    
    public function getPorcentagemByIdProduto($produtoID) {
        $query = $this->db->query("SELECT porcentagem FROM produto inner join fornecedor on produto.id_fornecedor = fornecedor.id where produto.id = " . $produtoID);
        foreach ($query->result() as $row) {
            return $row->porcentagem;
        }
        return 0;
    }

    function deletarFornecedor($dados, $idUsuario) {
        $fornecedor = array('ativo' => '0');
        $this->db->where('id', $dados['id']);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('fornecedor', $fornecedor);
        return array('msgRetorno' => 'Deletado com sucesso!');
    }

    function inserirFornecedor($dados, $idUsuario) {
        $fornecedor = array('descricao' => $dados['descricao'], 'email' => $dados['email'], 'telefone' => $dados['telefone'], 'porcentagem' => $dados['porcentagem'], 'id_usuario' => $idUsuario, 'ativo' => '1');
        $this->db->insert('fornecedor', $fornecedor);
        return array('msgRetorno' => 'Cadastrado com sucesso!');
    }

    function alterarFornecedor($dados, $idUsuario) {
        $fornecedor = array('descricao' => $dados['descricao'], 'email' => $dados['email'], 'telefone' => $dados['telefone'], 'porcentagem' => $dados['porcentagem']);
        $this->db->where('id', $dados['id']);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('fornecedor', $fornecedor);
        return array('msgRetorno' => 'Alterado com sucesso!');
    }

}
