<template>
    <v-container fluid>
        <!-- Criar Comprovante -->
        <comprovar-pagamento
            v-if="dadosProjeto.dtInicioExecucao"
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

        <v-card class="mt-3">
            <v-card-title primary-title>
                <h2>Comprovantes Nacionais</h2>
            </v-card-title>
            <v-card-text>
                <comprovante
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
                <comprovante
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
import Comprovante from './Comprovante';
import ComprovarPagamento from './ComprovarPagamento';
import ExcluirComprovante from './ExcluirComprovante';
import DetalhesProjeto from './DetalhesProjeto';
import DetalhesItem from './DetalhesItem';

Vue.filter('moedaFilter', Moeda);

export default {
    name: 'ItemDeCusto',
    components: {
        Comprovante,
        ComprovarPagamento,
        ExcluirComprovante,
        DetalhesProjeto,
        DetalhesItem,
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
        };
    },
    computed: {
        ...mapGetters({
            dadosProjeto: 'avaliacaoResultados/getDadosProjeto',
            getDadosItem: 'avaliacaoResultados/getDadosItem',
            statusComprovante: 'avaliacaoResultados/statusComprovante',
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
        dadosItem() {
            return this.getDadosItem;
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
        }),
    },
};
</script>
