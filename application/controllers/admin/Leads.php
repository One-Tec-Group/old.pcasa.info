<?php

header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

class Leads extends AdminController {

    public function __construct() {
        parent::__construct();
        $this->load->model('leads_model');
    }

    /* List all leads */

    public function index($id = '') {
        //close_setup_menu();

        if (!is_staff_member()) {
            access_denied('Leads');
        }

        $data['switch_kanban'] = true;

        if ($this->session->userdata('leads_kanban_view') == 'true') {
            $data['switch_kanban'] = false;
            $data['bodyclass'] = 'kan-ban-body';
        }

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }
        $data['summary'] = get_leads_summary();
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources'] = $this->leads_model->get_source();
        $data['title'] = _l('leads');
// in case accesed the url leads/index/ directly with id - used in search
        $data['leadid'] = $id;
        $this->load->view('admin/leads/manage_leads', $data);
    }

    public function table() {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        $this->app->get_table_data('leads');
    }

    public function kanban() {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        $data['statuses'] = $this->leads_model->get_status();
        echo $this->load->view('admin/leads/kan-ban', $data, true);
    }

    /* Add or update lead */

    public function lead($id = '') {
        if (!is_staff_member() || ($id != '' && !$this->leads_model->staff_can_access_lead($id))) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            if ($id == '') {
                $id = $this->leads_model->add($this->input->post());
                $message = $id ? _l('added_successfully', _l('lead')) : '';

                echo json_encode([
                    'success' => $id ? true : false,
                    'id' => $id,
                    'message' => $message,
                    'leadView' => $id ? $this->_get_lead_data($id) : [],
                ]);
            } else {
                $emailOriginal = $this->db->select('email')->where('id', $id)->get(db_prefix() . 'leads')->row()->email;
                $proposalWarning = false;
                $message = '';
                $success = $this->leads_model->update($this->input->post(), $id);

                if ($success) {
                    $emailNow = $this->db->select('email')->where('id', $id)->get(db_prefix() . 'leads')->row()->email;

                    $proposalWarning = (total_rows(db_prefix() . 'proposals', [
                                'rel_type' => 'lead',
                                'rel_id' => $id,]) > 0 && ($emailOriginal != $emailNow) && $emailNow != '') ? true : false;

                    $message = _l('updated_successfully', _l('lead'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'id' => $id,
                    'proposal_warning' => $proposalWarning,
                    'leadView' => $this->_get_lead_data($id),
                ]);
            }
            die;
        }

        echo json_encode([
            'leadView' => $this->_get_lead_data($id),
        ]);
    }

    private function _get_lead_data($id = '') {
        $reminder_data = '';
        $data['lead_locked'] = false;
        $data['openEdit'] = $this->input->get('edit') ? true : false;
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $data['status_id'] = $this->input->get('status_id') ? $this->input->get('status_id') : get_option('leads_default_status');

        if (is_numeric($id)) {
            $leadWhere = (has_permission('leads', '', 'view') ? [] : '(assigned = ' . get_staff_user_id() . ' OR addedfrom=' . get_staff_user_id() . ' OR is_public=1)');

            $lead = $this->leads_model->get($id, $leadWhere);

            if (!$lead) {
                header('HTTP/1.0 404 Not Found');
                echo _l('lead_not_found');
                die;
            }

            if (total_rows(db_prefix() . 'clients', ['leadid' => $id]) > 0) {
                $data['lead_locked'] = ((!is_admin() && get_option('lead_lock_after_convert_to_customer') == 1) ? true : false);
            }

            $reminder_data = $this->load->view('admin/includes/modals/reminder', [
                'id' => $lead->id,
                'name' => 'lead',
                'members' => $data['members'],
                'reminder_title' => _l('lead_set_reminder_title'),
                    ], true);

            $data['lead'] = $lead;
            $data['mail_activity'] = $this->leads_model->get_mail_activity($id);
            $data['notes'] = $this->misc_model->get_notes($id, 'lead');
            $data['activity_log'] = $this->leads_model->get_lead_activity_log($id);

            if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                $this->load->model('gdpr_model');
                $data['purposes'] = $this->gdpr_model->get_consent_purposes($lead->id, 'lead');
                $data['consents'] = $this->gdpr_model->get_consents(['lead_id' => $lead->id]);
            }
        }


        $data['statuses'] = $this->leads_model->get_status();
        $data['sources'] = $this->leads_model->get_source();

        $data = hooks()->apply_filters('lead_view_data', $data);

        return [
            'data' => $this->load->view('admin/leads/lead', $data, true),
            'reminder_data' => $reminder_data,
        ];
    }

    public function leads_kanban_load_more() {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        $status = $this->input->get('status');
        $page = $this->input->get('page');

        $this->db->where('id', $status);
        $status = $this->db->get(db_prefix() . 'leads_status')->row_array();

        $leads = $this->leads_model->do_kanban_query($status['id'], $this->input->get('search'), $page, [
            'sort_by' => $this->input->get('sort_by'),
            'sort' => $this->input->get('sort'),
        ]);

        foreach ($leads as $lead) {
            $this->load->view('admin/leads/_kan_ban_card', [
                'lead' => $lead,
                'status' => $status,
            ]);
        }
    }

    public function switch_kanban($set = 0) {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'leads_kanban_view' => $set,
        ]);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function export($id) {
        if (is_admin()) {
            $this->load->library('gdpr/gdpr_lead');
            $this->gdpr_lead->export($id);
        }
    }

    /* Delete lead from database */

    public function delete($id) {
        if (!$id) {
            redirect(admin_url('leads'));
        }

        if (!is_lead_creator($id) && !has_permission('leads', '', 'delete')) {
            access_denied('Delte Lead');
        }

        $response = $this->leads_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_lowercase')));
        } elseif ($response === true) {
            set_alert('success', _l('deleted', _l('lead')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_lowercase')));
        }
        $ref = $_SERVER['HTTP_REFERER'];

// if user access leads/inded/ID to prevent redirecting on the same url because will throw 404
        if (!$ref || strpos($ref, 'index/' . $id) !== false) {
            redirect(admin_url('leads'));
        }

        redirect($ref);
    }

    public function mark_as_lost($id) {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->mark_as_lost($id);
        if ($success) {
            $message = _l('lead_marked_as_lost');
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'leadView' => $this->_get_lead_data($id),
            'id' => $id,
        ]);
    }

    public function unmark_as_lost($id) {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->unmark_as_lost($id);
        if ($success) {
            $message = _l('lead_unmarked_as_lost');
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'leadView' => $this->_get_lead_data($id),
            'id' => $id,
        ]);
    }

    public function mark_as_junk($id) {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->mark_as_junk($id);
        if ($success) {
            $message = _l('lead_marked_as_junk');
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'leadView' => $this->_get_lead_data($id),
            'id' => $id,
        ]);
    }

    public function unmark_as_junk($id) {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->unmark_as_junk($id);
        if ($success) {
            $message = _l('lead_unmarked_as_junk');
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'leadView' => $this->_get_lead_data($id),
            'id' => $id,
        ]);
    }

    public function add_activity() {
        $leadid = $this->input->post('leadid');
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($leadid)) {
            ajax_access_denied();
        }
        if ($this->input->post()) {
            $message = $this->input->post('activity');
            $aId = $this->leads_model->log_lead_activity($leadid, $message);
            if ($aId) {
                $this->db->where('id', $aId);
                $this->db->update(db_prefix() . 'lead_activity_log', ['custom_activity' => 1]);
            }
            echo json_encode(['leadView' => $this->_get_lead_data($leadid), 'id' => $leadid]);
        }
    }

    public function get_convert_data($id) {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
            $this->load->model('gdpr_model');
            $data['purposes'] = $this->gdpr_model->get_consent_purposes($id, 'lead');
        }
        $data['lead'] = $this->leads_model->get($id);
        $this->load->view('admin/leads/convert_to_customer', $data);
    }

    /**
     * Convert lead to client
     * @since  version 1.0.1
     * @return mixed
     */
    public function convert_to_customer() {
        if (!is_staff_member()) {
            access_denied('Lead Convert to Customer');
        }

        if ($this->input->post()) {
            $default_country = get_option('customer_default_country');
            $data = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            $original_lead_email = $data['original_lead_email'];
            unset($data['original_lead_email']);

            if (isset($data['transfer_notes'])) {
                $notes = $this->misc_model->get_notes($data['leadid'], 'lead');
                unset($data['transfer_notes']);
            }

            if (isset($data['transfer_consent'])) {
                $this->load->model('gdpr_model');
                $consents = $this->gdpr_model->get_consents(['lead_id' => $data['leadid']]);
                unset($data['transfer_consent']);
            }

            if (isset($data['merge_db_fields'])) {
                $merge_db_fields = $data['merge_db_fields'];
                unset($data['merge_db_fields']);
            }

            if (isset($data['merge_db_contact_fields'])) {
                $merge_db_contact_fields = $data['merge_db_contact_fields'];
                unset($data['merge_db_contact_fields']);
            }

            if (isset($data['include_leads_custom_fields'])) {
                $include_leads_custom_fields = $data['include_leads_custom_fields'];
                unset($data['include_leads_custom_fields']);
            }

            if ($data['country'] == '' && $default_country != '') {
                $data['country'] = $default_country;
            }

            $data['billing_street'] = $data['address'];
            $data['billing_city'] = $data['city'];
            $data['billing_state'] = $data['state'];
            $data['billing_zip'] = $data['zip'];
            $data['billing_country'] = $data['country'];

            $data['is_primary'] = 1;
            $id = $this->clients_model->add($data, true);
            if ($id) {
                $primary_contact_id = get_primary_contact_user_id($id);

                if (isset($notes)) {
                    foreach ($notes as $note) {
                        $this->db->insert(db_prefix() . 'notes', [
                            'rel_id' => $id,
                            'rel_type' => 'customer',
                            'dateadded' => $note['dateadded'],
                            'addedfrom' => $note['addedfrom'],
                            'description' => $note['description'],
                            'date_contacted' => $note['date_contacted'],
                        ]);
                    }
                }
                if (isset($consents)) {
                    foreach ($consents as $consent) {
                        unset($consent['id']);
                        unset($consent['purpose_name']);
                        $consent['lead_id'] = 0;
                        $consent['contact_id'] = $primary_contact_id;
                        $this->gdpr_model->add_consent($consent);
                    }
                }
                if (!has_permission('customers', '', 'view') && get_option('auto_assign_customer_admin_after_lead_convert') == 1) {
                    $this->db->insert(db_prefix() . 'customer_admins', [
                        'date_assigned' => date('Y-m-d H:i:s'),
                        'customer_id' => $id,
                        'staff_id' => get_staff_user_id(),
                    ]);
                }
                $this->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted', false, serialize([
                    get_staff_full_name(),
                ]));
                $default_status = $this->leads_model->get_status('', [
                    'isdefault' => 1,
                ]);
                $this->db->where('id', $data['leadid']);
                $this->db->update(db_prefix() . 'leads', [
                    'date_converted' => date('Y-m-d H:i:s'),
                    'status' => $default_status[0]['id'],
                    'junk' => 0,
                    'lost' => 0,
                ]);
// Check if lead email is different then client email
                $contact = $this->clients_model->get_contact(get_primary_contact_user_id($id));
                if ($contact->email != $original_lead_email) {
                    if ($original_lead_email != '') {
                        $this->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted_email', false, serialize([
                            $original_lead_email,
                            $contact->email,
                        ]));
                    }
                }
                if (isset($include_leads_custom_fields)) {
                    foreach ($include_leads_custom_fields as $fieldid => $value) {
// checked don't merge
                        if ($value == 5) {
                            continue;
                        }
// get the value of this leads custom fiel
                        $this->db->where('relid', $data['leadid']);
                        $this->db->where('fieldto', 'leads');
                        $this->db->where('fieldid', $fieldid);
                        $lead_custom_field_value = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
// Is custom field for contact ot customer
                        if ($value == 1 || $value == 4) {
                            if ($value == 4) {
                                $field_to = 'contacts';
                            } else {
                                $field_to = 'customers';
                            }
                            $this->db->where('id', $fieldid);
                            $field = $this->db->get(db_prefix() . 'customfields')->row();
// check if this field exists for custom fields
                            $this->db->where('fieldto', $field_to);
                            $this->db->where('name', $field->name);
                            $exists = $this->db->get(db_prefix() . 'customfields')->row();
                            $copy_custom_field_id = null;
                            if ($exists) {
                                $copy_custom_field_id = $exists->id;
                            } else {
// there is no name with the same custom field for leads at the custom side create the custom field now
                                $this->db->insert(db_prefix() . 'customfields', [
                                    'fieldto' => $field_to,
                                    'name' => $field->name,
                                    'required' => $field->required,
                                    'type' => $field->type,
                                    'options' => $field->options,
                                    'display_inline' => $field->display_inline,
                                    'field_order' => $field->field_order,
                                    'slug' => slug_it($field_to . '_' . $field->name, [
                                        'separator' => '_',
                                    ]),
                                    'active' => $field->active,
                                    'only_admin' => $field->only_admin,
                                    'show_on_table' => $field->show_on_table,
                                    'bs_column' => $field->bs_column,
                                ]);
                                $new_customer_field_id = $this->db->insert_id();
                                if ($new_customer_field_id) {
                                    $copy_custom_field_id = $new_customer_field_id;
                                }
                            }
                            if ($copy_custom_field_id != null) {
                                $insert_to_custom_field_id = $id;
                                if ($value == 4) {
                                    $insert_to_custom_field_id = get_primary_contact_user_id($id);
                                }
                                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                                    'relid' => $insert_to_custom_field_id,
                                    'fieldid' => $copy_custom_field_id,
                                    'fieldto' => $field_to,
                                    'value' => $lead_custom_field_value,
                                ]);
                            }
                        } elseif ($value == 2) {
                            if (isset($merge_db_fields)) {
                                $db_field = $merge_db_fields[$fieldid];
// in case user don't select anything from the db fields
                                if ($db_field == '') {
                                    continue;
                                }
                                if ($db_field == 'country' || $db_field == 'shipping_country' || $db_field == 'billing_country') {
                                    $this->db->where('iso2', $lead_custom_field_value);
                                    $this->db->or_where('short_name', $lead_custom_field_value);
                                    $this->db->or_like('long_name', $lead_custom_field_value);
                                    $country = $this->db->get(db_prefix() . 'countries')->row();
                                    if ($country) {
                                        $lead_custom_field_value = $country->country_id;
                                    } else {
                                        $lead_custom_field_value = 0;
                                    }
                                }
                                $this->db->where('userid', $id);
                                $this->db->update(db_prefix() . 'clients', [
                                    $db_field => $lead_custom_field_value,
                                ]);
                            }
                        } elseif ($value == 3) {
                            if (isset($merge_db_contact_fields)) {
                                $db_field = $merge_db_contact_fields[$fieldid];
                                if ($db_field == '') {
                                    continue;
                                }
                                $this->db->where('id', $primary_contact_id);
                                $this->db->update(db_prefix() . 'contacts', [
                                    $db_field => $lead_custom_field_value,
                                ]);
                            }
                        }
                    }
                }
// set the lead to status client in case is not status client
                $this->db->where('isdefault', 1);
                $status_client_id = $this->db->get(db_prefix() . 'leads_status')->row()->id;
                $this->db->where('id', $data['leadid']);
                $this->db->update(db_prefix() . 'leads', [
                    'status' => $status_client_id,
                ]);

                set_alert('success', _l('lead_to_client_base_converted_success'));

                if (is_gdpr() && get_option('gdpr_after_lead_converted_delete') == '1') {
                    $this->leads_model->delete($data['leadid']);

                    $this->db->where('userid', $id);
                    $this->db->update(db_prefix() . 'clients', ['leadid' => null]);
                }

                log_activity('Created Lead Client Profile [LeadID: ' . $data['leadid'] . ', ClientID: ' . $id . ']');
                hooks()->do_action('lead_converted_to_customer', ['lead_id' => $data['leadid'], 'customer_id' => $id]);
                redirect(admin_url('clients/client/' . $id));
            }
        }
    }

    /* Used in kanban when dragging and mark as */

    public function update_lead_status() {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $this->leads_model->update_lead_status($this->input->post());
        }
    }

    public function update_status_order() {
        if ($post_data = $this->input->post()) {
            $this->leads_model->update_status_order($post_data);
        }
    }

    public function add_lead_attachment() {
        $id = $this->input->post('id');
        $lastFile = $this->input->post('last_file');

        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }

        handle_lead_attachments($id);
        echo json_encode(['leadView' => $lastFile ? $this->_get_lead_data($id) : [], 'id' => $id]);
    }

    public function add_external_attachment() {
        if ($this->input->post()) {
            $this->leads_model->add_attachment_to_database(
                    $this->input->post('lead_id'),
                    $this->input->post('files'),
                    $this->input->post('external')
            );
        }
    }

    public function delete_attachment($id, $lead_id) {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($lead_id)) {
            ajax_access_denied();
        }
        echo json_encode([
            'success' => $this->leads_model->delete_lead_attachment($id),
        ]);
    }

    public function delete_note($id, $lead_id) {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($lead_id)) {
            ajax_access_denied();
        }
        echo json_encode([
            'success' => $this->misc_model->delete_note($id),
        ]);
    }

    public function update_all_proposal_emails_linked_to_lead($id) {
        $success = false;
        $email = '';
        if ($this->input->post('update')) {
            $this->load->model('proposals_model');

            $this->db->select('email');
            $this->db->where('id', $id);
            $email = $this->db->get(db_prefix() . 'leads')->row()->email;

            $proposals = $this->proposals_model->get('', [
                'rel_type' => 'lead',
                'rel_id' => $id,
            ]);
            $affected_rows = 0;

            foreach ($proposals as $proposal) {
                $this->db->where('id', $proposal['id']);
                $this->db->update(db_prefix() . 'proposals', [
                    'email' => $email,
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }
            }

            if ($affected_rows > 0) {
                $success = true;
            }
        }

        echo json_encode([
            'success' => $success,
            'message' => _l('proposals_emails_updated', [
                _l('lead_lowercase'),
                $email,
            ]),
        ]);
    }

    public function save_form_data() {
        $data = $this->input->post();

// form data should be always sent to the request and never should be empty
// this code is added to prevent losing the old form in case any errors
        if (!isset($data['formData']) || isset($data['formData']) && !$data['formData']) {
            echo json_encode([
                'success' => false,
            ]);
            die;
        }

// If user paste with styling eq from some editor word and the Codeigniter XSS feature remove and apply xss=remove, may break the json.
        $data['formData'] = preg_replace('/=\\\\/m', "=''", $data['formData']);

        $this->db->where('id', $data['id']);
        $this->db->update(db_prefix() . 'web_to_lead', [
            'form_data' => $data['formData'],
        ]);
        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'success' => true,
                'message' => _l('updated_successfully', _l('web_to_lead_form')),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    public function form($id = '') {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }
        if ($this->input->post()) {
            if ($id == '') {
                $data = $this->input->post();
                $id = $this->leads_model->add_form($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('web_to_lead_form')));
                    redirect(admin_url('leads/form/' . $id));
                }
            } else {
                $success = $this->leads_model->update_form($id, $this->input->post());
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('web_to_lead_form')));
                }
                redirect(admin_url('leads/form/' . $id));
            }
        }

        $data['formData'] = [];
        $custom_fields = get_custom_fields('leads', 'type != "link"');

        $cfields = format_external_form_custom_fields($custom_fields);
        $data['title'] = _l('web_to_lead');

        if ($id != '') {
            $data['form'] = $this->leads_model->get_form([
                'id' => $id,
            ]);
            $data['title'] = $data['form']->name . ' - ' . _l('web_to_lead_form');
            $data['formData'] = $data['form']->form_data;
        }

        $this->load->model('roles_model');
        $data['roles'] = $this->roles_model->get();
        $data['sources'] = $this->leads_model->get_source();
        $data['statuses'] = $this->leads_model->get_status();

        $data['members'] = $this->staff_model->get('', [
            'active' => 1,
            'is_not_staff' => 0,
        ]);

        $data['languages'] = $this->app->get_available_languages();
        $data['cfields'] = $cfields;

        $db_fields = [];
        $fields = [
            'name',
            'title',
            'email',
            'phonenumber',
            'company',
            'address',
            'city',
            'state',
            'country',
            'zip',
            'description',
            'website',
        ];

        $fields = hooks()->apply_filters('lead_form_available_database_fields', $fields);

        $className = 'form-control';

        foreach ($fields as $f) {
            $_field_object = new stdClass();
            $type = 'text';
            $subtype = '';
            if ($f == 'email') {
                $subtype = 'email';
            } elseif ($f == 'description' || $f == 'address') {
                $type = 'textarea';
            } elseif ($f == 'country') {
                $type = 'select';
            }

            if ($f == 'name') {
                $label = _l('lead_add_edit_name');
            } elseif ($f == 'email') {
                $label = _l('lead_add_edit_email');
            } elseif ($f == 'phonenumber') {
                $label = _l('lead_add_edit_phonenumber');
            } else {
                $label = _l('lead_' . $f);
            }

            $field_array = [
                'subtype' => $subtype,
                'type' => $type,
                'label' => $label,
                'className' => $className,
                'name' => $f,
            ];

            if ($f == 'country') {
                $field_array['values'] = [];

                $field_array['values'][] = [
                    'label' => '',
                    'value' => '',
                    'selected' => false,
                ];

                $countries = get_all_countries();
                foreach ($countries as $country) {
                    $selected = false;
                    if (get_option('customer_default_country') == $country['country_id']) {
                        $selected = true;
                    }
                    array_push($field_array['values'], [
                        'label' => $country['short_name'],
                        'value' => (int) $country['country_id'],
                        'selected' => $selected,
                    ]);
                }
            }

            if ($f == 'name') {
                $field_array['required'] = true;
            }

            $_field_object->label = $label;
            $_field_object->name = $f;
            $_field_object->fields = [];
            $_field_object->fields[] = $field_array;
            $db_fields[] = $_field_object;
        }
        $data['bodyclass'] = 'web-to-lead-form';
        $data['db_fields'] = $db_fields;
        $this->load->view('admin/leads/formbuilder', $data);
    }

    public function forms($id = '') {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('web_to_lead');
        }

        $data['title'] = _l('web_to_lead');
        $this->load->view('admin/leads/forms', $data);
    }

    public function delete_form($id) {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }

        $success = $this->leads_model->delete_form($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('web_to_lead_form')));
        }

        redirect(admin_url('leads/forms'));
    }

// Sources
    /* Manage leads sources */
    public function sources() {
        if (!is_admin()) {
            access_denied('Leads Sources');
        }
        $data['sources'] = $this->leads_model->get_source();
        $data['title'] = 'Leads sources';
        $this->load->view('admin/leads/manage_sources', $data);
    }

    /* Add or update leads sources */

    public function source() {
        if (!is_admin() && get_option('staff_members_create_inline_lead_source') == '0') {
            access_denied('Leads Sources');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $inline = isset($data['inline']);
                if (isset($data['inline'])) {
                    unset($data['inline']);
                }

                $id = $this->leads_model->add_source($data);

                if (!$inline) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('lead_source')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : fales, 'id' => $id]);
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->leads_model->update_source($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('lead_source')));
                }
            }
        }
    }

    /* Delete leads source */

    public function delete_source($id) {
        if (!is_admin()) {
            access_denied('Delete Lead Source');
        }
        if (!$id) {
            redirect(admin_url('leads/sources'));
        }
        $response = $this->leads_model->delete_source($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_source_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lead_source')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_source_lowercase')));
        }
        redirect(admin_url('leads/sources'));
    }

// Statuses
    /* View leads statuses */
    public function statuses() {
        if (!is_admin()) {
            access_denied('Leads Statuses');
        }
        $data['statuses'] = $this->leads_model->get_status();
        $data['title'] = 'Leads statuses';
        $this->load->view('admin/leads/manage_statuses', $data);
    }

    /* Add or update leads status */

    public function status() {
        if (!is_admin() && get_option('staff_members_create_inline_lead_status') == '0') {
            access_denied('Leads Statuses');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $inline = isset($data['inline']);
                if (isset($data['inline'])) {
                    unset($data['inline']);
                }
                $id = $this->leads_model->add_status($data);
                if (!$inline) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('lead_status')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : fales, 'id' => $id]);
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->leads_model->update_status($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('lead_status')));
                }
            }
        }
    }

    /* Delete leads status from databae */

    public function delete_status($id) {
        if (!is_admin()) {
            access_denied('Leads Statuses');
        }
        if (!$id) {
            redirect(admin_url('leads/statuses'));
        }
        $response = $this->leads_model->delete_status($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_status_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lead_status')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_status_lowercase')));
        }
        redirect(admin_url('leads/statuses'));
    }

    /* Add new lead note */

    public function add_note($rel_id) {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($rel_id)) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            if ($data['contacted_indicator'] == 'yes') {
                $contacted_date = to_sql_date($data['custom_contact_date'], true);
                $data['date_contacted'] = $contacted_date;
            }

            unset($data['contacted_indicator']);
            unset($data['custom_contact_date']);

// Causing issues with duplicate ID or if my prefixed file for lead.php is used
            $data['description'] = isset($data['lead_note_description']) ? $data['lead_note_description'] : $data['description'];

            if (isset($data['lead_note_description'])) {
                unset($data['lead_note_description']);
            }

            $note_id = $this->misc_model->add_note($data, 'lead', $rel_id);

            if ($note_id) {
                if (isset($contacted_date)) {
                    $this->db->where('id', $rel_id);
                    $this->db->update(db_prefix() . 'leads', [
                        'lastcontact' => $contacted_date,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $this->leads_model->log_lead_activity($rel_id, 'not_lead_activity_contacted', false, serialize([
                            get_staff_full_name(get_staff_user_id()),
                            _dt($contacted_date),
                        ]));
                    }
                }
            }
        }
        echo json_encode(['leadView' => $this->_get_lead_data($rel_id), 'id' => $rel_id]);
    }

    public function test_email_integration() {
        if (!is_admin()) {
            access_denied('Leads Test Email Integration');
        }

        app_check_imap_open_function(admin_url('leads/email_integration'));

        require_once(APPPATH . 'third_party/php-imap/Imap.php');

        $mail = $this->leads_model->get_email_integration();
        $ps = $mail->password;
        if (false == $this->encryption->decrypt($ps)) {
            set_alert('danger', _l('failed_to_decrypt_password'));
            redirect(admin_url('leads/email_integration'));
        }
        $mailbox = $mail->imap_server;
        $username = $mail->email;
        $password = $this->encryption->decrypt($ps);
        $encryption = $mail->encryption;
// open connection
        $imap = new Imap($mailbox, $username, $password, $encryption);

        if ($imap->isConnected() === false) {
            set_alert('danger', _l('lead_email_connection_not_ok') . '<br /><b>' . $imap->getError() . '</b>');
        } else {
            set_alert('success', _l('lead_email_connection_ok'));
        }

        redirect(admin_url('leads/email_integration'));
    }

    public function email_integration() {
        if (!is_admin()) {
            access_denied('Leads Email Intregration');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            if (isset($data['fakeusernameremembered'])) {
                unset($data['fakeusernameremembered']);
            }
            if (isset($data['fakepasswordremembered'])) {
                unset($data['fakepasswordremembered']);
            }

            $success = $this->leads_model->update_email_integration($data);
            if ($success) {
                set_alert('success', _l('leads_email_integration_updated'));
            }
            redirect(admin_url('leads/email_integration'));
        }
        $data['roles'] = $this->roles_model->get();
        $data['sources'] = $this->leads_model->get_source();
        $data['statuses'] = $this->leads_model->get_status();

        $data['members'] = $this->staff_model->get('', [
            'active' => 1,
            'is_not_staff' => 0,
        ]);

        $data['title'] = _l('leads_email_integration');
        $data['mail'] = $this->leads_model->get_email_integration();
        $data['bodyclass'] = 'leads-email-integration';
        $this->load->view('admin/leads/email_integration', $data);
    }

    public function change_status_color() {
        if ($this->input->post()) {
            $this->leads_model->change_status_color($this->input->post());
        }
    }

    public function import() {
        if (!is_admin() && get_option('allow_non_admin_members_to_import_leads') != '1') {
            access_denied('Leads Import');
        }

        $dbFields = array("name", "email", "
        ", "phonenumber", "phone2", "phone3", "phone4", "skypeid", "landline", "fax", "birthdate", "passportnumber", "exipre", "email2", "email3");
//        var_dump($dbFields);
//        exit();
        array_push($dbFields, 'tags');

        $this->load->library('import/import_leads', [], 'import');
        $this->import->setDatabaseFields($dbFields)
                ->setCustomFields(get_custom_fields('leads'));

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

        $data['statuses'] = $this->leads_model->get_status();
        $data['sources'] = $this->leads_model->get_source();
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['title'] = _l('import');
        $this->load->view('admin/leads/import', $data);
    }

    public function validate_unique_field() {
        if ($this->input->post()) {

// First we need to check if the field is the same
            $lead_id = $this->input->post('lead_id');
            $field = $this->input->post('field');
            $value = $this->input->post($field);

            if ($lead_id != '') {
                $this->db->select($field);
                $this->db->where('id', $lead_id);
                $row = $this->db->get(db_prefix() . 'leads')->row();
                if ($row->{$field} == $value) {
                    echo json_encode(true);
                    die();
                }
            }

            echo total_rows(db_prefix() . 'leads', [$field => $value]) > 0 ? 'false' : 'true';
        }
    }

    public function bulk_action() {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        hooks()->do_action('before_do_bulk_action_for_leads');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids = $this->input->post('ids');
            $status = $this->input->post('status');
            $source = $this->input->post('source');
            $assigned = $this->input->post('assigned');
            $visibility = $this->input->post('visibility');
            $tags = $this->input->post('tags');
            $last_contact = $this->input->post('last_contact');
            $lost = $this->input->post('lost');
            $has_permission_delete = has_permission('leads', '', 'delete');
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($has_permission_delete) {
                            if ($this->leads_model->delete($id)) {
                                $total_deleted++;
                            }
                        }
                    } else {
                        if ($status || $source || $assigned || $last_contact || $visibility) {
                            $update = [];
                            if ($status) {
// We will use the same function to update the status
                                $this->leads_model->update_lead_status([
                                    'status' => $status,
                                    'leadid' => $id,
                                ]);
                            }
                            if ($source) {
                                $update['source'] = $source;
                            }
                            if ($assigned) {
                                $update['assigned'] = $assigned;
                            }
                            if ($last_contact) {
                                $last_contact = to_sql_date($last_contact, true);
                                $update['lastcontact'] = $last_contact;
                            }

                            if ($visibility) {
                                if ($visibility == 'public') {
                                    $update['is_public'] = 1;
                                } else {
                                    $update['is_public'] = 0;
                                }
                            }

                            if (count($update) > 0) {
                                $this->db->where('id', $id);
                                $this->db->update(db_prefix() . 'leads', $update);
                            }
                        }
                        if ($tags) {
                            handle_tags_save($tags, $id, 'lead');
                        }
                        if ($lost == 'true') {
                            $this->leads_model->mark_as_lost($id);
                        }
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_leads_deleted', $total_deleted));
        }
    }

    public function getEditView() {
        if ($this->input->post()) {
            $id = $this->input->post("id");
            $this->db->where("id", $id);
            foreach ($this->db->get("tblleads")->result() as $lead) {
                echo '<td colspan="10" style="height:230px;">
                    <table style="width:100%;box-shadow: 1px 1px 5px 1px darkgrey;" class="table-responsive">
                        <tr>
                        <br/>
                        <td colspan="6">
                            <table style="width:100%;height:100px;" class="table-responsive">
                                <tr style="border-bottom: 1px solid #a1b4cc;">
                                    <th style="padding: 5px;" colspan="2">
                                        <b>Name: </b>
                                        <span class="pull-right" style="margin-right: 2px;">
                                            <input type="hidden" id="salutation_' . $id . '" name="salutation_' . $id . '" value="' . $lead->salutation . '"/>
                                            <input type="radio" name="salutaion_select" value="Mr" ' . (($lead->salutation == "Mr") ? " checked='checked'" : "") . ' onclick="document.getElementById(' . "'salutation_$id'" . ').value = this.value;"/> Mr
                                            <input type="radio" name="salutaion_select" value="Mrs" style="margin-left: 10px;"' . (($lead->salutation == "Mrs") ? " checked='checked'" : "") . ' onclick="document.getElementById(' . "'salutation_$id'" . ').value = this.value;"/> Mrs
                                            <input type="radio" name="salutaion_select" value="Miss" style="margin-left: 10px;"' . (($lead->salutation == "Miss") ? " checked='checked'" : "") . ' onclick="document.getElementById(' . "'salutation_$id'" . ').value = this.value;"/> Miss
                                            <input type="radio" name="salutaion_select" value="Ms" style="margin-left: 10px;"' . (($lead->salutation == "Ms") ? " checked='checked'" : "") . ' onclick="document.getElementById(' . "'salutation_$id'" . ').value = this.value;"/> Ms
                                        </span>
                                        <input type="text" id="name_' . $id . '" name="name_' . $id . '" class="form-control" value="' . $lead->name . '" placeholder="Lead Name">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Passport NO.: </b><input type="text" id="passportnumber_' . $id . '" name="passportnumber_' . $id . '" class="form-control" value="' . $lead->passportnumber . '" placeholder="Paasport Number">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Expire: </b><input type="date" id="exipre_' . $id . '" name="expire_' . $id . '" class="form-control" value="' . $lead->exipre . '" placeholder="Exipring date">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>
                                        : </b><select id="nationality_' . $id . '" name="nationality_' . $id . '" class="form-control"><option value selected disabled>SELECT NATIONALITY</option>';
                foreach ($this->db->get("tblcountries")->result() as $nationality) {
                    echo '<option' . (($nationality->country_id == $lead->nationality) ? ' selected="selected"' : '') . ' value="' . $nationality->country_id . '">' . $nationality->nationality . ' (' . $nationality->short_name . ')</option>';
                }
                echo '</select>
                                    </th>
                                </tr>
                                <tr style="border-bottom: 1px solid #a1b4cc;">
                                    <th style="padding: 5px;" colspan="1">
                                        <b>E-mail: </b><input type="email" id="email_' . $id . '" name="email_' . $id . '" class="form-control" value="' . $lead->email . '" placeholder="Lead E-mail">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>E-mail 2: </b><input type="email" id="email2_' . $id . '" name="email2_' . $id . '" class="form-control" value="' . $lead->email2 . '" placeholder="Second E-mail">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>E-mail 3: </b><input type="email" id="email3_' . $id . '" name="email3_' . $id . '" class="form-control" value="' . $lead->email3 . '" placeholder="Third E-mail">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Main Phone Number: </b><input type="text" id="phonenumber_' . $id . '" name="phonenumber_' . $id . '" class="form-control" value="' . $lead->phonenumber . '" placeholder="Main Phone number">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">';
                if (is_admin() || is_admin_normal() || has_permission("leads", '', "assign_leads")) {
                    echo ' <b>Assigned To: </b><select id="assigned_' . $id . '" name="assigned_' . $id . '" class="form-control"><option value selected disabled>ASSIGNED TO</option>';
                    foreach ($this->db->get("tblstaff")->result() as $staff) {
                        echo '<option' . (($staff->staffid == $lead->assigned) ? ' selected="selected"' : '') . ' value="' . $staff->staffid . '">' . $staff->firstname . ' ' . $staff->lastname . '</option>';
                    }
                    echo '</select>';
                }
                echo '</th>
                                </tr>
                                <tr style="border-bottom: 1px solid #a1b4cc;">
                                    <th style="padding: 5px;" colspan="1">
                                        <b>Phone 1: </b><input type="text" id="phone1_' . $id . '" name="phone1_' . $id . '" class="form-control" value="' . $lead->phone1 . '" placeholder="First Phone number">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Phone 2: </b><input type="text" id="phone2_' . $id . '" name="phone2_' . $id . '" class="form-control" value="' . $lead->phone2 . '" placeholder="Second Phone number">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Phone 3: </b><input type="text" id="phone3_' . $id . '" name="phone3_' . $id . '" class="form-control" value="' . $lead->phone3 . '" placeholder="Third Phone number">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Phone 4: </b><input type="text" id="phone4_' . $id . '" name="phone4_' . $id . '" class="form-control" value="' . $lead->phone4 . '" placeholder="Fourth Phone number">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Category: </b><select id="category_' . $id . '" name="category_' . $id . '" class="form-control"><option value selected disabled>SELECT CATEGORY</option>';
                foreach ($this->db->get("tblleads_status")->result() as $category) {
                    echo '<option' . (($category->id == $lead->category) ? ' selected="selected"' : '') . ' value="' . $category->id . '">' . $category->name . '</option>';
                }
                echo '</select>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="padding: 5px;" colspan="1">
                                        <b>Skype ID: </b><input type="text" id="skypeid_' . $id . '" name="skypeid_' . $id . '" class="form-control" value="' . $lead->skypeid . '" placeholder="Skype ID">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Land Line: </b><input type="text" id="landline_' . $id . '" name="landline_' . $id . '" class="form-control" value="' . $lead->landline . '" placeholder="Landline Number">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Fax: </b><input type="text" id="fax_' . $id . '" name="fax_' . $id . '" class="form-control" value="' . $lead->fax . '" placeholder="FAX Number">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Birth Date: </b><input type="date" id="birthdate_' . $id . '" name="birthdate_' . $id . '" class="form-control" value="' . $lead->birthdate . '"  placeholder="Birth Date">
                                    </th>
                                    <th style="border-left: 1px solid #a1b4cc;padding: 5px;" colspan="1">
                                        <b>Source: </b><select id="source_' . $id . '" name="source_' . $id . '" class="form-control"><option value selected disabled>SELECT SOURCE</option>';
                foreach ($this->db->get("tblleads_sources")->result() as $source) {
                    echo '<option' . (($source->id == $lead->source) ? ' selected="selected"' : '') . ' value="' . $source->id . '">' . $source->name . '</option>';
                }
                echo '</select>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="4"></th>
                                    <th style="padding: 5px;" colspan="1" align="right">
                                        <a title="Save" class="btn btn-success btn-xs" onclick="saveEdits(' . $id . ');" style="margin-top:5px;">
                                            Save
                                        </a>
                                        <a title="Cancel" class="btn btn-danger btn-xs" onclick="leadAction(' . $id . ', ' . "'cancel'" . ');" style="margin-top:5px;">
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
            $updateData = array(
                "salutation" => $this->input->post("salutation"),
                "name" => $this->input->post("name"),
                "email" => $this->input->post("email"),
                "nationality" => $this->input->post("nationality"),
                "phonenumber" => $this->input->post("phonenumber"),
                "phone1" => $this->input->post("phone1"),
                "phone2" => $this->input->post("phone2"),
                "phone3" => $this->input->post("phone3"),
                "phone4" => $this->input->post("phone4"),
                "category" => $this->input->post("category"),
                "skypeid" => $this->input->post("skypeid"),
                "landline" => $this->input->post("landline"),
                "fax" => $this->input->post("fax"),
                "birthdate" => $this->input->post("birthdate"),
                "source" => $this->input->post("source"),
                "passportnumber" => $this->input->post("passportnumber"),
                "exipre" => $this->input->post("expire"),
                "email2" => $this->input->post("email2"),
                "email3" => $this->input->post("email3"),
                "assigned" => $this->input->post("assignedto"),
                "datemodified" => date("Y-m-d H:i:s")
            );

            $this->db->where('id', $this->input->post("id"));
            $this->db->update('tblleads', $updateData);

            $this->getViewAfterEdit($this->input->post("id"));
        }
    }

    protected function getViewAfterEdit($id) {
        $this->db->where("id", $id);
        foreach ($this->db->get("tblleads")->result() as $lead) {
            echo '<td>
                                <table class="table table-bordered table-responsive" style="width: 100%;max-width: 100%;font-size: 10px;box-shadow: 1px 1px 5px 1px darkgrey;">
                                    <thead>
                                        <tr>
                                            <th class="text text-center" style="padding-bottom: 10px;font-size: 10px;width:1px;">
                                                <div class="checkbox" align="center"><input type="checkbox" value="' . $lead->id . '"><label></label></div>
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
                                                <a href="javascript::();" onclick="leadAction(' . $lead->id . ', ' . "'editView'" . ');" class="btn btn-success tablebtn"><i class="fa fa-edit"></i> Edit</a>';
            if (get_option('show_table_export_button') == 'to_all' || (get_option('show_table_export_button') == 'only_admins' && (is_admin_normal() || is_admin()))) {
                echo '<a href="javascript::();" onclick="exportData(' . $lead->id . ', ' . "'singleLeadExport'" . ', ' . "''.$lead->name.''" . ');" class="btn btn-warning tablebtn"><i class="fa fa-download"></i> Export</a>';
            } else {
                $noExport = TRUE;
            }
            echo '<a href="javascript::();" onclick="leadAction(' . $lead->id . ', ' . "'leadAttach'" . ');" class="btn btn-primary tablebtn"><i class="fa fa-file-o"></i> Files</a>'
            . '<a href="javascript::();" onclick="leadAction(' . $lead->id . ', ' . "'deleteLead'" . ');" class="btn btn-danger tablebtn"><i class="fa fa-trash-o"></i> Delete</a>
                                                ' . ((isset($noExport) && $noExport == TRUE) ? "<br/><br/><br/><br/><br/><br/>" : "<br/><br/><br/><br/>") . '
                                                <a href="javascript::();" onclick="alert(' . "'Tracking'" . ')" class="btn btn-info tablebtn"><i class="fa fa-flag-checkered"></i><br/>Tracking</a>
                                                <a href="' . base_url("admin/properties/?page=1&items=5&leadID=" . $lead->id) . '" class="btn btn-primary tablebtn"><i class="fa fa-cog"></i><br/>Properties</a>
                                            </td>
                                            <td rowspan="2" class="text text-center" style="vertical-align: middle;">' . $lead->id . '</td>
                                            <td style="padding-left: 10px;width: 120px;">' . (($lead->salutation != "" && $lead->salutation != null) ? $lead->salutation . ". " : "") . $lead->name . '</td>
                                            <td style="padding-left: 10px;width: 120px;">' . $lead->email . '</td>
                                            <td style="padding-left: 10px;width: 200px;"><div align="left">';
            $phone_information = $this->leads_model->getPhoneNumbers();
            foreach ($phone_information as $single_phone) {
                if ((strlen($single_phone->calling_code) + 1) == strlen($single_phone->phone_code)) {
                    if (substr(ltrim($lead->phonenumber, '0'), 0, strlen($single_phone->calling_code)) === $single_phone->calling_code || substr(ltrim($lead->phonenumber, '0'), 0, strlen($single_phone->phone_code)) === $single_phone->phone_code) {
                        $d = new DateTime("now", new DateTimeZone($single_phone->time_zone));
                        echo '<div style="white-space:nowrap;"><img width="35px" class="img-rounded" src="' . base_url("assets/flags/" . $single_phone->flag) . '">&nbsp;&nbsp;<i style="margin-left: 10px;">' . $lead->phonenumber . '</i></div><br/>';
                        echo '<div><img width="40px" class="img-rounded" src="' . base_url("assets/images/world-time.png") . '">&nbsp;&nbsp;<div style="margin-top: -35px;margin-left: 50px;"><i>' . $single_phone->short_name . '</i><i><br/>Time: ' . $d->format("H:i A") . '</i></div></div>';
                    }
                }
            }
            echo '</div></td>
                                            <td style="padding-left: 10px;text-align: center;">
                                                <div align="center"><br/>
                                                    ' . staff_profile_image($lead->assigned, $classes = ['staff-profile-image', 'img-rounded'], $type = 'small', array("width" => "35px")) . '
                                                    <br/><br/>
                                                    <i>' . ((get_staff_full_name($lead->assigned) != "" && get_staff_full_name($lead->assigned) != null) ? get_staff_full_name($lead->assigned) : "UNKNOWN") . '</i>
                                                </div>
                                            </td>
                                            <td style="padding-left: 10px;width:120px;vertical-align:middle;">';
            $nationality_information = $this->leads_model->getNationalities_with_id($lead->nationality);
            if ($nationality_information != null) {
                echo '<div align="center"><br/><img width="50%" class="img-rounded" src="' . base_url("assets/flags/" . $nationality_information->flag) . '"><br/><br/>'
                . '<i>' . $nationality_information->nationality . '</i></div>';
            } else {
                echo '<div align="center"><i>UNKNOWN</i></div>';
            }
            echo '</td>
                                            <td style="padding-left: 10px;width:100px;vertical-align:middle">
                                                <select id="lead_category_' . $lead->id . '" name="lead_category_' . $lead->id . '" title="CATEGORY" class="form-control lead_category" onchange="changeCategory(' . $lead->id . ');" style="width: 100px;">
                                                    <option value disabled>CATEGORY</option>';
            foreach ($this->db->get("tblleads_status")->result() as $category) {
                echo '<option' . (($category->id == $lead->category) ? ' selected="selected"' : '') . ' value="' . $category->id . '">' . $category->name . '</option>';
            }
            echo '</select>
                                            </td>
                                            <td style="padding-left: 10px;width:120px;vertical-align:middle">
                                                <select id="lead_source_' . $lead->id . '" name="lead_source_' . $lead->id . '" title="SOURCE" class="form-control lead_source" onchange="changeSource(' . $lead->id . ');" style="width: 100px;">
                                                    <option value disabled>SOURCE</option>';
            foreach ($this->db->get("tblleads_sources")->result() as $source) {
                echo '<option' . (($source->id == $lead->source) ? ' selected="selected"' : '') . ' value="' . $source->id . '">' . $source->name . '</option>';
            }
            echo '</select>
                                            </td>
                                            <td style="padding-left: 10px;width:140px;vertical-align:middle;">
                                                <select id="lead_status_' . $lead->id . '" name="lead_status_' . $lead->id . '" title="STATUS" class="form-control lead_status" onchange="changeStatus(' . $lead->id . ');">
                                                    <option value disabled>STATUS</option>
                                                    <option' . (($lead->status == "Undefined") ? " selected='selected'" : "") . ' value="Undefined">Undefined</option>
                                                    <option' . (($lead->status == "Contacted") ? " selected='selected'" : "") . ' value="Contacted">Contacted</option>
                                                    <option' . (($lead->status == "Open") ? " selected='selected'" : "") . ' value="Open">Open</option>
                                                    <option' . (($lead->status == "Closed") ? " selected='selected'" : "") . ' value="Closed">Closed</option>
                                                </select>                                            
                                            </td>
                                        </tr>
                                        <tr style="height: 100px;">
                                            <td colspan="1" style="padding: 0px;">
                                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                                    <tbody>
                                                        <br/>
                                                        <tr><td style="padding-left: 10px;border: 0px;"><b style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">CREATED</b><br/>' . date("d - F - Y", strtotime($lead->dateadded)) . '</td></tr>
                                                        <tr><td style="padding-left: 10px;border: 0px;"><b style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;">MODIFIED</b><br/>' . date("d - F - Y", strtotime($lead->datemodified)) . '</td></tr>
                                                    </tbody>
                                                </table>
                                                
                                            </td>
                                            <td colspan="2" style="padding: 0px;width: auto;">
                                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                                    <thead>
                                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>NOTES</b></th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr><td style="padding-left: 10px;" id="lead_notes_' . $lead->id . '">';
            $this->db->where("rel_id", $lead->id);
            $this->db->where("rel_type", "lead");
            $this->db->limit(3);
            $this->db->order_by("id", "DESC");
            $notes = $this->db->get("tblnotes");
            if ($notes->num_rows() > 0) {
                echo '<ul style = "list-style-type:square;margin-left:3%;">';
                foreach ($notes->result() as $note) {
                    echo '<li>' . $note->description . ' (' . get_staff_full_name($note->addedfrom) . ' - ' . date("d - F - Y", strtotime($note->dateadded)) . ')</li>';
                }
                echo '</ul > ';
            } else {
                echo "No notes to be shown";
            }
            echo '<i class="fa fa-plus-square" style="font-size:15px;color:green;margin-left:98%;cursor:pointer;" onclick="add_note(' . $lead->id . ');"></i></td></tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td colspan="1" style="padding: 0px;width:140px;">
                                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                                    <thead>
                                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>REMINDERS</b></th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr><td style="padding-left: 10px;">' . $lead->reminders . '</td></tr>
                                                    </tbody>
                                                </table>
                                            </td>';
            $this->db->where("property_lead_id", $lead->id);
            $leadProps = $this->db->get("tblproperties");
            echo '<td colspan="1" style="padding: 0px;width: 120px;">
                                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                                    <thead>
                                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>PROPERTIES</b></th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr><td style="padding-left: 10px;font-size: 20px;text-align: center;vertical-align: middle;">';
            $this->db->join("tblproperties", "tblproperties.property_lead_id = tblleads.id AND tblleads.id = '" . $lead->id . "'");
            echo $this->db->get("tblleads")->num_rows();
            echo '   <i style="font-size: 25px;" class="fa fa-building"></i></td></tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td colspan="1" style="padding: 0px;width: 100px;">
                                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                                    <thead>
                                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>CITY</b></th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr><td style="padding-left: 10px;">' . $lead->city . '</td></tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td colspan="1" style="padding: 0px;width:120px;">
                                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                                    <thead>
                                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>COMMUNITIES</b></th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr><td style="padding-left: 10px;">';
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
            echo'</td></tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                            <td colspan="1" style="padding: 0px;width:140px;">
                                                <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                                                    <thead>
                                                        <tr><th style="font-size: 12px;vertical-align: middle;text-align: center;color: darkblue;"><b>SUB COMMUNITIES</b></th></tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr><td style="padding-left: 10px;">';
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
            echo '</td></tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>';
        }
    }

    public function deleteSingleLead() {
        if ($this->input->post()) {
            $response = "Something wrong happened, try again later.";
            $leadID = $this->input->post("id");
            $this->db->where('id', $leadID);
            if ($this->db->delete('tblleads')) {
                $response = "Deleted Successfully";
            }
            echo $response;
        }
    }

    public function singleLeadExport() {
        $leadName = $this->input->get("name");
        $leadID = $this->input->get("id");
        header('Content-type: application/excel');
        $filename = $leadName . '.xls';
        header('Content-Disposition: attachment; filename=' . $filename);
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head>
                    <!--[if gte mso 9]>
                    <xml>
                        <x:ExcelWorkbook>
                            <x:ExcelWorksheets>
                                <x:ExcelWorksheet>
                                    <x:Name>Sheet 1</x:Name>
                                    <x:WorksheetOptions>
                                        <x:Print>
                                            <x:ValidPrinterInfo/>
                                        </x:Print>
                                    </x:WorksheetOptions>
                                </x:ExcelWorksheet>
                            </x:ExcelWorksheets>
                        </x:ExcelWorkbook>
                    </xml>
                    <![endif]-->
                </head><body><table>';
        $this->getExportView($leadID);
        echo '</table></body></html>';
    }

    public function multipleLeadExport() {
        $leadName = "leads";
        $leadsIDs = $this->input->get("ids");
        $leadID = explode(",", $leadsIDs);
        header('Content-type: application/excel');
        $filename = $leadName . '.xls';
        header('Content-Disposition: attachment; filename=' . $filename);
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head>
                    <!--[if gte mso 9]>
                    <xml>
                        <x:ExcelWorkbook>
                            <x:ExcelWorksheets>
                                <x:ExcelWorksheet>
                                    <x:Name>Sheet 1</x:Name>
                                    <x:WorksheetOptions>
                                        <x:Print>
                                            <x:ValidPrinterInfo/>
                                        </x:Print>
                                    </x:WorksheetOptions>
                                </x:ExcelWorksheet>
                            </x:ExcelWorksheets>
                        </x:ExcelWorkbook>
                    </xml>
                    <![endif]-->
                </head><body><table>';
        for ($i = 0; $i < count($leadID) - 1; $i++) {
            $this->getExportView($leadID[$i]);
            echo "<tr></tr>";
        }
        echo '</table></body></html>';
    }

    protected function getExportView($id) {
        $this->db->where("id", $id);
        foreach ($this->db->get("tblleads")->result() as $lead) {
            echo '<tr role="row" class="odd">
                        <td>
                            <table class="table table-bordered table-responsive" style="width: 100%;max-width: 100%;padding: 10px;" border="1">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text text-center">#</th>
                                        <th>Name</th>
                                        <th>E-mail</th>
                                        <th>Phone</th>
                                        <th>Assigned User</th>
                                        <th>Nationality</th>
                                        <th>Category</th>
                                        <th>Source</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="height: 100px;">
                                        <td rowspan="2" class="text text-center" style="vertical-align: middle;">' . $lead->id . '</td>
                                        <td style="padding-left: 10px;width: 120px;vertical-align: middle;">' . $lead->name . '</td>
                                        <td style="padding-left: 10px;width: 120px;vertical-align: middle;">' . $lead->email . '</td>
                                        <td style="padding-left: 10px;width: 100px;vertical-align: middle;">' . $lead->phonenumber . '</td>
                                        <td style="padding-left: 10px;text-align: center;vertical-align: middle;">
                                            <div align="center">
                                                <i>' . get_staff_full_name($lead->assigned) . '</i>
                                            </div>
                                        </td>
                                        <td style="padding-left: 10px;width:120px;vertical-align: middle;">' . $lead->nationality . '</td>
                                        <td style="padding-left: 10px;width:100px;vertical-align: middle;">';
            foreach ($this->db->get("tblleads_status")->result() as $category) {
                echo (($category->id == $lead->category) ? $category->name : '');
            }
            echo '</td><td style="padding-left: 10px;width:120px;vertical-align: middle;">';
            foreach ($this->db->get("tblleads_sources")->result() as $source) {
                echo (($source->id == $lead->source) ? $source->name : '');
            }
            echo '</td>
            <td style="padding-left: 10px;width:140px;vertical-align: middle;">' . $lead->status . '</td>
            </tr>
            <tr style="height: 100px;">
                <td colspan="1" style="padding: 0px;">
                    <table class="table table-responsive" style="width: 100%;height: 100px;font-size: 10px;">
                        <tbody>
                            <br/>
                            <tr><td><b>CREATED</b><br/>' . date("d - M - Y", strtotime($lead->dateadded)) . '</td></tr>
                            <tr><td><b>MODIFIED</b><br/>' . date("d - M - Y", strtotime($lead->datemodified)) . '</td></tr>
                        </tbody>
                    </table>
                </td>
                <td colspan="2" style="padding: 0px;width: auto;">
                    <table class="table table-responsive" style="width: 100%;height: 100px;">
                        <thead>
                            <tr><th>Notes</th></tr>
                        </thead>
                        <tbody>
                            <tr><td style="padding-left: 10px;vertical-align: middle;">' . $lead->notes . '</td></tr>
                        </tbody>
                    </table>
                </td>
                <td colspan="1" style="padding: 0px;width:140px;">
                    <table class="table table-responsive" style="width: 100%;height: 100px;">
                        <thead>
                            <tr><th>Reminders</th></tr>
                        </thead>
                        <tbody>
                            <tr><td style="padding-left: 10px;vertical-align: middle;">' . $lead->reminders . '</td></tr>
                        </tbody>
                    </table>
                </td>
                <td colspan="1" style="padding: 0px;width: 120px;">
                    <table class="table table-responsive" style="width: 100%;height: 100px;">
                        <thead>
                            <tr><th>Properties</th></tr>
                        </thead>
                        <tbody>
                            <tr><td style="padding-left: 10px;vertical-align: middle;">' . $lead->propertis . '</td></tr>
                        </tbody>
                    </table>
                </td>
                <td colspan="1" style="padding: 0px;width: 100px;">
                    <table class="table table-responsive" style="width: 100%;height: 100px;">
                        <thead>
                            <tr><th>City</th></tr>
                        </thead>
                        <tbody>
                            <tr><td style="padding-left: 10px;vertical-align: middle;">' . $lead->city . '</td></tr>
                        </tbody>
                    </table>
                </td>
                <td colspan="1" style="padding: 0px;width:120px;">
                    <table class="table table-responsive" style="width: 100%;height: 100px;">
                        <thead>
                            <tr><th>Communities</th></tr>
                        </thead>
                        <tbody>
                            <tr><td style="padding-left: 10px;vertical-align: middle;">' . $lead->communities . '</td></tr>
                        </tbody>
                    </table>
                </td>
                <td colspan="1" style="padding: 0px;width:140px;">
                    <table class="table table-responsive" style="width: 100%;height: 100px;">
                        <thead>
                            <tr><th>Sub Communities</th></tr>
                        </thead>
                        <tbody>
                            <tr><td style="padding-left: 10px;vertical-align: middle;">' . $lead->subcommunities . '</td></tr>
            </tbody>
        </table>
    </td>
</tr>
</tbody>
</table>
</td>
</tr>';
        }
    }

    public function changeStatus() {
        if ($this->input->post()) {
            $lead_id = $this->input->post("id");
            $newstatus = $this->input->post("newstatus");

            $updateData = array(
                "status" => $newstatus
            );

            $this->db->where('id', $lead_id);
            $this->db->update('tblleads', $updateData);

            $this->db->where('id', $lead_id);
            foreach ($this->db->get("tblleads")->result() as $lead) {
                echo '<option' . (($lead->status == "Contacted") ? " selected='selected'" : "") . ' value = "Contacted">Contacted</option>
                      <option' . (($lead->status == "Open") ? " selected='selected'" : "") . ' value="Open">Open</option>
                      <option' . (($lead->status == "Closed") ? " selected='selected'" : "") . ' value = "Closed">Closed</option>';
            }
        }
    }

    public function changeCategory() {
        if ($this->input->post()) {
            $lead_id = $this->input->post("id");
            $newcategory = $this->input->post("newcategory");

            $updateData = array(
                "category" => $newcategory
            );

            $this->db->where('id', $lead_id);
            $this->db->update('tblleads', $updateData);

            echo "<option value disabled>CATEGORY</option>";
            $this->db->where('id', $lead_id);
            foreach ($this->db->get("tblleads")->result() as $lead) {
                foreach ($this->db->get("tblleads_status")->result() as $category) {
                    echo '<option' . (($category->id == $lead->category) ? ' selected="selected"' : '') . ' value="' . $category->id . '">' . $category->name . '</option>';
                }
            }
        }
    }

    public function changeSource() {
        if ($this->input->post()) {
            $lead_id = $this->input->post("id");
            $newsource = $this->input->post("newsource");

            $updateData = array(
                "source" => $newsource
            );

            $this->db->where('id', $lead_id);
            $this->db->update('tblleads', $updateData);

            echo "<option value disabled>SOURCE</option>";
            $this->db->where('id', $lead_id);
            foreach ($this->db->get("tblleads")->result() as $lead) {
                foreach ($this->db->get("tblleads_sources")->result() as $source) {
                    echo '<option' . (($source->id == $lead->source) ? ' selected="selected"' : '') . ' value="' . $source->id . '">' . $source->name . '</option>';
                }
            }
        }
    }

    public function getAddNote() {
        if ($this->input->post()) {
            $id = $this->input->post("id");
            echo '<div align="center">
                  <small style="color: red;">NEW LINES AND FOREIGN CHARACTERS ARE NOT ALLOWED</small>
                  <textarea class="form-control" rows="5" style="height:50px !important;" id="lead_new_note_' . $id . '"></textarea>
                  <button class="btn btn-success btn-xs pull-right" style="margin-top: 2px;padding:0px 2px;font-size: 10px;" onclick="submit_note(' . $id . ');">ADD</button>
                  </div>';
        }
    }

    public function addNote() {
        if ($this->input->post()) {
            $lead_id = $this->input->post("id");
            $new_note = $this->input->post("new_note");

            $insertData = array(
                "rel_id" => $lead_id,
                "rel_type" => "lead",
                "description" => $new_note,
                "date_contacted" => date("Y-m-d H:i:s"),
                "addedfrom" => get_staff_user_id(),
                "dateadded" => date("Y-m-d H:i:s")
            );

            $this->db->insert('tblnotes', $insertData);

            $this->db->where('id', $lead_id);
            foreach ($this->db->get("tblleads")->result() as $lead) {
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
                echo '<i class="fa fa-plus-square" style="font-size:15px;color:green;margin-left:98%;cursor:pointer;" onclick="add_note(' . $lead->id . ');"></i>';
            }
        }
    }

    public function leadAttach() {
        if ($this->input->post()) {
            $id = $this->input->post("id");
            echo'   <td>
                        <table class="table table-bordered table-responsive" style="width: 100%;max-width: 100%;font-size: 10px;box-shadow: 1px 1px 5px 1px darkgrey;height: 333px;">
                            <tr style="height: 50px !important;">
                                <th colspan="2" align="left" style="font-size: 15px;color: darkblue;padding:10px 10px 0px 10px;">
                                    <b>UPLOAD FILES TO USER</b>
                                    <a title="Cancel" class="btn btn-danger btn-xs pull-right" onclick="leadAction(' . $id . ', ' . "'cancel'" . ');">
                                            Cancel
                                        </a>
                                </th>
                            </tr>
                            <tr>
                                <td>
                                    <div class="mtop15 mbot15" id="lead_attachments" style="border: 1px dashed #c0ccda;border-radius: 6px;height: 80%;">
                                        <br/><br/><br/>
                                        <div class="row" align="left">';
            $this->db->where("lead_id", $id);
            foreach ($this->db->get(db_prefix() . "leads_attachments")->result() as $leadFiles) {
                echo ' <div class="display-block lead-attachment-wrapper col-md-3" style="padding-top: 0px;">
                <div class = "col-md-10"><div class = "pull-left"><i class = "mime mime-file"></i></div>
                <a href = "' . base_url("uploads\leads_attachments" . $leadFiles->file_name) . '" target = "_blank">' . str_replace($id . "_", "", $leadFiles->file_name) . '</a>
                <p class = "text-muted" style = "margin-left: 25px;">' . $leadFiles->file_type . '</p></div>
                <div class = "col-md-2 text-right"><a href = "javascript::();" class = "text-danger" onclick = "delete_lead_attachment(' . $leadFiles->id . ',' . $leadFiles->lead_id . ');">
                <i class = "fa fa fa-times"></i></a></div><div class = "clearfix"></div>
                <hr></div>';
            }
            echo '</div>
                                    </div>
                                </td>
                                <td style="max-width: 20%;width: 20%;">
                                    <form onclick="performClick(' . "'lead_File'" . ');" class="dropzone mtop15 mbot15 dz-clickable" id="lead-attachment-upload" method="post" accept-charset="utf-8" enctype="multipart/form-data" style="height: 80%;">
                                        <div class="dz-default dz-message"><span><b style="font-size: 15px;">Choose file to upload</b></span></div>
                                        <input type="file" id="lead_File" name="lead_File" style="display: none;" onchange="uploadAttach();">
                                        <input type="hidden" name="lead_id" value="1">
                                    </form>
                                </td>
                            </tr>
                        </table>
                    </td>';
        }
    }

    public function addAttach() {
        if ($this->input->post()) {
            $id = $this->input->post('lead_id');

            $this->db->where("lead_id", $id);
            if ($this->db->get(db_prefix() . "leads_attachments")->num_rows() > 7) {
                echo "maximum files reached";
            } else {
                $config['upload_path'] = './uploads/leads_attachments/';
                $config['allowed_types'] = 'gif|jpg|png|jpeg|jiff|pdf|xls|xlsx|doc|docx';
                $config['max_size'] = 4096;
                $config['file_name'] = $id . "_" . rand() . "_" . $_FILES['lead_File']['name'];

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('lead_File')) {
                    echo "Something went wrong, Please try again.";
                } else {
                    $upload_data = $this->upload->data();

                    $inertData = array(
                        "lead_id" => $id,
                        "file_type" => $upload_data['file_type'],
                        "file_name" => $upload_data['file_name']
                    );

                    $this->db->insert(db_prefix() . "leads_attachments", $inertData);

                    echo '<br/><br/><br/>';
                    $this->db->where("lead_id", $id);
                    foreach ($this->db->get(db_prefix() . "leads_attachments")->result() as $leadFiles) {
                        echo ' <div class="display-block lead-attachment-wrapper col-md-3" style="padding-top: 0px;">
                <div class = "col-md-10"><div class = "pull-left"><i class = "mime mime-file"></i></div>
                <a href = "' . base_url("uploads\leads_attachments" . $leadFiles->file_name) . '" target = "_blank">' . str_replace($id . "_", "", $leadFiles->file_name) . '</a>
                <p class = "text-muted" style = "margin-left: 25px;">' . $leadFiles->file_type . '</p></div>
                <div class = "col-md-2 text-right"><a href = "javascript::();" class = "text-danger" onclick = "delete_lead_attachment(' . $leadFiles->id . ',' . $leadFiles->lead_id . ');">
                <i class = "fa fa fa-times"></i></a></div><div class = "clearfix"></div>
                <hr></div>';
                    }
                }
            }
        }
    }

    public function deleteAttach() {
        if ($this->input->post()) {
            $fileID = $this->input->post("fileID");
            $leadID = $this->input->post("leadID");

            $this->db->where("id", $fileID);
            if ($this->db->delete(db_prefix() . "leads_attachments")) {
                echo $this->db->last_query();
                exit();
                echo '<br/><br/><br/>';
                $this->db->where("lead_id", $leadID);
                foreach ($this->db->get(db_prefix() . "leads_attachments")->result() as $leadFiles) {
                    echo ' <div class="display-block lead-attachment-wrapper col-md-3" style="padding-top: 0px;">
                <div class = "col-md-10"><div class = "pull-left"><i class = "mime mime-file"></i></div>
                <a href = "' . base_url("uploads\leads_attachments" . $leadFiles->file_name) . '" target = "_blank">' . str_replace($id . "_", "", $leadFiles->file_name) . '</a>
                <p class = "text-muted" style = "margin-left: 25px;">' . $leadFiles->file_type . '</p></div>
                <div class = "col-md-2 text-right"><a href = "javascript::();" class = "text-danger" onclick = "delete_lead_attachment(' . $leadFiles->id . ',' . $leadFiles->lead_id . ');">
                <i class = "fa fa fa-times"></i></a></div><div class = "clearfix"></div>
                <hr></div>';
                }
            } else {
                echo "error";
            }
        }
    }

    public function bulkAction() {
        if ($this->input->post()) {
            $method = $this->input->post("method");
            $response = "Something wrong happened, try again later.";
            $leadsIDs = $this->input->post("leads_ids");
            $leadID = explode(",", $leadsIDs);
            if ($method == "bulkDelete") {
                for ($i = 0; $i < count($leadID) - 1; $i++) {
                    $this->db->where('id', $leadID[$i]);
                    if ($this->db->delete('tblleads')) {
                        $response = "Deleted Successfully";
                    }
                }
            } else if ($method == "bulkAction") {
                if ($this->input->post("new_status") != "") {
                    for ($i = 0; $i < count($leadID) - 1; $i++) {
                        $this->db->where('id', $leadID[$i]);
                        if ($this->db->update('tblleads', array("status" => $this->input->post("new_status")))) {
                            $response = "Updated Successfully";
                        }
                    }
                }
                if ($this->input->post("new_source") != "") {
                    for ($i = 0; $i < count($leadID) - 1; $i++) {
                        $this->db->where('id', $leadID[$i]);
                        if ($this->db->update('tblleads', array("source" => $this->input->post("new_source")))) {
                            $response = "Updated Successfully";
                        }
                    }
                }
                if ($this->input->post("new_staff") != "") {
                    for ($i = 0; $i < count($leadID) - 1; $i++) {
                        $this->db->where('id', $leadID[$i]);
                        if ($this->db->update('tblleads', array("assigned" => $this->input->post("new_staff")))) {
                            $response = "Updated Successfully";
                        }
                    }
                }
            }
            echo $response;
        }
    }

}