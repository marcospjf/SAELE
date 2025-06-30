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

require_once('../CABECALHO.PHP');

$Campos = $_SESSION['Campos'];

MostraCabecalho("Solicita��o de Concurso Eleitoral");

if(!isset($_SESSION['Valido'])) { ?>
    <div class="Erro">
        <p><strong>Solicita��o inv�lida</strong>.</p>

        <p><input type="button" value="Voltar" onclick="javascript: location.href = 'SOL_Solicitacao.php';" /></p>
    </div>
    <?php
    exit;
}
?>

<br />

<table width="95%" border="0" cellspacing="0" cellpadding="0">
  <tr bgcolor="white">
    <td colspan="3">
      <font size="2" face="verdana"><b>&nbsp; Nome do Concurso:</b> <?=$Campos['NomeConcurso']?></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="3">
      <font size="2" face="verdana"><b>&nbsp; Per&iacute;odo:</b> De <?=$Campos['DataInicio']?> &agrave;s <?=$Campos['HoraInicio']?> at&eacute; <?=$Campos['DataFim']?> &agrave;s <?=$Campos['HoraFim']?></font>
    </td>
  </tr> 
  <tr bgcolor="white">
    <td>
      <font size="2" face="verdana"><b>&nbsp; Contato:</b> <?=$Campos['Contato']?></font>
    </td>
    <td>
      <font size="2" face="verdana"><b>Ramal:</b> <?=$Campos['RamalContato']?></font>
    </td>
    <td>
      <font size="2" face="verdana"><b>E-Mail:</b> <?=$Campos['EMail']?></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="3">
      <font size="2" face="verdana"><b>&nbsp; Elei&ccedil;&otilde;es do Concurso:</b><br /> &nbsp;&nbsp;&nbsp;
			<?=implode("<br /> &nbsp;&nbsp;&nbsp; ", $Campos['Eleicao'])?></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="3">
      <font size="2" face="verdana"><b>&nbsp; Tipo de Elei&ccedil;&atilde;o:</b>
        <?php switch($Campos['TipoEleicao']) {
          case "S": echo "Urna"; break;
          case "E": echo "Escopo"; break;
          case "N": echo "Livre"; break; }
        ?></font>
    </td>
  </tr>
  <tr bgcolor="#f5f5f5">
    <td colspan="3">
      <font size="2" face="verdana"><b>&nbsp; Observa&ccedil;&otilde;es adicionais:</b> <?=$Campos['Observacao']?></font>
    </td>
  </tr> 
</table>
<br />
<form action="SOL_Realiza_Solicitacao.php" method="POST" name="Form">
<div align="center">
  <font size="3" face="verdana"><b>Confirma os dados acima?</b></font><br /><br />

  <input type="button" value="Voltar" onclick="javascript: document.Form.action = 'SOL_Solicitacao.php'; document.Form.submit();" /> &nbsp;
  <input type="submit" value="Confirmar" />
</div>
</form>

</body>
</html>