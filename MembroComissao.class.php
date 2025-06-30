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
 * Esta classe representa um membro da comiss�o eleitoral de uma elei��o.
 * Al�m de um membro comum, uma pessoa pode ser gerente da elei��o, e essa distin��o
 * � indicada pela coluna "gerente".
 */
final class MembroComissao extends Entidade {
    protected $NomeTabela = "eleicoes.comissaoeleitoral";
    protected $VetorChaves = array(
      "codconcurso"     => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "ConcursoEleitoral"),
      "codeleicao"      => array(Tipo => numero, Tamanho => 4, Foreign => true, Classe => "Eleicao"),
      "codpessoaeleicao"=> array(Tipo => numero, Tamanho => 8, Foreign => true, Classe => "PessoaEleicao")
    );
    protected $VetorCampos = array(
      "gerente"     => array(Nome => "Gerente", Tipo => texto, Tamanho => 1, Obrigatorio => true, Valores => array("S", "N"))
    );

    protected $ClassesAnexadas = array(
        "PessoaEleicao" => array(
            "Tabela" => "eleicoes.pessoaeleicao",
            "Chaves" => array("codpessoaeleicao" => "codpessoaeleicao"),
            "Inner" => true)
    );

    private $Concurso, $Eleicao, $Pessoa;

    public function __construct($Arg1, $Arg2=null, $Arg3=null) {
        parent::__construct($Arg1, $Arg2, $Arg3);
        if($Arg1 instanceof ConcursoEleitoral) {
            $this->Concurso = $Arg1;
            $this->Eleicao = $Arg2;
            $this->Pessoa = $Arg3;
        }
    }

    public function salva() {
        if($this->novo()) {
            if($this->Eleicao->devolveCandidato($this->Pessoa) !== null)
                throw new MembroComissaoException("Esta pessoa � candidato desta Elei��o", 2);
        }
        parent::salva();
    }
}

class MembroComissaoException extends Exception {
    
}
?>