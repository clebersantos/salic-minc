<?php

class Readequacao_SaldoAplicacaoController extends Readequacao_GenericController 
{
    public function init()
    {
        parent::init();
        
        $idPronac = $this->_request->getParam('idPronac');
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        $this->idPronac = $idPronac;
        $this->view->idTipoReadequacao = Readequacao_Model_DbTable_TbReadequacao::TIPO_READEQUACAO_SALDO_APLICACAO;
        $this->view->idPronac = $idPronac;
        $this->view->idPerfil = $this->idPerfil;
    }

    public function indexAction()
    {
        $this->view->projeto = $this->projeto;
    }

    public function solicitarUsoSaldoAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $dados = $this->getRequest()->getPost();
        $idPronac = $dados['idPronac'];

        $tbReadequacao = new Readequacao_Model_DbTable_TbReadequacao();
        
        $idReadequacao = $tbReadequacao->criarReadequacaoPlanilha($idPronac, Readequacao_Model_DbTable_TbReadequacao::TIPO_READEQUACAO_SALDO_APLICACAO);

        $tbReadequacao = new Readequacao_Model_DbTable_TbReadequacao();
        $readequacao = $tbReadequacao->obterDadosReadequacao(
            Readequacao_Model_DbTable_TbReadequacao::TIPO_READEQUACAO_SALDO_APLICACAO,
            $idPronac,
            $idReadequacao
        );
        
        $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
        $verificarPlanilhaReadequadaAtual = $tbPlanilhaAprovacao->buscarPlanilhaReadequadaEmEdicao($idPronac, $idReadequacao);
        
        if (count($verificarPlanilhaReadequadaAtual) == 0) {
            $planilhaAtiva = $tbPlanilhaAprovacao->buscarPlanilhaAtiva($idPronac);
            $criarPlanilha = $tbPlanilhaAprovacao->copiarPlanilhas($idPronac, $idReadequacao);
            
            if ($criarPlanilha) {
                $this->_helper->json(array(
                    'msg' => 'Planilha copiada corretamente',
                    'success' => 'true',
                    'readequacao' => $readequacao
                ));
            } else {
                $this->_helper->json(array(
                    'msg' => 'Houve um erro ao tentar copiar a planilha',
                    'success' => 'false',
                    'readequacao' => $readequacao
                ));
            }
        }
    }

    public function carregarValorEntrePlanilhasAction()
    {
        $auth = Zend_Auth::getInstance();
        $this->_helper->layout->disableLayout();
        $idPronac = $this->_request->getParam("idPronac");
        
        if (strlen($idPronac) > 7) {
            $idPronac = Seguranca::dencrypt($idPronac);
        }
        
        try {
            $tbReadequacao = new Readequacao_Model_DbTable_TbReadequacao();
            
            $valorEntrePlanilhas = $tbReadequacao->carregarValorEntrePlanilhas(
                $idPronac,
                Readequacao_Model_DbTable_TbReadequacao::TIPO_READEQUACAO_SALDO_APLICACAO
            );
            $valorEntrePlanilhas['vlDiferencaPlanilhas'] = 'R$ '. number_format(
                ($valorEntrePlanilhas['PlanilhaReadequadaTotal'] - $valorEntrePlanilhas['PlanilhaAtivaTotal']
                ),
                2,
                ',',
                '.'
            );
                                                         
            $this->_helper->json([
                'valorEntrePlanilhas' => $valorEntrePlanilhas,
                'success' => 'true',
                'msg' => 'Readequa&ccedil;&atilde;o salva com sucesso!'
            ]);
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(412);
            $this->_helper->json([
                'data' => $valorEntrePlanilhas,
                'success' => 'false',
                'msg' => $e->getMessage()
            ]);
        }
    }
    
    public function salvarReadequacaoAction()
    {
        $dados = $this->getRequest()->getPost();
        
        try {
            
            if ($this->idPerfil != Autenticacao_Model_Grupos::PROPONENTE) {
                throw new Exception("Acesso negado!");
            }
            
            $dados['idTipoReadequacao'] = Readequacao_Model_DbTable_TbReadequacao::TIPO_READEQUACAO_SALDO_APLICACAO;
            $dados['stAtendimento'] = 'D';
            $dados['idDocumento'] = null;
            $dados['dsJustificativa'] = $dados['justificativa'];
            
            $readequacaoMapper = new Readequacao_Model_TbReadequacaoMapper();
            $dados = $readequacaoMapper->salvarSolicitacaoReadequacao($dados);

            $this->_helper->json(array('readequacao' => $dados, 'success' => 'true', 'msg' => 'Readequa&ccedil;&atilde;o salva com sucesso!'));
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(412);
            $this->_helper->json(array('data' => $dados, 'success' => 'false', 'msg' => $e->getMessage()));
        }
    }

    public function excluirReadequacaoAction()
    {
        $this->_helper->layout->disableLayout();
        
        if ($this->idPerfil != Autenticacao_Model_Grupos::PROPONENTE) {
            parent::message("Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar essa &aacute;rea do sistema!", "principal", "ALERT");
        }
        
        $idPronac = $this->_request->getParam("idPronac");
        $idReadequacao = $this->_request->getParam('idReadequacao');

        try {
            $tbReadequacao = new Readequacao_Model_DbTable_TbReadequacao();
            $dados = $tbReadequacao->buscar(['idReadequacao =?'=>$idReadequacao])->current();
            
            if (!empty($dados->idDocumento)) {
                $tbDocumento = new tbDocumento();
                $tbDocumento->excluirDocumento($dados->idDocumento);
            }
            
            $tbPlanilhaAprovacao = new tbPlanilhaAprovacao();
            $tbPlanilhaAprovacao->delete([
                'IdPRONAC = ?'=>$idPronac,
                'tpPlanilha = ?'=>'SR',
                'idReadequacao = ?'=>$idReadequacao
            ]);
            
            $exclusao = $tbReadequacao->delete([
                'idPronac =?'=> $idPronac,
                'idReadequacao =?'=>
                $idReadequacao
            ]);
            
            $this->_helper->json([
                'success' => 'true',
                'msg' => 'Readequa&ccedil;&atilde;o exclu&iacute;da com sucesso!'
            ]);
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(412);
            $this->_helper->json([
                'data' => $dados,
                'success' => 'false',
                'msg' => $e->getMessage()
            ]);
        }
    }
}