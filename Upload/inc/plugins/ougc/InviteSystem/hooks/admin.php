<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/plugins/ougc_invite_system/admin_hooks.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2020 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Allow registration to be invitation based, so that new registration require an invitation code for registration.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

declare(strict_types=1);

namespace ougc\InviteSystem\Hooks\Admin;

use FormContainer;
use MyBB;

use function ougc\InviteSystem\Core\languageLoad;
use function ougc\InviteSystem\MyAlerts\getAvailableLocations;
use function ougc\InviteSystem\MyAlerts\installLocation;
use function ougc\InviteSystem\MyAlerts\MyAlertsIsIntegrable;

use const ougc\InviteSystem\Admin\FIELDS_DATA;
use const ougc\InviteSystem\ROOT;

function admin_config_plugins_begin01(): bool
{
    global $mybb, $lang, $page, $db;

    if ($mybb->get_input('action') != 'ougc_invite_system') {
        return false;
    }

    languageLoad();

    if ($mybb->get_input('no') || !MyAlertsIsIntegrable()) {
        admin_redirect('index.php?module=config-plugins');
    }

    if ($mybb->request_method != 'post') {
        $page->output_confirm_action(
            'index.php?module=config-plugins&amp;action=ougc_invite_system',
            $lang->ougc_invite_system_myalerts_confirm
        );
    }

    $availableLocations = getAvailableLocations();

    foreach ($availableLocations as $availableLocation) {
        installLocation($availableLocation);
    }

    flash_message($lang->ougc_invite_system_myalerts_success, 'success');

    admin_redirect('index.php?module=config-plugins');

    return true;
}

function admin_config_plugins_deactivate(): bool
{
    global $mybb, $page;

    if (
        $mybb->get_input('action') != 'deactivate' ||
        $mybb->get_input('plugin') != 'ougc_invite_system' ||
        !$mybb->get_input('uninstall', MyBB::INPUT_INT)
    ) {
        return false;
    }

    if ($mybb->request_method != 'post') {
        $page->output_confirm_action(
            'index.php?module=config-plugins&amp;action=deactivate&amp;uninstall=1&amp;plugin=ougc_invite_system'
        );
    }

    if ($mybb->get_input('no')) {
        admin_redirect('index.php?module=config-plugins');
    }

    return true;
}

function admin_config_settings_begin(): bool
{
    languageLoad();

    return true;
}

function admin_tools_action_handler(array &$actions): array
{
    $actions['ougc_invite_system'] = [
        'active' => 'ougc_invite_system',
        'file' => 'logs.php'
    ];

    return $actions;
}

function admin_tools_menu_logs(array &$sub_menu): array
{
    global $lang;

    languageLoad();

    $sub_menu[] = [
        'id' => 'ougc_invite_system',
        'title' => $lang->ougc_invite_system_logs_menu,
        'link' => 'index.php?module=tools-ougc_invite_system'
    ];

    return $sub_menu;
}

function admin_load(): bool
{
    global $run_module, $page;

    if ($run_module !== 'tools' || $page->active_action !== 'ougc_invite_system') {
        return false;
    }

    require_once ROOT . '/admin/logs.php';

    return true;
}

function admin_tools_permissions(array &$permissions): array
{
    global $lang;

    languageLoad();

    $permissions['ougc_invite_system'] = $lang->ougc_invite_system_permissions;

    return $permissions;
}

function admin_user_groups_edit_graph_tabs(array &$tabs): array
{
    global $lang;

    languageLoad();

    $tabs['ougc_invite_system'] = $lang->ougc_invite_system_groups_tab;

    return $tabs;
}

function admin_user_groups_edit_graph(): bool
{
    global $lang, $form, $mybb;

    languageLoad();

    echo '<div id="tab_ougc_invite_system">';

    $form_container = new FormContainer($lang->ougc_invite_system_groups_tab);

    $form_container->output_row(
        $lang->ougc_invite_system_groups_users,
        '',
        '<div class="group_settings_bit">' . implode('</div><div class="group_settings_bit">', [
            $form->generate_check_box(
                'ougc_invite_system_canview',
                1,
                $lang->ougc_invite_system_groups_canview,
                ['checked' => $mybb->get_input('ougc_invite_system_canview', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canadd',
                1,
                $lang->ougc_invite_system_groups_canadd,
                ['checked' => $mybb->get_input('ougc_invite_system_canadd', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canstock',
                1,
                $lang->ougc_invite_system_groups_canstock,
                ['checked' => $mybb->get_input('ougc_invite_system_canstock', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canunlimitedstock',
                1,
                $lang->ougc_invite_system_groups_canunlimitedstock,
                ['checked' => $mybb->get_input('ougc_invite_system_canunlimitedstock', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canexpire',
                1,
                $lang->ougc_invite_system_groups_canexpire,
                ['checked' => $mybb->get_input('ougc_invite_system_canexpire', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canusergroup',
                1,
                $lang->ougc_invite_system_groups_canusergroup,
                ['checked' => $mybb->get_input('ougc_invite_system_canusergroup', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canadditionalgroups',
                1,
                $lang->ougc_invite_system_groups_canadditionalgroups,
                ['checked' => $mybb->get_input('ougc_invite_system_canadditionalgroups', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canemail',
                1,
                $lang->ougc_invite_system_groups_canemail,
                ['checked' => $mybb->get_input('ougc_invite_system_canemail', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_cancustom',
                1,
                $lang->ougc_invite_system_groups_cancustom,
                ['checked' => $mybb->get_input('ougc_invite_system_cancustom', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmultiple',
                1,
                $lang->ougc_invite_system_groups_canmultiple,
                ['checked' => $mybb->get_input('ougc_invite_system_canmultiple', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_candelete',
                1,
                $lang->ougc_invite_system_groups_candelete,
                ['checked' => $mybb->get_input('ougc_invite_system_candelete', MyBB::INPUT_INT)]
            ),
            $lang->ougc_invite_system_groups_activelimit . $form->generate_numeric_field(
                'ougc_invite_system_activelimit',
                $mybb->get_input('ougc_invite_system_activelimit', MyBB::INPUT_INT),
                ['id' => 'ougc_invite_system_activelimit', 'class' => 'field50', 'min' => 0]
            ),
            $lang->ougc_invite_system_groups_renewal . $form->generate_numeric_field(
                'ougc_invite_system_renewal',
                $mybb->get_input('ougc_invite_system_renewal', MyBB::INPUT_INT),
                ['id' => 'ougc_invite_system_renewal', 'class' => 'field50', 'min' => 0]
            ),
        ]) . '</div>'
    );

    $form_container->output_row(
        $lang->ougc_invite_system_groups_mods,
        '',
        '<div class="group_settings_bit">' . implode('</div><div class="group_settings_bit">', [
            $form->generate_check_box(
                'ougc_invite_system_canmanage',
                1,
                $lang->ougc_invite_system_groups_canmanage,
                ['checked' => $mybb->get_input('ougc_invite_system_canmanage', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmodadd',
                1,
                $lang->ougc_invite_system_groups_canmodadd,
                ['checked' => $mybb->get_input('ougc_invite_system_canmodadd', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmodstock',
                1,
                $lang->ougc_invite_system_groups_canmodstock,
                ['checked' => $mybb->get_input('ougc_invite_system_canmodstock', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmodunlimitedstock',
                1,
                $lang->ougc_invite_system_groups_canmodunlimitedstock,
                ['checked' => $mybb->get_input('ougc_invite_system_canmodunlimitedstock', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmodexpire',
                1,
                $lang->ougc_invite_system_groups_canmodexpire,
                ['checked' => $mybb->get_input('ougc_invite_system_canmodexpire', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmodusergroup',
                1,
                $lang->ougc_invite_system_groups_canmodusergroup,
                ['checked' => $mybb->get_input('ougc_invite_system_canmodusergroup', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmodadditionalgroups',
                1,
                $lang->ougc_invite_system_groups_canmodadditionalgroups,
                ['checked' => $mybb->get_input('ougc_invite_system_canmodadditionalgroups', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmodemail',
                1,
                $lang->ougc_invite_system_groups_canmodemail,
                ['checked' => $mybb->get_input('ougc_invite_system_canmodemail', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmodcustom',
                1,
                $lang->ougc_invite_system_groups_canmodcustom,
                ['checked' => $mybb->get_input('ougc_invite_system_canmodcustom', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmodmultiple',
                1,
                $lang->ougc_invite_system_groups_canmodmultiple,
                ['checked' => $mybb->get_input('ougc_invite_system_canmodmultiple', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canmoddelete',
                1,
                $lang->ougc_invite_system_groups_canmoddelete,
                ['checked' => $mybb->get_input('ougc_invite_system_canmoddelete', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canusers',
                1,
                $lang->ougc_invite_system_groups_canusers,
                ['checked' => $mybb->get_input('ougc_invite_system_canusers', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canpoints',
                1,
                $lang->ougc_invite_system_groups_canpoints,
                ['checked' => $mybb->get_input('ougc_invite_system_canpoints', MyBB::INPUT_INT)]
            ),
            $form->generate_check_box(
                'ougc_invite_system_canbypasslimit',
                1,
                $lang->ougc_invite_system_groups_canbypasslimit,
                ['checked' => $mybb->get_input('ougc_invite_system_canbypasslimit', MyBB::INPUT_INT)]
            ),
        ]) . '</div>'
    );

    $form_container->end();

    echo '</div>';

    return true;
}

function admin_user_groups_edit_commit(): bool
{
    global $updated_group, $mybb;

    foreach (FIELDS_DATA['usergroups'] as $fieldName => $fieldData) {
        $updated_group[$fieldName] = $mybb->get_input($fieldName, MyBB::INPUT_INT);
    }

    return true;
}