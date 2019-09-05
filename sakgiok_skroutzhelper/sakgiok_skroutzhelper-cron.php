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
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
/* Check to security tocken */
if (substr(Tools::encrypt('sakgiok_skroutzhelper/cron'), 0, 10) != Tools::getValue('token') || !Module::isInstalled('sakgiok_skroutzhelper'))
    die('Bad token');

$sakgiok_skroutzhelper = Module::getInstanceByName('sakgiok_skroutzhelper');
/* Check if the module is enabled */
if ($sakgiok_skroutzhelper->active) {
    $id_file = Tools::getValue('file_id');
    $ret = $sakgiok_skroutzhelper->_createXML((int) $id_file, 0, TRUE);
    echo $ret;
}