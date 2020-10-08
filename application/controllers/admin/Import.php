<?php

header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

class Import extends AdminController {

    public function __construct() {
        parent::__construct();
        $this->load->model('leads_model');
    }

    public function index() {

        $dbFields = array('DEVELOPER', 'COMMUNITY', 'SUB COMMUNITY', 'PROPERTY NUMBER', 'PROPERTY TYPE', 'PROPERTY SIZE (SQFT)', 'PROPERTY SIZE (SQM)', 'BEDROOM/S', 'BATHROOM/S', 'PARKING', 'OTHERS',
            'CLIENT TYPE', 'SALUTAION', 'FIRST NAME', 'LAST NAME', 'FULL NAME', 'PARTNER NAME', 'E-MAIL', 'E-MAIL 2', 'E-MAIL 3', 'MAIN PHONE NUMBER', 'PHONE NUMBER 1', 'PHONE NUMBER 2', 'PHONE NUMBER 3', 
            'PHONE NUMBER 4', 'SKYPE ID', 'LANDLINE', 'FAX', 'ADDRESS', 'P.O BOX', 'NATIONALITY', 'DATE OF BIRTH', 'PASSPORT NUMBER', 'PASSPORT EXIPRATION DATE');

        $this->load->library('import/Bulk_import', [], 'import');

        $this->import->setDatabaseFields($dbFields);


        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if ($this->input->post() && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
            $this->import->setSimulation($this->input->post('simulate'))
                    ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                    ->setFilename($_FILES['file_csv']['name'])
                    ->perform();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources'] = $this->leads_model->get_source();

        $data['title'] = _l('import');
        $this->load->view('admin/import/bulk-import', $data);
    }

}