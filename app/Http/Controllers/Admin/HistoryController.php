<?php namespace App\Http\Controllers\Admin;

use App\Code;
use App\Http\Controllers\Controller;

use App\Models\History;
use Request;
use Session;

class HistoryController extends Controller {

	/**
	 * 查看搜索历史记录
	 *
	 * @return Response
	 */
	public function index()
	{
		$page = intval(Request::get('page'));
		$number = intval(Request::get('number'));
		$index = $number * ($page - 1);
		$datas = array();
		if($page && $number){
			$query = History::withTrashed();
			$userid = intval(Request::get('user_id'));
			if($userid){
				$query->where('user_id', $userid);
			}
			$datas['total'] = $query->count();
			$datas['histories'] = $query->skip($index)->take($number)->get();
			$datas['page'] = $page;
			$datas['number'] = $number;
			return Code::response(0, $datas);
		}else{
			return Code::response(100);
		}
	}
}
