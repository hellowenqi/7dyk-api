<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Answer;
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
        DB::enableQueryLog();
		if (Request::has('page') && Request::has('number') && Request::has('is_answered')) {
            $orderableQuestionKeys = array(
                'question_id'           => 'id',
                'question_prize'        => 'prize',
                'question_time'         => 'time',
                'user_id'               => 'question_user_id',
                'teacher_id'            => 'answer_user_id',
            );
            $orderableAnswerKeys = array(
                'answer_like'           => 'like',
                'answer_like_virtual'   => 'like_virtual',
                'answer_listen'         => 'listen',
                'answer_listen_virtual' => 'listen_virtual',
				'answer_order'			=> 'order'
            );
            $orderableValues = ['asc', 'desc'];
            $total = 0;
            $data = [];
            $datas = [];
            $page = intval(Request::get('page'));
            $number = intval(Request::get('number'));
            $search = Request::get('search');
            $index = ($page - 1) * $number;
            $orderKey = Request::get('field');
            $orderValue = Request::get('order');
            //回答过的
            if(Request::get('is_answered') === 'true'){
                if ($orderKey && $orderValue && in_array($orderKey, array_keys($orderableAnswerKeys)) && $search == ''){
                //以answer 排序
                    $query = Answer::with('question')->with('user')->with('teacher');
                    $total = $query->count();
					if(!in_array($orderValue, $orderableValues)){
						return Code::response(100, '排序值请输入desc或者asc');
					}
                    $query->orderBy($orderableAnswerKeys[$orderKey], $orderValue);
                    $questions = $query->skip($index)->take($number)->get();
                    foreach ($questions as $key => $answer) {
                        $data[] = array(
                            'question_id'           =>  $answer->question->id,
                            'question_content'      =>  $answer->question->content,
                            'question_prize'        =>  $answer->question->prize,
                            'question_time'         =>  $answer->question->time,
                            'teacher_id'            =>  $answer->teacher->id,
                            'teacher_name'          =>  $answer->teacher->wechat,
                            'teacher_face'          =>  $answer->teacher->face,
                            'user_id'               =>  $answer->user->id,
                            'user_name'             =>  $answer->user->wechat,
                            'user_face'             =>  $answer->user->face,
                            'answer_listen'         =>  $answer->listen,
                            'answer_listen_virtual' =>  $answer->listen_virtual,
                            'answer_like'           =>  $answer->like_virtual == 0  ? $answer->like : $answer->like_virtual,
                            'answer_like_virtual'   =>  $answer->like_virtual,
                            'answer_audio'          =>  $answer->audio,
							'answer_order'			=>  $answer->order,
                            'weight'                =>  $answer->weight
                        );
                    }
                    $datas['page'] = $page;
                    $datas['number'] = $number;
                    $datas['total'] = $total;
                    $datas['datas'] = $data;
                }else{
                    //以question 内容排序和搜索
                    $query = Question::where('isanswered', 1)
                        ->with('answer')
                        ->with('user')
                        ->with('teacher');
                    if($search){
                        $query->where('content', 'like', "%$search%");
//                        $query->orWhereHas('user', function($query) use ($search) {
//                            $query->where('wechat', 'like', "%$search%");
//                        });
//                            $query->orWhereHas('teacher', function($query) use ($search) {
//                                $query->where('wechat', 'like', "%$search%");
//                            });
                    }
                    $total = $query->count();
                    if($orderKey && $orderValue && in_array($orderKey, array_keys($orderableQuestionKeys)) && in_array($orderValue, $orderableValues))
                    $query->orderBy($orderableQuestionKeys[$orderKey], $orderValue);
                    $questions = $query->skip($index)->take($number)->get();
                    foreach ($questions as $key => $question) {
                        $data[] = array(
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
                            'answer_listen'         =>  $question->answer->listen,
                            'answer_listen_virtual' =>  $question->answer->listen_virtual,
                            'answer_like'           =>  $question->answer->like,
                            'answer_like_virtual'   =>  $question->answer->like_virtual,
                            'answer_audio'          =>  $question->answer->audio,
							'answer_order'			=>  $question->answer->order,
                            'weight'                =>  $question->answer->weight
                        );
                    }
                    $datas['page'] = $page;
                    $datas['number'] = $number;
                    $datas['total'] = $total;
                    $datas['datas'] = $data;
                }
            }else{
            //未回答过的
				$query = Question::where('isanswered', 0)->with('user');
				if($search){
					$query->where('content', 'like', "%$search%");
				}
				$total = $query->count();
				array_pop($orderableQuestionKeys);
				if($orderKey && $orderValue && in_array($orderKey, array_keys($orderableQuestionKeys)) && in_array($orderValue, $orderableValues))
					$query->orderBy($orderableQuestionKeys[$orderKey], $orderValue);
				$questions = $query->skip($index)->take($number)->get();
				$data = array();
				foreach ($questions as $key => $question) {
					$data[] = array(
						'question_id'           =>  $question->id,
						'question_content'      =>  $question->content,
						'question_prize'        =>  $question->prize,
						'question_time'         =>  $question->time,
						'user_id'               =>  $question->user->id,
						'user_name'             =>  $question->user->wechat,
						'user_face'             =>  $question->user->face
					);
				}
				$datas['page'] = $page;
				$datas['number'] = $number;
				$datas['total'] = $total;
				$datas['datas'] = $data;
            }
            return Code::response(0, $datas);
		} else {
			return Code::response(100);
		}
	}

	public function questionModify(){
		if(!Request::has('question_id')) return Code::response(100);
		$question_id = Request::get('question_id');
		//修改顺序
		if(Request::has('order')){
			$order = intval(Request::get('order'));
			if($order === 0){
				return Code::response(100);
			}
			if(!Answer::where('question_id', $question_id)->first()){
				return Code::response(201);
			}
			$this->setOrder($question_id, $order);
		}
		$model = Answer::where('question_id', $question_id)->first();
		if(Request::has('answer_listen_virtual') && intval(Request::get('answer_listen_virtual'))){
			$model->listen_virtual = intval(Request::get('answer_listen_virtual'));
		}

		if(Request::has('answer_like_virtual') && intval(Request::get('answer_like_virtual'))){
			$model->like_virtual = intval(Request::get('answer_like_virtual'));
		}
		$model->save();
		return Code::response(0);
	}

	/*set order of a question*/
	public function setQuestionOrder(){
		if(Request::has('question_id') && Request::has('order')){
			$id = Request::get('question_id');
			$order = intval(Request::get('order'));
			if(!Answer::where('question_id', $id)->first()){
				return Code::response(201);
			}
			if($order === 0){
				return Code::response(100);
			}
			if($this->setOrder($id, $order)){
				return Code::response(0);
			}else{
				return Code::response(404);
			};
		}else{
			return Code::response(100);
		}
	}


	/**
	 * 设置虚拟的偷听和点赞数
	 */

	public function setVirtualValue(){
		if(!Request::has('question_id')) return Code::response(100);
		$question_id = Request::get('question_id');
		$model = Answer::where('question_id', $question_id)->first();
		if(!$model) return Code::response(201);
		if(Request::has('answer_listen_virtual') && intval(Request::get('answer_listen_virtual'))){
			$model->listen_virtual = intval(Request::get('answer_listen_virtual'));
		}

		if(Request::has('answer_like_virtual') && intval(Request::get('answer_like_virtual'))){
			$model->like_virtual = intval(Request::get('answer_like_virtual'));
		}
		if($model->save()){
			return Code::response(0);
		}else{
			return Code::response(404);
		}
	}



	/**递归的设置问题顺序
	 * @param $id
	 * @param $order
	 * @return bool
	 */
	private function setOrder($id, $order){
		$answer = Answer::where('order', $order)->where('question_id', '!=', $id)->first();
		if($answer){
			$this->setOrder($answer->question_id, $order + 1);
		}
		$model = Answer::where('question_id', $id)->first();
		$model->order = $order;
		$model->save();
		return true;
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
