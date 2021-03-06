<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TipoAvaliacaoRestControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    public function testGet()
    {
        $this->json(
            'GET',
            '/avaliacao-resultados/tipo-avaliacao/{idpronac}'
        )->seeJson([
            'teste Tipo Avaliacao GET'
        ]);
    }
    public function testPost()
    {
        $this->json(
            'POST',
            '/avaliacao-resultados/tipo-avaliacao'
        )->seeJson([
            'teste Tipo Avaliacao Post'
        ]);
    }
    public function testIndex()
    {
        $this->json(
            'GET',
            '/avaliacao-resultados/tipo-avaliacao'
        )->seeJson([
            'teste Tipo Avaliacao INDEX'
        ]);
    }
}
