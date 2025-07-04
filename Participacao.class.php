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

final class Participacao extends Entidade {
    protected $NomeTabela = "eleicoes.participacao";
    protected $VetorChaves = array(
      "codparticipacao" => array(Tipo => numero, Tamanho => 2, Foreign => false)
    );
    protected $VetorCampos = array(
      "descricaoparticipacao"   => array(Nome => "Descri��o", Tipo => texto, Tamanho => 60, Obrigatorio => true),
      "indvalidocandidato"      => array(Nome => "V�lido para candidato", Tipo => texto, Tamanho => 1, Obrigatorio => true, Valores => array('S', 'N')),
    );
}