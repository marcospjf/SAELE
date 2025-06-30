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


require_once("CONEXAO/DBPHP.php");
require_once("PUBLIC/Controlador.class.php");
require_once("PUBLIC/PessoaEleicao.class.php");
require_once("PUBLIC/ConcursoEleitoral.class.php");

session_start();
error_reporting(E_PARSE | E_ERROR);

require_once("Funcoes_Pessoa.php");

switch($_POST['Destino']) {
    case 'Votacao': $Origem = "LoginEleicoes.php?CodConcurso=".$_POST['CodConcurso']; break;
    case 'Administracao': $Origem = "LoginAdm.php"; break;
    case 'Solicitacao': $Origem = "LoginSol.php"; break;
    case 'SolicitacaoEnquete': $Origem = "LoginSol.php?Enquete"; break;
}

if (!AutenticaPessoa($_POST['Usuario'], $_POST['Senha'])) { ?>
	<html><body>
		<script> alert('Usu�rio ou senha inv�lidos.'); location.href = '<?=$Origem?>'; </script>
	</body></html>
    <?php
    exit;
}

$Pessoa = PessoaEleicao::devolvePessoaPorIdentificador($_POST['Usuario']);
if(is_null($Pessoa)) { ?>
	<html><body>
		<script> alert('Usu�rio ou senha inv�lidos.'); location.href = '<?=$Origem?>'; </script>
	</body></html>
    <?php
    exit;
}

if($Pessoa->get("pessoaautenticada") != "S") { ?>
	<html><body>
		<script> alert('Usu�rio n�o validado para o sistema.'); location.href = '<?=$Origem?>'; </script>
	</body></html>
    <?php
    exit;
}
$Controlador = Controlador::instancia($Pessoa);

switch($_POST['Destino']) {
  case 'Votacao':
      try {
          $Controlador->registraConcursoVotacao(new ConcursoEleitoral($_POST['CodConcurso']));
      }
      catch(ControladorException $e) { ?>
          <html><body>
            <script> alert('<?=$e->getMessage()?>'); location.href = '<?=$Origem?>'; </script>
          </body></html>
          <?php
          exit;
      }
      $Controlador->registraOrigem("LoginEleicoes.php?CodConcurso=".$_POST['CodConcurso']);
      header("Location: VOTACAO/ELC_Entrada.php");
      exit;
    case 'Administracao':
        if($Pessoa->eGerenteSistema() || $Pessoa->eMembroComissaoEleitoral()) {
            $Controlador->registraOrigem("LoginAdm.php");
            header("Location: ADMINISTRACAO/ELC_Cadastro_Concursos.php");
        }
        else {
            session_destroy(); ?>
            <html><body>
              <script> alert('Aplica��o n�o dispon�vel.'); location.href = 'LoginAdm.php'; </script>
            </body></html>
            <?php
        }
        exit;
    case 'Solicitacao':
        if($Pessoa->eSolicitante()) {
            $Controlador->registraOrigem("LoginSol.php");
            header("Location: SOLICITACAO/SOL_Solicitacao.php");
        }
        else {
            session_destroy(); ?>
            <html><body>
              <script> alert('Aplica��o n�o dispon�vel.'); location.href = 'LoginSol.php'; </script>
            </body></html>
            <?php
        }
        exit;
    case 'SolicitacaoEnquete':
        if($Pessoa->eSolicitante()) {
            $Controlador->registraOrigem("LoginSol.php?Enquete");
            header("Location: SOLICITACAO/SOL_Solicitacao_Enquete.php");
        }
        else {
            session_destroy(); ?>
            <html><body>
              <script> alert('Aplica��o n�o dispon�vel.'); location.href = 'LoginSol.php?Enquete'; </script>
            </body></html>
            <?php
        }
        exit;
}

