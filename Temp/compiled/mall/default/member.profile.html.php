<?php echo $this->fetch('member.header.html'); ?>
<style>
.borline td {padding:10px 0px;}
.ware_list th {text-align:left;}
</style>
<script type="text/javascript">

$(function(){
    $('#profile_form').validate({
        errorPlacement: function(error, element){
            $(element).parent('span').parent('b').after(error);
        },
        rules : {
            portrait : {
                accept   : 'gif|jpe?g|png'
            }
        },
        messages : {
            portrait  : {
                accept   : '支持gif、jpeg、jpg、png格式'
            }
        }
    });
    $('input[ectype="change_avatar"]').change(function(){

        var src = getFullPath($(this)[0]);
        $('img[ectype="avatar"]').attr('src', src);
        $('input[ectype="change_avatar"]').removeAttr('name');
        $(this).attr('name', 'portrait');
    });
});
</script>
<div class="content">
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right">
	<?php echo $this->fetch('member.curlocal.html'); ?>
	<link type="text/css" rel="stylesheet" href="<?php echo $this->res_base . "/" . 'css/safety.css'; ?>"/>
		<div class="security">
			<h1>安全信息</h1>
			<ul class="details">
			
			    <li>
					<p class="title">基本信息</p>
					<p class="info"><?php if ($this->_var['is_full_info']): ?>100%<?php else: ?>待完善<?php endif; ?></p>
					<a href="<?php echo url('app=member&act=baseinfo'); ?>" class="status">
					<?php if ($this->_var['is_full_info']): ?>修改<?php else: ?>完善<?php endif; ?>
					</a>
				</li>
				
				<li>
					<p class="title">登录密码</p>
					<p class="info">安全性高的密码可以使账号更安全</p>
					<a href="<?php echo url('app=member&act=password'); ?>" class="status">修改</a>
				</li>
				
				
				
				
				<li>
					<p class="title">个人邮箱</p>
					<p class="info"><?php if ($this->_var['profile']['email']): ?>已设置<?php else: ?>未设置<?php endif; ?></p>
					<a href="<?php echo url('app=member&act=email'); ?>" class="status">
					<?php if ($this->_var['profile']['email']): ?>修改<?php else: ?>设置<?php endif; ?>
					</a>
				</li>
				
			</ul>
		</div> 

            <div class="clear"></div>
            <div class="adorn_right1"></div>
            <div class="adorn_right2"></div>
            <div class="adorn_right3"></div>
            <div class="adorn_right4"></div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php echo $this->fetch('footer.html'); ?>
