<?php namespace App\Http\Controllers;

use App\Code;
use App\Models\History;
use App\Models\Hot;
use Request;
use Session;

class HistoryController extends Controller {

	/**
	 * 查看用户的历史记录
	 *
	 * @return Response
	 */
	public function index()
	{
		$user_id = Session::get('user_id');
//		$user_id = 33;
		$page = intval(Request::get('page'));
		$number = intval(Request::get('number'));
		$index = $number * ($page - 1);
		if($page && $number){
			$datas = array();
			$query = History::where('user_id', $user_id);
			$datas['total'] = count($query);
			$datas['result'] = $query->skip($index)->take($number)->get();
			$datas['page'] = $page;
			$datas['number'] = $number;
			return Code::response(0, $datas);
		}else{
			return Code::response(100);
		}
	}


	/**
	 * 删除单个或者全部删除个人浏览记录
	 */
	public function destroy()
	{
		$query = History::where('user_id', Session::get('user_id'));
//		$query = History::where('user_id', 33);
		if(Request::has('id')){
			$query->where('id', Request::get('id'));
		}
		if($query->delete()){
			return Code::response(0, '删除成功！');
		}else{
			return Code::response(404, '删除失败');
		}

	}


	/**
	 * 查询热门关键词
	 */
	public function hotList(){
		return Code::response(0, Hot::all());
	}

}
