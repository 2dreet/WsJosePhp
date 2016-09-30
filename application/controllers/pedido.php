<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pedido extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('pedido_dao');
    }

    public function getAllPedido() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) && isset($data['pagina']) && $data['pagina'] >= 0) {
            $retorno = $this->pedido_dao->getListaPedido($data, getDadosTokenJson($data['token'])->id);
            $retorno["token"] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function getPedido() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) && isset($data['idPedido']) && $data['idPedido'] > 0) {
            $retorno = array('token' => $data['token'], 'pedido' => $this->pedido_dao->getPedidoByIdPedido($data, getDadosTokenJson($data['token'])->id));
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function pagarParcela() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            $retorno = $this->pedido_dao->pagarParcelaPedido($data, getDadosTokenJson($data['token'])->id);
            $retorno["token"] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function pagarPedido() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            $retorno = $this->pedido_dao->pagarPedidoComplento($data, getDadosTokenJson($data['token'])->id);
            $retorno["token"] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function entregarPedido() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            $retorno = $this->pedido_dao->entregarPedidoByPedido($data, getDadosTokenJson($data['token'])->id);
            $retorno["token"] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function enviarPedido() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            if ($data['tipoFuncao'] == 'inserir') {
                $retorno = $this->pedido_dao->inserir($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else if ($data['tipoFuncao'] == 'alterar') {
                $retorno = $this->pedido_dao->alterar($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            } else if ($data['tipoFuncao'] == 'deletar') {
                $retorno = $this->pedido_dao->deletar($data['dados'], getDadosTokenJson($data['token'])->id);
                $retorno["token"] = $data['token'];
            }
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
