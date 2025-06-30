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
                       // P�gina com a lista dos participantes de determinada Elei��o
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();
$Direito = $Pessoa->eGerenteSistema();

MostraCabecalho("Lista de Participantes");

require_once("Relatorio_xajax.php");
$xajax->printJavascript('../xajax/');

if(isset($_GET['CodConcurso']) && isset($_GET['CodEleicao'])) {
    $Campos = $_GET;
    $Concurso = new ConcursoEleitoral($Campos['CodConcurso']);
    $Eleicao = $Concurso->devolveEleicao($Campos['CodEleicao']);

    try {
        $Controlador->registraEleicaoEdicao($Concurso, $Eleicao);
    }
    catch(ControladorException $e) {
        echo '
        <div class="Erro">
            <p><strong>Erro!</strong> O usu�rio n�o tem permiss�o para acessar esta p�gina.</p>
            <p><a href="ELC_Cadastro_Concursos.php">Voltar</a></p>
        </div>
        </body></html> ';
        exit;
    }
}
else {
    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();
}
$EstadoConcurso = $Concurso->estadoConcurso();
echo '
<table width="100%" cellspacing="0" cellpadding="0" align="center">
    <tr class="LinhaHR">
        <td colspan="4">
            <hr />
        </td>
    </tr>
    <tr class="linha1">
        <td width="20%" align="right">
            <b>'.$Concurso->retornaString(STR_CONCURSOELEITORAL).':&nbsp;</b>
        </td>
        <td width="80%" align="left">
            '.$Concurso->get("descricao").'
        </td>
    </tr>
    <tr class="linha2">
        <td width="20%" align="right">
            <b>'.$Concurso->retornaString(STR_ELEICAO).':&nbsp;</b>
        </td>
        <td width="80%" align="left">
            '.$Eleicao->get("descricao").'
        </td>
    </tr>

    <tr class="LinhaHR">
        <td colspan="4">
            <hr />
        </td>
    </tr>
</table>
<br />

<div id="TabelaGerentes">
</div>
<div id="TabelaComissao">
</div>
<br /> ';

echo '
<div align="center">
    <input type="button" value="Voltar" onClick="javascript: location.href = \'ELC_Cadastro_Concursos.php\';"> &nbsp;';
if ($Concurso->get("indbarradoporip") == 'S') {
    echo '
    <input type="button" value="Cadastro de Urnas" onClick="javascript: location.href = \'ELC_Lista_Urnas.php\';">';
}
elseif ($Concurso->get("indbarradoporip") == 'E') {
    echo '
    <input type="button" value="Cadastro de Escopo" onClick="javascript: location.href = \'ELC_Lista_Escopo.php\';">';
}
echo '
</div>
<br />

<div id="TabelaChapas">
</div>
<br /> ';

$SQL = " SELECT DataOperacao
         FROM eleicoes.LOGOPERACAO
         WHERE CodConcurso = :Concurso[numero]
           AND CodEleicao = :Eleicao[numero]
           AND Descricao = 'Dados de eleitor carregados' ";
$ConsultaLog = new consulta($SQL);
$ConsultaLog->setparametros("Concurso", $Concurso->getChave());
$ConsultaLog->setparametros("Eleicao", $Eleicao->getChave());
if($ConsultaLog->executa(true)) {
    echo "<div align='center'>\n";
    echo "<font size='2' face='verdana'><b>Dados de eleitor j&aacute; carregados em ".$ConsultaLog->campo("DataOperacao", data)."</b></font>\n";
    echo "</div><br />";
}
?>
<div align="center">
    <input type="button" value="Mostrar somente Eleitores n&atilde;o Autenticados" onClick="javascript: xajax_CarregaListaEleitores(true)"> &nbsp;
    <input type="button" value="Mostrar Todos os Eleitores" onClick="javascript: xajax_CarregaListaEleitores();"> <br />
    (* indica pessoa n&atilde;o autenticada)
</div>

<div id="TabelaEleitores">
</div>

<script language="javascript">
xajax_CarregaDados();
xajax_CarregaListaEleitores();

function ListaEleitoresImpressao(assinatura) {
    window.open('ELC_Impressao_Eleitores.php?Assinatura='+(assinatura ? 'S' : 'N'), 'x', 'status=1, width=750, height=300, top=100, left=40, scrollbars=yes').focus();
}

function ListaEleitoresPDF() {
    location.href = 'ELC_Lista_Eleitores_PDF.php';
}

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

<div id="LayerEdicao" class="Layer1" style="width: 650px; height: 200px; display: none;"></div>