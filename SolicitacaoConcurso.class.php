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

require_once("ConcursoEleitoral.class.php");
final class SolicitacaoConcurso extends Entidade {
    static private $Strings = array(
        'E' => array(
            STR_CONCURSOELEITORAL   => "Concurso Eleitoral",
            STR_ELEICAO             => "Elei��o",
            STR_CHAPA               => "Chapa",
            STR_GERENTE             => "Gerente",
            STR_ELEITOR             => "Eleitor",
            STR_CONCURSOSELEITORAIS => "Concursos Eleitorais",
            STR_ELEICOES            => "Elei��es",
            STR_CHAPAS              => "Chapas",
            STR_GERENTES            => "Gerentes",
            STR_ELEITORES           => "Eleitores",
        ),
        'Q' => array(
            STR_CONCURSOELEITORAL   => "Enquete",
            STR_ELEICAO             => "Quest�o",
            STR_CHAPA               => "Resposta",
            STR_GERENTE             => "Respons�vel",
            STR_ELEITOR             => "Participante",
            STR_CONCURSOSELEITORAIS => "Enquetes",
            STR_ELEICOES            => "Quest�es",
            STR_CHAPAS              => "Respostas",
            STR_GERENTES            => "Respons�veis",
            STR_ELEITORES           => "Participantes",
        )
    );
    
    protected $NomeTabela = "eleicoes.solicitacaoconcurso";
    protected $VetorChaves = array(
        "nrseqsolicitacaoconcurso"  => array(Tipo => numero, Tamanho => 6, Foreign => false)
    );
    protected $VetorCampos = array(
        "nomeconcurso"          => array(Nome => "Nome do Concurso", Tipo => texto, Tamanho => 120, Obrigatorio => true),
        "datainicioconcurso"    => array(Nome => "Data de In�cio", Tipo => datahora, Obrigatorio => true),
        "datafimconcurso"       => array(Nome => "Data de Fim", Tipo => datahora, Obrigatorio => true),
        "nomepessoacontato"     => array(Nome => "Pessoa para Contato", Tipo => texto, Tamanho => 72, Obrigatorio => false),
        "ramalcontato"          => array(Nome => "Ramal para Contato", Tipo => texto, Tamanho => 5, Obrigatorio => false),
        "emailcontato"          => array(Nome => "E-Mail para Contato", Tipo => texto, Tamanho => 50, Obrigatorio => false),
        "comissaoeleitoral"     => array(Nome => "Comiss�o Eleitoral", Tipo => texto, Tamanho => 255, Obrigatorio => false),
        "gerentesconcurso"      => array(Nome => "Gerentes do Concurso", Tipo => texto, Tamanho => 255, Obrigatorio => false),
        "indbarradoporip"       => array(Nome => "Barrado por IP", Tipo => texto, Tamanho => 1, Obrigatorio => true, Valores => array("S", "E", "N")),
        "perfileleitores"       => array(Nome => "Perfil dos Eleitores", Tipo => texto, Tamanho => 1, Obrigatorio => true),
        "datasolicitacao"       => array(Nome => "Data de Solicita��o", Tipo => datahora, Obrigatorio => false),
        "usuariosolicitacao"    => array(Nome => "Usu�rio de Solicita��o", Tipo => numero, Tamanho => 6, Obrigatorio => true, Classe => "PessoaEleicao"),
        "dataatendimento"       => array(Nome => "Data de Atendimento", Tipo => datahora, Obrigatorio => false),
        "codconcurso"           => array(Nome => "C�digo do Concurso", Tipo => numero, Tamanho => 4, Obrigatorio => false, Classe => "ConcursoEleitoral"),
        "codorgaoescopo"        => array(Nome => "�rg�o de Escopo", Tipo => numero, Tamanho => 5, Obrigatorio => false),
        "observacao"            => array(Nome => "Observa��o", Tipo => texto, Obrigatorio => false),
        "modalidadeconcurso"    => array(Nome => "Modalidade do Concurso", Tipo => texto, Tamanho => 1, Obrigatorio => true, Valores => array("E", "Q"))
    );

    public function retornaString($String) {
        if(isset(self::$Strings['E'][$String]))
            return self::$Strings[$this->get("modalidadeconcurso")][$String];
        else
            return null;
    }

    public function geraEleicaoSolicitacao() {
        return new EleicaoSolicitacao($this);
    }

    public function devolveEleicoesSolicitacao() {
        return new Iterador("EleicaoSolicitacao", " where nrseqsolicitacaoconcurso = :NrSeqSolicitacaoConcurso[numero]", array("NrSeqSolicitacaoConcurso" => $this->getChave()));
    }
}