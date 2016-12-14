<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Produto_dao extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('fornecedor_dao');
    }

    function getListaproduto($data, $idUsuario) {
        $pagina = $data['pagina'];
        $buscaAvancada = null;
        $where = "";
        if (isset($data['buscaAvancada'])) {
            $buscaAvancada = $data['buscaAvancada'];

            if (isset($buscaAvancada['descricao']) && $buscaAvancada['descricao'] != null && trim($buscaAvancada['descricao']) != "") {
                $where .= " AND descricao like '%" . $buscaAvancada['descricao'] . "%'";
            }

            if (isset($buscaAvancada['fornecedor']) && $buscaAvancada['fornecedor'] != null && $buscaAvancada['fornecedor'] != "") {
                $where .= " AND id_fornecedor = " . $buscaAvancada['fornecedor']['id'];
            }

            if (isset($buscaAvancada['estoquePositivo']) && $buscaAvancada['estoquePositivo'] != null && $buscaAvancada['estoquePositivo'] == true) {
                $where .= " AND estoque > 0 ";
            }
        }

        if (isset($data['buscaAvancada'])) {
            if (isset($data['buscaDescricao']) && $data['buscaDescricao'] != null && trim($data['buscaDescricao']) != "") {
                $where .= " AND descricao like '%" . $data['buscaDescricao'] . "%'";
            }
        }

        $listaProduto = null;
        if ($pagina > 0) {
            $pagina = $pagina * 10;
        }
        $query = $this->db->query("SELECT * FROM produto where ativo = true and id_usuario = " . $idUsuario . $where . " LIMIT " . $pagina . ",10");
        foreach ($query->result() as $row) {
            $queryFornecedor = $this->db->query("SELECT * FROM fornecedor where ativo = true and id = " . $row->id_fornecedor);
            $fornecedor = null;
            foreach ($queryFornecedor->result() as $rowFornecedor) {
                $fornecedor = array('id' => $rowFornecedor->id, 'descricao' => $rowFornecedor->descricao, 'email' => $rowFornecedor->email, 'telefone' => $rowFornecedor->telefone);
            }

            $lucro = $row->valor * ($this->fornecedor_dao->getPorcentagem($row->id_fornecedor)/100);
            
            $produto = array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'observacao' => $row->observacao, 'estoque' => $row->estoque, 'fornecedor' => $fornecedor,
                'lucro' => $lucro);
            
            $listaProduto[] = ($produto);
            unset($produto);
            unset($fornecedor);
        }

        $totalRegistro = 0;
        $query = $this->db->query("SELECT count(*) as count FROM produto where ativo = true and id_usuario = " . $idUsuario . $where);
        foreach ($query->result() as $row) {
            $totalRegistro = $row->count;
        }
        return array('dados' => $listaProduto, 'totalRegistro' => $totalRegistro);
    }

    function getProdutoByIdProduto($idProduto, $idUsuario) {
        $query = $this->db->query("SELECT * FROM produto where ativo = true and id = " . $idProduto . " and id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $queryFornecedor = $this->db->query("SELECT * FROM fornecedor where ativo = true and id = " . $row->id_fornecedor);
            $fornecedor = null;
            foreach ($queryFornecedor->result() as $rowFornecedor) {
                $fornecedor = array('id' => $rowFornecedor->id, 'descricao' => $rowFornecedor->descricao, 'email' => $rowFornecedor->email, 'telefone' => $rowFornecedor->telefone);
            }
            return $produto = array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'observacao' => $row->observacao, 'estoque' => $row->estoque, 'fornecedor' => $fornecedor);
        }
    }

    function insertProduto($dados, $idUsuario) {
        $fornecedor = $dados->fornecedor;
        if ($dados->valor == 0) {
            $dados->valor = 0.00;
        }
        if (!isset($dados->observacao)) {
            $dados->observacao = "";
        }

        $produto = array('descricao' => $dados->descricao, 'valor' => $dados->valor, 'observacao' => $dados->observacao,
            'estoque' => 0, 'id_fornecedor' => $fornecedor->id, 'id_usuario' => $idUsuario, 'ativo' => '1');

        $this->db->insert('produto', $produto);
        $produto_id = 0;
        $query = $this->db->query("SELECT LAST_INSERT_ID() as id FROM produto WHERE ativo = true AND id_usuario = " . $idUsuario . " LIMIT 1");
        foreach ($query->result() as $row) {
            $produto_id = $row->id;
        }
        return array('msgRetorno' => 'Cadastrado com sucesso!', 'produto' => $this->getProdutoByIdProduto($produto_id, $idUsuario));
    }

    function updatetProduto($dados, $idUsuario) {
        $fornecedor = $dados->fornecedor;
        if ($dados->valor == 0) {
            $dados->valor = 0.00;
        }
        $produto = array('descricao' => $dados->descricao, 'valor' => $dados->valor, 'observacao' => $dados->observacao,
            'id_fornecedor' => $fornecedor->id);

        $this->db->where('id', $dados->id);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('produto', $produto);
        return array('msgRetorno' => 'Alterado com sucesso!', 'produto' => $this->getProdutoByIdProduto($dados->id, $idUsuario));
    }

    function deleteProduto($dados, $idUsuario) {
        $produto = array('ativo' => '0');
        $this->db->where('id', $dados->id);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('produto', $produto);
        return array('msgRetorno' => 'Deletado com sucesso!');
    }

    function getMovimentacaoProdutoByIdProduto($data, $idUsuario) {
        $id = $data['id'];
        $pagina = $data['pagina'];
        $dataInicial = substr($data['data_inicial'], 0, 10);
        $dataFinal = substr($data['data_final'], 0, 10);
        $listaMovimentacaoProduto = null;
        if ($pagina > 0) {
            $pagina = $pagina * 10;
        }
        $query = $this->db->query("SELECT pm.id, pm.observacao, pm.quantidade, pm.data_movimento, tm.descricao, tm.id as tipo_movimentacao FROM produto_movimentacao pm inner join tipo_movimentacao tm on pm.tipo_movimentacao = tm.id"
                . " where pm.ativo = true and pm.id_usuario = " . $idUsuario . " and pm.id_produto = " . $id
                . "  AND DATE(data_movimento) >= '" . $dataInicial . "'"
                . "  AND DATE(data_movimento) <= '" . $dataFinal . "'"
                . " ORDER BY data_movimento desc LIMIT " . $pagina . ", 10");
        foreach ($query->result() as $row) {
            $movimentacaoProduto = array('id' => $row->id, 'observacao' => $row->observacao, 'quantidade' => $row->quantidade,
                'data_movimento' => str_replace(" ", "T", $row->data_movimento), 'descricao' => $row->descricao, 'tipoMovimentacao' => $row->tipo_movimentacao);
            $listaMovimentacaoProduto[] = ($movimentacaoProduto);
            unset($movimentacaoProduto);
        }

        $totalRegistro = 0;
        $query = $this->db->query("SELECT count(*) as count FROM produto_movimentacao"
                . " where ativo = true and id_usuario = " . $idUsuario . " and id_produto = " . $id
                . "  AND DATE(data_movimento) >= '" . $dataInicial . "'"
                . "  AND DATE(data_movimento) <= '" . $dataFinal . "'");
        foreach ($query->result() as $row) {
            $totalRegistro = $row->count;
        }
        return array('dados' => $listaMovimentacaoProduto, 'totalRegistro' => $totalRegistro, 'estoque' => $this->getEstoqueProdutoByIdProduto($id, $idUsuario));
    }

    function verificaEstoqueFuturo($produtoID, $idUsuario, $quantidade) {
        $estoqueAtual = $this->getEstoqueProdutoByIdProduto($produtoID, $idUsuario);
        if (($estoqueAtual - $quantidade) >= 0) {
            return true;
        }
        return false;
    }

    function movimentarProdutoByIdProduto($data, $idUsuario) {
        $dados = $data['dados'];
        $estoqueAtual = $this->getEstoqueProdutoByIdProduto($dados['id'], $idUsuario);
        $estoque = $estoqueAtual;
        if ($dados['tipoMovimentacao'] == "1" || $dados['tipoMovimentacao'] == "6" || $dados['tipoMovimentacao'] == "7") {
            $estoque = $estoque + $dados['estoque_movimento'];
        } else if ($dados['tipoMovimentacao'] == "2" || $dados['tipoMovimentacao'] == "3" || $dados['tipoMovimentacao'] == "4") {
            $estoque = $estoque - $dados['estoque_movimento'];
        } else if ($dados['tipoMovimentacao'] == "5") {
            $estoque = $dados['estoque_movimento'];
        }

        if ($estoque >= 0) {
            $produto = array('estoque' => $estoque);
            $this->db->where('id', $dados['id']);
            $this->db->where('id_usuario', $idUsuario);
            $this->db->update('produto', $produto);
            $produtoMovimentacao = array('observacao' => $dados['estoque_movimento_observacao'], 'quantidade' => $dados['estoque_movimento'], 'data_movimento' => date("Y-m-d H:i:s"), 'id_produto' => $dados['id'], 'tipo_movimentacao' => $dados['tipoMovimentacao'], 'id_usuario' => $idUsuario, 'ativo' => '1');
            $this->db->insert('produto_movimentacao', $produtoMovimentacao);
            return array('sucesso' => true, 'estoque' => $estoque);
        } else {
            return array('sucesso' => false, 'menssagem' => 'Estoque futuro negativo!', 'estoque' => $estoqueAtual);
        }
    }

    function getEstoqueProdutoByIdProduto($codigoProduto, $codigoUsuario) {
        $retorno = null;
        if (isset($codigoProduto) && $codigoProduto != null && $codigoProduto > 0) {
            $query = $this->db->query("SELECT * FROM produto where ativo = true and id_usuario = " . $codigoUsuario . " AND id =  " . $codigoProduto);
            foreach ($query->result() as $row) {
                $retorno = $row->estoque;
            }
        } else {
            $retorno = null;
        }
        return $retorno;
    }

    function getProdutoImagemByIdProduto($idProduto, $idUsuario) {
        $query = $this->db->query("SELECT imagem FROM produto where ativo = true and imagem is not null and id = " . $idProduto . " and id_usuario = " . $idUsuario . " limit 1");
        foreach ($query->result() as $row) {
            return ($row->imagem);
        }
        return file_get_contents('no-image.png');
    }

}
