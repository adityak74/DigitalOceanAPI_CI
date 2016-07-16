<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Digitalocean_api {

	protected $website;
	protected $auth_token;
	protected $curl;
	protected $endpoint = "https://api.digitalocean.com/v2/domains/";

	function __construct(){
		log_message('debug', "DigitalOcean API Initialized.");
		$this->_ci =& get_instance();
		$this->_ci->load->config('digitalocean_api');

		$this->website = (string) $this->_ci->config->item('website');
        $this->auth_token = (string) $this->_ci->config->item('auth_token');

        //Lets do some basic error handling to see if the configuration is A-OK.
        $temp_error_msg = '';
        if ($this->website === 'YOUR WEBSITE'){
            $temp_error_msg .= 'You need to set your website domain in the config file <br />';
        }
        
        if ($this->auth_token === 'YOUR AUTH TOKEN'){
            $temp_error_msg .= 'You need to set your auth token in the config file <br />';
        }
        
        if ($temp_error_msg != ''){
            show_error($temp_error_msg);
        }

	}

	public function __destruct() 
    {
        if(!is_null($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
    * @return array headers with Authentication tokens added 
    */
    private function build_curl_headers() 
    {
        $headers = array("Content-Type: application/json");
        if($this->auth_token) {
            $headers[] = "Authorization: Bearer $this->auth_token";
        }
        return $headers;        
    }

    /**
    * @param string $path
    * @return string adds the path to endpoint with.
    */
    private function build_api_call_url($path)
    {
        if (strpos($path, '/?') === false and strpos($path, '?') === false) {
            return $this->endpoint . $path . '/';
        }
        return $this->endpoint . $path;

    }

    /**
    * @param string $method ('GET', 'POST', 'DELETE', 'PATCH')
    * @param string $path whichever API path you want to target.
    * @param array $data contains the POST data to be sent to the API.
    * @return array decoded json returned by API.
    */
    private function api_call($method, $path, $data=null) 
    {
        $path = (string) $path;
        $method = (string) $method;
        //$data = (array) $data;
        $headers = $this->build_curl_headers();
        $request_url = $this-> build_api_call_url($path);

        $options = array();
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_RETURNTRANSFER] = true;
        
        if($method == 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = ($data);
        } else if($method == 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        } else if($method == 'PUT') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = ($data);
            $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        } else if ($method == 'GET' or $method == 'HEAD') {
        	$options[CURLOPT_POST] = 0;
            if (!empty($data)) {
                /* Update URL to container Query String of Paramaters */
                $request_url .= '?' . http_build_query($data);
            }
        }
        // $options[CURLOPT_VERBOSE] = true;
        $options[CURLOPT_URL] = $request_url;
        // $options[CURLOPT_HEADER] = 1;

        $this->curl = curl_init();
        $setopt = curl_setopt_array($this->curl, $options);
        $response = curl_exec($this->curl);
        $headers = curl_getinfo($this->curl);

        $error_number = curl_errno($this->curl);
        $error_message = curl_error($this->curl);
        $response_obj = json_decode($response, true);

        if($error_number != 0){
                throw new \Exception("Something went wrong. cURL raised an error with number: $error_number and message: $error_message." . PHP_EOL);
        }

        // if($response_obj->domain_records == false) {
        //     $message = json_encode($response_obj['message']);
        //     throw new \Exception($message . PHP_EOL);
        // }
        return $response_obj;
    }

    public function get_dns_record($recordID){
    	$response = $this->api_call('GET', $this->website."/records/" . $recordID , null);
    	return json_encode($response);
    }

    public function list_dns_records(){
    	$response = $this->api_call('GET', $this->website."/records" , null);
    	return json_encode($response);
    }

	public function create_dns_record($requestArray){
		if($requestArray!=null && is_array($requestArray)){
			$requestPayloadJSON = json_encode($requestArray);
			$response = $this->api_call('POST', $this->website."/records" , $requestPayloadJSON);
			return json_encode($response);
		}else{
			show_error("Invalid Request Array");
		}
	}

	public function delete_dns_record($recordID){
		if($recordID!=NULL){
			$response = $this->api_call('DELETE', $this->website."/records/" . $recordID , null);
			if($response==null)
				return json_encode(array("status"=>204,"message"=>"success"));
			else
				return json_encode($response);
		}else{
			show_error("Invalid Record ID");
		}
	}

	public function update_dns_record($recordID, $requestArray){
		if($recordID!=NULL && $requestArray!=NULL){
			$requestPayloadJSON = json_encode($requestArray);
			$response = $this->api_call('PUT', $this->website."/records/" . $recordID, $requestPayloadJSON);
			return json_encode($response);
		}else{
			show_error("Invalid Record ID or Update Data");
		}
	}

	public function getStatus($ch, $response){
	
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);

		$strs = explode(";", $header);
		$blocks = explode("\n",  $strs[0]);
		$chunks = explode(" ", $blocks[0]);

		$data = array("statusCode"=>$chunks[1], "status"=>$chunks[2]);

		return $data;
	}

}
