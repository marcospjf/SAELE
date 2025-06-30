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

if(!isset($_SESSION['Valido'])) {
    MostraCabecalho("Solicita��o de Concurso Eleitoral");
    ?>
    <div class="Erro">
        <p><strong>Solicita��o inv�lida</strong>.</p>

        <p><input type="button" value="Voltar" onclick="javascript: location.href = 'SOL_Solicitacao.php';" /></p>
    </div>
    <?php
    exit;
}

$Pessoa = Controlador::instancia()->recuperaPessoaLogada();

$Campos = $_SESSION['Campos'];

$Solicitacao = new SolicitacaoConcurso();
$Solicitacao->set("nomeconcurso", $Campos['NomeConcurso']);
$Solicitacao->set("datainicioconcurso", $Campos['DataInicio']." ".$Campos['HoraInicio']);
$Solicitacao->set("datafimconcurso", $Campos['DataFim']." ".$Campos['HoraFim']);
$Solicitacao->set("nomepessoacontato", $Campos['Contato']);
$Solicitacao->set("ramalcontato", $Campos['RamalContato']);
$Solicitacao->set("emailcontato", $Campos['EMail']);
$Solicitacao->set("indbarradoporip", $Campos['TipoEleicao']);
$Solicitacao->set("modalidadeconcurso", $_SESSION['ModalidadeConcurso']);
$Solicitacao->set("datasolicitacao", null, "now()");
$Solicitacao->set("usuariosolicitacao", $Pessoa);
$Solicitacao->salva();

foreach($Campos['Eleicao'] as $Indice => $Descr) {
    $Eleicao = $Solicitacao->geraEleicaoSolicitacao();
    $Eleicao->set("descricao", $Descr);
    $Eleicao->salva();
}

if($_SESSION['ModalidadeConcurso'] == "C") {
    $TituloPagina = "Solicita��o de ConcursoEleitoral";

    $Titulo = "Solicita��o de Elei��o Eletr�nica";
    $Mensagem = "Foi encaminhada uma solicita��o de elei��o, por ".$Pessoa->get("nomepessoa").".";
}
else {
    $TituloPagina = "Solicita��o de Enquete";

    $Titulo = "Solicita��o de Enquete";
    $Mensagem = "Foi encaminhada uma solicita��o de enquete, por ".$Pessoa->get("nomepessoa").".";
}
unset($_SESSION['Campos']);
unset($_SESSION['Valido']);
unset($_SESSION['ModalidadeConcurso']);
MostraCabecalho($TituloPagina); ?>
<br />
<div align="center">
  <font size="2" face="verdana">Solicita��o enviada com sucesso.</font><br /><br />

  <input type="button" value="Fechar" onclick="javascript: location.href='../ELC_Logout.php';" />
</div>

</body>
</html>