<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Fornecedor extends CI_Controller {

    public function getAllfornecedor() {
        $this->load->database();
        $query = $this->db->query("SELECT * FROM fornecedor where ativo = true and id_usuario = 1");
        foreach ($query->result() as $row) {
            //Obtem o usuario
            $queryUsuario = $this->db->query("SELECT * FROM usuario where id = " . $row->id_usuario);
            if ($queryUsuario->num_rows() > 0) {
                $rowUsuario = $queryUsuario->row();

                //Obtem a pessoa do usuario
                $queryPessoa = $this->db->query("SELECT * FROM pessoa where ativo = true and id_usuario = ". $row->id_usuario);
                if ($queryPessoa->num_rows() > 0) {
                    $rowPessoa = $queryPessoa->row();
                    $pessoa = array('id' => $rowPessoa->id, 'nome' => $rowPessoa->nome, 'sobre_nome' => $rowPessoa->sobre_nome, 'sexo' => $rowPessoa->sexo, 'data_cadastro' => $rowPessoa->data_cadastro, 'data_nascimento' => $rowPessoa->data_nascimento, 'ativo' => $rowPessoa->ativo);
                }

                $usuario = array('id' => $rowUsuario->id, 'usuario' => $rowUsuario->usuario, 'senha' => $rowUsuario->senha, 'pessoa' => $pessoa);
            }

            //Obtem a lista de todos os fornecedores
            $fornecedor = array('id' => $row->id, 'descricao' => $row->descricao, 'email' => $row->email, 'telefone' => $row->telefone, 'usuario' => $usuario, 'ativo' => $row->ativo);
            $listaFornecedor[] = $fornecedor;
        }
        header('Content-Type: application/json');
        echo json_encode($listaFornecedor);
    }

    public function getFornecedor() {
        
    }

    public function newFornecedor() {
        
    }

    public function updateFornecedor() {
        
    }

    public function removeFornecedor() {
        
    }

}
