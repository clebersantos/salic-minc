<?php

namespace Application\Modules\ExecucaoFisica\Service\Assinatura\Acao;

use MinC\Assinatura\Acao\IListaAcoesModulo;

class ListaAcoesModulo implements IListaAcoesModulo
{

    public function obterLista(): array
    {
        return [
            new Assinar(),
            new Encaminhar(),
            new Devolver(),
            new Finalizar()
        ];
    }
}
