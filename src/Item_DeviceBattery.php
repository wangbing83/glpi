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

/**
 * Relation between item and devices
**/
class Item_DeviceBattery extends Item_Devices
{
    public static $itemtype_2 = 'DeviceBattery';
    public static $items_id_2 = 'devicebatteries_id';

    protected static $notable = false;


    public static function getSpecificities($specif = '')
    {
        return [
         'serial'             => parent::getSpecificities('serial'),
         'otherserial'        => parent::getSpecificities('otherserial'),
         'locations_id'       => parent::getSpecificities('locations_id'),
         'states_id'          => parent::getSpecificities('states_id'),
         'manufacturing_date' => [
            'long name' => __('Manufacturing date'),
            'short name' => _n('Date', 'Dates', 1),
            'size'       => 10,
            'datatype'   => 'date',
            'id'         => 20,
         ],
         'real_capacity' => [
            'long name' => __('Real capacity (mWh)'),
            'short name' => __('Real capacity'),
            'size'       => 10,
            'id'         => 21,
            'datatype'   => 'progressbar',
            'max'       => 'capacity',    // Field used to represent 100% capacity value
         ]
        ];
    }
}
