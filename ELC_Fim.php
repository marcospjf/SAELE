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
MostraCabecalho("Sistema de Elei��es Eletr�nicas");

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();
$Concurso = $Controlador->recuperaConcursoVotacao();

$Controlador->removeEleicaoVotacao();

if($Concurso->get("indbarradoporip") == "S")
    echo '<embed src="../Som/conffim.wav" hidden="true" autostart="true">';

$Eleicoes = $Concurso->devolveEleicoesDisponiveisEleitor($Pessoa);
if($Eleicoes->temRegistro()) {
    $Controlador->registraEleicaoVotacao($Eleicoes->proximo());
    ?>
    <h1>Seu voto foi efetuado com sucesso.</h1>

    <h2>Clique no bot�o abaixo para prosseguir para a <?=$Concurso->retornaString(STR_ELEICAO)?> seguinte.</h2>

    <p style="text-align: center;"><input type="button" value="Prosseguir" onclick="javascript: location.href = 'ELC_Urna.php';" /></p>

    </body>
    </html>
    <?php
}
else {
    ?>
    <script>window.setTimeout("location.href = '../ELC_Logout.php';", 3000);</script>
    <div class="Fim">FIM</div>

    </body>
    </html>
    <?php
}