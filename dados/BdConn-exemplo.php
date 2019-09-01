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
 * Finalidade: Conexão e manipulação de dados do banco
 * ------------------------------------------------------------------------------------------
 *
 */

/**
 * Description of Bd
 *
 * @author vovostudio
 */

class BdConn {
    # Atributos
    #BANCO DEBIAN-DEV
    #private static $host = '192.168.15.10';
    #
    #
    #BANCO LOCAWEB
    private static $host = 'host.mysql.com.br';
    private static $banco = 'banco';
    private static $usuario = 'usuario';
    private static $senha = 'senha-da-nasa';
    private static $conexao;

    # Construtor

    function __construct() {
        self::$conexao = self::getConexao();
    }

    # Método que retorna a conexão

    private static function getConexao() {
        try {
            $conexao = new PDO('mysql:host=' . self::$host . ';dbname=' . self::$banco . '', '' . self::$usuario . '', '' . self::$senha . '');
            $conexao->exec("SET CHARACTER SET utf8"); // Sets encoding UTF-8  
            $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexao;
        } catch (PDOException $erro) {
            throw new Exception($erro->getMessage());
        }
    }

    # Método para SELECT

    /**
     * @param STRING $query Query que será executada no banco
     * @param ARRAY $parametros Parametros utilizados na $query
     * @throws Exceções PDO
     */
    static function consulta($query, $parametros) {
        try {
            $comando = self::$conexao->prepare($query);
            $comando->execute($parametros);

            $colecao = NULL;
            while ($linha = $comando->fetch(PDO::FETCH_OBJ)) {
                $colecao[] = $linha;
            }
            return $colecao;
        } catch (PDOException $erro) {
            throw new Exception($erro->getMessage() . "<hr> Query utilizada: " . $query);
        }
    }

    # Funcao / Método para INSERT, DELETE e UPDATE
    /**
     * 
     * @param type $query STRING com a query que será executada no banco
     * @param type $parametros ARRAY com os parametros utilizados na $query
     * @throws Exceções PDO
     */

    static function executa($query, $parametros) {
        try {
            $comando = self::$conexao->prepare($query);
            $comando->execute($parametros);
        } catch (PDOException $erro) {
            throw new Exception($erro->getMessage() . "<hr> Query utilizada: " . $query);
        }
    }

}
