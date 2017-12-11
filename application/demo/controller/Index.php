<?php
namespace app\demo\controller;

use app\demo\model\User;
use app\demo\model\Topic;
use app\demo\model\Reply;
use app\demo\model\Praise;

class Index extends \think\Controller
{
	function __construct()
	{
		parent::__construct();
		if($this->request->action() !='login'){
			$user = session('adminUser');
			if(!$user||!$user->is_admin){
				return $this->error('您還未登錄或者你沒有管理員權限');
			}
		}
	}
    public function index()
    {
       $user =session('adminUser');
    	$this->assign([
      		'user' => $user,
      		'active' => 'index',
      		'userCount' =>User::count(),
      		'praiseCount' =>Praise::count(),
      		'replyCount' =>Reply::count(),
      		'topicCount'=>Topic::where('is_delete',0)->count(),
    	]);

    echo $this->fetch('index');

    }

    public function login()
    {
    	if (request()->isPost()) {
	    	$login = input('post.username');
	      	$password = input('post.password');
	      	$cond = array();
	      	$cond['name|email'] = $login;
	      	$cond['password'] = md5(md5($password));
	      	$user = User::get($cond);
	      	if ($user && $user->is_admin) {
	       	 	session('adminUser', $user);
	        	return $this->success('登陆成功！', 'index/index');
	      	}
	      	return $this->error('登陆失败或者用户不是管理员！');
            
	    }
    	return $this->fetch('login');
    }

    public function topic_manage()
    {
        $user = session('adminUser');
    	$this->assign([
    		'topics' =>Topic::where(['is_delete'=>0])->select(),
            'active' => 'topic_manage',
    		'user' =>$user,
    		]);


    	echo $this->fetch('topic_manage');
    }

    public function delTopic()
    {
    	$topicId = input('get.topicId');
    	$user = session('adminUser');
    	if(!$user){
    		return;
    	}
    	$topic =Topic::find($topicId);
    	$topic ->is_delete =1;
    	$topic ->save();
    	$this ->success('該帖子已成功被刪除');
    }

    public function user_manage()
    {
        $user = session('adminUser');
        $this->assign([
            'users' =>User::getUsers(),
            'active' =>'topic',
            'user' =>$user,
            'active' => 'user_manage',
            ]);

        echo  $this->fetch('user_manage');
    }

    public function deluser()
    {
        $userId = input('get.userId');
        $user = session('adminUser');
        if(!$user){
            return;
        }
        $user =User::find($userId);
        $user ->is_delete =1;
        $user ->save();
        $this ->success('該用户已成功被刪除');
    }

    public function setAdmin()
    {
        $userId=input('get.userId');
        $user = session('adminUser');
        if(!$user){
            return;
        }
        $user =User::find($userId);
        $user ->is_admin=1;
        $user ->save();
        $this ->success('该用户已被设为管理员');
    }

    //退出登录
    public function checkout()
    {
        session('adminUser', null);
        $this->success('退出登陆成功','index/login');
    }


}




