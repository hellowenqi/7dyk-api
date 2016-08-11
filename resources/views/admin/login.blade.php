<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
</head>
<body>
<div>
    <h1>测试登陆</h1>
    <div>
        @if (session('msg'))
            <p style="color:red">{{session('msg')}}</p>
        @endif
        <form action="" method="post">
            <ul>
                <li><span>用户名</span>
                    <input type="text" name="username"/>
                </li>
                <li><span>密&nbsp;码</span>
                    <input type="password" name="password"/>
                </li>
                <li><span>验证码</span>
                    <input type="text" class="code" name="code"/>
                    <img src="{{url('admin/v1/login/code')}}" alt="" onclick="this.src='{{url('admin/v1/login/code')}}?'+Math.random()">
                </li>
                <li>
                    <input type="submit" value="登陆"/>
                </li>
            </ul>
        </form>
    </div>
</div>
</body>
</html>