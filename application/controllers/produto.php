<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Produto extends CI_Controller {

    public function getAllproduto() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $pagina = $data['pagina'];
        $buscaAvancada = null;
        $retorno = null;
        if (isset($token) && $token != null && $jwtUtil->validate($token) && isset($pagina) && $pagina >= 0) {
            $where = "";
            if (isset($data['buscaAvancada'])) {
                $buscaAvancada = $data['buscaAvancada'];
                if (isset($data['buscaDescricao']) && $data['buscaDescricao'] != null && trim($data['buscaDescricao']) != "") {
                    $where .= " AND descricao like '%" . $data['buscaDescricao'] . "%'";
                }

                if (isset($buscaAvancada['fornecedor']) && $buscaAvancada['fornecedor'] != null && $buscaAvancada['fornecedor'] != "") {
                    $where .= " AND id_fornecedor = " . $buscaAvancada['fornecedor']['id'];
                }

                if (isset($buscaAvancada['estoquePositivo']) && $buscaAvancada['estoquePositivo'] != null && $buscaAvancada['estoquePositivo'] == true) {
                    $where .= " AND estoque > 0 ";
                }
            }
            $listaProduto = null;
            $dadosToken = json_decode($jwtUtil->decode($token));
            if ($pagina > 0) {
                $pagina = $pagina * 10;
            }
            $this->load->database();
            $query = $this->db->query("SELECT * FROM produto where ativo = true and id_usuario = " . $dadosToken->id . $where . " LIMIT " . $pagina . ",10");
            foreach ($query->result() as $row) {
                $queryFornecedor = $this->db->query("SELECT * FROM fornecedor where ativo = true and id = " . $row->id_fornecedor);
                $fornecedor = null;



                foreach ($queryFornecedor->result() as $rowFornecedor) {
                    $fornecedor = array('id' => $rowFornecedor->id, 'descricao' => $rowFornecedor->descricao, 'email' => $rowFornecedor->email, 'telefone' => $rowFornecedor->telefone);
                }

                $produto = array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'observacao' => $row->observacao, 'estoque' => $row->estoque, 'fornecedor' => $fornecedor);
                $listaProduto[] = ($produto);
            }

            $totalRegistro = 0;
            $query = $this->db->query("SELECT count(*) as count FROM produto where ativo = true and id_usuario = " . $dadosToken->id . $where);
            foreach ($query->result() as $row) {
                $totalRegistro = $row->count;
            }

            $retorno = array('token' => $token, 'dados' => $listaProduto, 'totalRegistro' => $totalRegistro);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo (json_encode($retorno));
    }

    public function getMovimentacaoProduto() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['id'];
        $pagina = $data['pagina'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $retorno = array('token' => $token);
            if ($dados != null) {
                $listaMovimentacaoProduto = null;
                $dadosToken = json_decode($jwtUtil->decode($token));
                $this->load->database();

                if ($pagina > 0) {
                    $pagina = $pagina * 10;
                }

                $query = $this->db->query("SELECT pm.id, pm.observacao, pm.quantidade, pm.data_movimento, tm.descricao, tm.id as tipo_movimentacao FROM produto_movimentacao pm inner join tipo_movimentacao tm on pm.tipo_movimentacao = tm.id"
                        . " where pm.ativo = true and pm.id_usuario = " . $dadosToken->id . " and pm.id_produto = " . $dados
                        . " ORDER BY data_movimento desc LIMIT " . $pagina . ", 10");
                foreach ($query->result() as $row) {
                    $movimentacaoProduto = array('id' => $row->id, 'observacao' => $row->observacao, 'quantidade' => $row->quantidade,
                        'data_movimento' => str_replace(" ", "T", $row->data_movimento), 'descricao' => $row->descricao, 'tipoMovimentacao' => $row->tipo_movimentacao);
                    $listaMovimentacaoProduto[] = ($movimentacaoProduto);
                }

                $totalRegistro = 0;
                $query = $this->db->query("SELECT count(*) as count FROM produto_movimentacao"
                        . " where ativo = true and id_usuario = " . $dadosToken->id . " and id_produto = " . $dados);
                foreach ($query->result() as $row) {
                    $totalRegistro = $row->count;
                }

                $retorno = array('token' => $token, 'dados' => $listaMovimentacaoProduto, 'totalRegistro' => $totalRegistro);
            } else {
                $retorno = array('token' => $token, 'dados' => null);
            }
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo (json_encode($retorno));
    }

    public function getProdutoImagem($idProduto, $token) {
        $jwtUtil = new JwtUtil();
        if (isset($token) && $token != null && $jwtUtil->validate($token)) {
            if (isset($idProduto) && $idProduto != null) {
                $dadosToken = json_decode($jwtUtil->decode($token));
                $imagem = null;
                $this->load->database();
                $query = $this->db->query("SELECT imagem FROM produto where ativo = true and imagem is not null and id = " . $idProduto . " and id_usuario = " . $dadosToken->id . " limit 1");
                foreach ($query->result() as $row) {
                    $imagem = ($row->imagem);
                }

                if ($imagem == null || $imagem == false) {
                    $imagem = file_get_contents('no-image.png');
                }
                header("Content-Type: image/gif;");
                echo ($imagem);
            }
        }
    }

    public function insertProduto() {
        $jwtUtil = new JwtUtil();
        $imagem = false;
        if (isset($_FILES['imagem']['tmp_name'])) {
            $imagem = file_get_contents($_FILES['imagem']['tmp_name']);
        }
        $dados = json_decode($_POST['dados']);
        $token = $_POST['token'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            if ($dados != null) {
                $dadosToken = json_decode($jwtUtil->decode($token));
                $fornecedor = $dados->fornecedor;
                if ($dados->valor == 0) {
                    $dados->valor = 0.00;
                }
                if ($imagem == false) {
                    $produto = array('descricao' => $dados->descricao, 'valor' => $dados->valor, 'observacao' => $dados->observacao,
                        'estoque' => 0, 'id_fornecedor' => $fornecedor->id, 'id_usuario' => $dadosToken->id, 'ativo' => '1');
                } else {
                    $produto = array('descricao' => $dados->descricao, 'valor' => $dados->valor, 'observacao' => $dados->observacao,
                        'estoque' => 0, 'id_fornecedor' => $fornecedor->id, 'imagem' => $imagem, 'id_usuario' => $dadosToken->id, 'ativo' => '1');
                }

                $this->load->database();
                $this->db->insert('produto', $produto);

                $retorno = array('token' => $token, 'sucesso' => true);
            } else {
                $retorno = array('token' => $token, 'sucesso' => false);
            }
        } else {
            $retorno = array('token' => false);
        }

        echo json_encode($retorno);
    }

    public function updatetProduto() {
        $jwtUtil = new JwtUtil();
        $imagem = false;
        if (isset($_FILES['imagem']['tmp_name'])) {
            $imagem = file_get_contents($_FILES['imagem']['tmp_name']);
        }
        $dados = json_decode($_POST['dados']);
        $token = $_POST['token'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            if ($dados != null) {
                $dadosToken = json_decode($jwtUtil->decode($token));
                $fornecedor = $dados->fornecedor;
                if ($dados->valor == 0) {
                    $dados->valor = 0.00;
                }

                if ($imagem == false) {
                    $produto = array('descricao' => $dados->descricao, 'valor' => $dados->valor, 'observacao' => $dados->observacao,
                        'id_fornecedor' => $fornecedor->id);
                } else {
                    $produto = array('descricao' => $dados->descricao, 'valor' => $dados->valor, 'observacao' => $dados->observacao,
                        'id_fornecedor' => $fornecedor->id, 'imagem' => $imagem);
                }

                $this->load->database();
                $this->db->where('id', $dados->id);
                $this->db->where('id_usuario', $dadosToken->id);
                $this->db->update('produto', $produto);

                $retorno = array('token' => $token, 'sucesso' => true);
            } else {
                $retorno = array('token' => $token, 'sucesso' => false);
            }
        } else {
            $retorno = array('token' => false);
        }

        echo json_encode($retorno);
    }

    public function movimentarProduto() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $retorno = array('token' => $token);
            if ($dados != null) {

                $dadosTeste = json_encode($dados);

                $dadosToken = json_decode($jwtUtil->decode($token));

                $estoque = $dados['estoque'];
                if ($dados['tipoMovimentacao'] == "1") {
                    $estoque = $estoque + $dados['estoque_movimento'];
                } else if ($dados['tipoMovimentacao'] == "3") {
                    $estoque = $estoque - $dados['estoque_movimento'];
                } else if ($dados['tipoMovimentacao'] == "4") {
                    $estoque = $estoque - $dados['estoque_movimento'];
                } else if ($dados['tipoMovimentacao'] == "5") {
                    $estoque = $dados['estoque_movimento'];
                }
                $produto = array('estoque' => $estoque);

                $this->load->database();
                $this->db->where('id', $dados['id']);
                $this->db->where('id_usuario', $dadosToken->id);
                $this->db->update('produto', $produto);

                $produtoMovimentacao = array('observacao' => $dados['estoque_movimento_observacao'], 'quantidade' => $dados['estoque_movimento'], 'data_movimento' => date("Y-m-d H:i:s"), 'id_produto' => $dados['id'], 'tipo_movimentacao' => $dados['tipoMovimentacao'], 'id_usuario' => $dadosToken->id, 'ativo' => '1');
                $this->db->insert('produto_movimentacao', $produtoMovimentacao);

                $retorno = array('token' => $token, 'sucesso' => true);
            } else {
                $retorno = array('token' => $token, 'sucesso' => false);
            }
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

    public function deleteProduto() {
        $data = json_decode(file_get_contents('php://input'), true);
        $jwtUtil = new JwtUtil();
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            $produto = array('ativo' => '0');
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $this->db->where('id', $dados['id']);
            $this->db->where('id_usuario', $dadosToken->id);
            $this->db->update('produto', $produto);

            $retorno = array('token' => $token);
        } else {
            $retorno = array('token' => false);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
