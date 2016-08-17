<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Pedido extends CI_Controller {

    public function getAllPedido() {
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
//            if (isset($data['buscaAvancada'])) {
//                $buscaAvancada = $data['buscaAvancada'];
//
//                if (isset($buscaAvancada['descricao']) && $buscaAvancada['descricao'] != null && trim($buscaAvancada['descricao']) != "") {
//                    $where .= " AND descricao like '%" . $buscaAvancada['descricao'] . "%'";
//                }
//
//                if (isset($buscaAvancada['email']) && $buscaAvancada['email'] != null && trim($buscaAvancada['email']) != "") {
//                    $where .= " AND email like '%" . $buscaAvancada['email'] . "%'";
//                }
//
//                if (isset($buscaAvancada['telefone']) && $buscaAvancada['telefone'] != null && trim($buscaAvancada['telefone']) != "") {
//                    $where .= " AND telefone like '%" . $buscaAvancada['telefone'] . "%'";
//                }
//            }
//
//            if (isset($data['buscaDescricao'])) {
//                if (isset($data['buscaDescricao']) && $data['buscaDescricao'] != null && trim($data['buscaDescricao']) != "") {
//                    $where .= " AND descricao like '%" . $data['buscaDescricao'] . "%'";
//                }
//            }
            $listaPedido = null;
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $query = $this->db->query("SELECT p.* , concat(ps.nome, ' ', ps.sobre_nome) as nome FROM pedido p " .
            "inner join cliente c on p.id_cliente = c.id and p.id_usuario = c.id_usuario ".
            "inner join pessoa ps on ps.id = c.id_pessoa and ps.id_usuario = c.id_usuario ".
            "inner join tipo_pedido tp on tp.id = p.tipo_pedido ".
            "inner join status_pedido sp on sp.id = p.status ".
            " where p.ativo = true " . $where . " and p.id_usuario = " . $dadosToken->id . " LIMIT " . $pagina . "," . $limit);
            foreach ($query->result() as $row) {
                $dataVencimento = substr($row->data_vencimento, 0, 10);
                $pedido = array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'desconto' => $row->desconto
                    , 'dataVencimento' => $dataVencimento, 'cliente' => $row->nome, 'tipoPedido' => $row->tipo_pedido, 'status' => $row->status);
                $listaPedido[] = $pedido;
                unset($pedido);
            }

            $totalRegistro = 0;
            $query = $this->db->query("SELECT count(*) as count FROM pedido where ativo = true " . $where . " and id_usuario = " . $dadosToken->id);
            foreach ($query->result() as $row) {
                $totalRegistro = $row->count;
            }

            $retorno = array('token' => $token, 'dados' => $listaPedido, 'totalRegistro' => $totalRegistro);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function enviarPedido() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            if ($data['tipoFuncao'] == 'inserir') {
                $retorno = $this->inserir($data['dados'], $token);
            } else if ($data['tipoFuncao'] == 'alterar') {
                $retorno = $this->alterar($data['dados'], $token);
            } else if ($data['tipoFuncao'] == 'deletar') {
                $retorno = $this->deletar($data['dados'], $token);
            }
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    function deletar($dados, $token) {
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

    function inserir($dados, $token) {
        $jwtUtil = new JwtUtil();
        $dadosToken = json_decode($jwtUtil->decode($token));
        $dataVencimento = substr($dados['dataVencimento'], 0, 10);
        if (!isset($dados['desconto'])) {
            $dados['desconto'] = 0;
        }
        $status = 1;
        if (isset($dados['pedidoPago']) && $dados['pedidoPago']) {
            $status = 2;
        }
        $pedido = array('descricao' => $dados['descricao'], 'desconto' => $dados['desconto'], 'valor' => $dados['valorPedido'], 'data_vencimento' => $dataVencimento,
            'forma_pagamento' => $dados['formaPagamento']['id'], 'tipo_pedido' => $dados['tipoPedido']['id'],
            'id_cliente' => $dados['cliente']['id'], 'status' => $status, 'data_lancamento' => date("Y-m-d"), 'id_usuario' => $dadosToken->id, 'ativo' => '1');
        $this->load->database();
        $this->db->insert('pedido', $pedido);
        $pedido_id = 0;
        $query = $this->db->query("SELECT LAST_INSERT_ID() as id FROM pedido WHERE ativo = true AND id_usuario = " . $dadosToken->id . " LIMIT 1");
        foreach ($query->result() as $row) {
            $pedido_id = $row->id;
        }
        $valorPedido = $dados['valorPedido'] - $dados['desconto'];
        foreach ($dados['listaProduto'] as $produto) {
            $pedido_produto = array('pedido' => $pedido_id, 'produto' => $produto['id'], 'quantidade' => $produto['quantidadeCompra'],
                'valor' => $produto['valor'], 'id_usuario' => $dadosToken->id, 'ativo' => '1');
            $this->db->insert('pedido_produto', $pedido_produto);
        }
        if (isset($dados['parcelas']) && $dados['parcelas'] > 0) {
            $valorParcela = round($valorPedido / $dados['parcelas'], 2);
            for ($i = 0; $i < $dados['parcelas']; $i++) {
                $pedidoParcelamento = array('pedido' => $pedido_id, 'status' => 1, 'valor' => $valorParcela, 'id_usuario' => $dadosToken->id, 'ativo' => '1');
                $this->db->insert('pedido_parcelamento', $pedidoParcelamento);
            }
        }
        $retorno = array('token' => $token, 'msgRetorno' => 'Cadastrado com sucesso!');
        return ($retorno);
    }

    function alterar($dados, $token) {
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
