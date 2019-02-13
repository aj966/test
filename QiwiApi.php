<?php 

class QiwiApi {
    
    private $_phone;
    private $_token;
    private $_url;
    private $_proxy_ip;
    private $_proxy_auth;
 
    function __construct($phone, $token, $proxy_ip = false, $proxy_auth = false) {
        $this->_phone = $phone;
        $this->_token = $token;
        $this->_url   = 'https://edge.qiwi.com/';
        $this->_proxy_ip = $proxy_ip;
        $this->_proxy_auth = $proxy_auth;
    }
    
    private function sendRequest($method, array $content = [], $post = false) {
        $ch = curl_init();
        if ($post) {
            curl_setopt($ch, CURLOPT_URL, $this->_url . $method);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
        } else {
            curl_setopt($ch, CURLOPT_URL, $this->_url . $method . '/?' . http_build_query($content));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->_token
        ]); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        if ($this->_proxy_ip != false) {
			curl_setopt($ch, CURLOPT_PROXY, $this->_proxy_ip);
		}
        if ($this->_proxy_auth != false) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->_proxy_auth);
		}

        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, 1);
    }
    
    public function call($method = "", $params = []) {
        return $this->sendRequest($method, $params);
    }
    
    public function getBalance($providerId) {
        return $this->sendRequest('funding-sources/v2/persons/'. $providerId .'/accounts')['accounts'];
    }
    
    
    
	
    public function getAccount(Array $params = []) {
        return $this->sendRequest('person-profile/v1/profile/current', $params);
    }
    public function getPaymentsHistory(Array $params = []) {
        return $this->sendRequest('payment-history/v1/persons/' . $this->_phone . '/payments', $params);
    }
    public function getPaymentsStats(Array $params = []) {
        return $this->sendRequest('payment-history/v1/persons/' . $this->_phone . '/payments/total', $params);
    }
    
    
    
    
    
    /*
    Возвращаем список балансов:
    array(
        qw_wallet_rub => 95374.6
        qw_wallet_kzt => 660.31
        mc_megafon_rub => 0
    )
    */
    public function getBalance_($providerId) {
        $r = $this->sendRequest('funding-sources/v2/persons/'. $providerId .'/accounts');
        
        $res = [];
        foreach ($r['accounts'] as $k => $v) {
            if ($v['hasBalance'] != 1) {
                continue;
            }
            $res[$v['alias']] = isset($v['balance']['amount']) ? $v['balance']['amount'] : 0;
        }
        return $res;
    }
    
    
    
    
    /*
    Определяем статус транзакции:
    return WAITING | SUCCESS | ERROR
    */
    public function getStatusTransaction($transactionId) {
        
        $r = $this->sendRequest('payment-history/v2/transactions/'. $transactionId);
        
        return isset($r['status']) ? $r['status'] : false;
    }
    

    /*
    Определяем тип идентификации счетов:
    array(
        QIWI => FULL
        AKB => ANONYMOUS
    )
    */
    public function getIdentification_() {
        $r = $this->sendRequest('person-profile/v1/profile/current');
        
        if (!isset($r['contractInfo']) || !isset($r['contractInfo']['identificationInfo'])) {
            return [];
        }
        $res = [];
        foreach ($r['contractInfo']['identificationInfo'] as $k => $v) {
            $res[$v['bankAlias']] = $v['identificationLevel'];
        }
        return $res;
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