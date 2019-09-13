<?php

namespace Application\Modules\Parecer\Service;

class GerenciarParecer implements \MinC\Servico\IServicoRestZend
{
    /**
     * @var \Zend_Controller_Request_Abstract $request
     */
    private $request;

    /**
     * @var \Zend_Controller_Response_Abstract $response
     */
    private $response;

    private $idUsuario;
    private $idOrgao;
    private $idGrupo;
    private $idAgente;
    private $auth;

    const ID_ATO_ADMINISTRATIVO = \Assinatura_Model_DbTable_TbAssinatura::TIPO_ATO_ANALISE_INICIAL;


    function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;

        $this->auth = \Zend_Auth::getInstance()->getIdentity();
        $this->idUsuario = $this->auth->usu_codigo;

        $grupoAtivo = new \Zend_Session_Namespace('GrupoAtivo');
        $this->idOrgao = $grupoAtivo->codOrgao;
        $this->idGrupo = $grupoAtivo->codGrupo;

        $tbUsuario = new \Autenticacao_Model_DbTable_Usuario();
        $usuario = $tbUsuario->getIdUsuario($this->idUsuario);
        $this->idAgente = $usuario['idagente'];

        if (empty($this->idAgente)) {
            throw new \Exception("Agente n&atilde;o cadastrado");
        }
    }

    public function obterProdutos()
    {
        $tbDistribuirParecer = new \Parecer_Model_DbTable_TbDistribuirParecer();
        $filtro = $this->request->getParam('filtro');
        $where = ['a.idOrgao = ?' => $this->idOrgao];
        $produtos = $tbDistribuirParecer->obterPainelGerenciarParecer($filtro, $where);
        return \TratarArray::utf8EncodeArray($produtos);
    }

    public function inserirDistribuicaoProduto()
    {
        $params = $this->request->getParams();

        if (empty($params['idDistribuirParecer'])
            || empty($params['idPronac'])
            || empty($params['idProduto'])) {
            throw new \Exception("Dados obrigatórios não informados");
        }

        $whereDistribuicaoAtual = [];
        $whereDistribuicaoAtual["idDistribuirParecer = ?"] = $params['idDistribuirParecer'];
        $tbDistribuirParecer = new \Parecer_Model_DbTable_TbDistribuirParecer();
        $distribuicao = $tbDistribuirParecer->findBy($whereDistribuicaoAtual);
        $resposta = $this->distribuirProduto($params, $distribuicao);

        if ($resposta
            && !empty($distribuicao['idAgenteParecerista'])
            && $distribuicao['TipoAnalise'] == \Parecer_Model_TbDistribuirParecer::TIPO_ANALISE_PRODUTO_COMPLETO) {
            $this->desabilitarDocumentoAssinatura($params['idPronac']);
            $this->alterarSituacaoDoProjeto($params['idPronac']);
        }

        return $distribuicao;
    }

    public function inserirDistribuicaoTodosProdutosDoProjeto()
    {
        $params = $this->request->getParams();

        $whereDistribuicao = [
            "idPronac = ?" => $params['idPronac'],
            "idOrgao = ?" => $this->idOrgao,
            "stEstado = ?" => \Parecer_Model_TbDistribuirParecer::ST_ESTADO_ATIVO
        ];

        $tbDistribuirParecer = new \Parecer_Model_DbTable_TbDistribuirParecer();
        $distribuicoes = $tbDistribuirParecer->findAll($whereDistribuicao);

        $resposta = false;
        foreach ($distribuicoes as $distribuicao) {
            $resposta = $this->distribuirProduto($params, $distribuicao);
        }

        if ($resposta) {
            $this->desabilitarDocumentoAssinatura($params['idPronac']);
            $this->alterarSituacaoDoProjeto($params['idPronac']);
        }

        return $distribuicoes;
    }

    private function distribuirProduto($dados, $distribuicaoAtual)
    {
        if ($dados['tipoAcao'] === 'encaminhar' && empty($dados['idOrgaoDestino'])) {
            throw new \Exception("Selecione o org&atilde;o destino");
        }
//
//        $tbCredenciamentoParecerista = new \Agente_Model_DbTable_TbCredenciamentoParecerista();
//        if ($dados['tipoAcao'] === 'distribuir'
//            && !$tbCredenciamentoParecerista->isPareceristaCredenciado(
//                $dados['idAgenteParecerista'],
//                $dados['idAreaProduto'],
//                $dados['idSegmentoProduto'])
//        ) {
//            throw new \Exception("Parecerista n&atilde;o credenciado na &aacute;rea e segmento do Produto");
//        }

        $observacao = \TratarString::tratarTextoRicoParaUTF8($dados['observacao']);

        if (strlen($observacao) < 11) {
            throw new \Exception("O campo observa&ccedil;&atilde;o deve ter no m&iacute;nimo 11 caracteres");
        }

        $distribuicao = array_merge($distribuicaoAtual, [
            'Observacao' => $observacao,
            'idUsuario' => $this->idUsuario,
        ]);

        if (!empty($dados['idAgenteParecerista'])) {
            $distribuicao['idAgenteParecerista'] = $dados['idAgenteParecerista'];
        }

        if (!empty($dados['idOrgaoDestino'])) {
            $distribuicao['idOrgao'] = $dados['idOrgaoDestino'];
        }

        $tbDistribuirParecerMapper = new \Parecer_Model_TbDistribuirParecerMapper();
        if ($dados['tipoAcao'] === 'encaminhar') {
            return $tbDistribuirParecerMapper->encaminharProdutoParaVinculada($distribuicao);
        }

        return $tbDistribuirParecerMapper->distribuirProdutoParaParecerista($distribuicao);
    }

    public function solicitarReanaliseParecerista()
    {
        $params = $this->request->getParams();

        $distribuicao = $this->tratarDadosRequisicao();

        $tbDistribuirParecerMapper = new \Parecer_Model_TbDistribuirParecerMapper();
        $resposta = $tbDistribuirParecerMapper->solicitarReanaliseParecerista($distribuicao);
        if ($resposta && $distribuicao['stPrincipal'] == 1) {
            $this->desabilitarDocumentoAssinatura($params['idPronac']);
            $this->alterarSituacaoDoProjeto($params['idPronac']);
        }

        return $resposta;
    }

    public function devolverProdutoParaSecult()
    {
        $params = $this->request->getParams();

        $distribuicao = $this->tratarDadosRequisicao();

        $tbDistribuirParecerMapper = new \Parecer_Model_TbDistribuirParecerMapper();
        $resposta = $tbDistribuirParecerMapper->devolverProdutoParaSecult($distribuicao);

        if ($resposta) {
            $providenciaTomada = 'An&aacute;lise t&eacute;cnica conclu&iacute;da';

            $projetos = new \Projetos();
            $projetos->alterarSituacao(
                $params['idPronac'],
                null,
                \Projeto_Model_Situacao::PARECER_TECNICO_EMITIDO,
                $providenciaTomada
            );
        }
        return $resposta;
    }

    private function alterarSituacaoDoProjeto($idPronac)
    {
        $providenciaTomada = 'Encaminhado ao perito para an&aacute;lise t&eacute;cnica e emiss&atilde;o de parecer.';

        $projetos = new \Projetos();
        $projetos->alterarSituacao(
            $idPronac,
            null,
            \Projeto_Model_Situacao::ENCAMINHADO_PARA_ANALISE_TECNICA,
            $providenciaTomada
        );
    }

    private function desabilitarDocumentoAssinatura($idPronac)
    {
        $idTipoDoAtoAdministrativo = \Assinatura_Model_DbTable_TbAssinatura::TIPO_ATO_ANALISE_INICIAL;

        $objTbDocumentoAssinatura = new \Assinatura_Model_DbTable_TbDocumentoAssinatura();
        $idDocumentoAssinatura = $objTbDocumentoAssinatura->getIdDocumentoAssinatura($idPronac, $idTipoDoAtoAdministrativo);
        if ($idDocumentoAssinatura) {
            $objDocumentoAssinatura = new \Assinatura_Model_DbTable_TbDocumentoAssinatura();
            $dadosDocumentoAssinatura = ["stEstado" => \Assinatura_Model_TbDocumentoAssinatura::ST_ESTADO_DOCUMENTO_INATIVO];
            $whereDocumentoAssinatura = "idDocumentoAssinatura = $idDocumentoAssinatura";

            $objDocumentoAssinatura->update($dadosDocumentoAssinatura, $whereDocumentoAssinatura);
        }
    }

    public function salvarValidacaoParecer()
    {
        $params = $this->request->getParams();

        if (empty($params['idDistribuirParecer'])) {
            throw new \Exception("Identificador é obrigatório");
        }

        $dados = [
            "siAnalise" => \Parecer_Model_TbDistribuirParecer::SI_ANALISE_VALIDADO,
            "FecharAnalise" => \Parecer_Model_TbDistribuirParecer::FECHAR_ANALISE_FECHADA,
            'DtRetorno' => \MinC_Db_Expr::date(),
        ];
        $whereDocumentoAssinatura = "idDistribuirParecer = " . $params['idDistribuirParecer'];

        $tbDistribuirParecer = new \Parecer_Model_DbTable_TbDistribuirParecer();
        return $tbDistribuirParecer->update($dados, $whereDocumentoAssinatura);
    }

    private function tratarDadosRequisicao()
    {
        $params = $this->request->getParams();

        if (empty($params['idDistribuirParecer'])
            || empty($params['idPronac'])
            || empty($params['idProduto'])
        ) {
            throw new \Exception("Dados obrigatórios não informados");
        }

        $observacao = \TratarString::tratarTextoRicoParaUTF8($params['observacao']);

        if (strlen($observacao) < 11) {
            throw new \Exception("O campo observa&ccedil;&atilde;o deve ter no m&iacute;nimo 11 caracteres");
        }

        $whereDistribuicaoAtual = [];
        $whereDistribuicaoAtual["idDistribuirParecer = ?"] = $params['idDistribuirParecer'];
        $tbDistribuirParecer = new \Parecer_Model_DbTable_TbDistribuirParecer();
        $distribuicao = $tbDistribuirParecer->findBy($whereDistribuicaoAtual);

        return array_merge($distribuicao, [
            'Observacao' => $observacao,
            'idUsuario' => $this->idUsuario,
        ]);
    }

    public function solicitarAnaliseComplementarVinculada()
    {
        $params = $this->request->getParams();

        if (empty($params['idOrgaoDestino'])) {
            throw new \Exception("Identificador da unidade destino &eacute; obrigat&oacute;rio");
        }

        $distribuicao = $this->tratarDadosRequisicao();
        $distribuicao['idOrgao'] = $params['idOrgaoDestino'];
        $distribuicao['TipoAnalise'] = \Parecer_Model_TbDistribuirParecer::TIPO_ANALISE_CUSTO_PRODUTO;

        $tbDistribuirParecerMapper = new \Parecer_Model_TbDistribuirParecerMapper();
        return $tbDistribuirParecerMapper->encaminharProdutoParaVinculada($distribuicao);
    }
}
