<?php namespace App\Http\Controllers\Admin;



use App\Http\Requests;
use App\Http\Controllers\Controller;
//use App\Models\Answer;
use App\Models\User;
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
                'listener_number'       =>'listennum',
                'question_prize'        =>'prize',
                'answer_number'         =>'answernum',
                'answer_income'         =>'income',
                'answer_number_virtual' =>'answernum_virtual',
                'listen_number_virtual' =>'listennum_virtual',
                'question_prize_virtual'=>'prize_virtual',
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

            $query = Teacher::with('user');

           if($search) {
               $query =$query->wherehas('user',function($query) use($search){
                    $query->where('wechat', 'like', "%$search%");
                });

           }

            $total = $query->count();

            if ($orderKey && $orderValue && in_array($orderKey, array_keys($orderableTeacherKeys)) && in_array($orderValue, $orderableValues)){

                $query->orderBy($orderableTeacherKeys[$orderKey], $orderValue);

            }

            $teachers = $query->skip($index)->take($number)->get();


            foreach ($teachers as $key => $teacher) {

                $data[] = array(
                    'listener_number'       =>  $teacher->listennum,
                    'question_prize'        =>  $teacher->prize,
                    'answer_number'         =>  $teacher->answernum,
                    'answer_income'         =>  $teacher->income,
                    'teacher_id'            =>  $teacher->id,
                    'answer_number_virtual' =>  $teacher->answernum_virtual,
                    'listen_number_virtual' =>  $teacher->listennum_virtual,
                    'question_prize_virtual'=>  $teacher->prize_virtual,
                    'teacher_wechat'        =>  $teacher->user->wechat,
                    'teacher_company'       =>  $teacher->user->company,

                );
            }

            $datas['page'] = $page;
            $datas['number'] = $number;
            $datas['total'] = $total;
            $datas['datas'] = $data;
            return Code::response(0, $datas);

        } else {
            return Code::response(100);
        }
    }


    public function setVirtualValue(){
        if(!Request::has('teacher_id')) return Code::response(100);
        $teacher_id = Request::get('teacher_id');
        $model = Teacher::where('id', $teacher_id)->first();
        if(!$model) return Code::response(102);
        if(Request::has('teacher_listen_virtual') && intval(Request::get('teacher_listen_virtual'))){
            $model->listen_virtual = intval(Request::get('teacher_listen_virtual'));
        }

        if(Request::has('teacher_like_virtual') && intval(Request::get('teacher_like_virtual'))){
            $model->like_virtual = intval(Request::get('teacher_like_virtual'));
        }
        if(Request::has('teacher_answernum_virtual') && intval(Request::get('teacher_answernum_virtual'))){
            $model->answernum_virtual = intval(Request::get('teacher_answernum_virtual'));
        }
        if($model->save()){
            return Code::response(0);
        }else{
            return Code::response(404);
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
    public function test(){
        $id = 33;
        
    }

}
