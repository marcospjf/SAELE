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

final class Controlador {
    private $PessoaLogada = NULL;
    private $ConcursoEdicao = NULL;
    private $EleicaoEdicao = NULL;
    private $ConcursoVotacao = NULL;
    private $EleicaoVotacao = NULL;
    private $VetorCedula = array();
    private $NrVotoAtual = NULL;
    private $Origem = NULL;

    private function __construct(PessoaEleicao $Pessoa) {
        $this->PessoaLogada = $Pessoa;
    }

/**
 *
 * @param int $CodPessoa
 * @return Controlador
 */
    public static function instancia($Pessoa = null) {
        if(isset($_SESSION['Controlador']))
            return $_SESSION['Controlador'];
        elseif(!is_null($Pessoa)) {
            if($Pessoa->valido()) {
                $_SESSION['Controlador'] = new Controlador($Pessoa);
                return $_SESSION['Controlador'];
            }
            else throw new ControladorException("Pessoa inv�lida", 0);
        }
        else {
            ob_clean();
            echo "Sess�o expirada";
            exit;
        }
    }

/**
 *
 * @return PessoaEleicao
 */
    public function recuperaPessoaLogada() {
        return $this->PessoaLogada;
    }

    public function registraEleicaoEdicao(ConcursoEleitoral $Concurso, Eleicao $Eleicao) {
        if($this->PessoaLogada->eGerenteSistema() || ($Eleicao->verificaComissao($this->PessoaLogada) == COMISSAO_GERENTE)) {
            $this->ConcursoEdicao = $Concurso;
            $this->EleicaoEdicao = $Eleicao;
            return true;
        }
        else throw new ControladorException("Permiss�o negada", 0);
    }

    public function removeEleicaoEdicao() {
        unset($this->ConcursoEdicao);
        $this->ConcursoEdicao = null;
        unset($this->EleicaoEdicao);
        $this->EleicaoEdicao = null;
        return true;
    }

    public function registraConcursoVotacao(ConcursoEleitoral $Concurso) {
        if(!$Concurso->abertoParaVotacao()) {
            if($Concurso->estadoConcurso() < CONCURSO_INICIADO)
                throw new ControladorException("Concurso n�o iniciado", 0);
            if($Concurso->estadoConcurso() > CONCURSO_INICIADO)
                throw new ControladorException("Concurso j� encerrado", 0);
        }
        $this->ConcursoVotacao = $Concurso;
        return true;
    }

    public function registraEleicaoVotacao(Eleicao $Eleicao) {
        if(!($this->ConcursoVotacao instanceof ConcursoEleitoral))
            throw new ControladorException("N�o h� concurso registrado", 0);
        if($Eleicao->get("codconcurso") != $this->ConcursoVotacao->get("codconcurso"))
            throw new ControladorException("A Elei��o n�o faz parte do Concurso", 0);
        $Eleitor = $Eleicao->devolveEleitor($this->PessoaLogada);
        if(is_null($Eleitor))
            throw new ControladorException("Voc� n�o � eleitor desta elei��o", 0);
        if(!is_null($Eleitor->get("datahoravoto")))
            throw new ControladorException("Voc� j� votou nesta elei��o", 0);
        if($this->ConcursoVotacao->get("indbarradoporip") == "S") {
            $Urna = $Eleicao->devolveUrnaPorIP($_SERVER['REMOTE_ADDR']);
            if(is_null($Urna))
                throw new ControladorException("M�quina n�o autorizada nesta elei��o", 0);
            if($Urna->get("indativa") != "S")
                throw new ControladorException("M�quina n�o autorizada nesta elei��o", 0);
        }
        elseif($this->ConcursoVotacao->get("indbarradoporip") == "E") {
            $Escopo = $Eleicao->devolveEscopoPorPrefixoIP($_SERVER['REMOTE_ADDR']);
            if(is_null($Escopo))
                throw new ControladorException("M�quina n�o autorizada nesta elei��o", 0);
            if($Escopo->get("indativa") != "S")
                throw new ControladorException("M�quina n�o autorizada nesta elei��o", 0);
        }
        if(!$Eleicao->eleicaoZerada())
            throw new ControladorException("Elei��o n�o iniciada", 0);
        $this->EleicaoVotacao = $Eleicao;
        return true;
    }

    public function removeEleicaoVotacao() {
        unset($this->EleicaoVotacao);
        $this->EleicaoVotacao = null;
    }
/**
 *
 * @return ConcursoEleitoral
 */
    public function recuperaConcursoEdicao() {
        if($this->ConcursoEdicao instanceof ConcursoEleitoral) {
            return $this->ConcursoEdicao;
        }
        else throw new ControladorException("N�o h� concurso em edi��o", 1);
    }

/**
 *
 * @return ConcursoEleitoral
 */
    public function recuperaConcursoVotacao() {
        if($this->ConcursoVotacao instanceof ConcursoEleitoral) {
            $this->registraConcursoVotacao($this->ConcursoVotacao); // Refaz o registro para testar novamente as restri��es
            return $this->ConcursoVotacao;
        }
        else throw new ControladorException("N�o h� concurso para vota��o", 1);
    }
/**
 *
 * @return Eleicao
 */
    public function recuperaEleicaoEdicao() {
        if($this->EleicaoEdicao instanceof Eleicao) {
            return $this->EleicaoEdicao;
        }
        else throw new ControladorException("N�o h� elei��o em edi��o", 2);
    }
/**
 *
 * @return Eleicao
 */
    public function recuperaEleicaoVotacao() {
        if($this->EleicaoVotacao instanceof Eleicao) {
            $this->registraEleicaoVotacao($this->EleicaoVotacao);
            return $this->EleicaoVotacao;
        }
        else throw new ControladorException("N�o h� elei��o para vota��o", 2);
    }

    public function inicializaVetorCedula() {
        $this->VetorCedula = array();
        array_fill(1, $this->EleicaoVotacao->get("nrpossibilidades"), "B");
    }

    public function registraNrVotoAtual($Voto) {
        if($Voto <= $this->EleicaoVotacao->get("nrpossibilidades"))
            $this->NrVotoAtual = $Voto;
        else throw new ControladorException("Voto al�m do n�mero de possibilidades", 3);
    }

    public function registraVoto($Voto) {
        if(($Voto == "B") || ($Voto == "N") || (!is_null($this->EleicaoVotacao->devolveChapaPorNumero($Voto)))) {
            if(is_numeric($Voto)) {
                $PosVoto = array_search($Voto, $this->VetorCedula);
                if(($PosVoto !== false) && ($PosVoto !== $this->NrVotoAtual))
                    throw new ControladorException("Voto repetido", 4);
            }
            $this->VetorCedula[$this->NrVotoAtual] = $Voto;
        }
        else throw new ControladorException("Voto inv�lido: ".$Voto, 5);
    }

    public function devolveVetorCedula() {
        return $this->VetorCedula;
    }

    public function devolveNrVotoAtual() {
        return $this->NrVotoAtual;
    }

    public function devolveVoto($Voto) {
        if(isset($this->VetorCedula[$Voto]))
            return $this->VetorCedula[$Voto];
    }

    public function registraOrigem($Origem) {
        $this->Origem = $Origem;
    }

    public function recuperaOrigem() {
        return $this->Origem;
    }


    public function __destruct() {
        $_SESSION['Controlador'] = $this;
    }
}

class ControladorException extends Exception {
}
?>
