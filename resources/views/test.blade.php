<!DOCTYPE html>
<html>
<head>
	<title>test upload</title>
</head>
<body>
<form action="/uploadImage" method="post" enctype="multipart/form-data">
	{{ csrf_field() }}
    <input type="file" name="picture" />
    <input type="Submit">
</form>
</body>
</html>