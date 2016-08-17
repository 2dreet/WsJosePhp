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
            $query = $this->db->query("SELECT * FROM pedido where ativo = true " . $where . " and id_usuario = " . $dadosToken->id . " LIMIT " . $pagina . "," . $limit);
            foreach ($query->result() as $row) {
                $formaPagamento = null;
                $queryFormaPagamento = $this->db->query("SELECT * FROM forma_pagamento where ativo = true and id = " . $row->forma_pagamento);
                foreach ($queryFormaPagamento->result() as $rowFormaPagamento) {
                    $formaPagamento = array('id' => $rowFormaPagamento->id, 'descricao' => $rowFormaPagamento->descricao, 'ativo' => $rowFormaPagamento->ativo);
                }

                $status = null;
                $queryStatus = $this->db->query("SELECT * FROM status_pedido where ativo = true and id = " . $row->forma_pagamento);
                foreach ($queryStatus->result() as $rowStatus) {
                    $status = array('id' => $rowStatus->id, 'descricao' => $rowStatus->descricao, 'ativo' => $rowStatus->ativo);
                }

                $tipo = null;
                $queryTipo = $this->db->query("SELECT * FROM tipo_pedido where ativo = true and id = " . $row->forma_pagamento);
                foreach ($queryTipo->result() as $rowTipo) {
                    $tipo = array('id' => $rowTipo->id, 'descricao' => $rowTipo->descricao, 'ativo' => $rowTipo->ativo);
                }

                $cliente = null;
                $queryCliente = $this->db->query("SELECT c.id as idC, c.cpf, c.rg, c.email, p.id as idP, p.nome, p.sobre_nome, p.sexo, p.data_nascimento "
                        . " FROM cliente c INNER JOIN pessoa p ON c.id_pessoa = p.id AND c.id_usuario = p.id_usuario"
                        . " where p.ativo = true AND c.id = " . $row->id_cliente . " and c.id_usuario = " . $dadosToken->id);

                foreach ($queryCliente->result() as $rowCliente) {
                    $pessoa = array('id' => $rowCliente->idP, 'nome' => $rowCliente->nome, 'sobreNome' => $rowCliente->sobre_nome, 'sexo' => $rowCliente->sexo, 'dataNascimento' => $rowCliente->data_nascimento);
                    $cliente = array('id' => $rowCliente->idC, 'cpf' => $rowCliente->cpf, 'rg' => $rowCliente->rg, 'email' => $rowCliente->email, 'pessoa' => $pessoa);
                }

                $listaItem = null;
                $valor = 0;
                $queryListaItem = $this->db->query("SELECT * FROM pedido_produto where ativo = true and pedido = " . $row->id . " and id_usuario = " . $dadosToken->id);
                foreach ($queryListaItem->result() as $rowPedidoItem) {
                    $item = null;
                    $queryListaProduto = $this->db->query("SELECT * FROM produto where ativo = true and id = " . $rowPedidoItem->produto . " and id_usuario = " . $dadosToken->id);
                    foreach ($queryListaProduto->result() as $rowProduto) {
                        $pedidoItem = array('id_produto_item' => $rowPedidoItem->id, 'quantidadeCompra' => $rowPedidoItem->quantidade, 'valor_pedido_item' => $rowPedidoItem->valor, 'produto' => $item,
                            'ativo' => $rowPedidoItem->ativo, 'id' => $rowProduto->id, 'descricao' => $rowProduto->descricao, 'valor' => $rowProduto->valor);
                    }
                    $valor = $valor + $rowPedidoItem->valor;
                    $listaItem[] = $pedidoItem;
                }

                $dataVencimento = substr($row->data_vencimento, 0, 10);
                $pedido = array('id' => $row->id, 'descricao' => $row->descricao, 'desconto' => $row->desconto, 'data_lancamento' => $row->data_lancamento
                    , 'data_entrega' => $row->data_entrega, 'data_vencimento' => $dataVencimento, 'formaPagamento' => $formaPagamento, 'cliente' => $cliente,
                    'tipoPedido' => $tipo, 'status' => $status, 'listaProduto' => $listaItem, 'valor' => $valor);
                $listaPedido[] = $pedido;

                unset($pedido);
                unset($cliente);
                unset($pessoa);
                unset($tipo);
                unset($status);
                unset($formaPagamento);
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
        $pedido = array('descricao' => $dados['descricao'], 'desconto' => $dados['desconto'], 'data_vencimento' => $dataVencimento,
            'forma_pagamento' => $dados['formaPagamento']['id'], 'tipo_pedido' => $dados['tipoPedido']['id'],
            'id_cliente' => $dados['cliente']['id'], 'status' => $status, 'data_lancamento' => date("Y-m-d"), 'id_usuario' => $dadosToken->id, 'ativo' => '1');
        $this->load->database();
        $this->db->insert('pedido', $pedido);
        $pedido_id = 0;
        $query = $this->db->query("SELECT LAST_INSERT_ID() as id FROM pedido WHERE ativo = true AND id_usuario = " . $dadosToken->id . " LIMIT 1");
        foreach ($query->result() as $row) {
            $pedido_id = $row->id;
        }
        $valorTotal = 0;
        foreach ($dados['listaProduto'] as $produto) {
            $valorTotal = $valorTotal + $produto['valor'];
            $pedido_produto = array('pedido' => $pedido_id, 'produto' => $produto['id'], 'quantidade' => $produto['quantidadeCompra'],
                'valor' => $produto['valor'], 'id_usuario' => $dadosToken->id, 'ativo' => '1');
            $this->db->insert('pedido_produto', $pedido_produto);
        }
        $valorTotal = $valorTotal - $dados['desconto'];
        if (isset($dados['parcelas']) && $dados['parcelas'] > 0) {
            $valorParcela = round($valorTotal / $dados['parcelas'], 2);
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
