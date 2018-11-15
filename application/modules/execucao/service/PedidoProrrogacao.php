<?php

namespace Application\Modules\Execucao\Service\PedidoProrrogacao;

class PedidoProrrogacao
{
    /**
     * @var \Zend_Controller_Request_Abstract $request
     */
    private $request;

    /**
     * @var \Zend_Controller_Response_Abstract $response
     */
    private $response;

    function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function buscarPedidosProrrogacao()
    {
        $idPronac = $this->request->idPronac;
        if (strlen($idPronac) > 7) {
            $idPronac = \Seguranca::dencrypt($idPronac);
        }

        //****** Dados do Projeto - Cabecalho *****//
//        $projetos = new Projetos();
//        $DadosProjeto = $projetos->dadosProjeto(array('idPronac = ?' => $idpronac))->current();
//        $this->view->DadosProjeto = $DadosProjeto;

        //****** Lista de Prorroga��es *****//
        $prorrogacao = new \Prorrogacao();
        $DadosProrrogacoes = $prorrogacao->buscarProrrogacoes($idPronac)->toArray();
        return ($DadosProrrogacoes);
//        $this->view->DadosProrrogacoes = $DadosProrrogacoes;
    }
}

