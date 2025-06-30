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
                       // P�gina com a lista dos eleitores que j� votaram
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

$Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
$Eleicao = $Concurso->devolveEleicao($_GET['CodEleicao']);
$Urna = (isset($_GET['CodUrna']) ? $Eleicao->devolveUrna($_GET['CodUrna']) : NULL);

$Comissao = $Eleicao->verificaComissao($Pessoa);

MostraCabecalho("Lista de Votantes");
if(!$Pessoa->eGerenteSistema() && ($Comissao == false)) { ?>
    <div class="Erro">
        <p><strong>Erro:</strong> Permiss�o negada.</p>

        <p><a href="ELC_Cadastro_Concursos.php">Voltar</a></p>
    </div>
    </body>
    </html>
    <?php
    exit;
}

if ($Concurso->estadoConcurso() != CONCURSO_ENCERRADO) { ?>
    <div class="Erro">
        <p><strong>Erro:</strong> Lista de votantes n&atilde;o dispon&iacute;vel no momento.</p>

        <p><a href="ELC_Cadastro_Concursos.php">Voltar</a></p>
    </div>
    </body>
    </html>
    <?php
    exit;
}
?>

<br />

<table width="80%" border="1" cellspacing="0" cellpadding="0" class="tabela" align="center">
  <tr bgcolor="#d3d3d3">
      <td align="center">         
        <font class="a2">Lista de Votantes:</font>
      </td>
    </tr>
    <tr>
      <td>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabela">
          <?php
          if(is_null($Urna))
              $Eleitores = $Eleicao->devolveEleitores(ELEITOR_JAVOTOU);
          else
              $Eleitores = $Urna->devolveVotantes();
          $i = 1;
          foreach($Eleitores as $Eleitor) {
              $PessoaEleitor = $Eleitor->getObj("PessoaEleicao");
              ?>
              <tr class="Linha<?=$i?>">
                <td width="10%">&nbsp;<?=$PessoaEleitor->get("identificacaousuario")?></td>
              <td><?=$PessoaEleitor->get("nomepessoa")?></td>
              <?php
              $i = ($i % 2) + 1;
          } ?>
          </table>
        </td>
    </tr>
</table>
<div style="font-family:Verdana; font-size:9pt; margin-left: 10%;">
Total: <?=count($Eleitores)?>
</div>

<div align="center">
    <a href="ELC_Relatorio_Eleitores.php?CodConcurso=<?=$Concurso->getChave()?>&amp;CodEleicao=<?=$Eleicao->getChave().(is_null($Urna) ? null : '&amp;CodUrna='.$Urna->getChave())?>">Relat&oacute;rio de Eleitores</a>

    <br /><br />
    <?php
    if(isset($Urna))
        echo '<a href="ELC_Apuracao_Urna.php?CodConcurso='.$Concurso->getChave().'&amp;CodEleicao='.$Eleicao->getChave().'&amp;CodUrna='.$Urna->getChave().'">Voltar</a>';
    else
        echo '<a href="ELC_Apuracao.php?CodConcurso='.$Concurso->getChave().'&amp;CodEleicao='.$Eleicao->getChave().'">Voltar</a>'; ?>
</div>
</body>
</html>