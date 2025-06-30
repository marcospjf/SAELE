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
                               // Consulta de Eleitores para saber se j� votaram
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();

$Pessoa = $Controlador->recuperaPessoaLogada();

if(isset($_GET['CodConcurso']) && isset($_GET['CodEleicao'])) {
    $Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
    $Eleicao = $Concurso->devolveEleicao($_GET['CodEleicao']);
}
elseif(isset($_POST['CodConcurso']) && isset($_POST['CodEleicao'])) {
    $Concurso = new ConcursoEleitoral($_POST['CodConcurso']);
    $Eleicao = $Concurso->devolveEleicao($_POST['CodEleicao']);
}
else {
   echo "<br>Erro! Par�metros inv�lidos.<br><br>\n";
   echo "<a href=\"javascript: window.close();\">Fechar</a>\n";
   exit;
}
if(($Concurso->estadoConcurso() != CONCURSO_ENCERRADO) || ($Concurso->get("situacaoconcurso") != SITUACAOCONCURSO_APURADO)) {
   echo "<br>Erro! Este concurso n�o foi apurado.<br><br>\n";
   echo "<a href=\"javascript: window.close();\">Fechar</a>\n";
   exit;
}
if(!$Pessoa->eGerenteSistema() && ($Eleicao->verificaComissao($Pessoa) != COMISSAO_GERENTE)) {
   echo "<br><font class=\"a2\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.</font><br><br>\n";
   echo "<a href=\"javascript: window.close();\">Fechar</a>\n";
   exit;
}

if(isset($_POST['IN_Selecao']) && (trim($_POST['IN_Selecao']) != "")) { // Quando a pessoa j� foi selecionada ?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
    <title>Pesquisa de Eleitor</title>
    <link rel="stylesheet" type="text/css" href="code/eleicao.css">
</head>

<body bgcolor="#4682b4" aLink="#ffffff" link="#ffffff" vLink="#ffffff">
<div align="center">
<table border="0" cellpadding="0" cellspacing="0" width="95%">
    <tr bgcolor="white">
        <td colspan="6">
            <div align="center">
            <font face="verdana" size="3">
                <b>Pesquisa de Eleitor</b>
                <hr>
            </font>
            </div>
        </td>
    </tr>
    </table>
<table width="95%" bgcolor="white" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center">

<?php
    $PessoaSelecionada = new PessoaEleicao($_POST['IN_Selecao']);
    $Eleitor = $Eleicao->devolveEleitor($PessoaSelecionada);

    if(!is_null($Eleitor->get("datahoravoto"))) {
        if(!is_null($Eleitor->get("codurnavoto"))) {
            $Urna = new UrnaVirtual($Concurso, $Eleicao, $Eleitor->get("codurnavoto")); ?>
            O eleitor <?=$PessoaSelecionada->get("nomepessoa")?> votou nesta elei��o na urna "<?=$Urna->get("descricao")?>" (<?=$Urna->getChave()?>).
        <?php
        }
        else { ?>
                O eleitor <?=$PessoaSelecionada->get("nomepessoa")?> votou nesta elei��o.
        <?php
        }
    }
    else { ?>
                O eleitor <?=$PessoaSelecionada->get("nomepessoa")?> <b>n�o</b> votou nesta elei��o.
    <?php
    } ?>
    </td>
  </tr>
</table>
<br />
<input type="button" value="Voltar" onClick="javascript: location.href = 'ELC_Consulta_Eleitor.php?CodConcurso=<?=$Concurso->GetChave()?>&CodEleicao=<?=$Eleicao->GetChave()?>';" /> &nbsp;
<input type="button" value="Fechar" onClick="javascript: window.close();" />

</div>
</body>
</html>

<?php
}
elseif(isset($_POST['IN_Pesq']) && (trim($_POST['IN_Pesq']) != "")) { // Quando a pesquisa j� foi enviada
    if($_POST['IN_Criterio'] == 1) { // Pesquisa por nome
        if($_POST['IN_Pesquisa'] == 1) // Termo inicial
            $Criterio = $_POST['IN_Pesq']."%";
        else                           // Qualquer termo
            $Criterio = "%".$_POST['IN_Pesq']."%";
        $PessoasLocalizadas = new Iterador("PessoaEleicao",
            "where exists
                (select * from eleicoes.eleitor
                 where codconcurso = :CodConcurso[numero]
                   and codeleicao = :CodEleicao[numero]
                   and codpessoaeleicao = TAB.codpessoaeleicao)
              and upper(TAB.nomepessoa) like upper(:Criterio[texto]) ",
            array("CodConcurso" => $Concurso->getChave(), "CodEleicao" => $Eleicao->getChave(), "Criterio" => $Criterio));
    }
    else {                           // Pesquisa por CodPessoa
        $PessoasLocalizadas = new Iterador("PessoaEleicao",
            "where exists
                (select * from eleicoes.eleitor
                 where codconcurso = :CodConcurso[numero]
                   and codeleicao = :CodEleicao[numero]
                   and codpessoaeleicao = TAB.codpessoaeleicao)
               and TAB.codpessoaeleicao = :CodPessoaEleicao[numero] ",
            array("CodConcurso" => $Concurso->getChave(), "CodEleicao" => $Eleicao->getChave(), "CodPessoaEleicao" => $_POST['IN_Pesq']));
    } ?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
    <title>Pesquisa de Eleitor</title>
    <link rel="stylesheet" type="text/css" href="code/eleicao.css">
</head>

<body bgcolor="#4682b4" aLink="#ffffff" link="#ffffff" vLink="#ffffff">
<form name="Form" action="" method="post">
<input type="hidden" name="CodConcurso" value="<?=$Concurso->getChave()?>">
<input type="hidden" name="CodEleicao" value="<?=$Eleicao->getChave()?>">
<div align="center">
<table border="0" cellpadding="0" cellspacing="0" width="95%">
    <tr bgcolor="white">
        <td colspan="6">
            <div align="center">
            <font face="verdana" size="3">
                <b>Pesquisa de Eleitor</b>
                <hr>
            </font>
            </div>
        </td>
    </tr>
    </table>
<table width="95%" bgcolor="white" cellspacing="0" cellpadding="0">
  <tr bgcolor="#f5f5f5">
    <td align="center">
      <?php
      if($PessoasLocalizadas->temRegistro()) { ?>
			
      <font size="2" face="verdana">
      Selecione:<br />
      <select name="IN_Selecao" size="1">
        <?php
        foreach($PessoasLocalizadas as $CodPessoaEleicao => $Pessoa) {
          echo '<option value="'.$CodPessoaEleicao.'">'.$Pessoa->get("nomepessoa").'</option>';
        }
        ?>
      </select>
      </font>
    </td>
  </tr>
  <tr>
    <td align="center">
      <input type="submit" value="Enviar" />
    <?php
    }
    else {
        echo 'N�o foi encontrado nenhum eleitor nesta elei��o com o crit�rio selecionado.';
    }
    ?>
    </td>
  </tr>
</table>
<br />
<input type="button" value="Voltar" onClick="javascript: location.href = 'ELC_Consulta_Eleitor.php?CodConcurso=<?=$Concurso->GetChave()?>&CodEleicao=<?=$Eleicao->GetChave()?>';" /> &nbsp;
<input type="button" value="Fechar" onClick="javascript: window.close();" />

</div>
</form>
</body>
</html>
<?php
}
else { // Antes de enviar a pesquisa ?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
    <title>Pesquisa de Eleitor</title>
    <link rel="stylesheet" type="text/css" href="code/eleicao.css">
</head>

<script>
  function Mudou(obj) {
    if(obj.value == 1) {
      document.getElementById('Pesquisa1').disabled = false;
      document.getElementById('Pesquisa2').disabled = false;
    }
    else {
      document.getElementById('Pesquisa1').disabled = true;
      document.getElementById('Pesquisa2').disabled = true;
    }
  }

  function Valida(Form) {
    if(Form.IN_Pesq.value.length < 3) {
      alert('O crit�rio de pesquisa deve possuir, no m�nimo, 3 caracteres.');
      return false;
    }

    if(document.getElementById('Criterio2').checked) {
      if(isNaN(Form.IN_Pesq.value) || Form.IN_Pesq.value.length > 6) {
        alert('C�digo inv�lido.');
        return false;
      }
    }
    return true;
  }
</script>

<body bgcolor="#4682b4" aLink="#ffffff" link="#ffffff" vLink="#ffffff">
<form name="Form" action="" method="post">
<input type="hidden" name="CodConcurso" value="<?=$Concurso->getChave()?>" />
<input type="hidden" name="CodEleicao" value="<?=$Eleicao->getChave()?>" />
<div align="center">
    <table border="0" cellpadding="0" cellspacing="0" width="95%">
        <tr bgcolor="white">
            <td colspan="6">
                <div align="center">
                <font face="verdana" size="3">
                    <b>Pesquisa de Eleitor</b>
                    <hr>
                </font>
                </div>
            </td>
        </tr>
        </table>
      <table border="0" cellpadding="0" cellspacing="0" width="95%" bgcolor="#f5f5f5">
      <tr>
         <td align="center">
            <font face="verdana" size="2">
            Pesquisa:
            </font>
         </td>
         <td>
            <input type="text" class="normal" name="IN_Pesq" id="Pesq" size="55" maxlength="72" value="<?=(isset($_POST['IN_Pesq']) ? $_POST['IN_Pesq'] : null)?>">
         </td>
         <td>
            <input type="submit" class="normal" name="submitButtonName" value="Localizar" onClick="javascript: return(Valida(Form));">
         </td>
      </tr>
      <tr bgcolor="white">
         <td width="23%" align="center">
            <font face="verdana" size="2">
            Tipo de Pesquisa:
            </font>
         </td>
         <td align="center">
            <font face="verdana" size="2">
            <input type="radio" value="1" name="IN_Pesquisa" id="Pesquisa1" checked="checked">
            Termo Inicial
            <input type="radio" value="2" name="IN_Pesquisa" id="Pesquisa2">
            Qualquer Termo</font>
         </td>
         <td>
         </td>
      </tr>
      <tr bgcolor="#f5f5f5">
         <td width="23%" align="center">
            <font face="verdana" size="2">
            Pesquisar por:
            </font>
         </td>
         <td align="center">
            <font face="verdana" size="2">
            <input type="radio" value="1" name="IN_Criterio" id="Criterio1" onClick="javascript: Mudou(this);" checked="checked">
            Nome
            <input type="radio" value="2" name="IN_Criterio" id="Criterio2" onClick="javascript: Mudou(this);">
            C�digo</font>
         </td>
         <td>
         </td>
      </tr>
            
      <tr>
         <td bgcolor="white" colspan="3">
            <hr>
         </td>
      </tr>
   </table>
</div>
</form>

</body>
</html>
<?php
} ?>