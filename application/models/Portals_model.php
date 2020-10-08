<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Portals_model extends App_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_portals() {
        return $this->db->get(db_prefix() . 'portals')->result_array();
    }

    public function insert_portal($newPortal) {
        $insertData = array(
            "P_name" => $newPortal[0],
            "P_website" => $newPortal[1],
            "P_status" => $newPortal[2],
            "P_xml_url" => "xml/prxlfeeds/" . mb_substr($newPortal[0], 0, 2) . rand(1000000000, 9999999999) . ".xml",
            "P_cronjob_scyn" => $newPortal[3],
            "P_created_at" => date("Y-m-d H:i:s"),
            "P_modified_at" => NULL,
            "P_added_by" => get_staff_user_id(),
            "P_image" => $newPortal[4]
        );

        if ($this->db->insert(db_prefix() . "portals", $insertData)) {
            $lastID = $this->db->insert_id();
            $updateData = array(
                "P_ref_id" => "PRT" . sprintf('%03d', $lastID)
            );
            $this->db->where("id", $lastID);
            if ($this->db->update(db_prefix() . "portals", $updateData)) {
                log_activity('New Portal Inerted [Portal name: ' . $newPortal[0] . ', IP: ' . $this->input->ip_address() . ']');
                return true;
            } else {
                $this->db->where("id", $lastID);
                $this->db->delete(db_prefix() . "portals");
                log_activity('Failed Inserting Portal [Portal name: ' . $newPortal[0] . ', IP: ' . $this->input->ip_address() . ']');
                return false;
            }
        } else {
            return false;
        }
    }

    

}
