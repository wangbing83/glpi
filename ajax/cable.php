<?php

/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2022 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

use Glpi\Socket;

include('../inc/includes.php');

// Send UTF8 Headers
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

$action = (!isset($_POST['action'])) ? $_GET["action"] : $_POST['action'];

switch ($action) {
    case 'get_items_from_itemtype':
        if ($_POST['itemtype'] && class_exists($_POST['itemtype'])) {
            $_POST['itemtype']::dropdown(['name'                => $_POST['dom_name'],
                                       'rand'                => $_POST['dom_rand'],
                                       'display_emptychoice' => true,
                                       'display_dc_position' => true,
                                       'width'               => '100%',
            ]);
        }
        break;

    case 'get_socket_dropdown':
        if (
            (isset($_GET['itemtype']) && class_exists($_GET['itemtype']))
            && isset($_GET['items_id'])
        ) {
            Socket::dropdown(['name'         =>  $_GET['dom_name'],
                           'condition'    => ['socketmodels_id'   => isset($_GET['socketmodels_id']) ?? 0 ,
                                             'itemtype'           => $_GET['itemtype'],
                                             'items_id'           => $_GET['items_id']],
                           'displaywith'  => ['itemtype', 'items_id', 'networkports_id'],
            ]);
        }
        break;

    case 'get_networkport_dropdown':
         NetworkPort::dropdown(['name'                => 'networkports_id',
                                'display_emptychoice' => true,
                                'condition'           => ['items_id' => $_GET['items_id'],
                                                          'itemtype' => $_GET['itemtype']]]);
        break;


    case 'get_item_breadcrum':
        if (
            (isset($_GET['itemtype']) && class_exists($_GET['itemtype']))
            && isset($_GET['items_id']) && $_GET['items_id'] > 0
        ) {
            if (method_exists($_GET['itemtype'], 'getDcBreadcrumbSpecificValueToDisplay')) {
                echo $_GET['itemtype']::getDcBreadcrumbSpecificValueToDisplay($_GET['items_id']);
            }
        } else {
            echo "";
        }
        break;
}
