<?php namespace App\Models;

//use App\Models\BaseModel;
use Illuminate\Log\Writer;
use Monolog\Logger;

//class Mylog extends BaseModel{
class Mylog{
    //存放每个级别实例
    private static $obj_log = [];

    //日志类型映射
    private static $classify_arr = ['default', 'order_log','error_log'];

    /**
    * 单利初始化以及调取对象
    * @param $classify = 'default' 日志的的频道，对应不同的目录
    * @param $max_num = 0  日志记录的最大数量
    * @return object
    */

    public static function get_log_instance($classify = 'default', $max_num = 0)
    {
        if(empty(self::$obj_log[$classify])) {
        self::$obj_log[$classify] = new Writer(new Logger($classify));
        self::$obj_log[$classify]->useDailyFiles(self::get_path($classify), $max_num);
        }
        return self::$obj_log[$classify];
    }

    /**
    * 映射对应的目录
    * @param $classify 日志的不同的频道
    * @return string
    */
    private static function get_path($classify)
    {
        
        $root_path = public_path();
        $path = $root_path . '/../storage/logs/order/';
        $log_arr = self::$classify_arr;
        if(!empty($log_arr) && !empty($classify)) {
            if(in_array($classify, $log_arr)) {
                return $path . $classify. '/' . $classify . '.log';
            }
        }
        return $path . 'default/default.log';
    }

    /**
    * 映射对应的目录
    * @param $func 调用的方法
    * @param $arguments 参数,包括数据和日志等级
    */
    public static function __callStatic($func, $arguments)
    {
        $get_obj = self::get_log_instance($func);
        if(empty($get_obj)) {
            log::error('Save Log Error!');
        }
        if(empty($arguments) || !is_array($arguments) || !isset($arguments[0])) {
            $get_obj->info('No Data Save!');
        } else if(!isset($arguments[1])) {
            $get_obj->info($arguments[0]);
        } else {
            $get_obj->{$arguments[1]}($arguments[0]);
        }
    }
}