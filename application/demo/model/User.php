<?php
namespace app\demo\model;

use think\Model;

class User extends Model{
 public static function getUsers()
  {
  	
    return self::withCount(['topics'])->where('is_delete',0)->select();
  }

  public function topics() {
    return $this->hasMany('Topic', 'user_id');
  }
}
