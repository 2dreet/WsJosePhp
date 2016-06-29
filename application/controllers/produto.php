<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include 'JwtUtil.php';

class Produto extends CI_Controller {

    public function getAllproduto($token) {
        $jwtUtil = new JwtUtil();
        $retorno = null;
        if (isset($token) && $token != null && $jwtUtil->validate($token)) {
            $listaProduto = null;
            $dadosToken = json_decode($jwtUtil->decode($token));
            $this->load->database();
            $query = $this->db->query("SELECT * FROM produto where ativo = true and id_usuario = " . $dadosToken->id);
            foreach ($query->result() as $row) {
                $queryFornecedor = $this->db->query("SELECT * FROM fornecedor where ativo = true and id = " . $row->id_fornecedor);
                $fornecedor = null;
                foreach ($queryFornecedor->result() as $rowFornecedor) {
                    $fornecedor = array('id' => $rowFornecedor->id, 'descricao' => $rowFornecedor->descricao, 'email' => $rowFornecedor->email, 'telefone' => $rowFornecedor->telefone);
                }

                $produto = array('id' => $row->id, 'descricao' => $row->descricao, 'valor' => $row->valor, 'observacao' => $row->observacao, 'estoque' => $row->estoque, 'fornecedor' => $fornecedor);
                $listaProduto[] = ($produto);
            }

            $retorno = array('token' => $token, 'dados' => $listaProduto);
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
        $jwtUtil = new JwtUtil();
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'];
        $dados = $data['dados'];
        $retorno = null;
        if ($token != null && $jwtUtil->validate($token)) {
            if ($dados != null) {
                $dadosToken = json_decode($jwtUtil->decode($token));

                $estoque = $dados['estoque'];
                if ($dados['tipo_movimentacao'] == 1) {
                    $estoque = $estoque + $dados['estoque_movimento'];
                } else if ($dados['tipo_movimentacao'] == 3) {
                    $estoque = $estoque - $dados['estoque_movimento'];
                } else if ($dados['tipo_movimentacao'] == 4) {
                    $estoque = $estoque - $dados['estoque_movimento'];
                } else if ($dados['tipo_movimentacao'] == 5) {
                    $estoque = $dados['estoque_movimento'];
                }
                $produto = array('estoque' => $estoque);

                $this->load->database();
                $this->db->where('id', $dados->id);
                $this->db->where('id_usuario', $dadosToken->id);
                $this->db->update('produto', $produto);

                $produtoMovimentacao = array('observacao' => $dados['observacao'], 'quantidade' => $dados['estoque_movimento'], 'data_movimento' => date('Y-m-d H:i'), 'id_produto' => $dados['id_produto'], 'tipo_movimentacao' => $dados['tipo_movimentacao'], 'id_usuario' => $dadosToken->id, 'ativo' => '1');
                $this->db->insert('produto_movimentacao', $produtoMovimentacao);
                
                $retorno = array('token' => $token, 'sucesso' => true);
            } else {
                $retorno = array('token' => $token, 'sucesso' => false);
            }
        } else {
            $retorno = array('token' => false);
        }

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
