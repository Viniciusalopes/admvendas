<?php

/*
 * A licença MIT
 *
 * Copyright 2019 Viniciusalopes Tecnologia <suporte@viniciusalopes.com.br>.
 *
 * É concedida permissão, gratuitamente, a qualquer pessoa que obtenha uma cópia
 * deste software e dos arquivos de documentação associados (o "Software"), para
 * negociar o Software sem restrições, incluindo, sem limitação, os direitos de uso,
 * cópia, modificação e fusão, publicar, distribuir, sublicenciar e/ou vender cópias
 * do Software, e permitir que as pessoas a quem o Software é fornecido o façam,
 * sujeitas às seguintes condições:
 *
 * O aviso de copyright acima e este aviso de permissão devem ser incluídos em
 * todas as cópias ou partes substanciais do Software.
 *
 * O SOFTWARE É FORNECIDO "NO ESTADO EM QUE SE ENCONTRA", SEM NENHUM TIPO DE GARANTIA,
 * EXPRESSA OU IMPLÍCITA, INCLUINDO, MAS NÃO SE LIMITANDO ÀS GARANTIAS DE COMERCIALIZAÇÃO,
 * ADEQUAÇÃO A UM FIM ESPECÍFICO E NÃO VIOLAÇÃO. EM NENHUMA CIRCUNSTÂNCIA, OS AUTORES
 * OU PROPRIETÁRIOS DE DIREITOS DE AUTOR PODERÃO SER RESPONSABILIZADOS POR QUAISQUER
 * REIVINDICAÇÕES, DANOS OU OUTRAS RESPONSABILIDADES, QUER EM AÇÃO DE CONTRATO,
 * DELITO OU DE OUTRA FORMA, DECORRENTES DE, OU EM CONEXÃO COM O SOFTWARE OU O USO
 * OU OUTRAS NEGOCIAÇÕES NO PROGRAMAS.
 * ------------------------------------------------------------------------------------------
 * Projeto   : AdmVendas
 * Criado em : 01/09/2019
 * Autor     : Viniciusalopes (Vovolinux) <suporte@viniciusalopes.com.br>
 * Finalidade: Dados do Usuário
 * ------------------------------------------------------------------------------------------
 */

/**
 * Description of Usuario
 *
 * @author vovostudio
 */
require_once '../dados/BdConn.php';
require_once '../bin/funcoes.php';

class Usuario {

    private $dados;

    /**
     * @abstract Verifica se existe um usuário com o email
     * @param type $email
     * @return boolean
     */
    function existe($email) {
        $query = "SELECT usuario_id FROM tab_usuario WHERE usuario_email = ? LIMIT 1";
        $parametros = [$email];
        $consulta = (new BdConn())->consulta($query, $parametros);
        if (count($consulta) > 0) {
            return [TRUE, $consulta[0]->usuario_id];
        }
        return [FALSE, 0];
    }

    /**
     * @abstract Obtém os dados do usuário pelo e-mail
     * @param type $email
     * @return object Usuario
     */
    function set($email) {
        $query = "SELECT * FROM tab_usuario WHERE usuario_email = ? LIMIT 1";
        $parametros = [$email];
        $this->dados = (new BdConn())->consulta($query, $parametros)[0];
    }

    /**
     * 
     * @return object Usuario->dados
     */
    function get_dados() {
        return $this->dados;
    }

    /**
     * 
     * @return objects Usuario
     */
    function get($id = 'todos', $objeto = '') {

        $query = trim(
                "SELECT u.*, s.*, t.*, x.* FROM tab_usuario u 
                INNER JOIN tab_status s ON u.usuario_status_id = s.status_id 
                INNER JOIN tab_tipo   t ON u.usuario_tipo_id   = t.tipo_id 
                INNER JOIN tab_sexo   x ON u.usuario_sexo_id   = x.sexo_id "
        );
        $parametros = [];

        if ($id != 'todos') {
            $query .= " WHERE u.usuario_id = ? ";
            $parametros[] = $id;
        }

        $query .= " ORDER BY u.usuario_nome";

        /**
         *      DESATIVADO
         * 
         *      Para obter o nome de quem cadastrou, para todos os usuários da consulta
         *      Foi de
         *       $consulta = (new BdConn())->consulta($query, $parametros);
         *       foreach ($consulta as $c) {
         *           $c->usuario_inclusao_usuario_nome = $this->get_nome_usuario($c->usuario_inclusao_usuario_id);
         *       }
         *       
         *       return $consulta;
         *
         */
        $ret = (new BdConn())->consulta($query, $parametros);
        return ($id == 'todos') ? $ret : $ret[0];
    }

    function get_nome_usuario($usuario_id) {
        return (new BdConn())->consulta("SELECT usuario_nome FROM tab_usuario WHERE usuario_id = ? LIMIT 1", [$usuario_id])[0]->usuario_nome;
    }

    function set_ultimo_acesso($usuario_ultimo_acesso, $usuario_id) {
        $query = "UPDATE tab_usuario SET usuario_ultimo_acesso = ? WHERE usuario_id = ?";
        $parametros = [$usuario_ultimo_acesso, $usuario_id];
        (new BdConn())->executa($query, $parametros);
    }

    function get_padrao() {
        return (object) [
                    'usuario_id' => 0,
                    'usuario_status_id' => 1,
                    'usuario_nome' => '',
                    'usuario_email' => '',
                    'usuario_senha' => '',
                    'usuario_tipo_id' => 1,
                    'usuario_liberado' => 1,
                    'usuario_idioma' => 'pt_BR',
                    'usuario_sexo_id' => 1,
                    'usuario_inclusao_data' => agora(),
                    'usuario_inclusao_usuario_id' => $_SESSION['usuario']->usuario_id,
                    'usuario_ultimo_acesso' => NULL
        ];
    }

    function insert($post) {
        $this->dados = $_SESSION['tmp']['objUsuario'];

        $query = trim(
                "INSERT INTO tab_usuario (
                usuario_status_id,
                usuario_nome,
                usuario_email,
                usuario_tipo_id,
                usuario_liberado,
                usuario_idioma,
                usuario_sexo_id,
                usuario_inclusao_data,
                usuario_inclusao_usuario_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) "
        );
        # Assume o ['tmp']['objUsuario'] caso seja um clinete <-  :D Fabiano(Soluma)
        if (intval($post->usuario_tipo_id) === 2) {
            $post = $this->dados;
        }

        $parametros = [
            $post->usuario_status_id,
            $post->usuario_nome,
            $post->usuario_email,
            $post->usuario_tipo_id,
            boolval($post->usuario_liberado),
            $this->dados->usuario_idioma,
            $post->usuario_sexo_id,
            $this->dados->usuario_inclusao_data,
            $this->dados->usuario_inclusao_usuario_id
        ];

        (new BdConn())->executa($query, $parametros);
        Email::enviar_mensagem($post->usuario_nome, $post->usuario_sexo_id, $post->usuario_email, sha1(md5($this->dados->usuario_email)), "Novo Cadastro");
    }

    function update($post) {
        $this->dados = $_SESSION['tmp']['objUsuario'];

        $query = trim(
                "UPDATE tab_usuario SET
                usuario_status_id = ?,
                usuario_nome = ?,
                usuario_email = ?,
                usuario_senha = ?,
                usuario_tipo_id = ?,
                usuario_liberado = ?,
                usuario_sexo_id = ? 
                WHERE usuario_id = ?"
        );

        $senha = ((isset($post->senha1)) ? sha1(md5($post->senha1)) : $this->dados->usuario_senha);
        $parametros = [
            $post->usuario_status_id,
            $post->usuario_nome,
            $post->usuario_email,
            $senha,
            $this->dados->usuario_tipo_id,
            boolval($post->usuario_liberado),
            $post->usuario_sexo_id,
            $this->dados->usuario_id
        ];

        (new BdConn())->executa($query, $parametros);
    }

    function delete($usuario_id) {
        $query = "DELETE FROM tab_usuario WHERE usuario_id = ?";
        $parametros = [$usuario_id];
        (new BdConn())->executa($query, $parametros);
    }

    function validar($post) {

        if (texto_invalido($post->usuario_nome, 2)) {
            throw new Exception('usu003');
        }

        require_once '../negocio/Email.php';                    # Requer classe Email
        if (!Email::email_valido($post->usuario_email)) {       # Testa email
            throw new Exception('usu004');                      # Lança exceção caso FALSE
        }

        if (isset($post->senha1)) {
            if ($post->senha1 == '' && $post->senha2 == '') {
                throw new Exception('sen002');
            }
            if ($post->senha1 !== $post->senha2) {
                throw new Exception('sen003');
            }
        }

        $_SESSION['tmp']['usuario_ja_existe'] = $usuario_ja_existe = $this->existe($post->usuario_email);     # Salva a consulta na variavel 

        if ($_SESSION['modulo']->pagina == 'incluir.php') {
            if ($usuario_ja_existe[0]) {
                throw new Exception('usu005');
            }
        }

        if ($_SESSION['modulo']->pagina == 'editar.php') {
            if ($usuario_ja_existe[0] && ($usuario_ja_existe[1] != $_SESSION['tmp']['objUsuario']->usuario_id)) {
                throw new Exception('usu005');
            }
        }
        return TRUE;
    }

}
