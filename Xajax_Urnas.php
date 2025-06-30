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
db::instancia();

require("../xajax/xajax_core/xajax.inc.php");

$xajax = new xajax('Xajax_Urnas.php');
$xajax->configure('javascript URI', '../xajax/');
$xajax->configure('responseType', 'XML');
$xajax->configure('characterEncoding', 'ISO-8859-1');
$xajax->configure("decodeUTF8Input", true);

//$xajax->setFlag('debug', true);

$xajax->register(XAJAX_FUNCTION, "ListaUrnas");
$xajax->register(XAJAX_FUNCTION, "MostraEdicaoUrna");
$xajax->register(XAJAX_FUNCTION, "SalvaUrna");
$xajax->register(XAJAX_FUNCTION, "ExcluiUrna");

function ListaUrnas() {
    $objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();

    $Excluir = ($Concurso->estadoConcurso() == CONCURSO_NAOINICIADO);

	$Urnas = $Eleicao->devolveUrnas();
	$HTML = '
    <table width="85%" border="1" cellspacing="0" cellpadding="0" class="tabela" align="center">
        <tr bgcolor="#d3d3d3">
            <td align="center" width="12%"><a href="javascript: void(0);" onclick="javascript: xajax_MostraEdicaoUrna();">[incluir urna]</a></td>
            <td align="center">
                <font class="a2">Urnas Eleitorais:</font>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table border="0" cellspacing="0" cellpadding="0" class="tabela" width="100%"> ';
	$i = 1;
	foreach($Urnas as $CodUrna => $Urna) {
	  $HTML .= '
                    <tr class="Linha'.$i.'">
                        <td>
                            &nbsp;<a href="javascript: void(0);" onclick="javascript: xajax_MostraEdicaoUrna('.$CodUrna.');">[editar]</a>
                                   '.$Urna->get("descricao").' ('.$Urna->get("ip").') - '.($Urna->get("indativa") == "S" ? "ATIVA" : "N&Atilde;O ATIVA").($Excluir ? ' &nbsp;
                                    <a href="javascript: void(0);" onclick="javascript: xajax_ExcluiUrna('.$CodUrna.')">[excluir]</a>' : NULL).'
                        </td>
                    </tr> ';
		$i = ($i % 2) + 1;
	}
	$HTML .= '
                </table>
            </td>
        </tr>
    </table>';
	$objResponse->assign("ListaUrnas", "innerHTML", $HTML);
	return $objResponse;
}

function MostraEdicaoUrna($CodUrna=NULL) {
    $objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    if(is_null($CodUrna)) {
        $Descricao = $IP[0] = $IP[1] = $IP[2] = $IP[3] = NULL;
        $Ativa = true;
    }
    else {
        $Urna = $Eleicao->devolveUrna($CodUrna);
        $Descricao = $Urna->get("descricao");
        $Ativa = ($Urna->get("indativa") == "S");
        $IP = $Urna->devolvePartesIP();
    }
    $HTML = '
<form name="FormUrna" id="FormUrna">
	<input type="hidden" name="CodUrna" id="CodUrna" value="'.$CodUrna.'" />
	<table border="0" cellpadding="0" cellspacing="0" width="95%" align="center">
        <tr class="Linha1">
            <td>
                &nbsp;Descri&ccedil;&atilde;o da Urna:
            </td>
        </tr>
        <tr class="Linha2">
            <td>
                &nbsp;&nbsp;&nbsp;
                <input type="text" size="50" maxlength="120" name="Descricao" id="Descricao" value="'.$Descricao.'" />
            </td>
        </tr>
        <tr class="Linha1">
            <td>
                &nbsp;IP:
            </td>
        </tr>
        <tr class="Linha2">
            <td>
                &nbsp;&nbsp;&nbsp;
                <input type="text" size="3" maxlength="3" class="obrigatorio" name="IP[0]" id="IP0" value="'.$IP[0].'" />.
                <input type="text" size="3" maxlength="3" class="obrigatorio" name="IP[1]" id="IP1" value="'.$IP[1].'" />.
                <input type="text" size="3" maxlength="3" class="obrigatorio" name="IP[2]" id="IP2" value="'.$IP[2].'" />.
                <input type="text" size="3" maxlength="3" class="obrigatorio" name="IP[3]" id="IP3" value="'.$IP[3].'" />
            </td>
        </tr>
        <tr class="Linha1">
            <td>
                &nbsp;<input type="checkbox" name="UrnaAtiva" id="UrnaAtiva" value="S" '.($Ativa ? 'checked="checked"' : NULL).' />
                <label for="UrnaAtiva">Ativa</label>
            </td>
        </tr>
        <tr>
          <td align="center">
              <input type="button" value="Cancelar" onclick="javascript: FechaLayer();" /> &nbsp;
              <input type="button" value="Salvar" onclick="javascript: xajax_SalvaUrna(xajax.getFormValues(\'FormUrna\'));" />
            </td>
        </tr>
	</table>
	</form> ';
    $objResponse->assign('DivUrna', 'innerHTML', $HTML);
    $objResponse->script('ExibeLayer()');
    return $objResponse;
}

function SalvaUrna($Form) {
	$objResponse = new xajaxResponse();

	if(trim($Form['Descricao']) == "")
        return $objResponse->alert("Preencha a descri��o da urna.");

    $Controlador = Controlador::instancia();

    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    if(trim($Form['CodUrna']) == "") {
        if(!is_null($Eleicao->devolveUrnaPorIP(implode(".", $Form['IP']))))
            return $objResponse->alert("Este IP j� possui uma urna associada");
        $Urna = $Eleicao->geraUrna();
    }
    else {
        $Urna = $Eleicao->devolveUrna($Form['CodUrna']);
    }

    if(!$Urna->definePartesIP($Form['IP']))
        return $objResponse->alert("Preencha um endere�o de IP v�lido.");

    $Urna->set("descricao", $Form['Descricao']);
    $Urna->set("indativa", (isset($Form['UrnaAtiva']) && $Form['UrnaAtiva'] == 'S' ? 'S' : 'N'));
    $Urna->salva();

    $objResponse->appendResponse(ListaUrnas());
    $objResponse->script("FechaLayer()");
	return $objResponse;
}

function ExcluiUrna($CodUrna) {
    $objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    $Urna = $Eleicao->devolveUrna($CodUrna);
    $Urna->exclui();
    $objResponse->appendResponse(ListaUrnas());
	return $objResponse;
}

$xajax->processRequest();
?>