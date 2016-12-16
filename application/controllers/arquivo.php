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

    public function upload() {
//        header('Content-type: application/json');
//        header("Access-Control-Allow-Origin: *,*");
//        header("Access-Control-Allow-Headers: Content-Type");
//        header("Access-Control-Max-Age: 3600");
//        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
//        header("Access-Control-Allow-Headers: x-requested-with");

        $data["valido"] = true;

        $token = $_POST['token'];
        $nomeArquivo = "";
//        try {
        if (isset($_FILES['file']['tmp_name'])) {
            $data['token'] = $this->input->post("token");
            if (!isset($data['token']) || !jwt_validate($data['token'])) {
                $data["valido"] = false;
                $data["token"] = false;
                $data["msg"] = "Falha na autenticação. Verificar Login.";
            } else {
                $file = $_FILES['file']['tmp_name'];

                $date = new DateTime();
                $nomeArquivo = $date->getTimestamp() . "_" . $_FILES['file']['name'];
                $urlArquivo = 'assets/upload/repositorio_arquivos';
                $uploadfile = $urlArquivo . '/' . $nomeArquivo;
                $infoArquivo = pathinfo($uploadfile);
                move_uploaded_file($file, $uploadfile);

                $dataInsert = array();
                $dataInsert["descricao"] = $_FILES['file']['name'];
                $dataInsert["nome"] = $nomeArquivo;
                $dataInsert["size"] = $_FILES['file']['size'];
                $dataInsert["type"] = $_FILES['file']['type'];
                $dataInsert["extension"] = strtolower($infoArquivo['extension']);
                $dataInsert["pessoa_colaborador_id"] = getDadosTokenJson($data['token'])->usuario_id;
                $dataInsert["pessoa_associado_id"] = $_POST['id'];
                $dataInsert["data"] = Date('Y-m-d');

                $this->generic_dao->insert("arquivo", $dataInsert);
            }
        } else {
            $data["valido"] = false;
            $data["msg"] = "Erro no envio do arquivo. " . $_FILES['file']['error'];
        }
//        } catch (Exception $e) {
//            $data["valido"] = false;
//            $data["msg"] = "Erro ao enviar arquivo";
//        }

        if ($data["valido"]) {
            $data["nomeArquivo"] = $nomeArquivo;
            $data["msg"] = "Arquivo " . $_FILES['file']['name'] . " enviado com sucesso";
        }

        echo json_encode($data);
    }

}
