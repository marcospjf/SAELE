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
$db = db::instancia();

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

try {
    $Concurso = $Controlador->recuperaConcursoVotacao();
    $Eleicao = $Controlador->recuperaEleicaoVotacao();
}
catch(ControladorException $e) { ?>
    <div class="Erro">
        <p><strong>Erro:</strong> <?=$e->getMessage()?></p>
        <a href="../ELC_Logout.php">Voltar</a>
    </div>
    </body>
    </html>
    <?php
    exit;
}

$Eleitor = new Eleitor($Concurso, $Eleicao, $Pessoa);

switch($Concurso->get("indbarradoporip")) {
    case "S":
        $Urna = $Eleicao->devolveUrnaPorIP($_SERVER['REMOTE_ADDR']); break;
    case "E":
        $Escopo = $Eleicao->devolveEscopoPorPrefixoIP($_SERVER['REMOTE_ADDR']); break;
}

$db->iniciaTransacao();

$Consulta = new Consulta("lock table eleicoes.voto; ");
$Consulta->executa();

$Eleicao->geraLogOperacao(DESCRICAO_INICIOVOTO);
$VetorCedula = $Controlador->devolveVetorCedula();
foreach($VetorCedula as $DadoVoto) {
    $Voto = new Voto($Concurso, $Eleicao);
    if(is_numeric($DadoVoto))
        $Voto->defineVotoChapa($Eleicao->devolveChapaPorNumero($DadoVoto));
    elseif($DadoVoto == "B")
        $Voto->defineVotoBranco();
    elseif($DadoVoto == "N")
        $Voto->defineVotoNulo();

    if(isset($Urna))
        $Voto->defineUrna($Urna);
    elseif(isset($Escopo))
        $Voto->defineEscopo($Escopo);

    $Voto->salva();
}
if(isset($Urna))
    $Eleitor->set("codurnavoto", $Urna);
$Eleitor->set("indefetuouvoto", "S");
$Eleitor->set("datahoravoto", null, "now()");
$Eleitor->set("ipvoto", $_SERVER['REMOTE_ADDR']);
$Eleitor->salva();

$Eleicao->geraLogOperacao(DESCRICAO_VOTOEFETUADO);

$db->encerraTransacao();

header("Location: ELC_Fim.php");
