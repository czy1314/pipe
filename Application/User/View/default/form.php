{include file=member.header.html}
 <link rel="stylesheet" href="http://apps.bdimg.com/libs/bootstrap/3.3.0/css/bootstrap.min.css">
<style>
	.form-control {
		width: 575px;
	}
</style>
	<div class="content">
    {include file=member.menu.html}
		<div id="right">
			<div style="width: 600px; margin: 100px auto auto; border-radius: 5px; padding: 20px;background:#fff; border: 1px solid rgb(216, 216, 216);z-index:999;position:relative">
    <p style="text-align: center;"></p><h1 style="text-align: center;">范本</h1><p></p>
    <div style="width: 600px;"><img src="./style/exmple.png" width="600"></div>
    <p style="text-align: center;"></p><h1 style="text-align: center;">数据</h1><p></p>
    <form role="form" action="mbx.php<?php echo '?sid='.session_id();?>" method="post">
        <div class="form-group">
            <label for="name">{{first.DATA}}</label>
            <input class="form-control" name="first" id="name" placeholder="" type="text">
        </div>
        <div class="form-group">
            <label for="name">任务名称：</label>
            <input class="form-control" name="keyword1" id="keyword1" placeholder="" type="text">
        </div>
        <div class="form-group">
            <label for="name">通知类型：</label>
            <input class="form-control" name="keyword2" id="keyword2" placeholder="" type="text">
        </div>
        <div class="form-group">
            <label for="name">{{remark.DATA}}</label>
          
			<textarea class="form-control" rows="10" name="remark" id="remark" placeholder="" ></textarea>
        </div>
        <div class="form-group">
            <label for="name">跳转url</label>
            <input class="form-control" id="url" name="url" placeholder="" type="text">
        </div>



        <button id="submit"  type="submit" style="width:200px;display: block;margin:auto;margin-bottom: 100px;" class="btn btn-primary btn-lg">提交</button>
    </form>
   
</div>
		</div>
	</div>
</body>
</html>
