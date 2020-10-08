<?php

header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

class Portals extends AdminController {

    public function __construct() {
        parent::__construct();
        $this->load->model('portals_model');
    }

    public function index() {
        close_setup_menu();

        if (!is_staff_member()) {
            access_denied('Portals');
        }

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }
        $data['portals'] = $this->portals_model->get_portals();
        $data['title'] = "Portals";
        $this->load->view('admin/portals/manage_portals', $data);
    }

    public function addPortal() {
        if ($this->input->post()) {
            $config['upload_path'] = './uploads/portals/';
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size'] = 4096;
            $config['file_name'] = rand() . "_" . $_FILES['portal_image']['name'];

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('portal_image')) {
                echo "Something went wrong, Please try again.";
            } else {
                $upload_data = $this->upload->data();

                $inertData = array(
                    $this->input->post("newPortal_name"),
                    $this->input->post("newPortal_website"),
                    $this->input->post("newPortal_status"),
                    $this->input->post("newPortal_cron_scyn"),
                    $upload_data['file_name']
                );
                if ($this->portals_model->insert_portal($inertData)) {
                    $this->getPortals();
                } else {
                    echo "Something went wrong, Please try again.";
                }
            }
        }
    }

    public function deletePortal() {
        if ($this->input->post()) {
            $id = $this->input->POST('id');
            if ($this->db->delete(db_prefix() . 'portals', array('id' => $id))) {
                $this->getPortals();
            } else {
                echo 'Something wrong happened, try again.';
            }
        }
    }

    protected function getPortals() {
        $portals = $this->portals_model->get_portals();
        foreach ($portals as $portal) {
            echo '<tr id="portal_' . $portal['id'] . '">
                            <td><img width="50px;" height="50px;" class="img img-rounded" src="' . base_url("uploads/portals/" . $portal['P_image']) . '"/></td>
                            <td>' . $portal['P_ref_id'] . '</td>
                            <td>' . $portal['P_name'] . '</td>
                            <td>' . $portal['P_website'] . '</td>
                            <td>' . $portal['P_status'] . '</td>
                            <td>' . $portal['P_xml_url'] . '</td>
                            <td>' . $portal['P_cronjob_scyn'] . '</td>
                            <td>' . date("d F Y", strtotime($portal['P_created_at'])) . '</td>
                        <td>
                        <a href="javascript::();" onclick="portal_action(' . $portal['id'] . ', ' . "'edit'" . ');" data-name="' . $portal['P_name'] . '" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                        <a href="javascript::();" onclick="portal_action(' . $portal['id'] . ', ' . "'delete'" . ');" class="btn btn-danger btn-icon"><i class="fa fa-remove"></i></a>
                        </td>
                        </tr>';
        }
    }

}
