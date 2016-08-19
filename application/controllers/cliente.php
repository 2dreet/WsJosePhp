<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Cliente extends CI_Controller {

    public function getAllCliente() {
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
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $query = $this->db->query("SELECT c.id as idC, c.cpf, c.rg, c.email, p.id as idP, p.nome, p.sobre_nome, p.sexo, p.data_nascimento, pe.id as idPe, pe.rua, pe.numero, pe.complemento, pe.bairro, pe.cidade, pe.estado, pe.cep "
                    . " FROM cliente c INNER JOIN pessoa p ON c.id_pessoa = p.id AND c.id_usuario = p.id_usuario INNER JOIN pessoa_endereco pe ON pe.id_pessoa = p.id AND pe.id_usuario = p.id_usuario "
                    . " where p.ativo = true " . $where . " and c.id_usuario = " . $dadosToken->id . " LIMIT " . $pagina . "," . $limit);

            foreach ($query->result() as $row) {
                $listaTelefone = null;
                $queryTelefone = $this->db->query("SELECT pe.id, pe.telefone, pe.tipo_telefone, tt.descricao "
                        . " FROM pessoa_telefone pe INNER JOIN tipo_telefone tt ON pe.tipo_telefone = tt.id "
                        . " where pe.ativo = true and pe.id_usuario = " . $dadosToken->id . " AND pe.id_pessoa = " . $row->idP);
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
                    . "  where p.ativo = true " . $where . " and c.id_usuario = " . $dadosToken->id);
            foreach ($query->result() as $row) {
                $totalRegistro = $row->count;
            }

            $retorno = array('token' => $token, 'dados' => $listaCliente, 'totalRegistro' => $totalRegistro);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }
    
    
    public function getCliente() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $id = $data['idCliente'];
        $retorno = null;
        if (isset($token) && $token != null && $jwtUtil->validate($token) && isset($id) && $id > 0) {
            $cliente = null;
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $query = $this->db->query("SELECT c.id as idC, c.cpf, c.rg, c.email, p.id as idP, p.nome, p.sobre_nome, p.sexo, p.data_nascimento, pe.id as idPe, pe.rua, pe.numero, pe.complemento, pe.bairro, pe.cidade, pe.estado, pe.cep "
                    . " FROM cliente c INNER JOIN pessoa p ON c.id_pessoa = p.id AND c.id_usuario = p.id_usuario INNER JOIN pessoa_endereco pe ON pe.id_pessoa = p.id AND pe.id_usuario = p.id_usuario "
                    . " where p.ativo = true c.id = " . $id . " and c.id_usuario = " . $dadosToken->id);

            foreach ($query->result() as $row) {
                $listaTelefone = null;
                $queryTelefone = $this->db->query("SELECT pe.id, pe.telefone, pe.tipo_telefone, tt.descricao "
                        . " FROM pessoa_telefone pe INNER JOIN tipo_telefone tt ON pe.tipo_telefone = tt.id "
                        . " where pe.ativo = true and pe.id_usuario = " . $dadosToken->id . " AND pe.id_pessoa = " . $row->idP);
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


            $retorno = array('token' => $token, 'cliente' => $cliente);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function updateCliente() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $dadosToken = json_decode($jwtUtil->decode($token));
            $dadosEndereco = $dados['endereco'];
            $dadosTelefone = $dados['listaTelefone'];
            $dadosTelefoneRemovido = null;
            if (isset($dados['listaTelefoneRemovido'])) {
                $dadosTelefoneRemovido = $dados['listaTelefoneRemovido'];
            }
            $dataNascimento = substr($dados['pessoa']['dataNascimento'], 0, 10);

            $this->load->database();
            $pessoa = array('nome' => $dados['pessoa']['nome'], 'sobre_nome' => $dados['pessoa']['sobreNome'], 'sexo' => $dados['pessoa']['sexo'], 'data_cadastro' => date("Y-m-d"), 'data_nascimento' => $dataNascimento, 'id_usuario' => $dadosToken->id, 'ativo' => '1');
            $this->db->where('id', $dados['pessoa']['id']);
            $this->db->where('id_usuario', $dadosToken->id);
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
            $this->db->where('id_usuario', $dadosToken->id);
            $this->db->update('cliente', $cliente);

            if (!isset($dadosEndereco['complemento'])) {
                $dadosEndereco['complemento'] = "";
            }

            $endereco = array('rua' => $dadosEndereco['logradouro'], 'numero' => $dadosEndereco['numero'], 'complemento' => $dadosEndereco['complemento'],
                'bairro' => $dadosEndereco['bairro'], 'cidade' => $dadosEndereco['cidade'], 'estado' => $dadosEndereco['uf'], 'cep' => $dadosEndereco['cep']);
            $this->db->where('id', $dadosEndereco['id']);
            $this->db->where('id_pessoa', $pessoa_id);
            $this->db->where('id_usuario', $dadosToken->id);
            $this->db->update('pessoa_endereco', $endereco);


            foreach ($dadosTelefone as $telefoneAux) {
                if (!isset($telefoneAux['id'])) {
                    $telefone = array('telefone' => $telefoneAux['numero'], 'tipo_telefone' => $telefoneAux['tipoTelefone']['id'], 'id_pessoa' => $pessoa_id, 'id_usuario' => $dadosToken->id, 'ativo' => '1');
                    $this->db->insert('pessoa_telefone', $telefone);
                } else {
                    $telefone = array('telefone' => $telefoneAux['numero'], 'tipo_telefone' => $telefoneAux['tipoTelefone']['id']);
                    $this->db->where('id', $telefoneAux['id']);
                    $this->db->where('id_pessoa', $pessoa_id);
                    $this->db->where('id_usuario', $dadosToken->id);
                    $this->db->update('pessoa_telefone', $telefone);
                }
                unset($telefone);
            }

            if ($dadosTelefoneRemovido != null) {
                foreach ($dadosTelefoneRemovido as $telefoneAux) {
                    $telefone = array('ativo' => '0');
                    $this->db->where('id', $telefoneAux['id']);
                    $this->db->where('id_pessoa', $pessoa_id);
                    $this->db->where('id_usuario', $dadosToken->id);
                    $this->db->update('pessoa_telefone', $telefone);
                    unset($telefone);
                }
            }

            $retorno = array('token' => $token);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function insertCliente() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $dadosToken = json_decode($jwtUtil->decode($token));
            $dadosEndereco = $dados['endereco'];
            $dadosTelefone = $dados['listaTelefone'];
            $dataNascimento = substr($dados['pessoa']['dataNascimento'], 0, 10);

            $this->load->database();
            $pessoa = array('nome' => $dados['pessoa']['nome'], 'sobre_nome' => $dados['pessoa']['sobreNome'], 'sexo' => $dados['pessoa']['sexo'], 'data_cadastro' => date("Y-m-d"), 'data_nascimento' => $dataNascimento, 'id_usuario' => $dadosToken->id, 'ativo' => '1');
            $this->db->insert('pessoa', $pessoa);

            $pessoa_id = 0;
            $query = $this->db->query("SELECT LAST_INSERT_ID() as id FROM pessoa WHERE ativo = true AND id_usuario = " . $dadosToken->id . " LIMIT 1");
            foreach ($query->result() as $row) {
                $pessoa_id = $row->id;
            }

            if (!isset($dados['rg'])) {
                $dados['rg'] = "";
            }

            if (!isset($dados['cpf'])) {
                $dados['cpf'] = "";
            }

            $cliente = array('cpf' => $dados['cpf'], 'rg' => $dados['rg'], 'email' => $dados['email'], 'id_pessoa' => $pessoa_id, 'id_usuario' => $dadosToken->id);
            $this->db->insert('cliente', $cliente);

            if (!isset($dadosEndereco['complemento'])) {
                $dadosEndereco['complemento'] = "";
            }

            $endereco = array('rua' => $dadosEndereco['logradouro'], 'numero' => $dadosEndereco['numero'], 'complemento' => $dadosEndereco['complemento'],
                'bairro' => $dadosEndereco['bairro'], 'cidade' => $dadosEndereco['cidade'], 'estado' => $dadosEndereco['uf'], 'cep' => $dadosEndereco['cep'],
                'id_pessoa' => $pessoa_id, 'id_usuario' => $dadosToken->id, 'ativo' => '1');
            $this->db->insert('pessoa_endereco', $endereco);

            foreach ($dadosTelefone as $telefoneAux) {
                $telefone = array('telefone' => $telefoneAux['numero'], 'tipo_telefone' => $telefoneAux['tipoTelefone']['id'], 'id_pessoa' => $pessoa_id, 'id_usuario' => $dadosToken->id, 'ativo' => '1');
                $this->db->insert('pessoa_telefone', $telefone);
                unset($telefone);
            }

            $retorno = array('token' => $token);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function deleteCliente() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $pessoa = array('ativo' => '0');
            $this->db->where('id', $dados['id']);
            $this->db->where('id_usuario', $dadosToken->id);
            $this->db->update('pessoa', $pessoa);
            $retorno = array('token' => $token);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
