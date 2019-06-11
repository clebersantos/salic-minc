import Const from '../const';

export default {
    methods: {
        validarFormulario(readequacao, contador, minChar, campo = '') {
            const valido = {
                solicitacao: false,
                justificativa: false,
            };
            if (typeof readequacao.dsJustificativa === 'string') {
                if (contador.justificativa >= minChar.justificativa) {
                    valido.justificativa = true;
                }
            }
            if (typeof readequacao.dsSolicitacao === 'string'
                && readequacao.dsSolicitacao.trim() !== ''
            ) {
                if (typeof readequacao.idTipoReadequacao !== 'undefined') {
                    if (readequacao.idTipoReadequacao === Const.TIPO_READEQUACAO_PERIODO_EXECUCAO) {
                        /* eslint-disable no-param-reassign */
                        if (campo.includes('/')) {
                            const [day, month, year] = campo.split('/');
                            campo = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
                        }
                        if (campo !== ''
                            && readequacao.dsSolicitacao.trim() !== campo) {
                            if (contador.solicitacao >= minChar.dataExecucao) {
                                valido.solicitacao = true;
                            }
                        }
                    } else if (contador.solicitacao >= minChar.solicitacao) {
                        valido.solicitacao = true;
                    }
                }
            }
            return (valido.solicitacao && valido.justificativa);
        },
        validarItemPlanihla(item, contador, minChar) {
            const valido = {
                justificativa: false,
            };
            if (typeof item.dsJustificativa === 'string') {
                if (contador.justificativa >= minChar.justificativa) {
                    valido.justificativa = true;
                }
            }
            return (valido.justificativa);
        },
    },
};
