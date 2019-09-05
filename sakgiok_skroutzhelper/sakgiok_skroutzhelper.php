<?php

/** Copyright 2019 Sakis Gkiokas
 * This file is part of sakgiok_skroutzhelper module for Prestashop.
 *
 * Sakgiok_skroutzhelper is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Sakgiok_skroutzhelper is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * For any recommendations and/or suggestions please contact me
 * at sakgiok@gmail.com
 *
 *  @author    Sakis Gkiokas <sakgiok@gmail.com>
 *  @copyright 2019 Sakis Gkiokas
 *  @license   https://opensource.org/licenses/GPL-3.0  GNU General Public License version 3
 */
if (!defined('_PS_VERSION_'))
    exit;

class sakgiok_skroutzhelper extends Module
{

    protected $_html = '';
    protected $_out_xml = '';
    protected $_skroutz_file_list = '';
    protected $_viewindex = 0;
    protected $_enableAnalytics = false;
    protected $_analyticsShopID = '';
    protected $_displaySkroutzLogo = false;
    private $_checkupdate = false;
    private $_hide_helpForm = true;
    private $path = '';
    private $debug_mode = false;
    public $is17 = false;

    public function __construct()
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            $this->is17 = true;
        }
        $this->name = 'sakgiok_skroutzhelper';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Sakis Gkiokas';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Skroutz helper');
        $this->description = $this->l('Allows you to create the xml file for skroutz.gr and offers skroutz analytics support.');
        $this->ps_versions_compliancy = array('min' => '1.6.0.4', 'max' => '1.7.99.99');
        if (Configuration::get('SAKGIOK_SKROUTZHELPER_AUTO_UPDATE')) {
            $this->_checkupdate = true;
        } else {
            $this->_checkupdate = false;
        }
        $this->debug_mode = Configuration::get('SAKGIOK_SKROUTZHELPER_DEBUG_MODE');
        $this->_enableAnalytics = Configuration::get('SAKGIOK_SKROUTZHELPER_ENABLEANALYTICS');
        $this->path = _PS_MODULE_DIR_ . $this->name . '/';
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        /* Adds Module */
        $res = parent::install() &&
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_INFO_LINK', 'https://sakgiok.gr/programs/sakgiok_skroutzhelper/') &&
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_GITHUB_LINK', 'https://github.com/sakgiok/sakgiok_skroutzhelper') &&
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_FILE_LIST', $this->_skroutz_file_list) &&
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_ENABLEANALYTICS', $this->_enableAnalytics) &&
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_ANALYTICSSHOPID', $this->_analyticsShopID) &&
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_DISPLAYLOGO', $this->_displaySkroutzLogo) &&
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_AUTO_UPDATE', $this->_checkupdate) &&
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_DEBUG_MODE', $this->debug_mode) &&
                $this->registerHook('orderConfirmation') &&
                $this->registerHook('header') &&
                $this->registerHook('footer');
        return (bool) $res;
    }

    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
        /* Deletes Module */
        $res = parent::uninstall() &&
                Configuration::deleteByName('SAKGIOK_SKROUTZHELPER_INFO_LINK') &&
                Configuration::deleteByName('SAKGIOK_SKROUTZHELPER_GITHUB_LINK') &&
                Configuration::deleteByName('SAKGIOK_SKROUTZHELPER_FILE_LIST') &&
                Configuration::deleteByName('SAKGIOK_SKROUTZHELPER_ENABLEANALYTICS') &&
                Configuration::deleteByName('SAKGIOK_SKROUTZHELPER_ANALYTICSSHOPID') &&
                Configuration::deleteByName('SAKGIOK_SKROUTZHELPER_DISPLAYLOGO') &&
                Configuration::deleteByName('SAKGIOK_SKROUTZHELPER_DEBUG_MODE') &&
                Configuration::deleteByName('SAKGIOK_SKROUTZHELPER_AUTO_UPDATE');
        return (bool) $res;
    }

    public function getContent()
    {
        $this->context->controller->addCSS($this->path . 'views/css/sakgiok_skroutzhelper_admin.css');
        $this->_enableAnalytics = Configuration::get('SAKGIOK_SKROUTZHELPER_ENABLEANALYTICS');
        /* Validate & process */
        $bandvalue = $this->_postProcess();
        if (Configuration::get('SAKGIOK_SKROUTZHELPER_AUTO_UPDATE')) {
            $this->_checkupdate = true;
        } else {
            $this->_checkupdate = false;
        }

        if (Tools::isSubmit('sakgiok_skroutzhelper_check_update')) {
            $this->_checkupdate = true;
            $this->_hide_helpForm = false;
        }
        $this->_html = $this->renderHelpForm(false, $this->_checkupdate, $this->_hide_helpForm);
        if ($bandvalue == "VIEWXML") {
            $this->_html .= $this->renderForm_analytics() .
                    $this->renderForm_createxml() .
                    $this->renderForm_listxml() .
                    $this->renderForm_viewxml();
        } else {
            $this->_html .= $bandvalue .
                    $this->renderForm_analytics() .
                    $this->renderForm_createxml() .
                    $this->renderForm_listxml();
        }

        return $this->_html;
    }

    protected function _postProcess()
    {
        $received_values = Tools::getAllValues();
        foreach ($received_values as $key => $value) {
            if ($key == "sakgiok_skroutzhelper_generatexml") {
                if ($received_values['sakgiok_skroutzhelper_xmlname'] != '') {
                    if ($this->_createXML(Tools::safeOutput($received_values['sakgiok_skroutzhelper_xmlname']), $received_values['sakgiok_skroutzhelper_language'])) {
                        return $this->displayConfirmation($this->l('XML File generated'));
                    } else {
                        return $this->displayError($this->l('An Error Occured while creating xml'));
                    }
                } else {
                    return $this->displayError($this->l('Filename cannot be empty'));
                }
            }
            if ($key == "sakgiok_skroutzhelper_deletexmlfile") {
                if ($this->_deleteXMLFile($received_values['sakgiok_skroutzhelper_id_xmlfile'])) {
                    return $this->displayConfirmation($this->l('XML File deleted'));
                } else {
                    return $this->displayError($this->l('Could not delete the file.'));
                }
            }
            if ($key == "sakgiok_skroutzhelper_viewxmlfile") {
                $this->_viewindex = (int) $received_values['sakgiok_skroutzhelper_id_xmlfile'];

                return "VIEWXML";
            }

            if ($key == "submitAnalytics") {
                if ($received_values['sakgiok_skroutzhelper_enableanalytics'] == 0) {
                    $this->_enableAnalytics = FALSE;
                } else {
                    $this->_enableAnalytics = TRUE;
                }
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_ENABLEANALYTICS', $this->_enableAnalytics);

                if ($received_values['sakgiok_skroutzhelper_displaylogo'] == 0) {
                    $this->_displaySkroutzLogo = FALSE;
                } else {
                    $this->_displaySkroutzLogo = TRUE;
                }
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_DISPLAYLOGO', $this->_displaySkroutzLogo);

                if ($received_values['sakgiok_skroutzhelper_debug_mode'] == 0) {
                    $this->debug_mode = FALSE;
                } else {
                    $this->debug_mode = TRUE;
                }
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_DEBUG_MODE', $this->debug_mode);

                $this->_analyticsShopID = $received_values['sakgiok_skroutzhelper_analyticsshopid'];
                Configuration::updateValue('SAKGIOK_SKROUTZHELPER_ANALYTICSSHOPID', $this->_analyticsShopID);
                return;
            }
        }
    }

    private function renderForm_analytics()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Skroutz Analytics'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Shop ID'),
                        'name' => 'sakgiok_skroutzhelper_analyticsshopid',
                        'desc' => $this->l('You must enter the correct shop ID. You can get it from skroutz.')
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable Analytics'),
                        'name' => 'sakgiok_skroutzhelper_enableanalytics',
                        'desc' => $this->l('Enable or disable analytics globally.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display Skroutz Logo'),
                        'name' => 'sakgiok_skroutzhelper_displaylogo',
                        'desc' => $this->l('Display Skroutz logo at footer.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable debug mode'),
                        'name' => 'sakgiok_skroutzhelper_debug_mode',
                        'desc' => $this->l('Displays the results instead of sending data to skroutz.'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAnalytics';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigAnalyticsFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigAnalyticsFieldsValues()
    {
        return array(
            'sakgiok_skroutzhelper_analyticsshopid' => Configuration::get('SAKGIOK_SKROUTZHELPER_ANALYTICSSHOPID'),
            'sakgiok_skroutzhelper_enableanalytics' => Configuration::get('SAKGIOK_SKROUTZHELPER_ENABLEANALYTICS'),
            'sakgiok_skroutzhelper_displaylogo' => Configuration::get('SAKGIOK_SKROUTZHELPER_DISPLAYLOGO'),
            'sakgiok_skroutzhelper_debug_mode' => Configuration::get('SAKGIOK_SKROUTZHELPER_DEBUG_MODE'),
        );
    }

    private function renderForm_createxml()
    {
        $tot_pr = $this->_getProductNum();
        $ind_pr = $this->_getProductNum(TRUE);
        $this->context->smarty->assign(array(
            'sakgiok_skroutzhelper_total_products' => $tot_pr,
            'sakgiok_skroutzhelper_indexable_products' => $ind_pr,
            'sakgiok_skroutzhelper_form_action' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name,
            'sakgiok_skroutzhelper_languages' => $this->context->controller->getLanguages()
        ));

        return $this->display(__FILE__, 'views/templates/admin/form_createxml.tpl');
    }

    private function renderForm_listxml()
    {
        $langs = $this->context->controller->getLanguages();
        $xmlnames = Configuration::get('SAKGIOK_SKROUTZHELPER_FILE_LIST');
        $xml_count = 0;
        if ($xmlnames == "") {
            $xmlnames_arr = '';
        } else {
            $xmlnames_arr = array();
            $xmlnames_arr_tmp = explode('|', $xmlnames);
            $i = 0;
            foreach ($xmlnames_arr_tmp as $value) {
                $xmlnames_arr[$i]['file_id'] = $i;
                $xmlnames_arr[$i]['filename'] = $this->getConfFilename($value);
                $xmlnames_arr[$i]['lang_id'] = $this->getConfLang($value);
                $xmlnames_arr[$i]['lang'] = "";
                $cl = (int) $xmlnames_arr[$i]['lang_id'];
                foreach ($langs as $l) {
                    if ($l['id_lang'] == $cl) {
                        $xmlnames_arr[$i]['lang'] = $l['name'];
                    }
                }
                $i++;
            }
            $xml_count = count($xmlnames_arr);
        }

        $sakgiok_skroutzhelper_baseuri = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $sakgiok_skroutzhelper_baseurl = Configuration::get('PS_SSL_ENABLED') ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_;
        $this->context->smarty->assign(array(
            'sakgiok_skroutzhelper_baseurl' => $sakgiok_skroutzhelper_baseuri,
            'sakgiok_skroutzhelper_xmlnames_arr' => $xmlnames_arr,
            'sakgiok_skroutzhelper_xml_count' => $xml_count,
            'sakgiok_skroutzhelper_current_url' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name,
            'sakgiok_skroutzhelper_cronbase' => $sakgiok_skroutzhelper_baseurl . _MODULE_DIR_ . 'sakgiok_skroutzhelper/sakgiok_skroutzhelper-cron.php?token=' . substr(Tools::encrypt('sakgiok_skroutzhelper/cron'), 0, 10)
        ));

        return $this->display(__FILE__, 'views/templates/admin/form_listxml.tpl');
    }

    private function renderForm_viewxml()
    {
        $xmlnames_arr = explode('|', Configuration::get('SAKGIOK_SKROUTZHELPER_FILE_LIST'));
        $file_path = _PS_ROOT_DIR_ . '/' . $this->getConfFilename($xmlnames_arr[(int) $this->_viewindex]);
        $xml_content = file_get_contents($file_path);
        if ($xml_content === false) {
            $this->_html .= $this->displayError($this->l('Unable to read from xml file.'));
            return false;
        }
        //$xml_content="<p>".str_replace("\n", "</p><p>", $xml_content)."</p>";
        $this->context->smarty->assign(array(
            'sakgiok_skroutzhelper_filepath' => $file_path,
            'sakgiok_skroutzhelper_filecontents' => $xml_content
        ));

        return $this->display(__FILE__, 'views/templates/admin/form_viewxml.tpl');
    }

    public function _createXML($in_filename, $in_lang = 0, $refresh = false)
    {

        $f_filename = "";
        if ($refresh) {
            $xmlnames = Configuration::get('SAKGIOK_SKROUTZHELPER_FILE_LIST');
            if ($xmlnames == "") {
                $xmlnames_arr = '';
            } else {
                $xmlnames_arr = array();
                $xmlnames_arr_tmp = explode('|', $xmlnames);
                $i = 0;
                foreach ($xmlnames_arr_tmp as $value) {
                    $xmlnames_arr[$i]['file_id'] = $i;
                    $xmlnames_arr[$i]['filename'] = $this->getConfFilename($value);
                    $xmlnames_arr[$i]['lang_id'] = $this->getConfLang($value);
                    $i++;
                }
            }
            $f_filename = $xmlnames_arr[$in_filename]['filename'];
            $in_lang = $xmlnames_arr[$in_filename]['lang_id'];
        } else {
            $f_filename = $in_filename;
        }

        $lang = $in_lang;
        $http_pre = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://');
        $spc = "   ";
        $sql = 'SELECT p.id_product as id, p.reference as ref, pl.link_rewrite as link, mf.name as mfname FROM ' . _DB_PREFIX_ . 'product p '
                . 'LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (p.id_product = pl.id_product) '
                . 'LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer mf ON (p.id_manufacturer = mf.id_manufacturer) '
                . 'WHERE pl.`id_lang`=' . $lang;
//                . 'WHERE p.`skroutz_available`=1 AND pl.`id_lang`=' . $lang;
        $res = Db::getInstance()->executeS($sql);

        $this->_out_xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $this->_out_xml .= '<store>' . "\n";
        $this->_out_xml .= $spc . '<created_at>' . date('Y-m-d H:i:s') . '</created_at>' . "\n" .
                $spc . '<products>' . "\n";
        foreach ($res as $key => $value) {
            $pr_id = $value['id'];
            $prod = new Product($pr_id, TRUE);
            $link = new LinkCore();
            $pr_name = $prod->name[$lang];
            $pr_price = $prod->getPrice(TRUE, null, 2);
            $pr_manufacturer = $prod->manufacturer_name;
            $pr_reference = $prod->reference;
            $pr_stock = $prod->getQuantity($pr_id);
            if ($pr_stock > 0) {
                $pr_instock = 'Y';
                $pr_availability = 'Available in store / Delivery 1 to 3 days';
            } else {
                $pr_instock = 'N';
                $pr_availability = 'Delivery 4 to 10 days';
            }
            $pr_img = $prod->getImages($lang);
            $pr_link = $link->getProductLink($prod, null, null, null, $lang);
            $pr_link_rewrite = $prod->link_rewrite[$lang];
            $pr_cat_full = $prod->getProductCategoriesFull($pr_id, $lang);
            $pr_cat = array();
            $i = 0;
            foreach ($pr_cat_full as $cat_key => $cat_value) {
                $pr_cat[$i] = '';
                $cat = new CategoryCore($cat_key, $lang);
                $par = $cat->getParentsCategories($lang);
                foreach ($par as $par_key => $par_value) {
                    if ($par_value['id_category'] > 2) {
                        if ($pr_cat[$i] == '') {
                            $pr_cat[$i] = $par_value['name'];
                        } else {
                            $pr_cat[$i] = $par_value['name'] . " > " . $pr_cat[$i];
                        }
                    }
                }
                unset($cat);
                $i++;
            }
            $this->_out_xml .= $spc . $spc . '<product>' . "\n";
            $this->_out_xml .= $spc . $spc . $spc . '<id>' . $pr_id . '</id>' . "\n" .
                    $spc . $spc . $spc . '<name><![CDATA[' . $pr_name . ' (' . $pr_reference . ')' . ']]></name>' . "\n" .
                    $spc . $spc . $spc . '<link><![CDATA[' . $pr_link . ']]></link>' . "\n";

            if ($pr_img) {
                $imagePath = $http_pre . $link->getImageLink($pr_link_rewrite, $pr_img[0]['id_image']);
                $this->_out_xml .= $spc . $spc . $spc . '<image><![CDATA[' . $imagePath . ']]></image>' . "\n";
                foreach ($pr_img as $imgkey => $imgvalue) {
                    if ($imgkey > 0) {
                        $imagePath = $http_pre . $link->getImageLink($pr_link_rewrite, $imgvalue['id_image']);
                        $this->_out_xml .= $spc . $spc . $spc . '<additionalimage><![CDATA[' . $imagePath . ']]></additionalimage>' . "\n";
                    }
                }
            } else {
                $this->_out_xml .= $spc . $spc . $spc . '<image><![CDATA[]]></image>' . "\n";
            }
            $pr_cat_f = "";
            for ($i = 0; $i < count($pr_cat); $i++) {
                if ($pr_cat[$i] != "") {
                    $pr_cat_f = $pr_cat[$i];
                    break;
                }
            }
            $this->_out_xml .= $spc . $spc . $spc . '<category><![CDATA[' . $pr_cat_f . ']]></category>' . "\n" .
                    $spc . $spc . $spc . '<price_with_vat>' . $pr_price . '</price_with_vat>' . "\n" .
                    $spc . $spc . $spc . '<manufacturer><![CDATA[' . $pr_manufacturer . ']]></manufacturer>' . "\n" .
                    $spc . $spc . $spc . '<mpn>' . $pr_reference . '</mpn>' . "\n" .
                    $spc . $spc . $spc . '<instock>' . $pr_instock . '</instock>' . "\n" .
                    $spc . $spc . $spc . '<availability>' . $pr_availability . '</availability>' . "\n";
            $this->_out_xml .= $spc . $spc . '</product>' . "\n";
            unset($prod);
            unset($link);
        }
        $this->_out_xml .= $spc . '</products>' . "\n";
        $this->_out_xml .= '</store>';
        if ($fd = @fopen(_PS_ROOT_DIR_ . '/' . $f_filename, 'w')) {
            if (!@fwrite($fd, $this->_out_xml)) {
                if (!$refresh) {
                    $this->_html .= $this->displayError($this->l('Unable to write to the xml file.'));
                    return false;
                } else {
                    return sprintf($this->l('Filename %s - Lang %s. Unable to write to the xml file.'), $f_filename, $lang);
                }
            }
            if (!@fclose($fd)) {
                if (!$refresh) {
                    $this->_html .= $this->displayError($this->l('Cannot close the xml file.'));
                    return false;
                } else {
                    return sprintf($this->l('Filename %s - Lang %s. Cannot close the xml file.'), $f_filename, $lang);
                }
            }
        } else {
            if (!$refresh) {
                $this->_html .= $this->displayError($this->l('Unable to update the xml file. Please check the xml file\'s writing permissions.'));
                return false;
            } else {
                return sprintf($this->l('Filename %s - Lang %s. Unable to update the xml file. Please check the xml file\'s writing permissions.'), $f_filename, $lang);
            }
        }

        if (!$refresh) {
            $xmlnames = Configuration::get('SAKGIOK_SKROUTZHELPER_FILE_LIST');
            if ($xmlnames == "") {
                $xmlnames = $f_filename . $in_lang;
            } else {
                $xmlnames .= '|' . $f_filename . $in_lang;
            }
            Configuration::updateValue('SAKGIOK_SKROUTZHELPER_FILE_LIST', $xmlnames);
            return true;
        } else {
            return sprintf($this->l('Filename %s - Lang %s. Success in refreshing skroutz xml file from cron link.'), $f_filename, $lang);
        }
    }

    private function _deleteXMLFile($in_file_num)
    {
        $xmlnames_arr = explode('|', Configuration::get('SAKGIOK_SKROUTZHELPER_FILE_LIST'));
        if (@unlink(_PS_ROOT_DIR_ . '/' . $this->getConfFilename($xmlnames_arr[(int) $in_file_num]))) {
            $xmlnames_arr[(int) $in_file_num] = '|';
            $xmlnames = "";
            foreach ($xmlnames_arr as $value) {
                if ($value != '|') {
                    if ($xmlnames == "") {
                        $xmlnames .= $value;
                    } else {
                        $xmlnames .= '|' . $value;
                    }
                }
            }
            Configuration::updateValue('SAKGIOK_SKROUTZHELPER_FILE_LIST', $xmlnames);
            return true;
        }
        return false;
    }

    private function _getProductNum($onlyvalid = false)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'product pr';
        if ($onlyvalid) {
            //$sql .= ' WHERE skroutz_available=1';
        }
        $res = Db::getInstance()->executeS($sql);
        return count($res);
    }

    public function getConfigFieldsValues()
    {
        $fields = array();
        return $fields;
    }

    public function getConfFilename($inF)
    {
        $out = "";
        if (strlen($inF) > 0) {
            $out = substr($inF, 0, strlen($inF) - 1);
        }
        return $out;
    }

    public function getConfLang($inF)
    {
        $out = "";
        if (strlen($inF) > 0) {
            $out = substr($inF, strlen($inF) - 1);
        }
        return $out;
    }

    /**
     * Builds an Analytics Ecommerce addOrder action.
     *
     * @param array $order The completed order to report.
     * @return string The JavaScript representation of an Analytics Ecommerce addOrder action.
     */
    private function addOrderAction(&$order)
    {
        $out = '';
        if ($this->debug_mode) {
            $out = '<div>Order Data</div>';
            $out .= print_r($order, true);
        } else {
            $order_data = json_encode($order);
            $out = "skroutz_analytics('ecommerce', 'addOrder', JSON.stringify({$order_data}));";
        }
        return $out;
    }

    /**
     * Builds an Analytics Ecommerce addItem action.
     *
     * @param array $order The completed order to report.
     * @param array $item The purchesed product to report, part of this order.
     * @return string The JavaScript representation of an Analytics Ecommerce addItem action.
     */
    private function addItemAction(&$order, &$item)
    {
        $out = '';
        $item_data_array = array(
            'order_id' => $order['order_id'],
            'product_id' => $item['product_id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity']
        );
        if ($this->debug_mode) {
            $out = '<div>' . print_r($item_data_array, true) . '</div>';
        } else {
            $item_data = json_encode($item_data_array);
            $out = "skroutz_analytics('ecommerce', 'addItem', JSON.stringify({$item_data}));";
        }
        return $out;
    }

    public function hookHeader($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/sakgiok_skroutzhelper.css');
        $this->_enableAnalytics = Configuration::get('SAKGIOK_SKROUTZHELPER_ENABLEANALYTICS');
        if (!$this->_enableAnalytics)
            return;
        $this->_analyticsShopID = Configuration::get('SAKGIOK_SKROUTZHELPER_ANALYTICSSHOPID');
        $this->context->smarty->assign(array(
            'sk_shop_id' => $this->_analyticsShopID
        ));

        return $this->display(__FILE__, 'views/templates/hook/header_js.tpl');
    }

    public function getCODwfeeplusValues($order, $cart)
    {
        $ret = array(
            'fee' => 0,
            'shipping' => 0,
            'sipping_tax' => 0,
            'integration_product' => false,
            'codproduct_id' => Configuration::get('SG_CODWFEEPLUS_PRODUCT_ID'),
        );
        $codobj = new codwfeeplus();
        $id_shop = $order->id_shop;
        $id_carrier = $order->id_carrier;
        $address = Address::getCountryAndState($order->id_address_delivery);
        $id_country = $address['id_country'] ? $address['id_country'] : Configuration::get('PS_COUNTRY_DEFAULT');
        $id_state = $address['id_state'];
        $id_zone = Address::getZoneById($order->id_address_delivery);
        $cartvalue = $order->total_products_wt;
        $cat_array = array();
        $manufacturers = array();
        $suppliers = array();
        $products = $order->getCartProducts();
        foreach ($products as $product) {
            if ($product['id_product'] != $ret['codproduct_id']) {
                $cat = Product::getProductCategoriesFull($product['id_product']);
                foreach ($cat as $value) {
                    if (!in_array($value['id_category'], $cat_array)) {
                        $cat_array[] = $value['id_category'];
                    }
                }
                if (!in_array($product['id_manufacturer'], $manufacturers)) {
                    $manufacturers[] = $product['id_manufacturer'];
                }
                if (!in_array($product['id_supplier'], $suppliers)) {
                    $suppliers[] = $product['id_supplier'];
                }
            } else {
                $cartvalue = $cartvalue - $product['product_price_wt'];
            }
        }
        $carrier = new Carrier($id_carrier);
        if ($carrier->shipping_method == Carrier::SHIPPING_METHOD_PRICE) {
            $carriervalue = $carrier->getDeliveryPriceByPrice($cartvalue, $id_zone);
        } else {
            $carriervalue = $carrier->getDeliveryPriceByWeight($order->getTotalWeight(), $id_zone);
        }
        $addr = Address::initialize((int) $order->id_address_delivery);
        $carrier_tax = ((float) $carrier->getTaxesRate($addr)) * 0.01;

        $carriervalue_wt = Tools::ps_round((float) $carriervalue * (1.0 + (float) $carrier_tax), 9);
        $carriervalue_tax = Tools::ps_round((float) $carriervalue * (float) $carrier_tax, 9);
        $cust_group = array();
        if (Group::isFeatureActive()) {
            $cust_group = Customer::getGroupsStatic((int) $cart->id_customer);
        }
        $CODfee = $codobj->getCost_common($id_carrier, $id_country, $id_state, $id_zone, $cartvalue, $carriervalue_wt, $cat_array, $cust_group, $manufacturers, $suppliers, $id_shop);

        $cond_integration = $codobj->_cond_integration;
        $integration = Configuration::get('SG_CODWFEEPLUS_INTEGRATION_WAY');
        $integration_product = false;
        if ($integration == 0) {
            if ($cond_integration == 1 && $CODfee != 0) {
                $integration_product = true;
            } else {
                $integration_product = false;
            }
        } elseif ($integration == 2 && $CODfee != 0) {
            $integration_product = true;
        } else {
            $integration_product = false;
        }

        $ret['fee'] = $CODfee;
        $ret['integration_product'] = $integration_product;
        $ret['shipping'] = $carriervalue_wt;
        $ret['sipping_tax'] = $carriervalue_tax;
        return $ret;
    }

    public function hookOrderConfirmation($params)
    {
        if (!$this->_enableAnalytics)
            return;
        if ($this->is17) {
            $order = $params['order'];
        } else {
            $order = $params['objOrder'];
        }
        $cart = $params['cart'];

        $items = $order->getProductsDetail();
        $codValues = array(
            'fee' => 0,
            'shipping' => 0,
            'sipping_tax' => 0,
            'integration_product' => false,
            'codproduct_id' => 0,
        );
        if ($order->module == "codwfeeplus") {
            $codValues = $this->getCODwfeeplusValues($order, $cart);
        } else {
            $id_zone = Address::getZoneById($order->id_address_delivery);
            $cartvalue = $order->total_products_wt;
            $carrier = new Carrier($order->id_carrier);
            if ($carrier->shipping_method == Carrier::SHIPPING_METHOD_PRICE) {
                $carriervalue = $carrier->getDeliveryPriceByPrice($cartvalue, $id_zone);
            } else {
                $carriervalue = $carrier->getDeliveryPriceByWeight($order->getTotalWeight(), $id_zone);
            }
            $addr = Address::initialize((int) $order->id_address_delivery);
            $carrier_tax = ((float) $carrier->getTaxesRate($addr)) * 0.01;

            $codValues['shipping'] = Tools::ps_round((float) $carriervalue * (1.0 + (float) $carrier_tax), 9);
            $codValues['sipping_tax'] = Tools::ps_round((float) $carriervalue * (float) $carrier_tax, 9);
        }

        $codfee = $codValues['fee'];
        $cod_prod_integr = $codValues['integration_product'];
        $cod_prod_id = $codValues['codproduct_id'];
        $shipping = $codValues['shipping'];
        $shipping_tax = $codValues['sipping_tax'];

        $total_paid = $order->total_paid - $codfee;

        $tax = $order->total_products_wt - $order->total_products + $shipping_tax;
        if ($cod_prod_integr) {
            $p = new Product($cod_prod_id);
            $tax_percent = ((float) $p->getTaxesRate()) * 0.01;
            $price_notax = Tools::ps_round((float) $codfee / (1.0 + (float) $tax_percent), 9);
            $tax -= $codfee - $price_notax;
        }

        $order_sa = array(
            'order_id' => $order->id,
            'revenue' => Tools::ps_round((float) $total_paid, 2),
            'shipping' => Tools::ps_round((float) $shipping, 2),
            'tax' => Tools::ps_round((float) $tax, 2)
        );

        $items_sa = array();
        $i = 0;
        foreach ($items as $value) {
            if ($value['product_id'] != $cod_prod_id) {
                $items_sa[$i] = array(
                    'order_id' => $value['id_order'],
                    'product_id' => $value['product_id'],
                    'name' => $value['product_name'],
                    'price' => Tools::ps_round((float) $value['unit_price_tax_incl'], 2),
                    'quantity' => $value['product_quantity']
                );

                $i++;
            }
        }
        if ($this->debug_mode) {
            $out = '<div>';
        } else {
            $out = '<script>';
        }
//        // Print the Analytics Ecommerce AddOrder action
        $out .= $this->addOrderAction($order_sa);
        if ($this->debug_mode) {
            $out .= '<div>PRODUCTS</div>';
        }
        // Print each Analytics Ecommerce AddItem action
        foreach ($items_sa as &$item) {
            $out .= $this->addItemAction($order_sa, $item);
        }
        if ($this->debug_mode) {
            $out .= '</div>';
        } else {
            $out .= '</script>';
        }

        return $out;
    }

    public function hookFooter($params)
    {
        $this->_displaySkroutzLogo = Configuration::get('SAKGIOK_SKROUTZHELPER_DISPLAYLOGO');
        if (!$this->_displaySkroutzLogo)
            return;

//        if (!$this->isCached('sakgiok_skroutzhelper_logo.tpl', $this->getCacheId('sakgiok_skroutzhelper_logo'))) {
//            
//            $this->smarty->assign(array(
//                
//            ));
//        }

        return $this->display(__FILE__, 'sakgiok_skroutzhelper_logo.tpl', $this->getCacheId());
    }

    //HELP FORM

    public function renderHelpForm($ajax = false, $check_update = false, $hide = true)
    {
        $ret = '';
        $update_status = array(
            'res' => '',
            'cur_version' => '',
            'download_link' => '',
            'info_link' => Configuration::get('SAKGIOK_SKROUTZHELPER_INFO_LINK'),
            'github_link' => Configuration::get('SAKGIOK_SKROUTZHELPER_GITHUB_LINK'),
            'out' => '',
        );
        if ($check_update) {
            $ret = $this->getUpdateStatus();
            if (Tools::strpos($ret, 'error') === false) {
                $update_status['res'] = $this->_updatestatus['res'];
                $update_status['cur_version'] = $this->_updatestatus['cur_version'];
                $update_status['download_link'] = $this->_updatestatus['download_link'];
                $update_status['info_link'] = $this->_updatestatus['info_link'];
                $update_status['github_link'] = $this->_updatestatus['github_link'];
            } else {
                $update_status['res'] = 'error';
                if ($ret == 'error_res') {
                    $update_status['out'] = $this->l('Update site reported an error.');
                } elseif ($ret == 'error_resp') {
                    $update_status['out'] = $this->l('Invalid response from the update site.');
                } elseif ($ret == 'error_url') {
                    $update_status['out'] = $this->l('Update site could not be reached.');
                }
            }
        }
        $this->context->smarty->assign(array(
            'help_title' => $this->l('INFO'),
            'help_sub' => $this->l('click to toggle'),
            'module_name' => $this->displayName,
            'module_version' => $this->version,
            'help_ajax' => $ajax,
            'css_file' => _PS_MODULE_DIR_ . '/' . $this->name . '/views/css/sakgiok_skroutzhelper_admin.css',
            'update' => $update_status,
            'href' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name,
            'hide' => $hide,
        ));
        $lang_iso = Tools::strtolower(trim($this->context->language->iso_code));

        if (Tools::file_exists_cache(_PS_MODULE_DIR_ . '/' . $this->name . '/views/templates/admin/help_' . $lang_iso . '.tpl')) {
            $ret = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/' . $this->name . '/views/templates/admin/help_' . $lang_iso . '.tpl');
        } else {
            $ret = $this->context->smarty->fetch(_PS_MODULE_DIR_ . '/' . $this->name . '/views/templates/admin/help_en.tpl');
        }


        return $ret;
    }

    public function getUpdateStatus()
    {
        $ret = '';
        $info_var = 'SAKGIOK_SKROUTZHELPER_INFO_LINK';
        $git_var = 'SAKGIOK_SKROUTZHELPER_GITHUB_LINK';

        $version_arr = explode('.', $this->version);
        $Maj = (int) $version_arr[0];
        $Min = (int) $version_arr[1];
        $Rev = (int) $version_arr[2];

        $P = base64_encode(_PS_BASE_URL_ . __PS_BASE_URI__);
        $base_url = 'http://programs.sakgiok.gr/';
        $url = $base_url . $this->name . '/version.php?Maj=' . $Maj . '&Min=' . $Min . '&Rev=' . $Rev . '&P=' . $P;

        $response = Tools::file_get_contents($url);
        if ($response) {
            $arr = json_decode($response, true);
            if (isset($arr['res'])) {
                if ($arr['res'] == 'update') {
                    $this->_updatestatus['res'] = $arr['res'];
                    $this->_updatestatus['cur_version'] = $arr['cur_version'];
                    $this->_updatestatus['download_link'] = $arr['download_link'];
                    $this->_updatestatus['info_link'] = $arr['info_link'];
                    $this->_updatestatus['github_link'] = $arr['github_link'];
                    $ret = 'update';
                    $this->updateValueAllShops($info_var, $this->_updatestatus['info_link']);
                    $this->updateValueAllShops($git_var, $this->_updatestatus['github_link']);
                } elseif ($arr['res'] == 'current') {
                    $this->_updatestatus['res'] = $arr['res'];
                    $this->_updatestatus['cur_version'] = $arr['cur_version'];
                    $this->_updatestatus['download_link'] = $arr['download_link'];
                    $this->_updatestatus['info_link'] = $arr['info_link'];
                    $this->_updatestatus['github_link'] = $arr['github_link'];
                    $this->updateValueAllShops($info_var, $this->_updatestatus['info_link']);
                    $this->updateValueAllShops($git_var, $this->_updatestatus['github_link']);
                    $ret = 'current';
                } else {
                    $ret = 'error_res';
                }
            } else {
                $ret = 'error_resp';
            }
        } else {
            $ret = 'error_url';
        }

        return $ret;
    }

    public function updateValueAllShops($key, $value)
    {
        $this->storeContextShop();
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        $res = true;

        if (Shop::isFeatureActive()) {
            $shop_list = Shop::getShops(true, null, true);
            foreach ($shop_list as $shop) {
                Shop::setContext(Shop::CONTEXT_SHOP, $shop);
                $res &= Configuration::updateValue($key, $value);
            }
        } else {
            $res &= Configuration::updateValue($key, $value);
        }
        $this->resetContextShop();
    }

    public function storeContextShop()
    {
        if (Shop::isFeatureActive()) {
            $this->tmp_shop_context_type = Shop::getContext();
            if ($this->tmp_shop_context_type != Shop::CONTEXT_ALL) {
                if ($this->tmp_shop_context_type == Shop::CONTEXT_GROUP) {
                    $this->tmp_shop_context_id = Shop::getContextShopGroupID();
                } else {
                    $this->tmp_shop_context_id = Shop::getContextShopID();
                }
            }
        }
    }

    public function resetContextShop()
    {
        if (Shop::isFeatureActive()) {
            if ($this->tmp_shop_context_type != Shop::CONTEXT_ALL) {
                Shop::setContext($this->tmp_shop_context_type, $this->tmp_shop_context_id);
            } else {
                Shop::setContext($this->tmp_shop_context_type);
            }
        }
    }

}
