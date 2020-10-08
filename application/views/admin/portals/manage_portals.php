<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="javascript::();" onclick="new_portal(); return false;" class="btn btn-info pull-left display-block">NEW PORTAL</a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <table id="portals-table" class="table dt-table scroll-responsive" data-order-col="1" data-order-type="asc">
                            <thead>
                            <th>IMAGE</th>
                            <th>REFERENCE ID</th>
                            <th>PORTAL NAME</th>
                            <th>PORTAL WEBSiTE</th>
                            <th>STATUS</th>
                            <th>XML FEED URL</th>
                            <th>CRON JOB SYNCHRONIZATION</th>
                            <th>CREATED AT</th>
                            <th>ACTIONS</th>
                            </thead>
                            <tbody id="portals-list">
                                <?php foreach ($portals as $portal) { ?>
                                    <tr id="portal_<?= $portal['id']; ?>">
                                        <td><img width="50px;" height="50px;" class="img img-rounded" src="<?= base_url("uploads/portals/" . $portal['P_image']); ?>"/></td>
                                        <td><?= $portal['P_ref_id']; ?></td>
                                        <td><?= $portal['P_name']; ?></td>
                                        <td><?= $portal['P_website']; ?></td>
                                        <td><?= $portal['P_status']; ?></td>
                                        <td><?= $portal['P_xml_url']; ?></td>
                                        <td><?= $portal['P_cronjob_scyn']; ?></td>
                                        <td><?= date("d F Y", strtotime($portal['P_created_at'])); ?></td>
                                        <td>
                                            <a href="javascript::();" onclick="portal_action(<?= $portal['id']; ?>, 'edit');" data-name="<?= $portal['P_name']; ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                                            <a href="javascript::();" onclick="portal_action(<?= $portal['id']; ?>, 'delete');" class="btn btn-danger btn-icon"><i class="fa fa-remove"></i></a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    function new_portal() {
        if ($("#portals-table").find(' tbody tr:first').attr('id') !== 'new_portal') {
            $("<tr id='new_portal'><td colspan='9'><form id='new-portal-form' method='post' accept-charset='utf-8' enctype='multipart/form-data'><table style='width:100%;'><tbody>"
                    + "<td class='text text-center'><input type='file' style='float:left' id='portal_image' name='portal_image' class='form-control' required='required'/></td>"
                    + "<td class='text text-center'><input type='text' style='float:left' class='form-control' id='newPortal_name' name='newPortal_name' placeholder='PORTAL NAME' title='PORTAL NAME' style='width:100%;' required='required'/></td>"
                    + "<td class='text text-center'><input type='text' style='float:left' class='form-control' id='newPortal_website' name='newPortal_website' placeholder='PORTAL WEBSITE' title='PORTAL WEBSITE' style='width:100%;' required='required'/></td>"
                    + "<td class='text text-center'><select id='newPortal_status' style='float:left' name='newPortal_status' class='form-control' style='width:100%;' required='required'><option value='ACTIVE'>ACTIVE</option><option value='DISABLED'>DISABLED</option></select></td>"
                    + "<td class='text text-center'><input type='text' style='float:left' class='form-control' id='newPortal_xml_feed' name='newPortal_xml_feed' placeholder='PORTAL XML FEED' title='PORTAL XML FEED' style='width:100%;' required='required'/></td>"
                    + "<td class='text text-center'><input type='text' style='float:left' class='form-control' id='newPortal_cron_scyn' name='newPortal_cron_scyn' placeholder='PORTAL CRON SYNCHRONIZATION' title='PORTAL CRON SYNCHRONIZATION' style='width:100%;' required='required'/></td>"
                    + "<td class='text text-center'><a href='javascript::();' style='float:left' onclick='add_portal();' class='btn btn-success btn-large'><i class='fa fa-right'></i> SAVE</a>"
                    + "</tblody></table></form></td></tr>").prependTo("table > tbody");
        }
    }

    function portal_action(id, action) {
        if (action == "edit") {
            alert("editing");
        } else if (action == "delete") {
            var confirmMessage = confirm("Are you sure you want to delete the portal with id (( " + id + " ))\nPress OK to confirm DELETE!!");
            if (confirmMessage == true) {
                $("#portals-table").append('<div id="table-tickets_processing" class="dataTables_processing panel panel-default" style="display: block;"><div class="dt-loader"></div></div>');
                $.ajax({
                    type: 'POST',
                    url: "<?= base_url('admin/portals/deletePortal') ?>",
                    data: {"id": id},
                    success: function (response) {
                        if (response == "Something wrong happened, try again.") {
                            alert(response);
                        } else {
                            $("#portals-list").html(response);
                        }
                    }
                });
            }
        }
    }

    function add_portal() {
        $.ajax({
            url: "<?= base_url('admin/portals/addPortal') ?>",
            type: "POST",
            method: "POST",
            data: new FormData($("#new-portal-form")[0]),
            contentType: false,
            cache: false,
            processData: false,
            success: function (data)
            {
                if (data == "Something went wrong, Please try again.") {
                    alert("Something went wrong, Please try again.");
                } else {
                    $("#portals-list").html(data);
                }

            }
        });
    }
</script>
</body>
</html>
