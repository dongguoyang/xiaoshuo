<style>
	.line-center{text-align: center;}
</style>
<div class="container">
	<div class="row line-center">&nbsp;</div>
	<div class="row line-center">
		@foreach($admins as $v)
		<div class="col-xs-3">
			<a class="btn btn-success" href="{{$v['value']}}" target="_blank">{{$v['title']}}</a>
		</div>
		@endforeach
	</div>
</div>