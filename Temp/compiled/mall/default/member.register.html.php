<?php echo $this->fetch('header.html'); ?>
<link type="text/css" href="<?php echo $this->res_base . "/" . 'css/login.css'; ?>" rel="stylesheet" /> 
<script type="text/javascript">
$(function(){
	var url = "<?php echo $this->_var['site_url']; ?>/index.php?app=memberrec&act=ajax_get_code_&ajax";	
	var data = {step:'reg',phone_mob:$('#phone_mob').val()};
	code($('#btn_code_get'),url,data);
	decr_radio('user_role');					
    $('#register_form').validate({
        errorPlacement: function(error, element){
            var error_td = element.parent('dd');
            error_td.find('label').hide();
            error_td.append(error);
        },
        success       : function(label){
            label.addClass('validate_right').text('OK!');
        },
        onkeyup: false,
        rules : {
            user_name : {
                required : true,
                byteRange: [3,15,'<?php echo $this->_var['charset']; ?>'],
                remote   : {
                    url :'index.php?app=member&act=check_user&ajax=1',
                    type:'get',
                    data:{
                        user_name : function(){
                            return $('#user_name').val();
                        }
                    },
                    beforeSend:function(){
                        var _checking = $('#checking_user');
                        _checking.prev('.field_notice').hide();
                        _checking.next('label').hide();
                        $(_checking).show();
                    },
                    complete :function(){
                        $('#checking_user').hide();
                    }
                }
            },
            password : {
                required : true,
                minlength: 6
            },
            password_confirm : {
                required : true,
                equalTo  : '#password'
            },
            email : {
                required : true,
                email    : true
            },
			phone_mob : {
                required : true,
                maxlength:11,
				minlength:11
            },
			code : {
                required:true,
                maxlength:6,
				minlength:6
            },
			user_role:{
			   required:true
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
            },
            agree : {
                required : true
            }
        },
        messages : {
            user_name : {
                required : '您必须提供一个用户名',
                byteRange: '用户名必须在3-15个字符之间',
                remote   : '您提供的用户名已存在'
            },
            password  : {
                required : '您必须提供一个密码',
                minlength: '密码长度应在6-20个字符之间'
            },
            password_confirm : {
                required : '您必须再次确认您的密码',
                equalTo  : '两次输入的密码不一致'
            },
            email : {
                required : '您必须提供您的电子邮箱',
                email    : '这不是一个有效的电子邮箱'
            },
			phone_mob : {
                required : '请输入手机号码',
                maxlength:'手机号码长度不能超过11位',
				minlength:'手机号码长度不能少于11位'
            },
			code : {
                required : '请输入验证码',
                maxlength:'验证码长度不能超过6位',
				minlength:'验证码长度不能少于6位'
            },
			user_role:{
			   required:'请选择用户类型'
			},
            captcha : {
                required : '请输入右侧图片中的文字',
                remote   : '验证码错误'
            },
            agree : {
                required : '您必须阅读并同意该协议,否则无法注册'
            }
        }
    });
});
</script>
<script type="text/javascript">
$(function(){
	poshytip_message($('#user_name'));
	poshytip_message($('#password'));
	poshytip_message($('#password_confirm'));	
	poshytip_message($('#email'));
	poshytip_message($('#captcha1'));
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
<div id="page-register" class="w login-register mt20 mb20">
	
	<div class="w clearfix">
		<form name="" id="register_form" method="post" action="">
		<!-- <div class="col-main">
    	<div class="login-edit-field" area="login_left" style="margin:70px auto;"   widget_type="area">
         		<?php $this->display_widgets(array('page'=>'login','area'=>'login_left')); ?>
        </div>
		<h4>如果您是本站用户</h4>
		<div class="login-field">
			<span>我已经注册过帐号，立即<a href="index.php?app=member&act=login" class="login-field-btn">登录</a></span>
			<span>或者 <a href="index.php?app=find_password" class="find-password">找回密码</a></span>
		</div>
	    </div> -->
		<div class="title center" >用户注册</div>
		<div class="form-left">
    		
       	 	<div class="content">
				
        			<dl class="clearfix">
                		<dt>用户名</dt>
                    	<dd>
                    		<input type="text"  id="user_name" class="login"  name="user_name" title="3-15位字符，由中文、英文、数字以及'-'、'_'组成"  />
                        	<br /><label></label>
                    	 </dd>
                	</dl>
             		<dl class="clearfix">
                		<dt>密&nbsp;&nbsp;&nbsp;码</dt>
                    	<dd>
                    		<input class="login" type="password" id="password" name="password" title="长度在6-20个字符之间,由字母、数字和标点符号组成" />
                        	<div class="clr"></div><label></label>
                    	</dd>
                	</dl>
                	<dl class="clearfix">
              			<dt>确认密码</dt>
                    	<dd>
                    		<input class="login" type="password" id="password_confirm" name="password_confirm" title="请再次输入你的密码" />
                        	<div class="clr"></div><label></label>
                   	 	</dd>
                	</dl>
					<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>
      			
				<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>                
   			</div>
		</div>
		
		
		<div class="form-right">
    		<div class="title">&nbsp;</div>
       	 	<div class="content">
					<dl class="clearfix">
                		<dt>电子邮箱</dt>
                    	<dd>
                    		<input class="login" type="text" id="email" name="email" title="请输入你的常用电邮，将来用于找回密码和接收商城信息" />
                        	<div class="clr"></div><label></label>
                    	</dd>
                	</dl>
        			<dl class="clearfix">
                		<dt>手机</dt>
                    	<dd>
                    		<input type="text"  id="phone_mob" class="login"  name="phone_mob" title="输入手机验证码"  />
                        	<br /><label></label>
                    	 </dd>
                	</dl>
					<dl class="clearfix">
                		<dt>手机验证码</dt>
                    	<dd>
                    		<input class="input_css flex-box text" id="code" placeholder="请输入验证码" maxlength="6" name="code" value="" type="text">	<a id="btn_code_get">获取验证码</a>
							<p class="warning" id="w1"></p>
                    	 </dd>
                	</dl>
                	
            		<?php if ($this->_var['captcha']): ?>
                	<dl class="clearfix">
                		<dt>验证码</dt>
                    	<dd class="captcha clearfix">
                    		<input type="text" class="input float-left" name="captcha"  id="captcha1" title="请输入验证码，不区分大小写" />
                        	<img height="26" id="captcha" src="index.php?app=captcha&amp;<?php echo $this->_var['random_number']; ?>" class="float-left" />
                        	<a href="javascript:change_captcha($('#captcha'));" class="float-left">看不清，换一张</a>
                        	<div class="clr"></div><label></label>
                    	</dd>
                	</dl>
           			<?php endif; ?>
           			
					<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>
      			  
				<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>                
   			</div>
		</div>
		<div style="content:'\20'; display:block; overflow:hidden; height:0; clear:both;">&nbsp;</div>      
		<dl class="clearfix sub_reg">
                		
                    	<dd>
                 			<input type="submit" name="Submit"value="立即注册" id="submit"title="立即注册" />
                  			<input type="hidden" name="ret_url" value="<?php echo $this->_var['ret_url']; ?>" />
                    	 </dd>
						 
		</dl>
		<?php echo $this->_var['hidden_value_']; ?></form>  
    </div>
</div>
</div>
<?php echo $this->fetch('footer.html'); ?>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/supersized.3.2.7.min.js'; ?>"></script>
<script type="text/javascript" src="<?php echo $this->res_base . "/" . 'js/supersized-init.js'; ?>"></script>
