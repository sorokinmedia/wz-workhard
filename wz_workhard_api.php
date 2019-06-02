<?php

/* 
 * Проверка сушествуют ли поля и не являются ли  пустыми значениями
 * 
 * @param array $params список полей с формы
 * return boolean
 */
function wzz_validate_params($params) {
    foreach ($params as $param) {
        if (!isset($_REQUEST[$param])) {
            return false;
        }
        
        if (empty($_REQUEST[$param])) {
            return false;
        }
    }
    return true;
}

/* 
 * Получить список всех диалогов
 *
 * @param string $token
 * @return array
 */
function wzz_fetch_list_messages($token) {
    $headers = array(
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    );
    $response = wzz_request('GET', 'https://api.workhard.online/v2/common/private-messages', array(), $headers);
    return json_decode($response, true);
}

/* 
 * Получить переписку с другим пользователем
 *
 * @param string $token
 * @param int $chat_id
 * @return array
 */
function wzz_fetch_messages_by_id($token, $chat_id) {
    $headers = array(
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    );
    $response = wzz_request('GET', 'https://api.workhard.online/v2/common/private-messages/' . $chat_id, array(), $headers);
    return json_decode($response, true);
}

/* 
 * Отправить сообщение пользователю
 *
 * @param string $token
 * @param int $chat_id
 * @param string $message
 * @return array
 */
function wzz_send_message($token, $chat_id, $message) {
    $headers = array(
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    );
    $response = wzz_request('POST', 'https://api.workhard.online/v2/common/private-messages/' . $chat_id, array('message' => $message), $headers);
    return json_decode($response, true);
}

/*
 * Получить список всех папок(массив)
 *
 * @param string $token
 * @return array
 */
function wzz_fetch_folders_array($token){
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ];
    $response = wzz_request('GET', 'https://api.workhard.online/v2/customer/task/folders/array', [], $headers);
    return json_decode($response, true);
}

/*
 * Получить список задач для заданной папки
 *
 * @param string $token
 * @return array
 */
function wzz_fetch_folder_tasks($token, $folder_id){
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ];
    $response = wzz_request('GET', 'https://api.workhard.online/v2/customer/task/folder/' . $folder_id . '/tasks', [], $headers);
    return json_decode($response, true);
}

/* 
 * Получить задачу
 *
 * @param string $token
 * @param int $task_id
 * @return array
 */
function wzz_fetch_task_by_id($token, $task_id){
    $headers = array(
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    );
    $response = wzz_request('GET', 'https://api.workhard.online/v2/customer/task/' . $task_id, array(), $headers);
    return json_decode($response, true);
}

/* 
 * Получить логи по задаче
 *
 * @param string $token
 * @param int $task_id
 * @return array
 */
function wzz_fetch_task_logs($token, $task_id) {
    $headers = array(
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    );
    $response = wzz_request('GET', 'https://api.workhard.online/v2/customer/task/' . $task_id . '/logs', array(), $headers);
    return json_decode($response, true);    
}

/* 
 * Переместить задачу в другую папку
 *
 * @param string $token
 * @param int $folder_id
 * @param int $task_id
 * @return array
 */
function wzz_move_task_to_folder($token, $folder_id,  $task_id) {
    $headers = array(
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    );
    
    $post_data = array(
        'id' => $task_id,
        'folder_id' => $folder_id
    );
    
    $response = wzz_request('POST', 'https://api.workhard.online/v2/customer/task/' . $task_id . '/move', $post_data, $headers);
    return json_decode($response, true);
}

/* 
 * Получить дынные о денежных операций
 *
 * @param string $token
 * @param string $order - сортировка данных (SORT_ASC, SORT_DESC)
 * @param array $type - Тип операции
 * @param int $limit - Сколько выводить записей
 * @param int $offset - номер страницы 
 * @return array
 */
function wzz_fetch_billing_operations($token, $order='SORT_ASC', $type = array(), $limit = null, $offset=null) {
    $headers = array(
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    );
    
    $params = array();
    $params['order'] = $order;
    if ($type) {
        $params['type'] = $type;
    }
    
    if ($limit) {
        $params['limit'] = $limit;
    }
    
    if ($offset) {
        $params['offset'] = $offset;
    }
    
    $response = wzz_request('GET', 'https://api.workhard.online/v2/common/billing/operations', $params, $headers);
    return json_decode($response, true);    
}

function wzz_request($method_name, $url, $params = array(), $headers = array()){
	$curl = curl_init();
    
    if ($headers) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    
    if ($method_name === 'GET'){
        $url .= '?' . http_build_query($params);
    } elseif($method_name === 'POST'){
	    $params_encode = http_build_query($params);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params_encode);
    } else{
        $params_encode = http_build_query($params);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method_name);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params_encode);
    }
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);	
    
    $response = curl_exec($curl);
    curl_close($curl);
    
	return $response;
}
