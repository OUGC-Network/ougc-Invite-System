<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/plugins/ougc_invite_system/core.php)
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

namespace ougc\InviteSystem\Core;

use DateTime;
use MyLanguage;

use const ougc\InviteSystem\ROOT;

const CODE_STOCK_IS_INFINITE = -1;

const URL = 'usercp.php';

function languageLoad(&$userLanguage = null): bool
{
    if (!($userLanguage instanceof MyLanguage)) {
        global $lang;

        $userLanguage = &$lang;
    }

    if (!isset($userLanguage->setting_group_ougc_invite_system)) {
        $userLanguage->load('ougc_invite_system');

        if (defined('IN_ADMINCP')) {
            $userLanguage->load('ougc_invite_system', true);
        }
    }

    return true;
}

function addHooks(string $namespace): bool
{
    global $plugins;

    $namespaceLowercase = strtolower($namespace);

    $definedUserFunctions = get_defined_functions()['user'];

    foreach ($definedUserFunctions as $callable) {
        $namespaceWithPrefixLength = strlen($namespaceLowercase) + 1;

        if (substr($callable, 0, $namespaceWithPrefixLength) == $namespaceLowercase . '\\') {
            $hookName = substr_replace($callable, '', 0, $namespaceWithPrefixLength);

            $priority = substr($callable, -2);

            if (is_numeric(substr($hookName, -2))) {
                $hookName = substr($hookName, 0, -2);
            } else {
                $priority = 10;
            }

            $plugins->add_hook($hookName, $callable, $priority);
        }
    }

    return true;
}

function getSetting(string $settingKey = '')
{
    global $mybb;

    return SETTINGS[$settingKey] ?? (
        $mybb->settings['ougc_invite_system_' . $settingKey] ?? false
    );
}

function getTemplateName(string $templateName = ''): string
{
    $templatePrefix = '';

    if ($templateName) {
        $templatePrefix = '_';
    }

    return "ougcinvitesystem{$templatePrefix}{$templateName}";
}

function getTemplate(string $templateName = '', bool $enableHTMLComments = true): string
{
    global $templates;

    if (DEBUG) {
        $filePath = ROOT . "/templates/{$templateName}.html";

        $templateContents = file_get_contents($filePath);

        $templates->cache[getTemplateName($templateName)] = $templateContents;
    } elseif (my_strpos($templateName, '/') !== false) {
        $templateName = substr($templateName, strpos($templateName, '/') + 1);
    }

    return $templates->render(getTemplateName($templateName), true, $enableHTMLComments);
}

function alertSend(int $userID, int $referrerUserID): bool
{
    global $db;

    if (!class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        return false;
    }

    $query = $db->simple_select('alert_types', 'id', "code='ougc_invite_system_register'");

    $alertTypeID = (int)$db->fetch_field($query, 'id');

    if (!$alertTypeID) {
        return false;
    }

    $query = $db->simple_select(
        'alerts',
        'id',
        "object_id='{$userID}' AND uid='{$referrerUserID}' AND unread=1 AND alert_type_id='{$alertTypeID}'"
    );

    if ($db->num_rows($query)) {
        return false;
    }

    $time = new DateTime();

    $db->insert_query('alerts', [
        'uid' => $referrerUserID,
        'from_user_id' => $userID,
        'alert_type_id' => $alertTypeID,
        'object_id' => $userID,
        'dateline' => $time->format('Y-m-d H:i:s'),
        'extra_details' => json_encode([]),
        'unread' => 1,
    ]);

    return true;
}

function urlHandler(string $newUrl = ''): string
{
    static $setUrl = URL;

    if (($newUrl = trim($newUrl))) {
        $setUrl = $newUrl;
    }

    return $setUrl;
}

function urlHandlerSet(string $newUrl): string
{
    return urlHandler($newUrl);
}

function urlHandlerGet(): string
{
    return urlHandler();
}

function urlHandlerBuild(array $urlAppend = [], bool $fetchImportUrl = false, bool $encode = true): string
{
    global $PL;

    if (!is_object($PL)) {
        $PL or require_once PLUGINLIBRARY;
    }

    if ($fetchImportUrl === false) {
        if ($urlAppend && !is_array($urlAppend)) {
            $urlAppend = explode('=', $urlAppend);
            $urlAppend = [$urlAppend[0] => $urlAppend[1]];
        }
    }

    return $PL->url_append(urlHandlerGet(), $urlAppend, '&amp;', $encode);
}

function codeGenerate(string &$codeString = ''): string
{
    $length = min(max((int)getSetting('length'), 0), 50);

    $codeString = '';

    $characters = getSetting('characters');

    $charactersLength = my_strlen($characters);

    for ($i = 1; $i <= $length; ++$i) {
        $codeString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $codeString;
}

function expireCodes(): bool
{
    global $db;

    $timeNow = TIME_NOW;

    $stockInfiniteValue = CODE_STOCK_IS_INFINITE;

    $db->update_query(
        'ougc_invite_system_codes',
        ['active' => 0],
        "active!='0' AND ((stock!='{$stockInfiniteValue}' AND uses>=`stock`) OR (expire!='0' AND expire<'{$timeNow}'))",
        '',
        true
    );

    return true;
}

function userGetPermissions(int $userID): array
{
    $userData = get_user($userID);

    if (empty($userData['uid'])) {
        $groupPermissions = [];
    } else {
        $groupPermissions = usergroup_permissions("{$userData['usergroup']},{$userData['additionalgroups']}");

        if ($userData['displaygroup']) {
            $displayGroupProperties = usergroup_displaygroup($userData['displaygroup']);

            if (is_array($displayGroupProperties)) {
                $groupPermissions = array_merge($groupPermissions, $displayGroupProperties);
            }
        }
    }

    return $groupPermissions;
}

function codeInsert(array $insertData, bool $isUpdate = false, int $codeID = 0): bool
{
    global $db, $mybb;

    $codeData = [];

    if (isset($insertData['uid'])) {
        $codeData['uid'] = (int)$insertData['uid'];
    }

    if (isset($insertData['muid'])) {
        $codeData['muid'] = (int)$insertData['muid'];
    }

    if (isset($insertData['code'])) {
        $codeData['code'] = $db->escape_string($insertData['code']);
    }

    if (isset($insertData['email'])) {
        $codeData['email'] = $db->escape_string($insertData['email']);
    }

    if (isset($insertData['active'])) {
        $codeData['active'] = (int)$insertData['active'];
    }

    if (isset($insertData['usergroup'])) {
        $codeData['usergroup'] = (int)$insertData['usergroup'];
    } elseif (!$isUpdate) {
        $codeData['usergroup'] = (int)getSetting('default_usergroup');
    }

    if (isset($insertData['additionalgroups'])) {
        $codeData['additionalgroups'] = $db->escape_string($insertData['additionalgroups']);
    } elseif (!$isUpdate) {
        static $defaultAdditionalGroups = null;

        if ($defaultAdditionalGroups === null) {
            $defaultAdditionalGroups = getSetting('default_additionalgroups');

            if ((int)$defaultAdditionalGroups === -1) {
                $defaultAdditionalGroups = implode(',', array_keys((array)$mybb->cache->read('usergroups')));
            }
        }

        $codeData['additionalgroups'] = (string)$defaultAdditionalGroups;
    }

    if (isset($insertData['uses'])) {
        $codeData['uses'] = (int)$insertData['uses'];
    }

    if (isset($insertData['stock'])) {
        $codeData['stock'] = (int)$insertData['stock'];
    } elseif (!$isUpdate) {
        $codeData['stock'] = (int)getSetting('default_stock');
    }

    if (isset($insertData['points'])) {
        $codeData['points'] = (float)$insertData['points'];
    }

    if (isset($insertData['expire'])) {
        $codeData['expire'] = (int)$insertData['expire'];
    }

    if (isset($insertData['dateline'])) {
        $codeData['dateline'] = (int)$insertData['dateline'];
    } elseif (!$isUpdate) {
        $codeData['dateline'] = TIME_NOW;
    }

    if ($isUpdate) {
        $db->update_query('ougc_invite_system_codes', $codeData, "cid='{$codeID}'");
    } else {
        $db->insert_query('ougc_invite_system_codes', $codeData);
    }

    return true;
}

function codeUpdate(array $updateData, int $codeID): bool
{
    return codeInsert($updateData, true, $codeID);
}

function codeDelete(array $whereClauses): bool
{
    global $db;

    $db->delete_query('ougc_invite_system_codes', implode(' AND ', $whereClauses));

    return true;
}

function logInsert(array $insertData): bool
{
    global $db;

    $logData = [];

    if (isset($insertData['uid'])) {
        $logData['uid'] = (int)$insertData['uid'];
    }

    if (isset($insertData['cid'])) {
        $logData['cid'] = (int)$insertData['cid'];
    }

    if (isset($insertData['dateline'])) {
        $logData['dateline'] = (int)$insertData['dateline'];
    } else {
        $logData['dateline'] = TIME_NOW;
    }

    $db->insert_query('ougc_invite_system_logs', $logData);

    return true;
}

function logDelete(array $whereClauses): bool
{
    global $db;

    $db->delete_query('ougc_invite_system_logs', implode(' AND ', $whereClauses));

    return true;
}

function codeGet(
    array $whereClauses = [],
    array $queryFields = [
        'cid',
        'uid',
        'muid',
        'code',
        'email',
        'active',
        'usergroup',
        'additionalgroups',
        'uses',
        'stock',
        'points',
        'expire',
        'dateline'
    ]
): array {
    global $db;

    $query = $db->simple_select(
        'ougc_invite_system_codes',
        implode(',', $queryFields),
        implode(' AND ', $whereClauses),
        ['limit' => 1]
    );

    return (array)$db->fetch_array($query);
}

function executeTask(): bool
{
    global $mybb, $db;

    if (getSetting('renewalperiod')) {
        return false;
    }

    $renewalTime = TIME_NOW - (getSetting('renewalperiod') * 60 * 60 * 24);

    $userIDs = $groupIDs = [];

    $whereClauses = ["ougc_invite_system_renewal<'{$renewalTime}'"];

    $groupsCache = $mybb->cache->read('usergroups');

    foreach ($groupsCache as $groupID => $groupData) {
        if (!empty($groupData['ougc_invite_system_renewal'])) {
            $groupIDs[] = (int)$groupID;
        }
    }

    if ($groupIDs) {
        $groupIDsString = implode("','", $groupIDs);

        $whereClausesInner = ["usergroup IN ('{$groupIDsString}')"];

        foreach ($groupIDs as $groupID) {
            switch ($db->type) {
                case 'pgsql':
                case 'sqlite':
                    $whereClausesInner[] = "(','||additionalgroups||',' LIKE '%,{$groupID},%')";
                    break;
                default:
                    $whereClausesInner[] = "(CONCAT(',',additionalgroups,',') LIKE '%,{$groupID},%')";
                    break;
            }
        }

        $whereClauses[] = '(' . implode(' OR ', $whereClausesInner) . ')';
    }

    $query = $db->simple_select('users', 'uid', implode(' AND ', $whereClauses), ['limit' => 50]);

    while ($userID = $db->fetch_field($query, 'uid')) {
        $userIDs[] = (int)$userID;
    }

    if ($userIDs) {
        $usersCache = [];

        foreach ($userIDs as $userID) {
            $userGroupPermissions = userGetPermissions($userID);

            if (!$userGroupPermissions['ougc_invite_system_renewal']) {
                continue;
            }

            $usersCache[$userID] = $userGroupPermissions['ougc_invite_system_renewal'];
        }

        foreach ($usersCache as $userID => $userCodeCount) {
            $insertData = [
                'uid' => $userID
            ];

            while ($userCodeCount > 0) {
                $uniqueCodeFound = false;

                while ($uniqueCodeFound === false) {
                    $generatedCode = codeGenerate();

                    if (!codeGet(["code='{$db->escape_string(my_strtolower($generatedCode))}'"])) {
                        $insertData['code'] = my_strtolower($generatedCode);

                        $uniqueCodeFound = true;
                    }
                }

                codeInsert($insertData);

                --$userCodeCount;
            }

            $db->update_query('users', ['ougc_invite_system_renewal' => TIME_NOW], "uid='{$userID}'");
        }
    }

    return true;
}

function newPointsIsInstalled(): bool
{
    if (!defined('NEWPOINTS_VERSION_CODE') || !function_exists('newpoints_format_points')) {
        return false;
    }

    if (NEWPOINTS_VERSION_CODE < getSetting('newpointsMinimumVersion')) {
        return false;
    }

    return true;
}