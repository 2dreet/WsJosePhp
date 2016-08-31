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
            if (isset($data['buscaAvancada'])) {
                $buscaAvancada = $data['buscaAvancada'];
                if (isset($buscaAvancada['descricao']) && $buscaAvancada['descricao'] != null && trim($buscaAvancada['descricao']) != "") {
                    $where .= " AND p.descricao like '%" . $buscaAvancada['descricao'] . "%'";
                }
                if (isset($buscaAvancada['tipo_pedido']) && $buscaAvancada['tipo_pedido'] != null && $buscaAvancada['tipo_pedido']['id'] != "0") {
                    $where .= " AND tipo_pedido = '" . $buscaAvancada['tipo_pedido']['id'] . "'";
                }
                if (isset($buscaAvancada['status_pedido']) && $buscaAvancada['status_pedido'] != null && $buscaAvancada['status_pedido']['id'] != "0") {
                    $where .= " AND status = '" . $buscaAvancada['status_pedido']['id'] . "'";
                }
                if (isset($buscaAvancada['entregue']) && $buscaAvancada['entregue'] != null && $buscaAvancada['entregue']['valor'] != "0") {
                    $where .= " AND entregue = " . $buscaAvancada['entregue']['valor'];
                }
                if (isset($buscaAvancada['forma_pagamento']) && $buscaAvancada['forma_pagamento'] != null && $buscaAvancada['forma_pagamento']['id'] != "0") {
                    $where .= " AND forma_pagamento = '" . $buscaAvancada['forma_pagamento']['id'] . "'";
                }
                if (isset($buscaAvancada['cliente']) && $buscaAvancada['cliente'] != null) {
                    $where .= " AND id_cliente = '" . $buscaAvancada['cliente']['id'] . "'";
                }
                if (isset($buscaAvancada['data_vencimento_inicial']) && $buscaAvancada['data_vencimento_inicial'] != null) {
                    $where .= " AND date(data_vencimento) >= date('" . substr($buscaAvancada['data_vencimento_inicial'], 0, 10) . "')";
                }
                if (isset($buscaAvancada['data_vencimento_final']) && $buscaAvancada['data_vencimento_final'] != null) {
                    $where .= " AND date(data_vencimento) <= date('" . substr($buscaAvancada['data_vencimento_final'], 0, 10) . "')";
                }
                if (isset($buscaAvancada['data_pagamento_inicial']) && $buscaAvancada['data_pagamento_inicial'] != null) {
                    $where .= " AND date(data_pagamento) >= date('" . substr($buscaAvancada['data_pagamento_inicial'], 0, 10) . "')";
                }
                if (isset($buscaAvancada['data_pagamento_final']) && $buscaAvancada['data_pagamento_final'] != null) {
                    $where .= " AND date(data_pagamento) <= date('" . substr($buscaAvancada['data_pagamento_final'], 0, 10) . "')";
                }
            }
            if (isset($data['buscaDescricao'])) {
                if (isset($data['buscaDescricao']) && $data['buscaDescricao'] != null && trim($data['buscaDescricao']) != "") {
                    $where .= " AND p.descricao like '%" . $data['buscaDescricao'] . "%'";
                }
            }
            $listaPedido = null;
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $query = $this->db->query("SELECT p.* , concat(ps.nome, ' ', ps.sobre_nome) as nome FROM pedido p " .
                    "inner join cliente c on p.id_cliente = c.id and p.id_usuario = c.id_usuario " .
                    "inner join pessoa ps on ps.id = c.id_pessoa and ps.id_usuario = c.id_usuario " .
                    "inner join tipo_pedido tp on tp.id = p.tipo_pedido " .
                    "inner join status_pedido sp on sp.id = p.status " .
                    " where p.ativo = true " . $where . " and p.id_usuario = " . $dadosToken->id . " LIMIT " . $pagina . "," . $limit);
            foreach ($query->result() as $row) {
                $dataVencimento = substr($row->data_vencimento, 0, 10);
                $pedido = array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'desconto' => $row->desconto
                    , 'dataVencimento' => $dataVencimento, 'cliente' => $row->nome, 'tipo_pedido' => $row->tipo_pedido, 'status' => $row->status, 'entregue' => $row->entregue);
                $listaPedido[] = $pedido;
                unset($pedido);
            }

            $totalRegistro = 0;
            $query = $this->db->query("SELECT count(*) as count FROM pedido p where p.ativo = true " . $where . " and p.id_usuario = " . $dadosToken->id);
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

    private function getPedidoById($id, $idUsuario) {
        $this->load->database();
        $query = $this->db->query("SELECT p.* , concat(ps.nome, ' ', ps.sobre_nome) as nome FROM pedido p " .
                "inner join cliente c on p.id_cliente = c.id and p.id_usuario = c.id_usuario " .
                "inner join pessoa ps on ps.id = c.id_pessoa and ps.id_usuario = c.id_usuario " .
                "inner join tipo_pedido tp on tp.id = p.tipo_pedido " .
                "inner join status_pedido sp on sp.id = p.status " .
                " where p.ativo = true and p.id = '" . $id . "' and p.id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $dataVencimento = substr($row->data_vencimento, 0, 10);
            return array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'desconto' => $row->desconto
                , 'dataVencimento' => $dataVencimento, 'cliente' => $row->nome, 'tipo_pedido' => $row->tipo_pedido, 'status' => $row->status, 'entregue' => $row->entregue);
        }
    }

    private function getClienteById($id, $idUsuario) {
        $cliente = null;
        $this->load->database();
        $query = $this->db->query("SELECT c.id , p.nome, p.sobre_nome "
                . " FROM cliente c INNER JOIN pessoa p ON c.id_pessoa = p.id AND c.id_usuario = p.id_usuario"
                . " where p.ativo = true and c.id = " . $id . " and c.id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $nome = $row->nome . " " . $row->sobre_nome;
            $cliente = array('id' => $row->id, 'nome' => $nome);
        }
        return ($cliente);
    }

    private function getListaProdutoById($idPedido, $idUsuario) {
        $listaProduto = null;
        $this->load->database();
        $query = $this->db->query("SELECT pp.*,p.id as pId, p.descricao, p.valor as pValor FROM pedido_produto pp inner join produto p on pp.produto = p.id and pp.id_usuario = p.id_usuario "
                . " where pp.ativo = true and pp.pedido = " . $idPedido . " and pp.id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $produto = array('id_pedido' => $row->id, 'id' => $row->pId, 'descricao' => $row->descricao, 'valor' => $row->pValor, 'quantidade' => $row->quantidade);
            $listaProduto[] = $produto;
            unset($produto);
        }
        return ($listaProduto);
    }

    private function getListaParcelas($idPedido, $idUsuario) {
        $listaProduto = null;
        $this->load->database();
        $query = $this->db->query("SELECT * FROM pedido_parcelamento"
                . " where ativo = true and pedido = " . $idPedido . " and id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $produto = array('id' => $row->id, 'status' => $row->status, 'valor' => $row->valor, 'data_pagamento' => $row->data_pagamento);
            $listaProduto[] = $produto;
            unset($produto);
        }
        return ($listaProduto);
    }

    private function verificaPedidoPendente($idPedido, $idUsuario) {
        $retono = true;
        $this->load->database();
        $query = $this->db->query("SELECT * FROM pedido_parcelamento"
                . " where ativo = true and status = 1 and pedido = " . $idPedido . " and id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $retono = false;
        }
        return ($retono);
    }

    public function getPedido() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $id = $data['idPedido'];
        $retorno = null;
        if (isset($token) && $token != null && $jwtUtil->validate($token) && isset($id) && $id > 0) {
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $query = $this->db->query("SELECT * FROM pedido " .
                    " where ativo = true and id = " . $id . " and id_usuario = " . $dadosToken->id);
            $pedido = null;
            foreach ($query->result() as $row) {
                $dataVencimento = substr($row->data_vencimento, 0, 10);
                $cliente = $this->getClienteById($row->id_cliente, $dadosToken->id);
                $listaProduto = $this->getListaProdutoById($row->id, $dadosToken->id);
                $listaParcelas = $this->getListaParcelas($row->id, $dadosToken->id);
                $pedido = array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'desconto' => $row->desconto
                    , 'data_vencimento' => $dataVencimento, 'tipo_pedido' => $row->tipo_pedido, 'status' => $row->status, 'forma_pagamento' => $row->forma_pagamento,
                    'cliente' => $cliente, 'listaProduto' => $listaProduto, 'listaParcelas' => $listaParcelas, 'entregue' => $row->entregue);
            }
            $retorno = array('token' => $token, 'pedido' => $pedido);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function pagarParcela() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $dadosToken = json_decode($jwtUtil->decode($token));
            $parcelaAux = $data['parcela'];
            $pedidoId = $data['pedidoId'];
            $parcela = array('status' => 2, 'data_pagamento' => date("Y-m-d"));
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $this->db->where('id', $parcelaAux['id']);
            $this->db->where('pedido', $pedidoId);
            $this->db->where('id_usuario', $dadosToken->id);
            $this->db->update('pedido_parcelamento', $parcela);

            if ($this->verificaPedidoPendente($pedidoId, $dadosToken->id)) {
                $pedido = array('status' => 2, 'data_pagamento' => date("Y-m-d"));
                $this->db->where('id', $pedidoId);
                $this->db->where('id_usuario', $dadosToken->id);
                $this->db->update('pedido', $pedido);
            } else {
                $pedido = array('status' => 3, 'data_pagamento' => date("Y-m-d"));
                $this->db->where('id', $pedidoId);
                $this->db->where('id_usuario', $dadosToken->id);
                $this->db->update('pedido', $pedido);
            }

            $retorno = array('token' => $token, 'msgRetorno' => 'Pago com sucesso!');
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function pagarPedido() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $dadosToken = json_decode($jwtUtil->decode($token));
            $pedidoId = $data['pedidoId'];
            $this->load->database();
            $pedido = array('status' => 2, 'data_pagamento' => date("Y-m-d"));
            $this->db->where('id', $pedidoId);
            $this->db->where('id_usuario', $dadosToken->id);
            $this->db->update('pedido', $pedido);

            $retorno = array('token' => $token, 'msgRetorno' => 'Pago com sucesso!');
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function entregarPedido() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $dadosToken = json_decode($jwtUtil->decode($token));
            $pedidoId = $data['pedidoId'];
            $this->load->database();
            $pedido = array('entregue' => 1, 'data_entrega' => date("Y-m-d"));
            $this->db->where('id', $pedidoId);
            $this->db->where('id_usuario', $dadosToken->id);
            $this->db->update('pedido', $pedido);

            $retorno = array('token' => $token, 'msgRetorno' => 'Entregue com sucesso!');
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
        $pedido = array('ativo' => '0');
        $dadosToken = json_decode($jwtUtil->decode($token));
        $this->load->database();
        $this->db->where('id', $dados['id']);
        $this->db->where('id_usuario', $dadosToken->id);
        $this->db->update('pedido', $pedido);
        $retorno = array('token' => $token, 'msgRetorno' => 'Deletado com sucesso!');
        return ($retorno);
    }

    function inserir($dados, $token) {
        $jwtUtil = new JwtUtil();
        $dadosToken = json_decode($jwtUtil->decode($token));
        $dataVencimento = substr($dados['data_vencimento'], 0, 10);
        if (!isset($dados['desconto'])) {
            $dados['desconto'] = 0;
        }
        $pedido = array('descricao' => $dados['descricao'], 'desconto' => $dados['desconto'], 'valor' => $dados['valor'], 'data_vencimento' => $dataVencimento,
            'forma_pagamento' => $dados['forma_pagamento']['id'], 'tipo_pedido' => $dados['tipo_pedido']['id'],
            'id_cliente' => $dados['cliente']['id'], 'status' => 1, 'data_lancamento' => date("Y-m-d"), 'id_usuario' => $dadosToken->id, 'ativo' => '1');
        $this->load->database();
        $this->db->insert('pedido', $pedido);
        $pedido_id = 0;
        $query = $this->db->query("SELECT LAST_INSERT_ID() as id FROM pedido WHERE ativo = true AND id_usuario = " . $dadosToken->id . " LIMIT 1");
        foreach ($query->result() as $row) {
            $pedido_id = $row->id;
        }
        $valorPedido = $dados['valor'] - $dados['desconto'];
        foreach ($dados['listaProduto'] as $produto) {
            $pedido_produto = array('pedido' => $pedido_id, 'produto' => $produto['id'], 'quantidade' => $produto['quantidade'],
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
        $retorno = array('token' => $token, 'msgRetorno' => 'Cadastrado com sucesso!', 'pedido' => $this->getPedidoById($pedido_id, $dadosToken->id));
        return ($retorno);
    }

    function alterar($dados, $token) {
        $jwtUtil = new JwtUtil();
        $dadosToken = json_decode($jwtUtil->decode($token));
        $pedido_id = $dados['id'];
        $this->load->database();
        $this->db->where('pedido', $pedido_id);
        $this->db->where('id_usuario', $dadosToken->id);
        $this->db->delete('pedido_parcelamento');

        $this->db->where('pedido', $pedido_id);
        $this->db->where('id_usuario', $dadosToken->id);
        $this->db->delete('pedido_produto');

        $dataVencimento = substr($dados['data_vencimento'], 0, 10);
        if (!isset($dados['desconto'])) {
            $dados['desconto'] = 0;
        }

        $pedido = array('descricao' => $dados['descricao'], 'desconto' => $dados['desconto'], 'valor' => $dados['valor'], 'data_vencimento' => $dataVencimento,
            'forma_pagamento' => $dados['forma_pagamento']['id'], 'tipo_pedido' => $dados['tipo_pedido']['id'],
            'id_cliente' => $dados['cliente']['id'], 'data_lancamento' => date("Y-m-d"));
        $this->db->where('id', $pedido_id);
        $this->db->where('id_usuario', $dadosToken->id);
        $this->db->update('pedido', $pedido);
        $valorPedido = $dados['valor'] - $dados['desconto'];
        foreach ($dados['listaProduto'] as $produto) {
            $pedido_produto = array('pedido' => $pedido_id, 'produto' => $produto['id'], 'quantidade' => $produto['quantidade'],
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

        $retorno = array('token' => $token, 'msgRetorno' => 'Alterado com sucesso!', 'pedido' => $this->getPedidoById($pedido_id, $dadosToken->id));
        return ($retorno);
    }

}
