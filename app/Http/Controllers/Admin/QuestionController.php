<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Code;
use Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller {

	public function __constructor(){
		return;
	}

	/**
	 * list of Questions
	 */

	public function getList()
	{
		if (Request::has('page') && Request::has('number')) {
			$page = intval(Request::get('page'));
			$number = intval(Request::get('number'));
			$index = ($page - 1) * $number;
            $gets = Request::all();
            $orderableKeys = ['id', 'prize', 'isanswered', 'time', 'question_user_id', 'question_user_id', 'like', 'like_virtual', 'listen', 'listen_virtual'];
            $orderableValues = ['asc', 'desc'];
            $orderKey = Request::get('field');
            $orderValue = Request::get('order');

            //搜索功能 排序功能之后加
			$query = Question::with('answer')
                ->with('teacher')
                ->with('user')
                ->skip($index)
                ->take($number);
            if($orderKey && $orderValue && in_array($orderKey, $orderableKeys) && in_array($orderValue, $orderableValues)){
                $query -> orderBy($orderKey, $orderValue);
            }
//            DB::enableQueryLog();
            $questions = $query->get();
//            print_r(DB::getQueryLog());

			$datas = array();
			foreach ($questions as $key => $question) {
				$data = array(
					'question_id'           =>  $question->id,
					'question_content'      =>  $question->content,
					'question_prize'        =>  $question->prize,
                    'question_time'         =>  $question->time,
					'teacher_id'            =>  $question->teacher->id,
					'teacher_name'          =>  $question->teacher->wechat,
					'teacher_face'          =>  $question->teacher->face,
                    'user_id'               =>  $question->user->id,
                    'user_name'             =>  $question->user->wechat,
                    'user_face'             =>  $question->user->face,
                    'is_answered'           =>  $question->isanswered,
				);
                if($question->isanswered === 1){
                    $dataAnswer = array(
                        'answer_listen'         =>  $question->answer->listen,
                        'answer_listen_virtual' =>  $question->answer->listen_virtual,
                        'answer_like'           =>  $question->answer->like,
                        'answer_like_virtual'   =>  $question->answer->like_virtual,
                        'answer_audio'          =>  $question->answer->audio,
                        'weight'                =>  $question->weight
                    );
                    $data = array_merge($dataAnswer, $data);
                }
				$datas[] = $data;
			}
			return Code::response(0, $datas);
		} else {
			return Code::response(100);
		}
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
