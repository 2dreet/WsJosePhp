<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Arquivo_dao extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('generic_dao');
        $this->load->model('associado_dao');
    }

    public function getLista($data) {
        //retorna uma lista de arquivo
        $pagina = $data['pagina'];
        $limit = $data['limit'];
        if ($pagina > 0) {
            $pagina = $pagina * $limit;
        }

        $where = "";
        $orderBy = "";
        $nomePai = "";
        $contratoPai = "";

        if (isset($data['buscaAvancada']) && $data['buscaAvancada'] != null) {
            $buscaAvancada = $data['buscaAvancada'];

            if (isset($buscaAvancada['associado']) && isset($buscaAvancada['associado']['id'])) {
                $associado = $this->associado_dao->getAssociadoById(array('idPessoa' => $buscaAvancada['associado']['id']));
                $nomePai = $associado['associado']['pessoa']['nome'];
                $contratoPai = $associado['associado']['contrato'];
                $where .= " AND pessoa_associado_id = " . $buscaAvancada['associado']['id'];
            }
        }

        if (isset($data["orderBy"]) && !empty($data["orderBy"])) {
            //se contém |trim
            if (strpos($data["orderBy"], '|trim') !== false) {
                $data["orderBy"] = str_replace("|trim", "", $data["orderBy"]);
                $orderBy .= " order by trim(" . $data["orderBy"] . ") " . $data["orderByTipo"];
            } else {
                $orderBy .= " order by " . $data["orderBy"] . " " . $data["orderByTipo"];
            }
        }

        $query = $this->db->query("SELECT * FROM arquivo a " .
                "where a.ativo = true " . $where . $orderBy . " LIMIT " . $pagina . "," . $limit);
        $listaContato = $query->result("array");

        $query = $this->db->query("SELECT count(*) as count FROM arquivo a where a.ativo = true " . $where . $orderBy);
        $totalRegistro = $query->first_row()->count;

        return array('dados' => $listaContato, 'totalRegistro' => $totalRegistro, 'nome' => $nomePai, 'contrato' => $contratoPai);
    }

    public function deletarArquivo($data) {
        $dados = $data['dados'];
        $this->db->where('id', $dados['arquivoID']);
        $this->db->delete('arquivo');

        $retorno = unlink('assets/upload/repositorio_arquivos/' . $dados['nome']);
        return array('msg' => 'Deletado com sucesso!');
    }

}
