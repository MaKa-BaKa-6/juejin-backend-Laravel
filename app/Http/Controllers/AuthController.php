<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function respondWithToken($user, $remember, $msg)
    {
        $token = "access_token_" . $user->id . "_" . time();
        $refreshToken = "refresh_token_" . $user->id;
        $minutes = $remember ? 60 * 24 * 7 : 0;
        $cookie = cookie("refresh_token", $refreshToken, $minutes, "/", null, false, true);
        // $user["access_token"]=$token;
        return $this->success([
            "access_token" => $token,
            "user" => $user
        ], $msg)->withCookie($cookie);
    }

    public function login(Request $request)
    {
        $user = User::where("phone", $request->phone)->first();
        if (!$user || !Hash::check($request->password, $user->password)) return $this->error("账号或密码错误", 401);
        return $this->respondWithToken($user, $request->input("remember", false), "登录成功");
    }

    public function sendEmailCode(Request $request)
    {
        $request->validate(["email" => "required|email"]);
        $code = rand(100000, 999999);
        try {
            Mail::raw("您的登录验证码是：{$code}，有效期5分钟，请勿泄露给他人", function ($msg) use ($request) {
                $msg->to($request->email)->subject("登录验证码");
            });
            Cache::put("email_code_" . $request->email, $code, now()->addMinutes(5));
            return $this->success(null, "验证码已发送至{$request->email}");
        } catch (\Exception $e) {
            return $this->error("邮件发送失败");
        }
    }

    public function emailLogin(Request $request)
    {
        $request->validate(["email" => "required|email", "code" => "required"]);
        $cachedCode = Cache::get("email_code_" . $request->email);
        if ($request->code != $cachedCode) return $this->error("验证码错误或已过期", 401);
        Cache::forget("email_code_" . $request->email);
        $user = User::where("email", $request->email)->first();
        if (!$user) return $this->error("该用户还未注册");
        return $this->respondWithToken($user, $request->input("remember", false), "登录成功");
    }

    public function logout()
    {
        $cookie = cookie()->forget("refresh_token");
        return $this->success(null, "注销成功")->withCookie($cookie);
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                "username" => "required",
                "phone"    => "required|unique:users",
                "password" => "required",
            ], [
                'username.required' => '用户名不能为空',
                'phone.required'    => '手机号不能为空',
                'phone.unique'      => '该手机号已被注册',
                'password.required' => '密码不能为空',
            ]);
            User::create([
                "username" => $request->username,
                "phone"    => $request->phone,
                "password" => $request->password,
            ]);

            return $this->success(null, "注册成功");
        } catch (ValidationException $e) {
            return $this->error($e->validator->errors()->first());
        }
    }
}
