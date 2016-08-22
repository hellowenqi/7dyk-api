<?php namespace App\Http\Controllers\Admin;

use App\Code;
use App\Http\Controllers\Controller;

use App\Models\Hot;
use Request;
use Session;
use Illuminate\Support\Facades\DB;


class HotController extends Controller {

    /**
     * 查看搜索历史记录
     *
     * @return Response
     */
    public function index()
    {
        return Code::response(0, Hot::all());
    }

    /**
     * 增加
     * @return string
     */
    public function add(){
        $search = Request::get('search');
        $category = Request::get('category');
        if($category && $search){
            $model = new Hot();
            $model->category = $category;
            $model->search = $search;
            $model->save();
            return Code::response(0);
        }else{
            return Code::response(100);
        }

    }

    /**
     * 修改
     */

    public function update(){
        $id = intval(Request::get('id'));
        $category = intval(Request::get('category'));
        $search = Request::get('search');
        $model = Hot::find($id);
        DB::enableQueryLog();
        if($model){
            if($category) $model->category = $category;
            if($search) $model->search = $search;
            $model->save();
            return Code::response(0);
        }else{
            return Code::response(404, 'id错误');
        }
        var_dump(DB::getQueryLog());
    }
    /**
     * 删除
     */

    public function destroy(){
        $id = intval(Request::get('id'));
        $model = Hot::find($id);
        DB::enableQueryLog();
        if($model){
            $model->delete();
            return Code::response(0);
        }else{
            return Code::response(404, 'id错误');
        }
        var_dump(DB::getQueryLog());
    }

}
