<?php
defined('BASEPATH') or exit('No direct script access allowed');

        $allowedMemory = ini_get('memory_limit');
                $allowedTime = ini_get('max_execution_time');
                ini_set('max_execution_time', 1000);
                ini_set('memory_limit', -1);
if (($this->input->get('page')) && ($this->input->get('items'))) {
    $filters = str_replace("page=" . $this->input->get('page') . "&items=" . $this->input->get('items') . "&", "", $_SERVER['QUERY_STRING']);
    $filter_inputs = explode("&", $filters);
    $filter_array = array("category", "source", "status");
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
            if ($filter_input[0] != "page" && $filter_input[0] != "items" && $filter_input[0] != "filter_comm" && $filter_input[0] != "filter_sub_comm" && $filter_input[0] != "csrf_token_name" && ($filter_input[1] != "" || $filter_input[1] != null)) {
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
                if ($filter_input[0] == "filter_comm" || $filter_input[0] == "filter_sub_comm") {
                    if ($filter_input[0] == "filter_comm") {
                        $comm = " AND tblproperties.Communities = '" . str_replace("+", " ", $filter_input[1]) . "'";
                    }
                    if ($filter_input[0] == "filter_sub_comm") {
                        $sub_comm = " AND tblproperties.Sub_Communities = '" . str_replace("+", " ", $filter_input[1]) . "'";
                    }
                }
            }
        }
        if (isset($comm) && $comm != null && $comm != "" || isset($sub_comm) && $sub_comm != null && $sub_comm != "") {
            $this->db->join("tblproperties", "tblproperties.property_lead_id = tblleads.id" . ((isset($comm) && $comm != null && $comm != "") ? $comm : "") . ((isset($sub_comm) && $sub_comm != null && $sub_comm != "") ? $sub_comm : ""));
        }
        if (isset($categoryString) && ($categoryString != NULL || $categoryString != "")) {
            $this->db->where("`category` IN (" . $categoryString . ")");
        }
        if (isset($sourceString) && ($sourceString != NULL || $sourceString != "")) {
            $this->db->where("`source` IN (" . $sourceString . ")");
        }
        if (isset($statusString) && ($statusString != NULL || $statusString != "")) {
            $this->db->where("`status` IN (" . $statusString . ")");
        }
    }
//    $query = $this->db->get("tblleads");
//    var_dump($query->result());
//    exit();
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
init_head();
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="#" onclick="init_lead(); return false;" class="btn mright5 btn-info pull-left display-block">
                                <?php echo _l('new_lead'); ?>
                            </a>
                            <?php if (is_admin() || is_admin_normal() || has_permission("leads", '', "import_leads")) { ?>
                                <a href="<?php echo admin_url('leads/import'); ?>" class="btn btn-info pull-left display-block hidden-xs">
                                    <?php echo _l('import_leads'); ?>
                                </a>
                            <?php } ?>
                            <div class="row">
                                <div class="col-md-5">
                                    <a href="#" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" data-title="<?php echo _l('leads_summary'); ?>" data-placement="bottom" onclick="slideToggle('.leads-overview'); return false;"><i class="fa fa-bar-chart"></i></a>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="row hide leads-overview">
                                <hr class="hr-panel-heading" />
                                <div class="col-md-12">
                                    <h4 class="no-margin"><?php echo _l('leads_summary'); ?></h4>
                                </div>
                                <?php foreach ($summary as $status) { ?>
                                    <div class="col-md-2 col-xs-6 border-right">
                                        <h3 class="bold">
                                            <?php
                                            if (isset($status['percent'])) {
                                                echo '<span data-toggle="tooltip" data-title="' . $status['total'] . '">' . $status['percent'] . '%</span>';
                                            } else {
                                                // Is regular status
                                                echo $status['total'];
                                            }
                                            ?>
                                        </h3>
                                        <span style="color:<?php echo $status['color']; ?>" class="<?php echo isset($status['junk']) || isset($status['lost']) ? 'text-danger' : ''; ?>"><?php echo $status['name']; ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <hr/>
                        <div class="tab-content">
                            <div class="row" id="leads-table">
                                <div class="col-md-12">
                                    <form action="<?= base_url('admin/leads/'); ?>" method="get">
                                        <input type="hidden" name="page" value="1"/>
                                        <input type="hidden" name="items" value="<?= $this->input->get('items'); ?>"/>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <p class="bold"><?php echo _l('filter_by'); ?></p>
                                            </div>
                                            <div class="col-md-3 leads-filter-column">
                                                <?php if (is_admin() || is_admin_normal() || has_permission("leads", '', "view_all")) { ?>
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
                                            <div class="col-md-3 leads-filter-column">
                                                <select id="filter_category" style="height:30px !important;font-size: 12px !important;" title="ALL CATEGORIES"<?= (($this->input->get("filter_category") != null) ? " name='filter_category'" : ""); ?> onchange="filter_change('category');" class="selectpicker" multiple="multiple" data-width="100%" data-live-search="true">
                                                    <option value='ALL'>ALL CATEGORIES</option>
                                                    <?php
                                                    $category_array = array();
                                                    for ($i = 0; $i < count($filter_inputs); $i++) {
                                                        $filter_input = explode("=", $filter_inputs[$i]);
                                                        if ($filter_input[0] == "filter_category" && !(in_array($filter_input[1], $category_array))) {
                                                            array_push($category_array, $filter_input[1]);
                                                        }
                                                    }
                                                    foreach ($this->db->get("tblleads_status")->result() as $Scategory) {
                                                        echo '<option' . ((in_array($Scategory->id, $category_array)) ? " selected='selected'" : "") . ' value="' . $Scategory->id . '">' . $Scategory->name . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 leads-filter-column">
                                                <select id="filter_source" style="height:30px !important;font-size: 12px !important;" title="SOURCE"<?= (($this->input->get("filter_source") != null) ? " name='filter_source'" : ""); ?> onchange="filter_change('source');" class="selectpicker" multiple="multiple" data-width="100%" data-live-search="true">
                                                    <option value='ALL'>ALL SOURCES</option>
                                                    <?php
                                                    $source_array = array();
                                                    for ($i = 0; $i < count($filter_inputs); $i++) {
                                                        $filter_input = explode("=", $filter_inputs[$i]);
                                                        if ($filter_input[0] == "filter_source" && !(in_array($filter_input[1], $source_array))) {
                                                            array_push($source_array, $filter_input[1]);
                                                        }
                                                    }
                                                    foreach ($this->db->get("tblleads_sources")->result() as $Fsource) {
                                                        echo '<option' . ((in_array($Fsource->id, $source_array)) ? " selected='selected'" : "") . ' value="' . $Fsource->id . '">' . $Fsource->name . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 leads-filter-column">
                                                <div class="select-placeholder">
                                                    <select title="STATUS" id="filter_status" style="height:30px !important;font-size: 12px !important;"<?= (($this->input->get("filter_status") != null) ? " name='filter_status'" : ""); ?> onchange="filter_change('status');" class="selectpicker" data-width="100%" multiple="multiple" data-width="100%" data-live-search="true">
                                                        <option value="ALL">ALL STATUSES</option>
                                                        <?php
                                                        $status_array = array();
                                                        for ($i = 0; $i < count($filter_inputs); $i++) {
                                                            $filter_input = explode("=", $filter_inputs[$i]);
                                                            if ($filter_input[0] == "filter_status" && !(in_array($filter_input[1], $status_array))) {
                                                                array_push($status_array, $filter_input[1]);
                                                            }
                                                        }
                                                        ?>
                                                        <option<?= ((in_array("Undefined", $status_array)) ? " selected='selected'" : ""); ?> value="Undefined">Undefined</option>
                                                        <option<?= ((in_array("Contacted", $status_array)) ? " selected='selected'" : ""); ?> value="Contacted">Contacted</option>
                                                        <option<?= ((in_array("Open", $status_array)) ? " selected='selected'" : ""); ?> value="Open">Open</option>
                                                        <option<?= ((in_array("Closed", $status_array)) ? " selected='selected'" : ""); ?> value="Closed">Closed</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 leads-filter-column">
                                                <div class="select-placeholder" style="margin-top: 5%;">
                                                    <select id="filter_nationality" style="height:30px !important;font-size: 12px !important;" title="NATIONALITY"<?= (($this->input->get("filter_nationality") != null) ? " name='filter_nationality'" : ""); ?> onchange="filter_change('nationality');" class="selectpicker" data-width="100%" data-live-search="true">
                                                        <option value='ALL'>ALL NATIONALITIES</option>
                                                        <?php
                                                        foreach ($this->db->get("tblcountries")->result() as $Fnationality) {
                                                            echo '<option' . (($this->input->get("filter_nationality") != null && $this->input->get("filter_nationality") == $Fnationality->country_id) ? " selected='selected'" : "") . ' value="' . $Fnationality->country_id . '">' . $Fnationality->nationality . " (" . $Fnationality->short_name . ')</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 leads-filter-column">
                                                <div class="select-placeholder" style="margin-top: 5%;">
                                                    <select id="filter_city" style="height:30px !important;font-size: 12px !important;" title="CITY"<?= (($this->input->get("filter_city") != null) ? " name='filter_city'" : ""); ?> onchange="filter_change('city');" class="selectpicker" data-width="100%" data-live-search="true">
                                                        <option value='ALL'>ALL CITIES</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3 leads-filter-column">
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
                                            <div class="col-md-3 leads-filter-column">
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
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 leads-filter-column">
                                                <div class="col-md-1 pull-right" style="padding-right: 0px;">
                                                    <input style="margin-top: 5%;" class="form-control" type="submit" value="GO"/>
                                                </div>
                                                <div class="col-md-1 pull-right" style="padding-right: 0px;">
                                                    <input style="margin-top: 5%;" class="form-control" type="button" value="RESET" onclick="window.location.href = ('<?= base_url("admin/leads/?page=" . $this->input->get("page") . "&items=" . $this->input->get("items")); ?>');"/>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="clearfix"></div>
                                <hr/>
                                <div class="col-md-12">
                                    <?php
                                    $this->load->view("admin/tables/leads");
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script id="hidden-columns-table-leads" type="text/json">
    <?php echo get_staff_meta(get_staff_user_id(), 'hidden-columns-table-leads'); ?>
</script>
<?php include_once(APPPATH . 'views/admin/leads/status.php'); ?>
<?php init_tail(); ?>
<script>
    $(function () {
        $('#leads_bulk_mark_lost').on('change', function () {
            $('#move_to_status_leads_bulk').prop('disabled', $(this).prop('checked') == true);
            $('#move_to_status_leads_bulk').selectpicker('refresh')
        });
        $('#move_to_status_leads_bulk').on('change', function () {
            if ($(this).selectpicker('val') != '') {
                $('#leads_bulk_mark_lost').prop('disabled', true);
                $('#leads_bulk_mark_lost').prop('checked', false);
            } else {
                $('#leads_bulk_mark_lost').prop('disabled', false);
            }
        });
    });
    function leadAction(id, method) {
        if (localStorage.getItem("oldContent_" + id) === null) {
            localStorage.setItem("oldContent_" + id, $("#lead_" + id).html());
        }

        if (method === "editView") {
            $("#lead_" + id).append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
            $.ajax({
                type: 'POST',
                url: "<?= base_url('admin/leads/getEditView') ?>",
                data: {"id": id},
                success: function (data) {
                    $("#lead_" + id).html(data);
                }
            });
        } else if (method === "cancel") {
            //        alert("cancel");
            $("#lead_" + id).empty();
            $("#lead_" + id).html(localStorage.getItem("oldContent_" + id));
            localStorage.removeItem("oldContent_" + id);
        } else if (method === "deleteLead") {
            var confirmMessage = confirm("Are you sure you want to delete the lead with id (( " + id + " ))\nPress OK to confirm DELETE!!");
            if (confirmMessage == true) {
                $("#table-leads").append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
                $.ajax({
                    type: 'POST',
                    url: "<?= base_url('admin/leads/deleteSingleLead') ?>",
                    data: {"id": id},
                    success: function (response) {
                        alert(response);
                        if (response == "Deleted Successfully") {
                            $("#table-leads").load(window.location.href + " #table-leads");
                        }
                    }
                });
            }
        } else if (method === "leadAttach") {
            $.ajax({
                type: 'POST',
                url: "<?= base_url('admin/leads/leadAttach') ?>",
                data: {"id": id},
                success: function (data) {
                    $("#lead_" + id).html(data);
                }
            });
        }
    }

    function saveEdits(id) {
        $("#lead_" + id).append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
        var data = {
            id: id,
            salutation: $("#salutation_" + id).val(),
            name: $("#name_" + id).val(),
            email: $("#email_" + id).val(),
            nationality: $("#nationality_" + id).val(),
            phonenumber: $("#phonenumber_" + id).val(),
            phone1: $("#phone1_" + id).val(),
            phone2: $("#phone2_" + id).val(),
            phone3: $("#phone3_" + id).val(),
            phone4: $("#phone4_" + id).val(),
            category: $("#category_" + id).val(),
            skypeid: $("#skypeid_" + id).val(),
            landline: $("#landline_" + id).val(),
            fax: $("#fax_" + id).val(),
            birthdate: $("#birthdate_" + id).val(),
            source: $("#source_" + id).val(),
            passportnumber: $("#passportnumber_" + id).val(),
            expire: $("#exipre_" + id).val(),
            email2: $("#email2_" + id).val(),
            email3: $("#email3_" + id).val(),
            assignedto: $("#assigned_" + id).val()
        };
        $.ajax({
            type: 'POST',
            url: "<?= base_url('admin/leads/saveEdits') ?>",
            data: data,
            success: function (data) {
                localStorage.removeItem("oldContent_" + id);
                $("#lead_" + id).html(data);
            }
        });
    }

    function exportData(id, exportType, fileName) {
        if (exportType == "singleLeadExport") {
            window.open("<?= base_url('admin/leads/singleLeadExport/'); ?>" + '?id=' + id + '&name=' + fileName);
        }
    }
    function exportLeads(exportType) {
        if (exportType == "multipleLeadExport") {
            var ids = "";
            $('#lead-data tr').each(function () {
                var element = $(this).children().eq(0).children().find(".checkbox").children();
                if (element.prop('checked') == true) {
                    ids += element.val() + ",";
                }
            });
            if (ids.length > 0) {
                window.open("<?= base_url('admin/leads/multipleLeadExport/'); ?>" + '?ids=' + ids);
            }
        }
    }

    function changeStatus(id) {
        $("#lead_status_" + id).append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
        var data = {
            id: id,
            newstatus: $("#lead_status_" + id).val()
        };
        $.ajax({
            type: 'POST',
            url: "<?= base_url('admin/leads/changeStatus') ?>",
            data: data,
            success: function (data) {
                $("#lead_status_" + id).html(data);
            }
        });
    }

    function changeCategory(id) {
        $("#lead_category_" + id).append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
        var data = {
            id: id,
            newcategory: $("#lead_category_" + id).val()
        };
        $.ajax({
            type: 'POST',
            url: "<?= base_url('admin/leads/changeCategory') ?>",
            data: data,
            success: function (data) {
                $("#lead_category_" + id).html(data);
            }
        });
    }

    function changeSource(id) {
        $("#lead_source_" + id).append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
        var data = {
            id: id,
            newsource: $("#lead_source_" + id).val()
        };
        $.ajax({
            type: 'POST',
            url: "<?= base_url('admin/leads/changeSource') ?>",
            data: data,
            success: function (data) {
                $("#lead_source_" + id).html(data);
            }
        });
    }

    function filter_change(filter_name) {
        var filer_id = "#filter_" + filter_name;
        if ($(filer_id).val() != null && $(filer_id).val() != "ALL" && $(filer_id).val() != "" && $(filer_id).val().indexOf("ALL") < 0) {
            $(filer_id).attr("name", "filter_" + filter_name);
        } else {
            $(filer_id).removeAttr("name");
            if (filter_name === "category" || filter_name === "source" || filter_name === "status") {
                $(filer_id).parent().find("ul li").each(function () {
                    if ($(this).html().search("ALL") < 0) {
                        $(this).removeClass("selected");
                        $(this).find("a").removeClass("selected");
                    }
                });
            }
        }
    }

    function add_note(id) {
        $.ajax({
            type: 'POST',
            url: "<?= base_url('admin/leads/getAddNote') ?>",
            data: {"id": id},
            success: function (data) {
                $("#lead_notes_" + id).html(data);
            }
        });
    }

    function submit_note(id) {
        var data = {
            id: id,
            new_note: $("#lead_new_note_" + id).val()
        };
        $.ajax({
            type: 'POST',
            url: "<?= base_url('admin/leads/addNote') ?>",
            data: data,
            success: function (data) {
                $("#lead_notes_" + id).html(data);
            }
        });
    }

    function performClick(elemId) {
        var elem = document.getElementById(elemId);
        if (elem && document.createEvent) {
            var evt = document.createEvent("MouseEvents");
            evt.initEvent("click", false, false);
            elem.dispatchEvent(evt);
        }
    }

    function uploadAttach() {
        $.ajax({
            url: "<?= base_url('admin/leads/addAttach') ?>",
            type: "POST",
            data: new FormData($("#lead-attachment-upload")[0]),
            contentType: false,
            cache: false,
            processData: false,
            success: function (data)
            {
                if (data == "Something went wrong, Please try again.") {
                    alert("Something went wrong, Please try again.");
                } else if (data == "maximum files reached") {
                    alert("Sorry, maximum files reached - Upload cancelled");
                } else {
                    $("#lead_attachments").html(data);
                }

            }
        });
    }

    function delete_lead_attachment(id, lead) {
        $.ajax({
            url: "<?= base_url('admin/leads/deleteAttach') ?>",
            type: "POST",
            data: "fileID=" + id + "&leadID=" + lead,
            success: function (data)
            {
                if (data == "error") {
                    alert("Sorry, something wrong happened, try again.");
                } else {
                    $("#lead_attachments").html(data);
                }
            }
        });
    }

    function bulkAction(action) {
        var ids = "";
        $('#lead-data tr').each(function () {
            var element = $(this).children().eq(0).children().find(".checkbox").children();
            if (element.prop('checked') == true) {
                ids += element.val() + ",";
            }
        });
        if (ids.length > 0) {
            if (action == "delete") {
                var confirmMessage = confirm("Are you sure you want to delete selected leads\nPress OK to confirm DELETE!!");
                if (confirmMessage == true) {
                    $("#table-leads").append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
                    $.ajax({
                        type: 'POST',
                        url: "<?= base_url('admin/leads/bulkAction') ?>",
                        data: {"leads_ids": ids, "method": "bulkDelete"},
                        success: function (response) {
                            alert(response);
                            if (response == "Deleted Successfully") {
                                $("#table-leads").load(window.location.href + " #table-leads");
                            }
                        }
                    });
                }
            } else if (action == "change") {
                var confirmMessage = confirm("Are you sure you want to perform bulk action for selected leads\nPress OK to confirm!!");
                if (confirmMessage == true) {
                    $("#table-leads").append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
                    $.ajax({
                        type: 'POST',
                        url: "<?= base_url('admin/leads/bulkAction') ?>",
                        data: {"leads_ids": ids, "new_status": $("#bulk_status").val(), "new_source": $("#bulk_source").val(), "new_staff": $("#bulk_staff").val(), "method": "bulkAction"},
                        success: function (response) {
                            alert(response);
                            if (response == "Updated Successfully") {
                                $("#table-leads").load(window.location.href + " #table-leads");
                            }
                        }
                    });
                }
            }
        } else {
            alert("NO LEADS SELECTED !!!");
        }
    }
    function selectAll() {
        $('#lead-data tr').each(function () {
            var element = $(this).children().eq(0).children().find(".checkbox").children();
            element.prop('checked', true);
            $("#selectAllBtn").find("span").html("DESELECT ALL");
            $("#selectAllBtn").attr("onclick", "deSelectAll();");
        });
    }
    function deSelectAll() {
        $('#lead-data tr').each(function () {
            var element = $(this).children().eq(0).children().find(".checkbox").children();
            element.prop('checked', false);
            $("#selectAllBtn").find("span").html("SELECT ALL");
            $("#selectAllBtn").attr("onclick", "selectAll();");
        });
    }
</script>
</body>
</html>