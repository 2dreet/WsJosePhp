<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pedido_dao extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('cliente_dao');
        $this->load->model('produto_dao');
        $this->load->model('fornecedor_dao');
        $this->load->database();
    }

    function getListaPedido($data, $idUsuario) {
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
        $query = $this->db->query("SELECT p.* , concat(ps.nome, ' ', ps.sobre_nome) as nome FROM pedido p " .
                "inner join cliente c on p.id_cliente = c.id and p.id_usuario = c.id_usuario " .
                "inner join pessoa ps on ps.id = c.id_pessoa and ps.id_usuario = c.id_usuario " .
                "inner join tipo_pedido tp on tp.id = p.tipo_pedido " .
                "inner join status_pedido sp on sp.id = p.status " .
                " where p.ativo = true " . $where . " and p.id_usuario = " . $idUsuario . " LIMIT " . $pagina . "," . $limit);
        foreach ($query->result() as $row) {
            $dataVencimento = substr($row->data_vencimento, 0, 10);
            $pedido = array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'desconto' => $row->desconto
                , 'dataVencimento' => $dataVencimento, 'cliente' => $row->nome, 'tipo_pedido' => $row->tipo_pedido, 'status' => $row->status, 'entregue' => $row->entregue);
            $listaPedido[] = $pedido;
            unset($pedido);
        }
        $totalRegistro = 0;
        $valorTotal = 0;
        $descontoTotal = 0;
        $lucroTotal = 0;
        $query = $this->db->query("SELECT count(*) as count, sum(valor) as valorTotal, sum(desconto) as descontoTotal FROM pedido p where p.ativo = true " . $where . " and p.id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $totalRegistro = $row->count;
            $valorTotal = $row->valorTotal;
            $descontoTotal = $row->descontoTotal;
        }
        
        $query = $this->db->query("SELECT sum((pp.valor * (pp.porcentagem / 100)) * pp.quantidade)  as lucro FROM pedido p inner join pedido_produto pp on pp.pedido = p.id where p.ativo = true " . $where . " and p.id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $lucroTotal = $row->lucro - $descontoTotal;
        }
        
        return array('dados' => $listaPedido, 'totalRegistro' => $totalRegistro, 'valorTotal' => $valorTotal, 'descontoTotal' => $descontoTotal, 'lucroTotal' => $lucroTotal);
    }

    function getPedidoByIdPedido($data, $idUsuario) {
        $id = $data['idPedido'];
        $query = $this->db->query("SELECT * FROM pedido " .
                " where ativo = true and id = " . $id . " and id_usuario = " . $idUsuario);
        $pedido = null;
        foreach ($query->result() as $row) {
            $dataVencimento = substr($row->data_vencimento, 0, 10);
            $cliente = $this->cliente_dao->getClienteByClienteId($row->id_cliente, $idUsuario);
            $listaProduto = $this->getListaProdutoByIdPedido($row->id, $idUsuario);
            $listaParcelas = $this->getListaParcelas($row->id, $idUsuario);
            $pedido = array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'desconto' => $row->desconto
                , 'data_vencimento' => $dataVencimento, 'tipo_pedido' => $row->tipo_pedido, 'status' => $row->status, 'forma_pagamento' => $row->forma_pagamento,
                'cliente' => $cliente, 'listaProduto' => $listaProduto, 'listaParcelas' => $listaParcelas, 'entregue' => $row->entregue);
        }
        return $pedido;
    }

    function getListaProdutoByIdPedido($idPedido, $idUsuario) {
        $listaProduto = null;
        $query = $this->db->query("SELECT pp.*,p.id as pId, p.descricao, pp.valor as pValor FROM pedido_produto pp inner join produto p on pp.produto = p.id and pp.id_usuario = p.id_usuario "
                . " where pp.ativo = true and pp.pedido = " . $idPedido . " and pp.id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $produto = array('id_pedido' => $row->id, 'id' => $row->pId, 'descricao' => $row->descricao, 'valor' => $row->pValor, 'quantidade' => $row->quantidade, 'lucro' => ($row->pValor * ($row->porcentagem / 100)));
            $listaProduto[] = $produto;
            unset($produto);
        }
        return ($listaProduto);
    }

    function getListaParcelas($idPedido, $idUsuario) {
        $listaProduto = null;
        $query = $this->db->query("SELECT * FROM pedido_parcelamento"
                . " where ativo = true and pedido = " . $idPedido . " and id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $produto = array('id' => $row->id, 'status' => $row->status, 'valor' => $row->valor, 'data_pagamento' => $row->data_pagamento);
            $listaProduto[] = $produto;
            unset($produto);
        }
        return ($listaProduto);
    }

    function pagarParcelaPedido($data, $idUsuario) {
        $parcelaAux = $data['parcela'];
        $pedidoId = $data['pedidoId'];
        $parcela = array('status' => 2, 'data_pagamento' => date("Y-m-d"));
        $this->db->where('id', $parcelaAux['id']);
        $this->db->where('pedido', $pedidoId);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('pedido_parcelamento', $parcela);

        if ($this->verificaPedidoPendente($pedidoId, $idUsuario)) {
            $pedido = array('status' => 2, 'data_pagamento' => date("Y-m-d"));
            $this->db->where('id', $pedidoId);
            $this->db->where('id_usuario', $idUsuario);
            $this->db->update('pedido', $pedido);
        } else {
            $pedido = array('status' => 3, 'data_pagamento' => date("Y-m-d"));
            $this->db->where('id', $pedidoId);
            $this->db->where('id_usuario', $idUsuario);
            $this->db->update('pedido', $pedido);
        }

        return array('msgRetorno' => 'Pago com sucesso!');
    }

    function pagarPedidoComplento($data, $idUsuario) {
        $pedidoId = $data['pedidoId'];
        $pedido = array('status' => 2, 'data_pagamento' => date("Y-m-d"));
        $this->db->where('id', $pedidoId);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('pedido', $pedido);
        return array('msgRetorno' => 'Pago com sucesso!');
    }

    function entregarPedidoByPedido($data, $idUsuario) {
        $pedidoId = $data['pedidoId'];
        $pedido = array('entregue' => 1, 'data_entrega' => date("Y-m-d"));
        $this->db->where('id', $pedidoId);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('pedido', $pedido);
        return array('msgRetorno' => 'Entregue com sucesso!');
    }

    function verificaPedidoPendente($idPedido, $idUsuario) {
        $retono = true;
        $query = $this->db->query("SELECT * FROM pedido_parcelamento"
                . " where ativo = true and status = 1 and pedido = " . $idPedido . " and id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $retono = false;
        }
        return ($retono);
    }

    function getPedidoById($id, $idUsuario) {
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

    function inserir($dados, $idUsuario) {
        if ($dados['tipo_pedido']['id'] == 1) {
            $retornoEstoqueNegativo = null;
            foreach ($dados['listaProduto'] as $produto) {
                $estoquePositivo = $this->produto_dao->verificaEstoqueFuturo($produto['id'], $idUsuario, $produto['quantidade']);
                if ($estoquePositivo == false) {
                    $retornoEstoqueNegativo[] = $produto['descricao'] . " - Estoque Atual (" . $this->produto_dao->getEstoqueProdutoByIdProduto($produto['id'], $idUsuario) . ")";
                }
            }
            if ($retornoEstoqueNegativo != null) {
                return array("EstoqueNegativo" => $retornoEstoqueNegativo);
            }
        }

        $dataVencimento = substr($dados['data_vencimento'], 0, 10);
        if (!isset($dados['desconto'])) {
            $dados['desconto'] = 0;
        }
        $pedido = array('descricao' => $dados['descricao'], 'desconto' => $dados['desconto'], 'valor' => $dados['valor'], 'data_vencimento' => $dataVencimento,
            'forma_pagamento' => $dados['forma_pagamento']['id'], 'tipo_pedido' => $dados['tipo_pedido']['id'],
            'id_cliente' => $dados['cliente']['id'], 'status' => 1, 'data_lancamento' => date("Y-m-d"), 'id_usuario' => $idUsuario, 'ativo' => '1');
        if ($dados['tipo_pedido']['id'] == 1) {
            $pedido['entregue'] = 1;
            $pedido['data_entrega'] = date("Y-m-d");
        }
        $this->db->insert('pedido', $pedido);
        $pedido_id = 0;
        $query = $this->db->query("SELECT LAST_INSERT_ID() as id FROM pedido WHERE ativo = true AND id_usuario = " . $idUsuario . " LIMIT 1");
        foreach ($query->result() as $row) {
            $pedido_id = $row->id;
        }
        $valorPedido = $dados['valor'] - $dados['desconto'];
        foreach ($dados['listaProduto'] as $produto) {
            $pedido_produto = array('pedido' => $pedido_id, 'produto' => $produto['id'], 'quantidade' => $produto['quantidade'],
                'valor' => $produto['valor'], 'id_usuario' => $idUsuario, 'porcentagem' => $this->fornecedor_dao->getPorcentagemByIdProduto($produto['id']),'ativo' => '1');
            $this->db->insert('pedido_produto', $pedido_produto);

            if ($dados['tipo_pedido']['id'] == 1) {
                $movimentarEstoque["dados"] = array('tipoMovimentacao' => 2, 'id' => $produto['id'], 'estoque_movimento' => $produto['quantidade'], 'estoque_movimento_observacao' => "Venda");
                $this->produto_dao->movimentarProdutoByIdProduto($movimentarEstoque, $idUsuario);
            }
        }
        if (isset($dados['parcelas']) && $dados['parcelas'] > 0) {
            $valorParcela = round($valorPedido / $dados['parcelas'], 2);
            for ($i = 0; $i < $dados['parcelas']; $i++) {
                $pedidoParcelamento = array('pedido' => $pedido_id, 'status' => 1, 'valor' => $valorParcela, 'id_usuario' => $idUsuario, 'ativo' => '1');
                $this->db->insert('pedido_parcelamento', $pedidoParcelamento);
            }
        }

        return array('msgRetorno' => 'Cadastrado com sucesso!', 'pedido' => $this->getPedidoById($pedido_id, $idUsuario));
    }

    function alterar($dados, $idUsuario) {
        if ($dados['tipo_pedido']['id'] == 1) {
            $retornoEstoqueNegativo = null;
            foreach ($dados['listaProduto'] as $produto) {
                $estoquePositivo = $this->produto_dao->verificaEstoqueFuturo($produto['id'], $idUsuario, $produto['quantidade']);
                if ($estoquePositivo == false) {
                    $retornoEstoqueNegativo[] = $produto['descricao'] . " - Estoque Atual (" . $this->produto_dao->getEstoqueProdutoByIdProduto($produto['id'], $idUsuario) . ")";
                }
            }
            if ($retornoEstoqueNegativo != null) {
                return array("EstoqueNegativo" => $retornoEstoqueNegativo);
            }
        }
        $pedido_id = $dados['id'];
        $pedidoBanco = $this->getPedidoById($pedido_id, $idUsuario);
        $this->db->where('pedido', $pedido_id);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->delete('pedido_parcelamento');

        if ($pedidoBanco['tipo_pedido'] == 1) {
            $query = $this->db->query("SELECT * FROM pedido_produto where ativo = true and pedido = " . $pedido_id . " and id_usuario = " . $idUsuario);
            foreach ($query->result() as $row) {
                $movimentarEstoque["dados"] = array('tipoMovimentacao' => 6, 'id' => $row->produto, 'estoque_movimento' => $row->quantidade, 'estoque_movimento_observacao' => "Edição de Pedido");
                $this->produto_dao->movimentarProdutoByIdProduto($movimentarEstoque, $idUsuario);
            }
        }
        $this->db->where('pedido', $pedido_id);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->delete('pedido_produto');

        $dataVencimento = substr($dados['data_vencimento'], 0, 10);
        if (!isset($dados['desconto'])) {
            $dados['desconto'] = 0;
        }

        $pedido = array('descricao' => $dados['descricao'], 'desconto' => $dados['desconto'], 'valor' => $dados['valor'], 'data_vencimento' => $dataVencimento,
            'forma_pagamento' => $dados['forma_pagamento']['id'], 'tipo_pedido' => $dados['tipo_pedido']['id'],
            'id_cliente' => $dados['cliente']['id'], 'data_lancamento' => date("Y-m-d"));
        $this->db->where('id', $pedido_id);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('pedido', $pedido);
        $valorPedido = $dados['valor'] - $dados['desconto'];
        foreach ($dados['listaProduto'] as $produto) {
            $pedido_produto = array('pedido' => $pedido_id, 'produto' => $produto['id'], 'quantidade' => $produto['quantidade'],
                'valor' => $produto['valor'], 'id_usuario' => $idUsuario, 'ativo' => '1');
            $this->db->insert('pedido_produto', $pedido_produto);

            if ($dados['tipo_pedido']['id'] == 1) {
                $movimentarEstoque["dados"] = array('tipoMovimentacao' => 2, 'id' => $produto['id'], 'estoque_movimento' => $produto['quantidade'], 'estoque_movimento_observacao' => "Venda");
                $this->produto_dao->movimentarProdutoByIdProduto($movimentarEstoque, $idUsuario);
            }
        }
        if (isset($dados['parcelas']) && $dados['parcelas'] > 0) {
            $valorParcela = round($valorPedido / $dados['parcelas'], 2);
            for ($i = 0; $i < $dados['parcelas']; $i++) {
                $pedidoParcelamento = array('pedido' => $pedido_id, 'status' => 1, 'valor' => $valorParcela, 'id_usuario' => $idUsuario, 'ativo' => '1');
                $this->db->insert('pedido_parcelamento', $pedidoParcelamento);
            }
        }
        return array('msgRetorno' => 'Alterado com sucesso!', 'pedido' => $this->getPedidoById($pedido_id, $idUsuario));
    }

    function deletar($dados, $idUsuario) {

        if ($dados['tipo_pedido']['id'] == 1) {
            $query = $this->db->query("SELECT * FROM pedido_produto where ativo = true and pedido = " . $dados['id'] . " and id_usuario = " . $idUsuario);
            foreach ($query->result() as $row) {
                $movimentarEstoque["dados"] = array('tipoMovimentacao' => 7, 'id' => $row->produto, 'estoque_movimento' => $row->quantidade, 'estoque_movimento_observacao' => "Edição de Pedido");
                $this->produto_dao->movimentarProdutoByIdProduto($movimentarEstoque, $idUsuario);
            }
        }

        $pedido = array('ativo' => '0');
        $this->db->where('id', $dados['id']);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('pedido', $pedido);
        return array('msgRetorno' => 'Deletado com sucesso!');
    }

}
