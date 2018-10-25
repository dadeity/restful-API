<?php
/**
 * Created by PhpStorm.
 * User: Dwdmlos
 * Date: 2018/1/28
 * Time: 11:49
 */

class ErrorCode
{
    const USERNAME_EXISTS = 1;                   //用户名已存在
    const PASSWORD_CANNOT_EMPTY = 2;            //密码不能为空
    const USERNAME_CANNOT_EMPTY = 3;            //用户名不能为空
    const REGISTER_FAIL = 4;                     //注册失败
    const USERNAME_OR_PASSWORD_INVALID = 5;     //用户名或者密码错误
    const ARTICLE_TITLE_CANNOT_EMPTY = 6;       //文章标题不嫩为空
    const ARTICLE_CONTENT_CANNOT_EMPTY = 7;     //文章标题不嫩为空
    const ARTICLE_CREATE_FAIL = 8;               //文章发表失败
    const ARTICLE_ID_CANNOT_EMPTY = 9;           //文章ID不能为空
    const ARTICLE_NOT_FOND = 10;                  //文章不存在
    const PERMISSION_DENIED = 11;                 //您无权操作
    const ARTICLE_EDIT_FAIL = 12;                 //编辑文章失败
    const ARTICLE_DELETE_FAIL = 13;               //文章删除失败
    const PAGE_SIZE_TO_BIG = 14;                  //分页大小最大为100
    const SERVER_INTERNAL_ERROR = 15;             //服务器内部错误
}