<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Arquivo_dao extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

//    public function getLista($data) {
//        //retorna uma lista de arquivo
//        $pagina = $data['pagina'];
//        $limit = $data['limit'];
//        if ($pagina > 0) {
//            $pagina = $pagina * $limit;
//        }
//
//        $where = "";
//        $orderBy = "";
//        $nomePai = "";
//        $contratoPai = "";
//
//        if (isset($data['buscaAvancada']) && $data['buscaAvancada'] != null) {
//            $buscaAvancada = $data['buscaAvancada'];
//
//            if (isset($buscaAvancada['associado']) && isset($buscaAvancada['associado']['id'])) {
//                $associado = $this->associado_dao->getAssociadoById(array('idPessoa' => $buscaAvancada['associado']['id']));
//                $nomePai = $associado['associado']['pessoa']['nome'];
//                $contratoPai = $associado['associado']['contrato'];
//                $where .= " AND pessoa_associado_id = " . $buscaAvancada['associado']['id'];
//            }
//        }
//
//        if (isset($data["orderBy"]) && !empty($data["orderBy"])) {
//            //se contém |trim
//            if (strpos($data["orderBy"], '|trim') !== false) {
//                $data["orderBy"] = str_replace("|trim", "", $data["orderBy"]);
//                $orderBy .= " order by trim(" . $data["orderBy"] . ") " . $data["orderByTipo"];
//            } else {
//                $orderBy .= " order by " . $data["orderBy"] . " " . $data["orderByTipo"];
//            }
//        }
//
//        $query = $this->db->query("SELECT * FROM arquivo a " .
//                "where a.ativo = true " . $where . $orderBy . " LIMIT " . $pagina . "," . $limit);
//        $listaContato = $query->result("array");
//
//        $query = $this->db->query("SELECT count(*) as count FROM arquivo a where a.ativo = true " . $where . $orderBy);
//        $totalRegistro = $query->first_row()->count;
//
//        return array('dados' => $listaContato, 'totalRegistro' => $totalRegistro, 'nome' => $nomePai, 'contrato' => $contratoPai);
//    }

    public function deletarArquivo($idArquivo, $usuarioID, $nomeArquivo) {
        $this->db->where('id', $idArquivo);
        $this->db->where('usuario_id', $usuarioID);
        $this->db->delete('arquivo');
        $retorno = unlink('arquivos/contas/' . $nomeArquivo);
    }

    public function upload($file, $usuarioId, $contaId = null, $clienteId = null) {
        $nomeArquivo = "";
        if (isset($file['tmp_name'])) {
            $fileName = $file['tmp_name'];

            $date = new DateTime();
            $nomeArquivo = $usuarioId . "_" . $date->getTimestamp() . "_" . $file['name'];
            $urlArquivo = 'arquivos/contas';
            $uploadfile = $urlArquivo . '/' . $nomeArquivo;
            $infoArquivo = pathinfo($uploadfile);
            move_uploaded_file($fileName, $uploadfile);

            $dataInsert = array();
            $dataInsert["descricao"] = $file['name'];
            $dataInsert["nome"] = $nomeArquivo;
            $dataInsert["size"] = $file['size'];
            $dataInsert["type"] = $file['type'];
            $dataInsert["extension"] = strtolower($infoArquivo['extension']);
            $dataInsert["usuario_id"] = $usuarioId;
            $dataInsert["conta_id"] = $contaId;
            $dataInsert["cliente_id"] = $clienteId;
            $dataInsert["data"] = Date('Y-m-d');

            $this->db->insert("arquivo", $dataInsert);
            return true;
        }
        return false;
    }

}
