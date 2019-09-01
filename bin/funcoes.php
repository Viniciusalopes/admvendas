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
 * Finalidade: Fornecer funcoes comuns
 * ------------------------------------------------------------------------------------------
 */

# TRATAMENTO DE STRINGS

function numero($numero, $tamanho) {
    return str_pad($numero, $tamanho, '0', STR_PAD_LEFT);
}

function moeda($valor, $decimais) {
    if (strlen(trim($valor)) == 0) {
        $valor = '0,00';
    }
    return number_format($valor, $decimais, ',', '.');
}

function texto_invalido($texto, $menorIgual) {
    if (strlen(trim($texto)) <= intval($menorIgual)) {
        return TRUE;
    }
    return FALSE;
}

# VARIÁVEIS DE SESSÃO

function set_modulo() {

    # Identifica a página atual
    $nodes = explode('/', $_SERVER['SCRIPT_NAME']);

    # Classe principal do módulo da página
    $Classe = ucfirst($nodes[1]);

    # Tira o 's' do nome do módulo (Exemplo: /usuarios/ -> Usuario.php
    if (!file_exists('../dados/' . $Classe . 'php')) {
        $Classe = substr($Classe, 0, -1);
    }

    $_SESSION['modulo'] = (object) [
                'nome' => $nodes[1],
                'pagina' => $nodes[2],
                'classe' => $Classe
    ];

    # Seta o dic da página atual
    require_once '../dic/dic.php';

    # Classe para alerts na tela
    require_once '../html/Alert.php';

    # Páginas que não importam módulos
    $nao_importar = ['login', 'adm', 'home', 'contato'];

    if (!in_array($_SESSION['modulo']->nome, $nao_importar)) {
        require_once '../dados/' . $Classe . '.php';
    }
    $_SESSION['server'] = $_SERVER;
}

function clear_tmp() {
    # Limpa dados temporários da sessão
    if (isset($_SESSION['tmp'])) {
        unset($_SESSION['tmp']);
    }
}

function set_Obj() {
    /**
     * Era assim: (dentro de funcoes.php da pasta da página)
     * 
     *  require_once '../dados/Usuario.php';
     *  require_once '../html/Alert.php';
     *  function set_objUsuario() {
     *
     *      #Obtém dados do usuário para incluir/editar
     *      $usuario = ($_SESSION['modulo']->pagina == 'incluir.php') ? (new Usuario())->get_padrao() : (
     *                  (isset($_GET['id'])) ? (new Usuario())->get_usuario($_GET['id']) : $_SESSION['tmp']['objUsuario']);
     *      $_SESSION['tmp']['objUsuario'] = $usuario;
     *  }
     * 
     */
    #

    $Classe = $_SESSION['modulo']->classe;

    require_once '../dados/' . $Classe . '.php';

    # Obtém dados da classe para incluir/editar
    $obj = ($_SESSION['modulo']->pagina == 'incluir.php') ? (new $Classe())->get_padrao() : (
            (isset($_GET['id'])) ? (new $Classe())->get($_GET['id']) : $_SESSION['tmp']['obj' . $Classe]);
    $_SESSION['tmp']['obj' . $Classe] = $obj;
}

function set_listas() {
    /*
     * Era assim: (dentro de funcoes.php da pasta da página)
     *  
     *  function set_listas() {
     *      # Listas
     *      require_once '../dados/Sexo.php';
     *      $_SESSION['sexos'] = (new Sexo())->get_sexos();
     *
     *      require_once '../dados/Status.php';
     *      $_SESSION['status'] = (new Status())->get_status('usuario');
     *
     *      require_once '../dados/Tipo.php';
     *      $_SESSION['tipos'] = (new Tipo())->get_tipos('plano'); 
     * }
     * 
     */
    #
    foreach ($_SESSION['tmp']['lst']['listas'] as $l) {
        $Classe = $l->classe;
        $lista = 'list_' . $Classe;
        require_once '../dados/' . $Classe . '.php';
        $_SESSION['tmp']['lst'][$lista] = (new $Classe())->get($l->id, $l->objeto);
    }
}

function select_plano($plano_id) {
    # Identifica o plano pelo id selecionado
    foreach ($_SESSION['tmp']['lst']['list_Plano'] as $key => $value) {
        if ($plano_id == $_SESSION['tmp']['lst']['list_Plano'][$key]->plano_id) {
            $plano = $_SESSION['tmp']['lst']['list_Plano'][$key];
            break;
        }
    }

    # Atualiza dados do plano no objContrato

    $animal = (intval($plano->plano_tipo_id) == 6) ? TRUE : FALSE;
    $_SESSION['tmp']['objContrato']->objBeneficiario->beneficiario_animal = $animal;

    $proprio = ($animal) ? FALSE : $_SESSION['tmp']['objContrato']->objBeneficiario->beneficiario_proprio;
    $_SESSION['tmp']['objContrato']->objBeneficiario->beneficiario_proprio = $proprio;

    $_SESSION['tmp']['objContrato']->contrato_plano_id = $id;
    $_SESSION['tmp']['objContrato']->contrato_plano_tipo_id = $plano->plano_tipo_id;
    $_SESSION['tmp']['objContrato']->contrato_plano_tipo_nome = $plano->tipo_nome;
    $_SESSION['tmp']['objContrato']->contrato_plano_nome = $plano->plano_nome;
    $_SESSION['tmp']['objContrato']->contrato_plano_descricao = $plano->plano_descricao;
    $_SESSION['tmp']['objContrato']->contrato_plano_parcelas = $plano->plano_parcelas;
    $_SESSION['tmp']['objContrato']->contrato_plano_adesao = $plano->plano_adesao;
    $_SESSION['tmp']['objContrato']->contrato_plano_mensalidade = $plano->plano_mensalidade;
    $_SESSION['tmp']['objContrato']->contrato_plano_carencia = $plano->plano_carencia;
}

# TRARAMENTO DE DADOS

/**
 * 
 * @return type date
 * @abstract Retorna a hora atual.
 */
function agora() {
    date_default_timezone_set('America/Sao_Paulo');
    return date('Y-m-d H:i:s');
}

function data_valida($pData, $min = '', $max = '') {

    # Assume a pData caso não tenha passado min e max, e coloca primeiro e último segundo da data
    $minimo = primeiro_horario(($min == '') ? $pData : $min);
    #$minimo = date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime(($min == '') ? $pData : $min)) . ' 00:00:00'));
    $maximo = ultimo_horario(($max == '') ? $pData : $max);
    #$maximo = date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime(($max == '') ? $pData : $max)) . ' 23:59:59'));
    # Em branco
    if (texto_invalido($pData, 5)) {
        $_SESSION['tmp']['data_erro'] = 'texto_invalido (' . $pData . ')';
        return FALSE;
    }

    # Testa se está em um intervalo válido
    if (strtotime($pData) < strtotime($minimo) || strtotime($pData) > strtotime($maximo)) {
        $_SESSION['tmp']['data_erro'] = 'intervalo (' . $pData . ')';
        return FALSE;
    }

    # Separa data e hora
    $diaHora = explode(' ', $pData);

    # Separa dia¸ mes e ano
    $data = explode('-', $diaHora[0]);
    $dia = intval($data[2]);
    $mes = intval($data[1]);
    $ano = intval($data[0]);

    # Teste com php
    if (!checkdate($mes, $dia, $ano)) {
        $_SESSION['tmp']['data_erro'] = 'checkdate (' . $pData . ')';
        return FALSE;
    }

    return TRUE;
}

function menor_primeiro($data_inicial, $data_final) {
    # Corrige a ordem das datas
    if (date('Y-m-d', strtotime($data_inicial)) > date('Y-m-d', strtotime($data_final))) {
        $tmp = $data_inicial;
        $data_inicial = $data_final;
        $data_final = $tmp;
    }
    return [$data_inicial, $data_final];
}

function primeiro_horario($data) {
    return date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($data)) . ' 00:00:00'));
}

function ultimo_horario($data) {
    return date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($data)) . ' 23:59:59'));
}

function primeiro_dia($data) {
    return date('Y-m-d', strtotime(date('Y-m-', strtotime($data)) . '01'));
}

function ultimo_dia($data) {
    return date('Y-m-t', strtotime($data));
}

function esta_no_intervalo($data, $data_inicial, $data_final) {
    $datas = menor_primeiro($data_inicial, $data_final);
    $inicial = date('Y-m-d', strtotime($datas[0]));
    $final = date('Y-m-d', strtotime($datas[1]));
    $data = date('Y-m-d', strtotime($data));
    /**
     * teste de fidelidade XD
      echo '<hr>Data: ' . $data;
      echo '<br>Inicial: ' . $inicial;
      echo '<br>Final: ' . $final;


      if (strtotime($data) >= strtotime($inicial)) {
      echo '<br>Data é maior ou igual que a inicial.';
      }
      if (strtotime($data) <= strtotime($final)) {
      echo '<br>Data é menor ou igual que a final.';
      } else {
      echo '<br>Data NÃO é menor ou igual que a final.';
      }
      if (strtotime($data) >= strtotime($inicial) && strtotime($data) <= strtotime($final)) {
      echo '<br>Data está no intervalo.';
      } else {
      echo '<br>Data NÃO está no intervalo.';
      }
      echo (strtotime($data) >= strtotime($inicial) && strtotime($data) <= strtotime($final)) ? '<br>True' : '<br>False';
      echo (strtotime($data) >= strtotime($inicial) && strtotime($data) <= strtotime($final)) ? TRUE : FALSE;

     */
    return (strtotime($data) >= strtotime($inicial) && strtotime($data) <= strtotime($final)) ? TRUE : FALSE;
}

function proxima_data_valida($data) {
    $nova = date('Y-m-d', strtotime($data));
    while (!data_valida($nova)) {
        echo $nova . '<br>';
        $nova = date('Y-m-d', strtotime('+1 days', strtotime($nova)));
    }
    return $nova;
}
