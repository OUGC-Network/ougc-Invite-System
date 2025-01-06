<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/plugins/ougc_invite_system/admin/logs.php)
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

use function ougc\InviteSystem\Core\getSetting;
use function ougc\InviteSystem\Core\urlHandlerBuild;
use function ougc\InviteSystem\Core\expireCodes;
use function ougc\InviteSystem\Core\urlHandlerGet;
use function ougc\InviteSystem\Core\logDelete;
use function ougc\InviteSystem\Core\urlHandlerSet;

defined('IN_MYBB') || die('This file cannot be accessed directly.');

urlHandlerSet('index.php?module=tools-ougc_invite_system');

global $mybb, $db, $lang, $plugins;
global $page;

$page->add_breadcrumb_item($lang->ougc_invite_system_logs_menu, urlHandlerGet());

$plugins->run_hooks('admin_tools_ougc_invite_system_logs_begin');

if ($mybb->get_input('action') == 'delete') {
    if (!verify_post_check($mybb->get_input('my_post_key'))) {
        flash_message($lang->invalid_post_verify_key2, 'error');

        admin_redirect(urlHandlerGet());
    }

    $lid = $mybb->get_input('lid', MyBB::INPUT_INT);

    logDelete(["lid='{$lid}'"]);

    flash_message($lang->ougc_invite_system_logs_success_delete, 'success');

    admin_redirect(urlHandlerGet());
} else {
    $plugins->run_hooks('admin_ougc_invite_system_logs_start');

    $page->output_header($lang->ougc_invite_system_logs_menu);

    $where_sql = $sortBySelectOptions = $orderSelectOptions = [];

    $filterData = $mybb->get_input('filter', MyBB::INPUT_ARRAY);

    if (!empty($filterData['username'])) {
        $user = get_user_by_username($filterData['username']);

        $filterData['uid'] = (int)$user['uid'];
    }

    if (!empty($filterData['uid'])) {
        $filterData['uid'] = (int)$filterData['uid'];

        $where_sql[] = "c.uid='{$filterData['uid']}'";
    }

    if (!empty($filterData['creator_username'])) {
        $creator_user = get_user_by_username($filterData['creator_username']);

        $filterData['creator_uid'] = (int)$creator_user['uid'];
    }

    if (!empty($filterData['creator_uid'])) {
        $filterData['creator_uid'] = (int)$filterData['creator_uid'];

        $where_sql[] = "c.muid='{$filterData['creator_uid']}'";
    }

    $sortby = 'l.dateline';

    $sortBySelectOptions['dateline'] = ' selected="selected"';

    if (isset($filterData['sortby'])) {
        switch ($filterData['sortby']) {
            case 'username':
                $sortby = 'r.username';
                $sortBySelectOptions['username'] = ' selected="selected"';
                break;
            case 'createdby':
                $sortby = 'm.username';
                $sortBySelectOptions['createdby'] = ' selected="selected"';
                break;
        }
    }

    $order = 'asc';

    if (!empty($filterData['order'])) {
        $order = $filterData['order'];
    }

    if ($order !== 'asc') {
        $order = 'desc';

        $orderSelectOptions['desc'] = ' selected="selected"';
    } else {
        $orderSelectOptions['asc'] = ' selected="selected"';
    }

    expireCodes();

    $where_sql = implode(' AND ', $where_sql);

    $query = $db->simple_select(
        "ougc_invite_system_logs l LEFT JOIN {$db->table_prefix}ougc_invite_system_codes c ON (c.cid=l.cid)",
        'COUNT(l.lid) as count',
        $where_sql
    );

    $total_warnings = (int)$db->fetch_field($query, 'count');

    $sub_tabs['invite_logs'] = [
        'title' => $lang->sprintf($lang->ougc_invite_system_logs_nav, my_number_format($total_warnings)),
        'link' => urlHandlerGet(),
        'description' => $lang->ougc_invite_system_logs_menu_desc
    ];

    $page->output_nav_tabs($sub_tabs, 'invite_logs');

    $view_page = $mybb->get_input('page', MyBB::INPUT_INT);

    if ($view_page < 1) {
        $view_page = 1;
    }

    $perPage = getSetting('perpage');

    if ($perPage < 1) {
        $perPage = 10;
    }

    if (!empty($filterData['per_page'])) {
        $perPage = (int)$filterData['per_page'];
    }

    $start = ($view_page - 1) * $perPage;

    $pages = ceil($total_warnings / $perPage);

    if ($view_page > $pages) {
        $start = 0;

        $view_page = 1;
    }

    $build_url = [];

    if (is_array($filterData) && count($filterData)) {
        foreach ($filterData as $field => $value) {
            $build_url["filter[{$field}]"] = urlencode($value);
        }
    }

    $url = urlHandlerBuild($build_url);

    $query = $db->simple_select(
        "ougc_invite_system_logs l LEFT JOIN {$db->table_prefix}ougc_invite_system_codes c ON (c.cid=l.cid) LEFT JOIN {$db->table_prefix}users r ON (r.uid=c.uid) LEFT JOIN {$db->table_prefix}users u ON (u.uid=l.uid) LEFT JOIN {$db->table_prefix}users m ON (m.uid=c.muid)",
        'l.lid, l.dateline, c.code, c.usergroup, c.additionalgroups, r.uid as ref_uid, r.username as ref_username, r.usergroup as ref_usergroup, r.displaygroup as ref_displaygroup, u.uid as reg_uid, u.username AS reg_username, u.usergroup AS reg_usergroup, u.displaygroup AS reg_displaygroup, u.usergroup AS reg_usergroup, u.displaygroup AS reg_displaygroup, m.uid as mod_uid, m.username AS mod_username, m.usergroup AS mod_usergroup, m.displaygroup AS mod_displaygroup',
        $where_sql,
        ['order_by' => $sortby, 'order_dir' => $order, 'limit' => $perPage, 'limit_start' => $start]
    );

    $table = new Table();

    $table->construct_header($lang->ougc_invite_system_usercp_form_code, ['width' => '10%']);
    $table->construct_header($lang->ougc_invite_system_logs_username, ['width' => '10%']);
    $table->construct_header($lang->ougc_invite_system_logs_creator, ['width' => '10%']);
    $table->construct_header($lang->ougc_invite_system_logs_referrer, ['width' => '10%']);
    $table->construct_header($lang->ougc_invite_system_logs_usergroup, ['class' => 'align_center', 'width' => '15%']);
    $table->construct_header(
        $lang->ougc_invite_system_logs_additionalgroups,
        ['class' => 'align_center', 'width' => '20%']
    );
    $table->construct_header($lang->ougc_invite_system_logs_dateline, ['class' => 'align_center', 'width' => '15%']);
    $table->construct_header($lang->options, ['class' => 'align_center', 'width' => '10%']);

    $groups_cache = $mybb->cache->read('usergroups');

    while ($logData = $db->fetch_array($query)) {
        $group = $groups_cache[$logData['usergroup']];

        $additional_groups = [];

        foreach (explode(',', $logData['additionalgroups']) as $groupID) {
            $additional_groups[] = htmlspecialchars_uni($groups_cache[$groupID]['title'] ?? '');
        }

        $additional_groups || $additional_groups[] = $lang->never;

        $table->construct_cell(htmlspecialchars_uni($logData['code']));

        $reg_user = $lang->ougc_invite_system_logs_deleted;

        if ($logData['reg_uid']) {
            $reg_user = build_profile_link(
                format_name(
                    htmlspecialchars_uni($logData['reg_username']),
                    $logData['reg_usergroup'],
                    $logData['reg_displaygroup']
                ),
                $logData['reg_uid']
            );
        }

        $table->construct_cell($reg_user);

        $mod_user = $lang->ougc_invite_system_logs_task;

        if ($logData['mod_uid']) {
            $mod_user = build_profile_link(
                format_name(
                    htmlspecialchars_uni($logData['mod_username']),
                    $logData['mod_usergroup'],
                    $logData['mod_displaygroup']
                ),
                $logData['mod_uid']
            );
        }

        $table->construct_cell($mod_user);

        $ref_user = $lang->ougc_invite_system_logs_deleted;

        if ($logData['ref_uid']) {
            $ref_user = build_profile_link(
                format_name(
                    htmlspecialchars_uni($logData['ref_username']),
                    $logData['ref_usergroup'],
                    $logData['ref_displaygroup']
                ),
                $logData['ref_uid']
            );
        }

        $table->construct_cell($ref_user);

        $table->construct_cell(htmlspecialchars_uni($group['title']), ['class' => 'align_center']);

        $table->construct_cell(implode($lang->comma, $additional_groups), ['class' => 'align_center']);

        $table->construct_cell(my_date('normal', $logData['dateline']), ['class' => 'align_center']);

        $popup = new PopupMenu('log_' . $logData['lid'], $lang->options);

        $popup->add_item(
            $lang->delete,
            urlHandlerBuild([
                'action' => 'delete',
                'lid' => $logData['lid'],
                'my_post_key' => $mybb->post_code
            ]),
            "return AdminCP.deleteConfirmation(this, '{$lang->ougc_invite_system_logs_delete_confirm}')"
        );

        $table->construct_cell($popup->fetch(), ['class' => 'align_center']);

        $table->construct_row();
    }

    if ($table->num_rows() == 0) {
        $table->construct_cell($lang->ougc_invite_system_logs_empty, ['class' => 'align_center', 'colspan' => 8]);

        $table->construct_row();
    }

    $table->output($lang->ougc_invite_system_logs_menu);

    if ($total_warnings > $perPage) {
        echo draw_admin_pagination($view_page, $perPage, $total_warnings, $url) . '<br />';
    }

    $sort_by = [
        'dateline' => $lang->ougc_invite_system_logs_filter_dateline,
        'username' => $lang->ougc_invite_system_logs_filter_username,
        'createdby' => $lang->ougc_invite_system_logs_filter_createdby
    ];

    $order_array = [
        'asc' => $lang->ougc_invite_system_logs_filter_asc,
        'desc' => $lang->ougc_invite_system_logs_filter_desc
    ];

    $form = new Form(urlHandlerGet(), 'post');

    $form_container = new FormContainer($lang->ougc_invite_system_logs_filter);

    $form_container->output_row(
        $lang->ougc_invite_system_logs_filter_user,
        '',
        $form->generate_text_box(
            'filter[username]',
            $filterData['username'] ?? '',
            ['id' => 'filter_username']
        ),
        'filter_username'
    );

    $form_container->output_row(
        $lang->ougc_invite_system_logs_filter_creator,
        '',
        $form->generate_text_box(
            'filter[creator_username]',
            $filterData['creator_username'] ?? '',
            ['id' => 'filter_creator_username']
        ),
        'filter_creator_username'
    );

    $form_container->output_row(
        $lang->ougc_invite_system_logs_filter_sort,
        '',
        $form->generate_select_box(
            'filter[sortby]',
            $sort_by,
            $filterData['sortby'] ?? '',
            ['id' => 'filter_sortby']
        ) . " {$lang->in} " . $form->generate_select_box(
            'filter[order]',
            $order_array,
            $order,
            ['id' => 'filter_order']
        ) . " {$lang->order}",
        'filter_order'
    );

    $form_container->output_row(
        $lang->ougc_invite_system_logs_filter_perpage,
        '',
        $form->generate_numeric_field('filter[per_page]', $perPage, ['id' => 'filter_per_page', 'min' => 1]),
        'filter_per_page'
    );

    $form_container->end();

    $buttons[] = $form->generate_submit_button($lang->ougc_invite_system_logs_filter);

    $form->output_submit_wrapper($buttons);

    $form->end();

    $page->output_footer();

    exit;
}
