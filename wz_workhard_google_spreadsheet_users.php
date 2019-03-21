<?php

require_once(dirname(__FILE__).'/wz_workhard_google_spreadsheet.php');

add_action('admin_menu', 'wz_workhard_admin_spreadsheet_users');

add_action('wp_ajax_wz_workhard_ajax_spreadsheet_users', 'wz_workhard_ajax_spreadsheet_users');

function wz_workhard_admin_spreadsheet_users() {
    $parent_slug = 'wz-workhard';
    $page_title = 'Настройка гугл таблицы пользователей вп';
    $menu_title= 'Настройка гугл таблицы пользователей вп'; 
    $capability = 'manage_options';
    $menu_slug = 'wz-options-workhard-spreadsheet-users';
    $callback = 'wz_workhard_spreadsheet_users_callback';
    add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );
}

function wz_workhard_spreadsheet_users_callback() {
    $users = get_users();
?>
    <div class="wrap">
        <div id="app">
            <h2>Настройка экспорта записей статьи для пользоватля</h2>
            <form @submit.prevent="onSubmit()">
                <p>Выберете пользователя:</p>
                <select v-model="form.selectedUser">
                    <option :value="null">Выберете пользователя</option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user->ID ?>"><?php echo $user->display_name; ?></option>
                    <?php endforeach; ?>
                </select>
                <hr>
                <p>Название гугл таблицы для текущего пользователя:</p>
                <input type="text" v-model="form.tableName">
                
                <p>Цена публикации:</p>
                <input type="text" v-model="form.price">
                
                <button class="button button-primary" :disabled="!validateForm">Сохранить</button>
            </form>
            <hr>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Пользователь</th>
                        <th>Название гугл таблицы</th>
                        <th>Цена публикации</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead> 
                <tbody>
                     <tr v-for="item in rules" :key="item.user_id">
                        <td>{{ item.username }}</td>
                        <td>{{ item.table_name }}</td>
                        <td>{{ item.price }}</td>
                        <td><a href="#" @click.prevent="removeRule(item.user_id)">Удалить</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php
}

function wz_workhard_ajax_spreadsheet_users() {
    if(!wp_verify_nonce($_REQUEST['nonce'], 'workhard_ajax')){
        echo( json_encode( array('status' => 'error' ) ) );
        wp_die();
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (wzz_validate_params(array('method')) && $_GET['method'] == 'fetch_spreadsheet_users') {
            $options = get_option('wz_workhard_google_spreadsheet_users');
            
            $options = array_map(function($item) {
                $user = get_user_by( 'id', $item['user_id']);
                $item['username'] = $user->display_name;
                return $item;
            }, $options);
            
            echo json_encode($options);
            wp_die();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (wzz_validate_params(array('method', 'user_id',  'table_name', 'price')) && $_POST['method'] == 'insert_google_spreadsheet_user') {
            $options = get_option('wz_workhard_google_spreadsheet_users');
            
            $_POST = array_map('trim', $_POST);

            if( !wz_has_record_in_array($options, 'user_id', $_POST['user_id']) ){
                
                $options[] = array('user_id'=> $_POST['user_id'], 'table_name' => $_POST['table_name'], 'price' => $_POST['price']);
                update_option('wz_workhard_google_spreadsheet_users', $options);
                echo json_encode( array('status' => 'success') );
                wp_die();
            } else {
                echo json_encode( array('status' => 'error') );
                wp_die();
            }
        } elseif (wzz_validate_params(array('method', 'user_id')) && $_POST['method'] == 'remove_google_spreadsheet_user') {
            $_POST = array_map('trim', $_POST);
            
            $options = get_option('wz_workhard_google_spreadsheet_users');
            $options = array_filter($options, function ($item) {
                return $item['user_id'] != $_POST['user_id'];
            });
            
            update_option('wz_workhard_google_spreadsheet_users', $options);
            echo json_encode( array('status' => 'success') );
            wp_die();
        }
    }
}

function wz_has_record_in_array($array, $search_key, $search_value) {
    
    foreach ($array as $item) {
        if ($item[$search_key] == $search_value) {
            return true;
        }
    }
    
    return false;
}