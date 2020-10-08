<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Properties extends AdminController {

    public function index() {
        /*  if (!has_permission('properties', '', 'view')) {
          if (!have_assigned_customers() && !has_permission('properties', '', 'create')) {
          access_denied('properties');
          }
          } */
        $data['title'] = "Properties";
        $this->load->view('admin/properties/properties', $data);
//        $this->load->helper("properties");
    }

#IMPORT PROPERTIES

    public function import() {
        $_FILES['file_csv']['name'];

        $dbFields = array("Property_Ref_No", "Property_No", "Property_Status", "Transaction_Number", "Permit_Number", "Property_purpose", "Property_Type", "City", "Locality", "Sub_Locality", "Tower_Name", "Property_Title", "Property_Description", "Property_Size", "Property_Size_Unit", "Bedrooms", "Bathroom", "Barking", "Price", "Listing_Agent", "Listing_Agent_Phone", "Listing_Agent_Email", "Features", "Communities", "Sub_Communities", "Developer", "Images", "Videos", "Floor_Plans", "Last_Updated", "Rent_Frequency", "Off_Plan", "featured_on_companywebsite", "Exclusive_Rights");

        array_push($dbFields, 'tags');
        $this->load->library('import/import_properties', [], 'import');
        $this->import->setDatabaseFields($dbFields)->setCustomFields(get_custom_fields('properties'));

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
        $data['statuses'] = $this->properties_model->get_status();
        $data['sources'] = $this->properties_model->get_source();
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['title'] = _l('import');
        $this->load->view('admin/properties/import', $data);
    }

#ADD PROPERTY

    public function add_property() {
        if ($_REQUEST['action'] && $_REQUEST['action'] == 'add_proper') {
            $property_ref_no = $this->input->POST('property_ref_no');
            $property_title = $this->input->POST('property_title');
            $price = $this->input->POST('property_price');
            $property_purpose = $this->input->POST('property_purpose');
            $property_type = $this->input->POST('property_type');
            $area_sqft = $this->input->POST('area_sqft');
            $beds = $this->input->POST('beds');
            $baths = $this->input->POST('baths');
            $location = $this->input->POST('location');
            $country = $this->input->POST('property_country');
            $city = $this->input->POST('city');
            $communites = $this->input->POST('communites');
            $sub_communites = $this->input->POST('sub_communites');
            $developer = $this->input->POST('developer');
            $assigned = $this->input->POST('assigned');
            $parking = $this->input->POST('parking');
            $description = $this->input->POST('description');
            $is_public = $this->input->POST('is_public');
// check if image return null
            if ($_FILES['image']['name'] != "") {
                $image = ''; //$this->Main_model->photo('blog_photo');
            } else {
                $image = '';
            }
            $data = array(
                'Property_Ref_No' => $property_ref_no,
                'Property_Status' => $is_public,
                'Transaction_Number' => '',
                'Permit_Number' => '',
                'Property_purpose' => $property_purpose,
                'Property_Type' => $property_type,
                'City' => $city,
                'Locality' => $country,
                'Sub_Locality' => '',
                'Tower_Name' => '',
                'Property_Title' => $property_title,
                'Property_Description' => $description,
                'Property_Size' => $area_sqft,
                'Property_Size_Unit' => '',
                'Bedrooms' => $beds,
                'Bathroom' => $baths,
                'Price' => $price,
                'Listing_Agent' => '',
                'Listing_Agent_Phone' => '',
                'Listing_Agent_Email' => '',
                'Features' => '',
                'Images' => $image,
                'Videos' => '',
                'Floor_Plans' => '',
                'Last_Updated' => '',
                'Rent_Frequency' => '',
                'Off_Plan' => '',
                'featured_on_companywebsite' => '',
                'Exclusive_Rights' => '',
                'dateadded' => time(),
            );
            if (empty($property_title) || empty($price) || empty($property_purpose) || empty($property_type)) {
                echo '
     			<p style="color:#fff;background:red;text-align:center">Please Fill All the Required Fildes</p>';
//::::::::::::: add blog
            } elseif ($this->db->insert('tblproperties', $data)) {
//		redirect(base_url('admin/blog/show_blog/'));
                echo '<p style="color:#fff;background:#03a84e;text-align:center">Property Was Added Successfully</p>';
            } else {
                echo '
         <p style="color:#fff;background:red;text-align:center">There are a problem , please try again</p>';
            }
        }
//  $this->load->view('user_add_blog');
    }

    public function deleteSingleProp() {
        if ($this->input->post()) {
            $id = $this->input->POST('id');
            if ($this->db->delete('tblproperties', array('prop_id' => $id))) {
                echo 'Property Deleted successfully';
            } else {
                echo 'Something wrong happened, try again.';
            }
        }
    }

    public function getEditView() {
        if ($this->input->post()) {
            $id = $this->input->post("id");
            $this->db->where("prop_id", $id);
            foreach ($this->db->get(db_prefix() . "properties")->result() as $property) {
                echo '<td colspan="10" style="height:230px;">
                    <table style="width:100%;box-shadow: 1px 1px 5px 1px darkgrey;height:450px;" class="table-responsive">
                        <tr>
                        <br/>
                        <td colspan="6">
                            <table style="width:100%;height:100px;" class="table-responsive">
                                <tr>
                                <th align="left" style="padding: 5px;border-bottom: 1px solid #a1b4cc;background: papayawhip;" colspan="4"><i style="font-size:20px;margin-right:5px;" class="fa fa-edit"></i><b style="font-size:20px;">Listing Editor</b> <span style="font-size: 10px;"> (PROPERTY NUMBER: ' . $property->prop_id . ')</span><br/></th>
                                    <th rowspan="5" style="border-left: 1px solid #a1b4cc;padding: 5px;width:30%;" colspan=2">
                                        <div id="property_images" style="border: 1px dashed #c0ccda;border-radius: 6px;height: 100%;">
                                            <div class="row" align="left">';
                $imageCount = 0;
                $this->db->where("property_id", $property->prop_id);
                $propertyImage = $this->db->get(db_prefix() . "properties_images");
                foreach ($propertyImage->result() as $propImage) {
                    $imageCount++;
                    echo '<div class="display-block property-image-wrapper col-md-4" style="padding-top: 0px;margin-top: 10px;">
                                                    <div class = "col-md-12"><div align="center"><a href="' . base_url("uploads/properties/" . $propImage->image_name) . '" target="_blank"><img width="60%" height="60%" src="' . base_url("uploads/properties/" . $propImage->image_name) . '"/></a></div></div>
                                                    <br/>
                                                    <div class = "col-md-12 text-center" align="center"><a href="javascript::();" class="text-danger" onclick="delete_prop_image(' . $propImage->image_id . ',' . $propImage->property_id . ');">
                                                            <i class = "fa fa fa-times"></i></a>
                                                    </div>
                                                    <div class = "clearfix"></div>
                                                </div>';
                }
                for ($i = $imageCount; $i < 12; $i++) {
                    echo '<div class="display-block property-image-wrapper col-md-4" style="padding-top: 0px;margin-top: 10px;">
                                                    <div class="col-md-12"><div align="center"><img width="60%" height="60%" src="' . base_url("assets/images/default-prop.jpg") . '"/></div></div>
                                                    <br/>
                                                    <div class="col-md-12 text-center" align="center"><a class = "text-danger">
                                                            <i class = "fa fa fa-times"></i></a>
                                                    </div>
                                                    <div class = "clearfix"></div>
                                                </div>';
                }
                echo '</div>
                                        </div>
                                    </th>
                                    <tr>
                                <th style="padding: 5px;border-bottom: 1px solid #a1b4cc;width: 300px;" colspan="1">
                                        <b class="pull-left">Property Developer: </b><input type="text" id="propDeveloper_' . $id . '" name="propDeveloper_' . $id . '" class="form-control" value="' . $property->Developer . '" placeholder="Property Developer">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;border-bottom: 1px solid #a1b4cc;width: 300px;" colspan="1">
                                        <b class="pull-left">Assigned To: </b><select id="assigned_' . $id . '" name="assigned_' . $id . '" class="form-control"><option value selected disabled>ASSIGNED TO</option>';
                foreach ($this->db->get("tblstaff")->result() as $staff) {
                    echo '<option' . (($staff->staffid == $property->assigned) ? ' selected="selected"' : '') . ' value="' . $staff->staffid . '">' . $staff->firstname . ' ' . $staff->lastname . '</option>';
                }
                echo '</select></th>
                                    <th style="padding: 5px;border-bottom: 1px solid #a1b4cc;border-left: 1px solid #a1b4cc;width: 200px;" colspan="1">
                                        <b class="pull-left">Property Price: </b>
                                        <span class="pull-right" style="margin-right: 2px;"><b>PRICE IN AED</b></span>
                                        <input type="text" id="propPrice_' . $id . '" name="propPrice_' . $id . '" class="form-control" value="' . $property->Price . '" placeholder="Property Price">
                                    </th>
                                    <th style="padding: 5px;border-bottom: 1px solid #a1b4cc;border-left: 1px solid #a1b4cc;" colspan="1">
                                        <b class="pull-left">Property Area Size: </b>
                                        <span class="pull-right" style="margin-right: 2px;"><b>Area in SQM: </b>' . $property->Property_Size_SQM . '</span>
                                        <input type="text" id="propArea_' . $id . '" name="propArea_' . $id . '" class="form-control" value="' . $property->Property_Size_SQFT . '" placeholder="Property Price">
                                    </th>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #a1b4cc;">
                                        <th style="padding: 5px;width: 300px;" colspan="1">
                                            <b class="pull-left">Property Country: </b><select id="propCountry_' . $id . '" name="propCountry_' . $id . '" class="form-control"><option value selected disabled>SELECT COUNTRY</option>';
                foreach ($this->db->get("tblcountries")->result() as $country) {
                    echo '<option' . (($country->country_id == $property->Property_Country) ? ' selected="selected"' : '') . ' value="' . $country->country_id . '">' . $country->short_name . '</option>';
                }
                echo '</select>
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;width: 300px;" colspan="1">
                                        <b class="pull-left">Property City: </b><input type="text" id="propCity_' . $id . '" name="propCity_' . $id . '" class="form-control" value="' . $property->City . '" placeholder="Property City">
                                    </th>
                                    <th style="padding: 5px;border-bottom: 1px solid #a1b4cc;border-left: 1px solid #a1b4cc;" colspan="2">
                                        <b class="pull-left">Property Title: </b>
                                        <span class="pull-right" style="margin-right: 2px;">Referance Number: ' . $property->Property_Ref_No . '</span>
                                        <input type="text" id="propTitle_' . $id . '" name="propTitle_' . $id . '" class="form-control" value="' . $property->Property_Title . '" placeholder="Propperty Title">
                                    </th>
                                    </tr>
                                <tr>
                                    <th style="padding: 5px;border-bottom: 1px solid #a1b4cc;width: 300px;" colspan="1">
                                        <b class="pull-left">Property Location: </b><input type="text" id="propLocation_' . $id . '" name="propLocation_' . $id . '" class="form-control" value="' . $property->property_Location . '" placeholder="Property Location">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;border-bottom: 1px solid #a1b4cc;width: 300px;" colspan="1">
                                        <b class="pull-left">Property Type: </b>
                                        <span class="pull-right" style="margin-right: 2px;">
                                            <input type="hidden" id="propPurpose_' . $id . '" name="propPurpose_' . $id . '" value="' . $property->Property_purpose . '"/>
                                            <input type="radio" name="propPurpose_select" value="RENT" ' . ((strtolower($property->Property_purpose) == "rent") ? " checked='checked'" : "") . ' onclick="document.getElementById(' . "'propPurpose_" . $id . "'" . ').value = this.value;"/> RENT
                                            <input type="radio" name="propPurpose_select" value="SALE" style="margin-left: 10px;"' . ((strtolower($property->Property_purpose) == "sale") ? " checked='checked'" : "") . ' onclick="document.getElementById(' . "'propPurpose_" . $id . "'" . ').value = this.value;"/> SALE
                                        </span>
                                        <input type="text" id="propType_' . $id . '" name="propType_' . $id . '" class="form-control" value="' . $property->Property_Type . '" placeholder="Property Type">
                                    </th>
                                    <th colspan="2" rowspan="2" style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b class="pull-left">Property Description: </b>
                                        <textarea id="propDescription_' . $id . '" name="propDescription_' . $id . '" class="form-control" style="height:90%!important;" placeholder="Property Description" title="Property Description">' . $property->Property_Description . '</textarea>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="padding: 5px;width: 300px;border-bottom: 1px solid #a1b4cc;" colspan="1">
                                        <b class="pull-left">Property Community: </b><input type="text" id="propCommunity_' . $id . '" name="propCommunity_' . $id . '" class="form-control" value="' . $property->Communities . '" placeholder="Property Community">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;width: 300px;border-bottom: 1px solid #a1b4cc;" colspan="1">
                                        <b class="pull-left">Property Sub-Community: </b><input type="text" id="propSubCommunity_' . $id . '" name="propSubCommunity_' . $id . '" class="form-control" value="' . $property->Sub_Communities . '" placeholder="Property Sub-Community">
                                    </th>
                                </tr>
                                <tr>
                                    <th style="padding: 5px;" colspan="4" rowspan="1" align="left">
                                        <div style="display: inline-flex;margin-top:5px;">
                                            <img width="20px" height="20px" src="' . base_url("assets/images/beds.png") . '"/><b style="margin:4px;margin-right:20px;">Bedroom/s: </b> <input type="number" min="0" id="propBeds_' . $id . '" name="propBeds_' . $id . '" class="form-control" value="' . (($property->Bedrooms != null && $property->Bedrooms != "") ? $property->Bedrooms : "0") . '" style="height:20px!important;width:30px;padding:0 5px;">
                                            <img width="20px" height="20px" style="margin-left:45px;" src="' . base_url("assets/images/baths.png") . '"/><b style="margin:4px;margin-left:10px;margin-right:30px;">Bathroom/s: </b> <input type="number" min="0" id="propBaths_' . $id . '" name="propBaths_' . $id . '" class="form-control" value="' . (($property->Bathroom != null && $property->Bathroom != "") ? $property->Bathroom : "0") . '" style="height:20px!important;width:30px;padding:0 5px;">
                                            <img width="20px" height="20px" style="margin-left:45px;" src="' . base_url("assets/images/parking.png") . '"/><b style="margin:4px;margin-left:10px;margin-right:40px;">Parking: </b> <input type="number" min="0" id="propParking_' . $id . '" name="propParking_' . $id . '" class="form-control" value="' . (($property->Barking != null && $property->Barking != "") ? $property->Barking : "0") . '" style="height:20px!important;width:30px;padding:0 5px;">
                                        </div>
                                        <br/>';
                $propertyOther = explode("-", $property->Others);
                echo '<div style="display: inline-flex;margin-top:5px;">
                                            <img width="20px" height="20px" src="' . base_url("assets/images/AC.png") . '"/><b style="margin:4px;margin-right:6px;">Central AC: </b> <input type="checkbox" min="0" id="propCentralAC_' . $id . '" name="propCentralAC_' . $id . '" class="form-control" value="TRUE" style="height:20px!important;width:60px;"' . (($propertyOther[0] == "TRUE") ? " checked='checked'" : "") . '>
                                            <img width="20px" height="20px" style="margin-left:30px;" src="' . base_url("assets/images/heater.png") . '"/><b style="margin:4px;margin-left:10px;">Central Heater: </b> <input type="checkbox" min="0" id="propCentralHeater_' . $id . '" name="propCentralHeater_' . $id . '" class="form-control" value="TRUE" style="height:20px!important;width:60px;"' . (($propertyOther[1] == "TRUE") ? " checked='checked'" : "") . '>
                                            <img width="20px" height="20px" style="margin-left:30px;" src="' . base_url("assets/images/study_room.png") . '"/><b style="margin:4px;margin-left:10px;">Study room: </b> <input type="checkbox" min="0" id="propStudyRoom_' . $id . '" name="propStudyRoom_' . $id . '" class="form-control" value="TRUE" style="height:20px!important;width:60px;"' . (($propertyOther[2] == "TRUE") ? " checked='checked'" : "") . '>
                                        </div
                                        <br/>
                                        <div style="display: inline-flex;margin-top:5px;">
                                            <img width="20px" height="20px" src="' . base_url("assets/images/balcony.png") . '"/><b style="margin:4px;margin-right:20px;">Balcony: </b> <input type="checkbox" min="0" id="propBalcony_' . $id . '" name="propBalcony_' . $id . '" class="form-control" value="TRUE" style="height:20px!important;width:60px;"' . (($propertyOther[3] == "TRUE") ? " checked='checked'" : "") . '>
                                            <img width="20px" height="20px" style="margin-left:30px;" src="' . base_url("assets/images/pool.png") . '"/><b style="margin:4px;margin-left:10px;margin-right:17px;">Private Pool: </b> <input type="checkbox" min="0" id="propPool_' . $id . '" name="propPool_' . $id . '" class="form-control" value="TRUE" style="height:20px!important;width:60px;"' . (($propertyOther[4] == "TRUE") ? " checked='checked'" : "") . '>
                                            <img width="20px" height="20px" style="margin-left:30px;" src="' . base_url("assets/images/warehouse.png") . '"/><b style="margin:4px;margin-left:10px;margin-right:24px;">Storage: </b> <input type="checkbox" min="0" id="propStorage_' . $id . '" name="propStorage_' . $id . '" class="form-control" value="TRUE" style="height:20px!important;width:60px;"' . (($propertyOther[5] == "TRUE") ? " checked='checked'" : "") . '>
                                        </div
                                    </th>                                    
                                    <th colspan="1" style="vertical-align:bottom;padding-bottom:0px;">
                                        <form onclick="performClick(' . "'property_image'" . ');" class="dropzone dz-clickable" id="property-attachment-upload" method="post" accept-charset="utf-8" enctype="multipart/form-data" style="min-height:0px;height:20px;">
                                            <div class="dz-default dz-message" style="margin-top: -10px;"><span><b style="font-size: 15px;">Choose file to upload</b></span></div>
                                            <input type="file" id="property_image" name="property_image" style="display: none;" onchange="uploadImage();">
                                            <input type="hidden" name="property_id" value="' . $id . '">
                                        </form>
                                    </th>
                                    <th style="padding: 5px;vertical-align:bottom;" colspan="1" align="right">
                                        <a title="Save" class="btn btn-success btn-large" onclick="saveEdits(' . $id . ');" style="margin-top:5px;">
                                            Save
                                        </a>
                                        <a title="Cancel" class="btn btn-danger btn-large" onclick="property_action(' . $id . ', ' . "'cancel'" . ');" style="margin-top:5px;">
                                            Cancel
                                        </a>
                                    </th>
                                </tr>
                            </table>
                        </td>
                        </tr>
                    </table>
                </td>';
            }
        }
    }

    public function saveEdits() {
        if ($this->input->post()) {
            $propertyData = array(
                "Developer" => $this->input->post("prop_developer"),
                "assigned" => $this->input->post("prop_assigned"),
                "Price" => $this->input->post("prop_price"),
                "Property_Size_SQFT" => $this->input->post("prop_area"),
                "Property_Size_SQM" => (($this->input->post("prop_area")) * (0.09290304)),
                "Property_Country" => $this->input->post("prop_country"),
                "City" => $this->input->post("prop_city"),
                "Property_Title" => $this->input->post("prop_title"),
                "property_Location" => $this->input->post("prop_location"),
                "Property_purpose" => $this->input->post("prop_purpose"),
                "Property_Type" => $this->input->post("prop_type"),
                "Property_Description" => $this->input->post("prop_description"),
                "Communities" => $this->input->post("prop_community"),
                "Sub_Communities" => $this->input->post("prop_sub_community"),
                "Bedrooms" => $this->input->post("prop_beds"),
                "Bathroom" => $this->input->post("prop_baths"),
                "Barking" => $this->input->post("prop_parking"),
                "Others" => $this->input->post("others"),
                "Last_Updated" => date("Y-m-d H:i:s")
            );

            $this->db->where('prop_id', $this->input->post("prop_id"));
            $this->db->update(db_prefix() . "properties", $propertyData);

            $this->getViewAfterEdit($this->input->post("prop_id"));
        }
    }

    protected function getViewAfterEdit($prop_id) {
        $image = base_url("assets/images/default-prop.jpg");
        $this->db->where("property_id", $prop_id);
        $this->db->limit(1);
        $propertyImage = $this->db->get(db_prefix() . "properties_images");
        if ($propertyImage->num_rows() > 0) {
            $image = base_url("uploads/properties/" . $propertyImage->row()->image_name);
        }
        $this->db->where("prop_id", $prop_id);
        foreach ($this->db->get(db_prefix() . "properties")->result() as $property) {
            echo '<td>
                    <table class="table mytable table-responsive table-bordered" role="grid" aria-describedby="table-properties_info" border="1" style="box-shadow: 1px 1px 5px 1px darkgrey;height:450px;">
                        <thead class="alert-info has-row-options odd" role="row">
                            <tr>
                                <th colspan="1" rowspan="2" style="background: papayawhip;padding: 5px;">
                                    <img class="img img-resposive" src="' . $image . '" alt="" height="90" width="95">
                                </th>
                                <th align="left" colspan="4" style="background: papayawhip;padding-left: 10px;font-size: 14px;color: black;"><b style="color: darkblue;font-size: 15px;">Property Title:</b> ' . $property->Property_Title . '</th>
                                <th align="left" colspan="4" style="background: papayawhip;font-size: 14px;color: black;"><b style="color: darkblue;font-size: 15px;">Property No:</b> ' . $property->Property_No . '</th>
                                <th align="right" colspan="2" style="background: papayawhip;padding-right: 20px;font-size: 14px;color: black;"><b style="color: darkblue;font-size: 15px;">Created:</b> ' . date("d F Y", strtotime($property->dateadded)) . '</th>
                                <th align="right" colspan="2" style="background: papayawhip;padding-right: 20px;font-size: 14px;color: black;"><b style="color: darkblue;font-size: 15px;">Last updated:</b> ' . date("d F Y | h:i A", strtotime($property->Last_Updated)) . '</th>
                            </tr>
                            <tr class="alert-info has-row-options odd" role="row">
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">REF#</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">CUSTOM REF#</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">PURPOSE</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">TYPE</td>
                                <td colspan="3" style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">LOCATION</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">CITY</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">COUNTRY</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">COMMUNITIES</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">SUB COMMUNITIES</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">DEVELOPER</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="height: 70px;">
                                <th rowspan="4" style="width:50px;">
                                    <button style="width: 80px;font-size: 10px;" class="btn btn-default btn-xs btn-primary"><i class="fa fa-search"></i> Preview</button>
                                    <button style="width: 80px;font-size: 10px;" class="btn btn-default btn-xs  btn-success" onclick="property_action(' . $property->prop_id . ', ' . "'editView'" . ')"><i class="fa fa-pencil"></i> Edit</button>
                                    <button style="width: 80px;font-size: 10px;" class="btn btn-default btn-xs  btn-warning"><i class="fa fa-copy"></i> Duplicate </button>
                                    <button style="width: 80px;font-size: 10px;" class="btn btn-default btn-xs  btn-warning"><i class="fa fa-arrow-circle-down"></i> Export </button>
                                    <br/><br/><br/><br/>
                                    <button style="width: 80px;font-size: 10px;background-color: #7c5100;" class="btn btn-default btn-xs  btn-primary"><i class="fa fa-address-card-o"></i><br>Manage Ads </button>
                                    <button style="width: 80px;font-size: 10px;background-color: #7c5100;" class="btn btn-default btn-xs  btn-primary" onclick="property_action(' . $property->prop_id . ', ' . "'propertyLeads'" . ')><i class="fa fa-user"></i><br>Active Leads </button>
                                    <button style="width: 80px;font-size: 10px;" class="btn btn-default btn-xs  btn-danger" onclick="property_action(' . $property->prop_id . ', ' . "'delete'" . ')"><i class="fa fa-trash"></i> Delete </button>
                                </th>
                                <td style="font-size: 10px;"><span class="text-info-1">' . $property->Property_Ref_No . '</span></td>
                                <td style="font-size: 10px;"><span class="text-info-1">' . (($property->Property_Custom_REF != null && $property->Property_Custom_REF != "") ? $property->Property_Custom_REF : "NO REFERENCE") . '</span></td>
                                <td style="font-size: 10px;">
                                    ' . (($property->Property_purpose != null && $property->Property_purpose != "") ? ((strtolower($property->Property_purpose) == "rent") ? '<span class = "text-info-1" style = "border-radius: 5px;padding:5px 10px;color:#ff6f00;"><b>RENT</b></span>' : '<span class = "text-info-1" style = "border-radius: 5px;padding:5px 10px;color:green;"><b>SALE</b></span>') : 'UNKNOWN') . '
                                </td>
                                <td style="font-size: 10px;">
                                    <span class="text-info-1">' . $property->Property_Type . '</span>
                                </td>
                                <td colspan="3" style="font-size: 10px;">' . $property->property_Location . '</td>
                                <td style="font-size: 10px;">' . (($property->City != "" && $property->City != "") ? $property->City : "UNKNOWN") . '</td>
                                <td style="font-size: 10px;">';
            $this->db->where("country_id", $property->Property_Country);
            $countries = $this->db->get("tblcountries");
            if ($countries->num_rows() > 0) {
                foreach ($countries->result() as $country) {
                    echo $country->short_name;
                }
            } else {
                echo "UNKNOWN";
            }
            echo '</td>
                                <td style="font-size: 10px;">' . $property->Communities . '</td>
                                <td style="font-size: 10px;">' . $property->Sub_Communities . '</td>
                                <td style="font-size: 10px;">' . (($property->Developer != "" && $property->Developer != "") ? $property->Developer : "UNKNOWN") . '</td>
                            </tr>
                            <tr class="alert-info has-row-options odd" role="row">
                                <th colspan="2" style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">PRICE</th>
                                <td colspan="2" style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">AREA SIZE</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">BEDs</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">BATHs</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">PARKING</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">ASSIGNED</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">LISTING SCORE</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">STATUS</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">QUICK SHARE</td>
                                <td style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">AUICK ACTION</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="font-size: 10px;"><span class="text-info-1">' . $property->Price . '</span> AED</td>
                                <td colspan="2" style="font-size: 10px;">
                                    <span><img width="20px" style="margin-right: 10px;" src="' . base_url("assets/images/area.png") . '"/> ' . $property->Property_Size_SQFT . '&nbsp;&nbsp;&nbsp;ft²</span>
                                    <br/><br/>
                                    <span><img width="20px" style="margin-right: 10px;" src="' . base_url("assets/images/area.png") . '"/> ' . $property->Property_Size_SQM . '&nbsp;&nbsp;&nbsp;m²</span>
                                </td>
                                <td style="font-size: 10px;"><span class="text-info-1">' . $property->Bedrooms . '</span><br/><br/><img width="20px" src="' . base_url("assets/images/beds.png") . '"/></td>
                                <td style="font-size: 10px;"><span class="text-info-1">' . $property->Bathroom . '</span><br/><br/><img width="20px" src="' . base_url("assets/images/baths.png") . '"/></td>
                                <td style="font-size: 10px;"><span class="text-info-1">' . $property->Barking . '</span><br/><br/><img width="20px" src="' . base_url("assets/images/parking.png") . '"/></td>
                                <td style="font-size: 10px;text-align: center;vertical-align: middle;">
                                    <div align="center">
                                        ' . staff_profile_image($property->assigned, $classes = ['staff-profile-image', 'img-rounded'], $type = 'small', array("width" => "35px")) . '
                                        <br/><br/>
                                        <i>' . ((get_staff_full_name($property->assigned) != "" && get_staff_full_name($property->assigned) != null) ? get_staff_full_name($property->assigned) : "<br/>UNKNOWN") . '</i>
                                    </div>
                                </td>
                                <td style = "font-size: 10px;text-align: center;" align = "center">';
            $this->load->helper("properties");
            echo get_listing_view($property);
            echo '</td>
                                <td style = "font-size: 10px;" align = "left">
                                    <p> <i class = "fa fa-user"></i> Leads &rightarrowtail;';
            $this->db->where("property_id", $property->prop_id);
            echo $this->db->get("tblproperties_client")->num_rows();
            echo'</p>
                                    <p> <i class="fa fa-adn"></i> Portal Ads</p>
                                    <p> <i class="fa fa-share-alt"></i> MLS</p>
                                </td>
                                <td style="font-size: 10px;">
                                    <img src="' . base_url("assets/images/facebook-round-icon.png") . '" alt="" align="top" border="0" height="20" width="20">
                                    <img src="' . base_url("assets/images/twitter-round-icon-256.png") . '" alt="" align="top" border="0" height="20" width="20">
                                    <img src="' . base_url("assets/images/Google-plus-circle-icon-png.png") . '" alt="" align="top" border="0" height="20" width="20">
                                    <br>
                                    <img src="' . base_url("assets/images/linkedin_circle_black-512.png") . '" alt="" align="top" border="0" height="20" width="20">
                                    <img src="' . base_url("assets/images/GrwKkd-wp-logo-whatsapp-cut-out-png.png") . '" alt="" align="top" border="0" height="20" width="20">
                                    <img src="' . base_url("assets/images/instagraam-icon_black.png") . '" alt="" align="top" border="0" height="20" width="20">
                                </td>
                                <td style="font-size: 10px;"><img src="' . base_url("assets/images/download-cloud-solid.png") . '" alt="" align="top" border="0" height="35"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>';
        }
    }

    public function addImage() {
        if ($this->input->post()) {
            $id = $this->input->post('property_id');
            $this->db->where("property_id", $id);
            if ($this->db->get(db_prefix() . "properties_images")->num_rows() > 11) {
                echo "maximum files reached";
            } else {
                $config['upload_path'] = './uploads/properties/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|jiff|pdf|xls|xlsx|doc|docx';
                $config['max_size'] = 4096;
                $config['file_name'] = $id . "_" . rand() . "_" . $_FILES['property_image']['name'];

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('property_image')) {
                    echo "Something went wrong, Please try again.";
                } else {
                    $upload_data = $this->upload->data();

                    $inertData = array(
                        "property_id" => $id,
                        "image_name" => $upload_data['file_name']
                    );

                    $this->db->insert(db_prefix() . "properties_images", $inertData);

                    echo '<div class="row" align="left">';
                    $imageCount = 0;
                    $this->db->where("property_id", $id);
                    $propertyImage = $this->db->get(db_prefix() . "properties_images");
                    foreach ($propertyImage->result() as $propImage) {
                        $imageCount++;
                        echo '<div class="display-block property-image-wrapper col-md-4" style="padding-top: 0px;margin-top: 10px;">
                                                    <div class = "col-md-12"><div align="center"><a href="' . base_url("uploads/properties/" . $propImage->image_name) . '" target="_blank"><img width="60%" height="60%" src="' . base_url("uploads/properties/" . $propImage->image_name) . '"/></a></div></div>
                                                    <br/>
                                                    <div class = "col-md-12 text-center" align="center"><a href="javascript::();" class="text-danger" onclick="delete_prop_image(' . $propImage->image_id . ',' . $propImage->property_id . '");">
                                                            <i class = "fa fa fa-times"></i></a>
                                                    </div>
                                                    <div class = "clearfix"></div>
                                                </div>';
                    }
                    for ($i = $imageCount; $i < 12; $i++) {
                        echo '<div class="display-block property-image-wrapper col-md-4" style="padding-top: 0px;margin-top: 10px;">
                                                    <div class="col-md-12"><div align="center"><img width="60%" height="60%" src="' . base_url("assets/images/default-prop.jpg") . '"/></div></div>
                                                    <br/>
                                                    <div class="col-md-12 text-center" align="center"><a class = "text-danger">
                                                            <i class = "fa fa fa-times"></i></a>
                                                    </div>
                                                    <div class = "clearfix"></div>
                                                </div>';
                    }
                    echo '</div>';
                }
            }
        }
    }

    public function deleteImage() {
        if ($this->input->post()) {
            $fileID = $this->input->post("imageID");
            $prop_id = $this->input->post("propertyID");

            $this->db->where("image_id", $fileID);
            if ($this->db->delete(db_prefix() . "properties_images")) {
                echo '<div class="row" align="left">';
                $imageCount = 0;
                $this->db->where("property_id", $prop_id);
                $propertyImage = $this->db->get(db_prefix() . "properties_images");
                foreach ($propertyImage->result() as $propImage) {
                    $imageCount++;
                    echo '<div class="display-block property-image-wrapper col-md-4" style="padding-top: 0px;margin-top: 10px;">
                                                    <div class = "col-md-12"><div align="center"><a href="' . base_url("uploads/properties/" . $propImage->image_name) . '" target="_blank"><img width="60%" height="60%" src="' . base_url("uploads/properties/" . $propImage->image_name) . '"/></a></div></div>
                                                    <br/>
                                                    <div class = "col-md-12 text-center" align="center"><a href="javascript::();" class="text-danger" onclick="delete_prop_image(' . $propImage->image_id . ',' . $propImage->property_id . ');">
                                                            <i class = "fa fa fa-times"></i></a>
                                                    </div>
                                                    <div class = "clearfix"></div>
                                                </div>';
                }
                for ($i = $imageCount; $i < 12; $i++) {
                    echo '<div class="display-block property-image-wrapper col-md-4" style="padding-top: 0px;margin-top: 10px;">
                                                    <div class="col-md-12"><div align="center"><img width="60%" height="60%" src="' . base_url("assets/images/default-prop.jpg") . '"/></div></div>
                                                    <br/>
                                                    <div class="col-md-12 text-center" align="center"><a class = "text-danger">
                                                            <i class = "fa fa fa-times"></i></a>
                                                    </div>
                                                    <div class = "clearfix"></div>
                                                </div>';
                }
                echo '</div>';
            } else {
                echo "error";
            }
        }
    }

    public function getPreopertyLeads() {
        if ($this->input->post()) {
            $id = $this->input->post("id");
            echo '<td>
                    <table class="table table-responsive table-bordered" border="1" style="box-shadow: 1px 1px 5px 1px darkgrey;height:450px;">
                        <thead>
                        <tr>
                        <th style="padding: 5px;border-bottom: 1px solid #a1b4cc;background: papayawhip;" colspan="11">
                        <div class="row">
                        <div class="col-md-7" align="left" style="padding-left:20px;padding-top:5px;"><i style="font-size:20px;margin-right:5px;" class="fa fa-users"></i><b style="font-size:20px;">Active Lead/s Managment</b>
                        </div>
                            <div class="col-md-5" align="right" style="padding-right:20px;padding-top:5px;">
                            <a title="ADD NEW LEAD" class="btn btn-success btn-xs" onclick="propertyLeadForm(' . "'new'" . ');" style="margin-top:5px;"><i class="fa fa-plus"></i> ADD NEW LEAD</a>
                            <a title="ADD EXISTING LEAD" class="btn btn-success btn-xs" onclick="propertyLeadForm(' . "'existing'" . ');" style="margin-top:5px;"><i class="fa fa-plus"></i> ADD EXISTING LEAD</a>
                            <a title="Cancel" class="btn btn-danger btn-xs" onclick="property_action(' . $id . ', ' . "'cancel'" . ');" style="margin-top:5px;">CANCEL</a>
                            </div>
                            </div>
                            <br/>
                            </th>
                            </tr>
                            <tr id="newLeadForm">
                            </tr>
                            <tr>
                            <input type="hidden" value="' . $id . '" id="propertyID"/>
                                <td colspan="1" style="display:none;"></td>
                                <th>#</th>
                                <th>Name</th>
                                <th>E-mail</th>
                                <th>Phone</th>
                                <th>Nationality</th>
                                <th>Category</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Notes</th>
                                <th style = "width:1px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="propertLeadTable">';
            $this->propertyLeads($id);
            echo '</tbody>
                    </table>
                </td>';
        }
    }

    protected function propertyLeads($id) {
        $this->load->model("leads_model");
        $this->db->where("property_id", $id);
        $propertySecondLeads = $this->db->get("tblproperties_client");
        if ($propertySecondLeads->num_rows() > 0) {
            $this->load->helper("staff_helper");
            $this->load->model("leads_model");
            foreach ($propertySecondLeads->result() as $Leads) {
                $this->db->where("id", $Leads->lead_id);
                foreach ($this->db->get("tblleads")->result() as $Leadd) {
                    echo '<tr><td>' . $Leadd->id . '</td><td>' . $Leadd->name . '</td><td>' . $Leadd->email . '</td><td>' . $Leadd->phonenumber . '</td><td>';
                    $nationality_information = $this->leads_model->getNationalities_with_id($Leadd->nationality);
                    if ($nationality_information != null) {
                        echo '<div align="center"><img width="30px" class="img-rounded" src="' . base_url("assets/flags/" . $nationality_information->flag) . '"><br/>'
                        . '<i>' . $nationality_information->nationality . '</i></div>';
                    } else {
                        echo '<div align="center"><i>UNKNOWN</i></div>';
                    } echo'</td><td>';
                    $this->db->where("id", $Leadd->category);
                    foreach ($this->db->get("tblleads_status")->result() as $category) {
                        echo $category->name;
                    } echo '</td><td>';
                    $this->db->where("id", $Leadd->source);
                    foreach ($this->db->get("tblleads_sources")->result() as $source) {
                        echo $source->name;
                    } echo '</td><td>' . $Leadd->status . '</td><td>' . date("d - M - Y", strtotime($Leadd->dateadded)) . '</td><td>';
                    $this->db->where("rel_id", $Leadd->id);
                    $this->db->where("rel_type", "lead");
                    $this->db->limit(3);
                    $this->db->order_by("id", "DESC");
                    $notes = $this->db->get("tblnotes");
                    if ($notes->num_rows() > 0) {
                        echo '<ul style="list-style-type:square;margin-left:3%;">';
                        foreach ($notes->result() as $note) {
                            echo '<li>' . $note->description . ' (' . get_staff_full_name($note->addedfrom) . ' - ' . date("d - F - Y", strtotime($note->dateadded)) . ')</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo "No notes to be shown";
                    }
                    echo '</td><td><a href="javascript::();" onclick="removePropertyLead(' . "'" . $Leads->id . "'" . ',' . "'" . $Leads->lead_id . "'" . ',' . "'" . $Leads->property_id . "'" . ');" class="text text-danger"><i class="text text-danger fa fa-trash"></i></a></td></tr>';
                }
            }
        } else {
            echo "<tr><td colspan='11' class='text text-center'>NO LEADS TO SHOW</td>";
        }
    }

    public function addPropertyLead() {
        if ($this->input->post()) {
            $insertData = array(
                "lead_id" => $this->input->post("leadID"),
                "property_id" => $this->input->post("propertyID")
            );
            if ($this->db->insert("tblproperties_client", $insertData)) {
                $this->propertyLeads($this->input->post("propertyID"));
            } else {
                echo "error";
            }
        }
    }

    public function deletePropertyLead() {
        if ($this->input->post()) {
            $this->db->where("id", $this->input->post("id"));
            $this->db->where("lead_id", $this->input->post("leadID"));
            $this->db->where("property_id", $this->input->post("propertyID"));
            if ($this->db->delete(db_prefix() . "properties_client")) {
                $this->propertyLeads($this->input->post("propertyID"));
            } else {
                echo "error";
            }
        }
    }

    public function getPropertyLeadForm() {
        if ($this->input->post()) {
            if ($this->input->post("formType") == "existing") {
                echo '<td colspan="1" style="display:none;"></td>';
                echo "<td colspan='7' align='right'><label style='margin-top:5px;'>ADD EXISTING LEAD</label></td>";
                echo "<td colspan='3'><input type='text' id='newPropertyLeadName' class='form-control' placeholder='Enter lead name/email/phone/passport number' onkeyup='getLeadSearch(this.value);'/>";
                echo '<ul class="list-group" id="existingLeadToProperty"></ul>';
                echo "<input type='hidden' id='newPropertyLead'/></td>";
                echo '<td><a title="ADD" class="btn btn-success btn-xs" onclick="addPropertyLead();">ADD</a></td>';
            } else if ($this->input->post("formType") == "new") {
                echo '<td colspan="1" style="display:none;"></td>';
                echo "<td colspan='2'><input type='text' class='form-control' placeholder='Enter lead name' id='newleadName'/></td>";
                echo "<td colspan='2'><input type='email' class='form-control' placeholder='Enter lead E-mail' id='newleadEmail'/></td>";
                echo "<td colspan='1'><input type='text' class='form-control' placeholder='Enter lead phone' id='newleadPhone'/></td>";
                echo "<td colspan='1'><select class='form-control' title='LEAD NATIONALITY' id='newleadNationality'><option value disabled selected>SELECT NATIONALITY</option>";
                foreach ($this->db->get("tblcountries")->result() as $nationality) {
                    echo '<option value="' . $nationality->country_id . '">' . $nationality->nationality . ' (' . $nationality->short_name . ')</option>';
                }
                echo "</select></td><td colspan='1'><select class='form-control' title='LEAD CATEGORY' id='newleadCategory'><option value disabled selected>SELECT CATEGORY</option>";
                foreach ($this->db->get("tblleads_status")->result() as $category) {
                    echo '<option value="' . $category->id . '">' . $category->name . '</option>';
                }
                echo "</select></td><td colspan='1'><select class='form-control' title='LEAD SOURCE' id='newleadSource'><option value disabled selected>SELECT SOURCE</option>";
                foreach ($this->db->get("tblleads_sources")->result() as $source) {
                    echo '<option value="' . $source->id . '">' . $source->name . '</option>';
                }
                echo "</select></td>";
                echo "<td colspan='1'><select class='form-control' title='LEAD STATUS' id='newleadStatus'><option value disabled selected>SELECT STATUS</option>"
                . "<option value='Undefined'>Undefined</option><option value='Contacted'>Contacted</option><option value='Open'>Open</option><option value='Closed'>Closed</option></select></td>";
                echo "<td colspan='1'><input type='text' class='form-control' placeholder='Enter lead note' id='newLeadNote'/></td>";
                echo '<td><a title="ADD" class="btn btn-success btn-xs" onclick="addNewLead();">ADD</a></td>';
            }
        } else {
            echo "error";
        }
    }

    public function createAddLead() {
        if ($this->input->post()) {
            $leadData = array(
                "name" => $this->input->post("newleadName"),
                "email" => $this->input->post("newleadEmail"),
                "phonenumber" => $this->input->post("newleadPhone"),
                "nationality" => $this->input->post("newleadNationality"),
                "category" => $this->input->post("newleadCategory"),
                "source" => $this->input->post("newleadSource"),
                "status" => $this->input->post("newleadStatus")
            );

            if ($this->db->insert(db_prefix() . "leads", $leadData)) {
                $leadIDd = $this->db->insert_id();
                $insertData = array(
                    "rel_id" => $leadIDd,
                    "rel_type" => "lead",
                    "description" => $this->input->post("newLeadNote"),
                    "date_contacted" => date("Y-m-d H:i:s"),
                    "addedfrom" => get_staff_user_id(),
                    "dateadded" => date("Y-m-d H:i:s")
                );

                $this->db->insert('tblnotes', $insertData);

                $insertData = array(
                    "lead_id" => $leadIDd,
                    "property_id" => $this->input->post("newLeadProperty")
                );
                if ($this->db->insert("tblproperties_client", $insertData)) {
                    $this->propertyLeads($this->input->post("newLeadProperty"));
                } else {
                    echo "error";
                }
            } else {
                echo "error";
            }
        }
    }

    public function getLeadList() {
        if ($this->input->post()) {
            $leadSearch = $this->input->post("searchWith");
            $this->db->like("name", $leadSearch);
            $this->db->or_like("email", $leadSearch);
            $this->db->or_like("phonenumber", $leadSearch);
            $this->db->or_like("passportnumber", $leadSearch);
            $this->db->limit(20);
            foreach ($this->db->get(db_prefix() . "leads")->result() as $lead) {
                echo '<li style="cursor: pointer;" class="list-group-item" onmousedown="setInputValues(' . "'" . $lead->id . "'" . ',' . "'" . $lead->name . "'" . ');">' . $lead->name . '</li>';
            }
        }
    }

    public function AdsView() {
        if ($this->input->post()) {
            $id = $this->input->post("id");
            echo '<td>
                    <table class="table table-responsive table-bordered" border="1" style="box-shadow: 1px 1px 5px 1px darkgrey;height:450px;">
                        <thead>
                        <tr>
                            <th style="padding: 5px;border-bottom: 1px solid #a1b4cc;background: papayawhip;" colspan="11">
                                <div class="row">
                                <div class="col-md-7" align="left" style="padding-left:20px;padding-top:5px;"><i style="font-size:20px;margin-right:5px;" class="fa fa-bullhorn"></i><b style="font-size:20px;">Listing Advertisement Managment</b>
                                </div>
                                    <div class="col-md-5" align="right" style="padding-right:20px;padding-top:5px;">
                                        <a title="Cancel" class="btn btn-danger btn-xs" onclick="property_action(' . $id . ', ' . "'cancel'" . ');" style="margin-top:5px;">CANCEL</a>
                                    </div>
                                </div>
                                <br/>
                            </th>
                        </tr>
                        </thead>
                        <tbody>';
            $this->activePortals($id);
            echo '</tbody>
                    </table>
                </td>';
        }
    }

    protected function activePortals($id) {
        $portals = $this->db->get(db_prefix() . "portals");
        if ($portals->num_rows() > 0) {
            echo "<tr><td><div class='row'><br/>";
            foreach ($portals->result() as $portal) {
                $checked = "";
                $this->db->where("portal_id", $portal->id);
                $this->db->where("property_id", $id);
                if ($this->db->get(db_prefix() . "properties_portals")->num_rows() > 0) {
                    $checked = ' checked="true"';
                }
                echo '<div class="col-md-1" align="center">';
                echo '<img width="80px;" height="80px;" class="img img-rounded" src="' . base_url("uploads/portals/" . $portal->P_image) . '"/>';
                echo "</br></br>";
                echo '<label class="switch">
                        <input type="checkbox" id="portalSwitch_' . $id . '_' . $portal->id . '" onclick="changePortalAccess(' . $portal->id . ',' . $id . ');"' . $checked . '>
                        <span class="switch-slider round"></span>
                      </label>';
                echo '</div>';
            }
            echo "</div></td></tr>";
        } else {
            echo "<tr><td colspan='11' class='text text-center'>NO PORTALS TO SHOW</td>";
        }
    }

    public function changePortalStatue() {
        if ($this->input->post()) {
            if ($this->input->post("status") == "active") {
                $this->db->where("portal_id", $this->input->post("portalID"));
                $this->db->where("property_id", $this->input->post("propertyID"));
                if ($this->db->get(db_prefix() . "properties_portals")->num_rows() == 0) {
                    $portalPropertyInf = array(
                        "portal_id" => $this->input->post("portalID"),
                        "property_id" => $this->input->post("propertyID")
                    );
                    $this->db->insert(db_prefix() . "properties_portals", $portalPropertyInf);
                }
            } else if ($this->input->post("status") == "disable") {
                $this->db->where("portal_id", $this->input->post("portalID"));
                $this->db->where("property_id", $this->input->post("propertyID"));
                $this->db->delete(db_prefix() . "properties_portals");
            }
        }
    }

}