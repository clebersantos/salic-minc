<template>
    <v-container fluid>
        <v-toolbar>
            <v-btn
                :to="{ name: 'PlanilhaComprovacao', params: { id: idPronac }}"
                icon
                class="hidden-xs-only"
            >
                <v-icon>arrow_back</v-icon>
            </v-btn>
            <v-toolbar-title>
                Prestação de Contas: Comprovantes
            </v-toolbar-title>
        </v-toolbar>

        <v-card v-if="dadosProjeto.NomeProjeto">
            <v-card-title primary-title>
                <h2>{{ dadosProjeto.Pronac }} &#45; {{ dadosProjeto.NomeProjeto }}</h2>
            </v-card-title>
            <v-card-text>
                <div class="my-3">
                    <div class="d-inline-block mr-5">
                        <h4>Data Início da Execução</h4>
                        <p class="text-xs-left">{{ dadosProjeto.dtInicioExecucao | dataFilter }}</p>
                    </div>
                    <div class="d-inline-block mr-5">
                        <h4>Data Final da Execução</h4>
                        <p class="text-xs-left">{{ dadosProjeto.dtFimExecucao | dataFilter }}</p>
                    </div>
                    <div class="d-inline-block mr-5">
                        <h4>Valor Aprovado</h4>
                        <p class="text-xs-left">R$ {{ dadosProjeto.vlAprovado | moedaFilter }}</p>
                    </div>
                    <div class="d-inline-block mr-5">
                        <h4>Valor Comprovado</h4>
                        <p class="text-xs-left">R$ {{ dadosProjeto.vlComprovado | moedaFilter }}</p>
                    </div>
                    <div class="d-inline-block">
                        <h4>Valor a Comprovar</h4>
                        <p class="text-xs-left">R$ {{ dadosProjeto.vlComprovar | moedaFilter }}</p>
                    </div>
                </div>
            </v-card-text>
            <v-card-actions>
                <v-btn
                    :href="'/consultardadosprojeto/index?idPronac=' + idPronac"
                    color="success"
                    target="_blank"
                    class="mr-2"
                    dark
                >VER PROJETO</v-btn>
            </v-card-actions>
        </v-card>
    </v-container>
</template>

<script>
import Vue from 'vue';
import Moeda from '../../../../filters/money';

Vue.filter('moedaFilter', Moeda);

export default {
    filters: {
        dataFilter(data) {
            const dataFormatada = data.replace(/-/g, '/');
            const date = new Date(dataFormatada);
            return date.toLocaleString(['pt-BR'], {
                month: '2-digit',
                day: '2-digit',
                year: 'numeric',
            });
        },
    },
    props: {
        idPronac: { type: String, default: '' },
        dadosProjeto: {
            type: Object,
            default() {
                return {
                    NomeProjeto: 'Carregando...',
                    Pronac: 'Carregando...',
                    dtInicioExecucao: 'Carregando...',
                    dtFimExecucao: 'Carregando...',
                    vlAprovado: 'Carregando...',
                    vlComprovado: 'Carregando...',
                    vlComprovar: 'Carregando...',
                };
            },
        },
    },
};
</script>
