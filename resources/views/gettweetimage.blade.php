<!DOCTYPE html>
<html>
<head>
	<title>Get Image by Tweet ID</title>
	<style type="text/css">
		.e-inline {
			display: inline-block;
			vertical-align:text-top;
		}

		.preview_picture {
			display: inline-block;
		    max-width: 200px;
		    overflow: hidden;
		    padding: 2px;
		    border: 0;
		    vertical-align: text-top;
		    border: 1px solid #c4c4c4;
		    border-radius: 2px;
		    background: transparent;
		    cursor: pointer;
		    margin: 1px 2px 4px 0;
		    position: relative;		
		}
	</style>
</head>
<body>
推特網址或 ID：<input type="text" id="tweet_url" size="80" />&nbsp;<input type="button" id="btn" value="送出" />
<br/>
<br/>
<div> 
	擷取結果：<span id="result"></span><br/><br/>
	擷取網址：<span id="url"></span><br/><br/>
	縮圖預覽：（點擊可另開視窗放大）<span id="img"></span>
	<br/><br/><br/>
	<div id="todo" style="display:none;">
		你想怎麼做？<br/><br/>
		<input type="button" id="edit" value="放入噗浪框編輯" />&nbsp;
		<input type="button" id="replurk" value="一鍵轉發" />&nbsp;
	</div>
	<br/><br/>
	<div id="plurk_edit" style="display:none;">
		<div id="plurk" class="e-inline">
			<textarea id="plurk_text" rows="6" cols="80" /></textarea><br/>
			<input type="button" id="plurk_preview" value="預覽" />&nbsp;<input type="button" id="replurk" value="發射！" />
		</div>
		<div id="preview" class="e-inline" style="width:468px">
		</div>
	</div>
	</div>


	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		$(document).ready(function (){
			$("#plurk_edit").hide();
			$("#todo").hide();
		});

		$("#edit").click(function() { 
			$("#plurk_text").val($("#url").text()+"\r\n"+$("#tweet_url").val());
			$("#plurk_edit").show();
		});

		$("#plurk_preview").click(function() { 
			$("#preview").html($("#plurk_text").val().replace("\r\n", "<br>"));
			$("#plurk_edit").show();
		});

		$("#btn").click(function() {
			var tweet_url = $("#tweet_url").val();
			$.ajax({
				type: "POST",
				url: "{{ route('twitter.GetImageinTweet') }}",
				data: { tweet_url: tweet_url, _token: '{{ csrf_token() }}' }
			}).done(function(data) {
				var myObj = JSON.parse(data);				
				if (myObj.result == 1 ) {
					$("#result").text("成功");
					$("#url").html("<br>");
					$("#img").html("<br>");
					for (var key in myObj.image_url) {					    
					    $("#url").append(myObj.image_url[key]+" <br>");
					    $("#img").append("<a href=\""+myObj.image_url[key]+"\" target=\"blank\"><img src=\""+myObj.image_url[key]+":thumb\" /></a>&nbsp;");
					}
					$("#todo").show();
				} else {
					$("#result").text("失敗 - "+myObj.messege);
					$("#url").html("");
					$("#img").html("");
					$("#todo").hide();
					$("#plurk_edit").hide();
				}
			});
		});
	</script>
</body>
</html>