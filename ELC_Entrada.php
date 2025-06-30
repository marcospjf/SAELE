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

require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();
$Concurso = $Controlador->recuperaConcursoVotacao();

$Eleicoes = $Concurso->devolveEleicoesDisponiveisEleitor($Pessoa);
if($Eleicoes->temRegistro()) {
    $Eleicao = $Eleicoes->proximo();
    $Controlador->registraEleicaoVotacao($Eleicao);
    header("Location: ELC_Urna.php");
    exit;
}
else {
    MostraCabecalho("Sistema de Elei��es Eletr�nicas"); ?>
    <script>
        alert('N�o h� elei��es dispon�veis neste concurso para vota��o.');
        location.href = '../ELC_Logout.php';
    </script>
    </body>
    </html>
    <?php
}