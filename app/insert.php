<?php

// 插入项目：新建list

header('Content-type:text/html;charset:utf-8');

//拿到前端传入的文本
$new_project = isset($_POST['new_project']) ? trim($_POST['new_project']) : '';

// 启用session
session_start();
// 检查session
$admin = $_SESSION['admin'];

// 数据合法性验证：内容均不能为空
if (empty($new_project)) {
    //提示同时回到提交页面
    header('Refresh:2;url=index.php'); //header前不能有输出
    // 阻止脚本进行
    exit('内容不能为空！');
}

// 分割新建内容与标签
$explore_array = explode(" ", $new_project);
$new_project_name = $explore_array[0];
$new_project_label = $explore_array[1];

// 数据入库
include_once 'database_login.php';

// 插入数据
$public_time = time();
$sql = "INSERT INTO project VALUES(NULL,0,'{$new_project_name}',{$public_time},'{$new_project_label}','{$admin}')";

// 检查是否有相同数据
$check_sql = query($link, "SELECT * FROM project WHERE content = '{$new_project_name}'");
$check_finished = mysqli_fetch_array($check_sql, MYSQLI_ASSOC);
// var_dump($check_finished);

// 执行指令
if ($admin != null) {
    if ($new_project_name == $check_finished['content'] && $admin == $check_finished['admin']) {
        echo '数据库中已有此条数据';
    } else if ($new_project_name && query($link, $sql)) {
        echo '数据插入成功';
    } else if ($new_project_name == '') {
        echo '插入的数据为空';
    } else {
        echo '数据插入失败';
    }
}

// 跳转回主页
include_once 'utils.php';
back2index();