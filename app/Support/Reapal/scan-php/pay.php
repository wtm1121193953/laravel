<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>支付宝预支付接口</title>
  </head>
  
  <body>
    <div align="center">
    <form action="payResult.php" method="post">
    <table align="center">
    	<tr><td colspan="2" align="center"><h2>支付宝预支付接口</h2></td></tr>
		
    	<tr><td>客户端类型：</td><td><input type="text" name="client_type" value=""/>0,微信；1，支付宝</td></tr>
		<tr><td>商户订单号：</td><td><input type="text" name="order_no" value=""/></td></tr>
		<tr><td>金额：</td><td><input type="text" name="total_fee" value=""/></td></tr>
		<tr><td>微信openID或者支付宝buyer_user_id：</td><td><input type="text" name="user_id" value=""/></td></tr>
		<tr><td>微信或支付宝唯一编号：</td><td><input type="text" name="appid_source" value=""/></td></tr>
		<tr><td>商户手机号：</td><td><input type="text" name="store_phone" value=""/></td></tr>
		<tr><td>商户名称：</td><td><input type="text" name="store_name" value=""/></td></tr>
		<tr><td>入驻成功商户号：</td><td><input type="text" name="merchant_code" value=""/></td></tr>
    	<tr><td></td><td><input type="submit" value="提交"/></td></tr>		
    </table>
    </form>
    </div>
  </body>
</html>