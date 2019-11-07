<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{

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

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('ion_auth', 'form_validation', 'session');
        $this->load->helper('url', 'language');
        $this->load->model('Home_model');
    }

    public function index()
    {
        // Security check if the user is authorize
        if (cek_login_bol()) {
            redirect('vote', 'refresh');
        }

        $data['title'] = 'E-Voting';
        $data['action'] = site_url('user/userAuth/login');

        $this->load->view('front/main', $data);
    }

    public function vote()
    {
        // Security check if the user is authorize
        if (!cek_login_bol()) {
            redirect('user/userAuth', 'refresh');
        }

        // Get All Kandidat
        $kandidat_data = $this->Home_model->get_all('nourut', 'kandidat', 'ASC');

        $data = array(

            // Data kandidat diambil dari database
            'kandidat_data' => $kandidat_data,

        );

        // Check status sudah memilih atau belum
        $status = $this->session->userdata('status');
        if ($status === 'Belum Memilih') {
            $this->load->view('front/vote', $data);
        } elseif ($status === 'Sudah Memilih') {

            $data = array(
                'nama' => $this->session->userdata('nama'),
            );

            $this->load->view('front/terimakasih', $data);
        }
    }

    public function doVote($idkandidat)
    {
        // Security check if the user is authorize
        if (!cek_login_bol()) {
            redirect('user/userAuth', 'refresh');
        }

        // menetapkan idpemilih
        $idpemilih = $this->session->userdata('userid');
        // Tipe pemilih apakah guru atau siswa
        $tipe = $this->session->userdata('level');

        // Check status sudah memilih atau belum
        $status = $this->session->userdata('status');
        if ($status === 'Belum Memilih') {

            // insertData
            $insertData = array(
                'tipe' => $tipe,
                'idpemilih' => $idpemilih,
                'idkandidat' => $idkandidat,
            );

            // Insert data
            $this->Home_model->insert('data_pemilihan', $insertData);

            // Update Session data
            $userData = array(
                'status' => 'Sudah Memilih'
            );
            $this->session->set_userdata($userData);

            // Updare Database data
            $updateData = array(
                'status' => 'Sudah Memilih'
            );
            $this->Home_model->update('id', $idpemilih, 'data_pemilih', $updateData);

            redirect('vote', 'refresh');
        } else {
            redirect('home', 'refresh');
            $this->session->set_flashdata(
                'message',
                '<div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                Anda sudah memilih </div>',
            );
        }
    }
}

/* End of file home.php */
