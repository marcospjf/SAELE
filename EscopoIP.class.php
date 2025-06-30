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

/**
 * Esta classe representa um escopo de endere�os IP, determinado por um
 * prefixo. Esse prefixo pode conter 1, 2 ou 3 octetos.
 */
class EscopoIP extends Entidade {
    protected $NomeTabela = "eleicoes.dominioip";
    protected $VetorChaves = array(
      "codconcurso" => array(Tipo => "numero", Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "codeleicao"  => array(Tipo => "numero", Tamanho => 4, Foreign => true, Classe => "Eleicao"),
      "nrseqdominio"=> array(Tipo => "numero", Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "descricao"   => array(Nome => "Descri��o", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "prefixoip"   => array(Nome => "Prefixo IP", Tipo => texto, Tamanho => 15, Obrigatorio => true),
      "indativa"    => array(Nome => "Ativa", Tipo => texto, Tamanho => 1, Obrigatorio => true, Valores => array("S", "N"))
    );
    private $EdicaoIP = false;
    private $Concurso;
    private $Eleicao;

    public function __construct($Arg1, $Arg2=NULL, $Arg3=NULL) {
        parent::__construct($Arg1, $Arg2, $Arg3);
        if(($Arg1 instanceof ConcursoEleitoral) && ($Arg2 instanceof Eleicao)) {
            $this->Concurso = $Arg1;
            $this->Eleicao = $Arg2;
        }
        else {
            $this->Concurso = $Arg2['Concurso'];
            $this->Eleicao = $Arg2['Eleicao'];
        }
    }
/**
 * Devolve os octetos do prefixo IP da urna como um vetor.
 * @return array
 */
    public function devolvePartesIP() {
        return explode(".", $this->get("prefixoip"));
    }

    public function set($Campo, $Valor, $Mascara=null) {
        if(!$this->EdicaoIP && ($Campo == "prefixoip"))
            throw new Exception("Utilize o m�todo definePartesIP()", 0);
        else return parent::set($Campo, $Valor, $Mascara);
    }

/**
 * Define o prefixo IP do escopo, recebendo os octetos como um vetor.
 * O prefixo DEVE ser definido dessa forma.
 * @param array $IP
 * @return boolean
 */
    public function definePartesIP($IP) {
        if((count($IP) < 4) || !isset($IP[0]) || (trim($IP[0]) == "") || !is_numeric($IP[0]) || ($IP[0] < 0) || ($IP[0] > 255))
            return false;
        $IPCorreto[0] = $IP[0];
        $Prefixo = false;
        for($i = 1; $i < 4; $i++) {
            if( ($Prefixo && isset($IP[$i]) && trim($IP[$i]) != "")
             || (!$Prefixo && isset($IP[$i]) && trim($IP[$i]) != "" &&
                 ((!is_numeric($IP[$i])) || ($IP[$i] < 0) || ($IP[$i] > 255))))
                return false;
            if(!isset($IP[$i]) || (trim($IP[$i]) == "")) {
                $Prefixo = true;
            }
            else
                $IPCorreto[$i] = $IP[$i];
        }
        $this->EdicaoIP = true;
        $this->set("prefixoip", implode(".", $IPCorreto));
        $this->EdicaoIP = false;
        return true;
    }

    public function exclui() {
        if($this->Concurso->estadoConcurso() != CONCURSO_NAOINICIADO)
            throw new UrnaVirtualException("O Concurso Eleitoral j� iniciou", 0);
        parent::exclui();
    }
}

class UrnaVirtualException extends Exception {
}
?>