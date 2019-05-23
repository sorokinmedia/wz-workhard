<?php

require_once(dirname(__FILE__).'/includes/Arrch.php');

require_once(dirname(__FILE__).'/wz_workhard_google_spreadsheet.php');

use Arrch\Arrch;

add_action('admin_menu', 'wz_workhard_admin_statistics');

add_action('wp_ajax_wz_workhard_ajax_statistics', 'wz_workhard_ajax_statistics');

function wz_workhard_admin_statistics() {
    $parent_slug = 'wz-workhard';
    $page_title = 'Workhard статистика';
    $menu_title= 'Workhard статистика'; 
    $capability = 'edit_published_posts';
    $menu_slug = 'wz-options-workhard-statistics';
    $callback = 'wz_workhard_statistics_callback';
    add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );
}

function wz_workhard_statistics_callback() {     
    $data_select_fields = wz_fetch_select_fields();
    
?>
<div class="wrap">
    <?php if (current_user_can('administrator')): ?>
    <div id="app-stat-and-billing">
        <h2>Workhard - Статистика</h2>
        <p>Для выгрузки статистики о завершенных задачах в гугл таблицу, нажмите &quot;обновить статистику&quot;</p>
        <p><button @click="runExportTasks" class="button button-primary">Обновить статистику</button></p>
        <hr>
        <p>Информация по экспорту данных статистики: {{ infoExportTasks }}</p>
        <hr>
        <h2>Биллинг</h2>
        <p>Для выгрузки отчета биллинга в гугл таблицу, нажмите &quot;Экспортировать отчет&quot;</p>
        <p><button @click="runExportBilling" class="button button-primary">Экспортировать отчет</button></p>
        <hr>
        <p>Информация по экспорту данных статистики: {{ infoExportBilling }}</p>
    </div>
    <?php endif; ?>
    
    <div id="app-costs" @submit="addRow">
        <hr>
        <h2>Занесение расходов</h2>
        <form>
            <table class="form-table">
                <tr>
                    <th>Выберете сайт</th>
                    <td>
                        <select v-model="form.site">
                            <option value="">Выберете сайт</option>
                            <?php foreach ($data_select_fields['list_sites'] as $item): ?>
                            <option value="<?php echo $item; ?>"><?php echo $item; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                
                 <tr>
                    <th>Выберете название расхода</th>
                    <td>
                        <select v-model="form.costsName">
                            <option value="">Выберете название расхода</option>
                            <?php foreach ($data_select_fields['list_costs_names'] as $item): ?>
                            <option value="<?php echo $item; ?>"><?php echo $item; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                
                 <tr>
                    <th>Введите примечание</th>
                    <td>
                        <textarea v-model="form.note" class="large-text" rows="7"></textarea>
                    </td>
                </tr>
                
                <tr>
                    <th>Введите сумму</th>
                    <td>
                        <input v-model="form.sum" type="text">
                    </td>
                </tr>
            </table>
            <button class="button button-primary" :disabled="!validateForm()">Добавить запись</button>
        </form>
        <hr>
        <table class="wp-list-table widefat fixed striped costs">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Пользователь</th>
                    <th>Название сайта</th>
                    <th>Название расхода</th>
                    <th>Примечание</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                </tr>
            </thead> 
            <tbody>
                 <tr v-for="item in tableCosts">
                    <th>{{ item[0] }}</th>
                    <th>{{ item[1] }}</th>
                    <th>{{ item[2] }}</th>
                    <th>{{ item[3] }}</th>
                    <th>{{ item[4] }}</th>
                    <th>{{ item[5] }}</th>
                    <th>{{ item[6] }}</th>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php
}

function wz_workhard_ajax_statistics() {
    if(!wp_verify_nonce($_REQUEST['nonce'], 'workhard_ajax')){
        echo( json_encode( array('status' => 'error' ) ) );
        wp_die();
    }
   
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (wzz_validate_params(array('method')) && $_GET['method'] === 'get_info_export_tasks') {
            // Получить статус экспорта задач
            echo json_encode(get_option('export_tasks'));
            wp_die();
            
        } elseif (wzz_validate_params(array('method')) && $_GET['method'] === 'run_export_tasks') {
            // Запустить экспорт задач
            update_option('export_tasks', array('status' => 'preparation'));
            wz_run_schedule_addional_statistics('5seconds');
            
            echo json_encode(get_option('export_tasks'));
            wp_die();
            
        } elseif (wzz_validate_params(array('method')) && $_GET['method'] === 'get_info_export_billing') {
            // Получить статус экспорта биллинга
            echo json_encode(get_option('export_billing'));
            wp_die();
            
        } elseif (wzz_validate_params(array('method')) && $_GET['method'] === 'run_export_billing') {
             // Запустить экспорт биллинга
            update_option('export_billing', array('status' => 'preparation'));
            wz_run_schedule_billing();
            
            echo json_encode(get_option('export_billing'));
            wp_die();
        } elseif (wzz_validate_params(array('method')) && $_GET['method'] === 'fetch_table_costs') {
            // Получить данные с гугл таблицы занесенные расходы пользователем  
            $username = wp_get_current_user()->user_login;

            $data = wz_fetch_costs($username);
            
            echo json_encode($data);
            wp_die();
            
        }  
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (wzz_validate_params(array('method', 'site', 'costs_name', 'sum')) && $_POST['method'] === 'add_row_costs') {
            // Добавить запись о расходах в гугл таблицу
            $options_google_spreadsheet = get_option('wz_options_google_spreadsheet');
            $range_columns = 'A:G';
            $document_id = $options_google_spreadsheet['id'];
            $sheetname = $options_google_spreadsheet['sheetname_cost_entry'];

            // Если пользователь в настройка не ввел номер документа или название листа
            // прекратить дальнейшие действие скрипта
            if (empty($document_id) || empty($sheetname)) {
                echo json_encode(array('status'=>'error'));
                wp_die();
            }
                        
            $json_data = json_decode($options_google_spreadsheet['json_auth'], true);
            $valid_json_data = wz_validate_google_json_auth($json_data);

            if (!$valid_json_data){
                echo json_encode(array('status'=>'error'));
                wp_die();
            }
            
            $service = wz_google_create_service('Wordpress', $json_data);

            // Если нету записей в таблице, то вставить по умолчанию заголовки столбцов 
            // Иначе отключить скрипт
            $count_rows = wz_google_count_rows($service, $document_id,  $sheetname, $range_columns);
            
            if ($count_rows === 0) {

                $columns_names = array(
                    'Дата', 'Пользователь', 'Название сайта', 'Название расхода', 'Примечание', 'Сумма', 'Статус'
                );
                wz_google_insert_row($service, $document_id, $sheetname, $range_columns, $columns_names);
            } elseif ($count_rows === null) {
                echo json_encode(array('status'=>'error'));
                wp_die();
            }
            
            $dt = new DateTime();
            $dt->setTimezone(new DateTimeZone('Europe/Moscow'));
            $date_creation = $dt->format('d.m.Y H:i:s');
            
            $username = wp_get_current_user()->user_login;
            
            $_POST['sum'] = wz_mb_str_replace('.', ',', $_POST['sum']);        
            
            $values = array(
                $date_creation,
                $username,
                trim($_POST['site']),
                trim($_POST['costs_name']),
                trim($_POST['note']),
                trim($_POST['sum']),
            );
            
            
            
            wz_google_insert_row($service, $document_id, $sheetname, $range_columns, $values);
            
            echo json_encode(array('status'=>'success'));
            wp_die();
        }
    } 
    
    
}

/*
 * Получить значения для тегов select "Выберете сайт" и Выберете название расхода
 * @return array|null: пример вывода ['list_sites' => ['test1', 'test2', 'test3'], 'list_costs_names' => ['example1.com', 'example2.com']]
 */
function wz_fetch_select_fields() {
    // Получить данные с гугл таблицы занесенные расходы пользователем  
    $options_google_spreadsheet = get_option('wz_options_google_spreadsheet');
    $range_columns = 'H:I';
    $document_id = $options_google_spreadsheet['id'];
    $sheetname = $options_google_spreadsheet['sheetname_cost_entry'];

    // Если пользователь в настройка не ввел номер документа или название листа
    // прекратить дальнейшие действие скрипта
    if (empty($document_id) || empty($sheetname)) {
        return;
    }

    $json_data = json_decode($options_google_spreadsheet['json_auth'], true);
    $valid_json_data = wz_validate_google_json_auth($json_data);

    if (!$valid_json_data){
        return;
    }

    $service = wz_google_create_service('Wordpress', $json_data);

    $count_rows = wz_google_count_rows($service, $document_id,  $sheetname, $range_columns);

    if ($count_rows === null) {
        return;
    }
    
    $data = array('list_sites' => array(), 'list_costs_names' => array());
    
    $data_from_google = wz_google_get_values($service, $document_id, $sheetname, $range_columns);
    
    foreach ($data_from_google as $item) {
        
        if (!empty($item[0])) {
            $data['list_sites'][] = $item[0];
        }
        
        if (!empty($item[1])) {
            $data['list_costs_names'][] = $item[1];
        }
        
    }
    
    return $data;
    
}

/*
 * Получить данные расходов текущего пользователя, с гугл таблицы
 * @param string $username: Имя пользователя в вордпресс
 * @return array|null
 */
function wz_fetch_costs($username) {
    // Получить данные с гугл таблицы занесенные расходы пользователем  
    $options_google_spreadsheet = get_option('wz_options_google_spreadsheet');
    $range_columns = 'A:G';
    $document_id = $options_google_spreadsheet['id'];
    $sheetname = $options_google_spreadsheet['sheetname_cost_entry'];

    // Если пользователь в настройка не ввел номер документа или название листа
    // прекратить дальнейшие действие скрипта
    if (empty($document_id) || empty($sheetname)) {
        return;
    }

    $json_data = json_decode($options_google_spreadsheet['json_auth'], true);
    $valid_json_data = wz_validate_google_json_auth($json_data);

    if (!$valid_json_data){
        return;
    }

    $service = wz_google_create_service('Wordpress', $json_data);

    $count_rows = wz_google_count_rows($service, $document_id, $sheetname, $range_columns);

    if ($count_rows === null) {
        return;
    }
    
    $data_from_google = wz_google_get_values($service, $document_id, $sheetname, $range_columns);
    
    $count_data_from_google = count($data_from_google);
    
    $data_from_google = array_slice($data_from_google, 1, $count_data_from_google);
    
    // Выбрать данные с гугл таблицы, те которые ввел пользователь
    $data_from_google = array_filter($data_from_google, function($item) use ($username) {
        return $item[1] == $username;
    });
    
    // Если статус пустой, то заполнить "Выполняется проверка данных"
    $data_from_google = array_map(function ($item) {
        if (empty($item[6])) {
            $item[6] = 'Выполняется проверка данных';
        }
        return $item;
    }, $data_from_google); 
    
    // Сортировать по убыванию
    $data_from_google = array_reverse($data_from_google);
    
    return $data_from_google;
}

