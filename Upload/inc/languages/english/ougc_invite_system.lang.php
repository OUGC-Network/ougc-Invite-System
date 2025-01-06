<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/languages/english/admin/ougc_invite_system.lang.php)
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

$l = [
    'setting_group_ougc_invite_system' => 'ougc Invite System',

    'ougc_invite_system_modcp_nav' => 'Invite System',
    'ougc_invite_system_usercp_nav' => 'Invite System',
    'ougc_invite_system_usercp_content_desc' => 'Below is a list of invitation codes.',
    'ougc_invite_system_usercp_content_empty' => 'There are no codes to display.',
    'ougc_invite_system_usercp_stock_unlimited' => 'Unlimited',
    'ougc_invite_system_usercp_noemail' => '-',
    'ougc_invite_system_usercp_buttons_delete' => 'Delete',
    'ougc_invite_system_usercp_buttons_deactivate' => 'Deactivate',
    'ougc_invite_system_usercp_form_title' => 'Add New Code',
    'ougc_invite_system_usercp_form_desc' => 'Use the form below to add new codes.',
    'ougc_invite_system_usercp_form_code' => 'Code',
    'ougc_invite_system_usercp_form_code_desc' => 'Leave empty to generate random codes.',
    'ougc_invite_system_usercp_form_email' => 'Email',
    'ougc_invite_system_usercp_form_active' => 'Active',
    'ougc_invite_system_usercp_form_inactive' => 'Inactive',
    'ougc_invite_system_usercp_form_usergroup' => 'User Group',
    'ougc_invite_system_usercp_form_additionalgroups' => 'Additional Groups',
    'ougc_invite_system_usercp_form_uses' => 'Uses',
    'ougc_invite_system_usercp_form_stock' => 'Stock',
    'ougc_invite_system_usercp_form_points' => 'Points',
    'ougc_invite_system_usercp_form_points_desc' => 'Points to be awarded to the user that redeems this code.',
    'ougc_invite_system_usercp_form_multiple' => 'Codes amount',
    'ougc_invite_system_usercp_form_multiple_desc' => 'Generate multiple random codes at once.',
    'ougc_invite_system_usercp_form_unlimited' => 'Unlimited',
    'ougc_invite_system_usercp_form_users' => 'Users',
    'ougc_invite_system_usercp_form_stock_unlimited' => 'Allow unlimited stock.',
    'ougc_invite_system_usercp_form_expire' => 'Expire Date',
    'ougc_invite_system_usercp_form_dateline' => 'Creation Date',
    'ougc_invite_system_usercp_form_selectall' => 'Select All',
    'ougc_invite_system_modcp_current' => 'Current user',
    'ougc_invite_system_modcp_current_ban' => 'Ban User',
    'ougc_invite_system_modcp_current_liftban' => 'Lift Ban',
    'ougc_invite_system_modcp_filter' => 'Filter',
    'ougc_invite_system_modcp_filter_username' => 'Username',
    'ougc_invite_system_modcp_filter_inactive' => 'Inactive',
    'ougc_invite_system_modcp_filter_inactive_desc' => 'Show only inactive codes.',
    'ougc_invite_system_form_generate' => 'Generate Codes',
    'ougc_invite_system_errors_banned' => 'Your access to the invitation system has been disabled by a moderator.',
    'ougc_invite_system_errors_invalidcode' => 'The selected code is invalid.',
    'ougc_invite_system_errors_repeated_code' => 'One of the selected or generated codes already exist.',
    'ougc_invite_system_errors_invalidcodelength' => 'The selected code exceeds the limit of 50 maximum characters.',
    'ougc_invite_system_errors_invalidemail' => 'The selected email is invalid.',
    'ougc_invite_system_errors_invalidstock' => 'The selected stock amount is invalid.',
    'ougc_invite_system_errors_invalidpoints' => 'The selected points amount is invalid.',
    'ougc_invite_system_errors_invalidenddate' => 'The selected expiration date is invalid.',
    'ougc_invite_system_errors_invalidusers' => 'The selected users seem to be invalid.',
    'ougc_invite_system_errors_maxreached' => 'You have reached the maximum allowed active codes for you.',
    'ougc_invite_system_errors_maxreached_modcp' => 'The user has reached his maximum allowed active codes.',
    'ougc_invite_system_redirect_generated' => 'The invitation codes were successfully generated.<br />You will now be redirected back.',
    'ougc_invite_system_redirect_generated_limitexceeded' => 'The invitation codes were successfully generated but you did reach the maximum allowed active codes for you in the process.<br />You will now be redirected back.',
    'ougc_invite_system_redirect_generated_limitexceeded_modcp' => 'The invitation codes were successfully generated but one or more users did reach the maximum allowed active codes for their account in the process.<br />You will now be redirected back.',
    'ougc_invite_system_redirect_deactivated' => 'The selected invite codes were successfully deactivated.<br />You will now be redirected back.',
    'ougc_invite_system_redirect_deleted' => 'The selected invite codes were successfully deleted.<br />You will now be redirected back.',
    'ougc_invite_system_redirect_banned' => 'The user was successfully banned from this feature.<br />You will now be redirected back.',
    'ougc_invite_system_redirect_banlifted' => 'The user ban was successfully lifted.<br />You will now be redirected back.',
    'ougc_invite_system_register_title' => 'Invite Code',
    'ougc_invite_system_register_desc' => 'If you were invited to these forums by another member you can enter the invite code below. If not, simply leave this field blank.',
    'ougc_invite_system_register_error_required' => 'An invite code is required to be able to register to this board.',
    'ougc_invite_system_register_error_size' => 'The invite code you entered is invalid.',
    'ougc_invite_system_register_error_invalid' => 'The invite code you entered does not exists.',
    'ougc_invite_system_register_error_invalidemail' => 'The invite code you entered does not match the selected email.',
    'ougc_invite_system_register_error_maxregattempts' => 'You have reached the maximum number of registration attemps. You will need to wait {1} minutes.',
    'ougc_invite_system_pm_subject' => 'A user has registered using one of your invite codes.',
    'ougc_invite_system_pm_content' => '{1},

A user has registered using one of your invite codes.

The user name is {2}

To view user profile, you can go to the following URL:
{3}/{4}

Thank you,
{5} Staff',
    'myalerts_setting_ougc_invite_system_register' => 'Receive alert when somebody registers using my invite codes?',
    'myalerts_ougc_invite_system_register' => '{1} has registered using your invite code "<code>{2}</code>".',

    'ougc_invite_system_newpoints_logs_secondary' => 'User: <a href="{1}/{2}">{3}</a>',

    'ougc_invite_system_newpoints_logs_register' => 'Invite Registration',
    'ougc_invite_system_newpoints_logs_referral' => 'Invite Referral',

    'ougc_invite_system_task_ran' => 'This ougc Invite System task ran successfully.',
];