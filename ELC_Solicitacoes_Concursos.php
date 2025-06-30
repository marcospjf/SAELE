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
                       // Lista de Solicita��es Pendentes
require_once('../CABECALHO.PHP');

error_reporting(E_PARSE | E_ERROR);

$Pessoa = Controlador::instancia()->recuperaPessoaLogada();
if(!$Pessoa->eGerenteSistema()) {
    echo "<html><body>\n";
    echo "<div align=\"center\">\n";
    echo "<br><font size=\"2\" face=\"verdana\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.<br><br>\n";
    echo "<a href=\"javascript: history.back();\">Voltar</a></font>\n";
    echo "</div>";
    echo "</body></html>";
    exit;
}
$Solicitacoes = new Iterador("SolicitacaoConcurso", "where dataatendimento is null order by datasolicitacao desc");

MostraCabecalho("Solicita��o de Concursos Eleitorais");
?>

<br />

<table width="85%" border="1" cellspacing="0" cellpadding="0" align="center">
  <tr bgcolor="white">
    <td align="center">
      <font size="2" face="verdana"><b>Lista de Solicita��es Pendentes</b></font>
    </td>
  </tr>
  <?php if($Solicitacoes->temRegistro()) { ?>
    <tr bgcolor="white">
      <td>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <?php foreach($Solicitacoes as $NrSeqSolicitacaoConcurso => $Solicitacao) { ?>
          <tr>
            <td width="10%">
              <font size="2" face="verdana"><?=$NrSeqSolicitacaoConcurso?>: </font>
            </td>
            <td width="90%">
              <a href="ELC_Geracao_Concurso.php?NrSeqSolicitacaoConcurso=<?=$NrSeqSolicitacaoConcurso?>">
                <font size="2" face="verdana">
                <?=$Solicitacao->get("nomeconcurso")?>
                </font>
              </a>
            </td>
          </tr>
        <?php } ?>
        </table>
      </td>
    </tr>
  <?php } else { ?>
    <tr bgcolor="white">
      <td align="center">
        <br />
        <font size="2" face="verdana">N�o h� solicita��es pendentes.</font>
        <br /><br />
      </td>
    </tr>
  <?php } ?>
</table>

<br />
<div align="center">
  <input type="button" value="Voltar" onClick="javascript: location.href='ELC_Cadastro_Concursos.php';" />
</div>
</body>
</html>