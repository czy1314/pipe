<link type="text/css" href="<?php echo $this->res_base . "/" . 'css/footer.css'; ?>" rel="stylesheet" />
 
<footer>
    <div class="footer">
        <div class="footer-bottom fn-clear">
           
            <div class="icp-info">
				<!--<a href="http://wpa.qq.com/msgrd?v=3&uin=2052977199&site=qq&menu=yes" target="_blank" >在线客服</a><span class="line">|</span><a href="/intro/about.html">关于我们</a><span class="line">|</span><a href="/intro/about.html#contact">联系我们</a><span class="line">|</span><a href="javascript:window.scroll(0,0);">回到顶部</a> -->
				&nbsp;&nbsp;
				
				<?php if ($this->_var['icp_number']): ?><?php echo $this->_var['icp_number']; ?><?php endif; ?> <?php echo $this->_var['statistics_code']; ?></a>&nbsp;&nbsp;<br>Copyright © 2012-2015 51fqf All Rights Reserved 哈尔滨品胜社会咨询服务公司 版权所有<p></p>
				
			</div>
            
        </div>
    </div>
 </footer>
<?php if ($this->_var['member_role'] != 'seller_admin'): ?><?php echo $this->_var['service_key']; ?><?php endif; ?>
<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?ea6a236b975fb60582fd53bf2a553716";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>

