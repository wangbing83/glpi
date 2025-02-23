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
 * Holiday Class
**/
class Holiday extends CommonDropdown
{
    public static $rightname = 'calendar';

    public $can_be_translated = false;


    public static function getTypeName($nb = 0)
    {
        return _n('Close time', 'Close times', $nb);
    }


    public function getAdditionalFields()
    {

        return [['name'  => 'begin_date',
                         'label' => __('Start'),
                         'type'  => 'date'],
                   ['name'  => 'end_date',
                         'label' => __('End'),
                         'type'  => 'date'],
                   ['name'  => 'is_perpetual',
                         'label' => __('Recurrent'),
                         'type'  => 'bool']];
    }


    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
         'id'                 => '11',
         'table'              => $this->getTable(),
         'field'              => 'begin_date',
         'name'               => __('Start'),
         'datatype'           => 'date'
        ];

        $tab[] = [
         'id'                 => '12',
         'table'              => $this->getTable(),
         'field'              => 'end_date',
         'name'               => __('End'),
         'datatype'           => 'date'
        ];

        $tab[] = [
         'id'                 => '13',
         'table'              => $this->getTable(),
         'field'              => 'is_perpetual',
         'name'               => __('Recurrent'),
         'datatype'           => 'bool'
        ];

        return $tab;
    }


    public function prepareInputForAdd($input)
    {

        $input = parent::prepareInputForAdd($input);

        if (
            empty($input['end_date'])
            || ($input['end_date'] == 'NULL')
            || ($input['end_date'] < $input['begin_date'])
        ) {
            $input['end_date'] = $input['begin_date'];
        }
        return $input;
    }


    public function prepareInputForUpdate($input)
    {

        $input = parent::prepareInputForUpdate($input);

        if (
            isset($input['begin_date']) && (empty($input['end_date'])
            || ($input['end_date'] == 'NULL')
            || ($input['end_date'] < $input['begin_date']))
        ) {
            $input['end_date'] = $input['begin_date'];
        }

        return $input;
    }

    public function post_updateItem($history = 1)
    {

        $this->invalidateCalendarHolidayCache();

        parent::post_updateItem($history);
    }

    public function post_deleteFromDB()
    {

        $this->invalidateCalendarHolidayCache();

        parent::post_deleteFromDB();
    }

    public function cleanDBonPurge()
    {

        $this->deleteChildrenAndRelationsFromDb(
            [
            Calendar_Holiday::class,
            ]
        );
    }

   /**
    * Invalidate holidays cache on linked calendars.
    *
    * @return void
    */
    private function invalidateCalendarHolidayCache(): void
    {
        $calendar_holiday = new Calendar_Holiday();
        $calendar_holiday->invalidateHolidayCache($this->fields['id']);
    }

    public static function getIcon()
    {
        return "far fa-calendar-times";
    }
}
