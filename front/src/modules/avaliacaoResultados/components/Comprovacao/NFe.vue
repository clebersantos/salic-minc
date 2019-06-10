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
            <v-container fluid>
                <DetalhesItem
                    :dadosItem="{
                        Item: item.value,
                        Produto: produto.value,
                        Etapa: etapa.value,
                        uf: uf.value,
                        cidade: cidade.value,
                        vlAprovado: item.varlorAprovado,
                        vlComprovado: item.varlorComprovado,
                    }"
                />

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
                        sm6
                    >
                        <v-select
                            :items="[prod]"
                            item-text="xProd"
                            item-value="cProd"
                            label="Produto"
                            outline
                        />
                    </v-flex>
                    <v-btn
                        dark
                        @click="submit()">
                        Salvar
                    </v-btn>
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
                dataEmissao: nota.ide.dhEmi,
                dataPagamento: nota.ide.dhEmi,
                forma: 3,
            /*     numeroDocumento: this.numeroDocumentoPagamento, */
                valor: nota.pag.detPag.vPag,
                justificativa: 'NF-e',
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
