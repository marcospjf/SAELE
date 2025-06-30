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

class Iterador implements Iterator, Countable {
  private $NotEOF = false;
  private $TemRegistro;
  private $Consulta = NULL;
  private $NomeClasse = NULL;
  private $Objeto = NULL;
  private $VarsExtra = NULL;

  public function __construct($NomeClasse, $SQLAdicional=NULL, $ParametrosAdicionais=array(), $VarsExtra=NULL) {
    $this->NomeClasse = $NomeClasse;
    $dummy = new $NomeClasse("dummy");
    $Consulta = $dummy->devolveConsultaIterador();
    $Consulta->addSQL(" ".$SQLAdicional);
    $Consulta->setParametros(todos, $ParametrosAdicionais);

    $this->Consulta = $Consulta;
    $this->VarsExtra = $VarsExtra;
    $this->NotEOF = ($this->Consulta->executa(true));
    if($this->NotEOF) {
      $this->TemRegistro = true;
      $this->Objeto = new $this->NomeClasse($this->Consulta, $this->VarsExtra);
    }
    else
      $this->TemRegistro = false;
  }
  
  function proximo() {
    $Objeto = $this->Objeto;
    $this->next();
    return $Objeto;
  }
  
  function devolveVetor() {
    $VetorRetorno = array();
    if($this->NotEOF) {
      do {
        $VetorRetorno[$this->Objeto->getChave()] = $this->Objeto->getAll();
        $this->next();
      } while($this->NotEOF);
      $this->rewind();
    }
    return $VetorRetorno;
  }
  
  function temRegistro() {
    return $this->TemRegistro;
  }
  
  function rewind() {
    if($this->TemRegistro) {
      $this->NotEOF = $this->Consulta->primeiro();
      $this->Objeto = new $this->NomeClasse($this->Consulta, $this->VarsExtra);
    }
  }
  
  function current() {
    return $this->Objeto;
  }
  
  function key() {
    if(is_null($this->Objeto))
      return 0;
    
    return $this->Objeto->getChave();
  }
  
  function next() {
    $this->NotEOF = ($this->Consulta->proximo());
    if($this->NotEOF)
      $this->Objeto = new $this->NomeClasse($this->Consulta, $this->VarsExtra);
  }
  
  function valid() {
    return $this->NotEOF;
  }
  
  function count() {
    return $this->Consulta->nrlinhas();
  }
}