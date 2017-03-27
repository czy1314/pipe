<?php echo $this->fetch('member.header.html'); ?>
<style>.width_normal{width:480px;}</style>
<?php echo $this->_var['build_editor']; ?>
<div class="content">
    <div class="totline"></div>
    <div class="botline"></div>
    <?php echo $this->fetch('member.menu.html'); ?>
    <div id="right"> <?php echo $this->fetch('member.submenu.html'); ?>
        <div class="wrap">
            <div class="public">
                <div class="information">
                    <div class="setup info shop">
                        <form method="post"  id="my_wxconfig_form">
			

                            <table style="width: 100%">
                                
                                <tr>
                                    <th class="width2">first</th>
                                    <td>
                                        <p class="td_block"><input type="text" class="text width_normal" name="first" value="<?php echo htmlspecialchars($this->_var['wx_config']['url']); ?>" style="width:480px;" /></p>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <th>keyword1:</th>
                                    <td>
                                        <p class="td_block"><input type="text" name="keyword1" class="text width_normal" id="token" value="<?php echo htmlspecialchars($this->_var['wx_config']['token']); ?>" /></p>
                                    </td>
                                </tr>
								
								 
								 <tr>
                                    <th>keyword2：</th>
                                    <td>
                                        <p class="td_block"><input type="text" name="keyword2" class="text width_normal"  /></p>
                                     
                                    </td>
                                </tr>
								<tr>
                                    <th>remark：</th>
                                    <td>
                                        <p class="td_block">
											<textarea class="form-control" rows="50" cols="69" name="remark" id="remark" placeholder="" ></textarea>
									    </p>
                                     
                                    </td>
                                </tr>
								<tr>
                                    <th>跳转url：</th>
                                    <td>
                                        <p class="td_block"><input  type="text" name="url" placeholder="请填写用户点击消息之后跳转的网址" class="text width_normal"  /></p>
                                     
                                    </td>
                                </tr>
								<tr>
                                    <th>发送之前是否测试：</th>
                                    <td>
                                        <input type="checkbox" name="must_test" value="1" />
                                      <label style="" class="input_right_tip">发送之前测试，能够提高发送成功率</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="issuance"><input type="submit" class="btn" value="提交" /></div>
                                    </td>
                                </tr>
                            </table>
                        <?php echo $this->_var['hidden_value']; ?></form>
                    </div>
                </div>
            </div>
            <div class="wrap_bottom"></div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
</div>
<?php echo $this->fetch('footer.html'); ?>
