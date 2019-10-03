<?php
namespace app\index\controller;

use app\index\model\Nav as NavModel;
use think\Session;
use think\Cookie;
use think\Validate;

class Nav {
//    获取导航栏的数据
    public function get_nav(){
        $content=new NavModel();
        $navArr=[];
        $navTop=$content->where('tid', 0)->column('name','id');
//      遍历然后形成一个二维数组，一维是主项目，二维是子项目
        foreach ($navTop as $a => $b) {
            array_push($navArr,$b);
            $item=$content->where('tid', $a)->column('name','id');
            $arr=[];
            foreach ($item as $c => $d){
                array_push($arr,$d);
            }

            $navArr[$b]=$arr;
        }
        return json_encode(
            [
                'code'=>"0000",
                'msg' =>$navArr ,
                ]
            );
    }

    }