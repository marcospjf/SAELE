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

define("DESCRICAO_ZERESIMA", "Zer�sima");
define("DESCRICAO_CONTAGEM", "Contagem de votos");
define("DESCRICAO_RECONTAGEM", "Recontagem de votos");
define("DESCRICAO_FINALIZACAO", "Concurso Eleitoral finalizado");
define("DESCRICAO_ACESSOVOTACAO", "Acesso � �rea de vota��o");
define("DESCRICAO_INICIOVOTO", "Consist�ncia OK, in�cio da opera��o de voto");
define("DESCRICAO_VOTOEFETUADO", "Voto efetuado com sucesso");
define("DESCRICAO_EMAILS", "E-Mails enviados");

class LogOperacao extends Entidade {
    protected $NomeTabela = "eleicoes.logoperacao";
    protected $VetorChaves = array(
      "codconcurso"         => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "nrseqlogoperacao"    => array(Tipo => numero, Tamanho => 8, Foreign => false)
    );
    protected $VetorCampos = array(
      "codeleicao"          => array(Nome => "Elei��o", Tipo => numero, Tamanho => 4, Obrigatorio => false, Classe => "Eleicao"),
      "codpessoaeleicao"    => array(Nome => "Pessoa", Tipo => numero, Tamanho => 8, Obrigatorio => false, Classe => "PessoaEleicao"),
      "dataoperacao"        => array(Nome => "Data de Opera��o", Tipo => datahora, Obrigatorio => true),
      "ip"                  => array(Nome => "IP", Tipo => texto, Tamanho => 15, Obrigatorio => true),
      "descricao"           => array(Nome => "Descri��o", Tipo => texto, Tamanho => 120, Obrigatorio => true)
    );

    public static function getNumLogsPorDescricao($Descricao, ConcursoEleitoral $Concurso, Eleicao $Eleicao=null) {
        $SQL = " select count(*) as Num from eleicoes.logoperacao
                 where codconcurso = :CodConcurso[numero]
                   and descricao = :Descricao[texto] ";
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("CodConcurso", $Concurso->getChave());
        $Consulta->setParametros("Descricao", $Descricao);
        if(is_null($Eleicao))
            $Consulta->addSQL("and codeleicao is null");
        else {
            $Consulta->addSQL("and codeleicao = :CodEleicao[numero] ");
            $Consulta->setParametros("CodEleicao", $Eleicao->getChave());
        }
        $Consulta->executa(true);
        return (int)$Consulta->campo("Num");
    }

    public static function getLogPorDescricao($Descricao, ConcursoEleitoral $Concurso, Eleicao $Eleicao=null) {
        $SQL = " select * from eleicoes.logoperacao
                 where codconcurso = :CodConcurso[numero]
                   and descricao = :Descricao[texto] ";
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("CodConcurso", $Concurso->getChave());
        $Consulta->setParametros("Descricao", $Descricao);
        if(is_null($Eleicao))
            $Consulta->addSQL("and codeleicao is null");
        else {
            $Consulta->addSQL("and codeleicao = :CodEleicao[numero] ");
            $Consulta->setParametros("CodEleicao", $Eleicao->getChave());
        }
        if($Consulta->executa(true))
            return new LogOperacao($Consulta);
        else
            return null;
    }

    public static function getIteradorLogsPorDescricao($Descricao, ConcursoEleitoral $Concurso, Eleicao $Eleicao=null) {
        $SQL = " where codconcurso = :CodConcurso[numero]
                   and descricao = :Descricao[texto] ";
        if(is_null($Eleicao)) {
            $SQL .= " and codeleicao is null ";
            $CodEleicao = NULL;
        }
        else {
            $SQL .= "and codeleicao = :CodEleicao[numero] ";
            $CodEleicao = $Eleicao->getChave();
        }
        return new Iterador("LogOperacao", $SQL, array("CodConcurso" => $Concurso->getChave(), "CodEleicao" => $CodEleicao, "Descricao" => $Descricao));
    }
}
?>
