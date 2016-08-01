<?php namespace App\Http\Controllers\Admin;



use App\Http\Requests;
use App\Http\Controllers\Controller;
//use App\Models\Answer;
use App\Models\Question;
use App\Models\Teacher;
use App\Code;
use Request;
use Illuminate\Support\Facades\DB;



class TeacherController extends Controller {

    public function getList()
    {
        DB::enableQueryLog();
        if (Request::has('page') && Request::has('number')) {

            $orderableTeacherKeys=array(
                'listener_number'     =>'listennum',
                'question_prize'      =>'prize',
                'answer_number'       =>'answernum',
                'answer_income'       =>'income'
            );
            $orderableValues = ['asc', 'desc'];
            $data = [];
            $datas = [];
            $page = intval(Request::get('page'));
            $number = intval(Request::get('number'));
            $search = Request::get('search');
            $index = ($page - 1) * $number;

            $orderKey = Request::get('field');
            $orderValue = Request::get('order');

            if ($orderKey && $orderValue && in_array($orderKey, array_keys($orderableTeacherKeys)) && $search == ''){
                //为teacher 排序
                $query = Teacher::with('user');
                $total = $query->count();

                if(!in_array($orderValue, $orderableValues)){
                    return Code::response(100, '排序值请输入desc或者asc');
                }

                $query->orderBy($orderableTeacherKeys[$orderKey], $orderValue);

                $teachers = $query->skip($index)->take($number)->get();

                foreach ($teachers as $key => $teacher) {
                    $data[] = array(
                        'listener_number'           =>  $teacher->listennum,
                        'question_prize'            =>  $teacher->prize,
                        'answer_number'             =>  $teacher->answernum,
                        'answer_income'             =>  $teacher->income,
                        'user_id'                   =>  $teacher->user->id,
                    );
                }

                $datas['page'] = $page;
                $datas['number'] = $number;
                $datas['total'] = $total;
                $datas['datas'] = $data;
            }else{
                //teacher搜索
                $query = Teacher::with('user');
                $searchField=Request::get('searchfield');
                if($search){
                    $query->Where($searchField, '<', "$search");//搜索方式，，，
                }


                $total = $query->count();


                if ($orderKey && $orderValue && in_array($orderKey, array_keys($orderableTeacherKeys)) && in_array($orderValue, $orderableValues))
                $query->orderBy($orderableTeacherKeys[$orderKey], $orderValue);

                $teachers = $query->skip($index)->take($number)->get();

                foreach ($teachers as $key => $teacher) {
                    $data[] = array(
                        'listener_number'           =>  $teacher->listennum,
                        'question_prize'            =>  $teacher->prize,
                        'answer_number'             =>  $teacher->answernum,
                        'answer_income'             =>  $teacher->income,
                        'user_id'                   =>  $teacher->user->id,
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
