<?php

defined('BASEPATH') or exit('No direct script access allowed');

class XmlFeederCreator extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function index() {
        $portals = $this->db->get(db_prefix() . 'portals')->result_array();
        foreach ($portals as $portal) {
            if ($portal['P_status'] == "ACTIVE") {
                $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><Properties></Properties>");

                $this->db->where("portal_id", $portal['id']);
                $portalProperties = $this->db->get(db_prefix() . "properties_portals");
                if ($portalProperties->num_rows() > 0) {
                    foreach ($portalProperties->result() as $portalProperty) {

                        $this->db->where("prop_id", $portalProperty->property_id);
                        $properties = $this->db->get(db_prefix() . "properties");
                        if ($properties->num_rows() > 0) {
                            foreach ($properties->result() as $property) {
                                $xmlProperty = $xml->addChild("Property");
                                
                                $xmlProperty->addChild("Property_Ref_No", "<![CDATA[ " . $property->Property_Ref_No . " ]]>");
                                $xmlProperty->addChild("Property_Status", "<![CDATA[ " . $property->Property_Status . " ]]>");
                                $xmlProperty->addChild("Transaction_Number", "<![CDATA[ " . $property->Transaction_Number . " ]]>");
                                $xmlProperty->addChild("Permit_Number", "<![CDATA[ " . $property->Permit_Number . " ]]>");
                                $xmlProperty->addChild("Property_purpose", "<![CDATA[ " . $property->Property_purpose . " ]]>");
                                $xmlProperty->addChild("Property_Type", "<![CDATA[ " . $property->Property_Type . " ]]>");
                                $xmlProperty->addChild("City", "<![CDATA[ " . $property->City . " ]]>");
                                $xmlProperty->addChild("Locality", "<![CDATA[ " . $property->Communities . " ]]>");
                                $xmlProperty->addChild("Tower_Name", "<![CDATA[ " . $property->Tower_Name . " ]]>");
                                $xmlProperty->addChild("Property_Title", "<![CDATA[ " . $property->Property_Title . " ]]>");
                                $xmlProperty->addChild("Property_Description", "<![CDATA[ " . $property->Property_Description . " ]]>");
                                $xmlProperty->addChild("Property_Size", "<![CDATA[ " . $property->Property_Size_SQFT . " ]]>");
                                $xmlProperty->addChild("Property_Size_Unit", "<![CDATA[ SQFT ]]>");
                                $xmlProperty->addChild("Bedrooms", "<![CDATA[ " . $property->Bedrooms . " ]]>");
                                $xmlProperty->addChild("Bathroom", "<![CDATA[ " . $property->Bathroom . " ]]>");
                                $xmlProperty->addChild("Price", "<![CDATA[ " . $property->Price . " ]]>");

                                $this->db->where("staffid", $property->assigned);
                                $this->db->limit(1);
                                foreach ($this->db->get(db_prefix() . "staff")->result() as $staff) {
                                    $xmlProperty->addChild("Listing_Agent", "<![CDATA[ " . $staff->firstname . ' ' . $staff->lastname . " ]]>");
                                    $xmlProperty->addChild("Listing_Agent_Phone", "<![CDATA[ " . $staff->phonenumber . " ]]>");
                                    $xmlProperty->addChild("Listing_Agent_Email", "<![CDATA[ " . $staff->email . " ]]>");
                                }

                                $xmlFeatures = $xmlProperty->addChild("Features");
                                $featuresArray = array("Central AC", "Central Heater", "Study room", "Balcony", "Private Pool", "Storage");
                                $features = explode("-", $property->Price);
                                for ($i = 0; $i < count($features); $i++) {
                                    if ($features[$i] == "TRUE") {
                                        $xmlFeatures->addChild("Feature", "<![CDATA[ " . $featuresArray[$i] . " ]]>");
                                    }
                                }

                                $xmlImages = $xmlProperty->addChild("Images");
                                $this->db->where("property_id", $property->prop_id);
                                $propertyImage = $this->db->get(db_prefix() . "properties_images");
                                foreach ($propertyImage->result() as $propImage) {
                                    $xmlImages->addChild("Feature", "<![CDATA[ " . base_url("uploads/properties/" . $propImage->image_name) . " ]]>");
                                }

                                $xmlVideos = $xmlProperty->addChild("Videos");
                                $this->db->where("property_id", $property->prop_id);
                                $propertyVideo = $this->db->get(db_prefix() . "properties_videos");
                                foreach ($propertyVideo->result() as $propVideo) {
                                    $xmlVideos->addChild("Feature", "<![CDATA[ " . base_url("uploads/properties/" . $propVideo->video_name) . " ]]>");
                                }

                                $xmlProperty->addChild("Floor_Plans", "<![CDATA[ " . $property->Floor_Plans . " ]]>");
                                $xmlProperty->addChild("Last_Updated", "<![CDATA[ " . date("Y-m-d H:i:s", strtotime($property->Last_Updated)) . " ]]>");
                                $xmlProperty->addChild("Rent_Frequency", "<![CDATA[ " . $property->Rent_Frequency . " ]]>");
                                $xmlProperty->addChild("Off_Plan", "<![CDATA[ " . $property->Off_Plan . " ]]>");
                                $xmlProperty->addChild("featured_on_companywebsite", "<![CDATA[ " . $property->featured_on_companywebsite . " ]]>");
                                $xmlProperty->addChild("Exclusive_Rights", "<![CDATA[ " . $property->Exclusive_Rights . " ]]>");
                            }
                        }
                    }
                }

                $xml->asXML('./' . $portal['P_xml_url']);
            } else {
                if (file_exists('./' . $portal['P_xml_url'])) {
                    rename('./' . $portal['P_xml_url'], str_replace(".xml", "", $portal['P_xml_url']) . '_old.xml');
                }
            }
        }
    }

}