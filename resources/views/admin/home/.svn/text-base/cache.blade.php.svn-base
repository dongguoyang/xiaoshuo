<span id="zmr_delall" style="position:absolute;top:-36px;left:170px;color:#777;font-weight:300;font-size:15px;cursor:pointer;">清空所有缓存</span>
{{--<div class="container">--}}
<div class="row" style="margin-top: 50px;">
	<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
		<div style="margin-bottom: 15px;">
			&nbsp;&nbsp;&nbsp;
			缓存键Tag：<input type="text" id="zmr_key_tags" style="width:250px;height:35px;margin-right:10px;" placeholder="多个tag以 .. 进行分隔|user_stimg:收徒图片" />
			<button class="btn btn-danger" id="zmr_btntagsdel">删除</button>
		</div>
		<div style="margin-bottom: 15px;">
			&nbsp;&nbsp;&nbsp;&nbsp;
			Redis队列：<input type="text" id="zmr_key_list" style="width:250px;height:35px;margin-right:10px;" placeholder="duty_queue_list_ 任务队列" />
			<button class="btn btn-primary" id="zmr_getlist">查看</button>
			<button class="btn btn-danger" id="zmr_btnlistdel">删除</button>
		</div>
		<div style="margin-bottom: 15px;">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			缓存键：<input type="text" id="zmr_key" style="width:250px;height:35px;margin-right:10px;" placeholder="多数key以 DutySYS 开头" />
			<button class="btn btn-primary" id="zmr_getinfo">查看</button>
			<button class="btn btn-danger" id="zmr_btndel">删除</button>
		</div>
		<div style="margin-bottom: 15px;">
			Redis缓存键：<input type="text" id="zmr_key1" style="width:250px;height:35px;margin-right:10px;" />
			<button class="btn btn-primary" id="zmr_getinfo1">查看</button>
			<button class="btn btn-danger" id="zmr_btndel1">删除</button>
		</div>
	</div>


	<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
		<div style="margin-bottom: 15px;">
			缓存键Tag：user_stimg&nbsp;&nbsp;&nbsp;user_duty_lists..user_id
		</div>
		<div style="margin-bottom: 15px;">
			Redis队列：duty_queue_list_&nbsp;&nbsp;&nbsp;
		</div>
		<div style="margin-bottom: 15px;">
			缓存键：NovelSYSnovel_{nid}_section_{section_num}_info<br>NovelSYSnovel_section_list_{nid}_page_{page}_order_{order asc/desc}<br>NovelSYSnovel_section_first2last_{nid}<br>NovelSYSnovel_info_{nid}<br>NovelSYSuser_info_{uid}<br>NovelSYSsub_user_info_{first_account}_{cid}<br>NovelSYSread_log_ids_{uid}_{nid}<br>NovelSYSread_log_{uid}_{page}<br>
		</div>
		<div style="margin-bottom: 15px;">
			Redis缓存键：get_cash_lock_key_&nbsp;&nbsp;&nbsp;
		</div>
	</div>
</div>

<div style="width:100%;margin-top:20px;margin-left:100px;" id="zmr_info">

</div>

<script>
	// 清空所有缓存
	$("#zmr_btndel").click(function(){
		var key = $("#zmr_key").val();
		comfirmDel('/{{config('admin.route.prefix')}}/cache/delCacheInfo', key);
	});
	// 清空收徒二维码缓存
	$("#zmr_btntagsdel").click(function(){
		var key = $("#zmr_key_tags").val();
		comfirmDel('/{{config('admin.route.prefix')}}/cache/delTagsCache', key);
	});
	// 清空收徒二维码缓存
	$("#zmr_btnlistdel").click(function(){
		var key = $("#zmr_key_list").val();
		comfirmDel('/{{config('admin.route.prefix')}}/cache/delListCache', key);
	});

	$("#zmr_getinfo").click(function(){
		var key = $("#zmr_key").val();
		getRedisInfo("/{{config('admin.route.prefix')}}/cache/getCacheInfo", "#zmr_getinfo", key);
	});
	$("#zmr_getlist").click(function(){
		var key = $("#zmr_key_list").val();
		getRedisInfo("/{{config('admin.route.prefix')}}/cache/getListInfo", "#zmr_getlist", key);
	});

	$("#zmr_btndel1").click(function(){
		var key = $("#zmr_key1").val();
		comfirmDel('/{{config('admin.route.prefix')}}/cache/deleteRedisCache', key);
	});

	$("#zmr_getinfo1").click(function(){
		var key = $("#zmr_key1").val();
		getRedisInfo("/{{config('admin.route.prefix')}}/cache/getRedisInfo", "#zmr_getinfo1", key)
	});
	
	$("#zmr_delall").click(function(){
		comfirmDel('/{{config('admin.route.prefix')}}/cache/flushAll', 'flush');
	});


	function comfirmDel(url,  key, method='DELETE'){
		var title = "您正在执行清空整个 REDIS <br/><br/>该操作非常危险,确定要继续吗？";
		if (key != 'flush') {
			title = "您正在执行 Redis 删除<br/><br/>该操作会删除缓存 "+ key +",确定要继续吗？";
		} else {
			key = '';
		}
		swal({
			title: title,
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "确认",
			showLoaderOnConfirm: true,
			cancelButtonText: "取消",
			preConfirm: function() {
				return new Promise(function(resolve) {
					$.post(url,{_method:method,  '_token':'{{csrf_token()}}', 'key':key}, function(data){
						if(data.err_code == 0){
							swal(data.err_msg, "", "success");
						}else{
							swal(data.err_msg, "", "error");
						}
					});
				});
			}
		});
	}
	function getRedisInfo(url, btn,  key='', method='PUT'){
		if(key == ''){
			swal("缓存键名不能为空！", "", "error");
			return;
		}
		$(btn).html("查询中...").attr("disabled","disabled");
		$.post(url, {_method:method, '_token':'{{csrf_token()}}', key:key},function(data){
			$(btn).html("查看").removeAttr("disabled");

			if(data.err_code == 0){
				console.log(data.data)
				var rel = '';
				if (typeof data.data == 'object') {
					for (item in data.data) {
						if (typeof data.data[item] != 'object') {
							rel += item + " : " + data.data[item] + "<br>";
						}
					}
				} else {
					rel = data.data;
				}
				$("#zmr_info").html(rel);
			}else{
				swal(data.err_msg, "", "error");
			}
		},"json");
	}
</script>