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
                       // Gera��o de Concurso Eleitoral a partir de Solicita��o - GRAVA��O DOS DADOS
require_once('../CABECALHO.PHP');
require_once('../Funcoes_Pessoa.php');

$Pessoa = Controlador::instancia()->recuperaPessoaLogada();
if(!$Pessoa->eGerenteSistema()) {
    echo "<html><body>\n";
    echo "<div align=\"center\">\n";
    echo "<br><font size=\"2\" face=\"verdana\">Erro! O usu&aacute;rio n&atilde;o tem permiss&atilde;o para acessar esta p&aacute;gina.<br><br>\n";
    echo "<a href=\"javascript: history.back();\">Voltar</a></font>\n";
    echo "</div>";
    echo "</body></html>";
    exit;
}

$NrSeqSolicitacaoConcurso = $_POST['NrSeqSolicitacaoConcurso'];
$Solicitacao = new SolicitacaoConcurso($NrSeqSolicitacaoConcurso);
if(!$Solicitacao->valido())  { ?>
    <html><body>
        <div align="center">
        <font size="2" face="verdana">Erro! Solicita��o inv�lida.<br /><br />
        <a href="javascript: history.back();">Voltar</a></font>
        </div>
    </body></html>
    <?php
    exit;
}

$MSG = NULL;

$Valores = $_POST;

$Valores['DataHoraInicio'] = $Valores['DataInicio']." ".$Valores['HoraInicio'];
$Valores['DataHoraFim'] = $Valores['DataFim']." ".$Valores['HoraFim'];
$PessoaSolicitacao = new PessoaEleicao($Solicitacao->get("usuariosolicitacao"));

$db = db::instancia();
$db->iniciaTransacao();

if(!is_null($Solicitacao->get("codconcurso"))) { // ATUALIZA CONCURSO J� GERADO
    $Valores['CodConcurso'] = $Solicitacao->get("codconcurso");
    $Concurso = new ConcursoEleitoral($Valores['CodConcurso']);
    $Concurso->set("descricao", $Valores['DescConcurso']);
    $Concurso->set("datahorainicio", $Valores['DataHoraInicio']);
    $Concurso->set("datahorafim", $Valores['DataHoraFim']);
    $Concurso->set("indbarradoporip", $Valores['IndBarradoPorIP']);
    $Concurso->set("indhabilitacontagem", $Valores['HabilitaContagem']);
    $GerouConcurso = $Concurso->salva();
}
else { // GERA CONCURSO
    $Concurso = new ConcursoEleitoral();
    $Concurso->set("descricao", $Valores['DescConcurso']);
    $Concurso->set("datahorainicio", $Valores['DataHoraInicio']);
    $Concurso->set("datahorafim", $Valores['DataHoraFim']);
    $Concurso->set("indbarradoporip", $Valores['IndBarradoPorIP']);
    $Concurso->set("indhabilitacontagem", $Valores['HabilitaContagem']);
    $Concurso->set("modalidadeconcurso", $Solicitacao->get("modalidadeconcurso"));
    $GerouConcurso = $Concurso->salva();
    $Valores['CodConcurso'] = $Concurso->getChave();

    foreach($Valores['Eleicao'] as $NomeEleicao) {
        if(trim($NomeEleicao) != "") {
            $Eleicao = $Concurso->geraEleicao();
            $Eleicao->set("descricao", $NomeEleicao);
            $Eleicao->set("nrpossibilidades", 1);
            $Eleicao->set("nrdigitoschapa", 2);
            $Eleicao->salva();

            $Eleicao->cadastraMembroComissao($PessoaSolicitacao);
        }
    }
}

if(is_null($Concurso->get("situacaoconcurso")) && isset($Valores['Importar']) && ($Valores['Importar'] == "S")) {
    $Concurso->set("situacaoconcurso", SITUACAOCONCURSO_CARREGADO);
    $Concurso->salva();

    $Eleicoes = $Concurso->devolveEleicoes();
	foreach($Eleicoes as $CodEleicao => $Eleicao) {
        $Lista = file('../ARQUIVO/arq_'.$NrSeqSolicitacaoConcurso.'_'.$CodEleicao.'.txt');
		if($Lista === false) {
            $MSG .= "N�o foi poss�vel abrir o arquivo da elei��o ".$CodEleicao.". Certifique-se de que o arquivo 'arq_".$NrSeqSolicitacaoConcurso."_".$CodEleicao.".txt' encontra-se no diret�rio ARQUIVO.<br />";
		}
		else {
            foreach($Lista as $Linha) {
                $Linha = str_replace("\n", NULL, str_replace("\r", NULL, $Linha));
				$Dados = explode(";", $Linha);
				$Valores['IdentificacaoUsuario'] = $Dados[0];
				$Valores['NomePessoa'] = TiraAcentos(trim($Dados[1]));
				$Valores['NrRegistroGeral'] = trim($Dados[2]);
				$Valores['CPF'] = $Dados[3];
				$Valores['LocalTrabalho'] = trim($Dados[4]);
				$Valores['EMail'] = trim($Dados[5]);

                $Pessoa = new PessoaEleicao($Valores['CodPessoaEleicao']);
                if(!is_null(PessoaEleicao::devolvePessoaPorIdentificador($Valores['IdentificacaoUsuario']))) {
                    if($Pessoa->get("nomepessoa") != $Valores['NomePessoa']) {
                        $MSG .= "O participante ".$Valores['NomePessoa'].", de c�digo ".$Valores['CodPessoaEleicao'].", n�o foi inclu�da pois o c�digo j� est� sendo utilizado por ".$Pessoa->get("nomepessoa").".<br />";
                        $Insere = false;
                    }
                    else $Insere = true;
				}
				else {
                    $Insere = true;
                    $Pessoa = new PessoaEleicao();
                    $Pessoa->set("identificacaousuario", $Valores['IdentificacaoUsuario']);
                    $Pessoa->set("nomepessoa", $Valores['NomePessoa']);
                    $Pessoa->set("nrregistrogeral", $Valores['NrRegistroGeral']);
                    $Pessoa->set("cpf", $Valores['CPF']);
                    $Pessoa->set("localtrabalho", $Valores['LocalTrabalho']);
                    $Pessoa->set("email", $Valores['EMail']);
                    $Pessoa->salva();
				}
				if($Insere) {
                    $Eleicao->cadastraEleitor($Pessoa);
				}
			}
            $PessoasNaoAutenticadas = $Eleicao->devolveEleitores(ELEITOR_NAOHOMOLOGADO);
            foreach($PessoasNaoAutenticadas as $CodPessoaEleicao => $Eleitor) {
                $Pessoa = $Eleitor->getObj("PessoaEleicao");
                $Retorno = HomologaPessoa($Pessoa->getAll());
                if(is_null($Retorno)) {
                    $Pessoa->set("pessoaautenticada", "S");
                    $Pessoa->salva();
                }
                else {
                    $erro = true;
                    $MSG .= "A pessoa ".$Pessoa->get("nomepessoa")>" n�o foi homologada. Resposta: ".$Retorno."<br />";
                }
			}
		}
	}
}

if($Valores['Finalizar'] == "S") { // FINALIZA SOLICITA��O
    $Solicitacao->set("dataatendimento", null, "now()");
    $Solicitacao->set("codconcurso", $Concurso);
    $Solicitacao->salva();
    
    // E-MAIL PARA ADMINISTRADORES
	// CONFORME O CASO, PODE-SE ADQUIRIR O DESTINAT�RIO DA MENSAGEM DO BANCO DE DADOS, OU PODE SER UM ENDERE�O FIXO
    $Destinatario = "";
    $Gerentes = new Iterador("PessoaEleicao", "where email is not null and gerentesistema = 'S'");
    foreach($Gerentes as $Gerente)
      $Destinatario .= $Gerente->get("email").",";
    $Destinatario = rtrim($Destinatario, ",");
		
    $Titulo = "Gera��o de Concurso Eleitoral";
    $Header = "From: Elei��es\r\n"
             ."MIME-Version: 1.0\r\n"
             ."Content-Type: text/html; charset=iso-8859-1\r\n"
             ."Content-Transfer-Encoding: 8bit\r\n";
    $Mensagem = "<p>O Concurso Eleitoral <b>".$Concurso->getChave()." - ".$Concurso->get("descricao")."</b> foi gerado.</p>"
               ."<p>Elei&ccedil;&otilde;es geradas:</p>";
    $StrEleicoes = NULL;
    $Eleicoes = $Concurso->devolveEleicoes();
    foreach($Eleicoes as $CodEleicao => $Eleicao)
      $StrEleicoes .= " &nbsp; ".$CodEleicao." - ".$Eleicao->get("descricao")."<br />";
    $Mensagem .= $StrEleicoes;

    mail($Destinatario, $Titulo, $Mensagem, $Header);

    // E-MAIL PARA SOLICITANTE
		// O C�DIGO ABAIXO � USADO PARA ANEXAR UM ARQUIVO DE IMAGEM AO E-MAIL, A SER USADO NO LINK PARA AS ELEI��ES
/*    $NomeArq = "";
    $Arquivo = fopen($NomeArq, "rb");
    $Conteudo = stream_get_contents($Arquivo);
    $Conteudo = chunk_split(base64_encode($Conteudo));
    fclose($Arquivo); */

    $Destinatario = $Solicitacao->get("emailcontato");
    $Separador = md5(time());
    $Header = "From: Elei��es\r\n"
             ."MIME-Version: 1.0\r\n"
             ."Content-Type: text/html; charset=iso-8859-1\r\n"
             ."Content-Transfer-Encoding: 8bit\r\n";

    $Mensagem = "<p>Seu Concurso Eleitoral foi gerado com as seguintes caracter&iacute;sticas:</p>"
               ."<p>Elei&ccedil;&otilde;es geradas:</p>".$StrEleicoes
               ."<p>O link para acesso &eacute; <a href=\"http://alguma.coisa.com\">http://alguma.coisa.com</a></p>"
               ."<p>O &iacute;cone para a elei&ccedil;&atilde;o segue em anexo.</p>\r\n";
    mail($Destinatario, $Titulo, $Mensagem, $Header);

    $MSG .= "Solicita�ao finalizada com sucesso.";
}
else { // N�O FINALIZA
    $Solicitacao->set("dataatendimento", null);
    $Solicitacao->set("codconcurso", $Concurso);
    $Solicitacao->salva();
    $MSG .= "<b>O Concurso foi gerado com sucesso.</b>";
}

$db->encerraTransacao();
MostraCabecalho("Solicita&ccedil;&atilde;o de Concursos Eleitorais");
?>
    <div align="center">
    <br />
    <font size="2" face="verdana"><?=$MSG?></font>
    <br /><br />
    <input type="button" value="Voltar" onClick="javascript: location.href='ELC_Solicitacoes_Concursos.php';" />
    </div>
</body></html>