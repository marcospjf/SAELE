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
                       // P�gina principal da Administra��o, para cadastro de Concursos e Elei��es
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();
if(!$Pessoa->eGerenteSistema()) {
  echo "<html><body>\n";
  echo "<div align=\"center\">\n";
  echo "<br><font size=\"2\" face=\"verdana\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.<br><br>\n";
  echo "<a href=\"javascript: history.back();\">Voltar</a></font>\n";
  echo "</div>";
  echo "</body></html>";
  exit;
}

MostraCabecalho("Checklist do Concurso Eleitoral");

$Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
$Checklist = $Concurso->geraChecklist();
 ?>
<br />
<table width="75%" align="center" border="0" cellspacing="0" cellpadding="0">
    <?php
    $i = 1;
    foreach($Checklist as $ItemChecklist) { ?>
    <tr class="Linha<?=$i?>">
        <?=($ItemChecklist['OK']
                ? '<td width="25%" style="text-align: center; color: black; font-weight: bold;">OK</td>'
                : '<td width="25%" style="text-align: center; color: red; font-weight: bold;">PENDENTE</td>')?>
        <td><?=$ItemChecklist['Mensagem']?></td>
    </tr>
        <?php
        $i = ($i % 2) + 1;
    } ?>
</table>

<p style="text-align:center;">
  <input type="button" value="Voltar" onclick="javascript: location.href = 'ELC_Cadastro_Concursos.php';" />
</p>

</body>
</html>