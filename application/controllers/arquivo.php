<?php

class Arquivo extends CI_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->helper('file');
        $this->load->model('generic_dao');
        $this->load->model('arquivo_dao');
    }

    public function getLista() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token']) && isset($data['pagina']) && $data['pagina'] >= 0) {
            $retorno = $this->arquivo_dao->getLista($data);
            $retorno['token'] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function baixarArquivo() {
        $data = json_decode($this->input->post('json'));
        $conteudo = "";
        if (isset($data->token) && $data->token != null && jwt_validate($data->token) && isset($data->arquivoNome) && $data->arquivoNome != null) {
            $path = 'assets/upload/repositorio_arquivos/' . $data->arquivoNome;
            if (file_exists($path)) {
                $conteudo = read_file($path);
                header('HTTP/1.1 200 OK');
                header('Content-Type: text/plain; charset=ISO-8859-1');
                header("Content-Disposition: attachment; filename=" . $data->descricao);
                header("Content-Transfer-Encoding: binary");
                echo $conteudo;
            }
        }
    }

    public function deletarArquivo() {
        $data = json_decode(file_get_contents('php://input'), true);
        $retorno = null;
        if (isset($data['token']) && $data['token'] != null && jwt_validate($data['token'])) {
            $retorno = $this->arquivo_dao->deletarArquivo($data);
            $retorno['token'] = $data['token'];
        } else {
            $retorno = array('token' => false);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }
}
