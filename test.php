<?php
$arr = array('a','b','c');
$arr2 = array('d', 'e', 'f');

foreach($arr as &$value){//习惯用$value或$val
    $value .= '4';
}

//都处理完毕我们在页面模版输出,首先输出$arr2
//foreach($arr2 as $value){//习惯用$value或$val
//    //echo $value;
//}
unset($value);
foreach($arr as $value){//习惯用$value或$val
    echo $value, "\n";
}
?>