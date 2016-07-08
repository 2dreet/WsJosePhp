<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Usuario extends CI_Controller {

    public function logar() {
        $data = json_decode(file_get_contents('php://input'), true);

        $dados = $data['dados'];
        $retorno = "";

        if (isset($dados) && $dados != null) {
            $this->load->database();
            $sql = "SELECT * FROM usuario where usuario = '" . $dados['usuario'] . "' AND senha = '" . $dados['senha'] . "'";
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $rowUsuario = $query->row();
                $jsonToken = array('id' => $rowUsuario->id);
                $token = json_encode($jsonToken);

                $jwtUtil = new JwtUtil();
                $token = $jwtUtil->encode($token);
                $token = json_encode(array('token' => $token));
                $retorno = $token;
            } else {
                $retorno = json_encode(array('msgErro' => 'Usuário ou senha incorreto!'));
            }
        }
        header('Content-Type: application/json; charset=utf-8');
        echo ($retorno);
    }

    public function getUsuario() {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'];
        $retorno = "";
        $jwtUtil = new JwtUtil();

        if ($token != null && $jwtUtil->validate($token)) {
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $sql = "SELECT p.* FROM usuario u inner join pessoa p on u.id_pessoa = p.id where p.ativo = true AND u.id = " . $dadosToken->id;
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $row = $query->row();
                $pessoa = array('nome' => $row->nome, 'sobre_nome' => $row->sobre_nome, 'sexo' => $row->sexo, 'data_cadastro' => $row->data_cadastro, 'data_nascimento' => $row->data_nascimento);

                $listaRetorno[] = array('dados' => $pessoa);
                $listaRetorno[] = array('token' => $token);
                $retorno = $listaRetorno;
            } else {
                $retorno = json_encode(array('msgErro' => 'Usuário ou senha incorreto!'));
            }
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function insertUsuario() {
//        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = json_encode(array('id' => 1));
        $token = $jwtUtil->encode($token);
        $token = json_encode(array('token' => $token));
        header('Content-Type: application/json; charset=utf-8');
        echo $token;
    }

    public function updateUsuario() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = json_encode(array('id' => 1));
        $token = $jwtUtil->encode($token);
        $token = json_encode(array('token' => $token));
        header('Content-Type: application/json; charset=utf-8');
        echo $token;
    }

}
