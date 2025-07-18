<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->auth();
    }

    public function auth()
    {
    	if ($this->uri->segment(1) == 'home' || empty($this->uri->segment(1)) || $this->uri->segment(2) == 'login') {
    		if ($this->session->userdata('id')) {
    			if ($this->session->userdata('level') == '1') {
    				return redirect(base_url('admin'));
    			} else if ($this->session->userdata('level') == '2') {
    				return redirect(base_url('dosen'));
    			} else if ($this->session->userdata('level') == '3') {
    				return redirect(base_url('mahasiswa'));
    			}
    		}
    	}
    	if ($this->uri->segment(1) == 'admin') {
    		if (empty($this->session->userdata('id'))) {
    			return redirect(base_url('home'));
    		} else {
    			if ($this->session->userdata('level') == '2') {
    				return redirect(base_url('dosen'));
    			} else if ($this->session->userdata('level') == '3') {
    				return redirect(base_url('mahasiswa'));
    			}
    		}
    	}
    	if ($this->uri->segment(1) == 'dosen') {
    		if (empty($this->session->userdata('id'))) {
    			return redirect(base_url('home'));
    		} else {
    			if ($this->session->userdata('level') == '1') {
    				return redirect(base_url('admin'));
    			} else if ($this->session->userdata('level') == '3') {
    				return redirect(base_url('mahasiswa'));
    			}
    		}
    	}
    	if ($this->uri->segment(1) == 'mahasiswa') {
    		if (empty($this->session->userdata('id'))) {
    			return redirect(base_url('home'));
    		} else {
    			if ($this->session->userdata('level') == '2') {
    				return redirect(base_url('dosen'));
    			} else if ($this->session->userdata('level') == '1') {
    				return redirect(base_url('admin'));
    			}
    		}
    	}
    }

}

/* End of file MY_Controller.php */
