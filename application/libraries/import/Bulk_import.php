<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/import/App_import.php');

class Bulk_import extends App_import {

    private $uniqueValidationFields = [];
//    protected $notImportableFields = [];
    protected $requiredFields = ['name'];

    public function __construct() {
//        $this->notImportableFields = hooks()->apply_filters('not_importable_leads_fields', ['id', 'source', 'assigned', 'status', 'dateadded', 'last_status_change', 'addedfrom', 'leadorder', 'date_converted', 'lost', 'junk', 'is_imported_from_email_integration', 'email_integration_uid', 'is_public', 'dateassigned', 'client_id', 'lastcontact', 'last_lead_status', 'from_form_id', 'default_language', 'hash']);

        $uniqueValidationFields = json_decode(get_option('lead_unique_validation'));
        if (count($uniqueValidationFields) > 0) {
            $this->uniqueValidationFields = $uniqueValidationFields;
            $message = '';
            foreach ($uniqueValidationFields as $key => $field) {
                if ($key === 0) {
                    $message .= 'Based on your leads <b class="text-danger">unique validation</b> configured <a href="' . admin_url('settings?group=leads#unique_validation_wrapper') . '" target="_blank">options</a>, the lead <b>won\'t</b> be imported if:<br />';
                }

                $message .= '<br />&nbsp;&nbsp;&nbsp; - Lead <b>' . $field . '</b> already exists OR';
            }

            if ($message != '') {
                $message = substr($message, 0, -3);
            }

            $message .= '<br /><br />If you still want to import all leads, uncheck all unique validation field';

            $this->addImportGuidelinesInfo($message);
        }

        parent::__construct();
    }

    public function perform() {
        $this->initialize();

        $dbFieldsLeads = array('category', 'salutation', 'first_name', 'last_name', 'name', 'partner_name', 'email', 'email2', 'email3',
            'phonenumber', 'phone1', 'phone2', 'phone3', 'phone4', 'skypeid', 'landline', 'fax', 'address', 'po_box', 'nationality', 'birthdate', 'passportnumber', 'exipre');

        $dbFieldsProp = array("Developer", "Communities", "Sub_Communities", "Property_No", "Property_Type", "Property_Size_SQFT", "Property_Size_SQM", "Bedrooms", "Bathroom", "Barking", "Others");

        $fileFields = array("Developer", "Communities", "Sub_Communities", "Property_No", "Property_Type", "Property_Size_SQFT", "Property_Size_SQM", "Bedrooms", "Bathroom", "Barking", "Others",
            'category', 'salutation', 'first_name', 'last_name', 'name', 'partner_name', 'email', 'email2', 'email3',
            'phonenumber', 'phone1', 'phone2', 'phone3', 'phone4', 'skypeid', 'landline', 'fax', 'address', 'po_box', 'nationality', 'birthdate', 'passportnumber', 'exipre');

        $totalDatabaseFields = count($fileFields);
        foreach ($this->getRows() as $rowNumber => $row) {
            $insertLeads = [];
            $insertProp = [];
            for ($i = 0; $i < $totalDatabaseFields; $i++) {
                $row[$i] = $this->checkNullValueAddedByUser($row[$i]);
                if ($fileFields[$i] == 'name' && empty($row[$i])) {
                    $row[$i] = '/';
                }
                if (in_array($fileFields[$i], $dbFieldsLeads)) {
                    $insertLeads[$fileFields[$i]] = $row[$i];
                } else if (in_array($fileFields[$i], $dbFieldsProp)) {
                    $insertProp[$fileFields[$i]] = $row[$i];
                }
            }

            $insertLeads = $this->trimInsertValues($insertLeads);
            if (count($insertLeads) > 0) {
                $this->incrementImported();
                $id = null;
                if (!$this->isSimulation()) {
                    if (!isset($insertLeads['dateadded'])) {
                        $insertLeads['dateadded'] = date('Y-m-d H:i:s');
                    }
                    if (!isset($insertLeads['addedfrom'])) {
                        $insertLeads['addedfrom'] = get_staff_user_id();
                    }
                    $insertLeads['category'] = $this->ci->input->post('status');
                    $insertLeads['source'] = $this->ci->input->post('source');
                    if ($this->ci->input->post('responsible')) {
                        $insertLeads['assigned'] = $this->ci->input->post('responsible');
                    }
                    $this->ci->db->insert(db_prefix() . 'leads', $insertLeads);
                    $id = $this->ci->db->insert_id();
                    $propLeadID = $id;
                } else {
                    $this->simulationData[$rowNumber] = $this->formatValuesForSimulation($insertLeads);
                }
                $this->handleCustomFieldsInsert($id, $row, $i, $rowNumber, 'leads');
            }

            $insertProp['property_lead_id'] = $propLeadID;
            $insertProp = $this->trimInsertValues($insertProp);
            if (count($insertProp) > 0) {
                //$this->incrementImported();
                $id = null;
                if (!$this->isSimulation()) {
                    $this->ci->db->insert(db_prefix() . 'properties', $insertProp);
                    $id = $this->ci->db->insert_id();
                } else {
                    $this->simulationData[$rowNumber] = $this->formatValuesForSimulation($insertLeads);
                }
                $this->handleCustomFieldsInsert($id, $row, $i, $rowNumber, 'properties');
            }
            if ($this->isSimulation() && $rowNumber >= $this->maxSimulationRows) {
                break;
            }
        }
    }

    protected function tags_formatSampleData() {
        return 'tag1,tag2';
    }

    public function formatFieldNameForHeading($field) {
        if (strtolower($field) == 'title') {
            return 'Position';
        }

        return parent::formatFieldNameForHeading($field);
    }

    protected function email_formatSampleData() {
        return uniqid() . '@example.com';
    }

    protected function failureRedirectURL() {
        return admin_url('leads/import');
    }

    private function isDuplicateLead($data) {
        foreach ($this->uniqueValidationFields as $field) {
            if ((isset($data[$field]) && $data[$field] != '') && total_rows(db_prefix() . 'leads', [$field => $data[$field]]) > 0) {
                return true;
            }
        }

        return false;
    }

    private function formatValuesForSimulation($values) {
        foreach ($values as $column => $val) {
            if ($column == 'country' && !empty($val) && is_numeric($val)) {
                $country = $this->getCountry(null, $val);
                if ($country) {
                    $values[$column] = $country->short_name;
                }
            }
        }

        return $values;
    }

    private function getCountry($search = null, $id = null) {
        if ($search) {
            $this->ci->db->where('iso2', $search);
            $this->ci->db->or_where('short_name', $search);
            $this->ci->db->or_where('long_name', $search);
        } else {
            $this->ci->db->where('nationality', $id);
        }

        return $this->ci->db->get(db_prefix() . 'countries')->row();
    }

    private function countryValue($value) {
        if ($value != '') {
            if (!is_numeric($value)) {
                $country = $this->getCountry($value);
                $value = $country ? $country->country_id : 0;
            }
        } else {
            $value = 0;
        }

        return $value;
    }

}