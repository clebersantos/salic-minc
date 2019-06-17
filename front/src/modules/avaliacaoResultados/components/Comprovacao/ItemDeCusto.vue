<template>
    <v-container fluid>
        <v-speed-dial
            v-model="fab"
            bottom
            right
            direction="top"
            open-on-hover
            transition="slide-y-reverse-transition"
            fixed
        >
            <v-tooltip
                slot="activator"
                left
            >
                <v-btn
                    slot="activator"
                    v-model="fab"
                    color="teal"
                    dark
                    fab
                >
                    <v-icon>add</v-icon>
                    <v-icon>close</v-icon>
                </v-btn>
                <span>Criar Comprovante</span>
            </v-tooltip>

            <!-- NFe -->
            <v-tooltip
                left
            >
                <v-btn
                    slot="activator"
                    fab
                    dark
                    small
                    color="red"
                >
                    <v-icon>add</v-icon>
                </v-btn>
                <span>NFe</span>
            </v-tooltip>

            <!-- Criar Comprovante Internacional -->
            <v-tooltip
                left
            >
                <v-btn
                    slot="activator"
                    color="blue"
                    dark
                    fab
                    small
                    @click="comprovanteInternacionalDialog = !comprovanteInternacionalDialog"
                >
                    <v-icon>public</v-icon>
                </v-btn>
                <span>Criar Comprovante Internacional</span>
            </v-tooltip>

            <!-- Criar Comprovante Nacional -->
            <v-tooltip
                left
            >
                <v-btn
                    slot="activator"
                    fab
                    dark
                    small
                    color="success"
                    @click="comprovanteNacionalDialog = !comprovanteNacionalDialog"
                >
                    <strong>R$</strong>
                </v-btn>
                <span>Criar Comprovante Nacional</span>
            </v-tooltip>
        </v-speed-dial>

        <criar-comprovante-nacional
            v-if="dadosProjeto.dtInicioExecucao"
            :activator="comprovanteNacionalDialog"
            :id-planilha-itens="String(idPlanilhaItens)"
            :id-planilha-aprovacao="String(idPlanilhaAprovacao)"
            :data-inicio-formatada="dadosProjeto.dtInicioExecucao | dataFilter"
            :data-fim-formatada="dadosProjeto.dtFimExecucao | dataFilter"
            :data-inicio="dadosProjeto.dtInicioExecucao"
            :data-fim="dadosProjeto.dtFimExecucao"
            :valor-comprovar="valorComprovar | moedaFilter"
        />
        <criar-comprovante-internacional
            v-if="dadosProjeto.dtInicioExecucao"
            :activator="comprovanteInternacionalDialog"
            :id-planilha-itens="String(idPlanilhaItens)"
            :id-planilha-aprovacao="String(idPlanilhaAprovacao)"
            :data-inicio-formatada="dadosProjeto.dtInicioExecucao | dataFilter"
            :data-fim-formatada="dadosProjeto.dtFimExecucao | dataFilter"
            :data-inicio="dadosProjeto.dtInicioExecucao"
            :data-fim="dadosProjeto.dtFimExecucao"
            :valor-comprovar="valorComprovar | moedaFilter"
        />

        <!-- Excluir Comprovante -->
        <excluir-comprovante/>

        <detalhes-projeto
            :id-pronac="idPronac"
            :dados-projeto="dadosProjeto"
        />

        <detalhes-item
            :dados-item="dadosItem"
            :valor-comprovar="valorComprovar"
        />

        <v-btn
            color="primary"
            dark
            @click="openNFeModalAction({ open: true, type: 'new' })"
        >
            NFe
        </v-btn>
        <NFe
            v-bind="{
                uf:{ id: uf, value: uf},
                produto:{ id: produto, value: produto.produto},
                cidade:{ id: cidade.cdCidade, value: cidade.cidade},
                etapa: { id: etapa, value: dadosItem.Etapa },
                idPlanilhaAprovacao: idPlanilhaAprovacao,
                idPlanilhaItens: idPlanilhaItens,
                item: {
                    id: idPlanilhaItens,
                    value: dadosItem.Etapa,
                    varlorAprovado: dadosProjeto.varlorAprovado,
                    varlorComprovado: dadosProjeto.varlorComprovado,
                    varlorAprovado: dadosProjeto.varlorAprovado
                },
                projeto:{ idpronac: idPronac, nome: dadosProjeto.NomeProjeto },
                dialog: nfeModalGetter
            }"
        />
        <v-card class="mt-3">
            <v-card-title primary-title>
                <h2>Comprovantes Nacionais</h2>
            </v-card-title>
            <v-card-text>
                <lista-comprovantes
                    :id-pronac="String(idPronac)"
                    :id-planilha-itens="String(idPlanilhaItens)"
                    :produto="String(produto)"
                    :uf="String(uf)"
                    :id-uf="String(idUf)"
                    :cidade="String(cidade)"
                    :etapa="String(etapa)"
                    :valor-comprovar="valorComprovar | moedaFilter"
                    tipo="nacional"
                />
            </v-card-text>
        </v-card>

        <v-card class="mt-3">
            <v-card-title primary-title>
                <h2>Comprovantes Internacionais</h2>
            </v-card-title>
            <v-card-text>
                <lista-comprovantes
                    :id-pronac="String(idPronac)"
                    :id-planilha-itens="String(idPlanilhaItens)"
                    :produto="String(produto)"
                    :uf="String(uf)"
                    :id-uf="String(idUf)"
                    :cidade="String(cidade)"
                    :etapa="String(etapa)"
                    :valor-comprovar="valorComprovar | moedaFilter"
                    tipo="internacional"
                />
            </v-card-text>
        </v-card>

        <!-- Mensagem mostrada ao criar, editar ou excluir um comprovante -->
        <v-snackbar
            v-model="snackbar"
            :timeout="3000"
            :color="mensagemFinal.color"
            top
        >
            <v-icon
                class="mr-2"
                dark
            >
                {{ mensagemFinal.icon }}
            </v-icon>
            {{ mensagemFinal.msg }}
        </v-snackbar>
    </v-container>
</template>

<script>
import Vue from 'vue';
import { mapActions, mapGetters } from 'vuex';
import Moeda from '../../../../filters/money';
import ListaComprovantes from './ListaComprovantes';
import CriarComprovanteNacional from './CriarComprovanteNacional';
import CriarComprovanteInternacional from './CriarComprovanteInternacional';
import ExcluirComprovante from './ExcluirComprovante';
import DetalhesProjeto from './DetalhesProjeto';
import DetalhesItem from './DetalhesItem';
import NFe from './NFe';

Vue.filter('moedaFilter', Moeda);

export default {
    name: 'ItemDeCusto',
    components: {
        ListaComprovantes,
        CriarComprovanteNacional,
        CriarComprovanteInternacional,
        ExcluirComprovante,
        DetalhesProjeto,
        DetalhesItem,
        NFe,
    },
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
    data() {
        return {
            idPronac: this.$route.params.id,
            idUf: this.$route.params.idUf,
            uf: this.$route.params.uf,
            produto: this.$route.params.produto,
            cidade: this.$route.params.cidade,
            etapa: this.$route.params.etapa,
            idPlanilhaAprovacao: this.$route.params.idPlanilhaAprovacao,
            idPlanilhaItens: this.$route.params.idPlanilhaItens,
            snackbar: false,
            acao: '',
            mensagemFinal: {},
            fab: false,
            comprovanteInternacionalDialog: false,
            comprovanteNacionalDialog: false,
            nfeModal: false,
        };
    },
    computed: {
        ...mapGetters({
            dadosProjeto: 'avaliacaoResultados/getDadosProjeto',
            dadosItem: 'avaliacaoResultados/getDadosItem',
            statusComprovante: 'avaliacaoResultados/statusComprovante',
            nfeModalGetter: 'avaliacaoResultados/nfeModalGetter',
        }),
        dadosItemParams() {
            return {
                idPronac: this.idPronac,
                uf: this.uf,
                etapa: this.etapa,
                cidade: this.cidade,
                produto: this.produto,
                idPlanilhaItens: this.idPlanilhaItens,
            };
        },
        valorComprovar() {
            return this.dadosItem.vlAprovado - this.dadosItem.vlComprovado;
        },
        mensagemSucesso() {
            return {
                msg: `Comprovante ${this.acao} com sucesso!`,
                icon: 'done',
                color: 'success',
            };
        },
        mensagemFalha() {
            return {
                msg: `Houve um problema ao tentar ${this.acao} o comprovante, tente novamente mais tarde.`,
                icon: 'warning',
                color: 'error',
            };
        },
    },
    watch: {
        statusComprovante(status) {
            this.acao = status.acao;
            this.mensagemFinal = status.success ? this.mensagemSucesso : this.mensagemFalha;
            this.snackbar = true;
        },
    },
    mounted() {
        this.setDadosProjeto(this.idPronac);
        this.setDadosItem(this.dadosItemParams);
    },
    methods: {
        ...mapActions({
            setDadosProjeto: 'avaliacaoResultados/getDadosProjeto',
            setDadosItem: 'avaliacaoResultados/getDadosItem',
            openNFeModalAction: 'avaliacaoResultados/openNFeModalAction',
        }),
        nfeModalOpen() {
            this.nfeModal = true;
        },
    },
};
</script>
