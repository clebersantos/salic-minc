<template>
    <v-dialog
        v-model="dialog"
        width="500"
    >
        <v-card>
            <v-card-text>
                Você tem certeza que deseja excluir este comprovante?
            </v-card-text>
            <v-divider/>
            <v-card-actions>
                <v-spacer/>
                <v-btn
                    color="success"
                    flat
                    @click="excluir"
                >
                    SIM
                </v-btn>
                <v-btn
                    color="error"
                    flat
                    @click="dialog = false"
                >
                    NÃO
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    props: {
        idComprovantePagamento: { type: String, default: '' },
    },
    data() {
        return {
            dialog: false,
            idComprovante: '',
        };
    },
    watch: {
        status() {
            this.$root.$emit('recarregar-comprovantes');
        },
    },
    mounted() {
        this.$root.$on('excluir-comprovante', this.verificarExclusao);
    },
    methods: {
        ...mapActions({
            excluirComprovante: 'avaliacaoResultados/excluirComprovante',
        }),
        excluir() {
            this.excluirComprovante({ 'comprovante[idComprovantePagamento]': this.idComprovante });
            this.dialog = false;
        },
        verificarExclusao(idComprovantePagamento) {
            this.dialog = true;
            this.idComprovante = idComprovantePagamento;
        },
    },
};
</script>
