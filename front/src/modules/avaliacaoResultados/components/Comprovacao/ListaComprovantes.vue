<template>
    <v-container fluid>
        <div v-if="comprovantes.length > 0">
            <v-data-table
                :headers="headers"
                :items="comprovantes"
                class="elevation-1"
            >
                <template
                    slot="items"
                    slot-scope="props"
                >
                    <td>{{ props.item.fornecedor.nome }}</td>
                    <td>R$ {{ props.item.valor | moedaMasck }}</td>
                    <td class="text-xs-center">
                        <!-- Visualizar Comprovante -->
                        <v-tooltip
                            top
                        >
                            <v-btn
                                slot="activator"
                                flat
                                icon
                                color="green"
                                @click="visualizarComprovante(props.item)"
                            >
                                <v-icon>visibility</v-icon>
                            </v-btn>
                            <span>Visualizar</span>
                        </v-tooltip>

                        <!-- Editar Comprovante -->
                        <v-tooltip
                            top
                        >
                            <v-btn
                                slot="activator"
                                flat
                                icon
                                color="blue"
                                @click="editarComprovante(props.item)"
                            >
                                <v-icon>edit</v-icon>
                            </v-btn>
                            <span>Editar</span>
                        </v-tooltip>

                        <!-- Excluir Comprovante -->
                        <v-tooltip
                            top
                        >
                            <v-btn
                                slot="activator"
                                flat
                                icon
                                color="red"
                                @click="excluirComprovante(props.item.idComprovantePagamento)"
                            >
                                <v-icon>delete</v-icon>
                            </v-btn>
                            <span>Excluir</span>
                        </v-tooltip>
                    </td>
                </template>
            </v-data-table>
        </div>
        <div v-else>
            <p>Não há comprovantes <span v-if="tipo === 'nacional'">nacionais</span><span v-else>internacionais</span>.</p>
        </div>
    </v-container>
</template>

<script>
import Vue from 'vue';
import { mapActions } from 'vuex';
import Moeda from '../../../../filters/money';

Vue.filter('moedaMasck', Moeda);

export default {
    name: 'Comprovante',
    filters: {
        dataMasck(data) {
            const dataFormatada = data.replace(/-/g, '/');
            const date = new Date(dataFormatada);
            return date.toLocaleString(['pt-BR'], {
                month: '2-digit',
                day: '2-digit',
                year: '2-digit',
            });
        },
    },
    props: {
        idPronac: { type: String, default: '' },
        idPlanilhaItens: { type: String, default: '' },
        produto: { type: String, default: '' },
        uf: { type: String, default: '' },
        idUf: { type: String, default: '' },
        cidade: { type: String, default: '' },
        etapa: { type: String, default: '' },
        tipo: { type: String, default: '' },
        valorComprovar: { type: String, default: '' },
    },
    data() {
        return {
            comprovanteParams: {
                idPronac: this.idPronac,
                idPlanilhaItens: this.idPlanilhaItens,
                codigoProduto: this.produto,
                uf: this.uf,
                idUf: this.idUf,
                codigoCidade: this.cidade,
                codigoEtapa: this.etapa,
                tipo: this.tipo,
            },
            getter: `avaliacaoResultados/${this.tipo === 'nacional' ? 'listarComprovantesNacionais' : 'listarComprovantesInternacionais'}`,
            headers: [
                {
                    text: 'Fornecedor',
                    align: 'left',
                    sortable: false,
                    value: 'fornecedor',
                },
                {
                    text: 'Valor',
                    align: 'left',
                    sortable: false,
                    value: 'valor',
                },
                {
                    text: 'Ações',
                    align: 'center',
                    sortable: false,
                    value: 'acoes',
                },
            ],
            dialog: false,
        };
    },
    computed: {
        comprovantes() {
            return this.$store.getters[this.getter];
        },
    },
    mounted() {
        this.listarComprovantes(this.comprovanteParams);
        this.$root.$on('recarregar-comprovantes', () => this.listarComprovantes(this.comprovanteParams));
    },
    methods: {
        ...mapActions({
            listarComprovantes: 'avaliacaoResultados/listarComprovantes',
            excluirComprovante: 'avaliacaoResultados/excluirComprovante',
            carregarDadosEdicaoComprovante: 'avaliacaoResultados/carregarDadosEdicaoComprovante',
        }),
        visualizarComprovante(dadosComprovante) {
            // 1. Carregar dados do comprovante
            this.carregarDadosEdicaoComprovante(dadosComprovante);
            // 2. Emitir evento para iniciar visualização
            this.$root.$emit('visualizar-comprovante');
        },
        editarComprovante(dadosComprovante) {
            // 1. Carregar dados do comprovante
            this.carregarDadosEdicaoComprovante(dadosComprovante);
            // 2. Emitir evento para iniciar edição
            this.$root.$emit('editar-comprovante');
        },
        excluirComprovante(idComprovantePagamento) {
            this.$root.$emit('excluir-comprovante', idComprovantePagamento);
        },
    },
};
</script>
