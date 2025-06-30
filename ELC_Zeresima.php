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
                       // P�gina para zerar os votos de uma Elei��o ainda n�o iniciada
require_once('../cabecalho.php');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<?php
//error_reporting(E_ALL);

$campo['Pessoa'] = $_SESSION['CodPessoaEleicao'];

$campo['Concurso'] = $_POST['Concurso'];
$campo['Eleicao'] = $_POST['Eleicao'];

$DireitoMaster = VerificaGerenteSistema();

$SQL = " SELECT * FROM eleicoes.COMISSAOELEITORAL
         WHERE CodConcurso = :Concurso[numero]
           AND CodEleicao = :Eleicao[numero]
           AND CodPessoaEleicao = :Pessoa[numero]
           AND Gerente = 'S' ";
$ConsultaComissao = new consulta($db, $SQL);
$ConsultaComissao->setparametros("Concurso,Eleicao,Pessoa", $campo);
if (!$ConsultaComissao->executa(true) && !$DireitoMaster) {
     echo "<br><font class=\"a2\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.</font><br><br>\n";
     echo "<a href=\"javascript: history.back(-1);\">Voltar</a>\n";
     echo "</body></html>";
     exit;
}

$SQL = " SELECT CE.*
         FROM eleicoes.CONCURSOELEITORAL CE
         WHERE CE.CodConcurso = :Concurso[numero]
           AND NOT EXISTS (select * from eleicoes.logoperacao
                           where codconcurso = CE.codconcurso and descricao = 'Zer�sima') ";
$ConsultaConcurso = new consulta($db, $SQL);
$ConsultaConcurso->setparametros("Concurso",$campo);
if (!$ConsultaConcurso->executa(true)) {
   echo "<br><b>Erro!</b> Esta elei&ccedil;&atilde;o ainda est&aacute; ativa, ou j&aacute; acabou.<br />";
   echo "<a href=\"javascript: history.back(-1);\">Voltar</a>\n";
   echo "</body></html>";
   exit;
}

$SQL = " UPDATE eleicoes.ELEICAO
         SET VotosBrancos = 0,
             VotosNulos = 0
         WHERE CodConcurso = :Concurso[numero]
           AND CodEleicao = :Eleicao[numero] ";
$AtualizaEleicao = new consulta($db, $SQL);
$AtualizaEleicao->setparametros("Concurso,Eleicao", $campo);
$AtualizaEleicao->executa();

$SQL = " UPDATE eleicoes.CHAPA
         SET NrVotosRecebidos = 0
         WHERE CodConcurso = :Concurso[numero]
           AND CodEleicao = :Eleicao[numero] ";
$AtualizaChapas = new consulta($db, $SQL);
$AtualizaChapas->setparametros("Concurso,Eleicao", $campo);
$AtualizaChapas->executa();

$SQL = " UPDATE eleicoes.ELEITOR
         SET indefetuouvoto = NULL,
			       datahoravoto = NULL,
						 ip_voto = NULL
         WHERE codconcurso = :Concurso[numero]
           AND codeleicao = :Eleicao[numero] ";
$AtualizaEleitores = new consulta($db, $SQL);
$AtualizaEleitores->setparametros("Concurso,Eleicao", $campo);
$AtualizaEleitores->executa();

$SQL = " DELETE FROM eleicoes.VOTO
         WHERE codconcurso = :Concurso[numero]
           AND codeleicao = :Eleicao[numero] ";
$ExcluiVotos = new consulta($db, $SQL);
$ExcluiVotos->setparametros("Concurso,Eleicao", $campo);
$ExcluiVotos->executa();

$SQL = " DELETE FROM eleicoes.LOGOPERACAO
         WHERE codconcurso = :Concurso[numero]
           AND codeleicao = :Eleicao[numero]
					 AND descricao = 'Voto efetuado com sucesso' ";
$ExcluiLog = new consulta($db, $SQL);
$ExcluiLog->setparametros("Concurso,Eleicao", $campo);
$ExcluiLog->executa();

if (($AtualizaEleicao) && ($AtualizaChapas)) {
  $SQL = " SELECT coalesce(MAX(NrSeqLogOperacao), 0) + 1 as NrSeq
           FROM eleicoes.LOGOPERACAO
           WHERE CodConcurso = :Concurso[numero]
             AND CodEleicao = 1 ";
  $ConsultaLog = new consulta($db, $SQL);
  $ConsultaLog->setparametros("Concurso", $campo);
  $ConsultaLog->executa(true);

  $campo['NrSeq'] = $ConsultaLog->campo("NrSeq");
  $campo['IP'] = $_SERVER['REMOTE_ADDR'];

  $SQL = " INSERT INTO eleicoes.LOGOPERACAO
           (CodPessoaEleicao, CodConcurso, CodEleicao, DataOperacao, NrSeqLogOperacao, IP, Descricao)
           VALUES
           (:Pessoa[numero], :Concurso[numero], :Eleicao[numero], now(), :NrSeq[numero], :IP[texto], 'Zer�sima') ";
  $InsereLog = new consulta($db, $SQL);
  $InsereLog->setparametros("Pessoa,Concurso,Eleicao,NrSeq,IP", $campo);
  $InsereLog->executa();
?>

<html>
<body>
<script>
     alert('Elei��o zerada com sucesso!');
     location.href = 'ELC_Apuracao.php?Concurso=<?=$campo['Concurso']?>&Eleicao=<?=$campo['Eleicao']?>&Zeresima';
</script>
</body>
</html>
<?php
}
else { ?>
<html>
<body>
<script>
     alert('Erro ao zerar a elei��o!');
     location.href = 'ELC_Apuracao.php?Concurso=<?=$campo['Concurso']?>&Eleicao=<?=$campo['Eleicao']?>';
</script>
</body>
</html>
<?php
} ?>