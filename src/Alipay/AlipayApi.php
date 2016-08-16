<?php
namespace Alipay;

/**
 * 支付接口
 */
class AlipayApi
{

	/**
	 * 支付
	 * @param string $order_no 支付订单号
	 * @param string $subject 订单名称
	 * @param int $total_fee 价格
	 * @param string $body string 描述
	 * @return form表单
	 */
	public function pay($order_no, $subject, $price, $body = '')
	{
		$alipay_config = config('alipay');
		$parameter = array(			
			"service"       => $alipay_config['service'],
			"partner"       => $alipay_config['partner'],
			"seller_id"  => $alipay_config['seller_id'],
			"payment_type"	=> $alipay_config['payment_type'],
			"notify_url"	=> $alipay_config['notify_url'],
			"return_url"	=> $alipay_config['return_url'],
			
			"anti_phishing_key"=>$alipay_config['anti_phishing_key'],
			"exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
			"out_trade_no"	=> $out_trade_no,
			"subject"	=> $subject,
			"total_fee"	=> $total_fee,
			"body"	=> $body,
			"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
		$alipaySubmit = new AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
		return  $html_text;
	}

	/**
	 * 前台通知
	 * @return array
	 * 更多返回值参考支付宝文档
	 * https://doc.open.alipay.com/docs/doc.htm?treeId=62&articleId=104743&docType=1#s1
	 */
	public function returnback()
	{
		$alipay_config = config('alipay');

		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyReturn();  //验签

		if($verify_result) {
			$data['order_no'] = $_GET['out_trade_no']; //发起支付请求时的订单号
			$data['trade_no'] = $_GET['trade_no']; //支付宝交易号
			$data['trade_status'] = $_GET['trade_status'];  //交易状态
			$data['total_fee'] = $_GET['total_fee']; //交易金额


		    	if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				return ['code'=>200, 'data'=>$data, 'message'=>'支付受理完成'];
		    	} else {
		      		return ['code'=>400, 'message'=>$trade_status];
		    	}
		} else {
		    return ['code'=>400, 'message'=>'验证失败'];
		}
	}


	/**
	 * 后台通知
	 * @return array
	 * 更多返回值参考支付宝文档
	 * https://doc.open.alipay.com/docs/doc.htm?treeId=62&articleId=104743&docType=1#s3
	 */
	public function notify()
	{	
		$alipay_config = config('alipay');
		
		$alipayNotify = new AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();  //验签

		if ($verify_result) {
			$data['order_no'] = $_POST['out_trade_no']; //商户订单号
			$data['trade_no'] = $_POST['trade_no']; //支付宝交易号
			$data['trade_status'] = $_POST['trade_status'];	 //交易状态 

			$data['seller_id'] = $_POST['seller_id']; //卖家支付宝账户号
			$data['total_fee'] = $_POST['total_fee'];  //交易金额

			$data['buyer_email'] = $_POST['buyer_email']; //买家支付宝账户号
			$data['gmt_create'] = $_POST['gmt_create']; //交易创建时间
			$data['gmt_payment'] = $_POST['gmt_payment']; //交易付款时间
			$data['gmt_close'] = $_POST['gmt_close']; //交易关闭时间

			if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
				if ($_POST['seller_id'] == $alipay_config['seller_id'] ) {
					return ['code'=>200, 'data'=> $data,'message'=>'验证成功'];
				}
			}
		} else { //验证失败
			return ['code'=>400, 'message'=>'验证失败'];
		}
	}
}