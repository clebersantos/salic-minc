/**
 * Plugins jQuery
 */
(function ($) {
    $(document).ready(function ($) {
        var elmBody = $('body');

        // Adicionando evento do ajax modal.
        elmBody.on('click', '[data-ajax-modal]', function () {
            var objConfig = {};
            objConfig.strUrl = $(this).attr('data-ajax-modal');
            if ($(this).attr('data-ajax-modal-type') !== '') {
                objConfig.strType = $(this).attr('data-ajax-modal-type');
            }
            if ($(this).attr('data-ajax-modal-height') !== '') {
                objConfig.strHeight = $(this).attr('data-ajax-modal-height');
            }
            $.ajaxModal(objConfig);
        });

        // Adicionando evento ao select que renderiza outro.
        //@todo criar um plugin especifico para o select.
        elmBody.on('change', 'select[data-ajax-render]', function () {
            var elm = $(this),
                strUrl = elm.attr('data-ajax-render'),
                objTarget = $(elm.attr('data-ajax-target')),
                strVal = elm.val(),
                strValChecked = '';
            var strHtml = '';
            $3.ajax({
                method: "POST",
                url: strUrl,
                data: {id: strVal}
            }).done(function (result) {
                json = $3.parseJSON(result);
                strHtml = '<option value="" selected>Selecione...</option>';
                $3.each(json, function(key, value){
                    strHtml += '<optgroup label="' + key + '">';
                    $3.each(value, function(key2, value2){
                        if (strValChecked && strValChecked == key2) {
                            strHtml += '<option value="' + key2 + '" selected="selected">' + value2 + '</option>';
                        } else {
                            strHtml += '<option value="' + key2 + '">' + value2 + '</option>';
                        }
                    });
                    strHtml += '</optgroup>';
                });
                objTarget.html(strHtml);
                objTarget.material_select();
            });
        });

        // Adicionando evento de renderizar automaticamente o ajax no elemento.
        $.each($('div[data-ajax-render]'), function () {
            $.ajaxRender({strUrl: $(this).attr('data-ajax-render'), strTarget: '#' + $(this).attr('id')});
        });

        // Adicionando evento de enviar o formulario via ajax caso tenha a tag data-ajax-form.
        // @todo verificar uma forma legal para usar com o card da tela de login.
        elmBody.on('submit', 'form[data-ajax-form]', function(){
            strAnimate = 'jello';
//            strAnimate = 'wobble';
//            strAnimate = 'pulse';
            if ($(this).ajaxFormSubmit()) {
                $('.card').removeClass('fadeInUp');
                $('.card').removeClass('animated');
                $('.card').addClass(strAnimate);
                $('.card').addClass('animated');
                $('.card').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                $('.card').removeClass(strAnimate);
                $('.card').removeClass('animated');
                });
            } else {
                $('.card').removeClass('fadeInUp');
                $('.card').removeClass('animated');
                $('.card').addClass(strAnimate);
                $('.card').addClass('animated');
                $('.card').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
                $('.card').removeClass(strAnimate);
                $('.card').removeClass('animated');
                });
            }
            return false;
        });
    });

    // Pegando eventos ajax e exibindo informacao de carregamento.
    $(document).ajaxStart(function () {
        $('#container-progress').fadeIn('slow');
    });
    $(document).ajaxComplete(function () {
        $('.container').fadeIn(1500);
        $('#container-progress').fadeOut('slow');
    });

    /**
     * Cria uma div modal, executa um ajax renderizando o retorno dentro da modal e no final abre a modal com o conteudo renderizado.
     *
     * @param objOption - Objeto json de configuracoes.
     * @param objOption.strUrl - Url da tela que sera renderizado pelo ajax.
     * @param objOption.strIdModal - Titulo da modal.
     * @param callback - Funcao callback onde e executado apos terminar o ajax.
     *
     * @author Ruy Junior Ferreira Silva <ruyjfs@gmail.com>
     * @since 28/12/2016
     */
    $.ajaxModal = function (objOption, callback) {
        var objDefaults = {strUrl: '', strIdModal: 'modal', strType: 'modal-fixed-footer', strHeight: ''},
            objSettings = $.extend({}, objDefaults, objOption),
            strIdModal = '#' + objSettings.strIdModal;
        // Removendo e criando elemento div para o modal.
        $(strIdModal).remove();
        $('body').append('<div id="' + objSettings.strIdModal + '" class="modal ' + objSettings.strType + '" style="height: '+ objSettings.strHeight +'"></div>');
        $(strIdModal).modal();

        // Renderizando ajax e abrindo a modal por callback.
        objSettings.strTarget = strIdModal;
        $.ajaxRender(objSettings, function () {
            if (typeof callback == 'function') {
                callback.call(this);
            }
            $(strIdModal).modal('open');
        });
    };

    /**
     * Cria uma div modal, executa um ajax renderizando o retorno dentro da modal e no final abre a modal com o conteudo renderizado.
     *
     * @param objOption - Objeto json de configuracoes.
     * @param objOption.strUrl - Url da tela que sera renderizado pelo ajax.
     * @param objOption.strTarget - String da tag html (id, class, entre outros) para ser pego com jquery.
     * @param callback - Funcao callback onde e executado apos terminar o ajax.
     *
     * @author Ruy Junior Ferreira Silva <ruyjfs@gmail.com>
     * @since 28/12/2016
     */
    $.ajaxRender = function (objOption, callback) {
        var objDefaults = {strUrl: '', strTarget: ''},
            objSettings = $.extend({}, objDefaults, objOption),
            elmRender = $(objSettings.strTarget),
            elmProgress = $('#container-progress-part').clone();

        var time = true;
        setTimeout(function(){
            if (time) {
                elmRender.hide().html('<div class="center-align" style="height: 100%;"><div class="preloader-wrapper big active valign ">' + elmProgress.html() + '</div></div>').fadeIn();
            }
        }, 250);
        $.ajax({url: objSettings.strUrl}).done(function (result) {
            // setTimeout(function () {
                time = false;
                elmRender.hide().html(result).fadeIn('slow');
                if (typeof callback == 'function') {
                    callback.call(null, result);
                }
            // }, 2000)
        });
    };

    /**
     *
     * @param callback - Funcao callback onde e executado apos terminar o ajax.
     * @see ajaxFormSubmit
     */
    $.fn.ajaxFormSubmit = function(callback) {
        return $.ajaxFormSubmit({form: $(this)}, callback);
    };

    /**
     * Envia o formulario via ajax, preparando o retorno para exibir as mensagens no toast e redirecionando caso seja informado pelo retorno do ajax.
     * Obs:
     * Json esperado para retorno do ajax: {
     *      status, - Status da execucao do ajax: true ou false.
     *      msg, - String com a mensagem ou array associativo de mensagens, onde a chave e o name do input e o value do array e a mensagem.
     *      redirect - Url para redirecionar a pagina apos executar o ajax e o status for true;
     * }
     *
     * @param objOption - Objeto json de configuracoes.
     * @param objOption.form - Elemento ou o seletor do formulario para o envio.
     * @param callback - Funcao callback onde e executado apos terminar o ajax.
     *
     * @author Ruy Junior Ferreira Silva <ruyjfs@gmail.com>
     * @since 10/01/2017
     */
    $.ajaxFormSubmit = function (objOption, callback) {
        var objDefaults = {form: ''},
            objSettings = $.extend({}, objDefaults, objOption),
            elmForm = '',
            booReturn = false;

        if (typeof objSettings.form == 'string') {
            elmForm = $(objSettings.form);
        } else if (typeof objSettings.form == 'object') {
            elmForm = objSettings.form;
        } else {
            console.info('Precisa passar o elemento ou o selector do formulario como parametro!');
            return false;
        }

        if (elmForm.valid()) {
            $(elmForm).ajaxSubmit({
                beforeSerialize: function($form, options) {
                    tinyMCE.triggerSave();
                },
                success: function(result) {
                    var result = $3.parseJSON(result);
                    if (result.status == '1') {
                        Materialize.toast(result.msg, 4000, 'green light-green accent-1 black-text');
                        setTimeout(function(){
                            if (typeof result.redirect != 'undefined') {
                                window.location.href = result.redirect;
                            }
                        }, 500);
                        booReturn = true;
                    } else {
                        if (typeof result.msg != 'string') {
                            $3.each(result.msg, function(strNameElement, strMsg){
                                var elm = $3('[name=' + strNameElement + ']'),
                                    elmLabel = elm.closest('div.input-field').find('label'),
                                    strLabel = elm.closest('div.input-field').find('label').html();
                                if (elm.length > 0) {
                                    elm.focus();
                                    Materialize.toast('Campo ' + strLabel + ' &eacute; obrigat&oacute;rio!', 8000, 'red accent-1');
                                }
                            });
                        } else {
                            Materialize.toast(result.msg, 4000, 'red accent-1');
                        }
                    }
                    if (typeof callback == 'function') {
                        callback.call(null, booReturn);
                    }
                }
            });
        }
    };

    /**
     * Redireciona para a url informada adicionando a url base.
     *
     * @param strUrl
     *
     * @author Ruy Junior Ferreira Silva <ruyjfs@gmail.com>
     * @since 10/01/2017
     */
    $.redirect = function(strUrl) {
        // var elmContainer = $($('.container')[1]);
        // elmContainer.removeClass('fadeIn');
        // elmContainer.removeClass('animated');
        // strAnimate = 'fadeOutDown';
//            strAnimate = 'jello';
//            strAnimate = 'wobble';
//            strAnimate = 'pulse';
//            strAnimate = 'fadeOut';
//         elmContainer.addClass(strAnimate);
        // elmContainer.addClass('animated');
        // elmContainer.one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
            window.location.href = strUrl;
        // });
    };

    /**
     * Abre modal com botoes de confirmacao.
     *
     * @name modalConfirm
     * @param objOption.mixText - Conteudo que fica no centro da modal.
     * @param objOption.strTitle - Titulo da modal.
     * @param objOption.intWidth - Largura da modal.
     * @param objOption.intHeight - Altura da modal.
     * @param objOption.arrButtons - Array com os botoes da tela.
     * @param objOption.arrButtons.[0].title - .
     * @param objOption.arrButtons.[0].description - .
     * @param objOption.arrButtons.[0].class - .
     * @param objOption.arrButtons.[0].callback - .
     *
     * @todo terminar futuramente.
     */
    $.modalConfirm = function( objOption ) {
        var objDefaults = {
                'mixText' : '',
                'strTitle': 'Aviso!',
                'intWidth': '500',
                'intHeight': '200',
                'arrButtons': [
                    {title: '', description: '', class: ''},
                    {title: '', description: '', class: ''}
                ]
            },
            objSettings = $.extend( {}, objDefaults, objOption );

        $(strIdModal).remove();
        $('body').append('<div id="modal-" class="modal ' + objSettings.strType + '" style="height: '+ objSettings.strHeight +'"></div>');
        // Gerar id dinamicamente.
        var elmModal = $('#modal-');

        elmModal.modal();

        // Abrindo a modal por callback.
        // objSettings.strTarget = strIdModal;
        // if (typeof callback == 'function') {
        //     callback.call(this);
        // }
        // Adicionar conteudo na modal.
        // elmModal.html('');
        elmModal.modal('open');
        // elmModal.modal('close');
    };
}($3));