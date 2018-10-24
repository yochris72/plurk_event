<!DOCTYPE html>
<html>
<head>
	<title>index</title>
</head>
<body>
@if ( $plurk_login )
{!! $plurk_data['display_name'] . "<br/><br/>" . nl2br($plurk_data['about'], false) !!}
<br/><br/>
<a href="{{ route('logout_plurk') }}">登出 Plurk</a>
@else
<a href="{{ route('login_plurk') }}">登入 Plurk</a>
@endif
<br/><br/><br/>
@if ( $twitter_login )
<br/><br/>
{!! $twitter_data['name'] . "<br/><br/>" . $twitter_data['description'] !!}
<br/><br/>
<a href="{{ route('logout_twitter') }}">登出 Twitter</a>
@else
<a href="{{ route('login_twitter') }}">登入 Twitter</a>
@endif
</body>
</html>