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

define("CONCURSO_NAOINICIADO", 0);
define("CONCURSO_INICIADO", 1);
define("CONCURSO_ENCERRADO", 2);

define("SITUACAOCONCURSO_CRIADO", 0);
define("SITUACAOCONCURSO_CARREGADO", 1);
define("SITUACAOCONCURSO_HOMOLOGADO", 2);
define("SITUACAOCONCURSO_APURADO", 3);
define("SITUACAOCONCURSO_ARQUIVADO", 4);

define("MODALIDADE_ELEICAO", 'E');
define("MODALIDADE_ENQUETE", 'Q');

define("STR_CONCURSOELEITORAL", 0);
define("STR_ELEICAO", 1);
define("STR_CHAPA", 2);
define("STR_GERENTE", 3);
define("STR_ELEITOR", 4);
define("STR_VOTO", 5);
define("STR_CONCURSOSELEITORAIS", 100);
define("STR_ELEICOES", 101);
define("STR_CHAPAS", 102);
define("STR_GERENTES", 103);
define("STR_ELEITORES", 104);
define("STR_VOTOS", 105);

require_once("Eleicao.class.php");
require_once("LogOperacao.class.php");

/**
 * A classe ConcursoEleitoral representa um grupo de elei��es que ocorre em
 * um determinado per�odo de tempo. Utilizando o exemplo das elei��es federais,
 * a cada quatro anos, ocorre um CONCURSO ELEITORAL, dentro do qual se realizam
 * v�rias ELEI��ES: elei��o para deputado estadual e federal, senador, governador
 * e presidente.
 */
final class ConcursoEleitoral extends Entidade {
    static private $ListaParticipacoes = array();
    static private $SituacaoConcursoTextual = array(
        0 => "Criado",
        1 => "Carregado",
        2 => "Homologado",
        3 => "Apurado",
        4 => "Arquivado"
    );

    static private $Strings = array(
        'E' => array(
            STR_CONCURSOELEITORAL   => "Concurso Eleitoral",
            STR_ELEICAO             => "Elei��o",
            STR_CHAPA               => "Chapa",
            STR_GERENTE             => "Gerente",
            STR_ELEITOR             => "Eleitor",
            STR_VOTO                => "Voto",
            STR_CONCURSOSELEITORAIS => "Concursos Eleitorais",
            STR_ELEICOES            => "Elei��es",
            STR_CHAPAS              => "Chapas",
            STR_GERENTES            => "Gerentes",
            STR_ELEITORES           => "Eleitores",
            STR_VOTOS               => "Votos",
        ),
        'Q' => array(
            STR_CONCURSOELEITORAL   => "Enquete",
            STR_ELEICAO             => "Quest�o",
            STR_CHAPA               => "Op��o",
            STR_GERENTE             => "Respons�vel",
            STR_ELEITOR             => "Participante",
            STR_VOTO                => "Resposta",
            STR_CONCURSOSELEITORAIS => "Enquete",
            STR_ELEICOES            => "Quest�es",
            STR_CHAPAS              => "Op��es",
            STR_GERENTES            => "Respons�veis",
            STR_ELEITORES           => "Participantes",
            STR_VOTOS               => "Respostas",
        )
    );
    protected $NomeTabela = "eleicoes.concursoeleitoral";
    protected $VetorChaves = array(
      "codconcurso" => array(Tipo => "numero", Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "descricao"           => array(Nome => "Descri��o", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "datahorainicio"      => array(Nome => "In�cio", Tipo => datahora, Obrigatorio => true),
      "datahorafim"         => array(Nome => "Fim", Tipo => datahora, Obrigatorio => true),
      "indbarradoporip"     => array(Nome => "Barrado por IP", Tipo => texto, Obrigatorio => true, Valores => array('S', 'E', 'N')),
      "indhabilitacontagem" => array(Nome => "Contagem para gerentes", Tipo => texto, Obrigatorio => true, Valores => array('S', 'N')),
      "modalidadeconcurso"  => array(Nome => "Modalidade", Tipo => texto, Obrigatorio => true, Valores => array('E', 'Q')),
      "situacaoconcurso"    => array(Nome => "Situa��o", Tipo => numero, Obrigatorio => true, Valores => array(0, 1, 2, 3, 4))
    );
    private $EstadoConcurso = NULL;
/**
 * Cria uma nova Eleicao para o atual concurso.
 * @return Eleicao
 */
    public function geraEleicao() {
        return new Eleicao($this);
    }

/**
 * Devolve uma Eleicao do atual concurso. Caso ela n�o exista, devolve NULL.
 * @param int $CodEleicao O c�digo da elei��o desejada.
 * @return Eleicao
 */
    public function devolveEleicao($CodEleicao) {
        $Eleicao = new Eleicao($this, $CodEleicao);
        if($Eleicao->valido()) {
            return $Eleicao;
        }
        else {
            unset($Eleicao);
            return NULL;
        }
    }

/**
 * Devolve um iterador com todas as elei��es do concurso.
 * @return Iterador
 */
    public function devolveEleicoes() {
        return new Iterador("Eleicao", " where codconcurso = :CodConcurso[numero] order by codeleicao ",
                            array("CodConcurso" => $this->Get("codconcurso")), $this);
    }

/**
 * Devolve um iterador com todas as elei��es dispon�veis para vota��o para uma
 * determinada pessoa.
 * @param PessoaEleicao $Pessoa
 * @return Iterador
 */
    public function devolveEleicoesDisponiveisEleitor(PessoaEleicao $Pessoa) {
        if($this->abertoParaVotacao()) {
            $SQL = "
where TAB.codconcurso = :CodConcurso[numero]
  and exists (select * from eleicoes.eleitor
              where codconcurso = TAB.codconcurso
                and codeleicao = TAB.codeleicao
                and codpessoaeleicao = :CodPessoaEleicao[numero]
                and datahoravoto is null)
  and exists (select * from eleicoes.logoperacao
              where codconcurso = TAB.codconcurso
                and codeleicao = TAB.codeleicao
                and descricao = '".DESCRICAO_ZERESIMA."') ";
            $Campos['CodPessoaEleicao'] = $Pessoa->getChave();
            switch($this->get("indbarradoporip")) {
                case 'S':
                    $SQL .= "
  and exists (select * from eleicoes.urnavirtual
              where codconcurso = TAB.codconcurso
                and codeleicao = TAB.codeleicao
                and ip = :IP[texto]
                and indativa = 'S') ";
                    $Campos['IP'] = $_SERVER['REMOTE_ADDR'];
                    break;
                case 'E':
                    $SQL .= "
  and exists (select * from eleicoes.dominioip
              where codconcurso = TAB.codconcurso
                and codeleicao = TAB.codeleicao
                and :IP[texto] like prefixoip || '%'
                and indativa = 'S') ";
                    $Campos['IP'] = $_SERVER['REMOTE_ADDR'];
                    break;
            }
            $Campos['CodConcurso'] = $this->getChave();
            return new Iterador("Eleicao", $SQL, $Campos, $this);
        }
        else throw new ConcursoEleitoralException("Concurso n�o iniciado", 0);
    }

/**
 * Informa se o ConcursoEleitoral est� aberto para edi��o dos dados.
 * @return boolean
 */
    public function abertoParaAlteracoes() {
        return $this->estadoConcurso() == CONCURSO_NAOINICIADO;
    }
/**
 * Informa se o ConcursoEleitoral est� aberto para vota��o.
 * @return boolean
 */
    public function abertoParaVotacao() {
        return ($this->estadoConcurso() == CONCURSO_INICIADO)
            && ($this->get("situacaoconcurso") == SITUACAOCONCURSO_HOMOLOGADO);
    }
/**
 * Informa se a modalidade do ConcursoEleitoral admite candidatos.
 * @return boolean
 */
    public function admiteCandidatos() {
        return ($this->get("modalidadeconcurso") == "E");
    }

/**
 * Informa o estado do ConcursoEleitoral em rela��o � data atual:
 * CONCURSO_NAOINICIADO, CONCURSO_INICIADO ou CONCURSO_ENCERRADO.
 * @return int
 */
    public function estadoConcurso() {
        if(is_null($this->EstadoConcurso)) {
            $SQL = " select
                       case when datahorainicio > now() then ".CONCURSO_NAOINICIADO."
                            when datahorafim > now() then ".CONCURSO_INICIADO."
                            else ".CONCURSO_ENCERRADO." end as situacao
                     from eleicoes.concursoeleitoral
                     where codconcurso = :CodConcurso[numero] ";
            $Consulta = new consulta($SQL);
            $Consulta->setparametros("CodConcurso", $this->GetChave());
            $Consulta->executa(true);
            $this->EstadoConcurso = (int)$Consulta->campo("situacao");
        }
        return $this->EstadoConcurso;
    }

/**
 * Informa a situa��o atual do ConcursoEleitoral em forma textual.
 * @return string Descri��o da situa��o atual
 */
    public function situacaoConcursoTextual() {
        return self::$SituacaoConcursoTextual[$this->get("situacaoconcurso")];
    }
/**
 * Gera um vetor com o checklist do ConcursoEleitoral; ou seja, uma s�rie de verifica��es
 * feitas sobre o concurso para verificar o andamento do cadastro.
 * @return array
 */
    public function geraChecklist() {
        $Checklist = array(
            0 => array(
                "SQL" => " select case when not exists
                                (select * from eleicoes.eleicao E
                                 where codconcurso = :CodConcurso[numero]
                                   and not exists
                                    (select * from eleicoes.chapa where codconcurso = E.codconcurso))
                            then 'S' else 'N' end as Check ",
                "OK" => "J� existem chapas cadastradas em todas as elei��es deste concurso.",
                "Erro" => "Existem elei��es neste concurso que n�o possuem chapas."
                ),
            1 => array(
                "SQL" => " select case when not exists
                                (select * from eleicoes.eleicao e
                                 where e.codconcurso = :CodConcurso[numero]
                                   and not exists
                                    (select * from eleicoes.eleitor
                                     where codconcurso = e.codconcurso
                                       and codeleicao = e.codeleicao))
                            then 'S' else 'N' end as Check ",
                "OK" => "J� existem eleitores cadastrados em todas as elei��es deste concurso.",
                "Erro" => "Existem elei��es neste concurso que n�o possuem eleitores."
                ),
            2 => array(
                "SQL" => " select case when
                                (select situacaoconcurso from eleicoes.concursoeleitoral
                                 where codconcurso = :CodConcurso[numero]) >= ".SITUACAOCONCURSO_HOMOLOGADO."
                            then 'S' else 'N' end as Check ",
                "OK" => "Este concurso eleitoral j� foi homologado.",
                "Erro" => "A homologa��o final deste concurso ainda n�o foi realizada."
                ),
            3 => array(
                "SQL" => " select case when exists
                                (select 1 from eleicoes.logoperacao
                                  where codconcurso = :CodConcurso[numero]
                                    and descricao = '".DESCRICAO_ZERESIMA."')
                            then 'S' else 'N' end as Check ",
                "OK" => "A zer�sima deste concurso j� foi realizada.",
                "Erro" => "A zer�sima deste concurso ainda n�o foi realizada."
                )
        );
        $ChecklistRetorno = array();
        foreach($Checklist as $i => $ItemChecklist) {
            $Consulta = new Consulta($ItemChecklist['SQL']);
            $Consulta->setParametros("CodConcurso", $this->getChave());
            $Consulta->executa(true);
            if($Consulta->campo("Check") == 'S')
                $ChecklistRetorno[$i]['Mensagem'] = $ItemChecklist['OK'];
            else
                $ChecklistRetorno[$i]['Mensagem'] = $ItemChecklist['Erro'];
            $ChecklistRetorno[$i]['OK'] = ($Consulta->campo("Check") == 'S');
        }
        return $ChecklistRetorno;
    }

    public function retornaString($String) {
        if(isset(self::$Strings['E'][$String]))
            return self::$Strings[$this->get("modalidadeconcurso")][$String];
        else
            return null;
    }

/**
 * Efetua o procedimento de contagem de votos para as elei��es do ConcursoEleitoral.
 * Esse procedimento s� pode ser realizado ap�s o t�rmino do per�odo de vota��o,
 * e habilita a apura��o dos votos. Ele somente pode ser feito pelo gerente
 * do sistema.
 * @return boolean
 */
    public function realizaContagemVotos() {
        $Controlador = Controlador::instancia();
        $Pessoa = $Controlador->recuperaPessoaLogada();

        if(!$Pessoa->eGerenteSistema())
            throw new ConcursoEleitoralException("Opera��o exclusiva para gerentes", 1);
        if($this->estadoConcurso() != CONCURSO_ENCERRADO)
            throw new ConcursoEleitoralException("Os votos s� podem ser contados ap�s o t�rmino do concurso", 2);
        $db = DB::instancia();
        $db->iniciaTransacao();
        $Eleicoes = $this->devolveEleicoes();
        foreach($Eleicoes as $Eleicao) {
            $Eleicao->realizaContagemVotos();
        }

        if(is_null(LogOperacao::getLogPorDescricao(DESCRICAO_CONTAGEM, $this)))
            $this->geraLogOperacao(DESCRICAO_CONTAGEM);
        else
            $this->geraLogOperacao(DESCRICAO_RECONTAGEM);

        $this->set("situacaoconcurso", SITUACAOCONCURSO_APURADO);
        $this->salva();

        $db->encerraTransacao();
        return true;
    }

/**
 * Encerra o concurso eleitoral, alterado sua sita��o para Arquivado. Esse
 * procedimento pode ser feito somente ap�s a contagem de votos, e apenas pelo
 * gerente do concurso.
 * @return boolean
 */
    public function finalizaConcurso() {
        $Controlador = Controlador::instancia();
        $Pessoa = $Controlador->recuperaPessoaLogada();
        if(!$Pessoa->eGerenteSistema())
            throw new ConcursoEleitoralException("Usu�rio sem permiss�o", 0);
        if($this->get("situacaoconcurso") < SITUACAOCONCURSO_APURADO)
            throw new ConcursoEleitoralException("O Concurso ainda n�o foi apurado", 0);
        $db = DB::instancia();
        $db->iniciaTransacao();
        $this->set("situacaoconcurso", SITUACAOCONCURSO_ARQUIVADO);
        $this->salva();

        $this->geraLogOperacao(DESCRICAO_FINALIZACAO);

        $db->encerraTransacao();
        return true;
    }

/**
 * Gera um LogOperacao para o ConcursoEleitoral atual, com a descri��o informada.
 * @param string $Descricao
 * @return LogOperacao
 */
    public function geraLogOperacao($Descricao) {
        $Log = new LogOperacao($this);
        $Log->set("codpessoaeleicao", Controlador::instancia()->recuperaPessoaLogada());
        $Log->set("descricao", $Descricao);
        $Log->set("dataoperacao", null, "now()");
        $Log->set("ip", $_SERVER['REMOTE_ADDR']);
        $Log->salva();
        return $Log;
    }

    public static function devolveParticipacoes() {
        if(empty(self::$ListaParticipacoes)) {
            $SQL = " select * from eleicoes.participacao ";
            $Consulta = new Consulta($SQL);
            $Consulta->executa();
            while($Consulta->proximo())
                self::$ListaParticipacoes[$Consulta->campo("codparticipacao")] = $Consulta->campo("descricaoparticipacao");
        }
        return self::$ListaParticipacoes;
    }
}

class ConcursoEleitoralException extends Exception {
    
}