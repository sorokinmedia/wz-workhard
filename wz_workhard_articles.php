<?php

require_once(dirname(__FILE__) . '/includes/Arrch.php');

require_once(dirname(__FILE__) . '/wz_workhard_google_spreadsheet.php');

use Arrch\Arrch;

add_action('admin_menu', 'wz_workhard_admin_articles');

add_action('wp_ajax_wz_workhard_ajax_articles', 'wz_workhard_ajax_articles');

function wz_workhard_admin_articles()
{
    $parent_slug = 'wz-workhard';
    $page_title = 'Workhard статьи';
    $menu_title = 'Workhard статьи';
    $capability = 'edit_published_posts';
    $menu_slug = 'wz-options-workhard-articles';
    $callback = 'wz_workhard_articles_callback';
    add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback);
}

function wz_workhard_articles_callback()
{
    global $wpdb;
    $user_id = get_current_user_id();
    $permission_folders = wz_fetch_permission_folders_db($user_id);


    $options_spreadsheet_users = get_option('wz_workhard_google_spreadsheet_users');

    // Узнать пользователя id
    $current_user_id = get_current_user_id();

    $price_field = null;

    foreach ($options_spreadsheet_users as $item) {
        if ($item['user_id'] == $current_user_id) {
            $price_field = $item['price'];
            break;
        }
    }

    ?>
    <div class="wrap">
        <h2>Workhard - статьи</h2>
        <div id="app">
            <router-view></router-view>
        </div>
    </div>

    <div id="articles-list-page">
        <div>
            <p>Выберете категорию</p>
            <select @change="fetchArticles" v-model="selectedPermissionFolders">
                <option :value="null">Выберете категорию</option>
                <?php foreach ($permission_folders as $item): ?>
                    <option
                            :value="{fromFolderID: <?php echo $item->from_folder_id; ?>,
                            toFolderID: <?php echo $item->to_folder_id; ?>,
                            toFolderName: '<?php echo $item->to_folder_name ?>',
                            categoryID: '<?php echo $item->category_id ?>'
                            }"
                    >
                        <?php echo $item->from_folder_name . '->' . $item->to_folder_name . '->' . $item->category_name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" ref="priceHidden" name="price-hidden" value="<?php echo $price_field; ?>">
            <hr>
            <div v-if="articles.length == 0">Записей нет</div>
            <div v-else>
                <p><a href="#" @click="checkAll">Выделить всё</a> | <a href="#" @click="uncheckAll">Снять все
                        выделения</a></p>

                <p>
                <hr>
                <button class="btn btn-primary" @click="exportArticles" :disabled="!statusButtonExport">Экспортировать
                    записи
                </button>
                </p>
                <div id="wz_progress">
                    <div id="wz_bar" ref="bar"></div>
                </div>
                <hr>

                <table id="list-tasks" class="wp-list-table widefat fixed striped posts">
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Название заказа</th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="article in articles" :key="article.id">
                        <td><input ref="selectArticle" type="checkbox" :value="article.id" :id="article.id"
                                   v-model="selectedArticles"></td>
                        <td>{{ article.name }}</td>
                        <td>
                            <p ref="articleEdit">
                                <router-link
                                        :to="'/article-edit/' + selectedPermissionFolders.toFolderID + '/' + selectedPermissionFolders.toFolderName + '/' + article.id+ '/' + selectedPermissionFolders.categoryID">
                                    Перейти
                                </router-link>
                            </p>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <div id="article-edit-page">
        <div>
            <router-link to="/">Вернуться назад</router-link>
            <hr>
            <table id="table-article" class="wp-list-table widefat fixed striped posts">
                <tbody>
                <tr>
                    <th>Название заказа</th>
                    <td>{{ article.order_name }}</td>
                </tr>

                <tr>
                    <th>Название статьи</th>
                    <td>{{ article.title }}</td>
                </tr>

                <tr>
                    <th>Краткое описание</th>
                    <td>{{ article.description }}</td>
                </tr>

                <tr>
                    <th>Текст</th>
                    <td v-html="article.text"></td>
                </tr>
                <tr>
                    <th>Цена</th>
                    <td>
                        <input type="hidden" ref="priceHidden" name="price-hidden" value="<?php echo $price_field; ?>">
                        <input type="text" v-model="price">
                    </td>
                </tr>
                </tbody>
            </table>
            <button class="btn btn-primary" @click="insertPost">Добавить в черновик</button>
        </div>
    </div>

    <?php
}

function wz_workhard_ajax_articles()
{
    if (!wp_verify_nonce($_REQUEST['nonce'], 'workhard_ajax')) {
        echo(json_encode(array('status' => 'error')));
        wp_die();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($_GET['method'] === 'fetch_articles' && wzz_validate_params(array('method', 'from_folder_id'))) {
            /* Получить список статей */
            $token = get_option('wz_options_workhard')['token'];
            $folders = wzz_fetch_folders_array($token);
            $articles = array();

            foreach ($folders['response'] as $folder) {
                if ($folder['id'] === (int)$_GET['from_folder_id']) {
                    $item = array();

                    $options = array(
                        'where' => array(
                            array('status.alias', '==', 'Завершена')
                        )
                    );
                    $tasks = wzz_fetch_folder_tasks($token, $folder['id'])['response'];
                    $result = Arrch::find($tasks, $options);
                    foreach ($result as $task) {
                        $item['id'] = $task['id'];
                        $item['name'] = $task['name'];
                        $articles[] = $item;
                    }
                    break;
                }

            }
            echo json_encode($articles);
            wp_die();
        } elseif ($_GET['method'] === 'fetch_article' && wzz_validate_params(array('method', 'task_id'))) {
            /* Получить статью */
            $token = get_option('wz_options_workhard')['token'];

            $task = wzz_fetch_task_by_id($token, $_GET['task_id'])['response'];

            $result = $task['results'];


            $article = array(
                'order_name' => $task['name'],
                'title' => $result[0]['value'],
                'description' => $result[1]['value'],
                'text' => $result[2]['value'],
                'total_price' => $task['price_customer'] + $task['price_additional_customer']
            );
            echo json_encode($article);
            wp_die();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['method'] === 'insert_post' && wzz_validate_params(array('method', 'order_name', 'text', 'category_id'))) {
            /* Вставить новую статью в черновик */
            $title = !empty($_POST['title']) ? $_POST['title'] : $_POST['order_name'];
            $description = !empty($_POST['description']) ? $_POST['description'] : '';

            // Создаём объект записи
            $new_post = array(
                'post_title' => $title,
                'post_content' => $_POST['text'],
                'post_status' => 'draft',
                'post_author' => get_current_user_id(),
                'post_category' => array($_POST['category_id'])
            );

            $options_users = get_option('wz_options_users');

            if (!is_plugin_active('wordpress-seo/wp-seo.php') || (is_plugin_active('wordpress-seo/wp-seo.php') && !$options_users['yoast_description'])) {
                $new_post['post_excerpt'] = $description;
            }

            // Вставляем запись в базу данных
            $post_id = wp_insert_post($new_post);
            $post_url = wz_get_url_from_wp_posts_db($post_id);


            // Вставляем запись в yoast seo
            if (is_plugin_active('wordpress-seo/wp-seo.php')) {
                if ($options_users['yoast_title']) {
                    wz_add_title_to_yoast($post_id, $_POST['order_name']);
                }

                if ($options_users['yoast_description']) {
                    wz_add_description_to_yoast($post_id, $description);
                }
            }

            echo json_encode(array('post_id' => $post_id, 'post_url' => $post_url));
            wp_die();

        } elseif ($_POST['method'] === 'move_task_to_folder' && wzz_validate_params(array('method', 'folder_id', 'task_id'))) {
            /* Переместить статью в другую папку workhard */
            $token = get_option('wz_options_workhard')['token'];

            $response = wzz_move_task_to_folder($token, $_POST['folder_id'], $_POST['task_id']);
            echo json_encode($response);
            wp_die();

        } elseif ($_POST['method'] === 'insert_spreadsheet_user' && wzz_validate_params(array('method', 'folder_name', 'order_name', 'post_url', 'price', 'total_price'))) {
            /* Вставить отчет о публикации новой статьи в гугл таблицу пользователя вп */
            $options = get_option('wz_options_google_spreadsheet');
            $options_spreadsheet_users = get_option('wz_workhard_google_spreadsheet_users');
            $options_workhard = get_option('wz_options_workhard');

            $json_data = json_decode($options['json_auth'], true);
            $valid_json_data = wz_validate_google_json_auth($json_data);

            $service = wz_google_create_service('Wordpress', $json_data);

            $range_columns = "A:H";

            // Узнать пользователя id
            $current_user_id = get_current_user_id();
            foreach ($options_spreadsheet_users as $item) {
                if ($item['user_id'] == $current_user_id) {
                    $count_rows = wz_google_count_rows($service, $options['id'], $item['table_name'], $range_columns);

                    if ($count_rows == 0) {
                        /* Если нету записей в таблице, то вставить по умолчанию заголовки столбцов */
                        $columns_names = array(
                            'Дата', 'Название папки', 'Название задания', 'Адрес страницы', 'Стоимость',
                            'Фикс ставка за создание задачи', 'Фикс ставка за создание плана статьи', 'Фикс ставка за 1000 знаков',
                            'Общая (итоговоя) стоимость статьи'
                        );
                        wz_google_insert_row($service, $options['id'], $item['table_name'], $range_columns, $columns_names);
                    }

                    $dt = new DateTime();
                    $dt->setTimezone(new DateTimeZone('Europe/Moscow'));
                    $dt->setTimestamp(time());
                    $date = $dt->format('d.m.Y H:i:s');

                    $_POST['price'] = wz_mb_str_replace('.', ',', $_POST['price']);
                    $_POST['total_price'] = wz_mb_str_replace('.', ',', $_POST['total_price']);

                    $values = array(
                        $date, $_POST['folder_name'], $_POST['order_name'], $_POST['post_url'], $_POST['price'],
                        $options_workhard['price_task_creation'], $options_workhard['price_article_creation'], $options_workhard['price_thousand_characters'],
                        $_POST['total_price']
                    );

                    wz_google_insert_row($service, $options['id'], $item['table_name'], $range_columns, $values);

                    echo json_encode(array('status' => $item['table_name']));
                    wp_die();
                }
            }
            // Если есть пользователь в опциях 
            // Проверить есть ли заголовки, если нет то создать
            // Вставить записи

        }
    }

}
