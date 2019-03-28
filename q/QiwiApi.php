<?php 

class QiwiApi {
    
    private $_phone;
    private $_token;
    private $_url;
    private $_proxy_ip;
    private $_proxy_auth;
 
    function __construct($phone, $token, $proxy_ip = "", $proxy_auth = "") {
        $this->_phone = $phone;
        $this->_token = $token;
        $this->_url   = 'https://edge.qiwi.com/';
        $this->_proxy_ip = $proxy_ip;
        $this->_proxy_auth = $proxy_auth;
    }
    
    private function sendRequest($method, $post = []) {
        $ch = curl_init();
        if (count($post)) {
            curl_setopt($ch, CURLOPT_URL, $this->_url . $method);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        } else {
            curl_setopt($ch, CURLOPT_URL, $this->_url . $method);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->_token
        ]); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        if ($this->_proxy_ip != "") {
			curl_setopt($ch, CURLOPT_PROXY, $this->_proxy_ip);
		}
        if ($this->_proxy_auth != "") {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->_proxy_auth);
		}

        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, 1);
    }
    
    public function call($method = "", $params = []) {
        return $this->sendRequest($method, $params);
    }
    
    public function getAccount(Array $params = []) {
        return $this->sendRequest('person-profile/v1/profile/current', $params);
    }
    public function getPaymentsHistory(Array $params = []) {
        return $this->sendRequest('payment-history/v1/persons/' . $this->_phone . '/payments/?' . http_build_query($params));
    }
    public function getPaymentsStats(Array $params = []) {
        return $this->sendRequest('payment-history/v1/persons/' . $this->_phone . '/payments/total/?' . http_build_query($params));
    }
    public function getTax($providerId) {
        return $this->sendRequest('sinap/providers/'. $providerId .'/form');
    }  
    public function sendMoneyToQiwi(Array $params = []) {
        return $this->sendRequest('sinap/api/v2/terms/99/payments', $params, 1);
    }
    public function sendMoneyToProvider($providerId, Array $params = []) {
        return $this->sendRequest('sinap/api/v2/terms/'. $providerId .'/payments', $params, 1);
    }
}
