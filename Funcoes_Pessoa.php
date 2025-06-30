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

error_reporting(E_PARSE | E_ERROR);

/**
 * Esta fun��o � respons�vel por realizar a autentica��o da pessoa. Ela ser�
 * invocada no momento do login, e deve certificar que o usu�rio informado �
 * v�lido e sua senha est� correta. O valor de retorno dever� ser booleano
 * igual a TRUE caso a autentica��o seja bem sucedida e FALSE em caso contr�rio.
 * @param string $Usuario
 * @param string $Senha
 * @return boolean
 */
function AutenticaPessoa($Usuario, $Senha) {
	return true;
}

/**
 * Esta fun��o realiza a homologa��o dos dados de uma pessoa no sistema. Ela
 * receber� todos os dados da pessoa armazenados no sistema (Nome, CPF, Registro
 * geral, E-Mail, etc.) em um vetor, e dever� verificar se esses dados est�o
 * corretos de acordo com a base institucional. O valor de retorno ser� um
 * string, e dever� ser NULL quando a homologa��o for bem sucedida; em caso
 * contr�rio, a fun��o dever� retornar a mensagem de erro que ser� exibida
 * para o gerente de sistemas no momento da homologa��o. N�o h� restri��es
 * para a mensagem, por�m recomenda-se que ela seja expl�cita e auto-explicativa.
 * O vetor $DadosPessoa tem, por default, os seguintes �ndices:
 *  codpessoaeleicao: o c�digo de uso interno do sistema
 *  identificacaousuario: um c�digo de identifica��o definido pela institui��o
 *  nomepessoa: o nome da pessoa, como registrado no sistema
 *  cpf: o cpf, armazenado como n�mero - isto �, sem zeros � esquerda, pontos e tra�os
 *  nrregistrogeral: o n�mero da carteira de identidade
 *  localtrabalho: o nome do local de trabalho do usu�rio; pode ser vazio
 *  pessoaautenticada: um caracter S ou N, que diz que a pessoa est� homologada
 *  gerentesistema: um caracter S ou N que indica se a pessoa � gerente do sistema
 *  solicitante: um caracter S ou N que indica se a pessoa pode solicitar elei��es
 * @param array $DadosPessoa
 * @return string
 */
function HomologaPessoa($DadosPessoa) {
	return null;
} ?>