<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Api\UserRequest;
use Cache;

class UserController extends Controller
{
    public function store(UserRequest $request)
    {
        $verifyData = Cache::get($request->verification_key);

        if(!$verifyData){
            return $this->response->error('验证码已失效',422);
        }

        //hash_equals()可防止时序攻击的字符串比较，两个字符串是从第一位开始逐一进行比较的，发现不同就立即返回 false，那么通过计算返回的速度就知道了大概是哪一位开始不同的，这样就实现了电影中经常出现的按位破解密码的场景。而使用 hash_equals 比较两个字符串，无论字符串是否相等，函数的时间消耗是恒定的，这样可以有效的防止时序攻击。
        if(!hash_equals($verifyData['code'],$request->verification_code)){
            return $this->response->error('验证码错误');
        }

        $user = User::create([
           'name' => $request->name,
           'phone' => $verifyData['phone'],
           'password' => bcrypt($request->password)
        ]);

        //清除验证码缓存
        Cache::forget($request->verification_key);

        //注册成功后，我们暂时通过 DingoApi 提供的 created 方法返回，状态码为 201，之后的课程我们会修改为返回用户数据。
        return $this->response->created();

    }
}
