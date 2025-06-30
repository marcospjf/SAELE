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
 * A classe Voto representa cada voto dado individualmente por um eleitor em
 * uma elei��o. � importante notar que esta classe N�O extende a classe Entidade,
 * e serve apenas para registrar um voto dado, n�o para recuperar um voto individual
 * e buscar seu valor.
 */
class Voto {
    private $Concurso;
    private $Eleicao;

    private $Voto = null;
    private $Urna = null;
    private $Escopo = null;

/**
 * Cria o voto para um dado Concurso e uma dada Eleicao.
 * @param ConcursoEleitoral $Concurso
 * @param Eleicao $Eleicao
 */
    public function __construct(ConcursoEleitoral $Concurso, Eleicao $Eleicao) {
        if($Eleicao->get("codconcurso") != $Concurso->getChave())
            throw new VotoException("Elei��o inv�lida", 0);
        $this->Concurso = $Concurso;
        $this->Eleicao = $Eleicao;
    }

/**
 * Define a chapa para o qual o voto foi dado.
 * @param Chapa $Chapa
 */
    public function defineVotoChapa(Chapa $Chapa) {
        if(($Chapa->get("codconcurso") == $this->Concurso->getChave())
        && ($Chapa->get("codeleicao") == $this->Eleicao->getChave())) {
            $this->Voto = $Chapa;
        }
        else throw new VotoException("Chapa inv�lida para elei��o atual", 0);
    }
/**
 * Define o voto como branco.
 */
    public function defineVotoBranco() {
        $this->Voto = "B";
    }
/**
 * Define o voto como nulo.
 */
    public function defineVotoNulo() {
        $this->Voto = "N";
    }
/**
 * Define a Urna no qual o voto foi dado.
 * @param Urna $Urna
 */
    public function defineUrna(UrnaVirtual $Urna) {
        $this->Urna = $Urna;
        $this->Escopo = NULL;
    }
/**
 * Define o EscopoIP no qual o voto foi dado.
 * @param EscopoIP $Escopo
 */
    public function defineEscopo(EscopoIP $Escopo) {
        $this->Escopo = $Escopo;
        $this->Urna = NULL;
    }
/**
 * Registra o Voto no banco de dados.
 * @return boolean
 */
    public function salva() {
        do {
            $Rand = rand(1, 999999);
            $Consulta = new consulta("
select * from eleicoes.voto
where codconcurso = :CodConcurso[numero]
and codeleicao = :CodEleicao[numero]
and numerorandomico = :Rand[numero]");
            $Consulta->setParametros("CodConcurso", $this->Concurso->getChave());
            $Consulta->setParametros("CodEleicao", $this->Eleicao->getChave());
            $Consulta->setParametros("Rand", $Rand);
        } while($Consulta->executa(true));
        $Insere = new consulta("
insert into eleicoes.voto
 (codconcurso, codeleicao, numerorandomico,
  indvotobranco, indvotonulo, codchapa,
  codurna, dominio)
values
 (:CodConcurso[numero], :CodEleicao[numero], :Rand[numero],
  :VotoBranco[texto], :VotoNulo[texto], :CodChapa[numero],
  :CodUrna[numero], :Dominio[numero]) ");
        $Insere->setParametros("CodConcurso", $this->Concurso->getChave());
        $Insere->setParametros("CodEleicao", $this->Eleicao->getChave());
        $Insere->setParametros("Rand", $Rand);
        
        if($this->Voto == "B") {
            $Insere->setParametros("VotoBranco", "S");
            $Insere->setParametros("VotoNulo", null);
            $Insere->setParametros("CodChapa", null);
        }
        elseif($this->Voto == "N") {
            $Insere->setParametros("VotoBranco", null);
            $Insere->setParametros("VotoNulo", "S");
            $Insere->setParametros("CodChapa", null);
        }
        elseif($this->Voto instanceof Chapa) {
            $Insere->setParametros("VotoBranco", null);
            $Insere->setParametros("VotoNulo", null);
            $Insere->setParametros("CodChapa", $this->Voto->getChave());
        }
        else throw new VotoException("Voto inv�lido: ".$this->Voto, 0);

        if($this->Urna instanceof UrnaVirtual) {
            $Insere->setParametros("CodUrna", $this->Urna->getChave());
            $Insere->setParametros("Dominio", null);
        }
        elseif($this->Escopo instanceof Escopo) {
            $Insere->setParametros("CodUrna", null);
            $Insere->setParametros("Dominio", $this->Escopo->getChave);
        }
        else
            $Insere->setParametros("CodUrna, Dominio", null);

        $Insere->executa();

        return true;
    }
}

class VotoException extends Exception {
}
?>
