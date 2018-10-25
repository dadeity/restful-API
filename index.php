<?php
/**
 * Created by PhpStorm.
 * User: Dwdmlos
 * Date: 2018/1/28
 * Time: 10:26
 */

require __DIR__.'/lib/User.php';
require __DIR__.'/lib/Article.php';
$pdo = require __DIR__.'/lib/db.php';
$user = new User($pdo);
print_r($user->login('jck','jck'));
//$article = new Article($pdo);
//print_r($article->create('文章标题','文章内容',3));
//print_r($article->view(1));
//print_r($article->edit(1,'文章标题2','文章内容2',3));
//var_dump($article->delete(1,3));
//var_dump($article->getList(3,1,3));