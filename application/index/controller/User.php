<?php
namespace app\index\controller;

use app\index\model\User as UserModel;
use app\index\model\User_detail as UserDetailModel;
use app\index\model\Topic as TopicModel;

class User extends \think\Controller
{
	public function avatar()
	{	
		$user = session('user');
		if(!$user){
			return $this->error('请先登录','index\index\login');
		}
		$this->assign([
			'user'=>$user,
		]);

		echo $this->fetch('avatar');
	}
	//上传新头像
	public function upload_avatar()
	{
		session_start();
		$file = request()->file('avatar');

		$id = input('post.user_id');
		$user = UserModel::find($id);

		if($file){
			$info = $file->move(ROOT_PATH.'public'.DS.'uploads');
			if($info){
				$user ->avatar = $info->getSaveName();
				$user ->save();
				session('user',$user);
				//echo $info->getExtension();//文件格式
				return $info->getSaveName();
				//echo $info->getFilename(); 
			}else{
				return $file->getError();
			}
		}
		
	}


	//个人信息
	public function my()
	{
		$user = session('user');
		if(!$user){
			return $this->error('请先登录','index\index\login');
		}
		$userdetail = UserDetailModel::where('user_id',$user->id)->find();

		if(request()->isPost()){

 			$postData=input('post.');

			//如果已经有这条记录--更新,没有就实例化之后添加
			if(empty($userdetail)){				
				$userdetail = new UserDetailModel();
			}
			$userdetail ->user_id = $user->id;
			$userdetail ->nickname = $postData['nickname'];
			$userdetail ->location = $postData['location'];
			$userdetail ->company = $postData['company'];
			$userdetail ->website = $postData['website'];
			$userdetail ->signature = $postData['signature'];
			$userdetail ->bio = $postData['bio'];
			$userdetail ->save();
		}

		$this->assign([
			'user'=>$user,
			'userdetail' =>$userdetail,
			]);
	       
		echo $this->fetch('my');
	}

	public function password()
	{
		$user = session('user');
		if(!$user){
			return $this->error('请先登录','index\index\login');
		}
		//修改密码
		if(request()->isPost()){
			$postData = input('post.');
			if(empty($postData['password'])||empty($postData['password_confirmation'])){
				$this->error('新密码或确认密码不能为空');
			}
			if($postData['password']!=$postData['password_confirmation']){
				$this->error('两次输入的密码不一致，请重试');
			}
			$old_password=$postData['old_password'];

			$user = UserModel::find($user->id);
			if($user->password!=md5(md5($postData['old_password']))){
				$this->error('原密码输入错误，无法修改');
			}
			$user->password =md5(md5($postData['password']));
			$user->save();
			$this->success('密码修改成功');
		}
		$this->assign([
			'user'=>$user,
			]);

		echo $this->fetch('password');
	}

	public function mytopic()
	{
		$user = session('user');
		if(!$user){
			return $this->error('请先登录','index\index\login');
		}
		$topics = TopicModel::where('is_delete',0)->where('user_id',$user->id)->select();
		//print_r($topics[0]['id']);
		$this->assign([
			'user'=>$user,
			'topics' =>$topics,
			]);
		echo $this->fetch('my_topic');
	}

	public function delTopic()
    {
    	$topicId = input('get.topicId');
    	$user = session('user');
    	if(!$user){
    		return;
    	}
    	$topic =TopicModel::find($topicId);
    	$topic ->is_delete =1;
    	$topic ->save();
    	$this ->success('該帖子已成功被刪除');
    }
}