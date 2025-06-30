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
require_once('../Funcoes_Pessoa.php');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

if(!$Pessoa->eGerenteSistema()) {
  echo "<html><body>\n";
  echo "<div align=\"center\">\n";
  echo "<br><font size=\"2\" face=\"verdana\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.<br><br>\n";
  echo "<a href=\"javascript: history.back();\">Voltar</a></font>\n";
  echo "</div>";
  echo "</body></html>";
  exit;
}

$MSG = null;
$Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
$Eleicoes = $Concurso->devolveEleicoes();

foreach($Eleicoes as $CodEleicao => $Eleicao) {
    $MSG .= "<b>Descri&ccedil;&atilde;o: ".$Eleicao->get("descricao")."</b><br /><br />";
    $erro = false;

    $Pessoas = $Eleicao->devolvePessoasNaoHomologadas();
    foreach($Pessoas as $Pessoa) {
        $Retorno = HomologaPessoa($Pessoa->getAll());
        if(is_null($Retorno)) {
            $Pessoa->set("pessoaautenticada", "S");
            $Pessoa->salva();
        }
        else {
            $erro = true;
            $MSG .= "A pessoa ".$Pessoa->get("nomepessoa")>" n�o foi homologada. Resposta: ".$Retorno."<br />";
        }
    }
    if(!$erro) $MSG .= "Usu&aacute;rios validados sem erros.<br />";
    $MSG .= "<br />";
}
if(isset($_GET['Final'])) {
    $Concurso->set("situacaoconcurso", SITUACAOCONCURSO_HOMOLOGADO);
    $Concurso->salva();
}

MostraCabecalho("Valida��o de Concurso Eleitoral");
?>
<div align="center">
<br />
<font size="2" face="verdana"><?=$MSG?></font>
<br /><br />
<input type="button" value="Voltar" onClick="javascript: location.href='ELC_Cadastro_Concursos.php';" />
</div>
</body></html>