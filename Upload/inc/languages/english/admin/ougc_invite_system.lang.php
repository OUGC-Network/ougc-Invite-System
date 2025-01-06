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
    'setting_group_ougc_invite_system_desc' => 'Allow registration to be invitation based, so that new registration require an invitation code for registration.',
    'ougc_invite_system_myalerts_desc' => '<br /><code><a href="./index.php?module=config-plugins&amp;action=ougc_invite_system">Install MyAlerts Integration</a></code>',
    'ougc_invite_system_myalerts_confirm' => 'Are you sure you want to install MyAlerts integration?',
    'ougc_invite_system_myalerts_success' => 'MyAlerts integration has successfully been installed.',
    'setting_ougc_invite_system_invitegroups' => 'Allowed Invite Groups',
    'setting_ougc_invite_system_invitegroups_desc' => 'Select which groups users can be invited to. Administrator and moderator groups will be ignored. Fallback will always be <code><a href="./index.php?module=user-groups&action=edit&gid=2">Registered (2)</a></code>.',
    'setting_ougc_invite_system_requirecode' => 'Require Invite Code',
    'setting_ougc_invite_system_requirecode_desc' => 'If you turn this feature invite codes will be required in registrations.',
    'setting_ougc_invite_system_referralsystem' => 'Referral System Integration',
    'setting_ougc_invite_system_referralsystem_desc' => 'If you enable this invite code owners will be assigned as the referral of users.',
    'setting_ougc_invite_system_referral_points' => 'Referral Points Share',
    'setting_ougc_invite_system_referral_points_desc' => 'If referral integration is enabled, what percent of points does referrers receive? Percentage values only, from <code>0</code> to <code>100</code>. <code style="color: darkorange;">The <i>"Group Rate for Additions"</i>  group permission multiplies this.</code>',
    'setting_ougc_invite_system_regattempts' => 'Maximum Registration Attempts',
    'setting_ougc_invite_system_regattempts_desc' => 'Maximum registration attempts allowed for wrong code submits.',
    'setting_ougc_invite_system_regattempts_minutes' => 'Registration Wait Interval',
    'setting_ougc_invite_system_regattempts_minutes_desc' => 'How many minutes users will need to wait after reaching the maximum registration attempts.',
    'setting_ougc_invite_system_perpage' => 'Per Page Pagination',
    'setting_ougc_invite_system_perpage_desc' => 'Maximum items to display per page.',
    'setting_ougc_invite_system_notifications' => 'Notification Methods',
    'setting_ougc_invite_system_notifications_desc' => 'Select the notification method to use when users are added to private threads.',
    'setting_ougc_invite_system_notifications_pm' => 'Private Message',
    'setting_ougc_invite_system_notifications_myalerts' => 'MyAlerts',
    'setting_ougc_invite_system_characters' => 'Random Code Characters',
    'setting_ougc_invite_system_characters_desc' => 'Input which characters to use for generating random codes. Do not change this unless you know what you are doing nor leave empty.',
    'setting_ougc_invite_system_length' => 'Random Code Length',
    'setting_ougc_invite_system_length_desc' => 'Input how many characters to use for generating random codes between <code>1</code> and <code>50</code>.',
    'setting_ougc_invite_system_renewalperiod' => 'Renewal Period',
    'setting_ougc_invite_system_renewalperiod_desc' => 'Input every how many days users should be granted new codes automatically based off their group permission settings.',
    'setting_ougc_invite_system_default_usergroup' => 'Default Group',
    'setting_ougc_invite_system_default_usergroup_desc' => 'Select the default group for new generated codes.',
    'setting_ougc_invite_system_default_additionalgroups' => 'Default Additional Groups',
    'setting_ougc_invite_system_default_additionalgroups_desc' => 'Select the default additional groups for new generated codes.',
    'setting_ougc_invite_system_default_stock' => 'Default Stock',
    'setting_ougc_invite_system_default_stock_desc' => 'Select the default stock for new generated codes.',
    'setting_ougc_invite_system_actionName' => 'Action Name',
    'setting_ougc_invite_system_actionName_desc' => 'Input the name of the action to be used for the control panel page.',

    'ougc_invite_system_task_ran' => 'This ougc Invite System task ran successfully.',

    'ougc_invite_system_logs_menu' => 'Invite System Logs',
    'ougc_invite_system_logs_nav' => 'Invite System Logs ({1})',
    'ougc_invite_system_logs_menu_desc' => 'You can view all existing invite logs below.',
    'ougc_invite_system_logs_code' => 'Code',
    'ougc_invite_system_logs_username' => 'User',
    'ougc_invite_system_logs_deleted' => '<i>Deleted</i>',
    'ougc_invite_system_logs_task' => '<i>Task or Deleted</i>',
    'ougc_invite_system_logs_creator' => 'Creator',
    'ougc_invite_system_logs_referrer' => 'Referrer',
    'ougc_invite_system_logs_usergroup' => 'Usergroup',
    'ougc_invite_system_logs_additionalgroups' => 'Additional Groups',
    'ougc_invite_system_logs_dateline' => 'Date',
    'ougc_invite_system_logs_empty' => 'There are no logs to display.',
    'ougc_invite_system_logs_delete_confirm' => 'Are you sure you want to permanently delete this log?',
    'ougc_invite_system_logs_filter' => 'Filter Logs',
    'ougc_invite_system_logs_filter_user' => 'Referrer User',
    'ougc_invite_system_logs_filter_creator' => 'Code Creator',
    'ougc_invite_system_logs_filter_sort' => 'Sort By',
    'ougc_invite_system_logs_filter_perpage' => 'Items Per Page',
    'ougc_invite_system_logs_filter_asc' => 'ASC',
    'ougc_invite_system_logs_filter_desc' => 'DESC',
    'ougc_invite_system_logs_filter_dateline' => 'Date',
    'ougc_invite_system_logs_filter_username' => 'Referrer',
    'ougc_invite_system_logs_filter_createdby' => 'Creator',
    'ougc_invite_system_logs_success_delete' => 'The selected log entry was successfully deleted.',
    'ougc_invite_system_permissions' => 'Can manage invite system logs?	',
    'ougc_invite_system_pluginlibrary' => 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.',

    'ougc_invite_system_groups_tab' => 'Invite System',
    'ougc_invite_system_groups_users' => 'Users',
    'ougc_invite_system_groups_mods' => 'Moderators',
    'ougc_invite_system_groups_canview' => 'Can view?<br /><small>Allow users access their codes from the UserCP.</small>',
    'ougc_invite_system_groups_canadd' => 'Can add codes?<br /><small>Allow users to add codes.</small>',
    'ougc_invite_system_groups_canstock' => 'Can add custom stock?<br /><small>Allow users to set custom codes stock.</small>',
    'ougc_invite_system_groups_canunlimitedstock' => 'Can add unlimited stock?<br /><small>Allow users to set unlimited codes stock.</small>',
    'ougc_invite_system_groups_canexpire' => 'Can add expire time?<br /><small>Allow users to set expiration time to codes.</small>',
    'ougc_invite_system_groups_canusergroup' => 'Can choose primary group?<br /><small>Allow users to assign destination group for codes.</small>',
    'ougc_invite_system_groups_canadditionalgroups' => 'Can choose additional group?<br /><small>Allow users to assign additional groups for codes.</small>',
    'ougc_invite_system_groups_canemail' => 'Can assign emails to codes?<br /><small>Allow users to assign emails to codes.</small>',
    'ougc_invite_system_groups_cancustom' => 'Can create custom codes?<br /><small>Allow users to create custom codes strings.</small>',
    'ougc_invite_system_groups_canmultiple' => 'Can use bulk feature?<br /><small>Allow users to create multiple codes simultanously.</small>',
    'ougc_invite_system_groups_candelete' => 'Can delete codes?<br /><small>Allow users to delete their codes.</small>',
    'ougc_invite_system_groups_activelimit' => 'Maximum Active Invitations<br /><small class="input">The maximum number of active codes users can have.</small><br />',
    'ougc_invite_system_groups_renewal' => 'Periodic Renewal Count<br /><small class="input">Based on the general settings, how many codes should users in this group be granted by period?</small><br />',
    'ougc_invite_system_groups_canmanage' => 'Can manage codes?<br /><small>Allow moderators to manage codes from the  ModCP.</small>',
    'ougc_invite_system_groups_canmodadd' => 'Can add codes?<br /><small>Allow moderators to add.</small>',
    'ougc_invite_system_groups_canmodstock' => 'Can add custom stock?<br /><small>Allow moderators to set custom codes stock.</small>',
    'ougc_invite_system_groups_canmodunlimitedstock' => 'Can add unlimited stock?<br /><small>Allow moderators to set unlimited codes stock.</small>',
    'ougc_invite_system_groups_canmodexpire' => 'Can add expire time?<br /><small>Allow moderators to set expiration time to codes.</small>',
    'ougc_invite_system_groups_canmodusergroup' => 'Can choose primary group?<br /><small>Allow moderators to assign destination group for codes.</small>',
    'ougc_invite_system_groups_canmodadditionalgroups' => 'Can choose additional group?<br /><small>Allow moderators to assign additional groups for codes.</small>',
    'ougc_invite_system_groups_canmodemail' => 'Can assign emails to codes?<br /><small>Allow moderators to assign emails to codes.</small>',
    'ougc_invite_system_groups_canmodcustom' => 'Can create custom codes?<br /><small>Allow moderators to create custom codes strings.</small>',
    'ougc_invite_system_groups_canmodmultiple' => 'Can use bulk feature?<br /><small>Allow moderators to create multiple codes simultanously.</small>',
    'ougc_invite_system_groups_canmoddelete' => 'Can delete codes?<br /><small>Allow moderators to delete codes.</small>',
    'ougc_invite_system_groups_canbypasslimit' => 'Can bypass active invitations?<br /><small>Allow moderators to bypass users maximum active invitations.</small>',
    'ougc_invite_system_groups_canusers' => 'Can choose multiple users?<br /><small>Allow moderators to add codes for multuple users simultanously.</small>',
    'ougc_invite_system_groups_canpoints' => 'Can choose points?<br /><small>Allow moderators to assign points to codes.</small>',
    'ougc_invite_system_groups_can' => 'Can add codes?<br /><small></small>',
    'ougc_invite_system_groups_can' => 'Can add codes?<br /><small></small>',
    'in' => 'in',
    'order' => 'order',
];