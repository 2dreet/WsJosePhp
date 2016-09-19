<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Despesas_dao extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function getListaDespesa($data, $idUsuario) {
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

            if (isset($buscaAvancada['dataInicio']) && $buscaAvancada['dataInicio'] != null && trim($buscaAvancada['dataInicio']) != "") {
                $where .= " AND date(data_lancamento) >= date('" . substr($buscaAvancada['dataInicio'], 0, 10) . "') ";
            }

            if (isset($buscaAvancada['dataFim']) && $buscaAvancada['dataFim'] != null && trim($buscaAvancada['dataFim']) != "") {
                $where .= " AND date(data_lancamento) <= date('" . substr($buscaAvancada['dataFim'], 0, 10) . "') ";
            }
        }

        if (isset($data['buscaDescricao'])) {
            if (isset($data['buscaDescricao']) && $data['buscaDescricao'] != null && trim($data['buscaDescricao']) != "") {
                $where .= " AND descricao like '%" . $data['buscaDescricao'] . "%'";
            }
        }
        $listaDespesa = null;
        $query = $this->db->query("SELECT * FROM despesas where ativo = true " . $where . " and id_usuario = " . $idUsuario . " LIMIT " . $pagina . "," . $limit);
        foreach ($query->result() as $row) {
            $data = substr($row->data_lancamento, 0, 10);
            $despesa = array('id' => $row->id, 'descricao' => $row->descricao, 'data_lancamento' => $data, 'valor' => $row->valor);
            $listaDespesa[] = $despesa;
            unset($despesa);
        }

        $totalRegistro = 0;
        $valorTotal = 0;
        $query = $this->db->query("SELECT count(*) as count, sum(valor) as valorTotal  FROM despesas where ativo = true " . $where . " and id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $totalRegistro = $row->count;
            $valorTotal = $row->valorTotal;
        }

        return array('dados' => $listaDespesa, 'totalRegistro' => $totalRegistro, 'valorTotal' => $valorTotal);
    }

    function deletarDespesa($dados, $idUsuario) {
        $despesas = array('ativo' => '0');
        $this->db->where('id', $dados['id']);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('despesas', $despesas);
        return array('msgRetorno' => 'Deletado com sucesso!');
    }

    function inserirDespesa($dados, $idUsuario) {
        $despesas = array('descricao' => $dados['descricao'], 'valor' => $dados['valor'], 'data_lancamento' => substr($dados['data_lancamento'], 0, 10), 'id_usuario' => $idUsuario, 'ativo' => '1');
        $this->db->insert('despesas', $despesas);
        return array('msgRetorno' => 'Cadastrado com sucesso!');
    }

    function alterarDespesa($dados, $idUsuario) {
        $despesas = array('descricao' => $dados['descricao'], 'valor' => $dados['valor'], 'data_lancamento' => substr($dados['data_lancamento'], 0, 10));
        $this->db->where('id', $dados['id']);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('despesas', $despesas);
        return array('msgRetorno' => 'Alterado com sucesso!');
    }

}
