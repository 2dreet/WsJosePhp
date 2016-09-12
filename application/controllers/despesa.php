<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Despesa extends CI_Controller {

    public function getAllDespesa() {
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
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $query = $this->db->query("SELECT * FROM despesas where ativo = true " . $where . " and id_usuario = " . $dadosToken->id . " LIMIT " . $pagina . "," . $limit);
            foreach ($query->result() as $row) {
                $data = substr($row->data_lancamento, 0, 10);
                $despesa = array('id' => $row->id, 'descricao' => $row->descricao, 'data_lancamento' => $data, 'valor' => $row->valor);
                $listaDespesa[] = $despesa;
                unset($despesa);
            }

            $totalRegistro = 0;
            $query = $this->db->query("SELECT count(*) as count FROM despesas where ativo = true " . $where . " and id_usuario = " . $dadosToken->id);
            foreach ($query->result() as $row) {
                $totalRegistro = $row->count;
            }

            $retorno = array('token' => $token, 'dados' => $listaDespesa, 'totalRegistro' => $totalRegistro);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function enviarDespesa() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            if ($data['tipoFuncao'] == 'inserir') {
                $retorno = $this->inserirDespesa($data['dados'], $token);
            } else if ($data['tipoFuncao'] == 'alterar') {
                $retorno = $this->alterarDespesa($data['dados'], $token);
            } else if ($data['tipoFuncao'] == 'deletar') {
                $retorno = $this->deletarDespesa($data['dados'], $token);
            }
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    function deletarDespesa($dados, $token) {
        $jwtUtil = new JwtUtil();
        $despesas = array('ativo' => '0');
        $dadosToken = json_decode($jwtUtil->decode($token));
        $this->load->database();
        $this->db->where('id', $dados['id']);
        $this->db->where('id_usuario', $dadosToken->id);
        $this->db->update('despesas', $despesas);
        $retorno = array('token' => $token, 'msgRetorno' => 'Deletado com sucesso!');
        return ($retorno);
    }

    function inserirDespesa($dados, $token) {
        $jwtUtil = new JwtUtil();
        $dadosToken = json_decode($jwtUtil->decode($token));
        $despesas = array('descricao' => $dados['descricao'], 'valor' => $dados['valor'], 'data_lancamento' => substr($dados['data_lancamento'], 0, 10), 'id_usuario' => $dadosToken->id, 'ativo' => '1');
        $this->load->database();
        $this->db->insert('despesas', $despesas);
        $retorno = array('token' => $token, 'msgRetorno' => 'Cadastrado com sucesso!');
        return ($retorno);
    }

    function alterarDespesa($dados, $token) {
        $jwtUtil = new JwtUtil();
        $despesas = array('descricao' => $dados['descricao'], 'valor' => $dados['valor'], 'data_lancamento' => substr($dados['data_lancamento'], 0, 10));
        $dadosToken = json_decode($jwtUtil->decode($token));
        $this->load->database();
        $this->db->where('id', $dados['id']);
        $this->db->where('id_usuario', $dadosToken->id);
        $this->db->update('despesas', $despesas);
        $retorno = array('token' => $token, 'msgRetorno' => 'Alterado com sucesso!');
        return ($retorno);
    }

}
