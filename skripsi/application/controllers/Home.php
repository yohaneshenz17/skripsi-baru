<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
    }

    public function index()
    {
        $template = $this->db->get('home_template')->result();

        // Head
        $data['page_title'] = '';

        // Carousel
        $data['carousel_bg1'] = '';
        $data['carousel_subtitle1'] = '';
        $data['carousel_title1'] = '';
        $data['carousel_description1'] = '';
        $data['carousel_btn_href1'] = '';
        $data['carousel_btn_text1'] = '';

        $data['carousel_bg2'] = '';
        $data['carousel_subtitle2'] = '';
        $data['carousel_title2'] = '';
        $data['carousel_description2'] = '';
        $data['carousel_btn_href2'] = '';
        $data['carousel_btn_text2'] = '';

        $data['carousel_bg3'] = '';
        $data['carousel_subtitle3'] = '';
        $data['carousel_title3'] = '';
        $data['carousel_description3'] = '';
        $data['carousel_btn_href3'] = '';
        $data['carousel_btn_text3'] = '';
        // End Carousel

        // Tentang Kami
        $data['tentang_kami_subtitle'] = '';
        $data['tentang_kami_isi'] = '';
        // End Tentang Kami

        // Kontak
        $data['social_description'] = '';
        $data['link_fb'] = '';
        $data['link_twitter'] = '';
        $data['alamat'] = '';
        $data['phone'] = '';
        $data['email'] = '';
        $data['kotak_subtitle'] = '';
        // End Kontak

        foreach ($template as $item) {
            // Head
            $data['page_title'] = $item->page_title;

            // Carousel
            $data['carousel_bg1'] = $item->carousel_bg1;
            $data['carousel_subtitle1'] = $item->carousel_subtitle1;
            $data['carousel_title1'] = $item->carousel_title1;
            $data['carousel_description1'] = $item->carousel_description1;
            $data['carousel_btn_href1'] = $item->carousel_btn_href1;
            $data['carousel_btn_text1'] = $item->carousel_btn_text1;

            $data['carousel_bg2'] = $item->carousel_bg2;
            $data['carousel_subtitle2'] = $item->carousel_subtitle2;
            $data['carousel_title2'] = $item->carousel_title2;
            $data['carousel_description2'] = $item->carousel_description2;
            $data['carousel_btn_href2'] = $item->carousel_btn_href2;
            $data['carousel_btn_text2'] = $item->carousel_btn_text2;

            $data['carousel_bg3'] = $item->carousel_bg3;
            $data['carousel_subtitle3'] = $item->carousel_subtitle3;
            $data['carousel_title3'] = $item->carousel_title3;
            $data['carousel_description3'] = $item->carousel_description3;
            $data['carousel_btn_href3'] = $item->carousel_btn_href3;
            $data['carousel_btn_text3'] = $item->carousel_btn_text3;
            // End Carousel

            // Tentang Kami
            $data['tentang_kami_subtitle'] = $item->tentang_kami_subtitle;
            $data['tentang_kami_isi'] = $item->tentang_kami_isi;
            // End Tentang Kami

            // Kontak
            $data['social_description'] = $item->social_description;
            $data['link_fb'] = $item->link_fb;
            $data['link_twitter'] = $item->link_twitter;
            $data['alamat'] = $item->alamat;
            $data['phone'] = $item->phone;
            $data['email'] = $item->email;
            $data['kontak_subtitle'] = $item->kontak_subtitle;
            // End Kontak
        }
        $this->load->view('home/index', $data);
    }

    public function registrasi()
    {
        return $this->load->view('home/registrasi');
    }

    public function cek()
    {
        return $this->load->view('home/cek');
    }

    public function home_template()
    {
        $template = $this->db->get('home_template')->result();

        // Head
        $data['page_title'] = '';
        $data['id'] = '';

        // Carousel
        $data['carousel_bg1'] = '';
        $data['carousel_subtitle1'] = '';
        $data['carousel_title1'] = '';
        $data['carousel_description1'] = '';
        $data['carousel_btn_href1'] = '';
        $data['carousel_btn_text1'] = '';

        $data['carousel_bg2'] = '';
        $data['carousel_subtitle2'] = '';
        $data['carousel_title2'] = '';
        $data['carousel_description2'] = '';
        $data['carousel_btn_href2'] = '';
        $data['carousel_btn_text2'] = '';

        $data['carousel_bg3'] = '';
        $data['carousel_subtitle3'] = '';
        $data['carousel_title3'] = '';
        $data['carousel_description3'] = '';
        $data['carousel_btn_href3'] = '';
        $data['carousel_btn_text3'] = '';
        // End Carousel

        // Tentang Kami
        $data['tentang_kami_subtitle'] = '';
        $data['tentang_kami_isi'] = '';
        // End Tentang Kami

        // Kontak
        $data['social_description'] = '';
        $data['link_fb'] = '';
        $data['link_twitter'] = '';
        $data['alamat'] = '';
        $data['phone'] = '';
        $data['email'] = '';
        $data['kotak_subtitle'] = '';
        // End Kontak

        foreach ($template as $item) {
            // Head
            $data['page_title'] = $item->page_title;
            $data['id'] = $item->id;

            // Carousel
            $data['carousel_bg1'] = $item->carousel_bg1;
            $data['carousel_subtitle1'] = $item->carousel_subtitle1;
            $data['carousel_title1'] = $item->carousel_title1;
            $data['carousel_description1'] = $item->carousel_description1;
            $data['carousel_btn_href1'] = $item->carousel_btn_href1;
            $data['carousel_btn_text1'] = $item->carousel_btn_text1;

            $data['carousel_bg2'] = $item->carousel_bg2;
            $data['carousel_subtitle2'] = $item->carousel_subtitle2;
            $data['carousel_title2'] = $item->carousel_title2;
            $data['carousel_description2'] = $item->carousel_description2;
            $data['carousel_btn_href2'] = $item->carousel_btn_href2;
            $data['carousel_btn_text2'] = $item->carousel_btn_text2;

            $data['carousel_bg3'] = $item->carousel_bg3;
            $data['carousel_subtitle3'] = $item->carousel_subtitle3;
            $data['carousel_title3'] = $item->carousel_title3;
            $data['carousel_description3'] = $item->carousel_description3;
            $data['carousel_btn_href3'] = $item->carousel_btn_href3;
            $data['carousel_btn_text3'] = $item->carousel_btn_text3;
            // End Carousel

            // Tentang Kami
            $data['tentang_kami_subtitle'] = $item->tentang_kami_subtitle;
            $data['tentang_kami_isi'] = $item->tentang_kami_isi;
            // End Tentang Kami

            // Kontak
            $data['social_description'] = $item->social_description;
            $data['link_fb'] = $item->link_fb;
            $data['link_twitter'] = $item->link_twitter;
            $data['alamat'] = $item->alamat;
            $data['phone'] = $item->phone;
            $data['email'] = $item->email;
            $data['kontak_subtitle'] = $item->kontak_subtitle;
            // End Kontak
        }
        $this->load->view('admin/home', $data);
    }

    public function update_home_template()
    {
        // Head
        $page_title = $this->input->post('page_title');

        // Carousel
        $def_carousel_bg1 = $this->input->post('def_carousel_bg1');
        $def_carousel_bg2 = $this->input->post('def_carousel_bg2');
        $def_carousel_bg3 = $this->input->post('def_carousel_bg3');
        $carousel_bg1 = $_FILES['carousel_bg1']['name'];
        $carousel_bg2 = $_FILES['carousel_bg2']['name'];
        $carousel_bg3 = $_FILES['carousel_bg3']['name'];
        $carousel_subtitle1 = $this->input->post('carousel_subtitle1');
        $carousel_subtitle2 = $this->input->post('carousel_subtitle2');
        $carousel_subtitle3 = $this->input->post('carousel_subtitle3');
        $carousel_title1 = $this->input->post('carousel_title1');
        $carousel_title2 = $this->input->post('carousel_title2');
        $carousel_title3 = $this->input->post('carousel_title3');
        $carousel_description1 = $this->input->post('carousel_description1');
        $carousel_description2 = $this->input->post('carousel_description2');
        $carousel_description3 = $this->input->post('carousel_description3');

        // Tentang Kami
        $tentang_kami_subtitle = $this->input->post('tentang_kami_subtitle');
        $tentang_kami_isi = $this->input->post('tentang_kami_isi');

        // Kontak
        $kontak_subtitle = $this->input->post('kontak_subtitle');
        $social_description = $this->input->post('social_description');
        $link_fb = $this->input->post('link_fb');
        $link_twitter = $this->input->post('link_twitter');
        $alamat = $this->input->post('alamat');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');

        $dataUpdate = array(
            'page_title' => $page_title,
            'carousel_subtitle1' => $carousel_subtitle1,
            'carousel_subtitle2' => $carousel_subtitle2,
            'carousel_subtitle3' => $carousel_subtitle3,
            'carousel_title1' => $carousel_title1,
            'carousel_title2' => $carousel_title2,
            'carousel_title3' => $carousel_title3,
            'carousel_description1' => $carousel_description1,
            'carousel_description2' => $carousel_description2,
            'carousel_description3' => $carousel_description3,
            'tentang_kami_subtitle' => $tentang_kami_subtitle,
            'tentang_kami_isi' => $tentang_kami_isi,
            'tentang_kami_subtitle' => $tentang_kami_subtitle,
            'kontak_subtitle' => $kontak_subtitle,
            'social_description' => $social_description,
            'link_fb' => $link_fb,
            'link_twitter' => $link_twitter,
            'alamat' => $alamat,
            'phone' => $phone,
            'email' => $email,
        );

        $config['upload_path']          = './assets/essence/img/slider/';
        $config['allowed_types']        = 'jpg|png';

        $this->load->library('upload', $config);

        if ($carousel_bg1 == null || $carousel_bg1 == "") {
        } else {
            $path = './assets/essence/img/slider/' . $def_carousel_bg1;
            unlink($path);
            if (!$this->upload->do_upload('carousel_bg1')) {
                echo json_encode($this->upload->display_errors());
            } else {
                $data = $this->upload->data();
                $dataUpdate['carousel_bg1'] = $data['file_name'];
            }
        }

        if ($carousel_bg2 == null || $carousel_bg2 == "") {
        } else {
            $path = './assets/essence/img/slider/' . $def_carousel_bg2;
            unlink($path);
            if (!$this->upload->do_upload('carousel_bg2')) {
                echo json_encode($this->upload->display_errors());
            } else {
                $data = $this->upload->data();
                $dataUpdate['carousel_bg2'] = $data['file_name'];
            }
        }

        if ($carousel_bg3 == null || $carousel_bg3 == "") {
        } else {
            $path = './assets/essence/img/slider/' . $def_carousel_bg3;
            unlink($path);
            if (!$this->upload->do_upload('carousel_bg3')) {
                echo json_encode($this->upload->display_errors());
            } else {
                $data = $this->upload->data();
                $dataUpdate['carousel_bg3'] = $data['file_name'];
            }
        }

        $this->db->where('id', $this->input->post('id'));
        if ($this->db->update('home_template', $dataUpdate)) {
            $this->session->set_flashdata('succ_update', 'true');
            redirect(base_url('home-template'), 'refresh');
        } else {
            $this->session->set_flashdata('err_update', 'true');
            redirect(base_url('home-template'), 'refresh');
        }
    }
}

/* End of file Home.php */
