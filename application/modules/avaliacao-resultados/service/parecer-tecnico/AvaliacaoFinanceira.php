<?php

namespace Application\Modules\AvaliacaoResultados\Service\ParecerTecnico;


class AvaliacaoFinanceira
{
    /**
     * @var \Zend_Controller_Request_Abstract $request
     */
    private $request;

    /**
     * @var \Zend_Controller_Response_Abstract $response
     */
    private $response;


    function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function buscarDadosProjeto()
    {
        $vwResultadoDaAvaliacaoFinanceira = new \AvaliacaoResultados_Model_DbTable_vwResultadoDaAvaliacaoFinanceira();
        $dadosAvaliacaoFinanceira = $vwResultadoDaAvaliacaoFinanceira->buscarConsolidacaoComprovantes($this->request->idPronac);
        $dadosAvaliacaoFinanceira = $dadosAvaliacaoFinanceira->toArray();

        $projeto = new \Projetos();
        $dadosProjeto = $projeto->buscar([
            'idPronac = ?' => $this->request->idPronac
        ]);
        $dadosProjeto = $dadosProjeto->toArray()[0];

        $proponente = new \ProponenteDAO();
        $dadosProponente = $proponente->buscarDadosProponente($this->request->idPronac);
        $dadosProponente = (array) $dadosProponente[0];

        $tbAvaliacaoFinanceira = new \AvaliacaoResultados_Model_DbTable_tbAvaliacaoFinanceira();
        $where = [
            'idPronac' => $this->request->idPronac
        ];
        $dadosParecer = $tbAvaliacaoFinanceira->findBy($where);
        $dadosParecer = ($dadosParecer)?: new \stdClass();

        return [
            'consolidacaoComprovantes' => $dadosAvaliacaoFinanceira,
            'projeto' => $dadosProjeto,
            'proponente' => $dadosProponente,
            'parecer' => $dadosParecer
        ];
    }

    public function buscarAvaliacaoFinanceira()
    {
        $tbAvaliacaoFinanceira = new \AvaliacaoResultados_Model_DbTable_tbAvaliacaoFinanceira();
        $where = [
            $tbAvaliacaoFinanceira->getPrimary() => $this->request->idAvaliacaoFinanceira
        ];

        return $tbAvaliacaoFinanceira->findBy($where);
    }

    public function salvar()
    {
        $authInstance = \Zend_Auth::getInstance();
        $arrAuth = array_change_key_case((array) $authInstance->getIdentity());

        $parametros = $this->request->getParams();
        $tbAvaliacaoFinanceira = new \AvaliacaoResultados_Model_tbAvaliacaoFinanceira($parametros);
        $tbAvaliacaoFinanceira->setDtAvaliacaoFinanceira(date('Y-m-d h:i:s'));
        $tbAvaliacaoFinanceira->setIdUsuario($arrAuth['usu_codigo']);

        $mapper = new \AvaliacaoResultados_Model_tbAvaliacaoFinanceiraMapper();
        $codigo = $mapper->save($tbAvaliacaoFinanceira);

        $this->request->setParam('idAvaliacaoFinanceira', $codigo);

        if(!$codigo){
            return $mapper->getMessages();
        }

        return $this->buscarAvaliacaoFinanceira();
    }

}