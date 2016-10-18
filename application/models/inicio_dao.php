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
        if (isset($data['cliente']) && $data['cliente'] != null) {
            $where .= " and p.id_cliente = " . $data['cliente']['id'];
        }
        if (isset($data['dataInicio']) && $data['dataInicio'] != null) {
            $where .= " AND date(p.data_lancamento) >= date('" . substr($data['dataInicio'], 0, 10) . "')";
            $whereDespesas .= " AND date(data_lancamento) >= date('" . substr($data['dataInicio'], 0, 10) . "')";
        }
        if (isset($data['dataFim']) && $data['dataFim'] != null) {
            $where .= " AND date(p.data_lancamento) <= date('" . substr($data['dataFim'], 0, 10) . "')";
            $whereDespesas .= " AND date(data_lancamento) <= date('" . substr($data['dataFim'], 0, 10) . "')";
        }
        $query = $this->db->query("select " .
                "convert((SELECT sum(valor) as despesas FROM despesas where ativo = true and id_usuario = " . $idUsuario . $whereDespesas . "), decimal(9,2)) as despesas, " .
                "convert((SELECT sum(p.valor - p.desconto) as valor FROM pedido p where p.ativo = true AND p.status = 2 " . $where . " and p.id not in(SELECT pp.pedido FROM pedido_parcelamento pp where pp.ativo = true AND pp.pedido = p.id and pp.id_usuario = p.id_usuario)), decimal(9,2)) as pago, " .
                "convert((SELECT sum(p.valor - p.desconto) as valor FROM pedido p where p.ativo = true AND p.status = 1 " . $where . " and p.id not in(SELECT pp.pedido FROM pedido_parcelamento pp where pp.ativo = true AND pp.pedido = p.id and pp.id_usuario = p.id_usuario)), decimal(9,2)) as nao_pago, " .
                "convert((SELECT sum(pp.valor) as valor FROM pedido_parcelamento pp inner join pedido p on pp.pedido = p.id and pp.id_usuario = p.id_usuario where p.ativo = true AND pp.ativo = true AND pp.status = 1 " . $where . "), decimal(9,2)) as nao_pago_parcela," .
                "convert((SELECT sum(pp.valor) as valor FROM pedido_parcelamento pp inner join pedido p on pp.pedido = p.id and pp.id_usuario = p.id_usuario where p.ativo = true AND pp.ativo = true AND pp.status = 2 " . $where . "), decimal(9,2)) as pago_parcela");
        foreach ($query->result() as $row) {
            $valorDespesas = $row->despesas;
            $valorPago = $row->pago;
            $valorNaoPago = $row->nao_pago;
            $valorNaoPagoParcela = $row->nao_pago_parcela;
            $valorPagoParcela = $row->pago_parcela;
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
            $valorNaoPago = $valorNaoPago + $valorNaoPagoParcela;
            $valorPago = $valorPago + $valorPagoParcela;
            return array('valorRecebido' => $valorPago, 'valorReceber' => $valorNaoPago, 'valorDespesas' => $valorDespesas);
        }
        return array('pago' => 0.00, 'nao_pago' => 0.00, 'nao_pago_parcela' => 0.00);
    }

}
