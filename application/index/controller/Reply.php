<?php

namespace app\index\Controller;

use app\index\model\Reply as ReplyModel;

class Reply extends \think\Controller
{
	public function newReply()
	{
		$postData = input('post.');
		$user = session('user');
		$reply = new ReplyModel();
		$reply ->content =$postData['replyContent'];
		//echo $postData['replyContent'];
		if(isset($postData['replyId'])&& intval($postData['replyId'])>0){
			$reply ->reply_id =$postDatat['replyId'];
		}else{
			$reply ->reply_id =0;
		}
		$reply ->topic_id = $postData['topic_id'];
		$reply ->created_at =intval(microtime(true));
		$reply ->user_id=$user->id;
		$reply ->save();
		$this ->success('评论成功！');
	}
}