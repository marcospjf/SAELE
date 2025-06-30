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

final class Chapa extends Entidade {
    protected $NomeTabela = "eleicoes.chapa";
    protected $VetorChaves = array(
      "codconcurso" => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "codeleicao"  => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "Eleicao"),
      "codchapa"    => array(Tipo => numero, Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "descricao"           => array(Nome => "Descri��o", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "nrchapa"             => array(Nome => "N�mero da chapa", Tipo => numero, Tamanho => 2, Obrigatorio => true),
      "nrvotosrecebidos"    => array(Nome => "N�mero de votos contados", Tipo => numero, Obrigatorio => false)
    );
    private $Concurso;
    private $Eleicao;

    public function __construct($Arg1, $Arg2=NULL, $Arg3=NULL) {
        parent::__construct($Arg1, $Arg2, $Arg3);
        if($Arg1 instanceof ConcursoEleitoral) {
            $this->Concurso = $Arg1;
            $this->Eleicao = $Arg2;
        }
        else {
            $this->Concurso = $Arg2['Concurso'];
            $this->Eleicao = $Arg2['Eleicao'];
        }
    }

/**
 * Devolve o n�mero de votos recebidos pela Chapa atual. Como este procedimento 
 * informa parte do resultado da elei��o, ele s� pode ser executado ap�s o
 * t�rmino do per�odo de vota��o.
 * @return int
 */
    public function devolveNrVotos() {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new ChapaException("Os votos s� podem ser contados ap�s o t�rmino do concurso", 1);

        $SQL = " select count(*) as Nr from eleicoes.voto
                 where codconcurso = :codconcurso[numero]
                   and codeleicao = :codeleicao[numero]
                   and codchapa = :codchapa[numero] ";
        $Consulta = new Consulta($SQL);
        $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
        $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
        $Consulta->setParametros("codchapa", $this->get("codchapa"));
        $Consulta->executa(true);
        return $Consulta->campo("Nr");
    }

/**
 * Devolve o n�mero de votos recebidos pela Chapa atual em uma determinada chapa.
 * Como este procedimento informa parte do resultado da elei��o, ele s� pode
 * ser executado ap�s o t�rmino do per�odo de vota��o.
 * @return int
 */
    public function devolveNrVotosPorUrna(UrnaVirtual $Urna) {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new ChapaException("Os votos s� podem ser contados ap�s o t�rmino do concurso", 1);

        if( ($Urna->get("codconcurso") == $this->get("codconcurso"))
         && ($Urna->get("codeleicao") == $this->get("codeleicao"))) {
            $SQL = " select count(*) as Nr from eleicoes.voto
                     where codconcurso = :codconcurso[numero]
                       and codeleicao = :codeleicao[numero]
                       and codchapa = :codchapa[numero]
                       and codurna = :codurna[numero] ";
            $Consulta = new Consulta($SQL);
            $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
            $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
            $Consulta->setParametros("codchapa", $this->get("codchapa"));
            $Consulta->setParametros("codurna", $Urna->get("codurna"));
            $Consulta->executa(true);
            return $Consulta->campo("Nr");
        }
        else throw new ChapaException("Urna inv�lida", 0);
    }

/**
 * Devolve um candidato da atual chapa. Caso ele n�o exista, devolve NULL.
 * @param int $CodPessoaEleicao O c�digo do candidato.
 * @return Candidato
 */
    public function devolveCandidato($CodPessoaEleicao) {
        $Candidato = new Candidato($this->Concurso, $this->Eleicao, $this, $CodPessoaEleicao);
        if(!$Candidato->novo())
            return $Candidato;
        else
            return null;
    }
    
/**
 * Retorna um Iterador com todos os Candidatos da Chapa atual.
 * @return Iterador
 */
    public function devolveCandidatos() {
        $SQL = " where TAB.codconcurso = :CodConcurso[numero]
                   and TAB.codeleicao = :CodEleicao[numero]
                   and TAB.codchapa = :CodChapa[numero] ";
        $Campos = array("CodConcurso" => $this->get("codconcurso"),
                        "CodEleicao" => $this->get("codeleicao"),
                        "CodChapa" => $this->get("codchapa"));
        return new Iterador("Candidato", $SQL, $Campos);
    }

/**
 * Cadastra a PessoaEleicao informada como Candidato da Chapa atual, com a
 * Participacao informada.
 * @param PessoaEleicao $Pessoa
 * @param Participacao $Participacao
 * @return boolean
 */
    public function cadastraCandidato(PessoaEleicao $Pessoa, Participacao $Participacao) {
        $Candidato = new Candidato($this->Concurso, $this->Eleicao, $this, $Pessoa);
        $Candidato->set("codparticipacao", $Participacao);
        $Candidato->salva();
        return true;
    }

    public function exclui() {
        $Candidatos = $this->devolveCandidatos();
        foreach($Candidatos as $Candidato)
            $Candidato->exclui();
        return parent::exclui();
    }
}

class ChapaException extends Exception {
}