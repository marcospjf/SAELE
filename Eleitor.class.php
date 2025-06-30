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
 * Esta classe representa um Eleitor, relacionando uma PessoaEleicao a uma
 * Eleicao. A classe armazena tamb�m alguns dados do voto dado pelo eleitor:
 * a data e hora do voto, a urna onde foi dada (no caso de ConcursoEleitoral
 * controlado por urnas) e o IP da m�quina.
 */
final class Eleitor extends Entidade {
    protected $NomeTabela = "eleicoes.eleitor";
    protected $VetorChaves = array(
        "codconcurso"       => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
        "codeleicao"        => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "Eleicao"),
        "codpessoaeleicao"  => array(Tipo => numero, Tamanho => 8, Foreign => true, Classe => "PessoaEleicao")
    );
    protected $VetorCampos = array(
        "codurnaorigem"     => array(Nome => "Urna origem", Tipo => numero, Tamanho => 4, Obrigatorio => false, Classe => "UrnaVirtual"),
        "codurnavoto"       => array(Nome => "Urna voto", Tipo => numero, Tamanho => 4, Obrigatorio => false, Classe => "UrnaVirtual"),
        "datahoravoto"      => array(Nome => "Data hora voto", Tipo => datahora, Obrigatorio => false),
        "indefetuouvoto"    => array(Nome => "Indicador voto", Tipo => texto, Tamanho => 1, Obrigatorio => false),
        "ipvoto"            => array(Nome => "IP", Tipo => texto, Tamanho => 20, Obrigatorio => false)
    );
    private $Concurso;
    private $Eleicao;

    public function __construct($Arg1, $Arg2=NULL, $Arg3=NULL) {
        parent::__construct($Arg1, $Arg2, $Arg3);
        if($Arg1 instanceof ConcursoEleitoral) {
            $this->Concurso = $Arg1;
            $this->Eleicao = $Arg2;
        }
        elseif(isset($Arg2['Concurso']) && ($Arg2['Concurso'] instanceof ConcursoEleitoral)
            && isset($Arg2['Eleicao']) && ($Arg2['Eleicao'] instanceof Eleicao)) {
            $this->Concurso = $Arg2['Concurso'];
            $this->Eleicao = $Arg2['Eleicao'];
        }
    }

/**
 * Informa se o Eleitor atual j� votou.
 * @return boolean
 */
    public function jaVotou() {
        return !is_null($this->get("datahoravoto"));
    }

    protected $ClassesAnexadas = array(
        "PessoaEleicao" => array(
            "Tabela" => "eleicoes.pessoaeleicao",
            "Alias" => "P",
            "Chaves" => array("codpessoaeleicao" => "codpessoaeleicao"),
            "Inner" => true)
    );
}

class EleitorException extends Exception {
}
?>