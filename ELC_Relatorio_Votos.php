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

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

$Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
$Eleicao = $Concurso->devolveEleicao($_GET['CodEleicao']);
$Urna = (isset($_GET['CodUrna']) ? $Eleicao->devolveUrna($_GET['CodUrna']) : NULL);

require_once('../fpdf.php');
$PDF = new FPDF();
//define('FPDF_FONTPATH', '../Extensao/ObjetosPHP/PDF/font/');

class EleicaoPDF extends FPDF {
    private $Concurso, $Eleicao, $Urna;

    public function defineParametros(ConcursoEleitoral $Concurso, Eleicao $Eleicao, UrnaVirtual $Urna=NULL) {
        $this->Concurso = $Concurso;
        $this->Eleicao = $Eleicao;
        $this->Urna = $Urna;
    }

    function Header() {
        $this->SetFont('Arial','B', 12);
        $this->setX(0);
        $this->sety(15);
        $this->Cell(0, 6, 'SISTEMA DE ELEI��ES', 0, 1, 'C');
        $this->Cell(0, 6, 'Apura��o da '.$this->Concurso->retornaString(STR_ELEICAO), 0, 1, 'C');

        $this->line(10,30,200,30);

        $this->sety(31);
        $this->SetFont('Arial','B', 9);
        $this->Cell(35, 4, $this->Concurso->retornaString(STR_CONCURSOELEITORAL).':', 0, 0, 'R');
        $this->SetFont('Arial','', 9);
        $this->MultiCell(0, 4, $this->Concurso->get("descricao"), 0, 'L');

        $this->SetFont('Arial','B', 9);
        $this->Cell(35, 4, $this->Concurso->retornaString(STR_ELEICAO).':', 0, 0, 'R');
        $this->SetFont('Arial','', 9);
        $this->MultiCell(0, 4, $this->Eleicao->get("descricao"), 0, 'L');

        if(!is_null($this->Urna)) {
            $this->SetFont('Arial','B', 9);
            $this->Cell(35, 4, 'Urna:', 0, 0, 'R');
            $this->SetFont('Arial','', 9);
            $this->MultiCell(0, 4, $this->Urna->get("descricao"), 0, 'L');
        }

        $this->line(10,$this->gety(),200,$this->gety());
    }
}

$PDF = new EleicaoPDF('P','mm','A4');
$PDF->defineParametros($Concurso, $Eleicao, $Urna);
$PDF->AddPage();

$PDF->SetY($PDF->GetY() + 5);
$PDF->SetFont('Arial', 'B', 9);
$PDF->Cell(150, 4, $Concurso->retornaString(STR_CHAPA), 'TLRB');
$PDF->Cell(40, 4, 'Votos Recebidos', 'TLRB', 1);
$PDF->SetFont('Arial', '', 9);

$Chapas = $Eleicao->devolveChapas($_GET['ord'] == "nr" ? CHAPAS_PORNUMERO : CHAPAS_PORVOTOSDESC);
foreach($Chapas as $CodChapa => $Chapa) {
    if($Concurso->admiteCandidatos()) {
        $Candidatos = $Chapa->devolveCandidatos();
        if(!$Candidatos->TemRegistro()) $Border = 'LRB'; else $Border = 'LR';
    } else {
        $Candidatos = array();
        $Border = 'LRB';
    }

    if(!is_null($Urna))
        $Votos = $Chapa->devolveNrVotosPorUrna($Urna);
    else
        $Votos = $Chapa->get("nrvotosrecebidos");
  
    $Y = $PDF->GetY();

    $PDF->Multicell(150, 4, $Concurso->retornaString(STR_CHAPA)." ".$Chapa->get("nrchapa").' - '.$Chapa->get("descricao"), $Border);

    $Y2 = $PDF->GetY();

    $Num = $Y2 - $Y;
    if($Num > 4) {
        $Num = round($Num / 4);
        $Quebra = str_repeat("\n", $Num);
    }
    else $Quebra = NULL;
    $PDF->SetFont('Arial', 'B', 9);
    $PDF->SetXY(160, $Y);
    $PDF->Multicell(40, 4, $Votos.$Quebra, $Border);
    $PDF->SetFont('Arial', '', 9);

    $PDF->SetXY(10, $Y2);
    $i = 1;
    $Nr = count($Candidatos);
    foreach($Candidatos as $Candidato) {
        $PartCandidato = $Candidato->getObj("Participacao");
        $PessoaCandidato = $Candidato->getObj("PessoaEleicao");
        if($i == $Nr) $Border = "LRB"; else $Border = "LR";

        $PDF->Cell(150, 4, '  '.$PartCandidato->get("descricaoparticipacao").': '.$PessoaCandidato->get("nomepessoa"), $Border);
        $PDF->Cell(40, 4, '', $Border, 1);
        $i++;
    }
}

$PDF->SetFont('Arial', 'B', 9);
if(!is_null($Urna))
    $Votos = $Eleicao->devolveNrVotosBrancosPorUrna($Urna);
else
    $Votos = $Eleicao->get("votosbrancos");

$PDF->Cell(150, 4, 'Votos em Branco: ', 'LRB', 0, 'R');
$PDF->Cell(40, 4, $Votos, 'LRB', 1);

if(!is_null($Urna))
    $Votos = $Eleicao->devolveNrVotosNulosPorUrna($Urna);
else
    $Votos = $Eleicao->get("votosnulos");
$PDF->Cell(150, 4, 'Votos Nulos: ', 'LRB', 0, 'R');
$PDF->Cell(40, 4, $Votos, 'LRB', 1);

if($Concurso->estadoConcurso() == CONCURSO_ENCERRADO) {
    $PDF->SetY($PDF->GetY() + 5);

    $Eleitores = count($Eleicao->devolveEleitores());
    if(is_null($Urna))
        $Votantes = count($Eleicao->devolveEleitores(ELEITOR_JAVOTOU));
    else
        $Votantes = count($Urna->devolveVotantes());

    $PDF->Cell(150, 4, 'Total de Eleitores: ', 'TLRB', 0, 'R');
    $PDF->Cell(40, 4, $Eleitores, 'TLRB', 1);
    $PDF->Cell(150, 4, 'Total de Votantes Efetivos: ', 'TLRB', 0, 'R');
    $PDF->Cell(40, 4, $Votantes, 'TLRB', 1);
}

$LogZeresima = LogOperacao::getLogPorDescricao(DESCRICAO_ZERESIMA, $Concurso);
if(!is_null($LogZeresima)) {
  $PDF->SetFont('Arial', '', 9);

  $PDF->SetY($PDF->GetY() + 4);
  $PDF->Cell(0, 0, 'Zer�sima realizada dia '.$LogZeresima->get("dataoperacao", data)
                  .' �s '.$LogZeresima->get("dataoperacao", hora), NULL, 0, 'L');
}

$LogContagem = LogOperacao::getLogPorDescricao(DESCRICAO_CONTAGEM, $Concurso);
if(!is_null($LogContagem)) {
  $PDF->SetFont('Arial', '', 9);

  $PDF->SetY($PDF->GetY() + 4);
  $PDF->Cell(0, 0, 'Contagem dos votos realizada dia '.$LogContagem->get("dataoperacao", data)
                  .' �s '.$LogContagem->get("dataoperacao", hora), NULL, 0, 'L');
}

$LogRecontagens = LogOperacao::getIteradorLogsPorDescricao(DESCRICAO_RECONTAGEM, $Concurso);
foreach($LogRecontagens as $LogRecontagem) {
  $PDF->SetY($PDF->GetY() + 4);
  $PDF->Cell(0, 0, 'Recontagem dos votos realizada dia '.$LogRecontagem->get("dataoperacao", data)
                  .' �s '.$LogRecontagem->get("dataoperacao", hora), NULL, 0, 'L');
}

$SQL = "SELECT now() as agora ";
$ConsultaDataAtual = new consulta($SQL);
$ConsultaDataAtual->executa(true);

$PDF->SetY($PDF->GetY() + 10);
$PDF->SetFont('Arial', '', 9);
$PDF->Cell(0, 0, 'Impresso dia '.$ConsultaDataAtual->campo("agora", data)
                .' �s '.$ConsultaDataAtual->campo("agora", hora), NULL, 0, 'L');

$PDF->Output('Relatorio.pdf','D');
exit();
?>