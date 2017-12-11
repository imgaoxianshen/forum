<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

//获取配置文件中一级分类二级分类的名字
function getCategoryNames($id)
{
  $category=config('category');
  foreach ($category as $key => $cate) {
    foreach($cate as $category_id=>$categoryName){
      if($category_id==$id){
        return [$key,$categoryName];
      }
    }
  }
  return [];
}
// 应用公共文件
