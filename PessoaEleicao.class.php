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

require_once("Entidade.class.php");

class PessoaEleicao extends Entidade {
    protected $NomeTabela = "eleicoes.pessoaeleicao";
    protected $VetorChaves = array(
      "codpessoaeleicao"    => array(Tipo => numero, Tamanho => 4, Foreign => false)
    );
    protected $VetorCampos = array(
      "cpf"                 => array(Nome => "CPF", Tipo => cpf, Tamanho => 11, Obrigatorio => true),
      "nomepessoa"          => array(Nome => "Nome", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "nrregistrogeral"     => array(Nome => "Registro Geral", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "email"               => array(Nome => "E-Mail", Tipo => texto, Tamanho => 120, Obrigatorio => true),
      "localtrabalho"       => array(Nome => "Local de Trabalho", Tipo => texto, Tamanho => 120, Obrigatorio => false),
      "pessoaautenticada"   => array(Nome => "Autenticada", Tipo => texto, Tamanho => 1, Obrigatorio => false),
      "gerentesistema"      => array(Nome => "Gerente do Sistema", Tipo => texto, Tamanho => 1, Obrigatorio => false),
      "solicitante"         => array(Nome => "Solicitante", Tipo => texto, Tamanho => 1, Obrigatorio => false),
      "identificacaousuario"=> array(Nome => "Identifica��o do Usu�rio", Tipo => texto, Tamanho => 30, Obrigatorio => true),
    );

/**
 * Informa se a pessoa j� foi homologada.
 * @return boolean
 */
    public function homologada() {
        return ($this->get("pessoaautenticada") == "S");
    }

/**
 * Informa se a pessoa � gerente do sistema.
 * @return boolean
 */
    public function eGerenteSistema() {
        return $this->get("gerentesistema") == "S";
    }

/**
 * Informa se a pessoa pode solicitar concursos e enquetes.
 * @return boolean
 */
    public function eSolicitante() {
        return $this->get("solicitante") == "S";
    }

/**
 * Informa se a pessoa � membro de comiss�o eleitoral de alguma elei��o.
 * @return boolean
 */
    public function eMembroComissaoEleitoral() {
        $SQL = " select * from eleicoes.comissaoeleitoral
                 where codpessoaeleicao = :CodPessoaEleicao[numero] ";
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("CodPessoaEleicao", $this->getChave());
        return $Consulta->executa(true);
    }

    public static function devolvePessoaPorIdentificador($Identificador) {
        $SQL = " select * from eleicoes.pessoaeleicao where identificacaousuario = :Identificador[texto] ";
        $Consulta = new consulta($SQL);
        $Consulta->setParametros("Identificador", $Identificador);
        if($Consulta->executa(true))
            return new PessoaEleicao($Consulta);
        else
            return null;
    }
}