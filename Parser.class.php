<?php   
    /**
     * Parser class content
     * @author Vasinsky Igor
     * @email igor.vasinsky@gmail.com
     * @copyright 2013
     */
     
    class Parser{ 
        /**
         * string the address of the donor
         */ 
        public $url = '';
        /**
         * string replacement REFERER
         */
        public $referer = '/';
        /**
         * string Replacing USER AGENT
         */
        public $useragent = 'ParserBot';
        /**
         * int Timeout between attempts
         */
        public $timeout = 10;
        /**
         * bool Follow redirect
         */
        public $followlocation = true;
        /** 
         * bool Return content after parsing
         */
        public $returndata = true;
        /** 
         * string cookie file
         */
        private $filecookie = 'cookie.txt';
        /** 
         * bool use of cookies
         */
        private $cookie = false;
        /**
         * array Info about a connection
         */
        private $info = null;
        /** 
         * bool Deny certificate validation
         */
        public $sslpeer = false;
        /**
         * bool check for the existence of a host name
         */
        public $sslhost = false;
        /** 
         * int SSL version
         */
        public $sslversion = 2;
        /**
         * bool Use SSL certificate
         */
        private $setSSL = false;
        /**
         * string name of the certificate
         */
        private $sslcert;
        /**
         * string The password for the certificate
         */
        private $sslpass;
        /**
         * array Additional storage options for POST and GET requests
         */
        private $options_query = array();
        /**
         * string to store an entire page of content
         */
        private $content = '';
        
        /**
         * Prepare options for Curl 
         * return array
         */ 
        private function setOptions(){      
            $options[CURLOPT_URL] = $this->url;
            $options[CURLOPT_REFERER] = $this->referer;
            $options[CURLOPT_CONNECTTIMEOUT] = $this->timeout;
            $options[CURLOPT_RETURNTRANSFER] = $this->returndata;
            $options[CURLOPT_USERAGENT] = $this->useragent;
            
            if(!empty($this->options_query))
                $options = array_merge($options, $this->options_query);            
            
            if($this->cookie === true){
                $options[CURLOPT_COOKIEJAR] = $this->filecookie; 
                $options[CURLOPT_COOKIEFILE] = $this->filecookie;    
            }

            $options[CURLOPT_SSL_VERIFYPEER] = $this->sslpeer;
            $options[CURLOPT_SSL_VERIFYHOST] = $this->sslhost;
            $options[CURLOPT_SSLVERSION] = $this->sslversion;
                    
            if($this->setSSL === true){
                $options[CURLOPT_SSLCERT] = $this->sslcert;
                $options[CURLOPT_SSLCERTPASSWD] = $this->sslpass;
            }
            
            $options[ CURLOPT_FOLLOWLOCATION] = $this->followlocation;  
            
            return $options;     
        } 
        /**
         * use of cookies
         * @param string - path/name_file_cookie
         */
        public function setCookie($filecookie){
            $filecookie = $this->filecookie;
            $this->cookie = true;
        }
        /**
         * Use SSL certificate
         * @param string - path/name_cert
         * @param string - cert_password 
         * return object
         */
        public function useSSL($cert,$pass){
            $this->setSSL = true;
            $this->sslcert = $cert;
            $this->sslpass = $pass;
            return $this;
        }         
        
        /**
         * Return connection info 
         * return array
         */ 
        public function getInfo(){
            return $this->info;
        } 
        
        /**
         * initialization
         * return object
         */ 
        public function init(){

            if($this->url == '')
                throw new Exception('Unknown URL donor');
                               
            $options = $this->setOptions();
            
            $curl = curl_init();
            curl_setopt_array($curl, $options);
            $out = curl_exec($curl);
            $info = curl_getinfo($curl);
    
            if($info['http_code'] != 200){
                $this->info = $info;
                return false;
            }
    
            curl_close($curl); 
            
            $this->content = ($this->returndata !== false) ? $out : null;   
            
            return $this;       
        }
        
        /**
         * Send POST request
         * @param string - string post params 
         * return object
         */
        public function post($params){
            $this->options_query = array(
                                         CURLOPT_POST => true,
                                         CURLOPT_POSTFIELDS => $params  
                                         );      
            return $this;                               
        }        
        
        /**
         * change encoding   
         * return object
         */ 
        public function changeCharset($beforeCharset, $afterCharset){
            $this->content = iconv($beforeCharset,$afterCharset, $this->content);
            return $this;
        }
        
        /**
         * Returns the content
         * return string
         */
        public function getContent(){
            if($this->url == null)
                throw new Exception('&#1053;&#1077; &#1091;&#1082;&#1072;&#1079;&#1072;&#1085; URL &#1076;&#1086;&#1085;&#1086;&#1088;&#1072;');
            
            return $this->content;
        }

        /**
         * parses content
         * @param string full string pattern regexp
         * @param bool preg_match_all() - default / false - preg_match()
         * return array / bool
         */ 
        public function parse($regexp, $all = true){
            if($this->url == '')
                return false;            
            
            if($all === true){
                preg_match_all($regexp, $this->content, $res);
                return $res;
            }
            elseif($all === false){
                preg_match($regexp, $this->content, $res);
                return $res;
            }
            else
                return false;
        }              
    }
    /************************************* U S E *************************************************************************
    //Simple option parsing
    $parser = new Parser;
    $parser->url = 'http://phpforum.ru';
    $parser->init();
    $res = $parser->parse("#<title>(.*)</title>#i");

   
    //Change encoding donor site content
    $parser = new Parser;
    $parser->url = 'http://phpforum.ru';
    $parser->init();
    $parser->changeCharset('windows-1251','utf-8');
    $res = $parser->parse("#<title>(.*)</title>#iu");    
    
    //use of cookies
    $parser = new Parser;
    $parser->url = 'http://phpforum.ru';
    $parser->setCookie('mycookie.txt');
    $parser->init();
    $res = $parser->parse("#<title>(.*)</title>#i");
  
    //Send POST request
    $parser = new Parser;
    $parser->url = 'http://phpforum.ru';
    $parser->post('?pass=yourpassword&login=yourlogin');
    $parser->init();
    $res = $parser->parse("#<title>(.*)</title>#iu"); 
    
    //Use SSL certificate
    $parser = new Parser;
    $parser->url = 'http://phpforum.ru';
    $parser->useSSL('cert.crt','pass');
    $parser->init();
    $res = $parser->parse("#<title>(.*)</title>#iu");    
    
    //Get the entire content of the donor
  
    $parser = new Parser;
    $parser->url = 'http://phpforum.ru';
    $parser->init();
    $res = $parser->getContent();
    
    //Additional settings: USERAGENT, FOLLOWLOCATION &#1080; &#1090;.&#1076;.
    //You can set - addressing public class properties
    //$parser->useragent = 'Opera';
    //$parser->followlocation = false;
    //$parser->timeout = 25;  
    //$parser->returndata = false;      
    //$parser->referer = 'http://google.com/bot/';     
    //&#1080; &#1090;.&#1076;.
    **************************************************************************************************************/

?>
