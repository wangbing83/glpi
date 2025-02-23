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

use Glpi\Dashboard\Widget;

/**
 * Store printer metrics
 */
class PrinterLog extends CommonDBChild
{
    public static $itemtype        = 'Printer';
    public static $items_id        = 'printers_id';
    public $dohistory              = false;


   /**
    * Get name of this type by language of the user connected
    *
    * @param integer $nb number of elements
    *
    * @return string name of this type
    */
    public static function getTypeName($nb = 0)
    {
        return __('Page counters');
    }

   /**
    * Get the tab name used for item
    *
    * @param object $item the item object
    * @param integer $withtemplate 1 if is a template form
    * @return string|array name of the tab
    */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        $array_ret = [];

        if ($item->getType() == 'Printer') {
            $cnt = countElementsInTable([static::getTable()], [static::$items_id => $item->getField('id')]);
            $array_ret[] = self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $cnt);
        }
        return $array_ret;
    }


   /**
    * Display the content of the tab
    *
    * @param object $item
    * @param integer $tabnum number of the tab to display
    * @param integer $withtemplate 1 if is a template form
    * @return boolean
    */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == Printer::getType() && $item->getID() > 0) {
            $printerlog = new self();
            $printerlog->showMetrics($item);
            return true;
        }
        return false;
    }

   /**
    * Get metrics
    *
    * @param Printer $printer      Printer instance
    * @param array   $user_filters User filters
    *
    * @return array
    */
    public function getMetrics(Printer $printer, $user_filters = []): array
    {
        global $DB;

        $bdate = new DateTime();
        $bdate->sub(new DateInterval('P1Y'));
        $filters = [
         'date' => ['>', $bdate->format('Y-m-d')]
        ];
        $filters = array_merge($filters, $user_filters);

        $iterator = $DB->request([
         'FROM'   => $this->getTable(),
         'WHERE'  => [
            'printers_id'  => $printer->fields['id']
         ] + $filters
        ]);

        return iterator_to_array($iterator);
    }

   /**
    * Display form for agent
    *
    * @param Printer $printer Printer instance
    */
    public function showMetrics(Printer $printer)
    {
        $raw_metrics = $this->getMetrics($printer);

       //build graph data
        $params = [
         'label'         => $this->getTypeName(),
         'icon'          => Printer::getIcon(),
         'apply_filters' => [],
        ];

        $series = [];
        $labels = [];
        $i = 0;
        foreach ($raw_metrics as $metrics) {
            $date = new DateTime($metrics['date']);
            $labels[] = $date->format(__('Y-m-d'));
            unset($metrics['id'], $metrics['date'], $metrics['printers_id']);

            foreach ($metrics as $key => $value) {
                if ($value > 0) {
                    $series[$key]['name'] = $this->getLabelFor($key);
                    $series[$key]['data'][] = $value;
                }
            }
            ++$i;
        }

        $bar_conf = [
         'data'  => [
            'labels' => $labels,
            'series' => array_values($series),
         ],
         'label' => $params['label'],
         'icon'  => $params['icon'],
         'color' => '#ffffff',
         'distributed' => false
        ];

       //display graph
        echo "<div class='dashboard printer_barchart'>";
        echo Widget::multipleBars($bar_conf);
        echo "</div>";
    }

    private function getLabelFor($key)
    {
        switch ($key) {
            case 'total_pages':
                return __('Total pages');
            case 'bw_pages':
                return __('Black & White pages');
            case 'color_pages':
                return __('Color pages');
            case 'scanned':
                return __('Scans');
            case 'rv_pages':
                return __('Recto/Verso pages');
            case 'prints':
                return __('Prints');
            case 'bw_prints':
                return __('Black & White prints');
            case 'color_prints':
                return __('Color prints');
            case 'copies':
                return __('Copies');
            case 'bw_copies':
                return __('Black & White copies');
            case 'color_copies':
                return __('Color copies');
            case 'faxed':
                return __('Fax');
        }
    }
}
