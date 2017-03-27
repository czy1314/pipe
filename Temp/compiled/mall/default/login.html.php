<?php echo $this->fetch('header.html'); ?>
<link type="text/css" href="<?php echo $this->res_base . "/" . 'css/login.css'; ?>" rel="stylesheet" /> 
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/supersized.3.2.7.min.js'; ?>"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/supersized-init.js'; ?>"></script>
<script type="text/javascript">
$(function(){
    $('#login_form').validate({
        errorPlacement: function(error, element){
           var error_td = element.parent('dd');
            error_td.find('label').hide();
            error_td.append(error);
        },
        success       : function(label){
            label.addClass('validate_right').text('OK!');
        },
        onkeyup : false,
        rules : {
            user_name : {
                required : true
            },
            password : {
                required : true
            },
            captcha : {
                required : true,
                remote   : {
                    url : 'index.php?app=captcha&act=check_captcha',
                    type: 'get',
                    data:{
                        captcha : function(){
                            return $('#captcha1').val();
                        }
                    }
                }
            }
        },
        messages : {
            user_name : {
                required : '您必须提供一个用户名'
            },
            password  : {
                required : '您必须提供一个密码'
            },
            captcha : {
                required : '请输入右侧图片中的文字',
                remote   : '验证码错误'
            }
        }
    });
	
});
</script>
<style>
.w{width:95%;}
/*

	Supersized - Fullscreen Slideshow jQuery Plugin
	Version : 3.2.7
	Site	: www.buildinternet.com/project/supersized
	
	Author	: Sam Dunn
	Company : One Mighty Roar (www.onemightyroar.com)
	License : MIT License / GPL License
	
*/

* {margin:0; padding:0;}
body {background:#111; height:100%;}
	img {border:none;}
	
	#supersized {display:block; position:fixed; left:0; top:0; overflow:hidden; z-index:-999; height:100%; width:100%;}
		#supersized img {width:auto; height:auto; position:relative; display:none; outline:none; border:none;}
			#supersized.speed img {-ms-interpolation-mode:nearest-neighbor; image-rendering: -moz-crisp-edges;}	/*Speed*/
			#supersized.quality img {-ms-interpolation-mode:bicubic; image-rendering: optimizeQuality;}			/*Quality*/
		
		#supersized li {display:block; list-style:none; z-index:-30; position:fixed; overflow:hidden; top:0; left:0; width:100%; height:100%; background:#111;}
		#supersized a {width:100%; height:100%; display:block;}
			#supersized li.prevslide {z-index:-20;}
			#supersized li.activeslide {z-index:-10;}
				#supersized li.image-loading img{visibility:hidden;}
			#supersized li.prevslide img, #supersized li.activeslide img{display:inline;}


#supersized img {max-width: none !important}

</style>
<div id="main" class="w-full">
	<div id="page-login" class="w login-register mt20 mb20">
    	
        <div class="w clearfix">
		<div class="col-sub center">
      		<div class="form">
        	<div class="title">用户登录</div>
            <div class="content">
        		<form method="post" id="login_form">
                	<dl class="clearfix">
                    	<dt>用户名</dt>
                        <dd>
                        	<input class="login" type="text" name="user_name"  />
                            <div class="clr"></div><label></label>
                        </dd>
						<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>
                    </dl>
               		<dl class="clearfix">
                    	<dt>密&nbsp;&nbsp;&nbsp;码</dt>
                        <dd>
                        	<input class="login" type="password" name="password"/>
                            <div class="clr"></div><label></label>
                        </dd>
						<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>
                    </dl>
               
               		<?php if ($this->_var['captcha']): ?>
              		<dl class="clearfix">
                  		<dt>验证码</dt>
                  		<dd class="captcha clearfix">
                     		<input type="text" class="float-left" name="captcha" id="captcha1" />
                     		<img height="26" id="captcha" src="index.php?app=captcha&amp;<?php echo $this->_var['random_number']; ?>" class="float-left" />
                            <a href="javascript:change_captcha($('#captcha'));" class="float-left">看不清，换一张</a>
                     		<div class="clr"></div><label></label>
                  		</dd>
						<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>
               		</dl>
               		<?php endif; ?>
               		<dl class="clearfix">
                  		<dt><a href="<?php echo url('app=find_password'); ?>" class="find-password">忘记密码？</a></dt>
                  		<dd class="clearfix">
                     		<input type="submit" id="submit" name="Submit" value="登录" title="登录" />
                     		
                     		<input type="hidden" name="ret_url" value="<?php echo $this->_var['ret_url']; ?>" />
                  		</dd>
						<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>
               		</dl>
                	<dl class="clearfix">
                  		<dt>&nbsp;</dt>
                  		<dd class="register-now">
                        	如果您还不是会员，请<a href="<?php echo url('app=member&act=register&ret_url=' . $this->_var['ret_url']. ''); ?>" title="注册">注册</a>
                        </dd>
						<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>
               		</dl>
            
            		<div class="partner-login" style="display:none">
						<h3>你可以用合作伙伴账号登陆</h3>
						<p><a class="qq-login" href="#"></a><a class="weibo-login" href="#"></a></p>
					</div>
					<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>
         		<?php echo $this->_var['hidden_value']; ?></form>
				<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>
         	</div>
      	</div>
		</div>
        </div>
	</div>
</div>
<?php echo $this->fetch('footer.html'); ?>