<?php

/**
 * Let's Encrypt certificates controller.
 *
 * @category   apps
 * @package    lets-encrypt
 * @subpackage controllers
 * @author     Marc Laporte
 * @copyright  2017 Marc Laporte
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       https://github.com/eglooca/app-lets-encrypt
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Let's Encrypt certificates controller.
 *
 * @category   apps
 * @package    lets-encrypt
 * @subpackage controllers
 * @author     Marc Laporte
 * @copyright  2017 Marc Laporte
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       https://github.com/eglooca/app-lets-encrypt
 */

class Certificate extends ClearOS_Controller
{
    /**
     * Let's Encrypt certificates default controller.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('lets_encrypt');
        $this->load->library('lets_encrypt/Lets_Encrypt');

        // Load view data
        //---------------

        try {
            $data['certificates'] = $this->lets_encrypt->get_certificates();
        } catch (Engine_Engine_Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('lets_encrypt/summary', $data, lang('lets_encrypt_certificates'));
    }

    /**
     * Add view
     *
     * @param string $type type of certificate
     *
     * @return view
     */

    function add($type)
    {
        // Load dependencies
        //------------------

        $this->lang->load('lets_encrypt');
        $this->load->library('lets_encrypt/Lets_Encrypt');

        // Set validation rules
        //---------------------

        $this->form_validation->set_policy('email', 'lets_encrypt/Lets_Encrypt', 'validate_email', TRUE);
        $this->form_validation->set_policy('domain', 'lets_encrypt/Lets_Encrypt', 'validate_domain', TRUE);
        $this->form_validation->set_policy('domains', 'lets_encrypt/Lets_Encrypt', 'validate_domains');
        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        $data['provisioning'] = FALSE;

        if ($this->input->post('submit') && $form_ok) {
            try {
                $this->lets_encrypt->add(
                    $this->input->post('email'),
                    $this->input->post('domain'),
                    $this->input->post('domains'),
                    TRUE
                );

                $data['provisioning'] = TRUE;
                $data['domain'] = $this->input->post('domain');
                $data['domains'] = $this->input->post('domains');

                // Backgrounded, using Ajax to complete.
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------

        try {
            $data['email'] = $this->lets_encrypt->get_email();
        } catch (Engine_Engine_Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('lets_encrypt/add', $data, lang('lets_encrypt_certificates'));
    }

    /**
     * Delete view.
     *
     * @param string $name certificate basename
     *
     * @return view
     */

    function delete($name = NULL)
    {
        $confirm_uri = '/app/lets_encrypt/certificate/destroy/' . $name;
        $cancel_uri = '/app/lets_encrypt';
        $items = array($name);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    /**
     * Remove view.
     *
     * @param string $name certificate basename
     *
     * @return view
     */

    function destroy($name)
    {
        // Load libraries
        //---------------

        $this->load->library('Lets_Encrypt');

        // Handle form submit
        //-------------------

        try {
            $this->lets_encrypt->delete($name);
            $this->page->set_status_deleted();

            redirect('/lets_encrypt');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    /**
     * View view.
     *
     * @return view
     */

    function view($certificate)
    {
        $this->_item('view', $certificate);
    }

    /**
     * Common install/download method.
     *
     * @return string certificate
     */
    
    function download($certificate)
    {
        // Load dependencies
        //------------------

        $this->load->library('lets_encrypt/Lets_Encrypt');

        // Load view data
        //---------------

        try {
            $attributes = $this->lets_encrypt->get_certificate_attributes($certificate);
        } catch (Engine_Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load view
        //----------

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header("Content-Transfer-Encoding: binary");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . $certificate . ".pem;");

        echo $attributes['file_contents'];
    }

    /**
     * Common view/edit form.
     *
     * @param string $form_type   form type
     * @parma string $certificate certificate
     *
     * @return view
     */

    function _item($form_type, $certificate)
    {
        // Load dependencies
        //------------------

        $this->lang->load('lets_encrypt');
        $this->load->library('lets_encrypt/Lets_Encrypt');
        $this->load->library('certificate_manager/Certificate_Manager');

        // Load view data
        //---------------

        try {
            $data['form_type'] = $form_type;
            $data['certificate'] = $certificate;

            if ($form_type === 'add') {
            } else {
                $attributes = $this->lets_encrypt->get_certificate_attributes($certificate);

                $data['issued'] = $attributes['issued'];
                $data['expires'] = $attributes['expires'];
                $data['key_size'] = $attributes['key_size'];
                $data['domains'] = $attributes['domains'];
                $data['details'] = $attributes['details'];

                $data['state'] = $this->certificate_manager->get_state($certificate);
            }
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('lets_encrypt/certificate', $data, lang('lets_encrypt_certificate'));
    }
}
