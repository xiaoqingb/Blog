<?php

namespace app\index\controller;

use app\index\model\Member as MemberModel;
use think\Session;
use think\Cookie;
use think\Validate;

class Member extends Auth
{
    protected $beforeActionList = [
        'isAuthed' => ['only' => 'getName']
    ];

    public function getName()
    {
        $this->isAuthed();
        return json_encode(
            [
                'code' => '0000',
                'msg' => Session::get('username'),
            ]
        );
    }

    public function get_Member_list()
    {
        $content = new MemberModel();
        $userName = $content->orderRaw('userid desc')->limit(8)->column('username');
        $userHead = $content->orderRaw('userid desc')->limit(8)->column('userhead');
        return json_encode(
            [
                'code' => "0000",
                'msg' => [$userName, $userHead],
            ]
        );
    }

    public function get_member_msg()
    {
        $content = new MemberModel();
        $userName = input("get.memberName");
        $userHead = $content->where("username", $userName)->column('userhead');
        $userTime = $content->where("username", $userName)->column('usertime');
        $reply = $content->where("username", $userName)->column('reply');
        $userHome = $content->where("username", $userName)->column('userhome');
        $userSex = $content->where("username", $userName)->column('sex');
        $userDescription = $content->where("username", $userName)->column('description');

        return json_encode(
            [
                'code' => "0000",
                'msg' => [$userName, $userHead, $userTime, $userSex, $reply, $userHome, $userDescription],
            ]
        );
    }

    public function regist()
    {
        $email = input('post.email');
        $password = input('post.password');
        $nickName = input('post.nickName');
        $sex = input('post.sex');
        if (!$email || !$password) {
            return json_encode(
                [
                    'code' => '0002',
                    'msg' => '请求信息有误'
                ]
            );
        }
        $member = new MemberModel();
        //判断用户是否存在
        $result = $member->where('usermail', $email)->find();
        if (!$result === null) {
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => '该用户已存在'
                ]
            );
        }
        $member->usermail = $email;
        $member->password = md5($password);
        $member->username = $nickName;
        $member->sex = $sex;
        $member->userhead = '/blog/public/static/uploads/20170401/default.png';
        $member->save();
        return json_encode(
            [
                'code' => '0000',
                'msg' => '注册成功'
            ]
        );
    }

    public function login()
    {
        $email = input('post.email', '');
        $password = input('post.password', '');

//            step 1.验证用户的输入
        $rule = [
            'password' => 'require',
            'email' => 'require|email',
        ];
        $msg = [
            'password.require' => '你没有输入密码',
            'email.require' => '你忘记输入邮箱了',
            'email.email' => '你的邮箱格式有误',
        ];
        $data = [
            'password' => $password,
            'email' => $email,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => $validate->getError()
                ]
            );
        }
//            step 2.判断账号存不存在
        $member = new memberModel();
        $result = $member->where('usermail', $email)->find();
        if (!$result) {
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => $validate->getError()
                ]
            );
        }
//            step 3.验证账号密码正不正确
        $member = $member->where('usermail', $email)->where('password', md5($password))->find();
        if (!$member) {
            //密码错误
            return json_encode(
                [
                    'code' => '0002',
                    'msg' => '账号或密码错误!'
                ]
            );
        }
        $username = $member->query('select username from tp_member where usermail="' . $email . '" limit 1');
        $username = $username[0]['username'];
        $userid = $member->query('select userid from tp_member where usermail="' . $email . '" limit 1');
        $userid = $userid[0]['userid'];
//            step 4.成功登录后将数据写入 session 和cookie
        Session::set('username', $username);
        Session::set('userid', $userid);
        Cookie::set('user', $userid . '::' . $username, 7 * 24 * 60 * 60);
        return json_encode(
            [
                'code' => '0000',
                'msg' => $username
            ]
        );
    }

    public function logout()
    {
        Session::clear();
        Cookie::delete('user');
        return json_encode(
            [
                'code' => '0000',
                'msg' => '登出成功!'
            ]
        );
    }

    public function get_basic_msg()
    {
        $member = new MemberModel();
        $msg = $member->query('select username,usermail,userqq,userhome,description,userhead from tp_member where userid="' . Session::get("userid") . '"');
        return json_encode(
            [
                'code' => '0000',
                'msg' => $msg
            ]
        );
    }

    public function change_basic_msg()
    {
        $member = new MemberModel();
        $nickName = input('post.nickName');
        $email = input('post.email');
        $rule = [
            'email' => 'require|email',
        ];
        $msg = [
            'email.email' => '你的邮箱格式有误',
        ];
        $data = [
            'email' => $email,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => $validate->getError()
                ]
            );
        }
        $userQq = input('post.userQq');
        $userHome = input('post.$userHome');
        $description = input('post.description');
        if ($nickName) {
            $result=$member->table('tp_member')->where('userid', Session::get('userid'))->update(['username' => $nickName]);
        }
        if ($email) {
            $result=$member->table('tp_member')->where('userid', Session::get('userid'))->update(['usermail' => $email]);
        }
        if ($userQq) {
            $result=$member->table('tp_member')->where('userid', Session::get('userid'))->update(['userqq' => $userQq]);
        }
        if ($userHome) {
            $result=$member->table('tp_member')->where('userid', Session::get('userid'))->update(['userhome' => $userHome]);
        }
        if ($description) {
            $result=$member->table('tp_member')->where('userid', Session::get('userid'))->update(['description' => $description]);
        }
        return json_encode(
            [
                'code'=>'0000',
                'msg'=>'修改成功'
            ]
        );
    }
    public function change_password(){
        $member = new MemberModel();
        $newPassword = input('post.newPassword');
        $oldPassword = input('post.oldPassword');
        $result=$member->table('tp_member')->where('userid', Session::get('userid'))->where('password',$oldPassword)->select();
        if ($result===[]){
            return json_encode(
                [
                    'code' => '0000',
                    'msg' => '旧密码有误'
                ]
            );
        }
        $result=$member->table('tp_member')->where('userid', Session::get('userid'))->update(['password' => $newPassword]);
        return json_encode(
            [
                'code' => '0000',
                'msg' => '修改成功'
            ]
        );
    }
}