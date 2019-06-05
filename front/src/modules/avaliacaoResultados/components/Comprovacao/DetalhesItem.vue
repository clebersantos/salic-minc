<template>
    <v-card
        v-if="dadosItem.Item"
        class="mt-3"
    >
        <v-card-title primary-title>
            <h2>Item: {{ dadosItem.Item }}</h2>
        </v-card-title>
        <v-card-text>
            <div class="my-3">
                <div class="d-inline-block mr-5">
                    <h4>Produto</h4>
                    <p
                        class="text-xs-left"
                        v-html="dadosItem.Produto"/>
                </div>
                <div class="d-inline-block mr-5">
                    <h4>Etapa</h4>
                    <p class="text-xs-left">{{ dadosItem.Etapa }}</p>
                </div>
                <div class="d-inline-block mr-5">
                    <h4>UF</h4>
                    <p class="text-xs-left">{{ dadosItem.uf }}</p>
                </div>
                <div class="d-inline-block mr-5">
                    <h4>Cidade</h4>
                    <p class="text-xs-left">{{ dadosItem.cidade }}</p>
                </div>
                <div class="d-inline-block mr-5">
                    <h4>Itens de Custo</h4>
                    <p class="text-xs-left">{{ dadosItem.Item }}</p>
                </div>
                <div class="d-inline-block mr-5">
                    <h4>Aprovado</h4>
                    <p class="text-xs-left">R$ {{ dadosItem.vlAprovado | moedaFilter }}</p>
                </div>
                <div class="d-inline-block mr-5">
                    <h4>Total Comprovado</h4>
                    <p class="text-xs-left">R$ {{ dadosItem.vlComprovado | moedaFilter }}</p>
                </div>
                <div class="d-inline-block">
                    <h4>Faltando Comprovar</h4>
                    <p class="text-xs-left">R$ {{ valorComprovar | moedaFilter }}</p>
                </div>
            </div>
        </v-card-text>
    </v-card>
</template>

<script>
import Vue from 'vue';
import Moeda from '../../../../filters/money';

Vue.filter('moedaFilter', Moeda);

export default {
    props: {
        dadosItem: {
            type: Object,
            default() {
                return {
                    Item: 'Carregando...',
                    Produto: 'Carregando...',
                    Etapa: 'Carregando...',
                    uf: 'Carregando...',
                    cidade: 'Carregando...',
                    vlAprovado: 'Carregando...',
                    vlComprovado: 'Carregando...',
                };
            },
        },
        valorComprovar: { type: Number, default: -1 },
    },
};
</script>
