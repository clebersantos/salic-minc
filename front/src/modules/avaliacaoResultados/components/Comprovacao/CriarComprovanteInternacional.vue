<template>
    <v-dialog
        v-model="dialog"
        scrollable
    >
        <v-card>
            <v-toolbar
                dark
                card
                color="green darken-3">
                <v-btn
                    icon
                    dark
                    @click="dialog = false"
                >
                    <v-icon>close</v-icon>
                </v-btn>
                <v-toolbar-title v-if="modoEdicao">Editar Comprovante</v-toolbar-title>
                <v-toolbar-title v-else-if="modoVisualizacao">Visualizar Comprovante</v-toolbar-title>
                <v-toolbar-title v-else>Criar Comprovante</v-toolbar-title>
                <v-spacer/>
            </v-toolbar>
            <v-card-text id="start">
                <v-form
                    ref="form"
                    v-model="valid"
                    lazy-validation
                >
                    <v-container>
                        <h3 class="mb-2">
                            DADOS DA COMPROVAÇÃO DE PAGAMENTO INTERNACIONAL
                        </h3>
                        <v-layout
                            row
                            wrap
                        >
                            <v-flex
                                xs12
                            >
                                <v-select
                                    :items="paises"
                                    :readonly="modoVisualizacao"
                                    v-model="pais"
                                    item-value="id"
                                    item-text="nome"
                                    label="NACIONALIDADE DO FORNECEDOR *"
                                    outline
                                />
                            </v-flex>
                            <v-flex
                                sm12
                                md6
                            >
                                <v-text-field
                                    :readonly="modoVisualizacao"
                                    v-model="nomeEmpresa"
                                    label="NOME DA EMPRESA *"
                                    outline
                                />
                            </v-flex>
                            <v-flex
                                sm12
                                md6
                            >
                                <v-text-field
                                    :readonly="modoVisualizacao"
                                    v-model="endereco"
                                    label="ENDEREÇO *"
                                    outline
                                />
                            </v-flex>
                            <v-flex
                                xs12
                            >
                                <v-radio-group
                                    :readonly="modoVisualizacao"
                                    v-model="tipoDocumento"
                                    label="TIPO DE DOCUMENTO"
                                >
                                    <v-radio
                                        :value="6"
                                        label="Invoice"
                                        color="teal"
                                    />
                                    <v-radio
                                        :value="7"
                                        label="Outros"
                                        color="teal"
                                    />
                                </v-radio-group>
                            </v-flex>
                            <v-flex
                                sm12
                                md6
                            >
                                <v-text-field
                                    :rules="numeroRules"
                                    :readonly="modoVisualizacao"
                                    v-model="numero"
                                    label="NÚMERO DO DOCUMENTO *"
                                    outline
                                    @keypress="apenasNumeros"
                                />
                            </v-flex>
                            <v-flex
                                sm12
                                md6
                            >
                                <v-text-field
                                    :rules="serieRules"
                                    :readonly="modoVisualizacao"
                                    v-model="serie"
                                    label="SÉRIE *"
                                    outline
                                    @keypress="apenasNumeros"
                                />
                            </v-flex>
                            <v-flex
                                sm12
                                md6
                            >
                                <v-menu
                                    ref="dataDocumentoMenu"
                                    v-model="dataDocumentoPicker"
                                    :close-on-content-click="false"
                                    :nudge-right="40"
                                    :disabled="modoVisualizacao"
                                    lazy
                                    transition="scale-transition"
                                    offset-y
                                    full-width
                                    max-width="290px"
                                    min-width="290px"
                                >
                                    <v-text-field
                                        slot="activator"
                                        v-model="dataDocumentoFormatada"
                                        :rules="dataDocumentoRules"
                                        :hint="`*Início em: ${dataInicioFormatada} até ${dataFimFormatada}`"
                                        persistent-hint
                                        label="DATA DO DOCUMENTO *"
                                        placeholder="DD/MM/AAAA"
                                        append-icon="event"
                                        outline
                                        readonly
                                    />
                                    <v-date-picker
                                        ref="dataDocumentoPicker"
                                        v-model="dataDocumento"
                                        :min="dataInicio"
                                        :max="dataFim"
                                        no-title
                                        locale="pt-br"
                                        @change="save('dataDocumentoMenu')"
                                    />
                                </v-menu>
                            </v-flex>
                            <v-flex
                                sm12
                                md6
                            >
                                <v-menu
                                    ref="dataPagamentoMenu"
                                    v-model="dataPagamentoPicker"
                                    :close-on-content-click="false"
                                    :nudge-right="40"
                                    :disabled="modoVisualizacao"
                                    lazy
                                    transition="scale-transition"
                                    offset-y
                                    full-width
                                    max-width="290px"
                                    min-width="290px"
                                >
                                    <v-text-field
                                        slot="activator"
                                        v-model="dataPagamentoFormatada"
                                        :rules="dataPagamentoRules"
                                        label="DATA DO PAGAMENTO *"
                                        placeholder="DD/MM/AAAA"
                                        append-icon="event"
                                        outline
                                        readonly
                                    />
                                    <v-date-picker
                                        ref="dataPagamentoPicker"
                                        v-model="dataPagamento"
                                        :max="new Date().toISOString()"
                                        no-title
                                        locale="pt-br"
                                        @change="save('dataPagamentoMenu')"
                                    />
                                </v-menu>
                            </v-flex>
                            <v-flex
                                sm12
                                md6
                                lg3
                            >
                                <v-text-field
                                    v-money="money"
                                    id="valor"
                                    :hint="`*Máximo: R$ ${valorComprovar}`"
                                    :rules="valorRules"
                                    :readonly="modoVisualizacao"
                                    v-model.lazy="valor"
                                    label="VALOR *"
                                    persistent-hint
                                    outline
                                />
                            </v-flex>
                            <v-flex
                                xs12
                            >
                                <v-btn
                                    v-if="!modoVisualizacao"
                                    class="ml-0 d-inline-block"
                                    dark
                                    color="teal"
                                    @click="pickFile"
                                >
                                    COMPROVANTE *<v-icon right>attachment</v-icon>
                                </v-btn>
                                <v-text-field
                                    v-if="!modoVisualizacao"
                                    v-model="nomeArquivo"
                                    :rules="arquivoRules"
                                    :placeholder="nomeArquivo"
                                    class="d-inline-block"
                                    label="Selecionar arquivo..."
                                    readonly
                                    solo
                                    flat
                                    @click="pickFile"
                                />
                                <span
                                    v-if="modoVisualizacao"
                                    class="caption primary--text d-block"
                                >
                                    ARQUIVO
                                </span>
                                <v-btn
                                    v-if="modoVisualizacao"
                                    :href="`/upload/abrir/id/${dadosComprovante.idArquivo}`"
                                    class="ml-0"
                                    dark
                                    color="teal"
                                >
                                    {{ dadosComprovante.nmArquivo }}<v-icon right>get_app</v-icon>
                                </v-btn>
                                <input
                                    ref="inputComprovante"
                                    type="file"
                                    style="display: none;"
                                    accept=".pdf"
                                    @change="onFilePicked"
                                >
                            </v-flex>
                            <v-flex
                                xs12
                            >
                                <v-textarea
                                    :readonly="modoVisualizacao"
                                    v-model="justificativa"
                                    label="JUSTIFICATIVA"
                                    value=""
                                    placeholder="Digite aqui a justificativa."
                                    no-resize
                                    outline
                                />
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-form>
            </v-card-text>
            <v-card-actions v-if="!modoVisualizacao">
                <v-spacer/>
                <span
                    v-if="!valid"
                    align-center
                    justify-end
                    class="error--text caption mr-5"
                >
                    <v-icon class="error--text small mr-1">warning</v-icon>
                    Há campos inválidos e/ou não preenchidos!
                </span>
                <v-btn
                    :disabled="!valid"
                    flat
                    color="success"
                    @click="submit"
                >
                    Salvar
                </v-btn>
                <v-btn
                    flat
                    color="error"
                    @click="dialog = false"
                >
                    Cancelar
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import { VMoney } from 'v-money';
import { setTimeout } from 'timers';

export default {
    directives: { money: VMoney },
    props: {
        activator: { type: Boolean, default: false },
        idPlanilhaItens: { type: String, default: '' },
        idPlanilhaAprovacao: { type: String, default: '' },
        dataInicioFormatada: { type: String, default: '' },
        dataFimFormatada: { type: String, default: '' },
        dataInicio: { type: String, default: '' },
        dataFim: { type: String, default: '' },
        valorComprovar: { type: String, default: '0' },
    },
    data() {
        return {
            dialog: false,
            valid: true,
            modoEdicao: false,
            modoVisualizacao: false,

            pais: 1,
            nomeEmpresa: '',
            nomeEmpresaRules: [
                nomeEmpresa => !!nomeEmpresa || 'O campo nome da empresa é obrigatório!',
            ],
            endereco: '',
            enderecoRules: [
                endereco => !!endereco || 'O campo endereço é obrigatório!',
            ],
            tipoDocumento: 6,
            numero: '',
            numeroRules: [
                numero => !!numero || 'O campo número é obrigatório!',
            ],
            serie: '',
            serieRules: [
                serie => !!serie || 'O campo série é obrigatório!',
            ],
            dataDocumento: '',
            dataDocumentoPicker: false,
            dataDocumentoRules: [
                data => !!data || 'O campo data do documento é obrigatório!',
            ],
            dataPagamento: '',
            dataPagamentoPicker: false,
            dataPagamentoRules: [
                data => !!data || 'O campo data de pagamento é obrigatório!',
            ],
            valor: 'R$ 0,00',
            valorRules: [
                valor => (this.valorNumber(valor) > 0) || 'O campo valor é obrigatório e deve ser maior que 0(zero)!',
                valor => (this.valorNumber(valor) <= this.valorNumber(this.valorComprovar)) || 'O valor informado é maior que o valor a comprovar!',
            ],
            money: {
                decimal: ',',
                thousands: '.',
                prefix: 'R$ ',
                precision: 2,
            },
            nomeArquivo: '',
            arquivo: '',
            arquivoRules: [
                arquivo => !!arquivo || 'Anexar um arquivo de comprovante é obrigatório!',
            ],
            foiAtualizado: false,
            justificativa: '',
        };
    },
    computed: {
        ...mapGetters({
            paises: 'avaliacaoResultados/carregarPaises',
            status: 'avaliacaoResultados/statusComprovante',
            dadosComprovante: 'avaliacaoResultados/dadosComprovante',
        }),
        dataDocumentoFormatada() {
            if (!this.dataDocumento) return null;

            const [ano, mes, dia] = this.dataDocumento.split('-');
            return `${dia}/${mes}/${ano}`;
        },
        dataPagamentoFormatada() {
            if (!this.dataPagamento) return null;

            const [ano, mes, dia] = this.dataPagamento.split('-');
            return `${dia}/${mes}/${ano}`;
        },
    },
    watch: {
        activator() {
            this.dialog = true;
        },
        dataDocumentoPicker(val) {
            if (val) {
                setTimeout(() => {
                    this.$refs.dataDocumentoPicker.activePicker = 'YEAR';
                });
            }
        },
        dataPagamentoPicker(val) {
            if (val) {
                setTimeout(() => {
                    this.$refs.dataPagamentoPicker.activePicker = 'YEAR';
                });
            }
        },
        status() {
            this.dialog = false;
            setTimeout(this.reset, 2000, this.$refs.form.resetValidation);
            this.$root.$emit('recarregar-comprovantes');
        },
        dialog(value) {
            // Se a modal for fechada, limpar todos os campos do formulário
            if (!value) {
                setTimeout(this.reset, 500, this.$refs.form.resetValidation);
                document.getElementById('start').scrollTo(0, 0);
            }
        },
    },
    mounted() {
        this.carregarPaises();
        this.$root.$on('editar-comprovante', () => this.prepararEdicao());
        this.$root.$on('visualizar-comprovante', () => this.prepararVisualizacao());
    },
    methods: {
        ...mapActions({
            carregarPaises: 'avaliacaoResultados/carregarPaises',
            criarComprovante: 'avaliacaoResultados/criarComprovante',
            editarComprovante: 'avaliacaoResultados/editarComprovante',
        }),
        pickFile() {
            this.$refs.inputComprovante.click();
        },
        onFilePicked(e) {
            const arquivo = e.target.files[0];
            if (arquivo) {
                this.arquivo = arquivo;
                this.nomeArquivo = arquivo.name;
                this.foiAtualizado = true;
            }
        },
        save(picker, date) {
            this.$refs[picker].save(date);
        },
        apenasNumeros(e) {
            if (e.charCode < 48 || e.charCode > 57) {
                e.preventDefault();
            }
        },
        // Converte uma string de preço para um número flutuante
        valorNumber(number) {
            let string = number.replace(/R\$ /g, ''); // Retira prefixo R$
            string = string.replace(/\./g, ''); // Retira pontos
            string = string.replace(/,/g, '.'); // Transforma vírgulas em pontos
            return Number.parseFloat(string);
        },
        // Modo Edição
        prepararEdicao() {
            this.modoEdicao = true;
            this.preencherInputs();
            this.dialog = true;
        },
        // Modo Visualização
        prepararVisualizacao() {
            this.modoVisualizacao = true;
            this.preencherInputs();
            this.dialog = true;
        },
        preencherInputs() {
            const dados = this.dadosComprovante;
            // Encontrar código do país
            const paisObj = this.paises.find(pais => pais.nome === dados.fornecedor.nacionalidade);
            this.pais = paisObj.id;

            this.nomeEmpresa = dados.nmFornecedor;
            this.endereco = dados.endereco;
            this.tipoDocumento = parseInt(dados.tipo, 10);
            this.numero = dados.numero;
            this.serie = dados.serie;
            this.dataDocumento = dados.dataEmissao.slice(0, 10);
            this.dataPagamento = dados.dataPagamento.slice(0, 10);
            document.getElementById('valor').value = Number.parseFloat(dados.valor).toFixed(2);
            this.nomeArquivo = dados.nmArquivo;
            this.justificativa = dados.justificativa;
        },
        submit() {
            this.$refs.form.validate();

            if (this.valid) {
                const formData = new FormData();

                const comprovante = {
                    fornecedor: {
                        nacionalidade: this.paises[this.pais - 1].nome,
                        tipoPessoa: 1,
                        nome: this.nomeEmpresa,
                        eInternacional: true,
                        endereco: this.endereco,
                    },
                    arquivo: {
                        nome: this.nomeArquivo,
                        file: this.arquivo,
                    },
                    item: this.idPlanilhaItens,
                    idPlanilhaAprovacao: this.idPlanilhaAprovacao,
                    tipo: this.tipoDocumento,
                    numero: this.numero,
                    serie: this.serie,
                    dataEmissao: this.dataDocumentoFormatada,
                    dataPagamento: this.dataPagamentoFormatada,
                    forma: 1,
                    valor: this.valorNumber(this.valor),
                    valorAntigo: 0,
                    justificativa: this.justificativa,
                    foiAtualizado: this.foiAtualizado,
                };

                if (this.modoEdicao) {
                    comprovante.fornecedor.id = this.dadosComprovante.fornecedor.id;
                    comprovante['_index'] = parseInt(this.dadosComprovante.idComprovantePagamento, 10);
                    comprovante.id = parseInt(this.dadosComprovante.idComprovantePagamento, 10);
                    comprovante.idComprovantePagamento = parseInt(this.dadosComprovante.idComprovantePagamento, 10);
                    comprovante.valorAntigo = this.dadosComprovante.valor;
                }

                const comprovanteJSON = JSON.stringify(comprovante);
                formData.append('comprovante', comprovanteJSON);
                formData.append('arquivo', this.arquivo);

                if (this.modoEdicao) {
                    this.editarComprovante(formData);
                } else {
                    this.criarComprovante(formData);
                }
            }
        },
        reset(resetValidation) {
            this.pais = 1;
            this.nomeEmpresa = '';
            this.endereco = '';
            this.tipoDocumento = 6;
            this.numero = '';
            this.serie = '';
            this.dataDocumento = '';
            this.dataPagamento = '';
            document.getElementById('valor').value = 0;
            this.valor = 'R$ 0,00';
            this.nomeArquivo = '';
            this.arquivo = '';
            this.$refs.inputComprovante.value = '';
            this.foiAtualizado = false;
            this.justificativa = '';
            this.valid = true;
            this.modoEdicao = false;
            this.modoVisualizacao = false;
            resetValidation();
        },
    },
};
</script>
