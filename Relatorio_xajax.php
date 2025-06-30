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

require_once("../PUBLIC/ConcursoEleitoral.class.php");

require("../xajax/xajax_core/xajax.inc.php");

$xajax = new xajax('Relatorio_xajax.php');
$xajax->configure('javascript URI', '../xajax/');
$xajax->configure('responseType', 'XML');
$xajax->configure('characterEncoding', 'ISO-8859-1');
$xajax->configure("decodeUTF8Input", true);

//$xajax->setFlag('debug', true);

$xajax->register(XAJAX_FUNCTION, "CarregaDados");

$xajax->register(XAJAX_FUNCTION, "CarregaListaChapas");
$xajax->register(XAJAX_FUNCTION, "CarregaListaEleitores");

$xajax->register(XAJAX_FUNCTION, "CarregaInclusaoPessoa");
$xajax->register(XAJAX_FUNCTION, "PesquisaPessoas");
$xajax->register(XAJAX_FUNCTION, "InserePessoa");

$xajax->register(XAJAX_FUNCTION, "CarregaPessoa");

$xajax->register(XAJAX_FUNCTION, "CarregaEdicaoChapa");
$xajax->register(XAJAX_FUNCTION, "SalvaChapa");
$xajax->register(XAJAX_FUNCTION, "CarregaEdicaoParticipacao");
$xajax->register(XAJAX_FUNCTION, "SalvaParticipacao");

$xajax->register(XAJAX_FUNCTION, "ExcluiGerente");
$xajax->register(XAJAX_FUNCTION, "ExcluiMembroComissao");
$xajax->register(XAJAX_FUNCTION, "ExcluiChapa");
$xajax->register(XAJAX_FUNCTION, "ExcluiCandidato");
$xajax->register(XAJAX_FUNCTION, "ExcluiEleitor");
$xajax->register(XAJAX_FUNCTION, "ExcluiTodosEleitores");

function CarregaDados() {
    $objResponse = new xajaxResponse();
    $Controlador = Controlador::instancia();

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();

    $objResponse->appendResponse(CarregaListaGerentes($Concurso, $Eleicao));
    if($Concurso->get("modalidadeconcurso") == MODALIDADE_ELEICAO)
        $objResponse->appendResponse(CarregaListaComissao($Concurso, $Eleicao));
    $objResponse->appendResponse(CarregaListaChapas($Concurso, $Eleicao));
    return $objResponse;
}

function CarregaListaGerentes() {
    $Controlador = Controlador::instancia();
    $PessoaLogin = $Controlador->recuperaPessoaLogada();

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    
    $objResponse = new xajaxResponse();
    $HTML = '
<table width="85%" border="1" cellspacing="0" cellpadding="0" class="tabela" align="center">
    <tr bgcolor="#d3d3d3">
        <td align="center" width="12%"><a href="javascript: void(0);" onclick="javascript: xajax_CarregaInclusaoPessoa(\'G\');">[incluir gerente]</a></td>
        <td align="center">
            <font class="a2">'.$Concurso->retornaString(STR_GERENTES).' da '.$Concurso->retornaString(STR_ELEICAO).' cadastrados:</font>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabela" align="center"> ';
            $i = 1;
            $Gerentes = $Eleicao->devolveGerentes();
            $NumGerentes = count($Gerentes);
            foreach($Gerentes as $Gerente) {
                $Pessoa = $Gerente->getObj("PessoaEleicao");
                $HTML .= '
                <tr class="linha'.$i.'">
                    <td width="10%">&nbsp;'.$Pessoa->get("identificacaousuario").'</td>
                    <td><a href="javascript: void(0);" onclick="javascript: xajax_CarregaPessoa('.$Pessoa->get("codpessoaeleicao").');">'.$Pessoa->get("nomepessoa").'</a></td>';
                if (($NumGerentes > 1) && ($Gerente->get("codpessoaeleicao") != $PessoaLogin->get("codpessoaeleicao")))
                    $HTML .= '
                    <td align="right"><a href="javascript: void(0);" onclick="javascript: xajax_ExcluiGerente('.$Gerente->get("codpessoaeleicao").');">[excluir]</a>&nbsp;</td>';
                else
                    $HTML .= '
                    <td>&nbsp;</td>';
                $HTML .= '
                </tr>';
                $i = ($i % 2) + 1;
            }
            $HTML .= '
            </table>
        </td>
    </tr>
</table>
<br /> ';
    $objResponse->assign("TabelaGerentes", "innerHTML", $HTML);
    return $objResponse;
}

function CarregaListaComissao() {
    $Controlador = Controlador::instancia();
    $PessoaLogin = $Controlador->recuperaPessoaLogada();

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    $objResponse = new xajaxResponse();
    $HTML = '
<table width="85%" border="1" cellspacing="0" cellpadding="0" class="tabela" align="center">
    <tr bgcolor="#d3d3d3">
        <td align="center" width="12%"><a href="javascript: void(0);" onclick="javascript: xajax_CarregaInclusaoPessoa(\'M\');">[incluir membro]</a></td>
        <td align="center">
            <font class="a2">Membros da Comiss�o Eleitoral cadastrados:</font>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabela" align="center">';
            $i = 1;
            $Membros = $Eleicao->devolveMembrosComissao();
            $NumMembros = count($Membros);
            foreach($Membros as $Membro) {
                $Pessoa = $Membro->getObj("PessoaEleicao");
                $HTML .=  '
                <tr class="linha'.$i.'">
                    <td width="10%">&nbsp;'.$Pessoa->get("identificacaousuario").'</td>
                    <td><a href="javascript: void(0);" onclick="javascript: xajax_CarregaPessoa('.$Pessoa->get("codpessoaeleicao").');">'.$Pessoa->get("nomepessoa").'</a></td>';
                if ($NumMembros > 1)
                    $HTML .=  '
                    <td align="right"><a href="javascript: void(0);" onclick="javascript: xajax_ExcluiMembroComissao('.$Pessoa->get("codpessoaeleicao").');">[excluir]</a></td>';
                else
                    $HTML .=  '
                    <td>&nbsp;</td>';
                $HTML .=  '
                </tr>';
                $i = ($i % 2) + 1;
            }
            $HTML .=  '
            </table>
        </td>
    </tr>
</table> ';
    $objResponse->assign("TabelaComissao", "innerHTML", $HTML);
    return $objResponse;
}

function CarregaListaChapas() {
    $Controlador = Controlador::instancia();

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    $objResponse = new xajaxResponse();
    $HTML = '
<table width="85%" border="1" cellspacing="0" cellpadding="0" class="tabela" align="center">
    <tr bgcolor="#d3d3d3">
        <td align="center" width="14%">';
        if($Concurso->abertoParaAlteracoes()) {
            $HTML .= '<a href="javascript: void(0);" onclick="javascript: xajax_CarregaEdicaoChapa();">[Incluir '.$Concurso->retornaString(STR_CHAPA).']</a>';
        }
        else $HTML .= "&nbsp;";
        $HTML .= '
        </td>
        <td align="center">
            <font class="a2">Lista de '.$Concurso->retornaString(STR_CHAPAS).':</font>
        </td>
        <td align="center" width="12%"> ';
        if($Concurso->abertoParaAlteracoes()) {
            $HTML .= '<a href="javascript: Exclui_Todos_Candidatos();">[excluir tudo]</a>';
        }
        else $HTML .= "&nbsp;";
        $HTML .= '
        </td>
    </tr> ';
    $Chapas = $Eleicao->devolveChapas();
    foreach($Chapas as $CodChapa => $Chapa) {
        $HTML .= '
    <tr bgcolor="#d3d3d3">
        <td align="center" width="14%"> ';

        if($Concurso->abertoParaAlteracoes() && $Concurso->admiteCandidatos()) {
            $HTML .= '<a href="javascript: void(0);" onclick="javascript: xajax_CarregaInclusaoPessoa(\'C\', '.$CodChapa.');">[Incluir candidato]</a>';
        }
        else $HTML .= "&nbsp;";

        $HTML .= '
        </td>
        <td align="center">
            <strong>'.$Concurso->retornaString(STR_CHAPA).' '.$Chapa->get("descricao").' ('.$Chapa->get("nrchapa").')</strong> ';
            if($Concurso->abertoParaAlteracoes())
                $HTML .= '<a href="javascript: void(0);" onclick="javascript: xajax_CarregaEdicaoChapa('.$CodChapa.');">[Editar '.$Concurso->retornaString(STR_CHAPA).']</a>';
        $HTML .= '
        </td>
        <td align="center"> ';
        if($Concurso->abertoParaAlteracoes())
            $HTML .= '
            <a href="javascript: void(0);" onclick="javascript: xajax_ExcluiChapa('.$CodChapa.');">[Excluir '.$Concurso->retornaString(STR_CHAPA).']</a>';
        else $HTML .= "&nbsp;";
        $HTML .= '
        </td>
    </tr> ';
        if($Concurso->admiteCandidatos()) {
            $HTML .= '
    <tr>
        <td colspan="3">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabela" align="center">';
                $i = 1;
                $Candidatos = $Chapa->devolveCandidatos();
                foreach($Candidatos as $Candidato) {
                    $Pessoa = $Candidato->getObj("PessoaEleicao");
                    $Participacao = $Candidato->getObj("Participacao");
                    $HTML .= '
                <tr class="linha'.$i.'">
                    <td width="10%">&nbsp;'.$Pessoa->get("identificacaousuario").'</td>
                    <td><a href="javascript: void(0);" onclick="javascript: xajax_CarregaPessoa('.$Pessoa->get("codpessoaeleicao").');">'.$Pessoa->get("nomepessoa").' - '.$Participacao->get("descricaoparticipacao").'</a>';
                    if($Concurso->abertoParaAlteracoes())
                        $HTML .= '
                        <a href="javascript: void(0);" onclick="javascript: xajax_CarregaEdicaoParticipacao('.$CodChapa.', '.$Pessoa->get("codpessoaeleicao").');">[Editar Participacao]</a>';
                    $HTML .= '
                    </td>
                    <td align="right">';
                    if($Concurso->abertoParaAlteracoes())
                        $HTML .= '
                    <a href="javascript: void(0);" onclick="javascript: xajax_ExcluiCandidato('.$Pessoa->get("codpessoaeleicao").');">[excluir]</a></td>';
                    else $HTML .= "&nbsp;";

                    $HTML .= '
                </tr>';
                    $i = ($i % 2) + 1;
                }
            $HTML .= '
            </table>
        </td>
    </tr> ';
        }
    }
    $HTML .= '
</table> ';
    $objResponse->assign("TabelaChapas", "innerHTML", $HTML);
    return $objResponse;
}

function CarregaListaEleitores($ApenasNaoHomologados=false) {
    $Controlador = Controlador::instancia();

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    $objResponse = new xajaxResponse();
    $HTML = '
<table width="85%" border="1" cellspacing="0" cellpadding="0" class="tabela" align="center">
    <tr bgcolor="#d3d3d3">
        <td align="center" width="12%"><a href="javascript: void(0);" onclick="javascript: xajax_CarregaInclusaoPessoa(\'E\', \''.($ApenasNaoHomologados ? 'S' : null).'\');">[incluir eleitor]</a></td>
        <td align="center" class="a2">
            Lista de Eleitores cadastrados:
            <a href="javascript: ListaEleitoresImpressao(true);">[Imprimir para banca]</a>
            <a href="javascript: ListaEleitoresImpressao(false);">[Imprimir para divulga&ccedil;&atilde;o]</a>
            <a href="javascript: ListaEleitoresPDF();">[PDF]</a>
        </td>
        <td align="center" width="12%"><a href="javascript: void(0);" onclick="javascript: if(confirm(\'Tem certeza de que deseja excluir todos os eleitores? (Aten��o: somente ser�o exclu�dos os eleitores que n�o votaram)\')) xajax_ExcluiTodosEleitores(\''.($ApenasNaoHomologados ? 'S' : null).'\');">[excluir todos]</a></td>
    </tr>
    <tr>
        <td colspan="3">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabela" align="center"> ';
    $i = 1;
    if($ApenasNaoHomologados)
        $Eleitores = $Eleicao->devolveEleitores(ELEITOR_NAOHOMOLOGADO);
    else
        $Eleitores = $Eleicao->devolveEleitores();

    foreach($Eleitores as $Eleitor) {
        $Pessoa = $Eleitor->getObj("PessoaEleicao");
        $HTML .= '
                <tr class="linha'.$i.'">
                    <td width="10%">&nbsp;'.$Pessoa->get("identificacaousuario").'</td>
                    <td><a href="javascript: void(0);" onclick="javascript: xajax_CarregaPessoa('.$Eleitor->get("codpessoaeleicao").');">'.$Pessoa->get("nomepessoa").'</a> '.($Pessoa->get("pessoaautenticada") != "S" ? '*' : NULL).'</td>
                    <td align="right"> ';
        if (!$Eleitor->jaVotou())
            $HTML .= ' <a href="javascript: void(0);" onclick="javascript: if(confirm(\'Tem certeza de que deseja excluir o eleitor '.$Pessoa->get("nomepessoa").'?\')) xajax_ExcluiEleitor('.$Eleitor->get("codpessoaeleicao").', \''.($ApenasNaoHomologados ? 'S' : null).'\');">[excluir]</a>&nbsp;';
        else $HTML .= '&nbsp;';
        $HTML .= '
                    </td>
                </tr> ';
        $i = ($i % 2) + 1;
    }
    $HTML .= '
            </table>
        </td>
    </tr>
</table> ';
    $objResponse->assign("TabelaEleitores", "innerHTML", $HTML);
    return $objResponse;
}

function CarregaInclusaoPessoa($Acao, $Arg2=null) {
	$objResponse = new xajaxResponse();
    $Controlador = Controlador::instancia();

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();

    switch($Acao) {
        case "E": $String = "Eleitor"; break;
        case "M": $String = "Membro de Comiss�o"; break;
        case "G": $String = "Gerente"; break;
        case "C": $String = "Candidato"; break;
        default: throw new Exception("Inclus�o inv�lida", 0);
    }
    $HTML = '
<h1>Edi��o de '.$String.'</h1>

<form id="PesquisaPessoa" name="PesquisaPessoa" onsubmit="javascript: xajax_PesquisaPessoas(xajax.getFormValues(\'PesquisaPessoa\')); return false;">
<input type="hidden" name="Acao" value="'.$Acao.'" />
<table>
    <tr class="Linha1">
        <td>Termo da pesquisa:</td>
        <td><input name="TermoPesquisa" id="TermoPesquisa" type="text" size="30" /></td>
        <td><input type="button" value="Pesquisar" onclick="javascript: xajax_PesquisaPessoas(xajax.getFormValues(\'PesquisaPessoa\'));" /></td>
    </tr>
    <tr class="Linha2">
        <td>Tipo de pesquisa:</td>
        <td colspan="2"><input name="TipoPesquisa" type="radio" value="I" checked="checked" /> Termo inicial
                        <input name="TipoPesquisa" type="radio" value="Q" /> Qualquer termo</td>
    </tr>
</table>
</form>

<form id="SelecionaPessoa" name="SelecionaPessoa">
<input type="hidden" name="Acao" value="'.$Acao.'" />
<input type="hidden" name="Arg2" value="'.$Arg2.'" />
<div id="DivSelecionaPessoa"></div>
</form>

<div class="botoes">
    <input type="button" value="Cancelar" onclick="javascript: FechaLayer();" />
</div> ';
    $objResponse->assign("LayerEdicao", "innerHTML", $HTML);
    $objResponse->assign("LayerEdicao", "style.height", "400px");
    $objResponse->script('ExibeLayer()');
    $objResponse->script('document.getElementById(\'TermoPesquisa\').focus();');
    return $objResponse;
}

function PesquisaPessoas($Form) {
	$objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();

    if(strlen($Form['TermoPesquisa']) < 3)
        return $objResponse->alert("O termo de pesquisa deve ter, no m�nimo, 3 caracteres.");
    if($Form['TipoPesquisa'] == 'I')
        $Criterio = $Form['TermoPesquisa']."%";
    else
        $Criterio = "%".$Form['TermoPesquisa']."%";
    $ListaParametros['Criterio'] = $Criterio;
    switch($Form['Acao']) {
        case "E": // Eleitor
            $SQLRestr = '
where not exists
 (select * from eleicoes.eleitor E
  where E.codconcurso = :CodConcurso[numero]
    and E.codeleicao = :CodEleicao[numero]
    and E.codpessoaeleicao = TAB.codpessoaeleicao) ';
            break;
        case "M": // Membro de comiss�o
        case "G": // Gerente:
            $SQLRestr = '
where not exists
 (select * from eleicoes.comissaoeleitoral CE
  where CE.codconcurso = :CodConcurso[numero]
    and CE.codeleicao = :CodEleicao[numero]
    and CE.codpessoaeleicao = TAB.codpessoaeleicao) ';
            break;
        case "C": // Candidato
            $SQLRestr = '
where not exists
 (select * from eleicoes.comissaoeleitoral CE
  where CE.codconcurso = :CodConcurso[numero]
    and CE.codeleicao = :CodEleicao[numero]
    and CE.codpessoaeleicao = TAB.codpessoaeleicao)
  and not exists
 (select * from eleicoes.candidato C
  where C.codconcurso = :CodConcurso[numero]
    and C.codeleicao = :CodEleicao[numero]
    and C.codpessoaeleicao = TAB.codpessoaeleicao)';
            break;
    }

    $Pessoas = new Iterador("PessoaEleicao", $SQLRestr." and upper(NomePessoa) like upper(:Criterio[texto])",
                            array("Criterio" => $Criterio, "CodConcurso" => $Eleicao->get("CodConcurso"), "CodEleicao" => $Eleicao->get("CodEleicao")));

    $HTML = '
<table>
    <tr class="LinhaTitulo">
        <td>Selecione a Pessoa</td>
    </tr>
    <tr class="Linha1" style="text-align: center">
        <td><select name="PessoaSelecionada" size="1"> ';
        foreach($Pessoas as $CodPessoaEleicao => $Pessoa)
            $HTML .= '
                <option value="'.$CodPessoaEleicao.'">'.$Pessoa->get("nomepessoa").'</option>';
    $HTML .= '
            </select></td>
    </tr> ';

    if($Form['Acao'] == "C") { // Ao inserir candidato, deve-se selecionar o tipo de participa��o
        $HTML .= '
    <tr class="LinhaTitulo">
        <td>Selecione o tipo de participa��o:</td>
    </tr>
    <tr class="Linha1">
        <td style="text-align: center;">
            <select name="TipoParticipacao" size="1"> ';
        $Participacoes = ConcursoEleitoral::devolveParticipacoes();
        if(count($Participacoes) == 0)
          $HTML .= '
                <option value=""> -- Nenhuma participa��o cadastrada. Preencha o campo abaixo -- </option>';
        else {
          foreach($Participacoes as $CodParticipacao => $Descricao)
              $HTML .= '
                <option value="'.$CodParticipacao.'">'.$Descricao.'</option>';
        }
        $HTML .= '
            </select>
        </td>
    </tr>
    <tr class="Linha2">
        <td style="text-align: center; font-size: 12px;">
          Ou preencha um novo tipo de participa��o:<br />
          <input type="text" name="NovoTipoParticipacao" value="" />
        </td>
    </tr> ';
    }
    $HTML .= '
    <tr class="Linha2" style="text-align: center">
        <td><input type="button" value="Selecionar" onclick="javascript: xajax_InserePessoa(xajax.getFormValues(\'SelecionaPessoa\'));" />
    </tr>
</table> ';
    return $objResponse->assign("DivSelecionaPessoa", "innerHTML", $HTML);
}

function InserePessoa($Form) {
	$objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Eleicao = $Controlador->recuperaEleicaoEdicao();

    if($Form['PessoaSelecionada']) {
        try {
            switch($Form['Acao']) {
                case "C": //Candidato
                    $Pessoa = new PessoaEleicao($Form['PessoaSelecionada']);
                    $Chapa = $Eleicao->devolveChapa($Form['Arg2']);
                    
                    if(trim($Form['NovoTipoParticipacao']) != "") {
                      $Participacao = new Participacao();
                      $Participacao->set("descricaoparticipacao", $Form['NovoTipoParticipacao']);
                      $Participacao->set("indvalidocandidato", "S");
                      $Participacao->salva();
                    }
                    elseif(trim($Form['TipoParticipacao']) != "")
                      $Participacao = new Participacao($Form['TipoParticipacao']);
                    else
                      return $objResponse->alert("Selecione a participa��o, ou informe um novo tipo de participa��o.");
                      
                    $Chapa->cadastraCandidato($Pessoa, $Participacao);
                    $objResponse->appendResponse(CarregaListaChapas());
                    break;
                case "M": //Membro da Comiss�o
                    $Pessoa = new PessoaEleicao($Form['PessoaSelecionada']);
                    $Eleicao->cadastraMembroComissao($Pessoa);
                    $objResponse->appendResponse(CarregaListaComissao());
                    break;
                case "G": //Gerente
                    $Pessoa = new PessoaEleicao($Form['PessoaSelecionada']);
                    $Eleicao->cadastraGerente($Pessoa);
                    $objResponse->appendResponse(CarregaListaGerentes());
                    break;
                case "E": //Eleitor
                    $Pessoa = new PessoaEleicao($Form['PessoaSelecionada']);
                    $Eleicao->cadastraEleitor($Pessoa);
                    $objResponse->appendResponse(CarregaListaEleitores($Form['Arg2'] == "S"));
                    break;
            }
        }
        catch(CandidatoException $e) {
            return $objResponse->alert($e->getMessage());
        }
        catch(MembroComissaoException $e) {
            return $objResponse->alert($e->getMessage());
        }
    }
    $objResponse->script("FechaLayer()");
    return $objResponse;
}

function CarregaPessoa($CodPessoaEleicao) {
	$objResponse = new xajaxResponse();
    $Controlador = Controlador::instancia();
    if(!$Controlador->recuperaPessoaLogada()->eGerenteSistema())
        throw new Exception("Permiss�o negada", 0);

    $Pessoa = new PessoaEleicao($CodPessoaEleicao);
    $HTML = '
<h1>Dados da Pessoa</h1>

<table>
    <tr class="Linha1">
        <td>Nome:</td>
        <td>'.$Pessoa->get("nomepessoa").'</td>
    </tr>
    <tr class="Linha2">
        <td>Local de Trabalho:</td>
        <td>'.$Pessoa->get("localtrabalho").'</td>
    </tr>
    <tr class="Linha1">
        <td>Registro geral:</td>
        <td>'.$Pessoa->get("nrregistrogeral").'</td>
    </tr>
    <tr class="Linha2">
        <td>CPF:</td>
        <td>'.$Pessoa->get("cpf", cpf).'</td>
    </tr>
    <tr class="Linha1">
        <td>E-Mail:</td>
        <td>'.$Pessoa->get("email").'</td>
    </tr>
</table>

<div class="botoes">
    <input type="button" value="Fechar" onclick="javascript: FechaLayer();" />
</div>
</form>
';
    $objResponse->assign("LayerEdicao", "innerHTML", $HTML);
    $objResponse->assign("LayerEdicao", "style.height", "300px");
    $objResponse->script('ExibeLayer()');
    return $objResponse;
}

function ExcluiGerente($CodPessoaEleicao) {
	$objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    
    $Pessoa = new PessoaEleicao($CodPessoaEleicao);
    $Gerente = $Eleicao->devolveGerente($Pessoa);
    if($Gerente instanceof MembroComissao) {
        $Gerente->exclui();
        $objResponse->appendResponse(CarregaListaGerentes());
        return $objResponse;
    }
    else {
        return $objResponse->alert("Gerente inv�lido");
    }
}

function ExcluiMembroComissao($CodPessoaEleicao) {
	$objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Eleicao = $Controlador->recuperaEleicaoEdicao();

    $Pessoa = new PessoaEleicao($CodPessoaEleicao);
    $Membro = $Eleicao->devolveMembroComissao($Pessoa);
    if($Membro instanceof MembroComissao) {
        $Membro->exclui();
        $objResponse->appendResponse(CarregaListaComissao());
        return $objResponse;
    }
    else {
        return $objResponse->alert("Membro de comiss�o inv�lido");
    }
}

function ExcluiChapa($CodChapa) {
	$objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    $Chapa = $Eleicao->devolveChapa($CodChapa);
    if($Chapa instanceof Chapa) {
        $Chapa->exclui();
        $objResponse->appendResponse(CarregaListaChapas());
    }
    else {
        $objResponse->alert("Chapa inv�lida");
    }
    return $objResponse;
}

function ExcluiCandidato($CodPessoaEleicao) {
	$objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    $Pessoa = new PessoaEleicao($CodPessoaEleicao);
    $Candidato = $Eleicao->devolveCandidato($Pessoa);
    if($Candidato instanceof Candidato) {
        $Candidato->exclui();
        $objResponse->appendResponse(CarregaListaChapas());
    }
    else {
        $objResponse->alert("Candidato inv�lido");
    }
    return $objResponse;
}

function ExcluiEleitor($CodPessoaEleicao, $NaoHomologados) {
	$objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    $Pessoa = new PessoaEleicao($CodPessoaEleicao);
    $Eleitor = $Eleicao->devolveEleitor($Pessoa);
    if($Eleitor instanceof Eleitor) {
        $Eleitor->exclui();
        $objResponse->appendResponse(CarregaListaEleitores($NaoHomologados == "S"));
        return $objResponse;
    }
    else {
        return $objResponse->alert("Eleitor inv�lido");
    }
}

function ExcluiTodosEleitores($NaoHomologados) {
	$objResponse = new xajaxResponse();

    $Controlador = Controlador::instancia();

    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    $Eleicao->excluiEleitores(ELEITOR_NAOVOTOU, $NaoHomologados == "S" ? ELEITOR_NAOHOMOLOGADO : null);
    $objResponse->appendResponse(CarregaListaEleitores($NaoHomologados == "S"));
    return $objResponse;
}

function CarregaEdicaoChapa($CodChapa=NULL) {
    $objResponse = new xajaxResponse();
    $Controlador = Controlador::instancia();

    $Concurso = $Controlador->recuperaConcursoEdicao();

    if(!is_null($CodChapa)) {
        $Eleicao = $Controlador->recuperaEleicaoEdicao();

        $Chapa = $Eleicao->devolveChapa($CodChapa);
        $Descricao = $Chapa->get("descricao");
        $NrChapa = $Chapa->get("nrchapa");
    }
    else $Descricao = $NrChapa = NULL;

    $HTML = '
<h1>Edi��o de '.$Concurso->retornaString(STR_CHAPA).'</h1>

<form id="EdicaoChapa" name="EdicaoChapa">
<input type="hidden" name="CodChapa" value="'.$CodChapa.'" />
<table>
    <tr class="Linha1">
        <td>Descri��o:</td>
        <td><input type="text" size="30" name="Descricao" value="'.$Descricao.'" /></td>
    </tr>
    <tr class="Linha2">
        <td>N�mero:</td>
        <td><input type="text" size="5" name="NrChapa" value="'.$NrChapa.'" /></td>
    </tr>
</table>

<div class="botoes">
    <input type="button" value="Cancelar" onclick="javascript: FechaLayer();" />
    <input type="button" value="Salvar" onclick="javascript: xajax_SalvaChapa(xajax.getFormValues(\'EdicaoChapa\'));" />
</div>
</form>
';
    $objResponse->assign("LayerEdicao", "innerHTML", $HTML);
    $objResponse->assign("LayerEdicao", "style.height", "200px");
    $objResponse->script('ExibeLayer()');
    return $objResponse;
}

function SalvaChapa($Form) {
    $objResponse = new xajaxResponse();

    if(trim($Form['Descricao']) == "")
        return $objResponse->alert("Preencha a Descri��o");
    if(trim($Form['NrChapa']) == "" || !is_numeric($Form['NrChapa']) || $Form['NrChapa'] < 1)
        return $objResponse->alert("N�mero inv�lido");

    $Controlador = Controlador::instancia();

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();
    if(strlen($Form['NrChapa']) != $Eleicao->get("nrdigitoschapa"))
        return $objResponse->alert("O n�mero deve ter ".$Eleicao->get("nrdigitoschapa")
                                  .($Eleicao->get("nrdigitoschapa") > 1 ? ' d�gitos' : ' d�gito'));

    if($Form['CodChapa'] != "") {
        $Chapa = $Eleicao->devolveChapa($Form['CodChapa']);
        if($Form['NrChapa'] != $Chapa->get("NrChapa") && !is_null($Eleicao->devolveChapaPorNumero($Form['NrChapa'])))
            return $objResponse->alert("N�mero j� usado");
    }
    else {
        $Chapa = $Eleicao->geraChapa();
        if(!is_null($Eleicao->devolveChapaPorNumero($Form['NrChapa'])))
            return $objResponse->alert("N�mero j� usado");
    }
    $Chapa->set("Descricao", $Form['Descricao']);
    $Chapa->set("NrChapa", $Form['NrChapa']);
    $Chapa->salva();
    $objResponse->appendResponse(CarregaListaChapas($Concurso, $Eleicao));
    return $objResponse->script("FechaLayer()");
}

function CarregaEdicaoParticipacao($CodChapa, $CodPessoaEleicao) {
    $objResponse = new xajaxResponse();
    $Controlador = Controlador::instancia();

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();

    $Chapa = $Eleicao->devolveChapa($CodChapa);
    if($Chapa === null)
      return $objResponse->alert("Chapa inv�lida!");
    
    $Candidato = $Chapa->devolveCandidato($CodPessoaEleicao);
    if($Candidato === null)
      return $objResponse->alert("Candidato inv�lido!");
    
    $HTML = '
<form id="SalvaParticipacao" name="SalvaParticipacao">
<input type="hidden" name="CodChapa" value="'.$CodChapa.'" />
<input type="hidden" name="CodPessoaEleicao" value="'.$CodPessoaEleicao.'" />
<table>
    <tr class="LinhaTitulo">
        <td>Selecione o tipo de participa��o:</td>
    </tr>
    <tr class="Linha1">
        <td style="text-align: center;">
            <select name="TipoParticipacao" size="1"> ';
        foreach(ConcursoEleitoral::devolveParticipacoes() as $CodParticipacao => $Descricao)
            $HTML .= '
                <option value="'.$CodParticipacao.'"  '.($CodParticipacao == $Candidato->get("codparticipacao") ? 'selected="selected"' : null).'>'.$Descricao.'</option>';
        $HTML .= '
            </select>
        </td>
    </tr>
    <tr class="Linha2">
        <td style="text-align: center; font-size: 12px;">
          Ou preencha um novo tipo de participa��o:<br />
          <input type="text" name="NovoTipoParticipacao" value="" />
        </td>
    </tr>
    
    <tr class="Linha2" style="text-align: center">
        <td><input type="button" value="Selecionar" onclick="javascript: xajax_SalvaParticipacao(xajax.getFormValues(\'SalvaParticipacao\'));" />
    </tr>
</table>
</form>';
    $objResponse->assign("LayerEdicao", "innerHTML", $HTML);
    $objResponse->assign("LayerEdicao", "style.height", "200px");
    $objResponse->script('ExibeLayer()');
    return $objResponse;
}

function SalvaParticipacao($Form) {
    $objResponse = new xajaxResponse();
    $Controlador = Controlador::instancia();
    
    if(trim($Form['NovoTipoParticipacao']) == "" && trim($Form['TipoParticipacao']) == "")
      return $objResponse->alert("Selecione a participa��o, ou informa um novo tipo de participa��o.");

    $Concurso = $Controlador->recuperaConcursoEdicao();
    $Eleicao = $Controlador->recuperaEleicaoEdicao();

    $Chapa = $Eleicao->devolveChapa($Form['CodChapa']);
    if($Chapa === null)
      return $objResponse->alert("Chapa inv�lida!");
    
    $Candidato = $Chapa->devolveCandidato($Form['CodPessoaEleicao']);
    if($Candidato === null)
      return $objResponse->alert("Candidato inv�lido!");
        
    if(trim($Form['NovoTipoParticipacao']) != "") {
      $Participacao = new Participacao();
      $Participacao->set("descricaoparticipacao", $Form['NovoTipoParticipacao']);
      $Participacao->set("indvalidocandidato", "S");
      $Participacao->salva();
    }
    elseif(trim($Form['TipoParticipacao']) != "") {
      $Participacao = new Participacao($Form['TipoParticipacao']);
      if($Participacao->novo())
        return $objResponse->alert("Participa��o inv�lida!");
    }
    
    $Candidato->set("codparticipacao", $Participacao->get("codparticipacao"));
    $Candidato->salva();
    
    $objResponse->appendResponse(CarregaListaChapas($Concurso, $Eleicao));
    return $objResponse->script("FechaLayer()");
}

$xajax->processRequest();
?>