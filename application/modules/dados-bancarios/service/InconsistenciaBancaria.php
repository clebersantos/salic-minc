<?php

namespace Application\Modules\DadosBancarios\Service\InconsistenciaBancaria;

class InconsistenciaBancaria
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

    public function buscarInconsistenciaBancaria()
    {
        $idPronac = $this->request->idPronac;
            $where = array();
            $where['idPronac = ?'] = $idPronac;

            $Dados = new \Projetos();
//            $total = $Dados->inconsistenciasComprovacao($where, $order, null, null, true);

            $busca = $Dados->inconsistenciasComprovacao($where, null, null, null)->toArray();
//xd($busca);

            return $busca;
    }
}

