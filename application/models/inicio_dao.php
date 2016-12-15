<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Inicio_dao extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function getDadosIniciais($data, $idUsuario) {
        $where = " and p.id_usuario = " . $idUsuario;
        $whereDespesas = "";

        if (isset($data['dataInicio']) && $data['dataInicio'] != null) {
            $where .= " AND date(p.data_vencimento) >= date('" . substr($data['dataInicio'], 0, 10) . "')";
            $whereDespesas .= " AND date(data_lancamento) >= date('" . substr($data['dataInicio'], 0, 10) . "')";
        }
        if (isset($data['dataFim']) && $data['dataFim'] != null) {
            $where .= " AND date(p.data_vencimento) <= date('" . substr($data['dataFim'], 0, 10) . "')";
            $whereDespesas .= " AND date(data_lancamento) <= date('" . substr($data['dataFim'], 0, 10) . "')";
        }
        $sql = "select " .
                "convert((SELECT sum(p.valor - p.desconto) as valor FROM pedido p where p.ativo = true AND p.status = 2 " . $where . "), decimal(9,2)) as pago, " .
                "convert((SELECT sum(p.valor - p.desconto) as valor FROM pedido p where p.ativo = true AND p.status = 1 " . $where . "), decimal(9,2)) as nao_pago, " .
                "(SELECT sum(desconto) FROM pedido p where p.ativo = true AND p.status = 2 " . $where . " and p.id) as total_desconto, " .
                "convert((SELECT sum(valor) as despesas FROM despesas where ativo = true and id_usuario = " . $idUsuario . $whereDespesas . "), decimal(9,2)) as despesas, " .
                "(SELECT count(*) FROM pedido p where p.ativo = true AND p.status = 2 " . $where . " and p.id) as qtd_pago, " .
                "(SELECT count(*) FROM pedido p where p.ativo = true AND p.status = 1 " . $where . " and p.id) as qtd_nao_pago, " .
                "(SELECT count(*) FROM pedido p where p.ativo = true AND p.status = 3 " . $where . " and p.id) as qtd_pago_parcial, " .
                "convert((SELECT sum(pp.valor) as valor FROM pedido_parcelamento pp inner join pedido p on pp.pedido = p.id and pp.id_usuario = p.id_usuario where p.ativo = true AND p.status = 3 AND pp.ativo = true AND pp.status = 1 " . $where . "), decimal(9,2)) as nao_pago_parcela, " .
                "convert((SELECT sum(pp.valor) as valor FROM pedido_parcelamento pp inner join pedido p on pp.pedido = p.id and pp.id_usuario = p.id_usuario where p.ativo = true AND p.status = 3 AND pp.ativo = true AND pp.status = 2 " . $where . "), decimal(9,2)) as pago_parcela,"
                . " (SELECT convert(sum(valor * estoque), decimal(9,2)) as valor_estoque FROM produto inner join fornecedor on fornecedor.id = id_fornecedor where produto.ativo = true) as valor_estoque,"
                . " (SELECT convert(sum((valor * (fornecedor.porcentagem/100)) * estoque), decimal(9,2)) as lucro_estoque FROM produto inner join fornecedor on fornecedor.id = id_fornecedor where produto.ativo = true) valor_lucro_estoque, " .
                "convert((SELECT sum((select sum((pp.valor * (pp.porcentagem / 100)) * pp.quantidade) as lucro from pedido_produto pp where p.id = pp.pedido and pp.ativo = true)) as lucro FROM pedido p where p.ativo = true AND p.status = 2  " . $where . "), decimal(9,2)) as lucro";

        $query = $this->db->query($sql);
        foreach ($query->result() as $row) {
            $valorDespesas = $row->despesas;
            $valorPago = $row->pago;
            $valorNaoPago = $row->nao_pago;
            $valorNaoPagoParcela = $row->nao_pago_parcela;
            $valorPagoParcela = $row->pago_parcela;
            $lucro = $row->lucro;
            $lucroEstoque = $row->valor_lucro_estoque;
            $valorEstoque = $row->valor_estoque;
            $desconto = $row->total_desconto;
            if ($lucroEstoque == null) {
                $lucroEstoque = 0.00;
            }
            if ($valorEstoque == null) {
                $valorEstoque = 0.00;
            }
            if ($valorDespesas == null) {
                $valorDespesas = 0.00;
            }
            if ($valorPago == null) {
                $valorPago = 0.00;
            }
            if ($valorNaoPago == null) {
                $valorNaoPago = 0.00;
            }
            if ($valorNaoPagoParcela == null) {
                $valorNaoPagoParcela = 0.00;
            }
            if ($valorPagoParcela == null) {
                $valorPagoParcela = 0.00;
            }
            if ($lucro == null) {
                $lucro = 0.00;
            }
            if ($desconto == null) {
                $desconto = 0.00;
            }
            $valorNaoPago = $valorNaoPago + $valorNaoPagoParcela;
            $valorPago = $valorPago + $valorPagoParcela;

            $totalPago = $row->qtd_pago;
            $totalNaoPago = $row->qtd_nao_pago;
            $totalPagoParcial = $row->qtd_pago_parcial;

            if ($totalPago == null) {
                $totalPago = 0;
            }

            if ($totalNaoPago == null) {
                $totalNaoPago = 0;
            }

            if ($totalPagoParcial == null) {
                $totalPagoParcial = 0;
            }
            
            return array('valorRecebido' => $valorPago, 'valorReceber' => $valorNaoPago, 'valorDespesas' => $valorDespesas, 'lucro' => $lucro - $desconto,
                'desconto' => $desconto, 'grafico' => array($totalPago, $totalPagoParcial, $totalNaoPago), 'valorEstoque' => $valorEstoque,
                'lucroEstoque'=>$lucroEstoque);
        }
        return array('pago' => 0.00, 'nao_pago' => 0.00, 'nao_pago_parcela' => 0.00);
    }

}
