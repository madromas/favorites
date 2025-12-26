var icms = icms || {};

icms.favorites = (function ($) {
    
    this.options = {
        
        urls: {
            favorites:      '/favorites',               // корень
            autocomplete:   '/favorites/autocomplete'   // автокомплит тегов
        },

        selectors: {
            comma:      '.fav_comma',                   // символ "," в строке тегов tags_bar
            tag:        '.fav_tag',                     // пользовательский тег
            edit_tag: 	'.fav_edit_tags',               // ссылка редактирования
            paginator:  '.favorite_info_pagination',    // блок пагинации списка пользователей (info.tpl)

            form: {                             // (tags_form.tpl)
                tag_form:   '#fav_tag_form',    // форма редактирования пользовательских тегов
                tag_input:  '.tags_string',     // строка ввода тегов
                close:      '.fav_close'        // закрытие формы
            },

            widget: {                           // (favorite_widget.tpl)
                widget: '.favorite_widget',     // виджет добавления избранного
                count:  '.fav_count'            // количество добавлений в избранное
            }
        },
        
        data: {
            action:     'action',               // выполняемое действие
            favid:      'fav-id',               // id избранного
            controller: 'target-controller',    // контроллер компонента
            subject:    'target-subject',       // тип контента
            id:         'target-id',            // id записи контента
            url:        'url',                  // url-адрес
            page:       'page'                  // текущая страница
        }
        
    }

    this.setOptions = function(options){
        $.extend(true, this.options, options);
    }

    //========================================================================//

    this.onDocumentReady = function(){

        // получаем объект формы ввода пользовательских тегов
        var $tag_form = $(this.options.selectors.form.tag_form);

        if ($tag_form.length){
            
            // включаем автокомплит для тегов
            icms.favorites.tagsFormAutocomplete($tag_form);

            // вешаем обработчик формы сохранения тегов
            icms.favorites.tagsFormSubmit($tag_form);

            // устанавливаем обработчик закрытия формы добавления пользовательских тегов
            $($tag_form).on('click', this.options.selectors.form.close, function(){
                
                icms.favorites.closeTagsForm($tag_form);
                
            });

        }

    }

    /*
     * Возвращает jQuery объект строки тегов tags_bar в записи или списке записей
     * @param jQuery object or string object
     * @param string target_subject
     * @returns jQuery object of '.tags_bar'
     */
    this.getTagsBar = function(object, target_subject){  

        // если tags_bar родитель object возвращаем его
        if ($(object).parent('.tags_bar').length !== 0)
                    return $(object).parent('.tags_bar');

        // если контроллер не content, тегов у него нет
        if (!target_subject) return false;

        // формируем класс родителя строки тегов tags_bar
        var item_block_name = '.'+target_subject+'_item';
        
        if ( $(item_block_name).length == 0 )
                    item_block_name = '.'+target_subject+'_list_item';

        // возвращаем объект tags_bar
        return $(object)
                    .closest(item_block_name)
                    .children('.tags_bar');

    }

    /*
     * Добавление/удаление записи в список избранного пользователя
     * @param object link
     * @returns boolean
     */
    this.toggleFavorite = function(link){

        $link = $(link);
        
        var _this = this;
        
        // предотвращаем множественное нажатие
        if ($link.hasClass('disabled')) return false;
        
        // получаем объект виджета избранного
        var $widget = $link.parent( this.options.selectors.widget.widget );

        // получаем данные избранной записи
        var target_controller = $widget.data( this.options.data.controller );
        var target_subject    = $widget.data( this.options.data.subject );
        var target_id         = $widget.data( this.options.data.id );

        // получаем объект счетчика избранного и данные этого счетчика
        var $fav_count = $(this.options.selectors.widget.count, $widget);
        var count = isNaN( parseInt($fav_count.text()) ) ? 0 : parseInt( $fav_count.text() );

        // получаем объект строки тегов и html-строку тегов
        var $tags_bar = icms.favorites.getTagsBar($widget, target_subject);
        var tags_html = $tags_bar ? $tags_bar.html() : '';

        // получаем действие виджета избранного (add/delete)
        var action = $link.data( this.options.data.action );
        if (action === 'delete'){
            // при удалении берем id избранного
            var fav_id = $link.data( this.options.data.favid );
        }

        // отключаем ссылку избранного на время обработки данных
        $link.addClass('disabled');

        // отправляем запрос на обработку
        $.post( this.options.urls.favorites + '/' + action, {
            
            tc:     target_controller,  // компонент
            ts:     target_subject,     // тип контента
            ti:     target_id,          // id записи
            fav_id: fav_id              // id избранного
                
        }, function(result){  // обрабатываем полученный результат

            // выводим сообщение в случае ошибки
            if (result == null || typeof(result) == 'undefined' || result.error) {
                $.jGrowl(result.message, { theme: 'error' });
                $link.removeClass('disabled');
                return false;
            }

            // показываем сообщение об успешном выполнении запроса
            $.jGrowl(result.message, { theme: 'info' });

            // настраиваем ссылку избранного
            $link
                .toggleClass('fav_add')     // меняем класс ссылки избранного...
                .toggleClass('fav_delete')  // на противоположный
                .data(_this.options.data.action, action === 'add' ? 'delete' : 'add')  // устанавливаем action
                .attr('title', result.link_title);  // устанавливаем подсказку

            // пересчитываем количество избранного
            action === 'add' ? count++ : count--;
            $fav_count.text( count > 0 ? count : '');

            // если запрос был на доавление в избранное
            if (action === 'add') {

                // устанавливаем id избранного
                $link.data(_this.options.data.favid, result.fav_id);

                // если избранная запись относится к контенту и содержит теги
                if (target_subject && $(_this.options.selectors.edit_tag, $tags_bar).length == 0) {
                    // чистим строку тегов от конечных пробелов
                    $tags_bar.html( $.trim($tags_bar.html()) );
                    // устанавливаем ссылку на добавление пользовательских тегов
                    $tags_bar.append('<a href="javascript:void(0);" class="fav_edit_tags" onclick="return icms.favorites.showTagsForm(this, \'' + target_subject + "', " + target_id + ');">'+_this.options.lang.edit_tags+'</a>');
                }

            } 

            // если запрос был на удаление из избранного
            if (action === 'delete') {

                // если избранная запись была контентом удаляем пользователские теги
                if (target_subject) {
                    $( _this.options.selectors.edit_tag, $tags_bar ).remove();                        
                    $( _this.options.selectors.comma,    $tags_bar ).remove();
                    $( _this.options.selectors.tag,      $tags_bar ).remove();
                    $( _this.options.selectors.form.tag_form ).hide();
                }

            }

            // оповещаем слушателей события об изменении избранного
            icms.events.run('icms_favorites_toggle', {
                widget:  $widget,
                action:  action,
                tags:    tags_html
            });

            // активируем ссылку избранного
            $link.removeClass('disabled');

        }, 'json');

        return false;

    }

    /*
     * Показывает форму редактирования пользовательских тегов привязанную
     * к записи target_id типа target_subject
     * @param object link
     * @param string target_subject
     * @param string target_id
     */
    this.showTagsForm = function(link, target_subject, target_id){

        // закрываем ранее открытую форму
        icms.favorites.closeTagsForm();
        
        var $tag_form = $( this.options.selectors.form.tag_form );
        
        // устанавливаем форме параметры текущей записи
        $('input[name="ts"]', $tag_form).val(target_subject);
        $('input[name="ti"]', $tag_form).val(target_id);
        
        var $tags_bar = icms.favorites.getTagsBar(link, target_subject);
        
        // формируем массив пользовательских тегов и скрываем их из строки tags_bar
        var tags = [];
        $( this.options.selectors.comma,    $tags_bar ).hide();
        $( this.options.selectors.edit_tag, $tags_bar ).hide(30);
        $( this.options.selectors.tag,      $tags_bar ).each(function (){
            tags.push( $(this).text() );
            $(this).hide(30);
        });

        $tag_form
            .insertAfter($tags_bar)             // устанавливаем форму после строки тегов
            .show(50)                           // показываем форму
            .find('input[name="tags_string"]')  // добавляем в поле ввода...
                    .val( tags.join(', ') )     // ...пользовательские теги
                    .focus();                   // и устанавливаем фокус

        return false;
        
    }

    /*
     * Сохраняет введённые пользователем теги
     * @param jQuery object $tag_form
     */
    this.tagsFormSubmit = function($tag_form){
        
        // если объект формы не передан ищем его самостоятельно
        if (typeof $tag_form === 'undefined') $tag_form = $(this.options.selectors.form.tag_form);
        
        var _this = this;
        
        // обработчик формы сохранения тегов.
        $tag_form.submit(function(e){

            // получаем данные формы и адрес запроса
            var form_data = $(this).serializeArray();
            var url = '/' + $(this).attr('action');

            // выполняем запрос
            $.post(url, form_data, function(result){

                // выводим сообщение в случае ошибки
                if (result == null || typeof(result) == 'undefined' || result.error){
                    $.jGrowl(result.message, { theme: 'error' });
                    return false;
                }

                // получаем объект строки тегов
                var $tags_bar = icms.favorites.getTagsBar( $tag_form, $('input[name="ts"]', $tag_form).val() );
                
                // удаляем из строки старые пользовательские теги
                $( _this.options.selectors.comma,    $tags_bar ).remove();
                $( _this.options.selectors.tag,      $tags_bar ).remove();
                // записываем новые теги и показываем их
                $( _this.options.selectors.edit_tag, $tags_bar ).before(result.html).show();

                // скрываем форму редактирования пользовательских тегов
                $tag_form.hide();

            }, 'json');

            return false;

        });
        
    }

    /*
     * Устанавливает обработчик Devbridge Autocomplete
     * для формы редактирования пользовательских тегов
     * @param jQuery object $tag_form
     */
    this.tagsFormAutocomplete = function($tag_form){
        
        // если объект формы не передан ищем его самостоятельно
        if (typeof $tag_form === 'undefined') $tag_form = $(this.options.selectors.form.tag_form);
        
        // инициализируем автокомлпит для формы ввода пользовательских тегов
        $(this.options.selectors.form.tag_input, $tag_form).devbridgeAutocomplete({

            serviceUrl:         this.options.urls.autocomplete,     // url-адрес обработки запросов автозаполнения
            minChars:           2,                                  // минимальная длина запроса для срабатывания автозаполнения
            delimiter:          /(,|;)\s*/,                         // разделитель для нескольких запросов
            maxHeight:          400,                                // максимальная высота списка подсказок, в пикселях
            deferRequestBy:     300,                                // задержка запроса (мсек)
            params:             {type: 'tags'},                     // дополнительные параметры
            onSelect: function (suggestion) {                       // функция обработки при выборе одного из предложенных вариантов
                $(this).val( function(index, value){ return value + ', '; } ).focus();
            }

        });
        
    }

    /*
     * Закрывает форму редактирования пользовательских тегов
     * @param jQuery object $tag_form
     */
    this.closeTagsForm = function($tag_form){
        
        // если объект формы не передан ищем его самостоятельно
        if (typeof $tag_form === 'undefined') $tag_form = $(this.options.selectors.form.tag_form);
        // если форма скрыта закрывать её не надо
        if (!$tag_form.is(':visible')) return false;

        // получаем объект строки тегов
        var $tags_bar = icms.favorites.getTagsBar( $tag_form, $('input[name="ts"]', $tag_form).val() );
        
        // показываем пользовательские теги
        $( this.options.selectors.tag,      $tags_bar ).show();
        $( this.options.selectors.comma,    $tags_bar ).show();
        $( this.options.selectors.edit_tag, $tags_bar ).show();
        
        // скрываем форму
        $tag_form.hide();
        
        return false;
        
    }

    /*
     * Показывает список пользователей добавивших запись в избранное
     * @param object link
     */
    this.showUsersInfo = function(link){

        $link = $(link);

        // получаем объект виджета избранного для указанной ссылки
        var $widget = $link.parent( this.options.selectors.widget.widget );

        // отправляем запрос с необходимыми параметрами
        icms.modal.openAjax($link.data( this.options.data.url ), { 
            tc:     $widget.data( this.options.data.controller ),
            ts:     $widget.data( this.options.data.subject ),
            ti:     $widget.data( this.options.data.id ),
            total:  $link.text()
        });

    }

    /*
     * Обработчик пагинации для списка пользователей добавивших запись в избранное
     */
    this.bindUsersInfoPages = function(){

        var _this = this;

        // получаем объект пагинации
        var $paginator = $( this.options.selectors.paginator );

        // получаем данные избранного и url-запроса
        var controller  = $paginator.data( this.options.data.controller );
        var subject     = $paginator.data( this.options.data.subject );
        var id          = $paginator.data( this.options.data.id );
        var url         = $paginator.data( this.options.data.url );

        // обрабатываем нажатие на ссылку страницы
        $('a', $paginator).click(function(){

            var $link = $(this);  // текущая ссылка страницы
            var page = $link.data( _this.options.data.page );  // номер текущей страницы
            var $list = $('#favorite_info_window:visible .favorite_info_list');  // список пользователей

            // устанавливаем текущей ссылке статус активной страницы
            $('a', $paginator).removeClass('active');
            $link.addClass('active');

            // показываем статус загрузки данных
            $list.addClass('loading-panel');

            // выполняем запрос
            $.post(url, {

                tc:             controller,
                ts:             subject,
                ti:             id,
                page:           page,
                is_list_only:   true    // указатель получить только список пользователей

            }, function(result){

                // выводим результат запроса и скрываем статус загрузки
                $list.html(result).removeClass('loading-panel');

            }, "html");

            return false;

        });

    }

    //========================================================================//

    return this;

}).call(icms.favorites || {},jQuery);