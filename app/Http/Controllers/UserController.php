<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Course;
use App\Models\CourseGroupMember;
use App\Models\Report;
use App\Models\User;
use App\Models\UserBookStore;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    //
    public function index(Request $request)
    {
        $service = new UserServices();
        $result = $service->getAllUsers($request->all());
        return view('admin.user.index', $result);
    }

    public function getOpenid(Request $request)
    {
        $code = $request->input('code');
        $appId = 'wx3a7d272a09f29273';
        $appSecret = 'db427e8cd6f1bebfb408a6f70d92ea37';
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appId . "&secret=" . $appSecret . "&js_code=" . $code . "&grant_type=authorization_code";
        $result = file_get_contents($url);
        return $result;
    }

    public function saveUserInfo(Request $request)
    {
        $openid = $request->input('openid');
        $data = [
            'nickname' => $request->input('nickname'),
            'avatar_url' => $request->input('avatar_url'),
            'openid' => $openid
        ];
        $result = User::where(['openid' => $openid])->update($data);
        return ['success' => $result ? 1 : 0];
    }

    public function saveUserFullInfo(Request $request)
    {
        $openid = $request->input('openid');

        $data = [
            'name' => $request->input('name'),
            'gender' => $request->input('gender'),
            'sign' => $request->input('sign'),
            'mobile' => $request->input('mobile'),
            'child_name' => $request->input('child_name'),
            'child_gender' => $request->input('child_gender'),
            'child_grade' => $request->input('child_grade'),
            'child_class' => $request->input('child_class'),
            'child_school' => $request->input('child_school')
        ];
        $result = User::where('openid', $openid)->update($data);
        return ['success' => $result ? 1 : 0];
    }

    public function getUserInfo(Request $request)
    {
        $openid = $request->input('openid');
        $result = User::where('openid', $openid)->first();
        return ['success' => $result ? 1 : 0, 'content' => $result];
    }


    public function getUserReport(Request $request)
    {
        $openid = $request->input('openid');
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $result = Report::with(['images', 'comments' => function ($query) {
            return $query->orderByDesc('created_at')->skip(0)->take(21)->get();
        }, 'author', 'comments.author'])
            ->withCount(['comments', 'like' => function ($query) {
                return $query->where('status', 1);
            }]);
        $result->where('openid', $openid)->orderByDesc('created_at');

        $result = $result->skip(($page - 1) * $pageSize)->take($pageSize)->get();
        return ['success' => $result ? 1 : 0, 'content' => $result];
    }

    public function getUserPubBook(Request $request)
    {
        $openid = $request->input('openid');
        $result = Book::with(['CoverImages'])->where('openid', $openid)->orderByDesc('created_at')->get();
        return ['success' => $result ? 1 : 0, 'content' => $result];
    }

    public function getUserStoredBook(Request $request)
    {
        $openid = $request->input('openid');
        $result = UserBookStore::with(['book', 'book.cover_image'])->where('openid', $openid)->orderByDesc('created_at')->get();
        return ['success' => $result ? 1 : 0, 'content' => $result];
    }

    public function deleteUserStoredBook(Request $request)
    {
        $id = $request->input('id');
        $result = UserBookStore::find($id)->delete();
        return ['success' => $result ? 1 : 0];
    }

    //用户报名课程列表
    public function userCourseGroupList(Request $request)
    {
        $openid = $request->input('openid');
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);

        $result = CourseGroupMember::with(['courseCharge','group', 'course','group.group_member','group.group_member.user'])->where('openid', $openid)
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->orderByDesc('created_at')
            ->get();

        return ['success' => $result ? 1 : 0, 'content' => $result];
    }

    //用户开设课程列表
    public function userCourseList(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);

        $openid = $request->input('openid');
        $page = $request->input('page', 1);
        $result = Course::where('openid', $openid)
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->orderByDesc('created_at')
            ->get();
        return ['success' => $result ? 1 : 0, 'content' => $result];
    }
}
