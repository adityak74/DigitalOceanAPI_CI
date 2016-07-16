<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$resp = $this->digitalocean_api->list_dns_records();
		echo $resp;
	}

	/*
		Params
		@data array(
		"type"=>"A/AAAA/CNAME/MX",
		"name"=>"domain/website name",
		"data"=>"IP/@/Other data",  
		"priority"=>null/number, 
		"port"=>null, 
		"weight"=>null)
	*/
	public function create(){
		$data = array("type"=>"A","name"=>"sub.example.com.","data"=>"127.0.0.1",  "priority"=>null, "port"=>null, "weight"=>null);
		$resp = $this->digitalocean_api->create_dns_record($data);
		echo $resp;
	}

	public function delete($id){
		if($id!=NULL){
			$resp = $this->digitalocean_api->delete_dns_record($id);
			echo $resp;
		}
	}

	public function get($id){
		if($id!=NULL){
			$resp = $this->digitalocean_api->get_dns_record($id);
			echo $resp;
		}
	}

	/*
		Params
		@data array(
		"type"=>"A/AAAA/CNAME/MX",
		"name"=>"domain/website name",
		"data"=>"IP/@/Other data",  
		"priority"=>null/number, 
		"port"=>null, 
		"weight"=>null)
	*/
	public function update($id){
		if($id!=NULL){
			$data = array("name"=>"example");
			$resp = $this->digitalocean_api->update_dns_record($id, $data);
			echo $resp;
		}
	}

}
