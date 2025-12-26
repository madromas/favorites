var icms = icms || {};

icms.favtags = (function ($) {

    this.options = {

        selectors: {

            tag:        '.fav_tag',         // пользовательский тег
            edit_tag:   '.fav_edit_tags',   // ссылка редактирования

            form: {
                tag_form:   '#fav_tag_form',    // форма редактирования пользовательских тегов
                tag_input:  '.tags_string',     // строка ввода тегов
            },

            widget: {
                input:      '#tags_suggest',    // поле ввода поиска тега
                clear:      '#suggest_clear',   // кнопка очистки поля поиска тега
                top_tags:   '#top-tags',        // список выводимых тегов
                all_tags:   '#all-tags'         // список формируемый при поиске тегов
            }

        }
        
    }

    this.setOptions = function(options){
        $.extend(true, this.options, options);
    }

    //========================================================================//

    this.onDocumentReady = function(){

        // слушаем событие переключения (добавления/удаления) избранного
        icms.events.on('icms_favorites_toggle', function(data){
            icms.favtags.toggleFavLink(data.action, data.tags);
        });
        
        // вешаем обработчик поиска тегов на виджет
        icms.favtags.tagsWidgetSearch();        
        
        // устанавливаем обработчик формы сохранения тегов
        var $tag_form = $(this.options.selectors.form.tag_form);
        if ($tag_form.length)
            $tag_form.submit(function(e){                
                icms.favtags.tagsFormSubmit($tag_form);
            });

    }
    
    /*
     * Получает массив тегов записи и вызывает tagsWidgetUpdate()
     * для обновления виджета тегов
     * @param string action
     * @param string tags_html
     */
    this.toggleFavLink = function(action, tags_html){
        
        // получаем массив тегов
        var tags = [];
        $(tags_html)
                .not(this.options.selectors.edit_tag)
                .filter('a')
                .each(function(indx){
                    tags.push( $(this).text() );
                });
        
        // обновляем виджет пользовательских тегов
        icms.favtags.tagsWidgetUpdate(tags, action);
        
        // обновляем счетчик избранного на вкладке пользователя
        var counter = $('#user_profile_tabs .active .counter');
        var num = +counter.text();
        counter.text(action==='add' ? num+1 : num-1);

    }
    
    /*
     * Дополнительный обработчик формы добавления тегов,
     * проверяет ново-введенные теги и вызывает tagsWidgetUpdate()
     * для обновления виджета тегов
     * @param jQuery object $tags_form
     */
    this.tagsFormSubmit = function($tags_form){

                /*
                 * Сравнивает массивы и возвращает элементы
                 * массива arr1 которых нет в массие arr2
                 */
                function array_diff(arr1, arr2){    
                    var result = [], is_ok;
                    arr1.every(function(item1, i, arr1){
                        is_ok = true;
                        arr2.every(function(item2, j, arr2){            
                            if (item1==item2){
                                    is_ok = false;
                                    return false;
                            }
                            return true;
                        });
                        if (is_ok) result.push(item1);
                        return true;
                    });
                    return result;
                }
        
        // получаем массив старых тегов (до обновления)
        var tags_html = 
                $tags_form
                    .closest('.content_list_item')
                    .find('.tags_bar')
                    .html();
            
        var old_tags = [];
        $(tags_html)
                .filter(this.options.selectors.tag)
                .each(function(indx){
                    old_tags.push( $(this).text() );
                });
        
        // получаем массив новых тегов из строки ввода
        var new_tags = 
                $(this.options.selectors.form.tag_input, $tags_form)
                    .val()
                    .split(',')
                    .filter(function(item, i, arr){
                        return $.trim(item);
                    })
                    .map(function(item, i, arr){
                        return $.trim(item);
                    });
        
        // определяем какие теги нужно добавить в виджет тегов...
        var add = array_diff(new_tags, old_tags);
        // ... и какие теги нужно удалить
        var del = array_diff(old_tags, new_tags);

        if (add.length != 0)
            icms.favtags.tagsWidgetUpdate(add, 'add');
        
        if (del.length != 0)
            icms.favtags.tagsWidgetUpdate(del, 'delete');

    }
    
    /*
     * Обновление в актуальное состоние тегов виджета
     * @param array of string tags
     * @param string action
     */
    this.tagsWidgetUpdate = function(tags, action){
        
        if (tags.length == 0) return false;
        
        var _this = this;
        
        // скрываем кнопку очистки виджета тегов если она активна
        var $clear = $(this.options.selectors.widget.clear); 
        if ($clear.is(':visible')){ $clear.click(); }
        
        // формируем видимый список тегов #top-tags
        var $top_tags = $(this.options.selectors.widget.top_tags);  
        var new_items = ''; 

        // перебираем текущий список пользовательских тегов
        $('li', $top_tags).each(function(){

            var tag   =  $(this);
            var name  =  $('.name',  tag).text();  // название тега
            var count = +$('.count', tag).text();  // колличество

            var is_show = true;

            // сравниваем с переданным массивом
            tags.every(function(item){            
                if (item==name){  // при совпадении...
                        is_show = (action === 'add') ? ++count : --count;  // ...считаем колличество
                        return false;  // прерываем перебор
                }
                return true;
            });

            if (is_show)  // добавляем одобренный тег к набору
                new_items += '<li><a href="'+_this.options.url+encodeURIComponent(name)+'"><span class="count">'+count+'</span><span class="name">'+name+'</span></a></li>';

        });

        // выводим новый набор тегов
        $top_tags.html(new_items);


        // формируем список всех пользовательских тегов
        this.options.tags.every(function(item){

            // перебираем переданный массив тегов...
            tags.every(function(name, j){
                // ...и сравниваем его с пользовательскими тегами
                if ( item.name==name ){
                    // пересчитываем тег при совпадении
                    (action == 'add') ? ++item.count : --item.count;
                    tags.splice(j, 1);  // удаляем найденный тег из массива
                    return false;  // прерываем текущую итерацию
                }
                return true;
            });
            return true;
        });
        
        // оставшийся массив тегов добавляем к пользовательским
        if (tags.length) {
            tags.every(function(name){ 
                _this.options.tags.push( {name: name, ename: encodeURIComponent(name), count: 1} );
                return true;
            });
        }
        
    }

    /*
     * Обработчик поиска виджета пользовательских тегов.
     */
    this.tagsWidgetSearch = function(){
        
        var _this = this;
        
        var $tag_input   = $(this.options.selectors.widget.input);
        var $clear       = $(this.options.selectors.widget.clear);
        var $all_tags_ul = $(this.options.selectors.widget.all_tags);
        var $top_tags_ul = $(this.options.selectors.widget.top_tags);
        
        // обрабатываем ввод с клавиатуры
        $tag_input.keyup(function(e){
            
            // если нажата Esc очищаем поле ввода
            if (e.keyCode === 27) { $tag_input.val(''); }
            
            // если нажата клавиша Enter
            if (e.keyCode === 13){
                document.location.href = _this.options.url + encodeURIComponent($tag_input.val());
            }
            
            
            // получаем запрос пользователя...
            var query = $tag_input.val();
            
            // ... и обрабатываем его
            if (query === '') { $clear.click(); }
            else {
                
                $clear.show();
                $top_tags_ul.hide();
                $all_tags_ul.html('');
                
                var html = '';
                
                // формируем список тегов...
                for(var k in _this.options.tags){
                    
                    var tag = _this.options.tags[k];
                    
                    if (tag.name.indexOf(query)+1 && tag.count>0) {
                        
                        html += '<li>' +
                            '<a ' + ( tag.current ? 'class="current"' : '' ) + ' href="' + _this.options.url + tag.ename + '">' +
                                '<span class="count">' + tag.count + '</span>' +
                                '<span class="name">' + tag.name + '</span>' +
                            '</a>' +
                        '</li>';
                            
                    }
                    
                }
                // выводим список тегов
                $all_tags_ul.append(html).show();
                
            }
            
        });
        
        // обрабатываем клик по кнопке очистки поля ввода тегов
        $clear.click(function(){
            
            $tag_input.val('');
            $clear.hide();
            $top_tags_ul.show();
            $all_tags_ul.hide().html('');
            
        });
        
    }

    //========================================================================//

    return this;

}).call(icms.favtags || {},jQuery);