<template>
    <v-dialog
        v-model="dialog"
        fullscreen
        hide-overlay
        transition="dialog-bottom-transition"
    >
        <template v-slot:activator="{ on }">
            <v-btn
                color="primary"
                dark
                v-on="on">
                NFe
            </v-btn>
        </template>
        <v-card>
            <v-toolbar
                dark
                color="primary"
            >
                <v-btn
                    icon
                    dark
                    @click="dialog = false"
                >
                    <v-icon>close</v-icon>
                </v-btn>
                <v-toolbar-title>{{ projeto.idpronac }} - {{ projeto.nome }}</v-toolbar-title>
            </v-toolbar>
            <v-container
                fluid
                grid-list-lg>
                <v-layout column wrap>
                    <v-flex
                        xs6
                        sm6
                    >
                        <v-card>
                            <v-card-text>
                            <v-layout
                                align-start
                                justify-center
                                row
                            >
                                <v-flex>
                                    <v-text-field
                                        v-model="chaveAcesso"
                                        label="Código de Acesso"
                                        placeholder="0000000000000000000000000000000000000000000"
                                        outline
                                    />
                                </v-flex>
                                <v-flex>
                                    <v-btn
                                        dark
                                        @click="buscarNFe()"
                                        >
                                        Buscar
                                    </v-btn>
                                </v-flex>
                            </v-layout>
                            <v-layout
                                v-if="Object.keys(produtos).length > 0"
                                row
                            >
                                <v-flex
                                    xs12
                                    sm12
                                >
                                <h4>
                                    Fornecedor:
                                </h4>
                                <div
                                    v-if="Object.keys(emissor).length > 0"
                                >
                                    CNPJ: {{emissor.CNPJ}}
                                </div>
                                <div
                                    v-if="Object.keys(emissor).length > 0"
                                >
                                    Nome: {{emissor.xNome}}
                                </div>
                                    <v-select
                                        :items="[prod]"
                                        item-text="xProd"
                                        item-value="cProd"
                                        label="Produto"
                                        outline
                                    />
                                    <v-textarea
                                        v-model="justificativa"
                                        outline
                                        name="input-7-4"
                                        label="Justificativa"
                                    />
                                    <v-btn
                                        dark
                                        @click="submit()">
                                        Salvar
                                    </v-btn>
                                </v-flex>
                            </v-layout>
                            </v-card-text>
                        </v-card>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-card>
    </v-dialog>
</template>

<script>
import Vue from 'vue';
import { mapActions, mapGetters } from 'vuex';
import Moeda from '../../../../filters/money';
import DetalhesProjeto from './DetalhesProjeto';
import DetalhesItem from './DetalhesItem';

Vue.filter('moedaMasck', Moeda);

export default {
    name: 'NFe',
    components: {
        DetalhesProjeto,
        DetalhesItem,
    },
    props: {
        projeto: { type: Object, default: () => {} },
        produto: { type: Object, default: () => {} },
        etapa: { type: Object, default: () => {} },
        uf: { type: Object, default: () => {} },
        cidade: { type: Object, default: () => {} },
        item: { type: Object, default: () => {} },
        idPlanilhaAprovacao: { type: [String, Number], default: ''  },
    },
    data() {
        return {
            chaveAcesso: '',
            dialog: false,
            produtos: false,
            justificativa: 'NF-e',
            emissor: {},

        };
    },
    computed: {
        ...mapGetters({
            getNFe: 'avaliacaoResultados/getNFe',
        }),
        prod() {
            return Object.keys(this.getNFe).length > 0 ? this.getNFe.data.NFe.infNFe.det.prod : false;
        },
    },
    watch: {
        getNFe(val) {
            this.produtos = (Object.keys(val).length > 0) ? val.data.NFe.infNFe.det.prod : false;
            this.emissor = (Object.keys(val).length > 0) ? val.data.NFe.infNFe.emit : false;
        },
    },
    methods: {
        ...mapActions({
            buscarNFeAction: 'avaliacaoResultados/buscarNFeAction',
            criarComprovante: 'avaliacaoResultados/criarComprovante',
        }),
        buscarNFe() {
            this.buscarNFeAction(this.chaveAcesso);
        },

        submit() {
            const formData = new FormData();
            const nota = this.getNFe.data.NFe.infNFe;

            let d = new Date(nota.ide.dhEmi).toLocaleString("pt-BR", {timeZone: "America/Sao_Paulo"});
            d = new Date(d);
            d = d.getDate() + '/' + d.getMonth() + '/' + d.getFullYear();

            const comprovante = {
                fornecedor: {
            /*         nacionalidade: 31, */
            /*         tipoPessoa: this.cpfCnpjLabel === 'CPF' ? 1 : 2, */
                    CNPJCPF: nota.emit.CNPJ,
                    nome: nota.emit.xNome,
                    eInternacional: false,
                },
                item: this.item.id,
                idPlanilhaAprovacao: this.idPlanilhaAprovacao,
                tipo: 6,
                numero: nota.ide.nNF,
                serie: nota.ide.serie,
                dataEmissao: d,
                dataPagamento: d,
                forma: 3,
            /*     numeroDocumento: this.numeroDocumentoPagamento, */
                valor: nota.pag.detPag.vPag,
                justificativa: this.justificativa,
            };
            /* console.log(comprovante); */
            /* console.log(this.getNFe.data.NFe.infNFe); */
            /* return; */

            /* if (this.modoEdicao) { */
            /*     comprovante['_index'] = parseInt(this.dadosComprovante.idComprovantePagamento, 10); */
            /*     comprovante.id = parseInt(this.dadosComprovante.idComprovantePagamento, 10); */
            /*     comprovante.idComprovantePagamento = parseInt(this.dadosComprovante.idComprovantePagamento, 10); */
            /* } */

            const comprovanteJSON = JSON.stringify(comprovante);
            formData.append('comprovante', comprovanteJSON);
            /* formData.append('arquivo', this.arquivo); */

            // if (this.modoEdicao) {
            //     this.editarComprovante(formData);
            // } else {
                this.criarComprovante(formData);
            // }
        },
    },
};
</script>
