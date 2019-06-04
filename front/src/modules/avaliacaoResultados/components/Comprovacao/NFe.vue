<template>
    <v-dialog v-model="dialog" fullscreen hide-overlay transition="dialog-bottom-transition">
        <template v-slot:activator="{ on }">
            <v-btn color="primary" dark v-on="on">NFe</v-btn>
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
                <v-flex xs12 sm12 md12>
                    <v-text-field
                        label="Código de Acesso"
                        placeholder="0000 0000 0000 0000 0000 0000 0000 000 0000 0000 0000"
                        outline
                        v-model="chaveAcesso"
                    ></v-text-field>
                    <v-btn
                        @click="buscarNFe()"
                        dark
                    >
                        Buscar
                    </v-btn>
                </v-flex>
                <v-flex
                    v-if="produtos"
                    xs12
                    sm6
                    d-flex
                >
                    <v-select
                        :items="[produtos]"
                        item-text="xProd"
                        item-value="cProd"
                        label="Produto"
                        outline
                        ></v-select>
                </v-flex>
                <v-btn 
                    v-if="produtos"
                    dark  
                    @click="dialog = false">
                    Salvar
                </v-btn>
            </v-container>
        </v-card>
    </v-dialog>
</template>

<script>
import Vue from 'vue';
import { mapActions, mapGetters } from 'vuex';
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
        idPronac: { type:[String, Number] , default: '' },
        idPlanilhaItens: { type: [String, Number], default: '' },
        produto: { type: [String, Number], default: '' },
        uf: { type: String, default: '' },
        idUf: { type: [String, Number], default: '' },
        cidade: { type: [String, Number], default: '' },
        etapa: { type: [String, Number], default: '' },
        tipo: { type: String, default: '' },
    },
    data() {
        return {
            chaveAcesso: '',
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
        ...mapGetters({
            getNFe: 'avaliacaoResultados/getNFe',
        }),
        comprovantes() {
            return this.$store.getters[this.getter];
        },
        produtos() {
            return Object.keys(this.getNFe).length > 0 ? this.getNFe.data.NFe.infNFe.det.prod : false;
        },
    },
    created() {
        this.buscarNFeAction();
    },
    mounted() {
        this.listarComprovantes(this.comprovanteParams);
        this.$root.$on('recarregar-comprovantes', () => this.listarComprovantes(this.comprovanteParams));
    },
    methods: {
        ...mapActions({
            listarComprovantes: 'avaliacaoResultados/listarComprovantes',
            excluirComprovante: 'avaliacaoResultados/excluirComprovante',
            buscarNFeAction: 'avaliacaoResultados/buscarNFeAction',
        }),
        buscarNFe() {
            this.buscarNFeAction(this.chaveAcesso);
        },
    },
};
</script>
