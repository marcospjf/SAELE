<?php
/*
Copyright 2011 da UFRGS - Universidade Federal do Rio Grande do Sul

Este arquivo � parte do programa SAELE - Sistema Aberto de Elei��es Eletr�nicas.

O SAELE � um software livre; voc� pode redistribu�-lo e/ou modific�-lo dentro dos
termos da Licen�a P�blica Geral GNU como publicada pela Funda��o do Software Livre
(FSF); na vers�o 2 da Licen�a.

Este programa � distribu�do na esperan�a que possa ser �til, mas SEM NENHUMA GARANTIA;
sem uma garantia impl�cita de ADEQUA��O a qualquer MERCADO ou APLICA��O EM PARTICULAR.
Veja a Licen�a P�blica Geral GNU/GPL em portugu�s para maiores detalhes.

Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral GNU, sob o t�tulo "LICENCA.txt",
junto com este programa, se n�o, acesse o Portal do Software P�blico Brasileiro no
endere�o www.softwarepublico.gov.br ou escreva para a Funda��o do Software Livre(FSF)
Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA
*/

require_once("../CABECALHO.PHP");
MostraCabecalho("Cadastro de Usu�rio Inicial");

function RealizaCadastro($Form) {
    $Pessoa = new PessoaEleicao();

    if(trim($Form['identificacaousuario']) == "")
        return "Preencha o c�digo do usu�rio.";
    if(trim($Form['nomepessoa']) == "")
        return "Preencha o nome da pessoa.";
    if(trim($Form['nrregistrogeral']) == "")
        return "Preencha o Registro Geral da pessoa.";
    if(trim($Form['cpf']) == "")
        return "Preencha o CPF da pessoa.";
    if(trim($Form['email']) == "")
        return "Preencha o E-Mail da pessoa.";
    try {
        $Pessoa->set("identificacaousuario", $Form['identificacaousuario']);
        $Pessoa->set("nomepessoa", $Form['nomepessoa']);
        $Pessoa->set("localtrabalho", $Form['localtrabalho']);
        $Pessoa->set("nrregistrogeral", $Form['nrregistrogeral']);
        $Pessoa->set("cpf", $Form['cpf']);
        $Pessoa->set("email", $Form['email']);

        require_once("../Funcoes_Pessoa.php");
        $Resposta = HomologaPessoa($Pessoa->getAll());
        if(is_null($Resposta)) {
            $Pessoa->set("pessoaautenticada", "S");
        }
        else
            return "Os dados da pessoa n�o foram homologados. Resposta: ".$Resposta;

        $Pessoa->set("gerentesistema", "S");
        $Pessoa->set("solicitante", "N");

        $Pessoa->salva();
        return NULL;
    }
    catch(EntidadeValorInvalidoException $e) {
        return "Valor inv�lido: ".$e->getMessage();
    }
}

$Consulta = new Consulta("select * from eleicoes.pessoaeleicao");
if($Consulta->executa(true)) {
    echo "O cadastro inicial j� foi realizado. Acesso negado.";
    exit;
}
if(isset($_POST['Cadastra'])) {
    $Retorno = RealizaCadastro($_POST);
    if(is_null($Retorno)) { ?>
        <h1>Cadastro do Usu�rio Inicial</h1>

        <p>
            O seu cadastro foi conclu�do com sucesso. Agora voc� pode acessar
            o sistema com seu identificador de usu�rio e senha. Recomenda-se
            que este arquivo seja exclu�do.
        </p>
        <?php
        exit;
    }
    else { ?>
        <p class="Erro">
            Erro no cadastro:<br />
            <?=$Retorno?>
        </p>
        <?php
        $IdentificacaoUsuario = $_POST['identificacaousuario'];
        $NomePessoa = $_POST['nomepessoa'];
        $LocalTrabalho = $_POST['localtrabalho'];
        $NrRegistroGeral = $_POST['nrregistrogeral'];
        $CPF = $_POST['cpf'];
        $EMail = $_POST['email'];
    }
}
else {
    $IdentificacaoUsuario = $NomePessoa = $LocalTrabalho =
    $NrRegistroGeral = $CPF = $EMail = null;
}

?>
<h1>Cadastro do Usu�rio Inicial</h1>

<form id="EdicaoPessoa" name="EdicaoPessoa" method="POST">
<input type="hidden" name="Cadastra" value="S" />
<table align="center" width="50%" cellspacing="0" cellpadding="2">
    <tr class="Linha1">
        <td>C�digo do usu�rio:</td>
        <td><input type="text" size="20" name="identificacaousuario" value="<?=$IdentificacaoUsuario?>" /></td>
    </tr>
    <tr class="Linha2">
        <td>Nome:</td>
        <td><input type="text" size="40" name="nomepessoa" value="<?=$NomePessoa?>" /></td>
    </tr>
    <tr class="Linha1">
        <td>Local de Trabalho:</td>
        <td><input type="text" size="30" name="localtrabalho" value="<?=$LocalTrabalho?>" /></td>
    </tr>
    <tr class="Linha2">
        <td>Registro geral:</td>
        <td><input type="text" size="30" name="nrregistrogeral" value="<?=$NrRegistroGeral?>" /></td>
    </tr>
    <tr class="Linha1">
        <td>CPF:</td>
        <td><input type="text" size="30" name="cpf" value="<?=$CPF?>" /></td>
    </tr>
    <tr class="Linha2">
        <td>E-Mail:</td>
        <td><input type="text" size="30" name="email" value="<?=$EMail?>" /></td>
    </tr>
</table>

<div class="botoes">
    <input type="submit" value="Cadastrar" />
</div>
</form>

</body>
</html>