<template>
    <v-dialog v-model="dialog" fullscreen hide-overlay transition="dialog-bottom-transition">
        <template v-slot:activator="{ on }">
            <v-btn color="primary" dark v-on="on">Open Dialog</v-btn>
        </template>
        <v-card>
            <v-toolbar dark color="primary">
                <v-btn icon dark @click="dialog = false">
                    <v-icon>close</v-icon>
                </v-btn>
                <v-toolbar-title>Projeto XXX</v-toolbar-title>
            </v-toolbar>
            <v-container>
                <v-subheader>
                    Item:
                </v-subheader>
                <v-flex xs12 sm6 md3>
                    <v-text-field
                        label="Código de Acesso"
                        placeholder="0000 0000 0000 0000 0000 0000 0000 000 0000 0000 0000"
                        outline
                    ></v-text-field>
                    <v-btn dark>
                        Buscar
                    </v-btn>
                </v-flex>
                <v-flex xs12 sm6 d-flex>
                    <v-select
                        :items="items"
                        label="Outline style"
                        outline
                        ></v-select>
                </v-flex>
                <v-btn dark  @click="dialog = false">Salvar</v-btn>
            </v-container>
        </v-card>
    </v-dialog>
</template>

<script>
import Vue from 'vue';
import { mapActions } from 'vuex';
import Moeda from '../../../../filters/money';
import ComprovarPagamento from './ComprovarPagamento';
import ExcluirComprovante from './ExcluirComprovante';

Vue.filter('moedaMasck', Moeda);

export default {
    name: 'Comprovante',
    components: {
        ComprovarPagamento,
        ExcluirComprovante,
    },
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
        }),
    },
};
</script>
