<?php
class CheckOrder {
    private $login_url;
    private $check_url;
    private $cookie_path;
    private $username;
    private $password;

    public function __construct($options)
    {
        $this->change($options);
    }

    public function change($options)
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            } else {
                throw new Exception(sprintf('The property %s does not exists', $key));
            }
        }
    }

    public function check($url = null) 
    {
        if (!$url) {
            throw new Exception('You must provide url to check');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->login_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6'); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_path);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_path); 

        $html = new DOMDocument();
        $html->loadHTML(curl_exec($ch));
        $csrf_token = $html->getElementById('csrf_token')->getAttribute('value');

        if (curl_error($ch)) {
            throw new Exception(curl_error($ch));
        }

        curl_setopt($ch, CURLOPT_URL, $this->check_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            '_username' => $this->username, 
            '_password' => $this->password, 
            '_remember_me' => 'on',
            '_csrf_token' => $csrf_token
        )));

        curl_exec($ch);
        if (curl_error($ch)) {
            throw new Exception(curl_error($ch));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);

        curl_exec($ch); 
        
        if (curl_error($ch)) {
            throw new Exception(curl_error($ch));
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        unlink($this->cookie_path);

        return array(
            'url' => $info['url'],
            'http_code' => $info['http_code']
        );
    }
}
$check = new CheckOrder(array(
    'login_url' => 'http://domain/login',
    'check_url' => 'http://domain/login_check',
    'cookie_path' => 'path/to/cookie.tx',
    'username' => 'username',
    'password' => 'password'
));

echo('<pre>');
print_r($check->check('http://prgcc.salda.lt/lt/crm/order/DXN10000001190'));
echo('</pre>');

class CheckOrderStatic
{
    private static $login_url = 'http://domain/login';
    private static $check_url = 'http://domain/login_check';
    private static $cookie_path = 'path/to/cookie.txt';
    private static $username = 'username';
    private static $password = 'password';

    public static function check($url)
    {
        if (!$url) {
            throw new Exception('You must provide url to check');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$login_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6'); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookie_path);
        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookie_path); 

        $html = new DOMDocument();
        $html->loadHTML(curl_exec($ch));
        $csrf_token = $html->getElementById('csrf_token')->getAttribute('value');

        if (curl_error($ch)) {
            throw new Exception(curl_error($ch));
        }

        curl_setopt($ch, CURLOPT_URL, self::$check_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            '_username' => self::$username, 
            '_password' => self::$password, 
            '_remember_me' => 'on',
            '_csrf_token' => $csrf_token
        )));

        curl_exec($ch);
        if (curl_error($ch)) {
            throw new Exception(curl_error($ch));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, false);

        curl_exec($ch); 
        
        if (curl_error($ch)) {
            throw new Exception(curl_error($ch));
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        unlink(self::$cookie_path);

        return array(
            'url' => $info['url'],
            'http_code' => $info['http_code']
        );
    }
}

echo('<pre>');
print_r(CheckOrderStatic::check('http://domain/url_to_check'));
echo('</pre>');
