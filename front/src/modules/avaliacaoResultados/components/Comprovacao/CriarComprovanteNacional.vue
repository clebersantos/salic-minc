<template>
    <v-dialog
        v-model="dialog"
        scrollable
    >
        <v-tooltip
            slot="activator"
            left
        >
            <v-btn
                slot="activator"
                color="teal"
                dark
                fixed
                bottom
                right
                fab
                @click="valid = true"
            >
                <v-icon>add</v-icon>
            </v-btn>
            <span>Criar Comprovante</span>
        </v-tooltip>
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
                            IDENTIFICAÇÃO DO CONTRATADO
                        </h3>
                        <v-layout
                            row
                            wrap
                        >
                            <v-flex
                                v-if="!modoVisualizacao"
                                xs12
                            >
                                <v-radio-group
                                    v-model="cpfCnpjLabel"
                                    label="TIPO DO FORNECEDOR"
                                >
                                    <v-radio
                                        label="CNPJ"
                                        value="CNPJ"
                                        color="teal"
                                    />
                                    <v-radio
                                        label="CPF"
                                        value="CPF"
                                        color="teal"
                                    />
                                </v-radio-group>
                            </v-flex>
                            <v-flex
                                sm12
                                md3
                            >
                                <v-text-field
                                    :label="`${cpfCnpjLabel} *`"
                                    :rules="cpfCnpjRules"
                                    v-model="cpfCnpj"
                                    :mask="cpfCnpjMask"
                                    :placeholder="cpfCnpjPlaceholder"
                                    :readonly="modoVisualizacao"
                                    :append-icon="modoVisualizacao ? undefined : 'search'"
                                    return-masked-value
                                    validate-on-blur
                                    outline
                                    @click:append="buscarFornecedor(cpfCnpj)"
                                    @keyup.enter="buscarFornecedor(cpfCnpj)"
                                    @blur="buscarFornecedor(cpfCnpj)"
                                />
                            </v-flex>
                            <v-flex
                                sm12
                                md6
                            >
                                <v-text-field
                                    :label="nomeRazaoSocialLabel"
                                    :value="nomeRazaoSocial"
                                    outline
                                    readonly
                                />
                            </v-flex>
                            <v-flex
                                sm12
                            >
                                <div v-if="!agenteEhCadastrado && buscouFornecedor">
                                    <span class="error--text caption d-block ml-2 md-1">Fornecedor não cadastrado!</span>
                                    <v-btn
                                        :href="`/prestacao-contas/fornecedor/index/cpfcnpj/${cpfCnpj}`"
                                        color="teal"
                                        dark
                                    >
                                        CADASTRAR FORNECEDOR
                                    </v-btn>
                                </div>
                            </v-flex>
                        </v-layout>

                        <h3 class="my-2">DADOS DO COMPROVANTE DE DESPESA</h3>
                        <v-layout
                            row
                            wrap
                        >
                            <v-flex
                                sm12
                                md3
                            >
                                <v-select
                                    :items="tipoComprovanteOpcoes"
                                    :readonly="modoVisualizacao"
                                    v-model="tipoComprovante"
                                    item-value="value"
                                    item-text="text"
                                    label="TIPO COMPROVANTE *"
                                    outline
                                />
                            </v-flex>

                            <v-flex
                                sm12
                                md3
                            >
                                <v-menu
                                    ref="dataEmissaoMenu"
                                    v-model="dataEmissaoPicker"
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
                                        v-model="dataEmissaoFormatada"
                                        :rules="dataEmissaoRules"
                                        :hint="`*Início em: ${dataInicioFormatada} até ${dataFimFormatada}`"
                                        persistent-hint
                                        label="DATA DA EMISSÃO *"
                                        placeholder="DD/MM/AAAA"
                                        append-icon="event"
                                        outline
                                        readonly
                                    />
                                    <v-date-picker
                                        ref="dataEmissaoPicker"
                                        v-model="dataEmissao"
                                        :min="dataInicio"
                                        :max="dataFim"
                                        no-title
                                        locale="pt-br"
                                        @change="save('dataEmissaoMenu')"
                                    />
                                </v-menu>
                            </v-flex>
                            <v-flex
                                sm12
                                md3
                            >
                                <v-text-field
                                    :rules="numeroRules"
                                    :readonly="modoVisualizacao"
                                    v-model="numero"
                                    label="NÚMERO *"
                                    placeholder="00000000"
                                    outline
                                    @keypress="apenasNumeros"
                                />
                            </v-flex>
                            <v-flex
                                sm12
                                md3
                            >
                                <v-text-field
                                    :readonly="modoVisualizacao"
                                    v-model="serie"
                                    label="SÉRIE"
                                    placeholder="00000000000"
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
                        </v-layout>
                        <h3 class="my-2">DADOS DO COMPROVANTE BANCÁRIO</h3>
                        <v-layout
                            row
                            wrap
                        >
                            <v-flex
                                sm12
                                md6
                                lg3
                            >
                                <v-select
                                    :items="formaPagamentoOpcoes"
                                    :readonly="modoVisualizacao"
                                    v-model="formaPagamento"
                                    item-value="value"
                                    item-text="text"
                                    label="FORMA DE PAGAMENTO *"
                                    outline
                                />
                            </v-flex>
                            <v-flex
                                sm12
                                md6
                                lg3
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
                                        label="DATA PAGAMENTO *"
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
                                    :rules="numeroRules"
                                    :readonly="modoVisualizacao"
                                    v-model="numeroDocumentoPagamento"
                                    label="Nº DOCUMENTO PAGAMENTO *"
                                    placeholder="00000000000"
                                    outline
                                    @keypress="apenasNumeros"
                                />
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
                        </v-layout>
                        <h3 class="my-2">JUSTIFICATIVA</h3>
                        <v-layout
                            row
                            wrap
                        >
                            <v-flex
                                xs12
                            >
                                <v-textarea
                                    :readonly="modoVisualizacao"
                                    v-model="justificativa"
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
                    :disabled="!valid || buscandoFornecedor"
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
import { validadorCpf } from '@/mixins/validadorCpf';
import { validadorCnpj } from '@/mixins/validadorCnpj';
import { setTimeout } from 'timers';

export default {
    directives: { money: VMoney },
    mixins: [validadorCpf, validadorCnpj],
    props: {
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

            cpfCnpjLabel: 'CNPJ',
            cpfCnpj: '',
            cpfMask: '###.###.###-##',
            cnpjMask: '##.###.###/####-##',
            cpfPlaceholder: '000.000.000-00',
            cnpjPlaceholder: '00.000.000/0000-00',
            cpfRules: [
                cpf => !!cpf || 'O campo CPF é obrigatório!',
                cpf => this.validarCpf(cpf) || 'O CPF informado não é válido!',
            ],
            cnpjRules: [
                cnpj => !!cnpj || 'O campo CNPJ é obrigatório!',
                cnpj => this.validarCnpj(cnpj) || 'O CNPJ informado não é válido!',
            ],
            buscandoFornecedor: false,

            tipoComprovante: 1,
            tipoComprovanteOpcoes: [
                { value: 1, text: 'Cupom Fiscal' },
                { value: 2, text: 'Guia de Recolhimento' },
                { value: 3, text: 'Nota Fiscal/Fatura' },
                { value: 4, text: 'Recibo de Pagamento' },
                { value: 5, text: 'RPA' },
            ],
            dataEmissao: '',
            dataEmissaoPicker: false,
            dataEmissaoRules: [
                data => !!data || 'O campo data de emissão é obrigatório!',
            ],
            numero: '',
            numeroRules: [
                numero => !!numero || 'O campo Número é obrigatório!',
            ],
            serie: '',
            nomeArquivo: '',
            arquivo: '',
            arquivoRules: [
                arquivo => !!arquivo || 'Anexar um arquivo de comprovante é obrigatório!',
            ],
            foiAtualizado: false,

            formaPagamento: 1,
            formaPagamentoOpcoes: [
                { value: 1, text: 'Cheque' },
                { value: 2, text: 'Transferência Bancária' },
                { value: 3, text: 'Saque/Dinheiro' },
            ],
            dataPagamento: '',
            dataPagamentoPicker: false,
            dataPagamentoRules: [
                data => !!data || 'O campo data de pagamento é obrigatório!',
            ],
            numeroDocumentoPagamento: '',
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
            justificativa: '',
        };
    },
    computed: {
        ...mapGetters({
            agente: 'avaliacaoResultados/buscarAgente',
            status: 'avaliacaoResultados/statusComprovante',
            dadosComprovante: 'avaliacaoResultados/dadosComprovante',
        }),
        cpfCnpjMask() {
            return this.cpfCnpjLabel === 'CNPJ' ? this.cnpjMask : this.cpfMask;
        },
        cpfCnpjPlaceholder() {
            return this.cpfCnpjLabel === 'CNPJ' ? this.cnpjPlaceholder : this.cpfPlaceholder;
        },
        cpfCnpjRules() {
            return this.cpfCnpjLabel === 'CNPJ' ? this.cnpjRules : this.cpfRules;
        },
        nomeRazaoSocialLabel() {
            return this.cpfCnpjLabel === 'CNPJ' ? 'RAZÃO SOCIAL' : 'NOME';
        },
        nomeRazaoSocial() {
            if (this.modoEdicao || this.modoVisualizacao) {
                return this.dadosComprovante.nome;
            }
            return this.agenteEhCadastrado ? this.agente[0].agente.nome : '';
        },
        cpfCnpjParams() {
            return { cpf: this.cpfCnpj };
        },
        dataEmissaoFormatada() {
            if (!this.dataEmissao) return null;

            const [ano, mes, dia] = this.dataEmissao.split('-');
            return `${dia}/${mes}/${ano}`;
        },
        dataPagamentoFormatada() {
            if (!this.dataPagamento) return null;

            const [ano, mes, dia] = this.dataPagamento.split('-');
            return `${dia}/${mes}/${ano}`;
        },
        buscouFornecedor() {
            return this.agente.length > 0;
        },
        agenteEhCadastrado() {
            return this.agente.length > 0 && this.agente[0].msgCPF === 'cadastrado';
        },
    },
    watch: {
        dataEmissaoPicker(val) {
            if (val) {
                setTimeout(() => {
                    this.$refs.dataEmissaoPicker.activePicker = 'YEAR';
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
        cpfCnpj() {
            this.buscandoFornecedor = true;
        },
        agente(val) {
            if (val.length > 0 && val[0].msgCPF === 'cadastrado') {
                this.valid = true;
            } else {
                this.valid = false;
            }
            this.buscandoFornecedor = false;
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
        this.$root.$on('editar-comprovante', () => this.prepararEdicao());
        this.$root.$on('visualizar-comprovante', () => this.prepararVisualizacao());
    },
    methods: {
        ...mapActions({
            buscarAgente: 'avaliacaoResultados/buscarAgente',
            criarComprovante: 'avaliacaoResultados/criarComprovante',
            editarComprovante: 'avaliacaoResultados/editarComprovante',
            limparAgente: 'avaliacaoResultados/limparAgente',
        }),
        buscarFornecedor(cpfCnpj) {
            if (this.modoVisualizacao) {
                return;
            }
            if (this.cpfCnpjLabel === 'CNPJ') {
                if (!this.validarCnpj(cpfCnpj)) {
                    return;
                }
            } else if (!this.validarCpf(cpfCnpj)) {
                return;
            }

            this.buscarAgente(this.cpfCnpjParams);
        },
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
            this.cpfCnpjLabel = dados.CNPJCPF.length > 11 ? 'CNPJ' : 'CPF';
            this.cpfCnpj = dados.CNPJCPF;
            this.tipoComprovante = parseInt(dados.tipo, 10);
            this.dataEmissao = dados.dataEmissao.slice(0, 10);
            this.numero = dados.numero;
            this.serie = dados.serie;
            this.nomeArquivo = dados.nmArquivo;
            this.formaPagamento = parseInt(dados.forma, 10);
            this.dataPagamento = dados.dataPagamento.slice(0, 10);
            this.numeroDocumentoPagamento = dados.numeroDocumento;
            document.getElementById('valor').value = Number.parseFloat(dados.valor).toFixed(2);
            this.justificativa = dados.justificativa;
        },
        submit() {
            this.buscarFornecedor(this.cpfCnpj);
            this.$refs.form.validate();

            if (this.valid && this.agenteEhCadastrado) {
                const formData = new FormData();

                const comprovante = {
                    fornecedor: {
                        nacionalidade: 31,
                        tipoPessoa: this.cpfCnpjLabel === 'CPF' ? 1 : 2,
                        CNPJCPF: this.cpfCnpj.replace(/[/.-]/g, ''),
                        cnpjcpfMask: this.cpfCnpj,
                        nome: this.nomeRazaoSocial,
                        idAgente: this.agente[0].idAgente,
                        eInternacional: false,
                    },
                    arquivo: {
                        nome: this.nomeArquivo,
                        file: this.arquivo,
                    },
                    item: this.idPlanilhaItens,
                    idPlanilhaAprovacao: this.idPlanilhaAprovacao,
                    tipo: this.tipoComprovante,
                    numero: this.numero,
                    serie: this.serie,
                    dataEmissao: this.dataEmissaoFormatada,
                    dataPagamento: this.dataPagamentoFormatada,
                    forma: this.formaPagamento,
                    numeroDocumento: this.numeroDocumentoPagamento,
                    valor: this.valorNumber(this.valor),
                    valorAntigo: 0,
                    valorPermitido: this.valorNumber(this.valorComprovar),
                    justificativa: this.justificativa,
                    foiAtualizado: this.foiAtualizado,
                };

                if (this.modoEdicao) {
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
            this.cpfCnpjLabel = 'CNPJ';
            this.cpfCnpj = '';
            this.tipoComprovante = 1;
            this.dataEmissao = '';
            this.numero = '';
            this.serie = '';
            this.nomeArquivo = '';
            this.arquivo = '';
            this.$refs.inputComprovante.value = '';
            this.foiAtualizado = false;
            this.formaPagamento = 1;
            this.dataPagamento = '';
            this.numeroDocumentoPagamento = '';
            document.getElementById('valor').value = 0;
            this.valor = 'R$ 0,00';
            this.justificativa = '';
            this.limparAgente();
            this.modoEdicao = false;
            this.modoVisualizacao = false;
            resetValidation();
        },
    },
};
</script>
