<!DOCTYPE html>
<!--
A licença MIT

Copyright 2019 Viniciusalopes Tecnologia <suporte@viniciusalopes.com.br>.

É concedida permissão, gratuitamente, a qualquer pessoa que obtenha uma cópia
deste software e dos arquivos de documentação associados (o "Software"), para
negociar o Software sem restrições, incluindo, sem limitação, os direitos de uso,
cópia, modificação e fusão, publicar, distribuir, sublicenciar e/ou vender cópias
do Software, e permitir que as pessoas a quem o Software é fornecido o façam,
sujeitas às seguintes condições:

O aviso de copyright acima e este aviso de permissão devem ser incluídos em
todas as cópias ou partes substanciais do Software.

O SOFTWARE É FORNECIDO "NO ESTADO EM QUE SE ENCONTRA", SEM NENHUM TIPO DE GARANTIA,
EXPRESSA OU IMPLÍCITA, INCLUINDO, MAS NÃO SE LIMITANDO ÀS GARANTIAS DE COMERCIALIZAÇÃO,
ADEQUAÇÃO A UM FIM ESPECÍFICO E NÃO VIOLAÇÃO. EM NENHUMA CIRCUNSTÂNCIA, OS AUTORES
OU PROPRIETÁRIOS DE DIREITOS DE AUTOR PODERÃO SER RESPONSABILIZADOS POR QUAISQUER
REIVINDICAÇÕES, DANOS OU OUTRAS RESPONSABILIDADES, QUER EM AÇÃO DE CONTRATO,
DELITO OU DE OUTRA FORMA, DECORRENTES DE, OU EM CONEXÃO COM O SOFTWARE OU O USO
OU OUTRAS NEGOCIAÇÕES NO PROGRAMAS.
------------------------------------------------------------------------------------------
Projeto   : AdmVendas
Criado em : 01/09/2019
Autor     : Viniciusalopes (Vovolinux) <suporte@vovolinux.com.br>
Finalidade: Acesso ao sistema
------------------------------------------------------------------------------------------
-->
<html>
    <head>
        <?php
        require './html/head.php';
        head('Login | AdmVendas', '.');
        ?>
    </head>
    <body>
        <div class="row">
            <div class="col-4"></div>
            <div class="col-4">
                <form class="form-login" action="../bin/login.php" method="POST">
                    <div class="form-group">
                        <label for="usuario_login">Endereço de email</label>
                        <input type="email"
                               class="form-control"
                               id="usuario_login"
                               name="usuario_login"
                               aria-describedby="emailHelp"
                               placeholder="Seu email"
                               required="">
                        <small id="emailHelp" class="form-text text-muted">Nunca vamos compartilhar seu email, com ninguém.</small>
                    </div>
                    <div class="form-group">
                        <label for="usuario_senha">Senha</label>
                        <input type="password"
                               class="form-control"
                               id="usuario_senha"
                               name="usuario_senha"
                               placeholder="Senha"
                               required="">
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>

        <?php require './html/scripts-bootstrap.php'; ?>
    </body>
</html>
