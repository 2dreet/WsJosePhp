<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cliente_dao extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function getListaCliente($data, $idUsuario) {
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
                $where .= " AND (p.nome like '%" . $buscaAvancada['descricao'] . "%'";
                $where .= " OR p.sobre_nome like '%" . $buscaAvancada['descricao'] . "%')";
            }

            if (isset($buscaAvancada['sexo']) && $buscaAvancada['sexo'] != null && trim($buscaAvancada['sexo']) != "" && trim($buscaAvancada['sexo']) != "Todos") {
                $where .= " AND p.sexo = '" . $buscaAvancada['sexo'] . "'";
            }

            if (isset($buscaAvancada['rg']) && $buscaAvancada['rg'] != null && trim($buscaAvancada['rg']) != "") {
                $where .= " AND c.rg = '" . $buscaAvancada['rg'] . "'";
            }

            if (isset($buscaAvancada['cpf']) && $buscaAvancada['cpf'] != null && trim($buscaAvancada['cpf']) != "") {
                $where .= " AND c.cpf = '" . $buscaAvancada['cpf'] . "'";
            }

            if (isset($buscaAvancada['email']) && $buscaAvancada['email'] != null && trim($buscaAvancada['email']) != "") {
                $where .= " AND c.email = '" . $buscaAvancada['email'] . "'";
            }

            if (isset($buscaAvancada['cep']) && $buscaAvancada['cep'] != null && trim($buscaAvancada['cep']) != "") {
                $where .= " AND pe.cep = '" . $buscaAvancada['cep'] . "'";
            }

            if (isset($buscaAvancada['logradouro']) && $buscaAvancada['logradouro'] != null && trim($buscaAvancada['logradouro']) != "") {
                $where .= " AND pe.rua like '%" . $buscaAvancada['logradouro'] . "%'";
            }

            if (isset($buscaAvancada['bairro']) && $buscaAvancada['bairro'] != null && trim($buscaAvancada['bairro']) != "") {
                $where .= " AND pe.bairro like '%" . $buscaAvancada['bairro'] . "%'";
            }

            if (isset($buscaAvancada['cidade']) && $buscaAvancada['cidade'] != null && trim($buscaAvancada['cidade']) != "") {
                $where .= " AND pe.cidade like '%" . $buscaAvancada['cidade'] . "%'";
            }

            if (isset($buscaAvancada['uf']) && $buscaAvancada['uf'] != null && trim($buscaAvancada['uf']) != "") {
                $where .= " AND pe.estado = '" . $buscaAvancada['uf'] . "'";
            }
        }

        if (isset($data['buscaDescricao'])) {
            if (isset($data['buscaDescricao']) && $data['buscaDescricao'] != null && trim($data['buscaDescricao']) != "") {
                $where .= " AND (p.nome like '%" . $data['buscaDescricao'] . "%'";
                $where .= " OR p.sobre_nome like '%" . $data['buscaDescricao'] . "%')";
            }
        }
        $listaCliente = null;
        $query = $this->db->query("SELECT c.id as idC, c.cpf, c.rg, c.email, p.id as idP, p.nome, p.sobre_nome, p.sexo, p.data_nascimento, pe.id as idPe, pe.rua, pe.numero, pe.complemento, pe.bairro, pe.cidade, pe.estado, pe.cep "
                . " FROM cliente c INNER JOIN pessoa p ON c.id_pessoa = p.id AND c.id_usuario = p.id_usuario INNER JOIN pessoa_endereco pe ON pe.id_pessoa = p.id AND pe.id_usuario = p.id_usuario "
                . " where p.ativo = true " . $where . " and c.id_usuario = " . $idUsuario . " LIMIT " . $pagina . "," . $limit);

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

            $listaCliente[] = $cliente;
            unset($pessoa);
            unset($endereco);
            unset($cliente);
            unset($listaTelefone);
        }

        $totalRegistro = 0;
        $query = $this->db->query("SELECT count(*) count FROM cliente c INNER JOIN pessoa p ON c.id_pessoa = p.id AND c.id_usuario = p.id_usuario INNER JOIN pessoa_endereco pe ON pe.id_pessoa = p.id AND pe.id_usuario = p.id_usuario "
                . "  where p.ativo = true " . $where . " and c.id_usuario = " . $idUsuario);
        foreach ($query->result() as $row) {
            $totalRegistro = $row->count;
        }
        return array('dados' => $listaCliente, 'totalRegistro' => $totalRegistro);
    }

    function getClienteByClienteId($id, $idUsuario) {
        $cliente = null;
        $sql = "SELECT c.id as idC, c.cpf, c.rg, c.email, p.id as idP, p.nome, p.sobre_nome, p.sexo, p.data_nascimento, pe.id as idPe, pe.rua, pe.numero, pe.complemento, pe.bairro, pe.cidade, pe.estado, pe.cep "
                . " FROM cliente c INNER JOIN pessoa p ON c.id_pessoa = p.id AND c.id_usuario = p.id_usuario INNER JOIN pessoa_endereco pe ON pe.id_pessoa = p.id AND pe.id_usuario = p.id_usuario "
                . " where c.id = " . $id . " and c.id_usuario = " . $idUsuario;
        $query = $this->db->query($sql);
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
        return $cliente;
    }

    function updateCliente($dados, $idUsuario) {
        $dadosEndereco = $dados['endereco'];
        $dadosTelefone = $dados['listaTelefone'];
        $dadosTelefoneRemovido = null;
        if (isset($dados['listaTelefoneRemovido'])) {
            $dadosTelefoneRemovido = $dados['listaTelefoneRemovido'];
        }
        if (!isset($dados['pessoa']['dataNascimento'])) {
            $dataNascimento = null;
        } else {
            $dataNascimento = substr($dados['pessoa']['dataNascimento'], 0, 10);
        }
        $pessoa = array('nome' => $dados['pessoa']['nome'], 'sobre_nome' => $dados['pessoa']['sobreNome'], 'sexo' => $dados['pessoa']['sexo'], 'data_cadastro' => date("Y-m-d"), 'data_nascimento' => $dataNascimento, 'id_usuario' => $idUsuario, 'ativo' => '1');
        $this->db->where('id', $dados['pessoa']['id']);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('pessoa', $pessoa);
        $pessoa_id = $dados['pessoa']['id'];
        if (!isset($dados['rg'])) {
            $dados['rg'] = "";
        }
        if (!isset($dados['cpf'])) {
            $dados['cpf'] = "";
        }
        $cliente = array('cpf' => $dados['cpf'], 'rg' => $dados['rg'], 'email' => $dados['email']);
        $this->db->where('id', $dados['id']);
        $this->db->where('id_pessoa', $pessoa_id);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('cliente', $cliente);
        if (!isset($dadosEndereco['complemento'])) {
            $dadosEndereco['complemento'] = "";
        }
        $endereco = array('rua' => $dadosEndereco['logradouro'], 'numero' => $dadosEndereco['numero'], 'complemento' => $dadosEndereco['complemento'],
            'bairro' => $dadosEndereco['bairro'], 'cidade' => $dadosEndereco['cidade'], 'estado' => $dadosEndereco['uf'], 'cep' => $dadosEndereco['cep']);
        $this->db->where('id', $dadosEndereco['id']);
        $this->db->where('id_pessoa', $pessoa_id);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('pessoa_endereco', $endereco);
        foreach ($dadosTelefone as $telefoneAux) {
            if (!isset($telefoneAux['id'])) {
                $telefone = array('telefone' => $telefoneAux['numero'], 'tipo_telefone' => $telefoneAux['tipoTelefone']['id'], 'id_pessoa' => $pessoa_id, 'id_usuario' => $idUsuario, 'ativo' => '1');
                $this->db->insert('pessoa_telefone', $telefone);
            } else {
                $telefone = array('telefone' => $telefoneAux['numero'], 'tipo_telefone' => $telefoneAux['tipoTelefone']['id']);
                $this->db->where('id', $telefoneAux['id']);
                $this->db->where('id_pessoa', $pessoa_id);
                $this->db->where('id_usuario', $idUsuario);
                $this->db->update('pessoa_telefone', $telefone);
            }
            unset($telefone);
        }
        if ($dadosTelefoneRemovido != null) {
            foreach ($dadosTelefoneRemovido as $telefoneAux) {
                $telefone = array('ativo' => '0');
                $this->db->where('id', $telefoneAux['id']);
                $this->db->where('id_pessoa', $pessoa_id);
                $this->db->where('id_usuario', $idUsuario);
                $this->db->update('pessoa_telefone', $telefone);
                unset($telefone);
            }
        }
        return array('msgRetorno' => 'Alterado com sucesso!');
    }

    function insertCliente($dados, $idUsuario) {
        $dadosEndereco = $dados['endereco'];
        $dadosTelefone = $dados['listaTelefone'];
        if (!isset($dados['pessoa']['dataNascimento'])) {
            $dataNascimento = null;
        } else {
            $dataNascimento = substr($dados['pessoa']['dataNascimento'], 0, 10);
        }
        $pessoa = array('nome' => $dados['pessoa']['nome'], 'sobre_nome' => $dados['pessoa']['sobreNome'], 'sexo' => $dados['pessoa']['sexo'], 'data_cadastro' => date("Y-m-d"), 'data_nascimento' => $dataNascimento, 'id_usuario' => $idUsuario, 'ativo' => '1');
        $this->db->insert('pessoa', $pessoa);
        $pessoa_id = 0;
        $query = $this->db->query("SELECT LAST_INSERT_ID() as id FROM pessoa WHERE ativo = true AND id_usuario = " . $idUsuario . " LIMIT 1");
        foreach ($query->result() as $row) {
            $pessoa_id = $row->id;
        }
        if (!isset($dados['rg'])) {
            $dados['rg'] = "";
        }
        if (!isset($dados['cpf'])) {
            $dados['cpf'] = "";
        }
        $cliente = array('cpf' => $dados['cpf'], 'rg' => $dados['rg'], 'email' => $dados['email'], 'id_pessoa' => $pessoa_id, 'id_usuario' => $idUsuario);
        $this->db->insert('cliente', $cliente);

        if (!isset($dadosEndereco['complemento'])) {
            $dadosEndereco['complemento'] = "";
        }
        $endereco = array('rua' => $dadosEndereco['logradouro'], 'numero' => $dadosEndereco['numero'], 'complemento' => $dadosEndereco['complemento'],
            'bairro' => $dadosEndereco['bairro'], 'cidade' => $dadosEndereco['cidade'], 'estado' => $dadosEndereco['uf'], 'cep' => $dadosEndereco['cep'],
            'id_pessoa' => $pessoa_id, 'id_usuario' => $idUsuario, 'ativo' => '1');
        $this->db->insert('pessoa_endereco', $endereco);

        foreach ($dadosTelefone as $telefoneAux) {
            $telefone = array('telefone' => $telefoneAux['numero'], 'tipo_telefone' => $telefoneAux['tipoTelefone']['id'], 'id_pessoa' => $pessoa_id, 'id_usuario' => $idUsuario, 'ativo' => '1');
            $this->db->insert('pessoa_telefone', $telefone);
            unset($telefone);
        }
        return array('msgRetorno' => 'Cadastrado com sucesso!');
    }

    function deleteCliente($dados, $idUsuario) {
        $pessoa = array('ativo' => '0');
        $this->db->where('id', $dados['pessoa']['id']);
        $this->db->where('id_usuario', $idUsuario);
        $this->db->update('pessoa', $pessoa);
        return array('msgRetorno' => 'Deletado com sucesso!');
    }

}
