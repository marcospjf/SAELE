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

define("COMISSAO_MEMBRO", 1);
define("COMISSAO_GERENTE", 2);

define("ELEITOR_JAVOTOU", "JAVOTOU");
define("ELEITOR_NAOVOTOU", "NAOVOTOU");
define("ELEITOR_HOMOLOGADO", "HOMOLOGADO");
define("ELEITOR_NAOHOMOLOGADO", "NAOHOMOLOGADO");

define("CHAPAS_PORNUMERO", 0);
define("CHAPAS_PORVOTOSDESC", 1);

/**
 * Esta classe representa uma elei��o dentro de um ConcursoEleitoral, no qual
 * o eleitor poder� ter� uma ou mais possibilidades de voto em um n�mero determinado
 * de chapas.
 */
final class Eleicao extends Entidade {
    protected $NomeTabela = "eleicoes.eleicao";
    protected $VetorChaves = array(
      "codconcurso" => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "codeleicao"  => array(Tipo => numero, Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "descricao"           => array(Nome => "Descri��o", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "nrpossibilidades"    => array(Nome => "N�mero de possibilidades de votos", Tipo => numero, Obrigatorio => true),
      "nrdigitoschapa"      => array(Nome => "N�mero de d�gitos para chava", Tipo => numero, Obrigatorio => true, Valores => array(1, 2, 3, 4, 5)),
      "votosbrancos"        => array(Nome => "Votos brancos", Tipo => numero, Obrigatorio => false),
      "votosnulos"          => array(Nome => "Votos nulos", Tipo => numero, Obrigatorio => false)
    );
    private $Concurso;

    public function __construct($Arg1, $Arg2=NULL) {
        parent::__construct($Arg1, $Arg2);
        if($Arg1 instanceof ConcursoEleitoral) {
            $this->Concurso = $Arg1;
        }
        elseif($Arg2 instanceof ConcursoEleitoral) {
            $this->Concurso = $Arg2;
        }
    }

/**
 * Cria uma nova chapa para a atual elei��o.
 * @return Chapa
 */
    public function geraChapa() {
        return new Chapa($this->Concurso, $this);
    }

/**
 * Devolve uma chapa da atual elei��o. Caso ela n�o exista, devolve NULL.
 * @param int $CodChapa O c�digo da chapa desejada.
 * @return Chapa
 */
    public function devolveChapa($CodChapa) {
        $Chapa = new Chapa($this->Concurso, $this, $CodChapa);
        if(!$Chapa->novo())
            return $Chapa;
        else
            return null;
    }

/**
 * Devolve um iterador com todas as chapas da elei��o.
 * @return Iterador
 */
    public function devolveChapas($Ordem=null) {
        $StrOrdem = null;
        if($Ordem == CHAPAS_PORNUMERO)
            $StrOrdem = ' order by nrchapa ';
        elseif($Ordem == CHAPAS_PORVOTOSDESC)
            $StrOrdem = ' order by nrvotosrecebidos desc ';
        return new Iterador("Chapa",
                            "where codconcurso = :CodConcurso[numero]
                               and codeleicao = :CodEleicao[numero]".$StrOrdem,
                            array("CodConcurso" => $this->get("codconcurso"),
                                  "CodEleicao" => $this->getChave()),
                            array("Concurso" => $this->Concurso, "Eleicao" => $this));
    }
/**
 * Devolve o n�mero total de votos depositados na elei��o atual. Este procedimento
 * serve apenas para fins de auditoria.
 * @return int
 */
    public function devolveNrVotos() {
        $SQL = " select count(*) as Nr from eleicoes.voto
                 where codconcurso = :codconcurso[numero]
                   and codeleicao = :codeleicao[numero] ";
        $Consulta = new Consulta($SQL);
        $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
        $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
        $Consulta->executa(true);
        return $Consulta->campo("Nr");
    }

/**
 * Devolve o n�mero de votos em branco depositados na elei��o atual. Como este
 * procedimento informa parte do resultado da elei��o, ele s� pode ser executado
 * ap�s o t�rmino do per�odo de vota��o.
 * @return int
 */
    public function devolveNrVotosBrancos() {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new EleicaoException("Os votos s� podem ser contados ap�s o t�rmino do concurso", 1);

        $SQL = " select count(*) as Nr from eleicoes.voto
                 where codconcurso = :codconcurso[numero]
                   and codeleicao = :codeleicao[numero]
                   and indvotobranco = 'S' ";
        $Consulta = new Consulta($SQL);
        $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
        $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
        $Consulta->executa(true);
        return $Consulta->campo("Nr");
    }

/**
 * Devolve o n�mero de votos nulos depositados na elei��o atual. Como este
 * procedimento informa parte do resultado da elei��o, ele s� pode ser executado
 * ap�s o t�rmino do per�odo de vota��o.
 * @return int
 */
    public function devolveNrVotosNulos() {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new EleicaoException("Os votos s� podem ser contados ap�s o t�rmino do concurso", 1);
        $SQL = " select count(*) as Nr from eleicoes.voto
                 where codconcurso = :codconcurso[numero]
                   and codeleicao = :codeleicao[numero]
                   and indvotonulo = 'S' ";
        $Consulta = new Consulta($SQL);
        $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
        $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
        $Consulta->executa(true);
        return $Consulta->campo("Nr");
    }

/**
 * Devolve o n�mero de votos em branco depositados em uma determinada urna
 * na elei��o atual. Como este procedimento informa parte do resultado da
 * elei��o, ele s� pode ser executado ap�s o t�rmino do per�odo de vota��o.
 * @return int
 */
    public function devolveNrVotosBrancosPorUrna(UrnaVirtual $Urna) {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new EleicaoException("Os votos s� podem ser contados ap�s o t�rmino do concurso", 1);
        if( ($Urna->get("codconcurso") == $this->get("codconcurso"))
         && ($Urna->get("codeleicao") == $this->get("codeleicao"))) {
            $SQL = " select count(*) as Nr from eleicoes.voto
                     where codconcurso = :codconcurso[numero]
                       and codeleicao = :codeleicao[numero]
                       and indvotobranco = 'S'
                       and codurna = :codurna[numero] ";
            $Consulta = new Consulta($SQL);
            $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
            $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
            $Consulta->setParametros("codurna", $Urna->get("codurna"));
            $Consulta->executa(true);
            return $Consulta->campo("Nr");
        }
        else throw new EleicaoException("Urna inv�lida", 0);
    }

/**
 * Devolve o n�mero de votos nulos depositados em uma determinada urna
 * na elei��o atual. Como este procedimento informa parte do resultado da
 * elei��o, ele s� pode ser executado ap�s o t�rmino do per�odo de vota��o.
 * @return int
 */
    public function devolveNrVotosNulosPorUrna(UrnaVirtual $Urna) {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new EleicaoException("Os votos s� podem ser contados ap�s o t�rmino do concurso", 1);
        if( ($Urna->get("codconcurso") == $this->get("codconcurso"))
         && ($Urna->get("codeleicao") == $this->get("codeleicao"))) {
            $SQL = " select count(*) as Nr from eleicoes.voto
                     where codconcurso = :codconcurso[numero]
                       and codeleicao = :codeleicao[numero]
                       and indvotonulo = 'S'
                       and codurna = :codurna[numero] ";
            $Consulta = new Consulta($SQL);
            $Consulta->setParametros("codconcurso", $this->get("codconcurso"));
            $Consulta->setParametros("codeleicao", $this->get("codeleicao"));
            $Consulta->setParametros("codurna", $Urna->get("codurna"));
            $Consulta->executa(true);
            return $Consulta->campo("Nr");
        }
        else throw new EleicaoException("Urna inv�lida", 0);
    }

/**
 * Efetua o procedimento de contagem de votos para a elei��o. Esse procedimento
 * s� pode ser realizado ap�s o t�rmino do per�odo de vota��o do concurso.
 * @return boolean
 */
    public function realizaContagemVotos() {
        if($this->Concurso->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new EleicaoException("Os votos s� podem ser contados ap�s o t�rmino do concurso", 1);
        $Chapas = $this->devolveChapas();
        foreach($Chapas as $Chapa) {
            $Chapa->set("nrvotosrecebidos", $Chapa->devolveNrVotos());
            $Chapa->salva();
        }
        $this->set("votosbrancos", $this->devolveNrVotosBrancos());
        $this->set("votosnulos", $this->devolveNrVotosNulos());
        $this->salva();
    }

/**
 * Gera uma nova urna para a elei��o atual.
 * @return UrnaVirtual
 */
    public function geraUrna() {
        return new UrnaVirtual($this->Concurso, $this);
    }

/**
 * Devolve uma urna da atual elei��o a partir do c�digo. Caso ela n�o exista,
 * devolve NULL.
 * @param int $CodUrna O c�digo da urna desejado.
 * @return UrnaVirtual
 */
    public function devolveUrna($CodUrna) {
        $Urna = new UrnaVirtual($this->Concurso, $this, $CodUrna);
        if(!$Urna->novo())
            return $Urna;
        else
            return null;
    }

/**
 * Devolve um iterador com todas as urnas cadastradas para a elei��o.
 * @return Iterador
 */
    public function devolveUrnas() {
        return new Iterador("UrnaVirtual",
                            "where codconcurso = :CodConcurso[numero]
                               and codeleicao = :CodEleicao[numero]",
                            array("CodConcurso" => $this->get("codconcurso"),
                                  "CodEleicao" => $this->getChave()),
                            array("Concurso" => $this->Concurso, "Eleicao" => $this));
    }

/**
 * Devolve uma urna da atual elei��o a partir do endere�o IP. Caso ela n�o exista,
 * devolve NULL.
 * @param string $IP O endere�o IP da urna.
 * @return UrnaVirtual
 */
    public function devolveUrnaPorIP($IP) {
        $Consulta = new Consulta('
select * from eleicoes.urnavirtual
where codconcurso = :CodConcurso[numero]
  and codeleicao = :CodEleicao[numero]
  and ip = :IP[texto] ');
        $Consulta->setparametros("CodConcurso", $this->Concurso->Get("codconcurso"));
        $Consulta->setparametros("CodEleicao", $this->Get("codeleicao"));
        $Consulta->setParametros("IP", $IP);
        if($Consulta->executa(true))
            return new UrnaVirtual($Consulta, array("Concurso" => $this->Concurso, "Eleicao" => $this));
        else
            return null;
    }

/**
 * Devolve a chapa a partir do n�mero de vota��o. Caso n�o exista, devolve NULL.
 * @param int $NrChapa O n�mero da chapa desejada.
 * @return Chapa
 */
    public function devolveChapaPorNumero($NrChapa) {
        $SQL = " select * from eleicoes.chapa
                 where codconcurso = :CodConcurso[numero]
                   and codeleicao = :CodEleicao[numero]
                   and nrchapa = :NrChapa[numero] ";
        $Consulta = new consulta($SQL);
        $Consulta->setparametros("CodConcurso", $this->Concurso->Get("codconcurso"));
        $Consulta->setparametros("CodEleicao", $this->Get("codeleicao"));
        $Consulta->setparametros("NrChapa", $NrChapa);
        if($Consulta->executa(true))
          return new Chapa($Consulta, array("Concurso" => $this->Concurso, "Eleicao" => $this));
        else
          return NULL;
    }
/**
 * Gera um novo escopo IP para a elei��o.
 * @return EscopoIP
 */
    public function geraEscopoIP() {
        return new EscopoIP($this->Concurso, $this);
    }

/**
 * Devolve um escopo IP a partir de seu c�digo. Caso n�o exista, devolve NULL.
 * @param int $NrSeqEscopo
 * @return EscopoIP
 */
    public function devolveEscopoIP($NrSeqEscopo) {
        $Escopo = new EscopoIP($this->Concurso, $this, $NrSeqEscopo);
        if(!$Escopo->novo())
            return $Escopo;
        else
            return null;
    }

/**
 * Devolve um iterador com todos os escopos IP da elei��o.
 * @return Iterador
 */
    public function devolveEscoposIP() {
        return new Iterador("EscopoIP",
                            "where codconcurso = :CodConcurso[numero]
                               and codeleicao = :CodEleicao[numero]",
                            array("CodConcurso" => $this->get("codconcurso"),
                                  "CodEleicao" => $this->getChave()),
                            array("Concurso" => $this->Concurso, "Eleicao" => $this));
    }

/**
 * Devolve um escopo IP da elei��o que englobe o endere�o IP dado. Caso haja mais
 * de um escopo que satisfa�a esse requisito, ser� devolvido o escopo mais
 * espec�fico (ex. 192.168.15.* � mais espec�fico do que 192.168.*.*). Caso
 * nenhum escopo IP incorpore o endere�o dado, retorna NULL.
 * @param string $IP
 * @return EscopoIP
 */
    public function devolveEscopoPorPrefixoIP($IP) {
        $Consulta = new Consulta("
select * from eleicoes.dominioip
where codconcurso = :CodConcurso[numero]
  and codeleicao = :CodEleicao[numero]
  and :IP[texto] like prefixoip || '%' ");
        $Consulta->setParametros("CodConcurso", $this->get("codconcurso"));
        $Consulta->setParametros("CodEleicao", $this->get("codeleicao"));
        $Consulta->setParametros("IP", $IP);
        $Consulta->executa();
        $Escopo = null;
        $Octetos = 0;
        while($Consulta->proximo()) {
            if(substr_count($Consulta->campo("prefixoip"), ".") > $Octetos) {
                $Escopo = new EscopoIP($Consulta, array("Concurso" => $this->Concurso, "Eleicao" => $this));
                $Octetos = substr_count(".", $Consulta->campo("prefixoip"));
            }
        }
        return $Escopo;
    }

/**
 * Devolve um escopo IP da elei��o a partir do prefixo exato. Caso n�o exista,
 * devolve NULL.
 * @param string $IP
 * @return EscopoIP
 */
    public function devolveEscopoPorIPExato($IP) {
        $Consulta = new Consulta("
select * from eleicoes.dominioip
where codconcurso = :CodConcurso[numero]
  and codeleicao = :CodEleicao[numero]
  and prefixoip = :IP[texto] ");
        $Consulta->setParametros("CodConcurso", $this->Concurso->getChave());
        $Consulta->setParametros("CodEleicao", $this->getChave());
        $Consulta->setParametros("IP", $IP);
        if($Consulta->executa(true))
            return new EscopoIP($Consulta, array("Concurso" => $this->Concurso, "Eleicao" => $this));
        else
            return null;
    }

/**
 * Devolve um iterador com os eleitores da elei��o. � poss�vel aplicar filtros
 * na lista de eleitores: ELEITOR_JAVOTOU, ELEITOR_NAOVOTOU, ELEITOR_HOMOLOGADO
 * e ELEITOR_NAOHOMOLOGADO. Esses filtros podem ser combinados, passando cada um
 * como um par�metro diferente.
 * @return Iterador
 */
    public function devolveEleitores() {
        $SQL = " where TAB.codconcurso = :CodConcurso[numero]
                   and TAB.codeleicao = :CodEleicao[numero] ";
        foreach(func_get_args() as $Arg)
            switch($Arg) {
                case ELEITOR_JAVOTOU:
                    $SQL .= " and TAB.datahoravoto is not null "; break;
                case ELEITOR_NAOVOTOU:
                    $SQL .= " and TAB.datahoravoto is null "; break;
                case ELEITOR_HOMOLOGADO:
                    $SQL .= " and P.pessoaautenticada = 'S' "; break;
                case ELEITOR_NAOHOMOLOGADO:
                    $SQL .= " and coalesce(P.pessoaautenticada, 'N') = 'N' "; break;
            }
        $SQL .= " order by P.nomepessoa ";
        $Campos = array("CodConcurso" => $this->get("codconcurso"), "CodEleicao" => $this->get("codeleicao"));
        return new Iterador("Eleitor", $SQL, $Campos, array("Concurso" => $this->Concurso, "Eleicao" => $this));
    }

/**
 * Devolve um objeto Eleitor para a PessoaEleicao informada, caso ela seja
 * eleitora da Eleicao atual. Caso contr�rio, devolve NULL.
 * @param PessoaEleicao $Pessoa
 * @return Eleitor
 */
    public function devolveEleitor(PessoaEleicao $Pessoa) {
        $Eleitor = new Eleitor($this->Concurso, $this, $Pessoa);
        if(!$Eleitor->novo())
            return $Eleitor;
        else
            return null;
    }

/**
 * Exclui eleitores da elei��o. � poss�vel aplicar filtros para a exclus�o:
 * ELEITOR_JAVOTOU, ELEITOR_NAOVOTOU, ELEITOR_HOMOLOGADO e ELEITOR_NAOHOMOLOGADO.
 * Esses filtros podem ser combinados, passando cada um como um par�metro diferente.
 * @return boolean
 */
    public function excluiEleitores() {
        $SQL = " delete from eleicoes.eleitor
                 where codconcurso = :CodConcurso[numero]
                   and codeleicao = :CodEleicao[numero] ";
        foreach(func_get_args() as $Arg)
            switch($Arg) {
                case ELEITOR_JAVOTOU:
                    $SQL .= " and datahoravoto is not null "; break;
                case ELEITOR_NAOVOTOU:
                    $SQL .= " and datahoravoto is null "; break;
                case ELEITOR_HOMOLOGADO:
                    $SQL .= " and codpessoaeleicao in (select codpessoaeleicao from eleicoes.pessoaeleicao where pessoaautenticada = 'S') "; break;
                case ELEITOR_NAOHOMOLOGADO:
                    $SQL .= " and codpessoaeleicao in (select codpessoaeleicao from eleicoes.pessoaeleicao where and coalesce(P.pessoaautenticada, 'N') = 'N') "; break;
            }
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("CodConcurso", $this->get("CodConcurso"));
        $Consulta->setParametros("CodEleicao", $this->get("CodEleicao"));
        return $Consulta->executa();
    }

/**
 * Informa se a elei��o j� teve a zer�sima realizada.
 * @return boolean
 */
    public function eleicaoZerada() {
        return (!is_null($this->get("votosbrancos"))) && (!is_null($this->get("votosnulos")));
    }

/**
 * Zera os votos de uma elei��o. Procedimento necess�rio para
 * o in�cio de uma elei��o.
 * @return boolean
 */
    public function realizaZeresima() {
        $Controlador = Controlador::instancia();
        $Pessoa = $Controlador->recuperaPessoaLogada();
        if(!$Pessoa->eGerenteSistema() && ($this->verificaComissao($Pessoa) != COMISSAO_GERENTE))
            throw new EleicaoException("Pessoa sem permiss�o", 0);

        if($this->eleicaoZerada())
            throw new EleicaoException("A elei��o j� foi zerada", 0);

        try {
            $db = DB::instancia();
            $db->iniciaTransacao();
            $Chapas = $this->devolveChapas();
            foreach($Chapas as $Chapa) {
                $Chapa->set("nrvotosrecebidos", 0);
                $Chapa->salva();
            }
            $this->set("votosbrancos", 0);
            $this->set("votosnulos", 0);
            $this->salva();

            $Log = new LogOperacao($this->Concurso);
            $Log->set("codeleicao", $this);
            $Log->set("codpessoaeleicao", $Pessoa);
            $Log->set("dataoperacao", null, "now()");
            $Log->set("ip", $_SERVER['REMOTE_ADDR']);
            $Log->set("descricao", DESCRICAO_ZERESIMA);
            $Log->salva();
            $db->encerraTransacao();
            return true;
        }
        catch(Exception $e) {
            Consulta::desfazTransacao();
            throw $e;
        }
    }

/**
 * Verifica se a pessoa informada � membro da comiss�o ou gerente da elei��o.
 * Retorna false, se a pessoa n�o for nenhum dos dois, ou
 * retorna ELEICAO_GERENTE ou ELEICAO_MEMBROCOMISSAO.
 * @param PessoaEleicao $Pessoa
 * @return int|boolean
 */
    public function verificaComissao(PessoaEleicao $Pessoa) {
        $MembroComissao = new MembroComissao($this->Concurso, $this, $Pessoa);
        if(!$MembroComissao->novo()) {
            if($MembroComissao->get("gerente") == "S")
                return COMISSAO_GERENTE;
            else
                return COMISSAO_MEMBRO;
        }
        else return false;
    }

/**
 * Devolve o objeto Candidato para a PessoaEleicao informada, caso ela seja
 * candidato de alguma chapa da elei��o corrente. Caso contr�rio, devolve NULL.
 * @param PessoaEleicao $Pessoa
 * @return Candidato
 */
    public function devolveCandidato(PessoaEleicao $Pessoa) {
        $SQL = " select * from eleicoes.candidato
                 where codconcurso = :CodConcurso[numero]
                   and codeleicao = :CodEleicao[numero]
                   and codpessoaeleicao = :CodPessoaEleicao[numero] ";
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("CodConcurso", $this->get("CodConcurso"));
        $Consulta->setParametros("CodEleicao", $this->get("CodEleicao"));
        $Consulta->setParametros("CodPessoaEleicao", $Pessoa->get("CodPessoaEleicao"));
        if($Consulta->executa(true))
            return new Candidato($Consulta);
        else
            return null;
    }

/**
 * Retorna todos os gerentes da elei��o.
 * @return Iterador
 */
    public function devolveGerentes() {
        $SQL = " where TAB.codconcurso = :CodConcurso[numero]
                   and TAB.codeleicao = :CodEleicao[numero]
                   and TAB.gerente = 'S' ";
        $Campos = array("CodConcurso" => $this->get("codconcurso"), "CodEleicao" => $this->get("codeleicao"));
        return new Iterador("MembroComissao", $SQL, $Campos);
    }

/**
 * Devolve o objeto MembroComissao para a PessoaEleicao informada, caso ela seja
 * gerente elei��o corrente. Caso contr�rio, devolve NULL.
 * @param PessoaEleicao $Pessoa
 * @return Candidato
 */
    public function devolveGerente(PessoaEleicao $Pessoa) {
        $Gerente = new MembroComissao($this->Concurso, $this, $Pessoa);
        if(!$Gerente->novo() && ($Gerente->get("gerente") == "S"))
            return $Gerente;
        else
            return null;
    }

/**
 * Retorna todos os membros da comiss�o eleitoral da elei��o.
 * @return Iterador
 */
    public function devolveMembrosComissao() {
        $SQL = " where TAB.codconcurso = :CodConcurso[numero]
                   and TAB.codeleicao = :CodEleicao[numero]
                   and TAB.gerente = 'N' ";
        $Campos = array("CodConcurso" => $this->get("codconcurso"), "CodEleicao" => $this->get("codeleicao"));
        return new Iterador("MembroComissao", $SQL, $Campos);
    }

/**
 * Devolve o objeto MembroComissao para a PessoaEleicao informada, caso ela seja
 * membro da comiss�o eleitoral da elei��o corrente. Caso contr�rio, devolve NULL.
 * @param PessoaEleicao $Pessoa
 * @return Candidato
 */
    public function devolveMembroComissao(PessoaEleicao $Pessoa) {
        $Membro = new MembroComissao($this->Concurso, $this, $Pessoa);
        if($Membro->novo() || ($Membro->get("gerente") == "S"))
            return null;
        else
            return $Membro;
    }

/**
 * Cadastra a PessoaEleicao informada como gerente da elei��o.
 * @param PessoaEleicao $Pessoa
 * @return boolean
 */
    public function cadastraGerente(PessoaEleicao $Pessoa) {
        $MembroComissao = new MembroComissao($this->Concurso, $this, $Pessoa);
        if(!$MembroComissao->novo())
            throw new MembroComissaoException("Esta pessoa j� faz parte da comiss�o eleitoral", 1);
        $MembroComissao->set("gerente", "S");
        $MembroComissao->salva();
        return true;
    }

/**
 * Cadastra a PessoaEleicao informada na comiss�o eleitoral da elei��o.
 * @param PessoaEleicao $Pessoa
 * @return boolean
 */
    public function cadastraMembroComissao(PessoaEleicao $Pessoa) {
        $MembroComissao = new MembroComissao($this->Concurso, $this, $Pessoa);
        if(!$MembroComissao->novo())
            throw new MembroComissaoException("Esta pessoa j� faz parte da comiss�o eleitoral", 1);
        $MembroComissao->set("gerente", "N");
        $MembroComissao->salva();
        return true;
    }

/**
 * Cadastra a PessoaEleicao informada como Eleitor da elei��o.
 * @param PessoaEleicao $Pessoa
 * @return boolean
 */
    public function cadastraEleitor(PessoaEleicao $Pessoa) {
        $Eleitor = new Eleitor($this->Concurso, $this, $Pessoa);
        $Eleitor->salva();
        return true;
    }

/**
 * Devolve um iterador com as pessoas n�o homologadas relacionadas � elei��o:
 * membros da comiss�o, gerentes, candidatos e eleitores.
 * @return Iterador
 */
    public function devolvePessoasNaoHomologadas() {
        $SQL = " WHERE TAB.pessoaautenticada = 'N'
                 AND EXISTS (SELECT * from eleicoes.eleitor
                             where codconcurso = :codconcurso[numero]
                               and codeleicao = :codeleicao[numero]
                               and codpessoaeleicao = TAB.codpessoaeleicao)
                  OR EXISTS (SELECT * from eleicoes.candidato
                             where codconcurso = :codconcurso[numero]
                               and codeleicao = :codeleicao[numero]
                               and codpessoaeleicao = TAB.codpessoaeleicao)
                  OR EXISTS (SELECT * from eleicoes.comissaoeleitoral
                             where codconcurso = :codconcurso[numero]
                               and codeleicao = :codeleicao[numero]
                               and codpessoaeleicao = TAB.codpessoaeleicao)
               ORDER BY TAB.nomepessoa ";
        return new Iterador("PessoaEleicao", $SQL, array("codconcurso" => $this->get("codconcurso"), "codeleicao" => $this->get("codeleicao")));
    }

/**
 * Gera um LogOperacao para a Eleicao atual, com a descri��o informada.
 * @param string $Descricao
 * @return LogOperacao
 */
    public function geraLogOperacao($Descricao) {
        $Log = new LogOperacao($this->Concurso);
        $Log->set("codeleicao", $this);
        $Log->set("codpessoaeleicao", Controlador::instancia()->recuperaPessoaLogada());
        $Log->set("descricao", $Descricao);
        $Log->set("dataoperacao", null, "now()");
        $Log->set("ip", $_SERVER['REMOTE_ADDR']);
        $Log->salva();
        return $Log;
    }
}

class EleicaoException extends Exception {
}
?>
