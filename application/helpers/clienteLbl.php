<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ClienteLbl {
    public function getCliente($id, $idUsuario) {
        $cliente = null;
        $this->load->database();
        $query = $this->db->query("SELECT c.id as idC, c.cpf, c.rg, c.email, p.id as idP, p.nome, p.sobre_nome, p.sexo, p.data_nascimento, pe.id as idPe, pe.rua, pe.numero, pe.complemento, pe.bairro, pe.cidade, pe.estado, pe.cep "
                . " FROM cliente c INNER JOIN pessoa p ON c.id_pessoa = p.id AND c.id_usuario = p.id_usuario INNER JOIN pessoa_endereco pe ON pe.id_pessoa = p.id AND pe.id_usuario = p.id_usuario "
                . " where p.ativo = true c.id = " . $id . " and c.id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $listaTelefone = null;
            $queryTelefone = $this->db->query("SELECT pe.id, pe.telefone, pe.tipo_telefone, tt.descricao "
                    . " FROM pessoa_telefone pe INNER JOIN tipo_telefone tt ON pe.tipo_telefone = tt.id "
                    . " where pe.ativo = true and pe.id_usuario = " . $idUsuario . " AND pe.id_pessoa = " . $row->idP);
            foreach ($queryTelefone->result() as $rowTelefone) {
                $telefoneAux = array('id' => $rowTelefone->id, 'numero' => $rowTelefone->telefone,
                    'tipoTelefone' => array('id' => $rowTelefone->tipo_telefone, 'descricao' => $rowTelefone->descricao));
                $listaTelefone[] = $telefoneAux;
                unset($telefoneAux);
            }
            $pessoa = array('id' => $row->idP, 'nome' => $row->nome, 'sobreNome' => $row->sobre_nome, 'sexo' => $row->sexo, 'dataNascimento' => $row->data_nascimento);
            $endereco = array('id' => $row->idPe, 'logradouro' => $row->rua, 'numero' => $row->numero, 'complemento' => $row->complemento, 'bairro' => $row->bairro
                , 'cidade' => $row->cidade, 'uf' => $row->estado, 'cep' => $row->cep);
            $cliente = array('id' => $row->idC, 'cpf' => $row->cpf, 'rg' => $row->rg, 'email' => $row->email, 'endereco' => $endereco, 'pessoa' => $pessoa, 'listaTelefone' => $listaTelefone);
        }
        return ($cliente);
    }
}
