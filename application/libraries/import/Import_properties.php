<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/import/App_import.php');

class Import_properties extends App_import {

    private $uniqueValidationFields = [];
    protected $notImportableFields = [];
    protected $requiredFields = ['name'];

    public function __construct() {
        $this->notImportableFields = hooks()->apply_filters('not_importable_properties_fields', ['id', 'source', 'assigned', 'status', 'last_status_change', 'addedfrom', 'leadorder', 'date_converted', 'lost', 'junk', 'is_imported_from_email_integration', 'email_integration_uid', 'is_public', 'dateassigned', 'client_id', 'lastcontact', 'last_lead_status', 'from_form_id', 'default_language', 'hash']);

        $uniqueValidationFields = json_decode(get_option('property_unique_validation'));
        if (count($uniqueValidationFields) > 0) {
            $this->uniqueValidationFields = $uniqueValidationFields;
            $message = '';
            foreach ($uniqueValidationFields as $key => $field) {
                if ($key === 0) {
                    $message .= 'Based on your properties <b class="text-danger">unique validation</b> configured <a href="' . admin_url('settings?group=properties#unique_validation_wrapper') . '" target="_blank">options</a>, the lead <b>won\'t</b> be imported if:<br />';
                }

                $message .= '<br />&nbsp;&nbsp;&nbsp; - property <b>' . $field . '</b> already exists OR';
            }

            if ($message != '') {
                $message = substr($message, 0, -3);
            }

            $message .= '<br /><br />If you still want to import all properties, uncheck all unique validation field';

            $this->addImportGuidelinesInfo($message);
        }

        parent::__construct();
    }

    public function perform() {
        $this->initialize();

        $databaseFields = array("Property_Ref_No", "Property_No", "Property_Status", "Transaction_Number", "Permit_Number", "Property_purpose", "Property_Type", "City", "Locality", "Sub_Locality", "Tower_Name", "Property_Title", "Property_Description", "Property_Size", "Property_Size_Unit", "Bedrooms", "Bathroom", "Barking", "Price", "Listing_Agent", "Listing_Agent_Phone", "Listing_Agent_Email", "Features", "Communities", "Sub_Communities", "Developer","Images", "Videos", "Floor_Plans", "Last_Updated", "Rent_Frequency", "Off_Plan", "featured_on_companywebsite", "Exclusive_Rights");
        


        $totalDatabaseFields = count($databaseFields);

        foreach ($this->getRows() as $rowNumber => $row) {
            $insert = [];
            for ($i = 0; $i < $totalDatabaseFields; $i++) {
                $row[$i] = $this->checkNullValueAddedByUser($row[$i]);
                $insert[$databaseFields[$i]] = $row[$i];
            }

            $insert = $this->trimInsertValues($insert);
            if (count($insert) > 0) {
                if ($this->isDuplicateLead($insert)) {
                    continue;
                }

                $this->incrementImported();

                $id = null;

                if (!$this->isSimulation()) {
                    $insert['dateadded'] = date('Y-m-d H:i:s');

                    $tags = '';
                    if (isset($insert['tags']) || is_null($insert['tags'])) {
                        if (!is_null($insert['tags'])) {
                            $tags = $insert['tags'];
                        }
                        unset($insert['tags']);
                    }

                    $this->ci->db->insert(db_prefix() . 'properties', $insert);
                    $id = $this->ci->db->insert_id();

                    if ($id) {
                        handle_tags_save($tags, $id, 'properties');
                    }
                } else {
                    $this->simulationData[$rowNumber] = $this->formatValuesForSimulation($insert);
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
        return admin_url('properties/import');
    }

    private function isDuplicateLead($data) {
        foreach ($this->uniqueValidationFields as $field) {
            if ((isset($data[$field]) && $data[$field] != '') && total_rows(db_prefix() . 'properties', [$field => $data[$field]]) > 0) {
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