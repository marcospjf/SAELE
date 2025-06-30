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
                       // P�gina para cadastro de solicitantes
require_once('../CABECALHO.PHP');
error_reporting(E_PARSE | E_ERROR | E_WARNING | E_NOTICE);
include("Adm_Common.php");

$db = db::instancia();

$Pessoa = Controlador::instancia()->recuperaPessoaLogada();
if(!$Pessoa->eGerenteSistema()) {
  echo "<html><body>\n";
  echo "<div align=\"center\">\n";
  echo "<br><font size=\"2\" face=\"verdana\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.<br><br>\n";
  echo "<a href=\"ELC_Cadastro_Concursos.php\">Voltar</a></font>\n";
  echo "</div>";
  echo "<body></html>";
  exit;
}

MostraCabecalho("Cadastro de Solicitantes");

$xajax->printJavascript('../xajax');
?>
<script>
function ExibeLayer() {
    Layer = document.getElementById('LayerEdicao');

    larguraTela = window.innerWidth;
    if(isNaN(larguraTela)) larguraTela = document.body.clientWidth;
    if(isNaN(larguraTela)) larguraTela = document.documentElement.clientWidth;

    Layer.style.top = document.body.scrollTop + 120;
    Layer.style.left = Math.round((larguraTela - 600) / 2);
    Layer.style.display = 'block';
}

function FechaLayer() {
    Layer = document.getElementById('LayerEdicao');
    Layer.innerHTML = '';
    Layer.style.display = 'none';
}
</script>

<div style="text-align: center;">
    <input type="button" value="Cadastrar nova pessoa" onclick="javascript: xajax_CarregaEdicaoPessoa();" />
</div>

<form name="Form" id="Form" action="" method="POST" onsubmit="javascript: return false;">
<p style="text-align: center; font-family: verdana; font-size: 10pt; background-color: white;">
  Pesquisar pessoa:
	<input type="text" name="NomePesq" id="NomePesq" size="50" value="" /> &nbsp;
	<input type="submit" value="Pesquisar" onclick="javascript: xajax_PesquisaPessoas(xajax.getFormValues('Form'));" />
	 <br />
	Tipo de pesquisa:
	  <input type="radio" name="TipoPesq" value="1" checked="checked" /> Termo inicial
	  <input type="radio" name="TipoPesq" value="2" /> Qualquer termo
</p>
</form>

<div style="text-align: center;">
    <input type="button" value="Voltar" onclick="javascript: location.href='ELC_Cadastro_Concursos.php';" />
</div>

<p id="ListaSolicitantes" style="font-family: Verdana;">
</p>

<div id="LayerEdicao" class="Layer1" style="width: 650px; height: 250px; display: none;"></div>

</body>
</html>