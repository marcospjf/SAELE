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

require_once('../CONEXAO/DBPHP.php');
session_start();

$db = DB::instancia();

function MostraCabecalho($TituloPagina) {
?>
<html>
  <head>
    <META HTTP-EQUIV="expires" CONTENT="0">
    <META HTTP-EQUIV="pragma" CONTENT="no-cache">
    <META HTTP-EQUIV="content-type" CONTENT="text/html;charset=iso-8859-1">
    <META HTTP-EQUIV="Content-language" CONTENT="pt-BR">
    <META HTTP-EQUIV="Window-target" CONTENT="PRINCIPAL">
    <META NAME="ROBOTS" CONTENT="NOINDEX,NOFOLLOW">
    <title><?=$TituloPagina?></title>
    <link rel="stylesheet" title="Elei��es" href="../ELEICAO.CSS">
  </head>
<body bgcolor="#ffffff" background="../imagens/ufrgsfundo.jpg">
  <table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
     <tr>
        <td width="170px" bgcolor="white">
           <img src="../imagens/ufrgslogo_2.jpg" border="0">
        </td>
        <td colspan="2" bgcolor="#4682b4" align="center">
           <font face="verdana" color="white" size="4">
           <b>SISTEMA ABERTO DE ELEI&Ccedil;&Otilde;ES ELETR&Ocirc;NICAS</b>
           </font> <br>
           <font face="verdana" color="white" size="2">
           <b><?=htmlentities($TituloPagina)?></b>
           </font>
        </td>
     </tr>
  </table>
<?php
}

function __autoload($Nome) {
    if($Nome != "FPDF")
        if(file_exists("../PUBLIC/".$Nome.".class.php"))
            require_once("../PUBLIC/".$Nome.".class.php");
}


function TiraAcentos($Palavra) {
    $ComAcento='��������������������������������������������������';
    $SemAcento='aaaaaeeeeiiiiooooouuuuyyAAAAAEEEEIIIOOOOOUUUUYcCnN';

    for ($x=0; $x<strlen($Palavra); $x++) {
        $Pos = strrpos($ComAcento, $Palavra[$x]);
        if ($Pos !== false) {
            $Palavra[$x] = $SemAcento[$Pos];
        }
    }

    return $Palavra;
}

class SessaoExpiradaException extends Exception {

}