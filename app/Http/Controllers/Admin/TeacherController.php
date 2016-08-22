<?php namespace App\Http\Controllers\Admin;



use App\Http\Requests;
use App\Http\Controllers\Controller;
//use App\Models\Answer;
use App\Models\User;
use App\Models\Teacher;
use App\Code;
use Request;
use Session;
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


    public function teacherModify(){
        if(!Request::has('teacher_id')) return Code::response(100);
        $teacher_id = Request::get('teacher_id');
        $model = Teacher::where('id', $teacher_id)->first();
        if(!$model) return Code::response(102);
        if(Request::has('order')){
            $order = intval(Request::get('order'));
            if($order === 0){
                return Code::response(100);
            }
            $this->setOrder($teacher_id, $order);
        }
        if(Request::has('listen_number_virtual') && intval(Request::get('listen_number_virtual'))){
            $model->listennum_virtual = intval(Request::get('listen_number_virtual'));
        }

        if(Request::has('question_prize_virtual') && intval(Request::get('question_prize_virtual'))){
            $model->prize_virtual = intval(Request::get('question_prize_virtual'));
        }
        if(Request::has('answer_number_virtual') && intval(Request::get('answer_number_virtual'))){
            $model->answernum_virtual = intval(Request::get('answer_number_virtual'));
        }
        if($model->save()){
            return Code::response(0);
        }else{
            return Code::response(404);
        }
    }

    /**递归的设置导师的顺序
     * @param $id
     * @param $order
     * @return bool
     */
    private function setOrder($id, $order){
        $teacher = Teacher::where('order', $order)->where('id', '!=', $id)->first();
        if($teacher){
            $this->setOrder($teacher->id, $order + 1);
        }
        $model = Teacher::where('id', $id)->first();
        $model->order = $order;
        $model->save();
        return true;
    }





}
