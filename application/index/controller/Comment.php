<?php
namespace app\index\controller;


use app\index\model\Comment as CommentModel;
use think\Session;
use think\Cookie;
use think\Validate;

class Comment extends Auth{
    protected $beforeActionList = [
//        'isAuthed' => ['only' => 'get_name']
    ];
    public function get_recent_comment(){
        $comment=new CommentModel();
        $memberName=input('get.memberName');
        // get user的id
        $userId=$comment->query('select userid from tp_member where username="'.$memberName.'"');
        $data=$comment->query('select  a.time, b.title, a.content from tp_comment a  join tp_content b where  a.uid='.$userId[0]['userid'].' and a.fid=b.id');
        return json_encode(
            [
                'code'=>"0000",
                'msg'=>$data
            ]
        );
    }
    public function uploads()
    {
        $file = request()->file('file');
        //上传回调error为0
        if(empty($file)){
            $result["error"] = "1";
            $result['data'] = '';
        }else{
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/editor' );
            if($info){
                $name_path =$info->getSaveName();
                //成功上传后 获取上传信息
                $name_path =str_replace('\\',"/",$info->getSaveName());
                $result["error"] = '0';
                $result['data'] = "/blog/public/uploads/editor/".$name_path;
            }else{
                // 上传失败获取错误信息
                $result["code"] = "2";
                $result['data'] ='';
            }
        }
        exit(json_encode($result));
    }
    public function submit_comment(){
        $comment=new CommentModel();
        $comment->time=time();
        $comment->content=input('post.commentHtml');
        $comment->uid=Session::get('id');
        $fid=$comment->query('select id from tp_content where title="'.input('post.article').'"');
        $comment->fid=$fid[0]['id'];
        $comment->save();
        return json_encode(
            [
                'code'=>"0000",
                'msg'=>'提交成功'
            ]
        );
    }
    public function get_comment_list(){
        $comment=new commentModel();
        $titleName=input('get.title');
        $titleId=$comment->query('select id from tp_content where title="'.$titleName.'"');
        $uid=$comment->query('select b.userhead, b.username,a.time,a.content from tp_comment as a  join tp_member as b where  a.fid="'.$titleId[0]["id"].'"and  a.uid=b.userid ');
        return json_encode(
            [
                'code'=>'0000',
                'data'=>$uid
            ]
        );
    }
}
