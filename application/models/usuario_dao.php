<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Usuario_dao extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function logarSistema($data) {
        $sql = "SELECT * FROM usuario where usuario = '" . $data['dados']['usuario'] . "' AND senha = '" . $data['dados']['senha'] . "'";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $rowUsuario = $query->row();
            $jsonToken = array('id' => $rowUsuario->id);
            $token = json_encode($jsonToken);
            $token = jwt_encode($token);
            $token = json_encode(array('token' => $token));
            return $token;
        } else {
            return json_encode(array('msgErro' => 'Usuário ou senha incorreto!'));
        }
    }

    function getUsuarioById($data) {
        $token = $data['token'];
        $dadosToken = json_decode(jwt_decode($token));
        $this->load->database();
        $sql = "SELECT p.* FROM usuario u inner join pessoa p on u.id_pessoa = p.id where p.ativo = true AND u.id = " . $dadosToken->id;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $row = $query->row();
            $pessoa = array('nome' => $row->nome, 'sobre_nome' => $row->sobre_nome, 'sexo' => $row->sexo, 'data_cadastro' => $row->data_cadastro, 'data_nascimento' => $row->data_nascimento);
            $listaRetorno[] = array('dados' => $pessoa);
            $listaRetorno[] = array('token' => $token);
            return $listaRetorno;
        } else {
            return json_encode(array('msgErro' => 'Usuário ou senha incorreto!'));
        }
    }

}
