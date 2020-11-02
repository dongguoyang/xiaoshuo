<style>
	.line-center{text-align: center;}
	.m-top30{margin-top: 30px;}
</style>
<div class="container">
	<div class="row line-center">&nbsp;</div>
	<div class="row">
		<div class="">输入要解析的信息：</div>
		<textarea id="input" style="width: 100%;padding: 5px 10px;" rows="7" placeholder="输入要解析的信息"></textarea>
		<div class="m-top30">
			<div class="col-sm-5">
				<select class="form-control" id="func">
					<option value="">请选择操作</option>
					<option value="encrypt">encrypt</option>
					<option value="dncrypt">dncrypt</option>
					<option value="bcrypt">bcrypt</option>
				</select>
			</div>
			<span class="btn btn-info" id="testOut">解析</span>
		</div>
		<div class="m-top30">解析结果信息：</div>
		<textarea id="output" style="width: 100%;padding: 5px 10px;" rows="7" placeholder="解析的结果信息"></textarea>
	</div>
</div>

<script>
	$("#testOut").on('click', function () {
		var input = $("#input").val();
		var func = $("#func").val();
		if (!input || !func) {
		    alert('参数错误！');
		    return false;
		}

		$("#output").val('');
		$.post('testout', {input: input, func: func}, function (rel, succ) {
			if (succ == 'success' && rel.err_code == 0) {
			    $("#output").val(rel.data);
			} else {
			    alert(rel.err_msg);
			}
        })
    })
</script>