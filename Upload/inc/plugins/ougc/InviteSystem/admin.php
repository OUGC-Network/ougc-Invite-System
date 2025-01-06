<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/plugins/ougc_invite_system/admin.php)
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

namespace ougc\InviteSystem\Admin;

use DirectoryIterator;

use function Newpoints\Core\log_remove;
use function ougc\InviteSystem\Core\languageLoad;
use function ougc\InviteSystem\Core\newPointsIsInstalled;
use function ougc\InviteSystem\MyAlerts\getAvailableLocations;
use function ougc\InviteSystem\MyAlerts\getInstalledLocations;
use function ougc\InviteSystem\MyAlerts\MyAlertsIsIntegrable;
use function ougc\InviteSystem\MyAlerts\uninstallLocation;

use const ougc\InviteSystem\ROOT;

const TABLES_DATA = [
    'ougc_invite_system_codes' => [
        'cid' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'uid' => [// usedby, owner
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'muid' => [// mod, creator
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'code' => [
            'type' => 'VARCHAR',
            'size' => 50,
            'default' => ''
        ],
        'email' => [ // who can use
            'type' => 'VARCHAR',
            'size' => 220,
            'default' => ''
        ],
        'active' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'usergroup' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 2
        ],
        'additionalgroups' => [
            'type' => 'VARCHAR',
            'size' => 200,
            'default' => ''
        ],
        'uses' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'stock' => [
            'type' => 'INT',
            'default' => 0
        ],
        'points' => [
            'type' => 'DECIMAL',
            'size' => '16,2',
            'default' => 0
        ],
        'expire' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'dateline' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
    ],
    'ougc_invite_system_logs' => [
        'lid' => [
            'type' => 'INT',
            'unsigned' => true,
            'auto_increment' => true,
            'primary_key' => true
        ],
        'uid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'cid' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
        'dateline' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ]
    ]
];

const FIELDS_DATA = [
    'usergroups' => [
        'ougc_invite_system_canview' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'ougc_invite_system_canadd' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'ougc_invite_system_activelimit' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 10
        ],
        'ougc_invite_system_canmanage' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canmodadd' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'ougc_invite_system_canbypasslimit' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'ougc_invite_system_canstock' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canmodstock' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canunlimitedstock' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canmodunlimitedstock' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canexpire' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'ougc_invite_system_canmodexpire' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'ougc_invite_system_canusergroup' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canmodusergroup' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canadditionalgroups' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canmodadditionalgroups' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canemail' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'ougc_invite_system_canmodemail' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'ougc_invite_system_cancustom' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canmodcustom' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canmultiple' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canmodmultiple' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 1
        ],
        'ougc_invite_system_candelete' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canmoddelete' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canusers' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_canpoints' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_renewal' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 10
        ],
    ],
    'users' => [
        'ougc_invite_system_banned' => [
            'type' => 'TINYINT',
            'unsigned' => true,
            'default' => 0
        ],
        'ougc_invite_system_renewal' => [
            'type' => 'INT',
            'unsigned' => true,
            'default' => 0
        ],
    ],
];

const TASK_ENABLE = 1;

const TASK_DEACTIVATE = 0;

const TASK_DELETE = -1;

function pluginInformation(): array
{
    global $lang;

    languageLoad();

    $myAlertsDescription = '';

    if (pluginIsInstalled() && MyAlertsIsIntegrable()) {
        $myAlertsDescription .= $lang->ougc_invite_system_myalerts_desc;
    }

    return [
        'name' => 'ougc Invite System',
        'description' => $lang->setting_group_ougc_invite_system_desc . $myAlertsDescription,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.30',
        'versioncode' => 1830,
        'compatibility' => '18*',
        'codename' => 'ougc_invite_system',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
        ],
        'myalerts' => [
            'version' => '2.0.4',
            'url' => 'https://community.mybb.com/thread-171301.html'
        ]
    ];
}

function pluginActivation(): bool
{
    global $PL, $lang, $cache;

    pluginLibraryLoad();

    $PL->settings(
        'ougc_invite_system',
        $lang->setting_group_ougc_invite_system,
        $lang->setting_group_ougc_invite_system_desc,
        [
            'invitegroups' => [
                'title' => $lang->setting_ougc_invite_system_invitegroups,
                'description' => $lang->setting_ougc_invite_system_invitegroups_desc,
                'optionscode' => 'groupselect',
                'value' => -1,
            ],
            'requirecode' => [
                'title' => $lang->setting_ougc_invite_system_requirecode,
                'description' => $lang->setting_ougc_invite_system_requirecode_desc,
                'optionscode' => 'yesno',
                'value' => 1,
            ],
            'referralsystem' => [
                'title' => $lang->setting_ougc_invite_system_referralsystem,
                'description' => $lang->setting_ougc_invite_system_referralsystem_desc,
                'optionscode' => 'yesno',
                'value' => 1,
            ],
            'referral_points' => [
                'title' => $lang->setting_ougc_invite_system_referral_points,
                'description' => $lang->setting_ougc_invite_system_referral_points_desc,
                'optionscode' => 'numeric',
                'value' => 50,
            ],
            'regattempts' => [
                'title' => $lang->setting_ougc_invite_system_regattempts,
                'description' => $lang->setting_ougc_invite_system_regattempts_desc,
                'optionscode' => 'numeric',
                'value' => 5,
            ],
            'regattempts_minutes' => [
                'title' => $lang->setting_ougc_invite_system_regattempts_minutes,
                'description' => $lang->setting_ougc_invite_system_regattempts_minutes_desc,
                'optionscode' => 'numeric',
                'value' => 30,
            ],
            'perpage' => [
                'title' => $lang->setting_ougc_invite_system_perpage,
                'description' => $lang->setting_ougc_invite_system_perpage_desc,
                'optionscode' => 'numeric',
                'value' => 20,
            ],
            'notifications' => [
                'title' => $lang->setting_ougc_invite_system_notifications,
                'description' => $lang->setting_ougc_invite_system_notifications_desc,
                'optionscode' => "checkbox
pm={$lang->setting_ougc_invite_system_notifications_pm}
myalerts={$lang->setting_ougc_invite_system_notifications_myalerts}",
                'value' => 'pm',
            ],
            'characters' => [
                'title' => $lang->setting_ougc_invite_system_characters,
                'description' => $lang->setting_ougc_invite_system_characters_desc,
                'optionscode' => 'text',
                'value' => 'a-_bcdefghijklmnopqrstuvwxyz0123456789',
            ],
            'length' => [
                'title' => $lang->setting_ougc_invite_system_length,
                'description' => $lang->setting_ougc_invite_system_length_desc,
                'optionscode' => 'numeric',
                'value' => 20,
            ],
            'renewalperiod' => [
                'title' => $lang->setting_ougc_invite_system_renewalperiod,
                'description' => $lang->setting_ougc_invite_system_renewalperiod_desc,
                'optionscode' => 'numeric',
                'value' => 0,
            ],
            'default_usergroup' => [
                'title' => $lang->setting_ougc_invite_system_default_usergroup,
                'description' => $lang->setting_ougc_invite_system_default_usergroup_desc,
                'optionscode' => 'groupselectsingle',
                'value' => 2,
            ],
            'default_additionalgroups' => [
                'title' => $lang->setting_ougc_invite_system_default_additionalgroups,
                'description' => $lang->setting_ougc_invite_system_default_additionalgroups_desc,
                'optionscode' => 'groupselect',
                'value' => '',
            ],
            'default_stock' => [
                'title' => $lang->setting_ougc_invite_system_default_stock,
                'description' => $lang->setting_ougc_invite_system_default_stock_desc,
                'optionscode' => 'numeric',
                'value' => 1,
            ],
            // send email
            // you have been invited by...
            'actionName' => [
                'title' => $lang->setting_ougc_invite_system_actionName,
                'description' => $lang->setting_ougc_invite_system_actionName_desc,
                'optionscode' => 'text',
                'value' => 'invite_system'
            ],
        ]
    );

    // Add templates
    $templatesDirIterator = new DirectoryIterator(ROOT . '/templates');

    $templates = [];

    foreach ($templatesDirIterator as $template) {
        if (!$template->isFile()) {
            continue;
        }

        $pathName = $template->getPathname();

        $pathInfo = pathinfo($pathName);

        if ($pathInfo['extension'] === 'html') {
            $templates[$pathInfo['filename']] = file_get_contents($pathName);
        }
    }

    if ($templates) {
        $PL->templates('ougcinvitesystem', 'ougc Invite System', $templates);
    }

    // Insert/update version into cache
    $plugins = $cache->read('ougc_plugins');

    if (!$plugins) {
        $plugins = [];
    }

    $pluginInformation = pluginInformation();

    if (!isset($plugins['invitesystem'])) {
        $plugins['invitesystem'] = $pluginInformation['versioncode'];
    }

    /*~*~* RUN UPDATES START *~*~*/

    /*~*~* RUN UPDATES END *~*~*/

    dbVerifyTables();

    dbVerifyColumns();

    taskActivation();

    $cache->update_usergroups();

    change_admin_permission('tools', 'ougc_invite_system');

    $plugins['invitesystem'] = $pluginInformation['versioncode'];

    $cache->update('ougc_plugins', $plugins);

    return true;
}

function pluginDeactivation(): bool
{
    taskDeactivation();

    change_admin_permission('tools', 'ougc_invite_system', 0);

    return true;
}

function pluginInstallation(): bool
{
    global $cache, $db;

    dbVerifyTables();

    dbVerifyColumns();

    foreach (FIELDS_DATA['usergroups'] as $fieldName => $fieldData) {
        $db->update_query('usergroups', [$fieldName => 1], "gid='4'");
    }

    $cache->update_usergroups();

    // MyAlerts
    $MyAlertLocationsInstalled = array_filter(
        getAvailableLocations(),
        '\\ougc\InviteSystem\MyAlerts\\isLocationAlertTypePresent'
    );

    $cache->update('ougc_invite_system_myalerts', [
        'MyAlertLocationsInstalled' => $MyAlertLocationsInstalled,
    ]);

    return true;
}

function pluginIsInstalled(): bool
{
    global $db;

    $isInstalledEach = true;

    foreach (TABLES_DATA as $tableName => $tableColumns) {
        $isInstalledEach = $db->table_exists($tableName) && $isInstalledEach;
    }

    foreach (FIELDS_DATA as $tableName => $tableData) {
        if (!$db->table_exists($tableName)) {
            continue;
        }

        foreach ($tableData as $fieldName => $fieldData) {
            $isInstalledEach = $db->field_exists($fieldName, $tableName) && $isInstalledEach;
        }
    }

    return $isInstalledEach;
}

function pluginUninstallation(): bool
{
    global $db, $PL, $cache;

    pluginLibraryLoad();

    // Drop DB entries
    foreach (dbTables() as $tableName => $tableColumns) {
        $db->drop_table($tableName);
    }

    foreach (FIELDS_DATA as $tableName => $tableColumns) {
        foreach ($tableColumns as $fieldName => $fieldData) {
            !$db->field_exists($fieldName, $tableName) || $db->drop_column($tableName, $fieldName);
        }
    }

    $PL->settings_delete('ougc_invite_system');

    $PL->templates_delete('ougcinvitesystem');

    taskUninstallation();

    if (MyAlertsIsIntegrable()) {
        $installedLocations = getInstalledLocations();

        foreach ($installedLocations as $installedLocation) {
            uninstallLocation($installedLocation);
        }
    }

    if (newPointsIsInstalled()) {
        log_remove(
            [
                'ougc_invite_system_register',
                'ougc_invite_system'
            ]
        );
    }

    // Delete version from cache
    $plugins = (array)$cache->read('ougc_plugins');

    if (isset($plugins['invitesystem'])) {
        unset($plugins['invitesystem']);
    }

    if (!empty($plugins)) {
        $cache->update('ougc_plugins', $plugins);
    } else {
        $cache->delete('ougc_plugins');
    }

    $cache->delete('ougc_invite_system_myalerts');

    change_admin_permission('tools', 'ougc_invite_system', -1);

    return true;
}

function dbTables(): array
{
    $tablesData = [];

    foreach (TABLES_DATA as $tableName => $tableColumns) {
        foreach ($tableColumns as $fieldName => $fieldData) {
            if (!isset($fieldData['type'])) {
                continue;
            }

            $tablesData[$tableName][$fieldName] = dbBuildFieldDefinition($fieldData);
        }

        foreach ($tableColumns as $fieldName => $fieldData) {
            if (isset($fieldData['primary_key'])) {
                $tablesData[$tableName]['primary_key'] = $fieldName;
            }
            if ($fieldName === 'unique_key') {
                $tablesData[$tableName]['unique_key'] = $fieldData;
            }
        }
    }

    return $tablesData;
}

function dbVerifyTables(): bool
{
    global $db;

    $collation = $db->build_create_table_collation();

    foreach (dbTables() as $tableName => $tableColumns) {
        if ($db->table_exists($tableName)) {
            foreach ($tableColumns as $fieldName => $fieldData) {
                if ($fieldName == 'primary_key' || $fieldName == 'unique_key') {
                    continue;
                }

                if ($db->field_exists($fieldName, $tableName)) {
                    $db->modify_column($tableName, "`{$fieldName}`", $fieldData);
                } else {
                    $db->add_column($tableName, $fieldName, $fieldData);
                }
            }
        } else {
            $queryString = "CREATE TABLE IF NOT EXISTS `{$db->table_prefix}{$tableName}` (";

            foreach ($tableColumns as $fieldName => $fieldData) {
                if ($fieldName == 'primary_key') {
                    $queryString .= "PRIMARY KEY (`{$fieldData}`)";
                } elseif ($fieldName != 'unique_key') {
                    $queryString .= "`{$fieldName}` {$fieldData},";
                }
            }

            $queryString .= ") ENGINE=MyISAM{$collation};";

            $db->write_query($queryString);
        }
    }

    dbVerifyIndexes();

    return true;
}

function dbVerifyIndexes(): bool
{
    global $db;

    foreach (dbTables() as $tableName => $tableColumns) {
        if (!$db->table_exists($tableName)) {
            continue;
        }

        if (isset($tableColumns['unique_key'])) {
            foreach ($tableColumns['unique_key'] as $keyName => $keyValue) {
                if ($db->index_exists($tableName, $keyName)) {
                    continue;
                }

                $db->write_query(
                    "ALTER TABLE {$db->table_prefix}{$tableName} ADD UNIQUE KEY {$keyName} ({$keyValue})"
                );
            }
        }
    }

    return true;
}

function dbBuildFieldDefinition(array $fieldData): string
{
    $fieldDefinition = '';

    $fieldDefinition .= $fieldData['type'];

    if (isset($fieldData['size'])) {
        $fieldDefinition .= "({$fieldData['size']})";
    }

    if (isset($fieldData['unsigned'])) {
        if ($fieldData['unsigned'] === true) {
            $fieldDefinition .= ' UNSIGNED';
        } else {
            $fieldDefinition .= ' SIGNED';
        }
    }

    if (!isset($fieldData['null'])) {
        $fieldDefinition .= ' NOT';
    }

    $fieldDefinition .= ' NULL';

    if (isset($fieldData['auto_increment'])) {
        $fieldDefinition .= ' AUTO_INCREMENT';
    }

    if (isset($fieldData['default'])) {
        $fieldDefinition .= " DEFAULT '{$fieldData['default']}'";
    }

    return $fieldDefinition;
}

function dbVerifyColumns(): bool
{
    global $db;

    foreach (FIELDS_DATA as $tableName => $tableColumns) {
        foreach ($tableColumns as $fieldName => $fieldData) {
            if (!isset($fieldData['type']) || !$db->table_exists($tableName)) {
                continue;
            }

            if ($db->field_exists($fieldName, $tableName)) {
                $db->modify_column($tableName, "`{$fieldName}`", dbBuildFieldDefinition($fieldData));
            } else {
                $db->add_column($tableName, $fieldName, dbBuildFieldDefinition($fieldData));
            }
        }
    }

    return true;
}

function taskActivation($action = TASK_ENABLE): bool
{
    global $db, $lang;

    languageLoad();

    if ($action === TASK_DELETE) {
        $db->delete_query('tasks', "file='ougc_invite_system'");

        return true;
    }

    $query = $db->simple_select('tasks', '*', "file='ougc_invite_system'", ['limit' => 1]);

    $taskData = $db->fetch_array($query);

    if (!empty($taskData)) {
        $db->update_query('tasks', ['enabled' => $action], "file='ougc_invite_system'");
    } else {
        include_once MYBB_ROOT . 'inc/functions_task.php';

        $_ = $db->escape_string('*');

        $insertData = [
            'title' => $db->escape_string($lang->setting_group_ougc_invite_system),
            'description' => $db->escape_string($lang->setting_group_ougc_invite_system_desc),
            'file' => $db->escape_string('ougc_invite_system'),
            'minute' => '20,50',
            'hour' => $_,
            'day' => $_,
            'weekday' => $_,
            'month' => $_,
            'enabled' => 1,
            'logging' => 1
        ];

        $insertData['nextrun'] = fetch_next_run($insertData);

        $db->insert_query('tasks', $insertData);
    }

    return true;
}

function taskDeactivation(): bool
{
    return taskActivation(TASK_DEACTIVATE);
}

function taskUninstallation(): bool
{
    return taskActivation(TASK_DELETE);
}

function pluginLibraryLoad()
{
    global $PL, $lang;

    languageLoad();

    if ($file_exists = file_exists(PLUGINLIBRARY)) {
        global $PL;

        $PL || require_once PLUGINLIBRARY;
    }

    $pluginInformation = pluginInformation();

    if (!$file_exists || $PL->version < $pluginInformation['pl']['version']) {
        flash_message(
            $lang->sprintf(
                $lang->ougc_invite_system_pluginlibrary,
                $pluginInformation['pl']['url'],
                $pluginInformation['pl']['version']
            ),
            'error'
        );

        admin_redirect('index.php?module=config-plugins');
    }
}