<?php
namespace app\index\model;

class Topic extends \think\Model
{
  public static function getTopic($id)
  {
  	//withCount 的参数通过名字找对应的方法计算总数
    return self::withCount(['praises'])->withCount(['replies'])->find(['id' =>$id]);
  }

  public static function getTopics($page,$limitNum)
  {
  	return self::withCount(['praises'])->withCount(['replies'])->where('is_delete',0)->page($page,$limitNum)->select();
  }

  public function replies()
  {
  	return $this->hasMany('reply','topic_id');
  }

  public function user()
  {
    return $this->belongsTo('User','user_id');
  }

  public function praises()
  {
  	return $this->hasMany('Praise','topic_id');
  }

  public static function getPageInfo($page,$limitNum,$count)
  {
  	$page = intval($page) < 1 ? 1:intval($page);
  	$pageNum = ceil($count/$limitNum);
  	$page = $page > $pageNum ? $pageNum : $page;

  	$showPages = [];

  	for($leftPage = $page - 2;$leftPage <= $page ;$leftPage++){
  		if($leftPage > 0){
  			$showPages[] = $leftPage;
  		}
  	}

  	for($i = 1;$i <= 2 ;$i++){
  		if($page + $i<= $pageNum){
  			$showPages[] =$page + $i;
  		}	
  	}
  	return ['page' => $page,'showPages' =>$showPages,'pageNum' =>$pageNum];

  }


  public static function search($keyword,$page,$limitNum)
  {
  	$cond=[];
  	$cond['title']=['like','%'.$keyword.'%'];
  	$res=array();
  	$res['rows']=self::withCount(['praises'])->withCount(['replies'])->where($cond)->page($page,$limitNum)->where('is_delete',0)->select();
  	$res['total']=self::where($cond)->where('is_delete',0)->count();
  	return $res;
  }

  public static function searchByTime($page,$limitNum)
  {
    $res=array();
    $res['rows']= self::withCount(['praises'])->withCount(['replies'])->page($page,$limitNum)->where('is_delete',0)->order('created_at desc')->select();
    $res['total']=self::where('is_delete',0)->count();
    return $res;
  }

  public static function searchByParise($page,$limitNum)
  {
    $res=array();
    $res['rows']= self::withCount(['praises'])->withCount(['replies'])->page($page,$limitNum)->where('is_delete',0)->order('praises_count desc')->select();
    $res['total']=self::where('is_delete',0)->count();
    return $res;
  }


  public static function getTagTopics($topicIds,$page,$limitNum)
  {
  	$cond = [];
  	$cond['id'] = ['in',$topicIds];
  	$res=array();
  	$res['rows']= self::withCount(['praises'])->withCount(['replies'])->where($cond)->page($page,$limitNum)->select();
  	$res['total'] = self::where($cond)->count();
  	return $res;
  }
}
