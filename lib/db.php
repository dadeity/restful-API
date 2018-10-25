<?php
/**
 * 连接数据库并返回数据库句柄
 * Created by PhpStorm.
 * User: Dwdmlos
 * Date: 2018/1/28
 * Time: 10:51
 */
$pdo = new PDO('mysql:host=localhost;dbname=restful','root','root');
$pdo -> setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
return $pdo;