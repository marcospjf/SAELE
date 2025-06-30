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
$Direito = $Pessoa->eGerenteSistema();

$Concurso = $Controlador->recuperaConcursoEdicao();
$Eleicao = $Controlador->recuperaEleicaoEdicao();

$Eleitores = $Eleicao->devolveEleitores();

require_once('../fpdf.php');

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
        $this->Cell(0, 6, 'Relat�rio de Eleitores', 0, 1, 'C');

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

        $this->SetY($this->GetY() + 5);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 4, 'Lista de Eleitores:', 'TLRB', 1, 'C');
        $this->SetFont('Arial', '', 9);
    }
}

$PDF = new EleicaoPDF('P','mm','A4');
$PDF->defineParametros($Concurso, $Eleicao);
$PDF->AddPage();

$Nr = count($Eleitores);
$i = 1;
foreach($Eleitores as $Eleitor) {
    $Pessoa = $Eleitor->getObj("PessoaEleicao");
    if($i == $Nr) $Border = 'B'; else $Border = '';
    $PDF->Cell(190, 4, $Pessoa->get("nomepessoa"), 'LRB', 1);
    $i++;
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