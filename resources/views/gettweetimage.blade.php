<!DOCTYPE html>
<html>
<head>
	<title>Get Image by Tweet ID</title>
</head>
<body>
推特網址或 ID：<input type="text" id="tweet_url" size="100" />&nbsp;<input type="button" id="btn" value="送出" />
<br/>
<br/>
<div> 
	擷取結果：<span id="result"></span><br/><br/>
	擷取網址：<span id="url"></span><br/><br/>
	縮圖預覽：<span id="img"></span>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		$("#btn").click(function() {
			var tweet_url = $("#tweet_url").val();
			$.ajax({
				type: "POST",
				url: "{{ route('twitter.GetImageinTweet') }}",
				data: { tweet_url: tweet_url, _token: '{{ csrf_token() }}' }
			}).done(function(data) {
				var myObj = JSON.parse(data);
				//console.log(myObj.messege);
				if (myObj.result == 1 ) {
					$("#result").text("成功");
					$("#url").html("<br>");
					$("#img").html("<br>");
					for (var key in myObj.image_url) {					    
					    $("#url").append(myObj.image_url[key]+"<br>");
					    $("#img").append("<img src=\""+myObj.image_url[key]+":small\" />&nbsp;");
					}
				} else {
					$("#result").text("失敗 - "+myObj.messege);
					$("#url").html("");
					$("#img").html("");
				}
			});
		});
	</script>
</body>
</html>