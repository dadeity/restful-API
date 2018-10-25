<?php
/**
 * Created by PhpStorm.
 * User: Dwdmlos
 * Date: 2018/1/28
 * Time: 18:17
 */
require __DIR__.'/../lib/User.php';
require __DIR__.'/../lib/Article.php';
$pdo = require __DIR__.'/../lib/db.php';
class Restful
{
    /**
     * @var User
     */
    private $_user;
    /**
     * @var Article
     */
    private $_article;
    /**
     * 请求方法
     * @var string
     */
    private $_requestMethod;

    /**
     * 请求的资源名称
     * @var string
     */
    private $_resourcName;

    /**
     * 请求的资源ID
     * @var string
     */
    private $_id;

    /**
     * 允许的请求资源列表
     * @var array
     */
    private $_allowResourcs = ['users','articles'];

    /**
     * 允许请求的HTTP方法
     * @var array
     */
    private $_allowRequestMethods = ['GET','POST','PUT','DELETE','OPTIONS'];

    /**
     * 状态码
     * @var array
     */
    private $_statusCode = [
        200 => 'OK',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Server Internal Error',
    ];
    /**
     * Restful constructor.
     * @param $_user
     * @param $_article
     */
    public function __construct($_user, $_article)
    {
        $this->_user = $_user;
        $this->_article = $_article;
    }
    public function run()
    {
        try {
            $this->_setupRequestMethod();
            $this->_setupResource();
            if ($this->_resourcName == 'users') {
                $this->_json($this->_handleUser());
            } else {
                $this->_json($this->_handleActicle());
            }
        } catch (Exception $e) {
            $this->_json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 初始化请求方法
     */
    private function _setupRequestMethod()
    {
        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
        if (!in_array($this->_requestMethod,$this->_allowRequestMethods)) {
            throw new Exception('请求不被允许',405);
        }
    }

    /**
     * 初始化请求资源
     */
    private function _setupResource()
    {
        $path = $_SERVER['PATH_INFO'];
        $params = explode('/',$path);
        $this->_resourcName = $params[1];
        if (!in_array($this->_resourcName,$this->_allowResourcs)) {
            throw new Exception('请求资源不被允许',400);
        }
        if (empty(!$params[2])) {
            $this->_id = $params[2];
        }
    }
    /**
     * 输出json
     * @param $array
     */
    private function _json($array,$code = 0)
    {
        if ($array === null && $code === 0) {
            $code = 204;
        }
        if ($array !== null && $code === 0) {
            $code = 200;
        }
        header('HTTP/1.1 ' . $code . '' . $this->_statusCode[$code]);
        header('Content-Type:application/json;charset=utf-8');
        if ($array !== null) {
            echo json_encode($array,JSON_UNESCAPED_UNICODE);
        }
        exit();
    }
    /**
     * 请求用户
     * @return array
     * @throws Exception
     */
    private function _handleUser()
    {
        if ($this->_requestMethod !== 'POST') {
            throw new Exception('请求方法不被允许',405);
        }
        $body = $this->_getBodyParmas();
        if (empty($body['username'])) {
            throw new Exception('用户名不能为空',400);
        }
        if (empty($body['password'])) {
            throw new Exception('用户密码不能为空',400);
        }
        return $this->_user->register($body['username'],$body['password']);
    }

    /**
     * 请求文章资源
     */
    private function _handleActicle()
    {
        switch ($this->_requestMethod) {
            case 'POST':
                return $this->_handleActicleCreate();
            case 'PUT':
                return $this->_handleActicleEdit();
            case 'DELETE':
                return $this->_handleActicleDelete();
            case 'GET':
                if (empty($this->_id)) {
                    return $this->_handleActicleList();
                } else {
                    return $this->_handleActicleView();
                }
            default :
                throw new Exception('请求方法不被允许',405);
        }
    }

    /**
     * 获取请求体参数
     * @return string
     * @throws Exception
     */
    private function _getBodyParmas()
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            throw new Exception('请求参数错误',400);
        }
        return json_decode($raw,true);

    }
    /**
     * 创建文章
     * @return array
     * @throws Exception
     */
    private function _handleActicleCreate()
    {
        $body = $this->_getBodyParmas();
        if (empty($body['title'])) {
            throw new Exception('文章标题不能为空',400);
        }
        if (empty($body['content'])) {
            throw new Exception('文章内容不能为空',400);
        }
        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
        try {
            $acticle = $this->_article->create($body['title'],$body['content'],$user['userId']);
            return $acticle;
        } catch (Exception $e) {
            if (!in_array($e->getCode(),
                [
                    ErrorCode::ARTICLE_TITLE_CANNOT_EMPTY,
                    ErrorCode::ARTICLE_CONTENT_CANNOT_EMPTY,
                ])
            ) {
                throw new Exception($e->getCode(),400);
            }
            throw new Exception($e->getCode(),500);
        }

    }
    /**
     * 文章编辑
     * @return array|mixed
     * @throws Exception
     */
    private function _handleActicleEdit()
    {
        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
        try {
            $article = $this->_article->view($this->_id);
            if ($article['userId'] != $user['userId']) {
                throw new Exception('您无权编辑',403);
            }
            $body = $this->_getBodyParmas();
            $title = empty($body['title']) ? $article['title'] : $body['title'];
            $content = empty($body['content']) ? $article['content'] : $body['content'];
            if ($title == $article['title'] && $content == $article['content']) {
                return $article;
            }
            return $this->_article->edit($article['articleId'],$title,$content,$user['userId']);
        } catch (Exception $e) {
            if ($e->getCode() < 100) {
                if ($e->getCode() == ErrorCode::ARTICLE_NOT_FOND) {
                    throw new Exception($e->getMessage(),404);
                } else {
                    throw new Exception($e->getMessage(),400);
                }
            } else {
                throw $e;
            }
        }
    }

    /**
     * 删除文章
     * @return null
     * @throws Exception
     */
    private function _handleActicleDelete()
    {
        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
        try {
            $article = $this->_article->view($this->_id);
            if ($article['userId'] != $user['userId']) {
                throw new Exception('您无权编辑',403);
            }
            $this->_article->delete($article['articleId'],$user['userId']);
            return null;
        } catch (Exception $e) {
            if ($e->getCode() < 100) {
                if ($e->getCode() == ErrorCode::ARTICLE_NOT_FOND) {
                    throw new Exception($e->getMessage(),404);
                } else {
                    throw new Exception($e->getMessage(),400);
                }
            } else {
                throw $e;
            }
        }
    }
    /**
     * 文章分页
     * @return array
     * @throws Exception
     */
    private function _handleActicleList()
    {
        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $size = isset($_GET['size']) ? $_GET['size'] : 10;
        if ($size > 100) {
            throw new Exception('分页大小最大为100',400);
        }
        return $this->_article->getList($user['userId'],$page,$size);
    }
    private function _handleActicleView()
    {
        try {
            return $this->_article->view($this->_id);
        } catch (Exception $e) {
            if ($e->getCode() == ErrorCode::ARTICLE_NOT_FOND) {
                throw new Exception($e->getMessage(),404);
            } else {
                throw new Exception($e->getMessage(),500);
            }
        }
    }

    /**
     * 用户登录
     * @param $PHP_AUTH_USER
     * @param $PHP_AUTH_PW
     */
    private function _userLogin($PHP_AUTH_USER,$PHP_AUTH_PW)
    {
        try {
            return $this->_user->login($PHP_AUTH_USER,$PHP_AUTH_PW);
        } catch (Exception $e) {
            if (!in_array($e->getCode(),
                [
                    ErrorCode::USERNAME_CANNOT_EMPTY,
                    ErrorCode::PASSWORD_CANNOT_EMPTY,
                    ErrorCode::USERNAME_OR_PASSWORD_INVALID,
                ])
            ) {
                throw new Exception($e->getMessage(),401);
            }
            throw new Exception($e->getMessage(),500);
        }
    }
}
$user = new User($pdo);
$article = new Article($pdo);
$restful = new Restful($user,$article);
$restful->run();

