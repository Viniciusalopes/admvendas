<?php

/*
 * A licença MIT
 *
 * Copyright 2018 Viniciusalopes Tecnologia.
 *
 * É concedida permissão, gratuitamente, a qualquer pessoa que obtenha uma cópia  
 * deste software e dos arquivos de documentação associados (o "Software"), para 
 * negociar o Software sem restrições, incluindo, sem limitação, os direitos de uso,
 * cópia, modificação e fusão, publicar, distribuir, sublicenciar e/ou vender cópias
 * do Software, e permitir que as pessoas a quem o Software é fornecido o façam, 
 * sujeitas às seguintes condições:
 *
 * O aviso de copyright acima e este aviso de permissão devem ser incluídos em todas 
 * as cópias ou partes substanciais do Software.
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
 * Autor     : Viniciusalopes (Vovolinux) <suporte@vovolinux.com.br>
 * Finalidade: Iniciar e validar dados da $_SESSION
 * ------------------------------------------------------------------------------------------
 *
 */

# Inicia a sessão
date_default_timezone_set('America/Sao_Paulo');
session_start();

require_once '../bin/funcoes.php';

# Função que protege a página

function protege() {

    require_once '../dados/Usuario.php';
    if (!isset($_SESSION['usuario'])) {
        $_SESSION['logOff'][] = 1;
        logOff();
        return;
    } else {

        # Recebe os dados do usuário da sessão
        $email = ($_SESSION['usuario']->usuario_email) ?? '';
        $senha = ($_SESSION['usuario']->usuario_senha) ?? '';

        try {
            # Obtém os dados do usuário cadastrado com o e-mail
            if (!(new Usuario())->existe($email)[0]) {
                throw new Exception();
            }
        } catch (Exception $exc) {
            # Usuário da sessão não foi encontrado
            $_SESSION['logOff'][] = 2;
            logOff();
        }

        $tempo_limite = 1000; # 100 == 1 minuto
        # Calcula o tempo transcorrido 
        $agora = agora();
        $ultimoAcesso = $_SESSION['usuario']->usuario_ultimo_acesso;
        $tempo_transcorrido = (strtotime($agora) - strtotime($ultimoAcesso));

        # Compara o tempo transcorrido com o tempo limite
        if ($tempo_transcorrido >= $tempo_limite) {
            $_SESSION['logOff'][] = 3;
            logOff();
        } else {
            # Está dentro do tempo_limite: renova o tempo da sessão
            (new Usuario())->set_ultimo_acesso($agora, $_SESSION['usuario']->usuario_id);
            $_SESSION['usuario']->usuario_ultimo_acesso = $agora;
        }
    }
}

function permissao() {
    # Configuração de permissão de acesso à página atual
    require_once '../dados/Config.php';
    Config::get_config($_SESSION['usuario']->usuario_cliente_id, $_SESSION['usuario']->usuario_tipo_id);
    $permissao = Config::get_permissao();
    if ($permissao == 0) {# Não tem nenhum acesso ao cadastro
        $_SESSION['logOff'][] = 4;
        logOff();
    } else {
        return $permissao;
    }
}

# Encerra sessão e direciona para a tela de login

function logOff() {
    session_destroy();
    clearstatcache();
    header('Location: ../home');
}
