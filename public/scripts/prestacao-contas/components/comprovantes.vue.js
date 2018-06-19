const comprovantes = {
    template: `
        <div>
            <ul class="collapsible" data-collapsible="accordion">
                <li v-for="(dado, index) in dados">
                  <div class="collapsible-header">
                      Fornecedor: {{dado.Descricao}} - {{dado.vlComprovacao}}
                      <span :class="['badge white-text', badgeCSS(dado.stItemAvaliado)]">
                        {{situacao(dado.stItemAvaliado)}}
                      </span>
                  </div>
                  <div :class="['collapsible-body lighten-5', badgeCSS(dado.stItemAvaliado)]">
                        <div class="card">
                            <div class="card-content">
                                <template v-if="!formVisivel" >
                                    <comprovante-table :dados="dado"></comprovante-table>
                                </template>
                                <button v-if="!formVisivel" v-on:click="mostrarForm()" class="btn">editar</button>
                                <template v-if="formVisivel" >
                                    <sl-comprovar-form :dados="dado"></sl-comprovar-form>
                                </template>
                            </div>
                        </div>
                  </div>
                </li>
            </ul>
        </div>
    `,
    components:{
        'comprovante-table': comprovanteTable,
        // 'componenteform': slComprovarForm
    },
    props: [
        'idpronac',
        'produto',
        'stitemavaliado',
        'uf',
        'idmunicipio',
        'idplanilhaitem',
        'etapa',
        'componenteform',
    ],
    mounted: function() {
        console.log('teste') ;

        var vue = this;
        url = '/prestacao-contas/comprovante-pagamento';
        $3.ajax({
          type: "GET",
          url:url,
          data:{
              idPronac: this.idpronac,
              idPlanilhaItem: this.idplanilhaitem,
              produto: this.produto,
              uf: this.uf,
              idmunicipio: this.idmunicipio,
              etapa: this.etapa,
          }
        })
        .done(function(data) {
            vue.$data.dados = data.data;
        })
        .fail(function(jqXHR) {
            alert('error');
        });
    },
    methods:{
        badgeCSS: function(id) {
            if (id == 1) {
                return {
                    'green ': true,
                }
            }
            if (id == 3) {
                return {
                    ' red': true,
                }
            }
            if (id == 4) {
                return {
                    'grey': true,
                }
            }
        },
        situacao: function(id) {
            estado = null;
            switch(parseInt(id)) {
                case 1:
                     estado = 'Aprovado';
                    break;
                case 3:
                    estado =  'Recusado'
                    break;
                default:
                    estado =  'N\xE3o avaliado';
            }
            return estado;
        },
        mostrarForm: function() {
            this.formVisivel = true;
        }
    },
    data: function(){
        return {
            dados:{},
            formVisivel: false
        }
    }
}
