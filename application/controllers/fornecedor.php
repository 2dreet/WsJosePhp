<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Fornecedor extends CI_Controller {
    public function getAllfornecedor() {
//        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//            $data = json_decode(file_get_contents("php://input"));
                    
            $pessoa = array('id' => 1, 'nome' => 'José', 'sobre_nome' => 'Augusto', 'sexo' => 'Masculino', 'data_cadastro' => '2016-06-10', 'data_nascimento' => '1991-06-28', 'ativo' => '1');
            $usuario = array('id' => 1, 'usuario' => 'jose', 'senha' => '123', 'pessoa' => $pessoa);
            $fornecedor = array('id' => 1, 'descricao' => 'Coca Cola', 'email' => 'coca@coca.com', 'telefone' => '04198875715', 'usuario' => $usuario, 'ativo' => '1');
            $listaFornecedor[] = $fornecedor;
            
            $pessoa = array('id' => 1, 'nome' => 'José', 'sobre_nome' => 'Augusto', 'sexo' => 'Masculino', 'data_cadastro' => '2016-06-10', 'data_nascimento' => '1991-06-28', 'ativo' => '1');
            $usuario = array('id' => 1, 'usuario' => 'jose', 'senha' => '123', 'pessoa' => $pessoa);
            $fornecedor = array('id' => 2, 'descricao' => 'Fanta', 'email' => 'fanta@coca.com', 'telefone' => '04198875715', 'usuario' => $usuario, 'ativo' => '1');
            $listaFornecedor[] = $fornecedor;
            
            $pessoa = array('id' => 1, 'nome' => 'José', 'sobre_nome' => 'Augusto', 'sexo' => 'Masculino', 'data_cadastro' => '2016-06-10', 'data_nascimento' => '1991-06-28', 'ativo' => '1');
            $usuario = array('id' => 1, 'usuario' => 'jose', 'senha' => '123', 'pessoa' => $pessoa);
            $fornecedor = array('id' => 3, 'descricao' => 'Tubaina', 'email' => 'tubaina@tubaina.com', 'telefone' => '04198875715', 'usuario' => $usuario, 'ativo' => '1');
            $listaFornecedor[] = $fornecedor;
            
            header('Content-Type: application/json');
            echo json_encode($listaFornecedor);
        }
//    }
}