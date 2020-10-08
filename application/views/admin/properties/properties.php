<?php
defined('BASEPATH') or exit('No direct script access allowed');
init_head();
if (($this->input->get('page')) && ($this->input->get('items'))) {
    $this->load->helper("properties");
    $filters = str_replace("page=" . $this->input->get('page') . "&items=" . $this->input->get('items') . "&", "", $_SERVER['QUERY_STRING']);
    $filter_inputs = explode("&", $filters);
    $filter_array = array("ac", "heater", "study", "balcony", "pool", "storage");
    if (is_admin() || is_admin_normal() || has_permission("leads", '', "view_all")) {
        
    } else {
        $this->db->where("assigned", get_staff_user_id());
    }
    if ($this->input->get("search") != NULL && $this->input->get("search") != "") {
        $this->db->like("name", $this->input->get("search"));
        $this->db->or_like("email", $this->input->get("search"));
        $this->db->or_like("phonenumber", $this->input->get("search"));
    } else {
        $acString = "FALSE";
        $heaterString = "FALSE";
        $studyString = "FALSE";
        $balconyString = "FALSE";
        $poolString = "FALSE";
        $storageString = "FALSE";
        for ($i = 0; $i < count($filter_inputs); $i++) {
            $filter_input = explode("=", $filter_inputs[$i]);
            if ($filter_input[0] != "page" && $filter_input[0] != "items" && $filter_input[0] != "csrf_token_name" && $filter_input[0] != "leadID" && ($filter_input[1] != "" || $filter_input[1] != null)) {
                if (in_array(str_replace("filter_", "", $filter_input[0]), $filter_array) && $filter_input[1] == "TRUE") {
                    $otherFilter = true;
                    if (str_replace("filter_", "", $filter_input[0]) == "ac") {
                        $acString = $filter_input[1];
                    } else if (str_replace("filter_", "", $filter_input[0]) == "heater") {
                        $heaterString = $filter_input[1];
                    } else if (str_replace("filter_", "", $filter_input[0]) == "study") {
                        $studyString = $filter_input[1];
                    } else if (str_replace("filter_", "", $filter_input[0]) == "balcony") {
                        $balconyString = $filter_input[1];
                    } else if (str_replace("filter_", "", $filter_input[0]) == "pool") {
                        $poolString = $filter_input[1];
                    } else if (str_replace("filter_", "", $filter_input[0]) == "storage") {
                        $storageString = $filter_input[1];
                    }
                } else {
                    if (str_replace("filter_", "", $filter_input[0]) == "purpose") {
                        if ($filter_input[1] != "ALL") {
                            $this->db->where("lower(`tblproperties`.`Property_purpose`)", str_replace("+", " ", strtolower($filter_input[1])));
                        }
                    } else if (str_replace("filter_", "", $filter_input[0]) == "assigned") {
                        $this->db->where("tblproperties.assigned", str_replace("+", " ", $filter_input[1]));
                    } else if (str_replace("filter_", "", $filter_input[0]) == "type") {
                        $this->db->where("lower(`tblproperties`.`Property_Type`)", str_replace("+", " ", strtolower($filter_input[1])));
                    } else if (str_replace("filter_", "", $filter_input[0]) == "developer") {
                        $this->db->where("lower(`tblproperties`.`Developer`)", str_replace("+", " ", strtolower($filter_input[1])));
                    } else if (str_replace("filter_", "", $filter_input[0]) == "country") {
                        $this->db->where("tblproperties.Property_Country", str_replace("+", " ", $filter_input[1]));
                    } else if (str_replace("filter_", "", $filter_input[0]) == "comm") {
                        $this->db->where("lower(`tblproperties`.`Communities`)", str_replace("+", " ", strtolower($filter_input[1])));
                    } else if (str_replace("filter_", "", $filter_input[0]) == "sub_comm") {
                        $this->db->where("lower(`tblproperties`.`Sub_Communities`)", str_replace("+", " ", strtolower($filter_input[1])));
                    } else if (str_replace("filter_", "", $filter_input[0]) == "priceS" || str_replace("filter_", "", $filter_input[0]) == "priceE") {
                        if (str_replace("filter_", "", $filter_input[0]) == "priceS") {
                            $this->db->where("`tblproperties`.`Price` >=", str_replace("+", " ", $filter_input[1]));
                        } else {
                            $this->db->where("`tblproperties`.`Price` <=", str_replace("+", " ", $filter_input[1]));
                        }
                    } else if (str_replace("filter_", "", $filter_input[0]) == "areaS" || str_replace("filter_", " ", $filter_input[0]) == "areaE") {
                        if (str_replace("filter_", "", $filter_input[0]) == "areaS") {
                            $this->db->where("tblproperties.Property_Size_SQFT >=", str_replace("+", " ", $filter_input[1]));
                        } else {
                            $this->db->where("tblproperties.Property_Size_SQFT <=", str_replace("+", " ", $filter_input[1]));
                        }
                    } else if (str_replace("filter_", "", $filter_input[0]) == "bedS" || str_replace("filter_", "", $filter_input[0]) == "bedE") {
                        if (str_replace("filter_", "", $filter_input[0]) == "bedS") {
                            $this->db->where("tblproperties.Bedrooms >=", str_replace("+", " ", $filter_input[1]));
                        } else {
                            $this->db->where("tblproperties.Bedrooms <=", str_replace("+", " ", $filter_input[1]));
                        }
                    } else if (str_replace("filter_", "", $filter_input[0]) == "leads") {
                        $this->db->join("tblproperties_client", "tblproperties_client.property_id = tblproperties.prop_id");
                    }
                }
            }
        }
        if (isset($otherFilter) && $otherFilter != NULL) {
            $this->db->where("Others", $acString . "-" . $heaterString . "-" . $studyString . "-" . $balconyString . "-" . $poolString . "-" . $storageString);
        }

        if ($this->input->get("leadID")) {
            $this->db->where("property_lead_id", $this->input->get("leadID"));
        }
        $count = $this->db->get("tblproperties")->num_rows();
        $pages = ($count / $this->input->get('items'));
        if ($pages > intval($pages)) {
            $pages = intval($pages + 1);
        }
        if ($pages == 0) {
            $pages++;
        }
        if ($this->input->get('page') && (!($pages >= $this->input->get('page')))) {
            redirect(base_url() . "admin/properties/?page=1&items=5", "refresh");
        }
    }

    for ($i = 0; $i < count($filter_inputs); $i++) {
        $filter_input = explode("=", $filter_inputs[$i]);
        if ($filter_input[0] != "page" && $filter_input[0] != "items" && $filter_input[0] != "csrf_token_name" && $filter_input[0] != "leadID" && ($filter_input[1] != "" || $filter_input[1] != null)) {
            if (in_array(str_replace("filter_", "", $filter_input[0]), $filter_array) && $filter_input[1] == "TRUE") {
                $otherFilter = true;
                if (str_replace("filter_", "", $filter_input[0]) == "ac") {
                    $acString = $filter_input[1];
                } else if (str_replace("filter_", "", $filter_input[0]) == "heater") {
                    $heaterString = $filter_input[1];
                } else if (str_replace("filter_", "", $filter_input[0]) == "study") {
                    $studyString = $filter_input[1];
                } else if (str_replace("filter_", "", $filter_input[0]) == "balcony") {
                    $balconyString = $filter_input[1];
                } else if (str_replace("filter_", "", $filter_input[0]) == "pool") {
                    $poolString = $filter_input[1];
                } else if (str_replace("filter_", "", $filter_input[0]) == "storage") {
                    $storageString = $filter_input[1];
                }
            } else {
                if (str_replace("filter_", "", $filter_input[0]) == "purpose") {
                    if ($filter_input[1] != "ALL") {
                        $this->db->where("lower(`tblproperties`.`Property_purpose`)", str_replace("+", " ", strtolower($filter_input[1])));
                    }
                } else if (str_replace("filter_", "", $filter_input[0]) == "assigned") {
                    $this->db->where("tblproperties.assigned", str_replace("+", " ", $filter_input[1]));
                } else if (str_replace("filter_", "", $filter_input[0]) == "type") {
                    $this->db->where("lower(`tblproperties`.`Property_Type`)", str_replace("+", " ", strtolower($filter_input[1])));
                } else if (str_replace("filter_", "", $filter_input[0]) == "developer") {
                    $this->db->where("lower(`tblproperties`.`Developer`)", str_replace("+", " ", strtolower($filter_input[1])));
                } else if (str_replace("filter_", "", $filter_input[0]) == "country") {
                    $this->db->where("tblproperties.Property_Country", str_replace("+", " ", $filter_input[1]));
                } else if (str_replace("filter_", "", $filter_input[0]) == "comm") {
                    $this->db->where("lower(`tblproperties`.`Communities`)", str_replace("+", " ", strtolower($filter_input[1])));
                } else if (str_replace("filter_", "", $filter_input[0]) == "sub_comm") {
                    $this->db->where("lower(`tblproperties`.`Sub_Communities`)", str_replace("+", " ", strtolower($filter_input[1])));
                } else if (str_replace("filter_", "", $filter_input[0]) == "priceS" || str_replace("filter_", "", $filter_input[0]) == "priceE") {
                    if (str_replace("filter_", "", $filter_input[0]) == "priceS") {
                        $this->db->where("`tblproperties`.`Price` >=", str_replace("+", " ", $filter_input[1]));
                    } else {
                        $this->db->where("`tblproperties`.`Price` <=", str_replace("+", " ", $filter_input[1]));
                    }
                } else if (str_replace("filter_", "", $filter_input[0]) == "areaS" || str_replace("filter_", " ", $filter_input[0]) == "areaE") {
                    if (str_replace("filter_", "", $filter_input[0]) == "areaS") {
                        $this->db->where("tblproperties.Property_Size_SQFT >=", str_replace("+", " ", $filter_input[1]));
                    } else {
                        $this->db->where("tblproperties.Property_Size_SQFT <=", str_replace("+", " ", $filter_input[1]));
                    }
                } else if (str_replace("filter_", "", $filter_input[0]) == "bedS" || str_replace("filter_", "", $filter_input[0]) == "bedE") {
                    if (str_replace("filter_", "", $filter_input[0]) == "bedS") {
                        $this->db->where("tblproperties.Bedrooms >=", str_replace("+", " ", $filter_input[1]));
                    } else {
                        $this->db->where("tblproperties.Bedrooms <=", str_replace("+", " ", $filter_input[1]));
                    }
                } else if (str_replace("filter_", "", $filter_input[0]) == "leads") {
                    $this->db->join("tblleads", "tblleads.id = tblproperties.property_lead_id");
                    $this->db->join("tblproperties_client", "tblproperties_client.property_id = tblproperties.prop_id", 'left');
                }
            }
        }
    }
    if (isset($otherFilter) && $otherFilter != NULL) {
        $this->db->where("Others", $acString . "-" . $heaterString . "-" . $studyString . "-" . $balconyString . "-" . $poolString . "-" . $storageString);
    }
    if ($this->input->get("leadID")) {
        $this->db->where("property_lead_id", $this->input->get("leadID"));
    }
    $start = (($this->input->get('page') - 1) * $this->input->get('items'));
    $this->db->LIMIT($this->input->get('items'));
    $this->db->OFFSET($start);
    $propertiesQuery = $this->db->get("tblproperties");
} else {
    redirect(base_url() . "admin/properties/?page=1&items=5", "refresh");
}
?>
<style>
    .dropdown-menu{
        max-height: 400px !important;
    }
    span.ui-slider-handle.ui-state-default.ui-corner-all:first-of-type {
        border-radius: 10% 50% 50% 10%;
    }
    span.ui-slider-handle.ui-state-default.ui-corner-all:last-of-type {
        border-radius: 50% 10% 10% 50%;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#"  data-toggle="modal" data-target="#add_property" class="btn mright5 btn-info pull-left display-block">
                                <?php echo 'New Properties'; ?>
                            </a>
                            <?php if (is_admin() || get_option('allow_non_admin_members_to_import_leads') == '1') { ?>
                                <a href="<?php echo admin_url('properties/import'); ?>" class="btn btn-info pull-left display-block hidden-xs">
                                    <?php echo 'Import Properties'; ?>
                                </a>
                            <?php } ?>
                            <div class="clearfix"></div>
                        </div>
                        <hr class="hr-panel-heading" />
                        <div class="tab-content">
                            <div class="row">
                                <div class="col-md-12">
                                    <form action="<?= base_url('admin/properties/'); ?>" method="get">
                                        <input type="hidden" name="page" value="1"/>
                                        <input type="hidden" name="items" value="<?= $this->input->get('items'); ?>"/>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold"><?php echo _l('filter_by'); ?></p>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <?php if (is_admin() || is_admin_normal() || has_permission("properties", '', "view_all")) { ?>
                                                    <select id="filter_assigned" style="height:30px !important;font-size: 12px !important;" title="ASSIGNED TO"<?= (($this->input->get("filter_assigned") != null) ? " name='filter_assigned'" : ""); ?> onchange="filter_change('assigned');" class="selectpicker" data-width="100%" data-live-search="true">
                                                        <option value='ALL'>ALL STAFF</option>
                                                        <?php
                                                        foreach ($this->db->get("tblstaff")->result() as $stuff) {
                                                            echo '<option' . (($this->input->get("filter_assigned") != null && $this->input->get("filter_assigned") == $stuff->staffid) ? " selected='selected'" : "") . ' value="' . $stuff->staffid . '">' . $stuff->firstname . ' ' . $stuff->lastname . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                <?php } ?>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <select id="filter_type" style="height:30px !important;font-size: 12px !important;" title="ALL TYPES"<?= (($this->input->get("filter_type") != null) ? " name='filter_type'" : ""); ?> onchange="filter_change('type');" class="selectpicker" data-width="100%" data-live-search="true">
                                                    <option value='ALL'>ALL</option>
                                                    <option value disabled>RESIDENTIAL</option>
                                                    <?php
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Apartment") ? " selected='selected'" : "") . ' value="Apartment">Apartment</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Townhouse") ? " selected='selected'" : "") . ' value="Townhouse">Townhouse</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Villa Compund") ? " selected='selected'" : "") . ' value="Villa Compund">Villa Compund</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Residential Plot") ? " selected='selected'" : "") . ' value="Residential Plot">Residential Plot</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Residential Building") ? " selected='selected'" : "") . ' value="Residential Building">Residential Building</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Villa") ? " selected='selected'" : "") . ' value="Villa">Villa</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Penthouse") ? " selected='selected'" : "") . ' value="Penthouse">Penthouse</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Hotel Apartment") ? " selected='selected'" : "") . ' value="Hotel Apartment">Hotel Apartment</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Residential Floor") ? " selected='selected'" : "") . ' value="Residential Floor">Residential Floor</option>';
                                                    ?>
                                                    <option value disabled>COMMERCIAL</option>
                                                    <?php
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Office") ? " selected='selected'" : "") . ' value="Office">Office</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Warehouse") ? " selected='selected'" : "") . ' value="Warehouse">Warehouse</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Commercial Villa") ? " selected='selected'" : "") . ' value="Commercial Villa">Commercial Villa</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Commercial Plot") ? " selected='selected'" : "") . ' value="Commercial Plot">Commercial Plot</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Commercial Building") ? " selected='selected'" : "") . ' value="Commercial Building">Commercial Building</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Industrial Land") ? " selected='selected'" : "") . ' value="Industrial Land">Industrial Land</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Showroom") ? " selected='selected'" : "") . ' value="Showroom">Showroom</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Shop") ? " selected='selected'" : "") . ' value="Shop">Shop</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Labour Camp") ? " selected='selected'" : "") . ' value="Labour Camp">Labour Camp</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Bulk Unit") ? " selected='selected'" : "") . ' value="Bulk Unit">Bulk Unit</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Commercil Floor") ? " selected='selected'" : "") . ' value="Commercil Floor">Commercil Floor</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Factory") ? " selected='selected'" : "") . ' value="Factory">Factory</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Mixed Use Land") ? " selected='selected'" : "") . ' value="Mixed Use Land">Mixed Use Land</option>';
                                                    echo '<option' . (($this->input->get("filter_type") != null && $this->input->get("filter_type") == "Other Commercial") ? " selected='selected'" : "") . ' value="Other Commercial">Other Commercial</option>';
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <p class="bed-filters" style="margin:0 auto;display:inline-flex;width:100%;">
                                                    <input type="number" id="bed-filter-min" class="form-control" placeholder=1 aria-label="Minimum bed for filtering products" style="height:20px !important;width:90px;"/>
                                                    <label for="bed-filter-max" aria-label="Maximum bed for filtering products" style="margin:2px auto;"><b>Bedrooms</b></label>
                                                    <input type="number" id="bed-filter-max" class="form-control" placeholder=100 aria-label="Maximum bed for filtering products" style="height:20px !important;width:90px;"/>
                                                </p>
                                                <div id="bed-slider-range" data-bed-min="1" data-bed-max="100"></div>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <p class="price-filters" style="margin:0 auto;display:inline-flex;width:100%;">
                                                    <input type="number" id="price-filter-min" class="form-control" placeholder=1 aria-label="Minimum price for filtering products" style="height:20px !important;width:90px;"/>
                                                    <label for="price-filter-max" aria-label="Maximum price for filtering products" style="margin:2px auto;"><b>AED</b></label>
                                                    <input type="number" id="price-filter-max" class="form-control" placeholder=100000000 aria-label="Maximum price for filtering products" style="height:20px !important;width:90px;"/>
                                                </p>
                                                <div id="price-slider-range" data-price-min="1" data-price-max="100000000"></div>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <p class="area-filters" style="margin:0 auto;display:inline-flex;width:100%;">
                                                    <input type="number" id="area-filter-min" class="form-control" placeholder=1 aria-label="Minimum area for filtering products" style="height:20px !important;width:90px;"/>
                                                    <label for="area-filter-max" aria-label="Maximum area for filtering products" style="margin:2px auto;"><b>AERA</b></label>
                                                    <input type="number" id="area-filter-max" class="form-control" placeholder=10000 aria-label="Maximum area for filtering products" style="height:20px !important;width:90px;"/>
                                                </p>
                                                <div id="area-slider-range" data-area-min="1" data-area-max="10000"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 leads-filter-column">
                                                <input id='filter_location' type="text" class="form-control" style="margin-top: 5%;height:35px !important;font-size: 12px !important;" title="LOCATION"<?= (($this->input->get("filter_location") != null && $this->input->get("filter_location") != "") ? " name='filter_location' value='" . $this->input->get("filter_location") . "'" : ""); ?> onchange="filter_change('location');" placeholder="LOCATION"/>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <div class="select-placeholder" style="margin-top: 5%;">
                                                    <select id="filter_country" style="height:30px !important;font-size: 12px !important;" title="COUNTRIES"<?= (($this->input->get("filter_country") != null) ? " name='filter_country'" : ""); ?> onchange="filter_change('country');" class="selectpicker" data-width="100%" data-live-search="true">
                                                        <option value='ALL'>ALL COUNTRIES</option>
                                                        <?php
                                                        foreach ($this->db->get("tblcountries")->result() as $County) {
                                                            echo '<option' . (($this->input->get("filter_country") != null && $this->input->get("filter_country") == $County->country_id) ? " selected='selected'" : "") . ' value="' . $County->country_id . '">' . $County->short_name . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <div class="select-placeholder" style="margin-top: 5%;">
                                                    <select id="filter_comm" style="height:30px !important;font-size: 12px !important;" title="COMMUNITIES"<?= (($this->input->get("filter_comm") != null) ? " name='filter_comm'" : ""); ?> onchange="filter_change('comm');" class="selectpicker" data-width="100%" data-live-search="true">
                                                        <option value='ALL'>ALL COMMUNITIES</option>
                                                        <?php
                                                        $commArray = array();
                                                        foreach ($this->db->get("tblproperties")->result() as $property) {
                                                            if ($property->Communities != null && $property->Communities != "" && !(in_array($property->Communities, $commArray))) {
                                                                array_push($commArray, $property->Communities);
                                                                echo '<option' . (($this->input->get("filter_comm") != null && $this->input->get("filter_comm") == $property->Communities) ? " selected='selected'" : "") . ' value="' . $property->Communities . '">' . $property->Communities . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <div class="select-placeholder" style="margin-top: 5%;">
                                                    <select title="SUB COMMUNITIES" style="height:30px !important;font-size: 12px !important;" id="filter_sub_comm"<?= (($this->input->get("filter_sub_comm") != null) ? " name='filter_sub_comm'" : ""); ?> onchange="filter_change('sub_comm');" class="selectpicker" data-width="100%" data-live-search="true">
                                                        <option value='ALL'>ALL SUB COMMUNITIES</option>
                                                        <?php
                                                        $subCommArray = array();
                                                        foreach ($this->db->get("tblproperties")->result() as $property) {
                                                            if ($property->Sub_Communities != null && $property->Sub_Communities != "" && !(in_array($property->Sub_Communities, $subCommArray))) {
                                                                array_push($subCommArray, $property->Sub_Communities);
                                                                echo '<option' . (($this->input->get("filter_sub_comm") != null && $this->input->get("filter_sub_comm") == $property->Sub_Communities) ? " selected='selected'" : "") . ' value="' . $property->Sub_Communities . '">' . $property->Sub_Communities . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 leads-filter-column">
                                                <input id='filter_developer' type="text" class="form-control" style="margin-top:5%;height:35px !important;font-size: 12px !important;" title="DEVELOPER"<?= (($this->input->get("filter_developer") != null && $this->input->get("filter_developer") != "") ? " name='filter_developer' value='" . $this->input->get("filter_developer") . "'" : ""); ?> onchange="filter_change('developer');" placeholder="DEVELOPER"/>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2 leads-filter-column" style="display: inline-flex;">
                                                <input type="radio" name="filter_purpose" id="filter_purpose" class="form-control" style="margin-top:10%;width: 20px;"<?= ((!($this->input->get("filter_purpose")) || $this->input->get("filter_purpose") != null && $this->input->get("filter_purpose") == "ALL") ? " checked='checked'" : ""); ?>title="ALL" value="ALL"/> <b style="margin-top:12%;margin-left:5%;margin-right:5%;">ALL</b>
                                                <input type="radio" name="filter_purpose" id="filter_purpose" class="form-control" style="margin-top:10%;width: 20px;"<?= (($this->input->get("filter_purpose") != null && $this->input->get("filter_purpose") == "rent") ? " checked='checked'" : ""); ?> title="RENT" value="rent"/> <b style="margin-top:12%;margin-left:5%;margin-right:5%;">RENT</b>
                                                <input type="radio" name="filter_purpose" id="filter_purpose" class="form-control" style="margin-top:10%;width: 20px;"<?= (($this->input->get("filter_purpose") != null && $this->input->get("filter_purpose") == "sale") ? " checked='checked'" : ""); ?> title="SALE" value="sale"/> <b style="margin-top:12%;margin-left:5%;margin-right:5%;">SALE</b>
                                            </div>
                                            <div class="col-md-10 leads-filter-column">
                                                <th style="padding: 5px;" colspan="4" rowspan="1" align="left">
                                                    <br/>
                                                    <div style="display: inline-flex;margin-top:5px;">
                                                        <img width="20px" height="20px" src="<?= base_url("assets/images/AC.png"); ?>"/><b style="margin:4px;margin-right:15px;">Central AC: </b> <input type="checkbox" id="filter_ac"<?= (($this->input->get("filter_ac") != null && $this->input->get("filter_ac") == "TRUE") ? " name='filter_ac' checked='checked'" : ""); ?> class="form-control" value="TRUE" style="height:20px!important;width:20px;" onchange="filter_change('ac');">
                                                        <img width="20px" height="20px" style="margin-left:20px;" src="<?= base_url("assets/images/heater.png"); ?>"/><b style="margin:4px;margin-left:10px;">Central Heater: </b> <input type="checkbox" id="filter_heater"<?= (($this->input->get("filter_heater") != null && $this->input->get("filter_heater") == "TRUE") ? " name='filter_heater' checked='checked'" : ""); ?> class="form-control" value="TRUE" style="height:20px!important;width:20px;" onchange="filter_change('heater');">
                                                        <img width="20px" height="20px" style="margin-left:20px;" src="<?= base_url("assets/images/study_room.png"); ?>"/><b style="margin:4px;margin-left:10px;">Study room: </b> <input type="checkbox" id="filter_study"<?= (($this->input->get("filter_study") != null && $this->input->get("filter_study") == "TRUE") ? " name='filter_study' checked='checked'" : ""); ?> class="form-control" value="TRUE" style="height:20px!important;width:20px;" onchange="filter_change('study');">
                                                        <img width="20px" height="20px" style="margin-left:20px;" src="<?= base_url("assets/images/balcony.png"); ?>"/><b style="margin:4px;margin-right:15px;">Balcony: </b> <input type="checkbox" id="filter_balcony"<?= (($this->input->get("filter_balcony") != null && $this->input->get("filter_balcony") == "TRUE") ? " name='filter_balcony' checked='checked'" : ""); ?> class="form-control" value="TRUE" style="height:20px!important;width:20px;" onchange="filter_change('balcony');">
                                                        <img width="20px" height="20px" style="margin-left:20px;" src="<?= base_url("assets/images/pool.png"); ?>"/><b style="margin:4px;margin-left:10px;margin-right:15px;">Private Pool: </b> <input type="checkbox" id="filter_pool"<?= (($this->input->get("filter_pool") != null && $this->input->get("filter_pool") == "TRUE") ? " name='filter_pool' checked='checked'" : ""); ?> class="form-control" value="TRUE" style="height:20px!important;width:20px;" onchange="filter_change('pool');">
                                                        <img width="20px" height="20px" style="margin-left:20px;" src="<?= base_url("assets/images/warehouse.png"); ?>"/><b style="margin:4px;margin-left:10px;margin-right:15px;">Storage: </b> <input type="checkbox" id="filter_storage"<?= (($this->input->get("filter_storage") != null && $this->input->get("filter_storage") == "TRUE") ? " name='filter_storage' checked='checked'" : ""); ?> class="form-control" value="TRUE" style="height:20px!important;width:20px;" onchange="filter_change('storage');">
                                                        <i style="margin-left:20px;height: 20px;width:20px;margin-top: 5px;" class="fa fa-users"/></i><b style="margin:4px;margin-left:10px;margin-right:15px;">Contain leads: </b> <input type="checkbox" id="filter_leads"<?= (($this->input->get("filter_leads") != null && $this->input->get("filter_leads") == "TRUE") ? " name='filter_leads' checked='checked'" : ""); ?> class="form-control" value="TRUE" style="height:20px!important;width:20px;" onchange="filter_change('leads');">
                                                    </div>
                                                </th>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-10 leads-filter-column"></div>
                                            <div class="col-md-2 leads-filter-column">
                                                <div class="col-md-6 pull-right" style="padding-right: 0px;">
                                                    <input style="margin-top: 5%;" class="form-control" type="submit" value="GO"/>
                                                </div>
                                                <div class="col-md-6 pull-right" style="padding-right: 0px;">
                                                    <input style="margin-top: 5%;" class="form-control" type="button" value="RESET" onclick="window.location.href = ('<?= base_url("admin/properties/?page=" . $this->input->get("page") . "&items=" . $this->input->get("items")); ?>');"/>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="clearfix"></div>
                                <hr class="hr-panel-heading" />
                                <div class="col-md-12">
                                    <a href="#" data-toggle="modal" data-table=".table-properties" data-target="#leads_bulk_actions" class="hide bulk-actions-btn table-btn"><?php echo _l('bulk_actions'); ?></a>
                                    <div class="modal fade" id="add_property" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Add New Property</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <script>
                                                        $(document).ready(function () {
                                                            $(".add_propert").click(function () {
                                                                var property_ref_no = $("#property_ref_no").val();
                                                                var property_title = $("#property_title").val();
                                                                var property_price = $("#property_price").val();
                                                                var property_purpose = $("#property_purpose").val();
                                                                var property_type = $("#property_type").val();
                                                                var area_sqft = $("#area_sqft").val();
                                                                var beds = $("#beds").val();
                                                                var baths = $("#baths").val();
                                                                var location = $("#location").val();
                                                                var property_country = $("#property_country").val();
                                                                var city = $("#city").val();
                                                                var communites = $("#communites").val();
                                                                var sub_communites = $("#sub_communites").val();
                                                                var developer = $("#developer").val();
                                                                var assigned = $("#assigned").val();
                                                                var parking = $("#parking").val();
                                                                var description = $("#description").val();
                                                                var image = $("#image").val();
                                                                var csrf_token_name = $("#csrf_token_name").val();
                                                                var is_public = $("#is_public").val();
                                                                var data = {
                                                                    "property_ref_no": property_ref_no,
                                                                    "property_title": property_title,
                                                                    "property_price": property_price,
                                                                    "property_purpose": property_purpose,
                                                                    "property_type": property_type,
                                                                    "area_sqft": area_sqft,
                                                                    "beds": beds,
                                                                    "baths": baths,
                                                                    "location": location,
                                                                    "property_country": property_country,
                                                                    "city": city,
                                                                    "communites": communites,
                                                                    "sub_communites": sub_communites,
                                                                    "developer": developer,
                                                                    "assigned": assigned,
                                                                    "parking": parking,
                                                                    "description": description,
                                                                    "image": image,
                                                                    "csrf_token_name": csrf_token_name,
                                                                    "is_public": is_public
                                                                };
                                                                $.ajax({
                                                                    type: "post",
                                                                    url: "<?= admin_url("properties/add_property/?action=add_proper"); ?>",
                                                                    data: data,
                                                                    success: function (msg) {
                                                                        $('.property-message').fadeOut();
                                                                        $('.property-message').html(msg).fadeIn("slow");
                                                                    }
                                                                });
                                                            });
                                                        });
                                                    </script>
                                                    <div class="col-md-12">
                                                        <div class="top-lead-menu">
                                                            <div class="horizontal-scrollable-tabs preview-tabs-top">
                                                                <div class="scroller arrow-left" style="display: none;"><i class="fa fa-angle-left"></i></div>
                                                                <div class="scroller arrow-right" style="display: none;"><i class="fa fa-angle-right"></i></div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-content">
                                                            <div role="tabpanel" class="tab-pane active" id="tab_lead_profile">
                                                                <div class="lead-wrapper">
                                                                    <div class="clearfix no-margin property-message"></div>
                                                                    <form id="property_form" method="post" accept-charset="utf-8" novalidate="novalidate" onsubmit="return false" enctype="multipart/form-data">
                                                                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" id="csrf_token_name">
                                                                        <div class="row">
                                                                            <div class="clearfix"></div>
                                                                            <div class="lead-edit">
                                                                                <div class="col-md-6">
                                                                                    <div class="form-group" app-field-wrapper="property_ref_no"><label for="property_ref_no" class="control-label">Property Ref No</label><input type="text" id="property_ref_no" readonly name="property_ref_no" class="form-control" value="<?= $this->Properties_model->createRandomCode(); ?>">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="property_title"><label for="property_title" class="control-label"> <small class="req text-danger">* </small>Property Title</label>
                                                                                        <input type="text" id="property_title" name="property_title" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="property_price"><label for="property_price" class="control-label">Price</label><input type="number" id="property_price" name="property_price" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="property_purpose"><label for="property_purpose" class="control-label">Purpose</label>
                                                                                        <select id="property_purpose" name="property_purpose" class="form-control">
                                                                                            <option value=""></option>
                                                                                            <option value="Apartment">Rent</option>
                                                                                            <option value="Villa">Sale</option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="property_type">
                                                                                        <label for="property_type" class="control-label">Type</label>
                                                                                        <select id="property_type" name="property_type" class="form-control">
                                                                                            <option value=""></option>
                                                                                            <option value="Apartment">Apartment</option>
                                                                                            <option value="Villa">Villa</option>
                                                                                            <option value="Townhouse">Townhouse</option>
                                                                                            <option value="Office">Office</option>
                                                                                            <option value="Plot">Plot</option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="area_sqft"><label for="area_sqft" class="control-label">AREA Sqft / Meter </label>
                                                                                        <input type="number" min="0" id="area_sqft" name="area_sqft" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="beds"><label for="beds" class="control-label">Beds </label>
                                                                                        <input type="number" min="1" id="beds" name="beds" class="form-control" value="1">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="baths"><label for="baths" class="control-label">Baths</label>
                                                                                        <input type="number" min="1" id="baths" name="baths" class="form-control" value="1">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <div class="form-group" app-field-wrapper="location"><label for="location" class="control-label">Location</label><input type="text" id="location" name="location" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="property_country"><label for="property_country" class="control-label">Country</label>
                                                                                        <div class="dropdown bootstrap-select bs3" style="width: 100%;"><select id="property_country" name="property_country" class="selectpicker" data-none-selected-text="Nothing selected" data-width="100%" data-live-search="true" tabindex="-98">
                                                                                                <option value=""></option>
                                                                                                <?php
                                                                                                $query = $this->db->query("select * from `tblcountries` ");
                                                                                                foreach ($query->result() as $all_countries) {
                                                                                                    echo '<option value="' . $all_countries->country_id . '">' . $all_countries->short_name . '</option>';
                                                                                                }
                                                                                                ?>
                                                                                            </select>
                                                                                            <div class="dropdown-menu open" role="combobox">
                                                                                                <div class="bs-searchbox"><input type="text" class="form-control" autocomplete="off" role="textbox" aria-label="Search"></div>
                                                                                                <div class="inner open" role="listbox" aria-expanded="false" tabindex="-1">
                                                                                                    <ul class="dropdown-menu inner "></ul>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="city"><label for="city" class="control-label">City</label><input type="text" id="city" name="city" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="communites"><label for="communites" class="control-label">Communities</label><input type="text" id="communites" name="communites" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="sub_communites"><label for="sub_communites" class="control-label">Sub Communities </label><input type="text" id="sub_communites" name="sub_communites" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="developer"><label for="developer" class="control-label">Developer </label><input type="text" id="developer" name="developer" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="assigned"><label for="assigned" class="control-label">Assigned </label><input type="text" id="assigned" name="assigned" class="form-control">
                                                                                    </div>
                                                                                    <div class="form-group" app-field-wrapper="parking"><label for="parking" class="control-label">Parking </label>
                                                                                        <input type="number" min="0" id="parking" name="parking" class="form-control" value="0">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-12">
                                                                                    <div class="form-group" app-field-wrapper="description"><label for="description" class="control-label">Description</label>
                                                                                        <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                                                                                    </div>

                                                                                    <div class="row">
                                                                                        <div class="col-md-12">
                                                                                            <div class="form-group" app-field-wrapper="file"><label for="image" class="control-label">Image </label>
                                                                                                <input type="file" id="image" name="image" class="form-control">
                                                                                            </div>
                                                                                            <div class="checkbox-inline checkbox checkbox-primary">
                                                                                                <input type="hidden" name="is_public" id="is_public" checked="checked" value="1">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-12 mtop15">
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="clearfix"></div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-info pull-right lead-save-btn add_propert" id="uytug">Save</button>
                                                    <button type="button" class="btn btn-default pull-right mright5" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="dataTables_length" id="table-properties_length">
                                                <label>
                                                    <select name="table-properties_length" aria-controls="table-properties" class="form-control input-sm" onchange="window.location = this.value;">
                                                        <option<?= (($this->input->get("items") == 5) ? " selected='selected'" : ""); ?> value="<?php echo base_url(); ?>admin/properties/?page=1&items=5<?= ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>">5</option>
                                                        <option<?= (($this->input->get("items") == 10) ? " selected='selected'" : ""); ?> value="<?php echo base_url(); ?>admin/properties/?page=1&items=10<?= ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>">10</option>
                                                        <option<?= (($this->input->get("items") == 25) ? " selected='selected'" : ""); ?> value="<?php echo base_url(); ?>admin/properties/?page=1&items=25<?= ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>">25</option>
                                                        <option<?= (($this->input->get("items") == 50) ? " selected='selected'" : ""); ?> value="<?php echo base_url(); ?>admin/properties/?page=1&items=50<?= ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>">50</option>
                                                    </select>
                                                </label>
                                            </div>
                                            <div class="dt-buttons btn-group">
                                                <button onclick="exportLeads('multipleLeadExport');" class="btn btn-default buttons-collection btn-default-dt-options" tabindex="0" aria-controls="table-properties" type="button" aria-haspopup="true" aria-expanded="false">
                                                    <span>Export</span>
                                                </button>
                                                <button class="btn btn-default btn-default-dt-options btn-dt-reload" tabindex="0" aria-controls="table-properties" type="button" data-toggle="tooltip" title="" data-original-title="Reload">
                                                    <span><i class="fa fa-refresh"></i></span>
                                                </button> 
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="dataTables_filter">
                                                <form action="<?= base_url('admin/properties/'); ?>" method="get">
                                                    <input type="hidden" name="page" value="1"/>
                                                    <input type="hidden" name="items" value="<?= $this->input->get('items'); ?>"/>
                                                    <div class="input-group">
                                                        <input id="search" name="search" type="search" class="form-control input-sm pull-right" value="<?= (($this->input->post('search')) ? $this->input->post('search') : ''); ?>" placeholder="Search..." aria-controls="table-properties">
                                                        <span class="input-group-addon" style="padding: 6px 0px;">
                                                            <button type="submit" style="border: none;background: transparent;width: 30px;"><span class="fa fa-search"></span></button>
                                                        </span>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <hr/>
                                    <style>
                                        td,th{padding:15px;text-align:center;vertical-align:middle}table#t01{width:100%;background-color:#f1f1c1}.form-control{height:30px!important;font-size:12px!important}table.table{margin-top:0;margin-bottom:0}.form-control{height:30px!important;font-size:12px!important}.container{width:110px;height:110px;margin:100px auto}.prec{top:13px;position:relative;font-size:15px}.circle{position:relative;top:5px;left:5px;text-align:center;width:45px;height:45px;border-radius:100%;background-color:#e6f4f7}.active-border{position:relative;text-align:center;width:55px;height:55px;border-radius:100%;background-color:#39b4cc;background-image:linear-gradient(91deg,transparent 50%,#a2ecfb 50%),linear-gradient(90deg,#a2ecfb 50%,transparent 50%)}.list-group{background-color:#fff;display:none;list-style-type:none;margin:0 0 0 10px;padding:0;position:absolute;width:25%;max-width:30%;max-height:350px;overflow:auto}.list-group>li{border-color:gray;border-image:none;border-style:solid solid none;border-width:1px 1px 0;padding-left:5px}.list-group>li:last-child{border-bottom:1px solid gray}.form-control:focus+.list-group{display:block}input[type=number]::-webkit-inner-spin-button,input[type=number]::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}.switch{position:relative;display:inline-block;width:75px;height:25px}.switch input{opacity:0;width:0;height:0}.switch-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#4a4c50;-webkit-transition:.4s;transition:.4s}.switch-slider:before{position:absolute;content:"";height:25px;width:25px;left:0;bottom:0;background-color:#fff;-webkit-transition:.4s;transition:.4s}.switch input:checked+.switch-slider{background-color:#9ad43c}.switch input:focus+.switch-slider{box-shadow:0 0 1px #2196f3}.switch input:checked+.switch-slider:before{-webkit-transform:translateX(5p0x);-ms-transform:translateX(50px);transform:translateX(50px)}.switch-slider.round{border-radius:40px}.switch-slider.round:before{border-radius:50%}
                                    </style>
                                    <div class="row">
                                        <table id="table-properties" class="table table-properties customizable-table dataTable no-footer" role="grid"> 
                                            <tbody>
                                                <?php
                                                if ($propertiesQuery->num_rows()) {
                                                    foreach ($propertiesQuery->result() as $property) {
                                                        $image = base_url("assets/images/default-prop.jpg");
                                                        $this->db->where("property_id", $property->prop_id);
                                                        $this->db->limit(1);
                                                        $propertyImage = $this->db->get(db_prefix() . "properties_images");
                                                        if ($propertyImage->num_rows() > 0) {
                                                            $image = base_url("uploads/properties/" . $propertyImage->row()->image_name);
                                                        }
                                                        ?>
                                                        <tr id="property_<?= $property->prop_id; ?>" role="row" class="odd" style="background-color: #f9f9f9;">
                                                            <td>
                                                                <table class="table mytable table-responsive table-bordered" role="grid" aria-describedby="table-le0ads_info" border="1" style="box-shadow: 1px 1px 5px 1px darkgrey;height:450px;">
                                                                    <thead class="alert-info has-row-options odd" role="row">
                                                                        <tr>
                                                                            <th colspan="1" rowspan="2" style="background: papayawhip;padding: 5px;">
                                                                                <img class="img img-resposive" src="<?= $image; ?>" alt="" height="90" width="95">
                                                                            </th>
                                                                            <th align="left" colspan="4" style="background: papayawhip;padding-left: 10px;font-size: 14px;color: black;"><b style="color: darkblue;font-size: 15px;">Property Title:</b> <?= $property->Property_Title; ?></th>
                                                                            <th align="left" colspan="4" style="background: papayawhip;font-size: 14px;color: black;"><b style="color: darkblue;font-size: 15px;">Property No:</b> <?= $property->Property_No; ?></th>
                                                                            <th align="right" colspan="2" style="background: papayawhip;padding-right: 20px;font-size: 14px;color: black;"><b style="color: darkblue;font-size: 15px;">Created:</b> <?= date("d F Y", strtotime($property->dateadded)); ?></th>
                                                                            <th align="right" colspan="2" style="background: papayawhip;padding-right: 20px;font-size: 14px;color: black;"><b style="color: darkblue;font-size: 15px;">Last updated:</b> <?= date("d F Y | h:i A", strtotime($property->Last_Updated)); ?></th>
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
                                                                                <button style="width: 80px;font-size: 10px;" class="btn btn-default btn-xs  btn-success" onclick="property_action(<?= $property->prop_id; ?>, 'editView')"><i class="fa fa-pencil"></i> Edit</button>
                                                                                <button style="width: 80px;font-size: 10px;" class="btn btn-default btn-xs  btn-warning"><i class="fa fa-copy"></i> Duplicate </button>
                                                                                <button style="width: 80px;font-size: 10px;" class="btn btn-default btn-xs  btn-warning"><i class="fa fa-arrow-circle-down"></i> Export </button>
                                                                                <br/><br/><br/><br/>
                                                                                <button style="width: 80px;font-size: 10px;background-color: #7c5100;" class="btn btn-default btn-xs  btn-primary" onclick="property_action(<?= $property->prop_id; ?>, 'managePropAds')"><i class="fa fa-address-card-o"></i><br>Manage Ads </button>
                                                                                <button style="width: 80px;font-size: 10px;background-color: #7c5100;" class="btn btn-default btn-xs  btn-primary" onclick="property_action(<?= $property->prop_id; ?>, 'propertyLeads')"><i class="fa fa-user"></i><br>Active Leads </button>
                                                                                <button style="width: 80px;font-size: 10px;" class="btn btn-default btn-xs  btn-danger" onclick="property_action(<?= $property->prop_id; ?>, 'delete')"><i class="fa fa-trash"></i> Delete </button>
                                                                            </th>
                                                                            <td style="font-size: 10px;"><span class="text-info-1"><?= $property->Property_Ref_No; ?></span></td>
                                                                            <td style="font-size: 10px;"><span class="text-info-1"><?= (($property->Property_Custom_REF != null && $property->Property_Custom_REF != "") ? $property->Property_Custom_REF : "NO REFERENCE"); ?></span></td>
                                                                            <td style="font-size: 10px;">
                                                                                <?= (($property->Property_purpose != null && $property->Property_purpose != "") ? ((strtolower($property->Property_purpose) == "rent") ? '<span class="text-info-1" style="border-radius: 5px;padding:5px 10px;color:#ff6f00;"><b>RENT</b></span>' : '<span class="text-info-1" style="border-radius: 5px;padding:5px 10px;color:green;"><b>SALE</b></span>') : 'UNKNOWN'); ?>
                                                                            </td>
                                                                            <td style="font-size: 10px;">
                                                                                <span class="text-info-1"><?= $property->Property_Type; ?></span>
                                                                            </td>
                                                                            <td colspan="3" style="font-size: 10px;"><?= $property->property_Location; ?></td>
                                                                            <td style="font-size: 10px;"><?= (($property->City != "" && $property->City != "") ? $property->City : "UNKNOWN"); ?></td>
                                                                            <td style="font-size: 10px;"><?php
                                                                                $this->db->where("country_id", $property->Property_Country);
                                                                                $countries = $this->db->get("tblcountries");
                                                                                if ($countries->num_rows() > 0) {
                                                                                    foreach ($countries->result() as $country) {
                                                                                        echo $country->short_name;
                                                                                    }
                                                                                } else {
                                                                                    echo "UNKNOWN";
                                                                                }
                                                                                ?></td>
                                                                            <td style="font-size: 10px;"><?= $property->Communities; ?></td>
                                                                            <td style="font-size: 10px;"><?= $property->Sub_Communities; ?></td>
                                                                            <td style="font-size: 10px;"><?= (($property->Developer != "" && $property->Developer != "") ? $property->Developer : "UNKNOWN"); ?></td>
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
                                                                            <td colspan="2" style="font-size: 10px;"><span class="text-info-1"><?= $property->Price; ?></span> AED<br/></td>
                                                                            <td colspan="2" style="font-size: 10px;">
                                                                                <span><img width="20px" style="margin-right: 10px;" src="<?= base_url("assets/images/area.png"); ?>"/> <?= $property->Property_Size_SQFT; ?>&nbsp;&nbsp;&nbsp;ft²</span>
                                                                                <br/><br/>
                                                                                <span><img width="20px" style="margin-right: 10px;" src="<?= base_url("assets/images/area.png"); ?>"/> <?= $property->Property_Size_SQM; ?>&nbsp;&nbsp;&nbsp;m²</span>
                                                                            </td>
                                                                            <td style="font-size: 10px;"><span class="text-info-1"><?= $property->Bedrooms; ?></span><br/><br/><img width="20px" src="<?= base_url("assets/images/beds.png"); ?>"/></td>
                                                                            <td style="font-size: 10px;"><span class="text-info-1"><?= $property->Bathroom; ?></span><br/><br/><img width="20px" src="<?= base_url("assets/images/baths.png"); ?>"/></td>
                                                                            <td style="font-size: 10px;"><span class="text-info-1"><?= $property->Barking; ?></span><br/><br/><img width="20px" src="<?= base_url("assets/images/parking.png"); ?>"/></td>
                                                                            <td style="font-size: 10px;text-align: center;vertical-align: middle;">
                                                                                <div align="center">
                                                                                    <?= staff_profile_image($property->assigned, $classes = ['staff-profile-image', 'img-rounded'], $type = 'small', array("width" => "35px")); ?>
                                                                                    <br/><br/>
                                                                                    <i><?= ((get_staff_full_name($property->assigned) != "" && get_staff_full_name($property->assigned) != null) ? get_staff_full_name($property->assigned) : "<br/>UNKNOWN"); ?></i>
                                                                                </div>
                                                                            </td>
                                                                            <td style = "font-size: 10px;text-align: center;" align = "center">
                                                                                <?php
                                                                                echo get_listing_view($property);
                                                                                ?>
                                                                            </td>
                                                                            <td style = "font-size: 10px;" align = "left">
                                                                                <p> <i class = "fa fa-user"></i> Leads &rightarrowtail;
                                                                                    <?php
                                                                                    $this->db->where("property_id", $property->prop_id);
                                                                                    echo $this->db->get("tblproperties_client")->num_rows();
                                                                                    ?></p>
                                                                                <p> <i class="fa fa-adn"></i> Portal Ads</p>
                                                                                <p> <i class="fa fa-share-alt"></i> MLS</p>
                                                                            </td>
                                                                            <td style="font-size: 10px;">
                                                                                <img src="<?= base_url("assets/images/facebook-round-icon.png"); ?>" alt="" align="top" border="0" height="20" width="20">
                                                                                <img src="<?= base_url("assets/images/twitter-round-icon-256.png"); ?>" alt="" align="top" border="0" height="20" width="20">
                                                                                <img src="<?= base_url("assets/images/Google-plus-circle-icon-png.png"); ?>" alt="" align="top" border="0" height="20" width="20">
                                                                                <br>
                                                                                <img src="<?= base_url("assets/images/linkedin_circle_black-512.png"); ?>" alt="" align="top" border="0" height="20" width="20">
                                                                                <img src="<?= base_url("assets/images/GrwKkd-wp-logo-whatsapp-cut-out-png.png"); ?>" alt="" align="top" border="0" height="20" width="20">
                                                                                <img src="<?= base_url("assets/images/instagraam-icon_black.png"); ?>" alt="" align="top" border="0" height="20" width="20">
                                                                            </td>
                                                                            <td style="font-size: 10px;"><img src="<?= base_url("assets/images/download-cloud-solid.png"); ?>" alt="" align="top" border="0" height="35"></td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    echo '<tr class="odd"><td valign="top" colspan="11" class="dataTables_empty">No entries found</td></tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4"><br/><br/>
                                            <?php echo "Showing " . ($start + 1) . " to " . ((($this->input->get('items') + $start) < $count) ? ($this->input->get('items') + $start) : ($count)) . " of " . $count . " entries"; ?>
                                        </div>
                                        <div class="col-md-8">
                                            <ul class="pagination pull-right">
                                                <li class="paginate_button first <?php
                                                if ($this->input->get('page') == 1) {
                                                    echo "disabled";
                                                }
                                                ?>" id="datatables_first">
                                                    <a href="<?php echo base_url() . "admin/properties/?page=1&items=" . $this->input->get('items') . ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>" aria-controls="datatables" data-dt-idx="0" tabindex="0">First</a>
                                                </li>
                                                <li class="paginate_button previous <?php
                                                if ($this->input->get('page') == 1) {
                                                    echo "disabled";
                                                }
                                                ?>" id="datatables_previous">
                                                    <a href="<?php echo base_url() . "admin/properties/?page=" . ($this->input->get('page') - 1) . "&items=" . $this->input->get('items') . ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>" aria-controls="datatables" data-dt-idx="1" tabindex="0">Previous</a>
                                                </li>
                                                <?php
                                                if ($this->input->get('page') >= ($pages - 2) && ($pages >= 7)) {
                                                    $startLoop = $this->input->get('page') - (6 - ($pages - $this->input->get('page')));
                                                    $endLoop = $pages;
                                                } else if ($this->input->get('page') >= 5 && ($pages >= 7)) {
                                                    $startLoop = $this->input->get('page') - 3;
                                                    $endLoop = $this->input->get('page') + 3;
                                                } else {
                                                    if ($pages <= 7) {
                                                        $startLoop = 1;
                                                        $endLoop = $pages;
                                                    } else {
                                                        $startLoop = 1;
                                                        $endLoop = 7;
                                                    }
                                                }
                                                for ($i = $startLoop; $i <= $endLoop; $i++) {
                                                    echo "<li class = 'paginate_button" . (($this->input->get('page') == $i) ? " active" : "") . "'>"
                                                    . "<a href='" . base_url() . "admin/properties/?page=$i&items=" . $this->input->get('items') . ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : "") . "' tabindex='0'>$i</a>"
                                                    . "</li>";
                                                }
                                                ?>
                                                <li class="paginate_button next <?php
                                                if ($this->input->get('page') == $pages) {
                                                    echo "disabled";
                                                }
                                                ?>" id="datatables_next">
                                                    <a href="<?php echo base_url() . "admin/properties/?page=" . ($this->input->get('page') + 1) . "&items=" . $this->input->get('items') . ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>" aria-controls="datatables" data-dt-idx="6" tabindex="0">Next</a>
                                                </li>
                                                <li class="paginate_button last <?php
                                                if ($this->input->get('page') == $pages) {
                                                    echo "disabled";
                                                }
                                                ?>" id="datatables_last">
                                                    <a href="<?php echo base_url() . "admin/properties/?page=" . $pages . "&items=" . $this->input->get('items') . ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>" aria-controls="datatables" data-dt-idx="7" tabindex="0">Last</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    function property_action(id, method) {
        if (localStorage.getItem("oldContent_" + id) === null) {
            localStorage.setItem("oldContent_" + id, $("#property_" + id).html())
        }
        if (method === "editView") {
            $("#property_" + id).append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
            $.ajax({type: 'POST', url: "<?= base_url('admin/properties/getEditView') ?>", data: {"id": id}, success: function (data) {
                    $("#property_" + id).html(data)
                }})
        } else if (method === "cancel") {
            $("#property_" + id).empty();
            $("#property_" + id).html(localStorage.getItem("oldContent_" + id));
            localStorage.removeItem("oldContent_" + id)
        } else if (method === "delete") {
            var confirmMessage = confirm("Are you sure you want to delete the property with id (( " + id + " ))\nPress OK to confirm DELETE!!");
            if (confirmMessage == !0) {
                $("#table-properties").append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
                $.ajax({type: 'POST', url: "<?= base_url('admin/properties/deleteSingleProp') ?>", data: {"id": id}, success: function (response) {
                        alert(response);
                        if (response == "Deleted Successfully") {
                            $("#table-properties").load(window.location.href + " #table-properties")
                        }
                    }})
            }
        } else if (method === "propertyLeads") {
            $.ajax({type: 'POST', url: "<?= base_url('admin/properties/getPreopertyLeads') ?>", data: {"id": id}, success: function (data) {
                    $("#property_" + id).html(data)
                }})
        } else if (method === "managePropAds") {
            $.ajax({type: 'POST', url: "<?= base_url('admin/properties/AdsView') ?>", data: {"id": id}, success: function (data) {
                    $("#property_" + id).html(data)
                }})
        }
    }
    function saveEdits(id) {
        $("#lead_" + id).append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
        var others = "";
        if ($("#propCentralAC_" + id).prop("checked") == !0) {
            others += "TRUE"
        } else {
            others += "FALSE"
        }
        if ($("#propCentralHeater_" + id).prop("checked") == !0) {
            others += "-TRUE"
        } else {
            others += "-FALSE"
        }
        if ($("#propStudyRoom_" + id).prop("checked") == !0) {
            others += "-TRUE"
        } else {
            others += "-FALSE"
        }
        if ($("#propBalcony_" + id).prop("checked") == !0) {
            others += "-TRUE"
        } else {
            others += "-FALSE"
        }
        if ($("#propPool_" + id).prop("checked") == !0) {
            others += "-TRUE"
        } else {
            others += "-FALSE"
        }
        if ($("#propStorage_" + id).prop("checked") == !0) {
            others += "-TRUE"
        } else {
            others += "-FALSE"
        }
        var data = {prop_id: id, prop_developer: $("#propDeveloper_" + id).val(), prop_assigned: $("#assigned_" + id).val(), prop_price: $("#propPrice_" + id).val(), prop_area: $("#propArea_" + id).val(), prop_country: $("#propCountry_" + id).val(), prop_city: $("#propCity_" + id).val(), prop_title: $("#propTitle_" + id).val(), prop_location: $("#propLocation_" + id).val(), prop_purpose: $("#propPurpose_" + id).val(), prop_type: $("#propType_" + id).val(), prop_description: $("#propDescription_" + id).val(), prop_community: $("#propCommunity_" + id).val(), prop_sub_community: $("#propSubCommunity_" + id).val(), prop_beds: $("#propBeds_" + id).val(), prop_baths: $("#propBaths_" + id).val(), prop_parking: $("#propParking_" + id).val(), others: others};
        $.ajax({type: 'POST', url: "<?= base_url('admin/properties/saveEdits') ?>", data: data, success: function (data) {
                localStorage.removeItem("oldContent_" + id);
                $("#property_" + id).html(data)
            }})
    }
    function performClick(elemId) {
        var elem = document.getElementById(elemId);
        if (elem && document.createEvent) {
            var evt = document.createEvent("MouseEvents");
            evt.initEvent("click", !1, !1);
            elem.dispatchEvent(evt)
        }
    }
    function uploadImage() {
        $.ajax({url: "<?= base_url('admin/properties/addImage') ?>", type: "POST", data: new FormData($("#property-attachment-upload")[0]), contentType: !1, cache: !1, processData: !1, success: function (data)
            {
                if (data == "Something went wrong, Please try again.") {
                    alert("Something went wrong, Please try again.")
                } else if (data == "maximum files reached") {
                    alert("Sorry, maximum files reached - Upload cancelled")
                } else {
                    $("#property_images").html(data)
                }
            }})
    }
    function delete_prop_image(id, prop) {
        $.ajax({url: "<?= base_url('admin/properties/deleteImage') ?>", type: "POST", data: "imageID=" + id + "&propertyID=" + prop, success: function (data)
            {
                if (data == "error") {
                    alert("Sorry, something wrong happened, try again.")
                } else {
                    $("#property_images").html(data)
                }
            }})
    }
    function addPropertyLead() {
        $.ajax({url: "<?= base_url('admin/properties/addPropertyLead') ?>", type: "POST", data: "leadID=" + $("#newPropertyLead").val() + "&propertyID=" + $("#propertyID").val(), success: function (data)
            {
                if (data == "error") {
                    alert("Sorry, something wrong happened, try again.")
                } else {
                    $("#propertLeadTable").html(data);
                    $("#newPropertyLead").val('');
                    $("#newPropertyLeadName").val('')
                }
            }})
    }
    function removePropertyLead(id, leadID, propertyID) {
        $.ajax({url: "<?= base_url('admin/properties/deletePropertyLead') ?>", type: "POST", data: "id=" + id + "&leadID=" + leadID + "&propertyID=" + propertyID, success: function (data)
            {
                if (data == "error") {
                    alert("Sorry, something wrong happened, try again.")
                } else {
                    $("#propertLeadTable").html(data)
                }
            }})
    }
    function propertyLeadForm(type) {
        $.ajax({url: "<?= base_url('admin/properties/getPropertyLeadForm') ?>", type: "POST", data: "formType=" + type, success: function (data)
            {
                if (data == "error") {
                    alert("Sorry, something wrong happened, try again.")
                } else {
                    $("#newLeadForm").html(data)
                }
            }})
    }
    function getLeadSearch(leadSearch) {
        $.ajax({url: "<?= base_url('admin/properties/getLeadList') ?>", type: "POST", data: "searchWith=" + leadSearch, success: function (data)
            {
                if (data == "error") {
                    alert("Sorry, something wrong happened, try again.")
                } else {
                    $("#existingLeadToProperty").html(data)
                }
            }})
    }
    function setInputValues(id, name) {
        $("#newPropertyLead").val(id);
        $("#newPropertyLeadName").val(name)
    }
    function addNewLead() {
        var formData = {newleadName: $("#newleadName").val(), newleadEmail: $("#newleadEmail").val(), newleadPhone: $("#newleadPhone").val(), newleadNationality: $("#newleadNationality").val(), newleadCategory: $("#newleadCategory").val(), newleadSource: $("#newleadSource").val(), newleadStatus: $("#newleadStatus").val(), newLeadNote: $("#newLeadNote").val(), newLeadProperty: $("#propertyID").val(), };
        $.ajax({url: "<?= base_url('admin/properties/createAddLead') ?>", type: "POST", data: formData, success: function (data)
            {
                if (data == "error") {
                    alert("Sorry, something wrong happened, try again.")
                } else {
                    $("#propertLeadTable").html(data);
                    $("#newleadName").val('');
                    $("#newleadEmail").val('');
                    $("#newleadPhone").val('');
                    $("#newleadNationality").val('');
                    $("#newleadCategory").val('');
                    $("#newleadSource").val('');
                    $("#newleadStatus").val('');
                    $("#newLeadNote").val('');
                    $("#propertyID").val('')
                }
            }})
    }
    function filter_change(filter_name) {
        var filer_id = "#filter_" + filter_name;
        if ($(filer_id).val() != null && $(filer_id).val() != "ALL" && $(filer_id).val() != "") {
            $(filer_id).attr("name", "filter_" + filter_name)
        } else {
            $(filer_id).removeAttr("name")
        }
    }

    function changePortalAccess(portalID, propertyID) {
        if ($("#portalSwitch_" + propertyID + "_" + portalID).prop("checked") == true) {
            var data = {
                propertyID: propertyID,
                portalID: portalID,
                status: "active"
            };
        } else {
            var data = {
                propertyID: propertyID,
                portalID: portalID,
                status: "disable"
            };
        }
        $.ajax({
            url: "<?= base_url('admin/properties/changePortalStatue') ?>",
            type: "POST",
            data: data,
            success: function (data)
            {
                if (data == "error") {
                    alert("Sorry, something wrong happened, try again.")
                }
            }});
    }
</script>
<script>
    var url = new URL(window.location.href);
    $(function () {
        var $slider = $("#price-slider-range");
        var priceMin = $slider.attr("data-price-min"), priceMax = $slider.attr("data-price-max");
        $("#price-filter-min, #price-filter-max").map(function () {
            $(this).attr({"min": priceMin, "max": priceMax})
        });
        var printedPriceMin = url.searchParams.get("filter_priceS");
        var printedPriceMax = url.searchParams.get("filter_priceE");
        var printedMinPrice = priceMin;
        var printedMaxPrice = priceMax;
        if (printedPriceMin != null) {
            printedMinPrice = printedPriceMin;
            $("#bed-filter-min").attr("name", "filter_priceS")
        }
        if (printedPriceMax != null) {
            printedMaxPrice = printedPriceMax;
            $("#bed-filter-max").attr("name", "filter_priceE")
        }
        $("#price-filter-min").attr({"placeholder": "MIN " + priceMin, "value": printedMinPrice});
        $("#price-filter-max").attr({"placeholder": "MAX " + priceMax, "value": printedMaxPrice});
        $slider.slider({range: !0, min: Math.max(priceMin, 0), max: priceMax, values: [printedMinPrice, printedMaxPrice], slide: function (event, ui) {
                $("#price-filter-min").val(ui.values[0]);
                $("#price-filter-max").val(ui.values[1]);
                checkValues()
            }});
        $("#price-filter-min, #price-filter-max").map(function () {
            $(this).on("input", function () {
                updateSlider();
                checkValues()
            })
        });
        function updateSlider() {
            $slider.slider("values", [$("#price-filter-min").val(), $("#price-filter-max").val()]);
            checkValues()
        }
        function checkValues() {
            if ($("#price-filter-min").val() <= priceMin) {
                $("#price-filter-min").removeAttr("name")
            } else {
                $("#price-filter-min").attr("name", "filter_priceS")
            }
            if ($("#price-filter-max").val() > (priceMax - 1)) {
                $("#price-filter-max").removeAttr("name")
            } else {
                $("#price-filter-max").attr("name", "filter_priceE")
            }
        }
    }
    );
    $(function () {
        var $slider = $("#bed-slider-range");
        var bedMin = $slider.attr("data-bed-min"), bedMax = $slider.attr("data-bed-max");
        $("#bed-filter-min, #bed-filter-max").map(function () {
            $(this).attr({"min": bedMin, "max": bedMax})
        });
        var printedBedMin = url.searchParams.get("filter_bedS");
        var printedBedMax = url.searchParams.get("filter_bedE");
        var printedMinBed = bedMin;
        var printedMaxBed = bedMax;
        if (printedBedMin != null) {
            printedMinBed = printedBedMin;
            $("#bed-filter-min").attr("name", "filter_bedS")
        }
        if (printedBedMax != null) {
            printedMaxBed = printedBedMax;
            $("#bed-filter-max").attr("name", "filter_bedE")
        }
        $("#bed-filter-min").attr({"placeholder": "MIN " + bedMin, "value": printedMinBed});
        $("#bed-filter-max").attr({"placeholder": "MAX " + bedMax, "value": printedMaxBed});
        $slider.slider({range: !0, min: Math.max(bedMin, 0), max: bedMax, values: [printedMinBed, printedMaxBed], slide: function (event, ui) {
                $("#bed-filter-min").val(ui.values[0]);
                $("#bed-filter-max").val(ui.values[1]);
                checkValues()
            }});
        $("#bed-filter-min, #bed-filter-max").map(function () {
            $(this).on("input", function () {
                updateSlider();
                checkValues()
            })
        });
        function updateSlider() {
            $slider.slider("values", [$("#bed-filter-min").val(), $("#bed-filter-max").val()]);
            checkValues()
        }
        function checkValues() {
            if ($("#bed-filter-min").val() <= bedMin) {
                $("#bed-filter-min").removeAttr("name")
            } else {
                $("#bed-filter-min").attr("name", "filter_bedS")
            }
            if ($("#bed-filter-max").val() > (bedMax - 1)) {
                $("#bed-filter-max").removeAttr("name")
            } else {
                $("#bed-filter-max").attr("name", "filter_bedE")
            }
        }
    }
    );
    $(function () {
        var $slider = $("#area-slider-range");
        var areaMin = $slider.attr("data-area-min"), areaMax = $slider.attr("data-area-max");
        $("#area-filter-min, #area-filter-max").map(function () {
            $(this).attr({"min": areaMin, "max": areaMax})
        });
        var printedAreaMin = url.searchParams.get("filter_areaS");
        var printedAreaMax = url.searchParams.get("filter_areaE");
        var printedMinArea = areaMin;
        var printedMaxArea = areaMax;
        if (printedAreaMin != null) {
            printedMinArea = printedAreaMin;
            $("#area-filter-min").attr("name", "filter_areaS")
        }
        if (printedAreaMax != null) {
            printedMaxArea = printedAreaMax;
            $("#area-filter-max").attr("name", "filter_areaE")
        }
        $("#area-filter-min").attr({"placeholder": "MIN " + areaMin, "value": printedMinArea});
        $("#area-filter-max").attr({"placeholder": "MAX " + areaMax, "value": printedMaxArea});
        $slider.slider({range: !0, min: Math.max(areaMin, 0), max: areaMax, values: [printedMinArea, printedMaxArea], slide: function (event, ui) {
                $("#area-filter-min").val(ui.values[0]);
                $("#area-filter-max").val(ui.values[1]);
                checkValues()
            }});
        $("#area-filter-min, #area-filter-max").map(function () {
            $(this).on("input", function () {
                updateSlider();
                checkValues()
            })
        });
        function updateSlider() {
            $slider.slider("values", [$("#area-filter-min").val(), $("#area-filter-max").val()]);
            checkValues()
        }
        function checkValues() {
            if ($("#area-filter-min").val() <= areaMin) {
                $("#area-filter-min").removeAttr("name")
            } else {
                $("#area-filter-min").attr("name", "filter_areaS")
            }
            if ($("#area-filter-max").val() > (areaMax - 1)) {
                $("#area-filter-max").removeAttr("name")
            } else {
                $("#area-filter-max").attr("name", "filter_areaE")
            }
        }
    }
    )
</script>
</body>
</html>