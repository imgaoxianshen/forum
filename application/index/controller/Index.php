<?php
namespace app\index\controller;

use app\index\model\User;

class Index extends \think\Controller
{
    public function register()
    {
      session_start();
      if(request()->isPost()){
        //把所有表单数据放入postDate
        $postDate=input('post.');
        if(!captcha_check($postDate['verifycode'])){
          return $this->error('验证码校验失败');
        }
        if(!$this->checkPassword($postDate)){
          return $this->error('密码校验失败');
        }
        $user=new User();
        $user->name=$postDate['username'];
        $user->email=$postDate['email'];
        $user->avatar='images/avatar.jpg';
        $user->password=md5(md5($postDate['password']));
        //返回当前时间戳
        $user->created_at=intval(microtime(true));
        $user->save();
        return $this->success('恭喜你注册成功！');
      }
      echo $this->fetch("register");
    }

    private function checkPassword($data)
    {
      if(!$data['password']){
        return false;
      }
      if($data['password']!==$data['password_confirmation']){
        return false;
      }
      return true;
    }


    public function login()
    {
      if(request()->isPost()){
        $login=input('post.login');
        $password=input('post.password');
        //查询条件
        $cond=[];
        $cond['name|email'] =$login;
        $cond['password']=md5(md5($password));
        $user = User::get($cond);
        if($user) {
          session('user',$user);
          return $this->success('恭喜你~登陆成功！','\index\topic\index');
        }
        return $this->error('抱歉，登陆失败，可能是用户名或者密码错误！');

      }
      echo $this->fetch("login",['user', session('user')]);
    }

    public function logout(){
      session('user',null);
      echo $this->fetch("login");
    }






}
