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
 * Esta classe representa um endere�o IP que ter� permiss�o de acessar a
 * �rea de vota��o da Eleicao correspondente, caso o ConcursoEleitoral seja
 * restrito por urnas.
 */
class UrnaVirtual extends Entidade {
    protected $NomeTabela = "eleicoes.urnavirtual";
    protected $VetorChaves = array(
      "codconcurso" => array(Tipo => "numero", Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "codeleicao"  => array(Tipo => "numero", Tamanho => 4, Foreign => true, Classe => "Eleicao"),
      "codurna"     => array(Tipo => "numero", Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "ip"          => array(Nome => "Endere�o IP", Tipo => texto, Tamanho => 15, Obrigatorio => true),
      "descricao"   => array(Nome => "Descri��o", Tipo => texto, Tamanho => 120, Obrigatorio => true),
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
 * Devolve os octetos do endere�o IP da urna como um vetor de quatro posi��es.
 * @return array
 */
    public function devolvePartesIP() {
        preg_match('/^([\d]{1,3})\.([\d]{1,3})\.([\d]{1,3})\.([\d]{1,3})$/', $this->get("ip"), $IP);
        if(count($IP) == 5)
            return array($IP[1], $IP[2], $IP[3], $IP[4]);
        else
            return array();
    }

    public function set($Campo, $Valor, $Mascara=null) {
        if(!$this->EdicaoIP && ($Campo == "ip"))
            throw new Exception("Utilize o m�todo definePartesIP()", 0);
        else return parent::set($Campo, $Valor, $Mascara);
    }

/**
 * Define o endere�o IP da urna, recebendo os quatro octetos como um vetor de
 * quatro posi��es. O endere�o DEVE ser definido dessa forma.
 * @param array $IP
 * @return boolean
 */
    public function definePartesIP($IP) {
        if( (isset($IP[0]) && is_numeric($IP[0]) && ($IP[0] >= 0) && ($IP[0] <= 255))
         && (isset($IP[1]) && is_numeric($IP[1]) && ($IP[1] >= 0) && ($IP[1] <= 255))
         && (isset($IP[2]) && is_numeric($IP[2]) && ($IP[2] >= 0) && ($IP[2] <= 255))
         && (isset($IP[3]) && is_numeric($IP[3]) && ($IP[3] >= 0) && ($IP[3] <= 255))) {
            $this->EdicaoIP = true;
            $this->set("ip", implode(".", $IP));
            $this->EdicaoIP = false;
            return true;
        }
        else return false;
    }
/**
 * Devolve um iterador com todos os eleitores que votaram nesta urna.
 * @return Iterador
 */
    public function devolveVotantes() {
        return new Iterador("Eleitor",
            " where codconcurso = :codconcurso[numero]
                and codeleicao = :codeleicao[numero]
                and codurnavoto = :codurna[numero]",
            array("codconcurso" => $this->get("codconcurso"),
                  "codeleicao" => $this->get("codeleicao"),
                  "codurna" => $this->get("codurna")));
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