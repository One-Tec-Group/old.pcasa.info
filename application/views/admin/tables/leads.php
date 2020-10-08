<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="table-leads">
    <div class="row">
        <?php
        
        $allowedMemory = ini_get('memory_limit');
                $allowedTime = ini_get('max_execution_time');
                ini_set('max_execution_time', 1000);
                ini_set('memory_limit', -1);
        if (($this->input->get('page')) && ($this->input->get('items'))) {
            if (is_admin() || is_admin_normal() || has_permission("leads", '', "view_all")) {
                
            } else {
                $this->db->where("assigned", get_staff_user_id());
            }
            $filters = str_replace("page=" . $this->input->get('page') . "&items=" . ($this->input->get('items')), "", $_SERVER['QUERY_STRING']);
            $filter_inputs = explode("&", $filters);
            $filter_array = array("category", "source", "status");
            if ($this->input->get("search") != NULL && $this->input->get("search") != "") {
                $this->db->like("name", $this->input->get("search"));
                $this->db->or_like("email", $this->input->get("search"));
                $this->db->or_like("phonenumber", $this->input->get("search"));
            } else {
                for ($i = 0; $i < count($filter_inputs); $i++) {
                    $filter_input = explode("=", $filter_inputs[$i]);
                    if ($filter_input[0] != "page" && $filter_input[0] != "items" && $filter_input[0] != "filter_comm" && $filter_input[0] != "filter_sub_comm" && $filter_input[0] != "csrf_token_name" && (isset($filter_input[1]) && $filter_input[1] != null && $filter_input[1] != "")) {
                        if (in_array(str_replace("filter_", "", $filter_input[0]), $filter_array)) {
                            if (str_replace("filter_", "", $filter_input[0]) == "category") {
                                if ($categoryString != NULL || $categoryString != "") {
                                    $categoryString .= ",";
                                }
                                if (!isset($categoryString)) {
                                    $categoryString = "";
                                }
                                $categoryString .= $filter_input[1];
                            } else if (str_replace("filter_", "", $filter_input[0]) == "source") {
                                if ($sourceString != NULL || $sourceString != "") {
                                    $sourceString .= ",";
                                }
                                if (!isset($sourceString)) {
                                    $sourceString = "";
                                }
                                $sourceString .= $filter_input[1];
                            } else if (str_replace("filter_", "", $filter_input[0]) == "status") {
                                if ($statusString != NULL || $statusString != "") {
                                    $statusString .= ",";
                                }
                                if (!isset($statusString)) {
                                    $statusString = "";
                                }
                                $statusString .= "'" . $filter_input[1] . "'";
                            }
                        } else {
                            $this->db->where(str_replace("filter_", "", $filter_input[0]), $filter_input[1]);
                        }
                    }
                    if ($filter_input[0] == "filter_comm" || $filter_input[0] == "filter_sub_comm") {
                        if ($filter_input[0] == "filter_comm") {
                            $comm = " AND tblproperties.Communities = '" . str_replace("+", " ", $filter_input[1]) . "'";
                        }
                        if ($filter_input[0] == "filter_sub_comm") {
                            $sub_comm = " AND tblproperties.Sub_Communities = '" . str_replace("+", " ", $filter_input[1]) . "'";
                        }
                    }
                }
                if (isset($comm) && $comm != null && $comm != "" || isset($sub_comm) && $sub_comm != null && $sub_comm != "") {
                    $this->db->join("tblproperties", "tblproperties.property_lead_id = tblleads.id" . ((isset($comm) && $comm != null && $comm != "") ? $comm : "") . ((isset($sub_comm) && $sub_comm != null && $sub_comm != "") ? $sub_comm : ""));
                }
                if (isset($categoryString) && $categoryString != null && $categoryString != "") {
                    $this->db->where("`category` IN (" . $categoryString . ")");
                }
                if (isset($sourceString) && $sourceString != null && $sourceString != "") {
                    $this->db->where("`source` IN (" . $sourceString . ")");
                }
                if (isset($statusString) && $statusString != null && $statusString != "") {
                    $this->db->where("`status` IN (" . $statusString . ")");
                }
            }
            $count = $this->db->get("tblleads")->num_rows();
            $pages = ($count / $this->input->get('items'));
            if ($pages > intval($pages)) {
                $pages = intval($pages + 1);
            }
            if ($pages == 0) {
                $pages++;
            }
            if ($this->input->get('page') && (!($pages >= $this->input->get('page')))) {
                redirect(base_url() . "admin/leads/?page=1&items=5", "refresh");
            }
        } else {
            redirect(base_url() . "admin/leads/?page=1&items=5", "refresh");
        }
        if (is_admin() || is_admin_normal() || has_permission("leads", '', "view_all")) {
            
        } else {
            $this->db->where("assigned", get_staff_user_id());
        }
        if ($this->input->get("search") != NULL && $this->input->get("search") != "") {
            $this->db->like("name", $this->input->get("search"));
            $this->db->or_like("email", $this->input->get("search"));
            $this->db->or_like("phonenumber", $this->input->get("search"));
        } else {
            for ($i = 0; $i < count($filter_inputs); $i++) {
                $filter_input = explode("=", $filter_inputs[$i]);
                if ($filter_input[0] != "page" && $filter_input[0] != "items" && $filter_input[0] != "filter_comm" && $filter_input[0] != "filter_sub_comm" && $filter_input[0] != "csrf_token_name" && (isset($filter_input[1]) && $filter_input[1] != "" && $filter_input[1] != null)) {
                    if (!(in_array(str_replace("filter_", "", $filter_input[0]), $filter_array))) {
                        $this->db->where(str_replace("filter_", "", $filter_input[0]), $filter_input[1]);
                    }
                }
                if ($filter_input[0] == "filter_comm" || $filter_input[0] == "filter_sub_comm") {
                    if ($filter_input[0] == "filter_comm") {
                        $comm = " AND tblproperties.Communities = '" . str_replace("+", " ", $filter_input[1]) . "'";
                    }
                    if ($filter_input[0] == "filter_sub_comm") {
                        $sub_comm = " AND tblproperties.Sub_Communities = '" . str_replace("+", " ", $filter_input[1]) . "'";
                    }
                }
            }
            if (isset($comm) && $comm != null && $comm != "" || isset($sub_comm) && $sub_comm != null && $sub_comm != "") {
                $this->db->join("tblproperties", "tblproperties.property_lead_id = tblleads.id" . ((isset($comm) && $comm != null && $comm != "") ? $comm : "") . ((isset($sub_comm) && $sub_comm != null && $sub_comm != "") ? $sub_comm : ""));
            }
            if (isset($categoryString) && $categoryString != null && $categoryString != "") {
                $this->db->where("`category` IN (" . $categoryString . ")");
            }
            if (isset($sourceString) && $sourceString != null && $sourceString != "") {
                $this->db->where("`source` IN (" . $sourceString . ")");
            }
            if (isset($statusString) && $statusString != null && $statusString != "") {
                $this->db->where("`status` IN (" . $statusString . ")");
            }
        }
        $start = (($this->input->get('page') - 1) * $this->input->get('items'));
        $this->db->LIMIT($this->input->get('items'));
        $this->db->OFFSET($start);
        $query = $this->db->get("tblleads");
        ?>
        <div class="col-md-7">
            <div class="dataTables_length" id="table-leads_length">
                <label>
                    <select name="table-leads_length" aria-controls="table-leads" class="form-control input-sm" onchange="window.location = this.value;">
                        <option<?= (($this->input->get("items") == 5) ? " selected='selected'" : ""); ?> value="<?php echo base_url(); ?>admin/leads/?page=1&items=5<?= ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>">5</option>
                        <option<?= (($this->input->get("items") == 10) ? " selected='selected'" : ""); ?> value="<?php echo base_url(); ?>admin/leads/?page=1&items=10<?= ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>">10</option>
                        <option<?= (($this->input->get("items") == 25) ? " selected='selected'" : ""); ?> value="<?php echo base_url(); ?>admin/leads/?page=1&items=25<?= ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>">25</option>
                        <option<?= (($this->input->get("items") == 50) ? " selected='selected'" : ""); ?> value="<?php echo base_url(); ?>admin/leads/?page=1&items=50<?= ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>">50</option>
                    </select>
                </label>
            </div>
            <div class="dt-buttons btn-group">
                <button onclick="selectAll();" class="btn btn-default buttons-collection btn-default-dt-options" id="selectAllBtn">
                    <span>SELECT ALL</span>
                </button>
                <?php if (get_option('show_table_export_button') == 'to_all' || (get_option('show_table_export_button') == 'only_admins' && (is_admin_normal() || is_admin()))) { ?>
                    <button onclick="exportLeads('multipleLeadExport');" class="btn btn-default buttons-collection btn-default-dt-options" tabindex="0" aria-controls="table-leads" type="button" aria-haspopup="true" aria-expanded="false">
                        <span>Export</span>
                    </button>
                <?php } ?>
                <button class="btn btn-default btn-default-dt-options" data-toggle="collapse" href="#bulk-action" aria-expanded="true" aria-controls="bulk-action"><span><?= _l('bulk_actions'); ?></span></button>
            </div>
        </div>
        <div class="col-md-5">
            <div class="dataTables_filter">
                <form action="<?= base_url('admin/leads/'); ?>" method="get">
                    <input type="hidden" name="page" value="1"/>
                    <input type="hidden" name="items" value="<?= $this->input->get('items'); ?>"/>
                    <div class="input-group">
                        <input id="search" name="search" type="search" class="form-control input-sm pull-right" value="<?= (($this->input->post('search')) ? $this->input->post('search') : ''); ?>" placeholder="Search..." aria-controls="table-leads">
                        <span class="input-group-addon" style="padding: 6px 0px;">
                            <button type="submit" style="border: none;background: transparent;width: 30px;"><span class="fa fa-search"></span></button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        <div id="bulk-action" class="collapse">
            <div class="card-body">
                <div class="row" align="center">
                    <div class="col-md-12">
                        <div class="col-md-12">
                            <?php
                            if (is_admin() || is_admin_normal()) {
                                echo '<div class="col-md-1"></div>';
                            } else {
                                echo '<div class="col-md-2"></div>';
                            }
                            ?>
                            <div class="col-md-2" style="padding-top:15px;">
                                <select id="bulk_status" style="height:30px !important;font-size: 12px !important;" class="selectpicker" data-width="100%" data-width="100%" data-live-search="true">
                                    <option value disabled selected>SELECT STATUS</option>
                                    <option value="Undefined">Undefined</option>
                                    <option value="Contacted">Contacted</option>
                                    <option value="Open">Open</option>
                                    <option value="Closed">Closed</option>
                                </select>
                            </div>
                            <div class="col-md-1 vertical_seperator"></div>
                            <div class="col-md-2" style="padding-top:15px;" align="center">
                                <select id="bulk_source" style="height:30px !important;font-size: 12px !important;" class="selectpicker" data-width="100%" data-live-search="true">
                                    <option value disabled selected>SELECT SOURCE</option>
                                    <?php
                                    foreach ($this->db->get("tblleads_sources")->result() as $Fsource) {
                                        echo '<option value="' . $Fsource->id . '">' . $Fsource->name . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-1 vertical_seperator"></div>
                            <div class="col-md-2" style="padding-top:15px;" align="center">
                                <select id="bulk_staff" style="height:30px !important;font-size: 12px !important;" class="selectpicker" data-width="100%" data-live-search="true">
                                    <option value disabled selected>SELECT STAFF</option>
                                    <?php
                                    foreach ($this->db->get("tblstaff")->result() as $stuff) {
                                        echo '<option value="' . $stuff->staffid . '">' . $stuff->firstname . ' ' . $stuff->lastname . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-1 vertical_seperator"></div>
                            <div class="col-md-1" style="padding-top:8px;">
                                <a href="javascript::();" onclick="bulkAction('change');" class="btn btn-primary">GO</a>
                            </div>
                            <?php if (is_admin() || is_admin_normal()) { ?>
                                <div class="col-md-2" style="padding-top:8px;">
                                    <a href="javascript::();" onclick="bulkAction('delete');" class="btn btn-danger">MASS DELETE</a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr/>
    <style>
        .tablebtn{
            padding: 5px 10px;
            margin-bottom: 4px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            text-shadow: 0 1px 1px rgba(0,0,0,0.1);
            border-radius: 3px;
            text-align: center;
            font-weight: 200;
            font-size: 10px;
            width: 85px;
        }
        table.table{
            margin-top: 0px;
            margin-bottom: 0px;
        }
        .form-control{
            height:30px !important;
            font-size: 12px !important;
        }
        .lead_status,.lead_source,.lead_category{
            -moz-appearance: window;
            -webkit-appearance: none;
        }
        .overlay {  
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }
        .vertical_seperator{
            border-right: 1px solid #eee;
            height: 50px;
            margin-left: 20px;
            margin-right: 20px;
            display: inline-block;
            width: 0px !important;
            border-left: 1px solid #eee;
        }
    </style>
    <div class="row">
        <table class="table table-leads customizable-table dataTable no-footer" role="grid">
            <tbody id="lead-data">
                <?php
                if ($query->num_rows() > 0) {
                    foreach ($query->result() as $lead) {
                        ?>   
                        <tr id="lead_<?= $lead->id; ?>" role="row" class="odd" style="background-color: #f9f9f9;">
                            <td>
                                <table class="table table-bordered table-responsive" style="width: 100%;max-width: 100%;font-size: 10px;box-shadow: 1px 1px 5px 1px darkgrey;">
                                    <thead>
                                        <tr>
                                            <th class="text text-center" style="padding-bottom: 10px;font-size: 10px;width: 1px;">
                                                <div class="checkbox" align="center"><input type="checkbox" value="<?= $lead->id; ?>"><label></label></div>
                                            </th>
                                            <th style="width: 12px;font-size: 10px;vertical-align: middle;text-align: center;color: darkblue;" class="text text-center"><b>#</b></th>
                                            <th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>NAME</b></th>
                                            <th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>E-MAIL</b></th>
                                            <th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>PHONE</b></th>
                                            <th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>ASSIGNED USER</b></th>
                                            <th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>NATIONALITY</b></th>
                                            <th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>CATEGORY</b></th>
                                            <th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>SOURCE</b></th>
                                            <th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>STATUS</b></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr style="height: 100px;">
                                            <td class="text text-center" rowspan="2">
                                                <a href="javascript::();" onclick="leadAction(<?= $lead->id; ?>, 'editView');" class="btn btn-success tablebtn"><i class="fa fa-edit"></i> Edit</a>
                                                <?php if (get_option('show_table_export_button') == 'to_all' || (get_option('show_table_export_button') == 'only_admins' && (is_admin_normal() || is_admin()))) { ?>
                                                    <a href="javascript::();" onclick="exportData(<?= $lead->id; ?>, 'singleLeadExport', '<?= $lead->name; ?>');" class="btn btn-warning tablebtn"><i class="fa fa-download"></i> Export</a>
                                                    <?php
                                                } else {
                                                    $noExport = TRUE;
                                                }
                                                ?>
                                                <a href="javascript::();" onclick="leadAction(<?= $lead->id; ?>, 'leadAttach');" class="btn btn-primary tablebtn"><i class="fa fa-file-o"></i> Files</a>
                                                <?php if (is_admin() || is_admin_normal()) { ?>
                                                    <a href="javascript::();" onclick="leadAction(<?= $lead->id; ?>, 'deleteLead');" class="btn btn-danger tablebtn"><i class="fa fa-trash-o"></i> Delete</a>
                                                <?php } ?>
                                                <?= ((isset($noExport) && $noExport == TRUE) ? "<br/><br/><br/><br/><br/><br/>" : "<br/><br/><br/><br/>"); ?>
                                                <a href="javascript::();" onclick="alert('Tracking')" class="btn btn-info tablebtn"><i class="fa fa-flag-checkered"></i><br/>Tracking</a>
                                                <a href="<?= base_url("admin/properties/?page=1&items=5&leadID=" . $lead->id); ?>" class="btn btn-primary tablebtn"><i class="fa fa-cog"></i><br/>Properties</a>
                                            </td>
                                            <td rowspan="2" class="text text-center" style="vertical-align: middle;"><?= $lead->id; ?></td>
                                            <td style="padding-left: 10px;width: 120px;"><?= (($lead->salutation != "" && $lead->salutation != null) ? $lead->salutation . ". " : "") . $lead->name; ?></td>
                                            <td style="padding-left: 10px;width: 120px;"><?= $lead->email; ?></td>
                                            <td style="padding-left: 10px;width: 200px;"><?= get_phone_number_info($lead->phonenumber); ?></td>
                                            <td style="padding-left: 10px;text-align: center;">
                                                <div align="center">
                                                    <?= staff_profile_image($lead->assigned, $classes = ['staff-profile-image', 'img-rounded'], $type = 'small', array("width" => "35px")); ?>
                                                    <br/>
                                                    <i><?= ((get_staff_full_name($lead->assigned) != "" && get_staff_full_name($lead->assigned) != null) ? get_staff_full_name($lead->assigned) : "<br/>UNKNOWN"); ?></i>
                                                </div>
                                            </td>
                                            <td style="padding-left: 10px;width:120px;vertical-align: middle;"><?= get_lead_nationality($lead->nationality); ?></td>
                                            <td style="padding-left: 10px;width:100px;vertical-align: middle;">
                                                <select id="lead_category_<?= $lead->id; ?>" name="lead_category_<?= $lead->id; ?>" title="CATEGORY" class="form-control lead_category" onchange="changeCategory(<?= $lead->id; ?>);" style="width: 100px;">
                                                    <option value disabled>CATEGORY</option>
                                                    <?php
                                                    foreach ($this->db->get("tblleads_status")->result() as $category) {
                                                        echo '<option' . (($category->id == $lead->category) ? ' selected="selected"' : '') . ' value="' . $category->id . '">' . $category->name . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td style="padding-left: 10px;width:120px;vertical-align: middle;">
                                                <select id="lead_source_<?= $lead->id; ?>" name="lead_source_<?= $lead->id; ?>" title="SOURCE" class="form-control lead_source" onchange="changeSource(<?= $lead->id; ?>);" style="width: 100px;">
                                                    <option value disabled>SOURCE</option>
                                                    <?php
                                                    foreach ($this->db->get("tblleads_sources")->result() as $source) {
                                                        echo '<option' . (($source->id == $lead->source) ? ' selected="selected"' : '') . ' value="' . $source->id . '">' . $source->name . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td style="padding-left: 10px;width:140px;vertical-align: middle;">
                                                <select id="lead_status_<?= $lead->id; ?>" name="lead_status_<?= $lead->id; ?>" title="STATUS" class="form-control lead_status" onchange="changeStatus(<?= $lead->id; ?>);" style="width: 100px;">
                                                    <option value disabled>STATUS</option>
                                                    <option<?= (($lead->status == "Undefined") ? " selected='selected'" : ""); ?> value="Undefined">Undefined</option>
                                                    <option<?= (($lead->status == "Contacted") ? " selected='selected'" : ""); ?> value="Contacted">Contacted</option>
                                                    <option<?= (($lead->status == "Open") ? " selected='selected'" : ""); ?> value="Open">Open</option>
                                                    <option<?= (($lead->status == "Closed") ? " selected='selected'" : ""); ?> value="Closed">Closed</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr style="height: 100px;">
                                            <td colspan="1" style="padding: 0px;">
                                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                                    <tbody>
                                                    <br/>
                                                    <tr><td style="padding-left: 10px;border: 0px;"><b style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">CREATED</b><br/><?= date("d - M - Y", strtotime($lead->dateadded)); ?></td></tr>
                                                    <tr><td style="padding-left: 10px;border: 0px;"><b style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">MODIFIED</b><br/><?= date("d - M - Y", strtotime($lead->datemodified)); ?></td></tr>
                                    </tbody>
                                </table>
                            </td>
                            <td colspan="2" style="padding: 0px;width: auto;">
                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                    <thead>
                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>NOTES</b></th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td style="padding-left: 10px;" id="lead_notes_<?= $lead->id; ?>">
                                                <?php
                                                $this->db->where("rel_id", $lead->id);
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
                                                ?><i class="fa fa-plus-square" style="font-size:15px;color:green;margin-left:98%;cursor:pointer;" onclick="add_note(<?= $lead->id; ?>);"></i>
                                            </td></tr>
                                    </tbody>
                                </table>
                            </td>
                            <td colspan="1" style="padding: 0px;width:140px;">
                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                    <thead>
                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>REMINDERS</b></th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td style="padding-left: 10px;"><?= $lead->reminders; ?></td></tr>
                                    </tbody>
                                </table>
                            </td>
                            <?php
                            $this->db->where("property_lead_id", $lead->id);
                            $leadProps = $this->db->get("tblproperties");
                            ?>
                            <td colspan="1" style="padding: 0px;width: 120px;">
                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                    <thead>
                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>PROPERTIES</b></th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td style="padding-left: 10px;font-size: 20px;text-align: center;vertical-align:middle;">
                                                <?php
                                                $this->db->join("tblproperties", "tblproperties.property_lead_id = tblleads.id AND tblleads.id = '" . $lead->id . "'");
                                                echo $this->db->get("tblleads")->num_rows();
                                                ?>
                                                <i class="fa fa-building" style="font-size: 25px;"></i></td></tr>
                                    </tbody>
                                </table>
                            </td>
                            <td colspan="1" style="padding: 0px;width: 100px;">
                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                    <thead>
                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>CITY</b></th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td style="padding-left: 10px;"><?= $lead->city; ?></td></tr>
                                    </tbody>
                                </table>
                            </td>
                            <td colspan="1" style="padding: 0px;width:120px;">
                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                    <thead>
                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>COMMUNITIES</b></th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td style="padding-left: 10px;"><?php
                                                if ($leadProps->num_rows() > 0) {
                                                    $found = 0;
                                                    $comm = "";
                                                    foreach ($leadProps->result() as $property) {
                                                        if ($property->Communities != null && $property->Communities != "") {
                                                            $comm .= "<li>" . $property->Communities . "</li>";
                                                            $found++;
                                                        }
                                                    }
                                                    if ($found > 0) {
                                                        echo '<ul style="list-style-type:square;margin-left:3%;">' . $comm . '</ul>';
                                                    } else {
                                                        echo "No Communtinities found";
                                                    }
                                                } else {
                                                    echo "No Communtinities found";
                                                }
                                                ?></td></tr>
                                    </tbody>
                                </table>
                            </td>
                            <td colspan="1" style="padding: 0px;width:140px;">
                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                    <thead>
                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>SUB COMMUNITIES</b></th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td style="padding-left: 10px;"><?php
                                                if ($leadProps->num_rows() > 0) {
                                                    $found = 0;
                                                    $comm = "";
                                                    foreach ($leadProps->result() as $property) {
                                                        if ($property->Sub_Communities != null && $property->Sub_Communities != "") {
                                                            $comm .= "<li>" . $property->Sub_Communities . "</li>";
                                                            $found++;
                                                        }
                                                    }
                                                    if ($found > 0) {
                                                        echo '<ul style="list-style-type:square;margin-left:3%;">' . $comm . '</ul>';
                                                    } else {
                                                        echo "No SUB-Communtinities found";
                                                    }
                                                } else {
                                                    echo "No SUB-Communtinities found";
                                                }
                                                ?></td></tr>
                                    </tbody>
                                </table>
                            </td>
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
                    <a href="<?php echo base_url() . "admin/leads/?page=1&items=" . $this->input->get('items') . ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>" aria-controls="datatables" data-dt-idx="0" tabindex="0">First</a>
                </li>
                <li class="paginate_button previous <?php
                if ($this->input->get('page') == 1) {
                    echo "disabled";
                }
                ?>" id="datatables_previous">
                    <a href="<?php echo base_url() . "admin/leads/?page=" . ($this->input->get('page') - 1) . "&items=" . $this->input->get('items') . ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>" aria-controls="datatables" data-dt-idx="1" tabindex="0">Previous</a>
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
                    . "<a href='" . base_url() . "admin/leads/?page=$i&items=" . $this->input->get('items') . ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : "") . "' tabindex='0'>$i</a>"
                    . "</li>";
                }
                ?>
                <li class="paginate_button next <?php
                if ($this->input->get('page') == $pages) {
                    echo "disabled";
                }
                ?>" id="datatables_next">
                    <a href="<?php echo base_url() . "admin/leads/?page=" . ($this->input->get('page') + 1) . "&items=" . $this->input->get('items') . ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>" aria-controls="datatables" data-dt-idx="6" tabindex="0">Next</a>
                </li>
                <li class="paginate_button last <?php
                if ($this->input->get('page') == $pages) {
                    echo "disabled";
                }
                ?>" id="datatables_last">
                    <a href="<?php echo base_url() . "admin/leads/?page=" . $pages . "&items=" . $this->input->get('items') . ((isset($filters) && $filters != null && $filters != "") ? "&" . $filters : ""); ?>" aria-controls="datatables" data-dt-idx="7" tabindex="0">Last</a>
                </li>
            </ul>
        </div>
    </div>
</div>