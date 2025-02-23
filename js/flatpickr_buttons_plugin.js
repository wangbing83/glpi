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

window.CustomFlatpickrButtons = (config = {}) => {

   return (fp) => {
      let wrapper;

      if (config.buttons === undefined) {
         config.buttons = [{
            label: fp.config.enableTime ? __('Now') : __("Today"),
            attributes: {
               'class': 'btn btn-outline-secondary'
            },
            onClick: (e, fp) => {
               fp.setDate(new Date());
            }
         }];
      }

      return {
         onReady: () => {
            wrapper = `<div class="flatpickr-custom-buttons pb-1 text-start"><div class="buttons-container">`;

            (config.buttons).forEach((b, index) => {
               const button = document.createElement('button');
               button.type = 'button';
               button.classList.add('ms-2');
               button.textContent = b.label;
               button.setAttribute('btn-id', index);
               if (typeof b.attributes !== 'undefined') {
                  Object.keys(b.attributes).forEach((key) => {
                     if (key === 'class') {
                        button.classList.add(...b.attributes[key].split(' '));
                        return;
                     }

                     button.setAttribute(key, b.attributes[key]);
                  });
               }

               wrapper += button.outerHTML;

               fp.pluginElements.push(button);
            });
            wrapper += '</div></div>';

            fp.calendarContainer.appendChild($.parseHTML(wrapper)[0]);

            $(fp.calendarContainer).on('click', '.flatpickr-custom-buttons button', (e) => {
               e.stopPropagation();
               e.preventDefault();

               const btn = $(e.target);
               const btn_id = btn.attr('btn-id');
               const click_handler = config.buttons[btn_id].onClick;

               if (typeof click_handler !== 'function') {
                  return;
               }

               click_handler(e, fp);
            });
         },

         onDestroy: () => {
            $(wrapper).remove();
         },
      };
   };
};
