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

use Glpi\Plugin\Hooks;

/**
 * This class manages locks
 * Lock management is available for objects and link between objects. It relies on the use of
 * a is_dynamic field, to incidate if item supports lock, and is_deleted field to incidate if the
 * item or link is locked
 * By setting is_deleted to 0 again, the item is unlock
 *
 * Note : GLPI's core supports locks for objects. It's up to the external inventory tool to manage
 * locks for fields
 *
 * @since 0.84
 **/
class Lock extends CommonGLPI
{
    public static function getTypeName($nb = 0)
    {
        return _n('Lock', 'Locks', $nb);
    }


   /**
    * Display form to unlock fields and links
    *
    * @param CommonDBTM $item the source item
   **/
    public static function showForItem(CommonDBTM $item)
    {
        global $DB;

        $ID       = $item->getID();
        $itemtype = $item->getType();
        $header   = false;

       //If user doesn't have update right on the item, lock form must not be displayed
        if (!$item->isDynamic() || !$item->can($item->fields['id'], UPDATE)) {
            return false;
        }

        $rand = mt_rand();
        Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
        $massiveactionparams = [
         'container'      => 'mass' . __CLASS__ . $rand,
        ];
        Html::showMassiveActions($massiveactionparams);

        echo "<table class='tab_cadre_fixehov'>";

        echo "<tr>";
        echo "<th>";
        echo Html::getCheckAllAsCheckbox("mass'.__CLASS__.$rand", '__RAND__');
        echo "</th>";
        echo "<th>" . __('Locked items') . "</th></tr>";

       //Use a hook to allow external inventory tools to manage per field lock
        $results =  Plugin::doHookFunction(Hooks::DISPLAY_LOCKED_FIELDS, ['item'   => $item,
                                                                        'header' => $header]);
        $header |= $results['header'];

       //Special locks for computers only
        if ($itemtype == 'Computer') {
            $computer_item = new Computer_Item();
           //Locks for items recorded in glpi_computers_items table
            $types = ['Monitor', 'Peripheral', 'Printer'];
            foreach ($types as $type) {
                $params = ['is_dynamic'    => 1,
                            'is_deleted'    => 1,
                            'computers_id'  => $ID,
                            'itemtype'      => $type];
                $params['FIELDS'] = ['id', 'items_id'];
                $first  = true;
                foreach ($DB->request('glpi_computers_items', $params) as $line) {
                   /** @var CommonDBTM $asset */
                    $asset = new $type();
                    $asset->getFromDB($line['items_id']);
                    if ($first) {
                        echo "<tr>";
                        echo "<th>";
                        Html::showCheckbox(['criterion' => ['tag_for_massive' => 'select_' . $type]]);
                        echo "</th>";
                        echo "<th>" . $type::getTypeName(Session::getPluralNumber()) . "</th></tr>\n";
                        $first = false;
                    }

                    echo "<tr class='tab_bg_1'>";

                    echo "<td class='center' width='10'>";
                    if ($computer_item->can($line['id'], UPDATE) || $computer_item->can($line['id'], PURGE)) {
                        $header = true;
                        echo Html::getMassiveActionCheckBox(Computer_Item::class, $line['id'], ['massive_tags' => 'select_' . $type]);
                    }
                    echo "</td>";

                    echo "<td class='left' width='95%'>" . $asset->getName() . "</td>";
                    echo "</tr>\n";
                }
            }

           //items disks
            $item_disk = new Item_Disk();
            $params = [
            'is_dynamic'   => 1,
            'is_deleted'   => 1,
            'items_id'     => $ID,
            'itemtype'     => $itemtype
            ];
            $params['FIELDS'] = ['id', 'name'];
            $first  = true;
            foreach ($DB->request($item_disk->getTable(), $params) as $line) {
                if ($first) {
                    echo "<tr>";
                    echo "<th>";
                    Html::showCheckbox(['criterion' => ['tag_for_massive' => 'select_' . $item_disk::getType()]]);
                    echo "</th>";
                    echo "<th>" . $item_disk->getTypeName(Session::getPluralNumber()) . "</th></tr>\n";
                    $first = false;
                }

                echo "<tr class='tab_bg_1'>";

                echo "<td class='center' width='10'>";
                if ($item_disk->can($line['id'], UPDATE) || $item_disk->can($line['id'], PURGE)) {
                    $header = true;
                    echo Html::getMassiveActionCheckBox(Item_Disk::class, $line['id'], ['massive_tags' => 'select_' . $item_disk::getType()]);
                }
                echo "</td>";

                echo "<td class='left' width='95%'>" . $line['name'] . "</td>";
                echo "</tr>\n";
            }

            $computer_vm = new ComputerVirtualMachine();
            $params = ['is_dynamic'    => 1,
                         'is_deleted'    => 1,
                         'computers_id'  => $ID];
            $params['FIELDS'] = ['id', 'name'];
            $first  = true;
            foreach ($DB->request($computer_vm->getTable(), $params) as $line) {
                if ($first) {
                    echo "<tr>";
                    echo "<th>";
                    Html::showCheckbox(['criterion' => ['tag_for_massive' => 'select_' . $computer_vm::getType()]]);
                    echo "</th>";
                    echo "<th>" . $computer_vm->getTypeName(Session::getPluralNumber()) . "</th></tr>\n";
                    $first = false;
                }

                echo "<tr class='tab_bg_1'>";

                echo "<td class='center' width='10'>";
                if ($computer_vm->can($line['id'], UPDATE) || $computer_vm->can($line['id'], PURGE)) {
                    $header = true;
                    echo Html::getMassiveActionCheckBox(ComputerVirtualMachine::class, $line['id'], ['massive_tags' => 'select_' . $computer_vm::getType()]);
                }
                echo "</td>";

                echo "<td class='left' width='95%'>" . $line['name'] . "</td>";
                echo "</tr>\n";
            }
        }

        $lockedfield = new Lockedfield();
        if ($lockedfield->isHandled($item)) {
            echo "<tr>";
            echo "<th>";
            Html::showCheckbox(['criterion' => ['tag_for_massive' => 'select_' . $lockedfield::getType()]]);
            echo "</th>";
            echo "<th>" . $lockedfield->getTypeName() . "</th></tr>";

           //inventory locked fields
            $locked_iterator = $DB->request([
            'FROM'   => $lockedfield->getTable(),
            'WHERE'  => [
               'itemtype'  => $itemtype,
               'items_id'  => $ID
            ]
            ]);

           //get fields labels
            $so = $item->rawSearchOptions();
            $so_fields = [];
            foreach ($so as $sof) {
                if (isset($sof['table'])) {
                    $so_fields[$sof['field']] = $sof['name'];
                }
            }

            foreach ($locked_iterator as $row) {
                echo "<tr class='tab_bg_1'>";
                echo "<td class='center' width='10'>";
                if ($lockedfield->can($row['id'], UPDATE) || $lockedfield->can($row['id'], PURGE)) {
                    $header = true;
                    echo Html::getMassiveActionCheckBox(Lockedfield::class, $row['id'], ['massive_tags' => 'select_' . $lockedfield::getType()]);
                }
                echo "</td>";
                $field_label = $row['field'];
                if (isset($so_fields[$row['field']])) {
                    $field_label = $so_fields[$row['field']];
                } else if (isForeignKeyField($row['field'])) {
                   //on fkey, we can try to retrieve the object
                    $object = getItemtypeForForeignKeyField($row['field']);
                    if ($object != 'UNKNOWN') {
                        $field_label = $object::getTypeName(1);
                    }
                }
                echo "<td class='left' width='95%'>" . $field_label . "</td>";
                echo "</tr>\n";
            }
        }

       //Software versions
        $item_sv = new Item_SoftwareVersion();
        $item_sv_table = Item_SoftwareVersion::getTable();

        $iterator = $DB->request([
         'SELECT'    => [
            'isv.id AS id',
            'sv.name AS version',
            's.name AS software'
         ],
         'FROM'      => "{$item_sv_table} AS isv",
         'LEFT JOIN' => [
            'glpi_softwareversions AS sv' => [
               'FKEY' => [
                  'isv' => 'softwareversions_id',
                  'sv'  => 'id'
               ]
            ],
            'glpi_softwares AS s'         => [
               'FKEY' => [
                  'sv'  => 'softwares_id',
                  's'   => 'id'
               ]
            ]
         ],
         'WHERE'     => [
            'isv.is_deleted'  => 1,
            'isv.is_dynamic'  => 1,
            'isv.items_id'    => $ID,
            'isv.itemtype'    => $itemtype,
         ]
        ]);
        echo "<tr>";
        echo "<th>";
        Html::showCheckbox(['criterion' => ['tag_for_massive' => 'select_' . Software::getType()]]);
        echo "</th>";
        echo "<th>" . Software::getTypeName(Session::getPluralNumber()) . "</th></tr>\n";
        foreach ($iterator as $data) {
            echo "<tr class='tab_bg_1'>";

            echo "<td class='center' width='10'>";
            if ($item_sv->can($data['id'], UPDATE) || $item_sv->can($data['id'], PURGE)) {
                $header = true;
                echo Html::getMassiveActionCheckBox(Item_SoftwareVersion::class, $data['id'], ['massive_tags' => 'select_' . Software::getType()]);
            }
            echo "</td>";

            echo "<td class='left' width='95%'>" . $data['software'] . " " . $data['version'] . "</td>";
            echo "</tr>\n";
        }

       //Software licenses
        $item_sl = new Item_SoftwareLicense();
        $item_sl_table = Item_SoftwareLicense::getTable();

        $iterator = $DB->request([
         'SELECT'    => [
            'isl.id AS id',
            'sl.name AS version',
            's.name AS software'
         ],
         'FROM'      => "{$item_sl_table} AS isl",
         'LEFT JOIN' => [
            'glpi_softwarelicenses AS sl' => [
               'FKEY' => [
                  'isl' => 'softwarelicenses_id',
                  'sl'  => 'id'
               ]
            ],
            'glpi_softwares AS s'         => [
               'FKEY' => [
                  'sl'  => 'softwares_id',
                  's'   => 'id'
               ]
            ]
         ],
         'WHERE'     => [
            'isl.is_deleted'  => 1,
            'isl.is_dynamic'  => 1,
            'isl.items_id'    => $ID,
            'isl.itemtype'    => $itemtype,
         ]
        ]);

        echo "<tr>";
        echo "<th>";
        Html::showCheckbox(['criterion' => ['tag_for_massive' => 'select_' . SoftwareLicense::getType()]]);
        echo "</th>";
        echo "<th>" . SoftwareLicense::getTypeName(Session::getPluralNumber()) . "</th></tr>\n";
        foreach ($iterator as $data) {
            echo "<tr class='tab_bg_1'>";

            echo "<td class='center' width='10'>";
            if ($item_sl->can($data['id'], UPDATE) || $item_sl->can($data['id'], PURGE)) {
                $header = true;
                echo Html::getMassiveActionCheckBox(Item_SoftwareLicense::class, $data['id'], ['massive_tags' => 'select_' . SoftwareLicense::getType()]);
            }
            echo "</td>";

            echo "<td class='left' width='95%'>" . $data['software'] . " " . $data['version'] . "</td>";
            echo "</tr>\n";
        }

        $first  = true;
        $networkport = new NetworkPort();
        $params = ['is_dynamic' => 1,
                      'is_deleted' => 1,
                      'items_id'   => $ID,
                      'itemtype'   => $itemtype];
        $params['FIELDS'] = ['id'];
        foreach ($DB->request($networkport->getTable(), $params) as $line) {
            $networkport->getFromDB($line['id']);
            if ($first) {
                echo "<tr>";
                echo "<th>";
                Html::showCheckbox(['criterion' => ['tag_for_massive' => 'select_' . $networkport::getType()]]);
                echo "</th>";
                echo "<th>" . $networkport->getTypeName(Session::getPluralNumber()) . "</th></tr>\n";
                $first = false;
            }

            echo "<tr class='tab_bg_1'>";

            echo "<td class='center' width='10'>";
            if ($networkport->can($line['id'], UPDATE) || $networkport->can($line['id'], PURGE)) {
                $header = true;
                echo Html::getMassiveActionCheckBox(NetworkPort::class, $line['id'], ['massive_tags' => 'select_' . $networkport::getType()]);
            }
            echo "</td>";

            echo "<td class='left' width='95%'>" . $networkport->getName() . "</td>";
            echo "</tr>\n";
        }

        $first = true;
        $networkname = new NetworkName();
        $params = [
         'glpi_networknames.is_dynamic' => 1,
         'glpi_networknames.is_deleted' => 1,
         'glpi_networknames.itemtype'   => 'NetworkPort',
         'glpi_networknames.items_id'   => new QueryExpression($DB->quoteName('glpi_networkports.id')),
         'glpi_networkports.items_id'   => $ID,
         'glpi_networkports.itemtype'   => $itemtype
        ];
        $params['FIELDS'] = ['glpi_networknames' => 'id'];
        foreach ($DB->request(['glpi_networknames', 'glpi_networkports'], $params) as $line) {
            $networkname->getFromDB($line['id']);
            if ($first) {
                echo "<tr>";
                echo "<th>";
                Html::showCheckbox(['criterion' => ['tag_for_massive' => 'select_' . $networkname::getType()]]);
                echo "</th>";
                echo "<th>" . NetworkName::getTypeName(Session::getPluralNumber()) . "</th></tr>\n";
                $first = false;
            }

            echo "<tr class='tab_bg_1'>";

            echo "<td class='center' width='10'>";
            if ($networkname->can($line['id'], UPDATE) || $networkname->can($line['id'], PURGE)) {
                $header = true;
                echo Html::getMassiveActionCheckBox(NetworkName::class, $line['id'], ['massive_tags' => 'select_' . $networkname::getType()]);
            }
            echo "</td>";

            echo "<td class='left' width='95%'>" . $networkname->getName() . "</td>";
            echo "</tr>\n";
        }

        $first  = true;
        $ipaddress = new IPAddress();
        $params = [
         'glpi_ipaddresses.is_dynamic' => 1,
         'glpi_ipaddresses.is_deleted' => 1,
         'glpi_ipaddresses.itemtype'   => 'NetworkName',
         'glpi_ipaddresses.items_id'   => new QueryExpression($DB->quoteName('glpi_networknames.id')),
         'glpi_networknames.itemtype'  => 'NetworkPort',
         'glpi_networknames.items_id'  => new QueryExpression($DB->quoteName('glpi_networkports.id')),
         'glpi_networkports.items_id'  => $ID,
         'glpi_networkports.itemtype'  => $itemtype
        ];
        $params['FIELDS'] = ['glpi_ipaddresses' => 'id'];
        foreach (
            $DB->request(['glpi_ipaddresses',
                                  'glpi_networknames',
                                  'glpi_networkports'], $params) as $line
        ) {
            $ipaddress->getFromDB($line['id']);
            if ($first) {
                echo "<tr>";
                echo "<th>";
                Html::showCheckbox(['criterion' => ['tag_for_massive' => 'select_' . IPAddress::getType()]]);
                echo "</th>";
                echo "<th>" . IPAddress::getTypeName(Session::getPluralNumber()) . "</th></tr>\n";
                $first = false;
            }

            echo "<tr class='tab_bg_1'>";

            echo "<td class='center' width='10'>";
            if ($ipaddress->can($line['id'], UPDATE) || $ipaddress->can($line['id'], PURGE)) {
                $header = true;
                echo Html::getMassiveActionCheckBox(IPAddress::class, $line['id'], ['massive_tags' => 'select_' . $ipaddress::getType()]);
            }
            echo "</td>";

            echo "<td class='left' width='95%'>" . $ipaddress->getName() . "</td>";
            echo "</tr>\n";
        }

        $types = Item_Devices::getDeviceTypes();
        $nb    = 0;
        foreach ($types as $type) {
            $nb += countElementsInTable(
                getTableForItemType($type),
                ['items_id'   => $ID,
                                      'itemtype'   => $itemtype,
                                      'is_dynamic' => 1,
                'is_deleted' => 1 ]
            );
        }
        if ($nb) {
            echo "<tr>";
            echo "<th>";
            Html::showCheckbox(['criterion' => ['tag_for_massive' => 'select_components']]);
            echo "</th>";
            echo "<th>" . _n('Component', 'Components', Session::getPluralNumber()) . "</th></tr>\n";
            foreach ($types as $type) {
                $type_item = new $type();

                $associated_type  = str_replace('Item_', '', $type);
                $associated_table = getTableForItemType($associated_type);
                $fk               = getForeignKeyFieldForTable($associated_table);

                $iterator = $DB->request([
                'SELECT'    => [
                  'i.id',
                  't.designation AS name'
                ],
                'FROM'      => getTableForItemType($type) . ' AS i',
                'LEFT JOIN' => [
                  "$associated_table AS t"   => [
                     'ON' => [
                        't'   => 'id',
                        'i'   => $fk
                     ]
                  ]
                ],
                'WHERE'     => [
                  'itemtype'     => $itemtype,
                  'items_id'     => $ID,
                  'is_dynamic'   => 1,
                  'is_deleted'   => 1
                ]
                ]);

                foreach ($iterator as $data) {
                     echo "<tr class='tab_bg_1'>";

                     echo "<td class='center' width='10'>";
                    if ($type_item->can($data['id'], UPDATE) || $type_item->can($data['id'], PURGE)) {
                        $header = true;
                        echo Html::getMassiveActionCheckBox($type, $data['id'], ['massive_tags' => 'select_components']);
                    }
                     echo "</td>";

                     echo "<td class='left' width='95%'>";
                     printf(__('%1$s: %2$s'), $associated_type::getTypeName(), $data['name']);
                     echo "</td></tr>\n";
                }
            }
        }

        echo "</table>";
        $massiveactionparams['ontop'] = false;
        Html::showMassiveActions($massiveactionparams);
        Html::closeForm();

        echo "</div>\n";
    }


   /**
    * @see CommonGLPI::getTabNameForItem()
    *
    * @param $item               CommonGLPI object
    * @param $withtemplate       (default 0)
   **/
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if ($item->isDynamic() && $item->can($item->fields['id'], UPDATE)) {
            return Lock::getTypeName(Session::getPluralNumber());
        }
        return '';
    }


   /**
    * @param $item            CommonGLPI object
    * @param $tabnum          (default 1)
    * @param $withtemplate    (default 0)
   **/
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        if ($item->isDynamic() && $item->can($item->fields['id'], UPDATE)) {
            self::showForItem($item);
        }
        return true;
    }


   /**
    * Get infos to build an SQL query to get locks fields in a table
    *
    * @param string $itemtype      itemtype of the item to look for locked fields
    * @param string $baseitemtype  itemtype of the based item
    *
    * @return array  which contains necessary information to build the SQL query
   **/
    public static function getLocksQueryInfosByItemType($itemtype, $baseitemtype)
    {
        global $DB;

        $condition = [];
        $table     = false;
        $field     = '';
        $type      = $itemtype;

        switch ($itemtype) {
            case 'Peripheral':
            case 'Monitor':
            case 'Printer':
            case 'Phone':
                $condition = ['itemtype'   => $itemtype,
                               'is_dynamic' => 1,
                               'is_deleted' => 1];
                $table     = 'glpi_computers_items';
                $field     = 'computers_id';
                $type      = 'Computer_Item';
                break;

            case 'NetworkPort':
                $condition = ['itemtype'   => $baseitemtype,
                               'is_dynamic' => 1,
                               'is_deleted' => 1];
                $table     = 'glpi_networkports';
                $field     = 'items_id';
                break;

            case 'NetworkName':
                $condition = [
                'glpi_networknames.is_dynamic' => 1,
                'glpi_networknames.is_deleted' => 1,
                'glpi_networknames.itemtype'   => 'NetworkPort',
                'glpi_networknames.items_id'   => new QueryExpression($DB->quoteName('glpi_networkports.id')),
                'glpi_networkports.itemtype'   => $baseitemtype
                ];
                $condition['FIELDS']
                       = ['glpi_networknames' => 'id'];
                $table     = ['glpi_networknames', 'glpi_networkports'];
                $field     = 'glpi_networkports.items_id';
                break;

            case 'IPAddress':
                $condition = [
                'glpi_ipaddresses.is_dynamic'   => 1,
                'glpi_ipaddresses.is_deleted'   => 1,
                'glpi_ipaddresses.itemtype'     => 'NetworkName',
                'glpi_ipaddresses.items_id'     => 'glpi_networknames.id',
                'glpi_networknames.itemtype'    => 'NetworkPort',
                'glpi_networknames.items_id'    => 'glpi_networkports.id',
                'glpi_networkports.itemtype'    => $baseitemtype];
                $condition['FIELDS']
                       = ['glpi_ipaddresses' => 'id'];
                $table     = ['glpi_ipaddresses', 'glpi_networknames', 'glpi_networkports'];
                $field     = 'glpi_networkports.items_id';
                break;

            case 'Item_Disk':
                $condition = [
                'is_dynamic' => 1,
                'is_deleted' => 1,
                'itemtype'   => $itemtype
                ];
                $table     = Item_Disk::getTable();
                $field     = 'items_id';
                break;

            case 'ComputerVirtualMachine':
                $condition = [
                'is_dynamic' => 1,
                'is_deleted' => 1,
                'itemtype'   => $itemtype];
                $table     = 'glpi_computervirtualmachines';
                $field     = 'computers_id';
                break;

            case 'SoftwareVersion':
                $condition = [
                'is_dynamic' => 1,
                'is_deleted' => 1,
                'itemtype'   => $itemtype];
                $table     = 'glpi_items_softwareversions';
                $field     = 'items_id';
                $type      = 'Item_SoftwareVersion';
                break;

            default:
               // Devices
                if (preg_match('/^Item\_Device/', $itemtype)) {
                    $condition = ['itemtype'   => $baseitemtype,
                                  'is_dynamic' => 1,
                                  'is_deleted' => 1];
                    $table     = getTableForItemType($itemtype);
                    $field     = 'items_id';
                }
        }

        return ['condition' => $condition,
                   'table'     => $table,
                   'field'     => $field,
                   'type'      => $type];
    }


   /**
    * @since 0.85
    *
    * @see CommonDBTM::getMassiveActionsForItemtype()
    **/
    public static function getMassiveActionsForItemtype(
        array &$actions,
        $itemtype,
        $is_deleted = 0,
        CommonDBTM $checkitem = null
    ) {

        $action_name = __CLASS__ . MassiveAction::CLASS_ACTION_SEPARATOR . 'unlock';

        if (
            Session::haveRight('computer', UPDATE)
            && ($itemtype == 'Computer')
        ) {
            $actions[$action_name] = __('Unlock components');
        }
    }


   /**
    * @since 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    **/
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case 'unlock':
                $types = ['Monitor'                => _n('Monitor', 'Monitors', Session::getPluralNumber()),
                           'Peripheral'             => Peripheral::getTypeName(Session::getPluralNumber()),
                           'Printer'                => Printer::getTypeName(Session::getPluralNumber()),
                           'SoftwareVersion'        => SoftwareVersion::getTypeName(Session::getPluralNumber()),
                           'NetworkPort'            => NetworkPort::getTypeName(Session::getPluralNumber()),
                           'NetworkName'            => NetworkName::getTypeName(Session::getPluralNumber()),
                           'IPAddress'              => IPAddress::getTypeName(Session::getPluralNumber()),
                           'Item_Disk'              => Item_Disk::getTypeName(Session::getPluralNumber()),
                           'Device'                 => _n('Component', 'Components', Session::getPluralNumber()),
                           'ComputerVirtualMachine' => ComputerVirtualMachine::getTypeName(Session::getPluralNumber())];

                echo __('Select the type of the item that must be unlock');
                echo "<br><br>\n";

                Dropdown::showFromArray(
                    'attached_item',
                    $types,
                    ['multiple' => true,
                                          'size'     => 5,
                    'values'   => array_keys($types)]
                );

                echo "<br><br>" . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
        }
        return false;
    }


   /**
    * @since 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
    public static function processMassiveActionsForOneItemtype(
        MassiveAction $ma,
        CommonDBTM $baseitem,
        array $ids
    ) {
        global $DB;

        switch ($ma->getAction()) {
            case 'unlock':
                $input = $ma->getInput();
                if (isset($input['attached_item'])) {
                    $attached_items = $input['attached_item'];
                    if (($device_key = array_search('Device', $attached_items)) !== false) {
                        unset($attached_items[$device_key]);
                        $attached_items = array_merge($attached_items, Item_Devices::getDeviceTypes());
                    }
                    $links = [];
                    foreach ($attached_items as $attached_item) {
                        $infos = self::getLocksQueryInfosByItemType($attached_item, $baseitem->getType());
                        if ($item = getItemForItemtype($infos['type'])) {
                             $infos['item'] = $item;
                             $links[$attached_item] = $infos;
                        }
                    }
                    foreach ($ids as $id) {
                        $action_valid = false;
                        foreach ($links as $infos) {
                            $infos['condition'][$infos['field']] = $id;
                            $locked_items = $DB->request($infos['table'], $infos['condition']);

                            if ($locked_items->count() === 0) {
                                $action_valid = true;
                                continue;
                            }
                            foreach ($locked_items as $data) {
                             // Restore without history
                                $action_valid = $infos['item']->restore(['id' => $data['id']]);
                            }
                        }

                        $baseItemType = $baseitem->getType();
                        if ($action_valid) {
                            $ma->itemDone($baseItemType, $id, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($baseItemType, $id, MassiveAction::ACTION_KO);

                            $erroredItem = new $baseItemType();
                            $erroredItem->getFromDB($id);
                            $ma->addMessage($erroredItem->getErrorMessage(ERROR_ON_ACTION));
                        }
                    }
                }
                return;
        }
    }
}
