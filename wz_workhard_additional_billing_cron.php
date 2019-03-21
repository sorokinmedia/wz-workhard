<?php

require_once(dirname(__FILE__).'/includes/Arrch.php');
require_once(dirname(__FILE__).'/wz_workhard_google_spreadsheet.php');

use Arrch\Arrch;

add_action('wz_cron_billing', 'wz_cron_billing_callback');


function wz_cron_billing_callback() {
    $options_google_spreadsheet = get_option('wz_options_google_spreadsheet');
    
    $range_columns = 'A:D';
    $document_id = trim($options_google_spreadsheet['id']);
    $sheetname = trim($options_google_spreadsheet['sheetname_billing']);
    
    // Если пользователь в настройка не ввел номер документа или название листа
    // прекратить дальнейшие действие скрипта
    if (empty($document_id) || empty($sheetname)) {
        wz_clear_scheduled_billing();
        return;
    }
    
    $json_data = json_decode($options_google_spreadsheet['json_auth'], true);
    $valid_json_data = wz_validate_google_json_auth($json_data);
    
    // Если не валидный ключ гугл
    // то прекратить дальнейшее действие скрипта
    if (!$valid_json_data){
        wz_clear_scheduled_billing();
        return;
    }
    
    $service = wz_google_create_service('Wordpress', $json_data);

    $count_rows = wz_google_count_rows($service, $document_id,  $sheetname, $range_columns);
    
    // Если нет записей создать колонки Дата, тип операции, комментарий,  сумма пополения
    if ($count_rows === 0) {
        
        $columns_names = array(
            'Дата', 'Тип операции', 'Комментарий', 'Cтоимость'
        );
        wz_google_insert_row($service, $document_id, $sheetname, $range_columns, $columns_names);
    } elseif ($count_rows === null) {
        update_option('export_billing', array('status' => 'ready'));
        wz_clear_scheduled_billing();
        return;
    }
    // Данные для получения биллинг операций
    $token = get_option('wz_options_workhard')['token'];
    $type = array(1, 5);
    $order = 'SORT_ASC';
    $limit = 20;    
    $offset = ($count_rows - 1) / $limit + 1;
    $offset = floor($offset);
        
    // Если нет запись операций, то отключить выполнение скрипта
    $operations = wzz_fetch_billing_operations($token, $order, $type,  $limit, $offset)['response'];
    if (!$operations) {
        update_option('export_billing', array('status' => 'ready'));
        wz_clear_scheduled_billing();
        return;
    }
  
    
    $list_operations = array();
    
    // Если количество запсей в workhard больше, чем в гугл, 
    // то выполнить экспорт записей
    // Иначе прекратить выполнение задачи
    $count_operations_in_workhard = $operations['count'];
    if ($count_operations_in_workhard > $count_rows) {
        $rows_limit_offset = $limit * $offset;
    
        $slice_offset = ($count_rows - 1) % $limit; 
        $slice_length = $rows_limit_offset - ($count_rows - 1);

        $list_operations = array_slice($operations['operations'], $slice_offset, $slice_length);
    } else {
        update_option('export_billing', array('status' => 'ready'));
        wz_clear_scheduled_billing();
        return;
    }
    
    $export_billing = get_option('export_billing');
    if (!$export_billing || $export_billing['status'] == 'ready' || $export_billing['status'] == 'preparation') {
        update_option('export_billing', array('status' => 'works'));
    } 
    
    
    // Экспортировать данные
    foreach ($list_operations as $item) {
        $dt = new DateTime();
        $dt->setTimezone(new DateTimeZone('Europe/Moscow'));
        $dt->setTimestamp($item['created_at']);
        
        $date_creatiton = $dt->format('d.m.Y H:i:s');
        
        $type = '';
        if ($item['type']['id'] == 1){
            $type='Пополнение';
        } elseif ($item['type']['id'] == 5) {
            $type='Перевод';
        }
        
        $values = array(
            $date_creatiton, $type, $item['comment'], $item['sum']
        );
        wz_google_insert_row($service, $document_id, $sheetname, $range_columns, $values);
    }  
}

/*
 * Запустить крон выполнения экспорта Биллинга
 */
function wz_run_schedule_billing() {
    $schedule_interval = '5seconds';
    $schedule_name = 'wz_cron_billing';
    $time = wp_next_scheduled($schedule_name);
    if ($time) {
        return;
    }
    wp_schedule_event(time(), $schedule_interval, $schedule_name);
}

/*
 * Отключить крон выполнения экспорта Биллинга
 */
function wz_clear_scheduled_billing(){
    $schedule_name = 'wz_cron_billing';
    $time = wp_next_scheduled($schedule_name);
    wp_unschedule_event($time, $schedule_name);
    wp_clear_scheduled_hook($schedule_name);
}



?>