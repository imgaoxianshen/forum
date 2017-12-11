<?php
namespace app\index\controller;
use app\index\model\User as UserModel;
use app\index\model\Tag as TagModel;
use app\index\model\Topic as TopicModel;
use app\index\model\TopicTag as Topic_TagModel;
use app\index\model\Praise as PraiseModel;
use app\index\model\Reply as ReplyModel;
use app\index\model\Tips as TipsModel;
class Topic extends \think\Controller
{
    public function newTopic()
    {
      $user=session('user');
      if(empty($user)){
          return $this->error("您还未登陆，无法发帖，请先登录");
      }

      if(request()->isPost()){
        $postData=input('post.');
        //编辑的话有topic_id
        if(empty($postData['thread']['topic_id'])){
          $topic =new TopicModel();
        }else{
          $topic = TopicModel::find($postData['thread']['topic_id']);
        }

        $topic->title=$postData['thread']['title'];
        //节点
        $topic->category_id=$postData['thread']['category_id'];
        $topic->content=$postData['thread']['body'];
        $topic->user_id=$user->id;
        $topic->created_at=intval(microtime(true));
        $topic->save();
        //处理tags
        //处理tag之前全删了
        
        Topic_TagModel::where('topic_id',$topic->id)->delete();
       
        $tags=$postData['tags'];
        foreach($tags as $tag){
          //已有标签
          if(is_numeric($tag)){

            $this->createTopicTag($tag,$topic->id);
            continue;
          }
          //新建标签
          $newTag=$this->createTag($tag);
          $this->createTopicTag($newTag->id,$topic->id);
        }

        if(empty($postData['thread']['topic_id'])){
          $this->success('恭喜，发表了新的帖子~');
        }else{
          $this->success('恭喜，修改了你的的帖子~');
        }
        
      }

      $this->assign([
        'user' =>session('user'),
        'category' =>config('category'),
        'tags' =>TagModel::all()
      ]);
      echo  $this->fetch('newpost');
    }

    private function createTopicTag($tagId,$topicId)
    {
      $topicTag = new Topic_TagModel();
      $topicTag->tag_id =$tagId;
      $topicTag->topic_id=$topicId;
      $topicTag->save();
    }

    private function createTag($name)
    {
      $tag=new TagModel();
      $tag->name=$name;
      $tag->save();
      return $tag;
    }


    public function detail()
    {
      $topicId=input('get.id');
      $topic =TopicModel::getTopic($topicId);
      $user=session('user');
      $tipscount = TipsModel::count();
      $tipscontent=TipsModel::find(mt_rand(1,$tipscount));
     
      $this->assign([
        'user'=>$user,
        'replies'=>ReplyModel::where(['topic_id'=>$topicId])->select(),
        'topic'=>$topic,
        'usercount' => UserModel::count(),
        'tagcount' =>TagModel::count(),
        'replycount' =>ReplyModel::count(),
        'tipscontent'=>$tipscontent->content,
        'topicTags'=>Topic_TagModel::getTopicTagsByTopicId($topic->id),
        'categoryNames'=>getCategoryNames($topic->category_id),
      ]);

      echo  $this->fetch('detail');
    }

    public function praise()
    {
      $user =session('user');
      //未登录无法点赞
      if(!$user){
        return $this->error('您还未登陆，无法点赞');
      }
      if(request()->isPost()){
        $topicId = intval(input('post.topicId'));
      }
      if(request()->isGet()){
        $topicId = intval(input('get.topicId'));
      }
      $praise = PraiseModel::get(['user_id'=>$user->id,'topic_id'=>$topicId,'type'=>0]);
      if($praise){
        $praise->delete();
        return $this->success('取消赞成功');
      }else {
        $praise = new PraiseModel();
        $praise ->user_id = $user ->id;
        $praise ->topic_id=$topicId;
        $praise ->created_at=intval(microtime(true));
        $praise ->type=0;
        $praise->save();
        return $this->success('点赞成功');
      }

    }


    public function index()
    {
      //page 当前页
      $page = input('get.page');
      //showPages , pageNum --总页数
      $count = TopicModel::where('is_delete',0)->count();
      $pageInfo = TopicModel::getPageInfo($page,config('limitNum'),$count);

      //算tips表中总共有几个数据
      $tipscount = TipsModel::count();
      //取其中一条数据
      $tipscontent=TipsModel::find(mt_rand(1,$tipscount));

      //$cacheName = 'index'.$pageInfo['page'].'topics';
      //缓存会影响点赞效果的实现
      // $topics = cache($cacheName);
      // if(!$topics){
        $topics = TopicModel::getTopics($page,config('limitNum'));
      //  cache($cacheName,$topics,1000);
      // }

      $this->assign([
        'hotTags'=>Topic_TagModel::getHotTags(config('hotTagsNum')),
        'user' =>session('user'),
        'tipscontent' =>$tipscontent->content,
        'topics' => $topics,
        'usercount' => UserModel::count(),
        'tagcount' =>TagModel::count(),
        'replycount' =>ReplyModel::count(),
        'page' => $pageInfo['page'],
        'showPages' => $pageInfo['showPages'],
        'pageNum' => $pageInfo['pageNum'],
      ]);
     
      echo $this->fetch("index");
    }
    //关键字模糊查询（标题）
    public function search()
    {
      $page = input('get.page');//当前页
      $keyword=input('get.keyword');
      $res=TopicModel::search($keyword,$page,config('limitNum'));
      $topics=$res['rows'];
      $total=$res['total'];//这页有几个
      $pageInfo = TopicModel::getPageInfo($page,config('limitNum'),$total);
       //算tips表中总共有几个数据
      $tipscount = TipsModel::count();
      //取其中一条数据
      $tipscontent=TipsModel::find(mt_rand(1,$tipscount));
      $this->assign([
        'topics' =>$topics,
        'hotTags'=>Topic_TagModel::getHotTags(config('hotTagsNum')),
        'user' =>session('user'),
        'usercount' => UserModel::count(),
        'tagcount' =>TagModel::count(),
        'keyword'=>$keyword,
        'replycount' =>ReplyModel::count(),
        'tipscontent' =>$tipscontent->content,
        'page' => $pageInfo['page'],
        'showPages' => $pageInfo['showPages'],
        'pageNum' => $pageInfo['pageNum'],
        ]);
      echo $this->fetch('index');
    }

    public function searchByTime()
    {
      $page = input('get.page');
      $res = TopicModel::searchByTime($page,config('limitNum'));
      $topics = $res['rows'];
      $total = $res['total'];
      $pageInfo = TopicModel::getPageInfo($page,config('limitNum'),$total);
       //算tips表中总共有几个数据
      $tipscount = TipsModel::count();
      //取其中一条数据
      $tipscontent=TipsModel::find(mt_rand(1,$tipscount));
      
      $this->assign([
        'topics' =>$topics,
        'hotTags'=>Topic_TagModel::getHotTags(config('hotTagsNum')),
        'user' =>session('user'),
        'usercount' => UserModel::count(),
        'tagcount' =>TagModel::count(),
        'replycount' =>ReplyModel::count(),
        'tipscontent' =>$tipscontent->content,
        'page' => $pageInfo['page'],
        'showPages' => $pageInfo['showPages'],
        'pageNum' => $pageInfo['pageNum'],
        ]);
      
     echo $this->fetch('index');

    }

    public function searchByParise()
    {
      $page = input('get.page');
      $res = TopicModel::searchByParise($page,config('limitNum'));
      $topics = $res['rows'];
      $total = $res['total'];
      $pageInfo = TopicModel::getPageInfo($page,config('limitNum'),$total);
       //算tips表中总共有几个数据
      $tipscount = TipsModel::count();
      //取其中一条数据
      $tipscontent=TipsModel::find(mt_rand(1,$tipscount));
      
      $this->assign([
        'topics' =>$topics,
        'hotTags'=>Topic_TagModel::getHotTags(config('hotTagsNum')),
        'user' =>session('user'),
        'usercount' => UserModel::count(),
        'tagcount' =>TagModel::count(),
        'replycount' =>ReplyModel::count(),
        'tipscontent' =>$tipscontent->content,
        'page' => $pageInfo['page'],
        'showPages' => $pageInfo['showPages'],
        'pageNum' => $pageInfo['pageNum'],
        ]);
      echo $this->fetch('index');
    }




    public function tag()
    {
      $page = input('get.page');//当前页
      $tagId = input('get.tag');
        //算tips表中总共有几个数据
      $tipscount = TipsModel::count();
      //取其中一条数据
      $tipscontent=TipsModel::find(mt_rand(1,$tipscount));
      $topicIds = Topic_TagModel::where(['tag_id'=>$tagId])->column('topic_id');
      $res = TopicModel::getTagTopics($topicIds,$page,config('limitNum'));
      $total=$res['total'];
      $topics = $res['rows'];
      $pageInfo = TopicModel::getPageInfo($page,config('limitNum'),$total);
      $this->assign([
        'topics'=> $topics,
        'usercount' => UserModel::count(),
        'user' =>session('user'),
        'tagcount' =>TagModel::count(),
        'replycount' =>ReplyModel::count(),
        'hotTags'=>Topic_TagModel::getHotTags(config('hotTagsNum')),
        'tipscontent' =>$tipscontent->content,
        'page' => $pageInfo['page'],
        'showPages' => $pageInfo['showPages'],
        'pageNum' => $pageInfo['pageNum'],
      ]);
      echo $this->fetch('index');
    }

    public function editTopic()
    {
      $user=session('user');
      if(empty($user)){
          return $this->error("您还未登陆，无法编辑帖子");
      }
      if(request()->isGet()){
        $topicId = input('get.id');
        $topic = TopicModel::where('is_delete',0)->find($topicId);
        if(empty($topic)){
          $this->error('该帖子不存在');
        }
        //找适配tag
        $topic_tag = Topic_TagModel::where('topic_id',$topicId)->select();
        $tts=array();
        foreach($topic_tag as $tt){
          $tts[]=$tt['tag_id'];
        }


        $this->assign([
          'topic'=> $topic, 
          'category' =>config('category'),
          'tags' =>TagModel::all(),
          'tts' =>$tts, 
        ]);

      }

      echo $this->fetch('newpost');

    }


}
