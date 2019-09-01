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
 * Finalidade: Redirecionar por tipo de usuário
 * ------------------------------------------------------------------------------------------
 */

require_once '../bin/sessao.php';
require_once '../bin/funcoes.php';

try {
    # Verifica o request
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $post = $_POST;
    } else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $post = $_GET;
    }

    $_SESSION['tmp']['post'] = (object) $post;         # Salva o post na sessão
    #
    #
    #  Verifica o preenchimento dos email
    require_once '../negocio/Email.php';        # Requer classe Email
    if (!Email::email_valido($post['email'])) { # Testa email
        throw new Exception('log001');          # Lança exceção caso FALSE
    }

    # Verifica se o usuário existe
    require_once '../dados/Usuario.php';                # Requer a classe Usuario
    $Usuario = new Usuario();                           # Instancia o objeto Usuario

    if (!$Usuario->existe($post['email'])[0]) { # Usuário existe no banco?
        throw new Exception('usu001');                  # Lança exceção caso FALSE
    } else {

        $Usuario->set($post['email']);
        # Verifica o preenchimento da senha
        require_once '../negocio/Senha.php';        # Requer a classe Senha
        if (!Senha::senha_valida($post['senha'])) { # Testa senha
            throw new Exception('log002');          # Lança exceção caso FALSE
        }

        # Calcula a senha para o formato do banco
        $senha = sha1(md5($post['senha']));

        # Verifica a senha
        if ($senha != $Usuario->get_dados()->usuario_senha) {
            throw new Exception('sen001');
        }

        # Verifica se o usuário está liberado
        if (!boolval($Usuario->get_dados()->usuario_liberado)) {
            throw new Exception('usu002');
        }
        
        # Hora atual
        $agora = agora();
        
        # Atualiza o último acesso no banco
        $Usuario->set_ultimo_acesso($agora, $Usuario->get_dados()->usuario_id);
        
        # Grava o usuário na sessão
        $_SESSION['usuario'] = $Usuario->get_dados();
        
        # Atualiza o último acesso no próprio objetoUsuario
        $_SESSION['usuario']->usuario_ultimo_acesso = $agora;
 
        
        # Seleciona a página inicial por tipo de usuário
        switch ($_SESSION['usuario']->usuario_tipo_id) {
            case 1:
                $location = '../adm/mailbox.php';
                break;
        }
    }
    unset($_SESSION['tmp']['post']);
    header('location: ' . $location);
} catch (Exception $exc) {
    $_SESSION['erro'] = (object) ['codigo' => $exc->getMessage()];  # Salva o código do erro na sessão
    header('location: ../login');
}


