<?php
/*
Copyright 2011 da UFRGS - Universidade Federal do Rio Grande do Sul

Este arquivo é parte do programa SAELE - Sistema Aberto de Eleições Eletrônicas.

O SAELE é um software livre; você pode redistribuí-lo e/ou modificá-lo dentro dos
termos da Licença Pública Geral GNU como publicada pela Fundação do Software Livre
(FSF); na versão 2 da Licença.

Este programa é distribuído na esperança que possa ser útil, mas SEM NENHUMA GARANTIA;
sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO ou APLICAÇÃO EM PARTICULAR.
Veja a Licença Pública Geral GNU/GPL em português para maiores detalhes.

Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título "LICENCA.txt",
junto com este programa, se não, acesse o Portal do Software Público Brasileiro no
endereço www.softwarepublico.gov.br ou escreva para a Fundação do Software Livre(FSF)
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
      "codparticipacao" => array(Nome => "Participação", Tipo => numero, Tamanho => 2, Obrigatorio => true, Classe => "Participacao")
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
                throw new CandidatoException("Esta pessoa faz parte da Comissão Eleitoral", 1);
            if($this->Eleicao->devolveCandidato($this->Pessoa) !== null)
                throw new CandidatoException("Esta pessoa já é candidato desta Eleição", 2);
        }
        parent::salva();
    }
}

class CandidatoException extends Exception {
    
}