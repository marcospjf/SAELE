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
                       // P�gina para apura��o dos votos de determinada Elei��o
require_once('../CABECALHO.PHP');

$Controlador = Controlador::instancia();
$Pessoa = $Controlador->recuperaPessoaLogada();

$Concurso = new ConcursoEleitoral($_GET['CodConcurso']);
$Eleicao = $Concurso->devolveEleicao($_GET['CodEleicao']);

$Comissao = $Eleicao->verificaComissao($Pessoa);

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
if( ($Concurso->estadoConcurso() < CONCURSO_ENCERRADO) || ($Concurso->get("situacaoconcurso") != SITUACAOCONCURSO_APURADO)
 || (!$Pessoa->eGerenteSistema() && $Comissao != COMISSAO_GERENTE)) { ?>
    <div class="Erro">
        <p><strong>Erro:</strong> Esta elei��o est� indispon�vel para visualiza��o dos votos.</p>

        <p><a href="ELC_Cadastro_Concursos.php">Voltar</a></p>
    </div>
    </body>
    </html>
    <?php
    exit;
}

if(isset($_GET['CodUrna']) && (trim($_GET['CodUrna']) != "")) {
    $Urna = $Eleicao->devolveUrna($_GET['CodUrna']);
    if(is_null($Urna)) { ?>
        <div class="Erro">
            <p><strong>Erro:</strong> Urna inv�lida.</p>

            <p><a href="ELC_Cadastro_Concursos.php">Voltar</a></p>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
  MostraCabecalho('Apura��o da Urna "'.$Urna->get("descricao").'"');
  ?>
  <script language="javascript">
    function MostraCandidatos(Chapa) {
      el = document.getElementById('D'+Chapa);
      el.style.display = (el.style.display == 'block') ? 'none' : 'block';
    }
  </script>

  <table width="100%" cellspacing="0" cellpadding="0">
     <tr class="LinhaHR">
       <td colspan="4">
         <hr>
       </td>
     </tr>
     <tr class="linha1">
       <td width="20%" align="right">
         <font size="2" face="verdana"><b>Concurso Eleitoral:&nbsp;</b></font>
       </td>
       <td width="80%" align="left">
         <font size="2" face="verdana"><?=$Concurso->get("descricao")?></font>
       </td>
     </tr>   
     <tr class="linha2"> 
       <td width="20%" align="right">
         <font size="2" face="verdana"><b>Elei��o:&nbsp;</b></font>
       </td>
       <td width="80%" align="left">
         <font size="2" face="verdana"><?=$Eleicao->get("descricao")?></font>
       </td>
     </tr>   
     <tr class="linha1">
       <td width="20%" align="right">
         <font size="2" face="verdana"><b>Urna:&nbsp;</b></font>
       </td>
       <td width="80%" align="left">
         <font size="2" face="verdana"><?=$Urna->get("descricao")?></font>
       </td>
     </tr>

     <tr class="LinhaHR">
       <td colspan="4">
         <hr>
       </td>
     </tr>
  </table>

  <br />
  <div class="Centro">
  <table border="1" width="85%" cellspacing="0" cellpadding="0" align="center">
    <tr bgcolor="d3d3d3">
        <td width="70%">
            <font size="2" face="verdana">
                <b>Chapa</b>
            </font>
        </td>
        <td width="30%">
            <font size="2" face="verdana">
                <b>Votos recebidos</b>
            </font>
        </td>
    </tr>
    <?php
    $Chapas = $Eleicao->devolveChapas();
    $i = 1;
    foreach($Chapas as $CodChapa => $Chapa) {
        $Candidatos = $Chapa->devolveCandidatos(); ?>
        <tr class="<?=$classe?>">
            <td>
              <font size="2" face="verdana">
                &nbsp;Chapa <?=$Chapa->get("nrchapa")?> - <?=$Chapa->get("descricao")?> <a href="javascript: MostraCandidatos(<?=$CodChapa?>);">[Mostrar Candidatos]</a>
                </font>
                <div ID="D<?=$CodChapa?>" style="display: block;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabela">
                <?php
                foreach($Candidatos as $Candidato) {
                    $Pessoa = $Candidato->getObj("PessoaEleicao");
                    $Participacao = $Candidato->getObj("Participacao"); ?>
                    <tr class="linha<?=$i?>">
                        <td>
                          &nbsp;&nbsp;&nbsp;<b><?=$Participacao->get("descricaoparticipacao")?></b> - <?=$Pessoa->get("nomepessoa")?>
                        </td>
                    </tr>
                <?php
                } ?>
            </table>
            </div>
            </td>
            <td valign="top">
                &nbsp;<?=$Chapa->devolveNrVotosPorUrna($Urna)?>
            </td>
        </tr>
        <?php
        $i = ($i % 2) + 1;
    } ?>
        <tr bgcolor="d3d3d3">
            <td align="right">
                <font size="2" face="verdana">
                    <b>Votos em Branco: &nbsp; </b>
                </font>
            </td>
            <td>
                <font size="2" face="verdana">
                    <b>&nbsp;<?=$Eleicao->devolveNrVotosBrancosPorUrna($Urna)?></b>
                </font>
            </td>
        </tr>
        <tr bgcolor="d3d3d3">
            <td align="right">
                <font size="2" face="verdana">
                    <b>Votos Nulos: &nbsp; </b>
                </font>
            </td>
            <td>
                <font size="2" face="verdana">
                    <b>&nbsp;<?=$Eleicao->devolveNrVotosNulosPorUrna($Urna)?></b>
                </font>
            </td>
        </tr> 
  </table>

  <br />

  <table width="85%" border="1" cellspacing="0" cellpadding="0" align="center">
     <tr bgcolor="d3d3d3">
         <td align="right" width="70%">
             <font size="2" face="verdana">
                 <b>Total de Votantes na Urna: &nbsp; </b>
             </font>
         </td>
         <td>
             <font size="2" face="verdana">
                 <b>&nbsp;<?=count($Urna->devolveVotantes())?></b>
             </font>
         </td>
     </tr>
  </table>

  <a href="javascript: void(0);" onclick="javascript: janela = window.open('ELC_Relatorio_Votos.php?CodConcurso=<?=$Concurso->getChave()?>&CodEleicao=<?=$Eleicao->getChave()?>&CodUrna=<?=$Urna->getChave()?>&ord=nr', 'hhg', 'top=5, left=10');">Relat&oacute;rio de Votos (por n&uacute;mero de chapa)</a><br />
  <a href="javascript: void(0);" onclick="javascript: janela = window.open('ELC_Relatorio_Votos.php?CodConcurso=<?=$Concurso->getChave()?>&CodEleicao=<?=$Eleicao->getChave()?>&CodUrna=<?=$Urna->getChave()?>&ord=votos', 'hhg', 'top=5, left=10');">Relat&oacute;rio de Votos (por ordem de coloca&ccedil;&atilde;o)</a>

  <br /><br />

  <a href="ELC_Lista_Eleitores.php?CodConcurso=<?=$Concurso->getChave()?>&CodEleicao=<?=$Eleicao->getChave()?>&CodUrna=<?=$Urna->getChave()?>">Lista de Votantes</a><br /><br />
  
  <a href="ELC_Apuracao_Urna.php?CodConcurso=<?=$Concurso->getChave()?>&CodEleicao=<?=$Eleicao->getChave()?>">Voltar</a>
  </div>

  <?php
}
else {
    $Urnas = $Eleicao->devolveUrnas();
    MostraCabecalho("Mapa de Urnas");
    ?>
    <table width="100%" cellspacing="0" cellpadding="0">
     <tr class="LinhaHR">
       <td colspan="4">
         <hr>
       </td>
     </tr>
     <tr class="linha1">
       <td width="20%" align="right">
         <font size="2" face="verdana"><b>Concurso Eleitoral:&nbsp;</b></font>
       </td>
       <td width="80%" align="left">
         <font size="2" face="verdana"><?=$Concurso->get("descricao")?></font>
       </td>
     </tr>   
     <tr class="linha2"> 
       <td width="20%" align="right">
         <font size="2" face="verdana"><b>Elei��o:&nbsp;</b></font>
       </td>
       <td width="80%" align="left">
         <font size="2" face="verdana"><?=$Eleicao->get("descricao")?></font>
       </td>
     </tr>

     <tr class="LinhaHR">
       <td colspan="4">
         <hr>
       </td>
     </tr>
    </table>

  <br />
  <div align="center">
  <font size="2" face="verdana">
  Selecione a urna:<br /><br />
  <?php
  foreach($Urnas as $CodUrna => $Urna)
    echo '<a href="ELC_Apuracao_Urna.php?CodConcurso='.$_GET['CodConcurso'].'&CodEleicao='.$_GET['CodEleicao'].'&CodUrna='.$CodUrna.'">'
        .$Urna->get("descricao").'</a><br />'; ?>
  <br /><br />
  <a href="ELC_Apuracao.php?CodConcurso=<?=$_GET['CodConcurso']?>&amp;CodEleicao=<?=$_GET['CodEleicao']?>">Voltar</a>
  </font>
  </div>
<?php } ?>
</body>
</html>