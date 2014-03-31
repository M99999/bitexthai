<?php
class bitexthai
{
	var $api_key, $nonce, $signature, $twofa;
	var $api_url = 'https://exchange.bitcoin.co.th/api/';
	var $msg;
	function __construct($api_key, $api_secret, $twofa=''){
		$this->api_key = $api_key;
		
		$mt = explode(' ', microtime());
		$this->nonce = $mt[1].substr($mt[0], 2, 6);
		
		$this->signature = base64_encode(hash('sha256', $api_key.$this->nonce.$api_secret));
		if($twofa != ''){
			$this->twofa = $twofa;
		}
	}
	
	function curl($data='', $endpoint=''){
		if($ch = curl_init ()){
			$data['key'] = $this->api_key;
			$data['nonce'] = $this->nonce;
			$data['signature'] = $this->signature;
			if($this->twofa != ''){
				$data['twofa'] = $this->twofa;
			}
			
			curl_setopt ( $ch, CURLOPT_URL, $this->api_url.$endpoint);
			curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, false );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false ); 
			curl_setopt ( $ch, CURLOPT_POST, count($data));
			curl_setopt ( $ch, CURLOPT_POSTFIELDS,$data);
			
			$str = curl_exec ( $ch );
			curl_close ( $ch );
			return json_decode($str);
		}
		return false;
	}
	
	function order($pairing_id=1, $type='buy', $amount = 0, $rate = 0){
		$order = $this->curl(array('pairing' => $pairing_id,
								   'type' => $type,
								   'amount' => $amount,
								   'rate' => $rate), 'order');
		if(!$order->success){
			$this->msg = $order->error;
		}else{
			$this->msg = $order->order_id;
		}
		return $order->success;
	}
	
	function cancel($pairing_id=1, $order_id=0){
		$order = $this->curl(array('pairing' => $pairing_id,
								   'order_id' => $order_id),
								   'cancel');
		if(!$order->success){
			$this->msg = $order->error;
		}
		return $order->success;
	}
	
	function balance(){
		$balance = $this->curl('','balance');
		if($balance->success){
			return $balance->balance;
		}else{
			$this->msg = $balance->error;
			return false;
		}
	}
	
	function getorders($data=''){
		$orders = $this->curl($data,'getorders');
		if($orders->success){
			return $orders->orders;
		}else{
			$this->msg = $orders->error;
			return false;
		}
	}
	function history($data=''){
		$history = $this->curl($data,'history');
		if($history->success){
			return $history->transactions;
		}else{
			$this->msg = $history->error;
			return false;
		}
	}
	function deposit($currency = 'BTC', $new = false){
		$deposit = $this->curl(array('currency' => $currency, 'new' => $new),'deposit');
		if($deposit->success){
			return $deposit->address;
		}else{
			$this->msg = $deposit->error;
			return false;
		}
	}
	
	function withdraw($currency, $amount, $address){
		$withdraw = $this->curl(array('currency' => $currency, 'amount' => $amount, 'address' => $address),'withdrawal');
		if($withdraw->success){
			return $withdraw->withdrawal_id;
		}else{
			$this->msg = $withdraw->error;
			return false;
		}
	}
}
?>