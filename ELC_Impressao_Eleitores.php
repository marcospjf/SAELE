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
                       // Relat�rio de Eleitores para Impress�o
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();
$Direito = $Pessoa->eGerenteSistema();

$Assinatura = ($_GET['Assinatura'] == 'S');

$Concurso = $Controlador->recuperaConcursoEdicao();
$Eleicao = $Controlador->recuperaEleicaoEdicao();

$Eleitores = $Eleicao->devolveEleitores();
?>

<html>
<head>
<title>Relat&oacute;rio de Eleitores</title>
<link rel="stylesheet" type="text/css" href="../CODE/ELEICAO.css">
</head>

<body bgcolor="white">

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
      <font size="2" face="verdana">UNIVERSIDADE FEDERAL DO RIO GRANDE DO SUL</font>
    </td>
  </tr>
  <tr>
    <td>
      <font size="2" face="verdana">SISTEMA DE ELEI&Ccedil;&Otilde;ES - <?=$Eleicao->get("descricao")?></font>
    </td>
  </tr>
  <tr>
    <td>
      <font size="2" face="verdana">RELAT&Oacute;RIO DE ELEITORES</font>
    </td>
  </tr>
</table>

<hr />

<div align="center">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="Eleitores">
  <tr bgcolor="white">
    <td width="320">
      <b>Nome</b>
    </td>
    <?php if($Assinatura) { ?>
    <td width="100" align="right">
      <b>C&oacute;digo&nbsp;</b>
    </td>
    <td width="330" align="center">
      <b>Assinatura</b>
    </td>
    <?php } ?>
  </tr>
<?php
$linha = "#f5f5f5";
foreach($Eleitores as $Eleitor) {
    $Pessoa = $Eleitor->getObj("PessoaEleicao"); ?>
      <tr bgcolor="<?=$linha?>">
        <td>
          <?=$Pessoa->get("nomepessoa")?>
        </td>
        <?php if($Assinatura) { ?>
        <td align="right">
          <?=$Pessoa->getChave()?>&nbsp;
        </td>
        <td class="assinatura">&nbsp;</td>
        <?php } ?>
      </tr>
  <?php
  if ($linha == "white")
      $linha = "#f5f5f5";
  else
      $linha = "white";
}
?>
</table>
</div>

<script>
window.print();
</script>

</body>
</html>