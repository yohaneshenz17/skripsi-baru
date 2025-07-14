<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class App_lib
{
    protected $ci;

    protected $layout;
    
    protected $section = [];

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function url($key = null)
    {
        $url = explode('/', uri_string());
        for ($i=0; $i < 9; $i++) { 
            $link[] = ($url[$i]) ? $url[$i] : "";
        }
        $this->url = $link;

        if ($key) {
            return $this->url[$i];
        }
    }

    public function validate($data)
    {
        foreach ($data as $key => $item) {
            $field = str_replace("_", " ", $key);
            if (empty($item)) {
                $hasil = [
                    'error' => true,
                    'message' => "parameter '$field' harus diset"
                ];
                goto output;
            }
        }

        $hasil = true;

        output:
        return $hasil;
    }

    public function extend($layout)
    {
        $this->layout = $layout;
    }

    public function setVar($variable, $value)
    {
        $this->section[$variable] = $value;
    }

    public function section()
    {
        ob_start();
        ob_flush();
    }

    public function endSection($section)
    {
        $this->section[$section] = ob_get_clean();
        ob_clean();
    }
    
    public function init()
    {
        $data = $this->section;
        $this->ci->load->view($this->layout, $data);
    }

    public function auth($level = null)
    {
        if ($level) {
            if ($level == '1') {
                return redirect(base_url('admin/dashboard'));
            } else if ($level == '2') {
                return redirect(base_url('dosen/dashboard'));
            } else if ($level == '3') {
                return redirect(base_url('mahasiswa/dashboard'));
            } else {
                return redirect(base_url('home'));
            }
        }
    }

}

/* End of file App_lib.php */
