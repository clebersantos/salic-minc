import AnalisarJustificativa from './components/AnalisarJustificativa';
import AnalisarAlteracoes from './components/AnalisarAlteracoes';

const PainelReadequacoesView = () => import(/* webpackChunkName: "painel-readequacoes-view" */ './views/PainelReadequacoesView');
const AnalisarReadequacaoView = () => import(/* webpackChunkName: "analisar-readequacao-view" */ './views/AnalisarReadequacaoView');

export default [
    {
        path: '/readequacao/painel/:idPronac',
        name: 'PainelReadequacoes',
        component: PainelReadequacoesView,
        meta: {
            title: 'Painel de Readequações',
        },
    },
    {
        path: '/readequacao/analisar/:idPronac/:idReadequacao',
        component: AnalisarReadequacaoView,
        meta: {
            title: 'Analisar Readequação',
        },
        children: [
            {
                path: '',
                name: 'analisar-justificativa',
                component: AnalisarJustificativa,
            },
            {
                path: 'alteracoes',
                name: 'analisar-alteracoes',
                component: AnalisarAlteracoes,
            },
        ],
    },
];
