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
                           // Finaliza��o de Concurso Eleitoral
require_once('../CABECALHO.PHP');

$Cod = $_SESSION['CodPessoaEleicao'];       
$Direito = VerificaGerenteSistema();

if(!$Direito) {
  echo "<html><body>\n";
  echo "<div align=\"center\">\n";
  echo "<br><font size=\"2\" face=\"verdana\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.<br><br>\n";
  echo "<a href=\"javascript: window.close();\">Fechar</a></font>\n";
  echo "</div>";
  echo "<body></html>";
  exit;
}

if($_POST['Confirma'] == "S") {
  $campo = $_POST;
  $campo['CodPessoa'] = $Cod;
  $campo['IP'] = $_SERVER['REMOTE_ADDR'];
  $SQL = " UPDATE eleicoes.CONCURSOELEITORAL SET situacaoconcurso = 'F' WHERE CodConcurso = :CodConcurso[numero] ";
  $Atualiza = new consulta($db, $SQL);
  $Atualiza->setparametros("CodConcurso", $_POST);
  $Atualiza->executa();

  $SQL = " INSERT INTO eleicoes.LOGOPERACAO
           (CodPessoaEleicao, CodConcurso, CodEleicao, DataOperacao, NrSeqLogOperacao, IP, Descricao)
           SELECT :CodPessoa[numero], :CodConcurso[numero], 1, now(),
                  coalesce(MAX(NrSeqLogOperacao), 0) + 1, :IP[texto], 'Concurso Eleitoral Finalizado'
           FROM eleicoes.LOGOPERACAO WHERE CodPessoaEleicao = :CodPessoa[numero]
            AND CodConcurso = :CodConcurso[numero] AND CodEleicao = :CodEleicao[numero] ";
  $Insere = new consulta($db, $SQL);
  $Insere->setparametros("CodPessoa,CodConcurso,IP", $campo);
  $Insere->executa();
  ?>
  <html><body><script>window.close();</script></body></html>
  <?php
  exit;
} ?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=iso-8859-1">
    <title>Finaliza&ccedil;&atilde;o de Concurso Eleitoral</title>
    <link rel="stylesheet" type="text/css" href="code/eleicao.css"> 
</head>

<body bgcolor="#4682b4" aLink="#ffffff" link="#ffffff" vLink="#ffffff">
<form method="POST" action="" name="Form">
<input type="hidden" name="CodConcurso" value="<?=$_GET['CodConcurso']?>" />
<input type="hidden" name="Confirma" value="S" />
<table width="100%" border="0" cellspacin="0" cellpadding="0">
  <tr bgcolor="white">
    <td align="center">
      <font size="2" face="verdana">Tem certeza de que deseja finalizar este concurso eleitoral?</font>
      <br /><br />
      <input type="button" value="N&atilde;o" onClick="javascript: window.close();" />
      <input type="submit" value="Sim">
    </td>
  </tr>
</table>
</form>
</body>

</html>