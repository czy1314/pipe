<?php echo $this->fetch('member.header.html'); ?>
<div class="content">

<link type="text/css" href="<?php echo $this->res_base . "/" . 'css/font-awesome.css'; ?>" rel="stylesheet" />
    <?php echo $this->fetch('member.menu.html'); ?>
    <div class="no-bg" id="right">
    	<?php echo $this->fetch('member.curlocal.html'); ?>
        
        <div class="profile clearfix">
		
        	
             <div class="info clearfix">
				
				<dl class="col-1 fleft">
					<dt>
						<span>欢迎您，</span><strong><?php echo htmlspecialchars($this->_var['user']['user_name']); ?></strong>
						
					</dt>
					
					<dd>
						<span>上次登录时间：<?php echo local_date("Y-m-d H:i:s",$this->_var['user']['last_login']); ?></span>
						<span>上次登录 IP：<?php echo $this->_var['user']['last_ip']; ?></span>
					</dd>
					<dd><?php echo sprintf('您有 <em class="red">%s</em> 条短消息，<a href="index.php?app=message&act=newpm">点击查看</a>', $this->_var['new_message']); ?></dd>
				</dl>
				  
			</div>
        </div>
    </div>  
       