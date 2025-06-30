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

final class Candidato extends Entidade {
    protected $NomeTabela = "eleicoes.candidato";
    protected $VetorChaves = array(
      "codconcurso"     => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "codeleicao"      => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "Eleicao"),
      "codchapa"        => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "Chapa"),
      "codpessoaeleicao"=> array(Tipo => numero, Tamanho => 8, Foreign => true, Classe => "PessoaEleicao")
    );
    protected $VetorCampos = array(
      "codparticipacao" => array(Nome => "Participa��o", Tipo => numero, Tamanho => 2, Obrigatorio => true, Classe => "Participacao")
    );

    protected $ClassesAnexadas = array(
        "PessoaEleicao" => array(
            "Tabela" => "eleicoes.pessoaeleicao",
            "Chaves" => array("codpessoaeleicao" => "codpessoaeleicao"),
            "Inner" => true),
        "Participacao" => array(
            "Tabela" => "eleicoes.participacao",
            "Chaves" => array("codparticipacao" => "codparticipacao"),
            "Inner" => true)
    );

    private $Concurso, $Eleicao, $Chapa, $Pessoa;

    public function __construct($Arg1, $Arg2=null, $Arg3=null, $Arg4=null) {
        parent::__construct($Arg1, $Arg2, $Arg3, $Arg4);
        if($Arg1 instanceof ConcursoEleitoral) {
            $this->Concurso = $Arg1;
            $this->Eleicao = $Arg2;
            $this->Chapa = $Arg3;
            $this->Pessoa = $Arg4;
        }
    }

    public function salva() {
        if($this->novo()) {
            if($this->Eleicao->verificaComissao($this->Pessoa) !== false)
                throw new CandidatoException("Esta pessoa faz parte da Comiss�o Eleitoral", 1);
            if($this->Eleicao->devolveCandidato($this->Pessoa) !== null)
                throw new CandidatoException("Esta pessoa j� � candidato desta Elei��o", 2);
        }
        parent::salva();
    }
}

class CandidatoException extends Exception {
    
}