<?php

class actionFavoritesInfo extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) { cmsCore::error404(); }
        if (!$this->options['is_user_list_show']) { cmsCore::error404(); }

        // Получаем параметры
        $target_controller  = $this->request->get('tc');        // название контроллера
        $target_subject     = $this->request->get('ts');        // системное имя типа контента
        $target_id          = $this->request->get('ti');        // ID записи
        $total              = $this->request->get('total', 1);  // количество добавлений записи в избранное
        $page               = $this->request->get('page', 1);   // текущая страница

        // Проверяем валидность полученных данных
        $is_valid = ($this->validate_sysname($target_controller)===true) &&
                    ($this->validate_sysname($target_subject)===true) &&
                    is_numeric($target_id) &&
                    is_numeric(trim($total)) &&
                    is_numeric($page);

        if (!$is_valid) { cmsCore::error404(); }
        if ($target_controller=='content' && !$target_subject) { cmsCore::error404(); }

        $perpage = 10;  // количество пользователей в списке

        $profiles = $this->model->limitPage($page, $perpage)->getUsersForTarget($target_controller, $target_subject, $target_id);

        $pages = ceil($total / $perpage);

         $is_list_only = (bool)$this->request->get('is_list_only'); // флаг что нужно вывести только голый список

        if ($is_list_only){

            cmsTemplate::getInstance()->render('info_list', array(
                'profiles' => $profiles,
            ));

        }

        if (!$is_list_only){

            cmsTemplate::getInstance()->render('info', array(
                'target_controller' => $target_controller,
                'target_subject' => $target_subject,
                'target_id' => $target_id,
                'profiles' => $profiles,
                'page' => $page,
                'pages' => $pages,
                'perpage' => $perpage
            ));

        }

    }

}