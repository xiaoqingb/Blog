<?php
namespace app\index\controller;

use app\index\model\Content as ContentModel;
use think\Session;
use think\Cookie;
use think\Validate;

class Content extends Auth{
    protected $beforeActionList = [
        'isAuthed' => ['only' => 'show_article']
    ];

//  获取轮播图的图片地址
    public function get_carousel_pic(){
        $content=new ContentModel();
//      设置要显示的图片数
        $picNum=input('get.picNum');
        $picSrc=$content->query('select pic,title from tp_content order by time desc limit '.$picNum);
        return json_encode(
                    [
                        'code'=>"0000",
                        'msg'=>$picSrc,
                    ]
                );
    }
//  获取文章列表
    public function get_article_list(){
        $content=new ContentModel();
        $keyword=input('get.keyword');
        if($keyword){
//          每一个列表的每一项相同内容都取出来形成一个数组
            $description=$content->where("keywords",'like','%'.$keyword.'%')->column('description') ;
            if(!$description)
            return json_encode(
                [
                    'code'=>"0001",
                    'msg'=>"搜索内容不存在",
                ]
            );
            $pic=$content->where("keywords",'like','%'.$keyword.'%')->column('pic') ;
            $view=$content->where("keywords",'like','%'.$keyword.'%')->column('view') ;
            $id=$content->where("keywords",'like','%'.$keyword.'%')->column('uid');
            $reply=$content->where("keywords",'like','%'.$keyword.'%')->column('reply');
            $title=$content->where("keywords",'like','%'.$keyword.'%')->column('title');
            $time=$content->where("keywords",'like','%'.$keyword.'%')->column('time');

//          根据id从会员表中取出名字，若没有名字就默认匿名
            foreach ($id as $item){
                $result=$content->query('select username from tp_member where userid='.$item);
                if (!$result){
                    $nameList[]='匿名';
                }else{
                    $result=$result[0]['username'];
                    $nameList[]= $result;
                }
            }
            return json_encode(
                [
                    'code'=>"0000",
                    'msg'=>[$title,$description,$pic,$view,$reply,$id,$nameList,$time],
                ]
            );
        }
        $limitNum=input('get.num');
        $description=$content->orderRaw('id desc')->limit($limitNum)->column('description') ;
        $pic=$content->orderRaw('id desc')->limit($limitNum)->column('pic') ;
        $view=$content->orderRaw('id desc')->limit($limitNum)->column('view') ;
        $id=$content->orderRaw('id desc')->limit($limitNum)->column('id');
        $reply=$content->orderRaw('id desc')->limit($limitNum)->column('reply');
        $title=$content->orderRaw('id desc')->limit($limitNum)->column('title');
        $time=$content->orderRaw('id desc')->limit($limitNum)->column('time');
        foreach ($id as $item){
            $result=$content->query('select username from tp_member where userid='.$item);
            if (!$result){
                $nameList[]='匿名';
            }else{
                $result=$result[0]['username'];
            $nameList[]= $result;
            }
        }
        return json_encode(
                    [
                        'code'=>"010",
                        'msg'=>[$title,$description,$pic,$view,$reply,$id,$nameList,$time],
                    ]
                );
    }
//  获取指定会员的文章列表
    public function get_member_article(){
        $content=new ContentModel();
        $memberName=input("get.memberName");
        $menberId=$content->query("select userid from tp_member where username='".$memberName."'");
        $menberId=$menberId[0]['userid'];
        $title=$content->orderRaw('time desc')->where('uid',$menberId)->column('title');
        $time=$content->orderRaw('time desc')->where('uid',$menberId)->column('time');

        return json_encode(
            [
                'code'=>"010",
                'msg'=>[$title,$time]
            ]
        );
    }
//  获取主页面右边的文章数，会员数，回复量
    public function get_article_menber_reply_count(){
        $content=new ContentModel();
        $articleCount=$content->field('count(*) as article')->count();
        $replyCount=$content->where('reply',">","0")->count();
//      这里也是跨表查询了会员数
        $menberCount=$content->query('select count(*) as count from tp_member ');
        $menberCount=$menberCount[0]['count'];

        return json_encode(
            [
                'code'=>"010",
                'msg'=>[$articleCount,$menberCount,$replyCount],
            ]
        );
    }
//    提交稿子
    public function submit_article(){
        $content=new ContentModel();
        $content->content=input('post.articleHtml');
        $content->description=input('post.articleText');
        $content->title=input('post.articleTitle');
        $content->time=time();
        $content->uid=Session::get('id');

        $pattern ='<img.*?src="(.*?)">';
        $html =input('post.articleHtml') ;
        preg_match($pattern,$html,$matches);
        if (!$matches[1]){
            $matches[1]='\blog\public\static\uploads\20170401\00.jpg';
        }
        $content->pic=$matches[1];
        $content->save();
        return json_encode(
            [
                'code'=>"010",
                'msg'=>"2333",
            ]
        );
    }
//    显示指定文章
    public function show_article(){
        $content=new ContentModel();
        $view=$content->query('select `view` from tp_content where title="'.input('get.title').'"');
        $view=(int) $view[0]['view'] +1;
        $result=$content->table('tp_content')->where('title', input('get.title'))->update(['view' => $view]);
        $msg1=$content->query('select content from tp_content where title="'.input('get.title').'"');
        return json_encode(
            [
                'code'=>"010",
                'msg'=> $msg1,
            ]
        );
    }
//    编辑文章
    public function edit_article(){
        $content=new ContentModel();
        $contentId=$content->query('select id from tp_content where title="'.input('post.oldTitle').'"');
//        $result=$content->query('update tp_content set content="'.input('post.articleHtml').'" where id="'..'"');
        $result=$content->table('tp_content')->where('id', $contentId[0]['id'])->update(['content' => input('post.articleHtml')]);
//        $result=$content->query('update tp_content set title="'.input('post.newTitle').'" where title="'.input('post.oldTitle').'"');
        $result=$content->table('tp_content')->where('id', $contentId[0]['id'])->update(['title' => input('post.newTitle')]);
        $pattern ='<img.*?src="(.*?)">';
        $html =input('post.articleHtml') ;
        preg_match($pattern,$html,$matches);
        if ($matches===[]){
            $matches[1]='static\uploads\20170401\00.jpg';
        }
//        $result=$content->query('update tp_content set pic="'.$matches[1].'" where id="'.$contentId.'"');
        $result=$content->table('tp_content')->where('id', $contentId[0]['id'])->update(['pic' => $matches[1]]);
//        $result=$content->query('update tp_content set description="'.input('post.articleText').'" where id="'.$contentId.'"');
        $result=$content->table('tp_content')->where('id', $contentId[0]['id'])->update(['description' => input('post.articleText')]);
        //TODO: 这里用原生query更新数据表tp_content里的content内容时，会报错，update语句不能含有html内容，花费了我好多时间0.0

        return json_encode(
            [
                'code'=>"0000",
                'msg'=>$result ,
            ]
        );
    }
//    上传图片
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
//    获取热门推荐
    public  function get_hot_command(){
        $content= new ContentModel();
        $content=$content->query('select title , time  from tp_content where hot=1');
        return json_encode(
            [
                'code'=>'0000',
                'msg'=>$content,
            ]
        );
    }
//    获取置顶推荐
    public function get_settop_commmend(){
        $content=new ContentModel();
        $content=$content->query('select pic,title from tp_content where settop=1');
        return json_encode(
            [
                'code'=>'0000',
                'msg'=>$content,
            ]
        );
    }
//    获取我的文章内容
    public function get_my_article(){
        $content=new ContentModel();
        $content=$content->query('select title,time from tp_content where uid="'.Session::get('userid').'"');
        return json_encode(
            [
                'code'=>'0000',
                'msg'=>$content
            ]
        );
    }
}