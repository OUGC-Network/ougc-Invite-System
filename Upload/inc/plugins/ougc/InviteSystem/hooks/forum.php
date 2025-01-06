<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/plugins/ougc_invite_system/forum_hooks.php)
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

namespace ougc\InviteSystem\Hooks\Forum;

use MyBB;

use function ougc\InviteSystem\Core\codeGet;
use function ougc\InviteSystem\Core\newPointsIsInstalled;
use function ougc\InviteSystem\Core\urlHandlerBuild;
use function ougc\InviteSystem\Core\codeDelete;
use function ougc\InviteSystem\Core\codeUpdate;
use function ougc\InviteSystem\Core\expireCodes;
use function ougc\InviteSystem\Core\codeGenerate;
use function ougc\InviteSystem\Core\getSetting;
use function ougc\InviteSystem\Core\getTemplate;
use function ougc\InviteSystem\Core\userGetPermissions;
use function ougc\InviteSystem\Core\codeInsert;
use function ougc\InviteSystem\Core\languageLoad;
use function ougc\InviteSystem\Core\urlHandlerSet;
use function ougc\InviteSystem\MyAlerts\initLocations;
use function ougc\InviteSystem\MyAlerts\initMyAlerts;
use function ougc\InviteSystem\MyAlerts\myalertsIsIntegrable;
use function ougc\InviteSystem\MyAlerts\registerMyAlertsFormatters;

use const ougc\InviteSystem\Core\CODE_STOCK_IS_INFINITE;

function global_start(): bool
{
    global $templatelist, $mybb;

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    $templatelist .= ',';

    if (defined('THIS_SCRIPT')) {
        if (THIS_SCRIPT == 'usercp.php' || THIS_SCRIPT == 'modcp.php') {
            $templatelist .= ', ougcinvitesystem_usercp_nav, ougcinvitesystem_content_button_deactivate, ougcinvitesystem_content_button_delete, ougcinvitesystem_content_buttons, ougcinvitesystem_filter, ougcinvitesystem_content_multipage, ougcinvitesystem_content_points_thead, ougcinvitesystem_content_points_column, ougcinvitesystem_content_row, ougcinvitesystem_form_code, ougcinvitesystem_form_email, ougcinvitesystem_form_usergroup_item, ougcinvitesystem_form_usergroup, ougcinvitesystem_form_stock_unlimited, ougcinvitesystem_form_stock, ougcinvitesystem_form_expire, ougcinvitesystem_form_multiple, ougcinvitesystem_form_users, ougcinvitesystem_form_points, ougcinvitesystem_form, ougcinvitesystem_content, ougcinvitesystem, ougcinvitesystem_modcp_nav, ougcinvitesystem_filter_username, ougcinvitesystem_content_empty,ougcinvitesystem_current';
        }
    }

    if (MyAlertsIsIntegrable()) {
        initMyAlerts();

        initLocations();
    }

    if (myalertsIsIntegrable() && !empty($mybb->user['uid'])) {
        registerMyAlertsFormatters();
    }

    return true;
}

function myalerts_load_lang(): bool
{
    return languageLoad();
}

function usercp_menu_built(): bool
{
    global $mybb;

    if (!empty($mybb->usergroup['ougc_invite_system_canview'])) {
        global $lang, $usercpnav;

        languageLoad();

        $url = urlHandlerBuild(['action' => getSetting('actionName')]);

        $usercpnav = str_replace('<!--OUGC_INVITE_SYSTEM-->', eval(getTemplate('usercp_nav')), $usercpnav);
    }

    return true;
}

function usercp_start(): bool
{
    return modcp_start();
}

function modcp_start(): bool
{
    global $mybb, $lang, $plugins, $db;
    global $headerinclude, $header, $theme, $footer, $gobutton;
    global $modcp_nav, $usercpnav;

    languageLoad();

    $isModeratorControlPanel = $plugins->current_hook === 'modcp_start';

    if ($isModeratorControlPanel) {
        $moderatorGroupPermissions = $mybb->usergroup;

        urlHandlerSet('modcp.php');

        $hasPermission = !empty($moderatorGroupPermissions['ougc_invite_system_canmanage']);

        $currentUserID = $mybb->get_input('uid', MyBB::INPUT_INT);

        if ($currentUserID) {
            $userData = get_user($mybb->get_input('uid', MyBB::INPUT_INT));

            if (empty($userData['uid'])) {
                error($lang->ougc_invite_system_error_invalid_user);
            }

            $mybb->input['username'] = $userData['username'];
        } elseif ($mybb->get_input('username')) {
            $userData = get_user_by_username($mybb->get_input('username'));

            if (empty($userData['uid'])) {
                error($lang->ougc_invite_system_error_invalid_user);
            }

            $currentUserID = (int)$userData['uid'];
        }

        $userGroupPermissions = userGetPermissions($currentUserID);
    } else {
        $userGroupPermissions = $moderatorGroupPermissions = $mybb->usergroup;

        $hasPermission = !empty($userGroupPermissions['ougc_invite_system_canview']);

        $currentUserID = (int)$mybb->user['uid'];
    }

    $userData = get_user($currentUserID);

    $urlParams = [];

    $actionName = getSetting('actionName');

    urlHandlerSet(urlHandlerBuild(['action' => $actionName]));

    $url = urlHandlerBuild();

    $whereClauses = ["uid='{$currentUserID}'"];

    if ($hasPermission) {
        if ($isModeratorControlPanel) {
            $modcp_nav = str_replace('<!--OUGC_INVITE_SYSTEM-->', eval(getTemplate('modcp_nav')), $modcp_nav);

            $navigation = &$modcp_nav;

            $urlParams['uid'] = $currentUserID;

            $pageTitle = $lang->ougc_invite_system_modcp_nav;
        } else {
            $navigation = &$usercpnav;

            $pageTitle = $lang->ougc_invite_system_usercp_nav;
        }
    }

    if ($mybb->get_input('action') !== $actionName) {
        return false;
    }

    if (!$hasPermission) {
        error_no_permission();
    }

    if (!$isModeratorControlPanel && !empty($userData['ougc_invite_system_banned'])) {
        error($lang->ougc_invite_system_errors_banned);
    }

    expireCodes();

    if ($isModeratorControlPanel) {
        add_breadcrumb($lang->nav_modcp, 'modcp.php');
    } else {
        add_breadcrumb($lang->nav_usercp, 'usercp.php');
    }

    add_breadcrumb($pageTitle);

    $groupsCache = $mybb->cache->read('usergroups');

    if ($mybb->request_method === 'post') {
        if ($mybb->get_input('do') === 'add') {
            if (empty($userGroupPermissions['ougc_invite_system_canadd']) && empty($moderatorGroupPermissions['ougc_invite_system_canmodadd'])) {
                error_no_permission();
            }

            $errors = [];

            $insertData = [
                'muid' => (int)$mybb->user['uid']
            ];

            $generateCount = $mybb->get_input('multiple', MyBB::INPUT_INT);

            if (
                empty($userGroupPermissions['ougc_invite_system_canmultiple']) &&
                empty($moderatorGroupPermissions['ougc_invite_system_canmodmultiple']) ||
                $generateCount < 1
            ) {
                $generateCount = 1;
            }

            if (
                (!empty($userGroupPermissions['ougc_invite_system_cancustom']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodcustom'])) &&
                $generateCount === 1 &&
                $mybb->get_input('code')
            ) {
                if (codeGet(["code='{$db->escape_string(my_strtolower($mybb->get_input('code')))}'"])) {
                    $errors[] = $lang->ougc_invite_system_errors_repeated_code;
                } else {
                    $isCustomCode = true;

                    $insertData['code'] = my_strtolower($mybb->get_input('code'));

                    if (my_strlen($insertData['code']) > 50) {
                        $errors[] = $lang->ougc_invite_system_errors_invalidcodelength;
                    }
                }
            }

            if (
                (!empty($userGroupPermissions['ougc_invite_system_canemail']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodemail'])) &&
                $mybb->get_input('email')
            ) {
                if (validate_email_format($mybb->get_input('email'))) {
                    $insertData['email'] = $mybb->get_input('email');
                } else {
                    $errors[] = $lang->ougc_invite_system_errors_invalidemail;
                }
            }

            foreach (['usergroup', 'additionalgroups'] as $codeFieldKey) {
                if (empty($userGroupPermissions['ougc_invite_system_can' . $codeFieldKey]) && empty($moderatorGroupPermissions['ougc_invite_system_canmod' . $codeFieldKey])) {
                    continue;
                }

                if (my_strpos(getSetting('invitegroups'), ',') === false) {
                    $groupIDs = (int)getSetting('invitegroups');
                } else {
                    $groupIDs = array_map('intval', explode(',', getSetting('invitegroups')));
                }

                if ((int)$groupIDs === -1 || is_array($groupIDs)) {
                    foreach ($groupsCache as $groupID => $groupData) {
                        $groupID = (int)$groupID;

                        if (
                            ((int)$groupIDs !== -1 && !in_array($groupID, (array)$groupIDs)) ||
                            !empty($groupData['cancp']) ||
                            !empty($groupData['issupermod']) ||
                            !empty($groupData['canmodcp']) ||
                            $groupID === 1
                        ) {
                            continue;
                        }

                        $inputGroups = array_map('intval', $mybb->get_input($codeFieldKey, MyBB::INPUT_ARRAY));

                        $selectedElement = '';

                        if (!empty($inputGroups)) {
                            foreach ($inputGroups as $inputGroupID) {
                                if ($groupID == (int)$inputGroupID) {
                                    if (!empty($insertData[$codeFieldKey])) {
                                        $insertData[$codeFieldKey] .= ',' . $groupID;
                                    } else {
                                        $insertData[$codeFieldKey] = $groupID;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($userGroupPermissions['ougc_invite_system_canstock']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodstock'])) {
                if (
                    (!empty($userGroupPermissions['ougc_invite_system_canunlimitedstock']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodunlimitedstock'])) &&
                    $mybb->get_input('unlimited', MyBB::INPUT_INT)
                ) {
                    $insertData['stock'] = CODE_STOCK_IS_INFINITE;
                } elseif ($mybb->get_input('stock', MyBB::INPUT_INT)) {
                    $insertData['stock'] = max($mybb->get_input('stock', MyBB::INPUT_INT), 0);
                } else {
                    $errors[] = $lang->ougc_invite_system_errors_invalidstock;
                }
            }

            if (!empty($moderatorGroupPermissions['ougc_invite_system_canpoints'])) {
                if ($mybb->get_input('points', MyBB::INPUT_FLOAT)) {
                    $insertData['points'] = $mybb->get_input('points', MyBB::INPUT_FLOAT);
                } elseif ($mybb->get_input('points', MyBB::INPUT_FLOAT) < 0) {
                    $errors[] = $lang->ougc_invite_system_errors_invalidpoints;
                }
            }

            if (!empty($userGroupPermissions['ougc_invite_system_canexpire']) || empty($moderatorGroupPermissions['ougc_invite_system_canmodexpire'])) {
                $expireDate = $mybb->get_input('expire_date');

                $expireTime = $mybb->get_input('expire_time');

                if ($expireDate && $expireTime) {
                    $endDate = array_map('intval', (array)explode('-', $expireDate));

                    $endTime = array_map('intval', (array)explode(':', $expireTime));

                    $endDateExpire = (int)gmmktime(
                        $endTime[0] ?? 0,
                        $endTime[1] ?? 0,
                        0,
                        $endDate[1] ?? 0,
                        $endDate[2] ?? 0,
                        $endDate[0] ?? 0
                    );

                    if ($endDateExpire <= TIME_NOW ||
                        !checkdate($endDate[1] ?? 0, $endDate[2] ?? 0, $endDate[0] ?? 0)) {
                        $errors[] = $lang->ougc_invite_system_errors_invalidenddate;
                    } else {
                        $insertData['expire'] = $endDateExpire;
                    }
                }
            }

            if (
                $isModeratorControlPanel &&
                !empty($moderatorGroupPermissions['ougc_invite_system_canusers']) &&
                $mybb->get_input('usernames')
            ) {
                $userNames = array_filter((array)explode(',', $mybb->get_input('usernames')));

                $userNamesList = implode(
                    "','",
                    array_map([$db, 'escape_string'], array_map('my_strtolower', $userNames))
                );

                $query = $db->simple_select('users', 'uid', "LOWER(username) IN ('{$userNamesList}')");

                $userIDs = [];

                while ($userData = $db->fetch_array($query)) {
                    $userIDs[] = (int)$userData['uid'];
                }

                if (empty($userIDs)) {
                    $errors[] = $lang->ougc_invite_system_errors_invalidusers;
                }
            } else {
                $userIDs = $currentUserID;
            }

            if (empty($userIDs)) {
                $errors[] = $lang->ougc_invite_system_errors_invalidusers;
            }

            $limitExceeded = false;

            if (!is_array($userIDs)) {
                if (empty($moderatorGroupPermissions['ougc_invite_system_canbypasslimit'])) {
                    if (empty($userGroupPermissions['ougc_invite_system_activelimit'])) {
                        $limitExceeded = true;
                    }

                    if (empty($limitExceeded)) {
                        $maximumInvites = (int)$userGroupPermissions['ougc_invite_system_activelimit'];

                        $codesData = codeGet(
                            ["uid='{$currentUserID}'", "active='1'"],
                            ['COUNT(cid) AS existingInvites']
                        );

                        $existingInvites = $codesData['existingInvites'] ?? 0;

                        if ($existingInvites >= $maximumInvites) {
                            $limitExceeded = true;
                        }

                        if ($generateCount > ($maximumInvites - $existingInvites)) {
                            $generateCount = $maximumInvites - $existingInvites;
                        }

                        //$generateCount -= $existingInvites;

                        if ($generateCount < 1) {
                            $limitExceeded = true;
                        }
                    }
                }

                $userIDs = [$userIDs];
            }

            if ($limitExceeded) {
                if ($isModeratorControlPanel) {
                    $errors[] = $lang->ougc_invite_system_errors_maxreached_modcp;
                } else {
                    $errors[] = $lang->ougc_invite_system_errors_maxreached;
                }
            }

            if (empty($errors)) {
                $userGroupsCache = [$currentUserID => $userGroupPermissions];

                $limitExceeded = false;

                foreach ($userIDs as $userID) {
                    $insertData['uid'] = $userID;

                    if (!isset($userGroupsCache[$userID])) {
                        $userGroupsCache[$userID] = userGetPermissions($userID);
                    }

                    $userGroupPermissions = $userGroupsCache[$userID];

                    $userGenerateCount = $generateCount;

                    if (empty($moderatorGroupPermissions['ougc_invite_system_canbypasslimit'])) {
                        if (empty($userGroupPermissions['ougc_invite_system_activelimit'])) {
                            $limitExceeded = true;

                            break;
                        }

                        $maximumInvites = (int)$userGroupPermissions['ougc_invite_system_activelimit'];

                        $codesData = codeGet(
                            ["uid='{$currentUserID}'", "active='1'"],
                            ['COUNT(cid) AS existingInvites']
                        );

                        $existingInvites = $codesData['existingInvites'] ?? 0;

                        if ($existingInvites >= $maximumInvites) {
                            $limitExceeded = true;
                        }

                        if ($userGenerateCount > ($maximumInvites - $existingInvites)) {
                            $userGenerateCount = $maximumInvites - $existingInvites;
                        }

                        //$userGenerateCount -= $existingInvites;

                        if ($userGenerateCount < 1) {
                            $limitExceeded = true;
                        }
                    }

                    if ($generateCount != $userGenerateCount) {
                        $limitExceeded = true;
                    }

                    while ($userGenerateCount > 0) {
                        if (!isset($isCustomCode)) {
                            $uniqueCodeFound = false;

                            while ($uniqueCodeFound === false) {
                                $generatedCode = codeGenerate();

                                if (!codeGet(["code='{$db->escape_string(my_strtolower($generatedCode))}'"])) {
                                    $insertData['code'] = my_strtolower($generatedCode);

                                    $uniqueCodeFound = true;
                                }
                            }
                        }

                        codeInsert($insertData);

                        --$userGenerateCount;

                        if (isset($isCustomCode)) {
                            break 2;
                        }
                    }
                }

                $message = $lang->ougc_invite_system_redirect_generated;

                if (!empty($limitExceeded)) {
                    $message = $lang->ougc_invite_system_redirect_generated_limitexceeded;

                    if ($isModeratorControlPanel) {
                        $message = $lang->ougc_invite_system_redirect_generated_limitexceeded_modcp;
                    }
                }

                redirect(urlHandlerBuild($urlParams), $lang->ougc_invite_system_redirect_generated);
            } else {
                $errors = inline_error($errors);
            }
        } elseif ($mybb->get_input('do') === 'manage') {
            if (
                $mybb->get_input('delete') &&
                empty($userGroupPermissions['ougc_invite_system_candelete']) &&
                empty($moderatorGroupPermissions['ougc_invite_system_canmoddelete'])
            ) {
                error_no_permission();
            }

            foreach ($mybb->get_input('check', MyBB::INPUT_ARRAY) as $codeID) {
                $codeID = (int)$codeID;

                if (isset($mybb->input['deactivate'])) {
                    codeUpdate(['active' => 0], $codeID);
                }

                if (isset($mybb->input['delete'])) {
                    $whereClauses = ["cid='{$codeID}'"];

                    if (empty($moderatorGroupPermissions['ougc_invite_system_canmanage'])) {
                        $whereClauses[] = "uses='0'";
                    }

                    codeDelete($whereClauses);
                }
            }

            if ($mybb->get_input('deactivate')) {
                redirect(urlHandlerBuild($urlParams), $lang->ougc_invite_system_redirect_deactivated);
            }

            if ($mybb->get_input('delete')) {
                redirect(urlHandlerBuild($urlParams), $lang->ougc_invite_system_redirect_deleted);
            }
        }
    }

    $errors = $errors ?? '';

    if ($isModeratorControlPanel && $currentUserID && $mybb->get_input('do') === 'toggleBan') {
        $status = 1;

        $message = $lang->ougc_invite_system_redirect_banned;

        if (!empty($userData['ougc_invite_system_banned'])) {
            $status = 0;

            $message = $lang->ougc_invite_system_redirect_banlifted;
        }

        $db->update_query('users', ['ougc_invite_system_banned' => $status], implode(' AND ', $whereClauses));

        redirect(urlHandlerBuild($urlParams), $message);
    }

    $formInputCode = htmlspecialchars_uni($mybb->get_input('code'));

    $formInputMail = htmlspecialchars_uni($mybb->get_input('email'));

    $formInputDateExpire = htmlspecialchars_uni($mybb->get_input('expire_date'));

    $formInputDateTimeExpire = htmlspecialchars_uni($mybb->get_input('expire_time'));

    $formInputStock = max($mybb->get_input('stock', MyBB::INPUT_INT), 1);

    $formInputMultiple = max($mybb->get_input('multiple', MyBB::INPUT_INT), 1);

    //$formInputUnlimited = min($mybb->get_input('unlimited', MyBB::INPUT_INT), 0);

    $formInputPoints = $mybb->get_input('points', MyBB::INPUT_FLOAT);

    if ($mybb->get_input('inactive', MyBB::INPUT_INT)) {
        $inactiveCheckedElement = ' checked="checked"';

        $whereClauses[] = "active='0'";
    } else {
        $inactiveCheckedElement = '';

        $whereClauses[] = "active='1'";
    }

    $buttons = $buttonDeactivate = '';

    if (!$inactiveCheckedElement || !empty($userGroupPermissions['ougc_invite_system_candelete']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmoddelete'])) {
        if (!$inactiveCheckedElement) {
            $buttonDeactivate = eval(getTemplate('content_button_deactivate'));
        }

        if (!empty($userGroupPermissions['ougc_invite_system_candelete']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmoddelete'])) {
            $buttonDelete = eval(getTemplate('content_button_delete'));
        }

        $buttons = eval(getTemplate('content_buttons'));
    }

    $currentUserName = $filterUserName = '';

    if ($isModeratorControlPanel) {
        if ($currentUserID) {
            $userName = htmlspecialchars_uni($userData['username']);

            $userName = format_name($userName, $userData['usergroup'], $userData['additionalgroups']);

            $userName = build_profile_link($userName, $userData['uid']);

            $banUrl = urlHandlerBuild(array_merge(['do' => 'toggleBan'], $urlParams));

            $banStatus = $lang->ougc_invite_system_modcp_current_ban;

            if (!empty($userData['ougc_invite_system_banned'])) {
                $banStatus = $lang->ougc_invite_system_modcp_current_liftban;
            }

            $currentUserName = eval(getTemplate('current'));
        }

        if ($mybb->get_input('username')) {
            $filterUserName = htmlspecialchars_uni($mybb->get_input('username'));
        }

        $filterUserName = eval(getTemplate('filter_username'));
    }

    $filterPanel = eval(getTemplate('filter'));

    $totalCodes = 0;

    if ($currentUserID) {
        $codesData = codeGet($whereClauses, ['COUNT(cid) AS totalCodes']);

        $totalCodes = $codesData['totalCodes'] ?? 0;

        $colspan = 9;

        if (newPointsIsInstalled()) {
            ++$colspan;
        }
    }

    if (!$inactiveCheckedElement) {
        $status = $lang->ougc_invite_system_usercp_form_active;
    } else {
        $status = $lang->ougc_invite_system_usercp_form_inactive;
    }

    $codesList = $pointsColumn = '';

    if ($totalCodes) {
        $perPage = (int)getSetting('perpage');

        if (!$perPage) {
            $perPage = 10;
        }

        $page = $mybb->get_input('page', MyBB::INPUT_INT);

        $pages = $totalCodes / $perPage;

        $pages = ceil($pages);

        if ($page > $pages || $page <= 0) {
            $page = 1;
        }

        if ($page) {
            $start = ($page - 1) * $perPage;
        } else {
            $start = 0;

            $page = 1;
        }

        $multipage = multipage($totalCodes, $perPage, $page, urlHandlerBuild($urlParams));

        $multipage = eval(getTemplate('content_multipage'));

        $query = $db->simple_select(
            'ougc_invite_system_codes',
            'cid, uid, muid, code, email, active, usergroup, additionalgroups, uses, stock, points, expire, dateline',
            implode(' AND ', $whereClauses),
            [
                'limit' => $perPage,
                'limit_start' => $start,
                'order_by' => 'dateline',
                'order_dir' => 'desc'
            ]
        );

        $trow = alt_trow(true);

        if (newPointsIsInstalled()) {
            $pointsColumn = eval(getTemplate('content_points_thead'));
        }

        while ($codeData = $db->fetch_array($query)) {
            $codeID = (int)$codeData['cid'];

            $codeUses = my_number_format($codeData['uses']);

            $codeStock = (int)$codeData['stock'];

            $codeString = htmlspecialchars_uni($codeData['code']);

            $codeUpperCase = my_strtoupper($codeString);

            $codeLowerCase = my_strtolower($codeString);

            if ($codeStock === CODE_STOCK_IS_INFINITE) {
                $codeStock = $lang->ougc_invite_system_usercp_stock_unlimited;
            } else {
                $codeStock = my_number_format($codeData['stock']);
            }

            if (!empty($codeData['email'])) {
                $codeMail = htmlspecialchars_uni($codeData['email']);
            } else {
                $codeMail = $lang->ougc_invite_system_usercp_noemail;
            }

            $pointsThead = '';

            if (newPointsIsInstalled()) {
                $codePoints = (float)$codeData['points'];

                $codePoints = newpoints_format_points($codePoints);

                $pointsThead = eval(getTemplate('content_points_column'));
            }

            if (!empty($codeData['expire'])) {
                $codeDateExpire = my_date('normal', $codeData['expire']);
            } else {
                $codeDateExpire = $lang->never;
            }

            if (!empty($codeData['dateline'])) {
                $codeDate = my_date('normal', $codeData['dateline']);
            } else {
                $codeDate = $lang->never;
            }

            foreach (['code', 'email', 'additionalgroups'] as $key) {
                $codeData[$key] = htmlspecialchars_uni($codeData[$key]);
            }

            $defaultGroup = $defaultAdditionalGroups = '';

            if (isset($groupsCache[$codeData['usergroup']])) {
                $defaultGroup = format_name(
                    htmlspecialchars_uni($groupsCache[$codeData['usergroup']]['title'] ?? ''),
                    $codeData['usergroup'] ?? 0
                );
            }

            if (!empty($codeData['additionalgroups'])) {
                $defaultAdditionalGroups = [];

                foreach (explode(',', $codeData['additionalgroups']) as $groupID) {
                    if (isset($groupsCache[$groupID])) {
                        $defaultAdditionalGroups[] = format_name(
                            htmlspecialchars_uni($groupsCache[$groupID]['title']),
                            $groupID
                        );
                    }
                }

                $defaultAdditionalGroups = implode($lang->comma, $defaultAdditionalGroups);
            } else {
                $defaultAdditionalGroups = '-';
            }

            $codesList .= eval(getTemplate('content_row'));

            $trow = alt_trow();
        }
    }

    $form = '';

    if (!empty($userGroupPermissions['ougc_invite_system_canadd']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodadd'])) {
        $display = false;

        $codeData = $codeGroup = $codeAdditionalGroups = $codeEmail = $codeStock = $codeExpiration = $codeMultiple = $codeUsers = $codePoints = '';

        if (!empty($userGroupPermissions['ougc_invite_system_cancustom']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodcustom'])) {
            $display = true;

            $length = min(max((int)getSetting('length'), 0), 50);

            $codeData = eval(getTemplate('form_code'));
        }

        if (!empty($userGroupPermissions['ougc_invite_system_canemail']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodemail'])) {
            $display = true;

            $codeEmail = eval(getTemplate('form_email'));
        }

        foreach (['usergroup', 'additionalgroups'] as $codeFieldKey) {
            if (empty($userGroupPermissions['ougc_invite_system_can' . $codeFieldKey]) && empty($moderatorGroupPermissions['ougc_invite_system_canmod' . $codeFieldKey])) {
                continue;
            }

            $groupIDs = array_map('intval', explode(',', getSetting('invitegroups')));

            if ($groupIDs) {
                $groupList = '';

                foreach ($groupsCache as $groupID => $groupData) {
                    $groupID = (int)$groupID;

                    if (
                        (!in_array(-1, $groupIDs) && !in_array($groupID, $groupIDs)) ||
                        !empty($groupData['cancp']) ||
                        !empty($groupData['issupermod']) ||
                        !empty($groupData['canmodcp']) ||
                        $groupID === 1
                    ) {
                        continue;
                    }

                    $groupTitle = htmlspecialchars_uni($groupData['title']);

                    $inputGroups = array_map('intval', $mybb->get_input($codeFieldKey, MyBB::INPUT_ARRAY));

                    $selectedElement = '';

                    if (!empty($inputGroups)) {
                        foreach ($inputGroups as $inputGroupID) {
                            if ($groupID == (int)$inputGroupID) {
                                $selectedElement = ' selected="selected"';
                            }
                        }
                    }

                    $groupList .= eval(getTemplate('form_usergroup_item'));
                }

                $title = $lang->{"ougc_invite_system_usercp_form_{$codeFieldKey}"};

                $multipleElement = '';

                if ($codeFieldKey === 'additionalgroups') {
                    $multipleElement = ' multiple="multiple" size="3"';
                }

                $display = true;

                if ($codeFieldKey === 'additionalgroups') {
                    $codeAdditionalGroups = eval(getTemplate('form_additionalgroups'));
                } else {
                    $codeGroup = eval(getTemplate('form_usergroup'));
                }
            }
        }

        if (!empty($userGroupPermissions['ougc_invite_system_canstock']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodstock'])) {
            $display = true;

            $unlimitedCheckBox = '';

            if (!empty($userGroupPermissions['ougc_invite_system_canunlimitedstock']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodunlimitedstock'])) {
                $checkedElementUnlimited = '';

                if ($mybb->get_input('unlimited', MyBB::INPUT_INT)) {
                    $checkedElementUnlimited = ' checked="checked"';
                }

                $unlimitedCheckBox = eval(getTemplate('form_stock_unlimited'));
            }

            $codeStock = eval(getTemplate('form_stock'));
        }

        if (!empty($userGroupPermissions['ougc_invite_system_canexpire']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodexpire'])) {
            $display = true;

            $endDateDate = $mybb->get_input('expire_date');

            $endDateTime = $mybb->get_input('expire_time');

            if ($endDateDate && $endDateTime) {
                $endDate = (array)explode('-', $endDateDate);

                $endTime = (array)explode(':', $endDateTime);

                $endDate = (int)gmmktime(
                    (int)$endTime[0],
                    (int)$endTime[1],
                    0,
                    (int)$endDate[1],
                    (int)$endDate[2],
                    (int)$endDate[0]
                );
            }

            $codeExpiration = eval(getTemplate('form_expire'));
        }

        if (!empty($userGroupPermissions['ougc_invite_system_canmultiple']) || !empty($moderatorGroupPermissions['ougc_invite_system_canmodmultiple'])) {
            $display = true;

            $codeMultiple = eval(getTemplate('form_multiple'));
        }

        if ($isModeratorControlPanel && !empty($moderatorGroupPermissions['ougc_invite_system_canusers'])) {
            $display = true;

            $userNames = implode(',', array_filter((array)explode(',', $mybb->get_input('usernames'))));

            $codeUsers = eval(getTemplate('form_users'));
        }

        if (!empty($moderatorGroupPermissions['ougc_invite_system_canpoints'])) {
            $display = true;

            $codePoints = eval(getTemplate('form_points'));
        }

        if ($display) {
            $form = eval(getTemplate('form'));
        }
    }

    if (!$codesList && $currentUserID) {
        $codesList = eval(getTemplate('content_empty'));
    }

    $content = '';

    if ($currentUserID) {
        $content = eval(getTemplate('content'));
    }

    $page = eval(getTemplate());

    output_page($page);

    exit;
}

function member_register_start(): bool
{
    if (getSetting('referralsystem') && getSetting('requirecode')) {
        global $mybb;

        $mybb->settings['usereferrals'] = 0;
    }

    return true;
}

function member_do_register_start(): bool
{
    return member_register_start();
}

function member_register_end(): bool
{
    global $mybb, $ougcInviteSystemRegister, $lang, $theme, $footer;

    languageLoad();

    $codeString = htmlspecialchars_uni($mybb->input['ougc_invite_system'] ?? $mybb->get_input('invite'));

    $required = getSetting('requirecode') ? 'true' : 'false';

    $ougcInviteSystemRegister = eval(getTemplate('register'));

    $length = min(max((int)getSetting('length'), 0), 50);

    $footer .= eval(getTemplate('register_validator'));

    return true;
}

function member_register_agreement(): bool
{
    global $mybb, $ougcInviteSystemRegisterAgreement;

    $codeString = htmlspecialchars_uni($mybb->input['ougc_invite_system'] ?? $mybb->get_input('invite'));

    $ougcInviteSystemRegisterAgreement = eval(getTemplate('register_agreement'));

    return true;
}

function member_register_coppa(): bool
{
    member_register_agreement();

    return true;
}

function xmlhttp(): bool
{
    global $mybb;

    if ($mybb->get_input('action') !== 'ougc_invite_system') {
        return false;
    }

    global $db, $lang;

    languageLoad();

    $charset = $lang->settings['charset'] ?? 'UTF-8';

    header("Content-type: application/json; charset={$charset}");

    $timeNow = TIME_NOW;

    $inviteCode = $db->escape_string(my_strtolower($mybb->input['ougc_invite_system'] ?? $mybb->get_input('invite')));

    $codeData = codeGet([
        "active='1'",
        "code='{$inviteCode}'",
        "(expire=0 OR expire>{$timeNow})"
    ], ['cid', 'uid', 'stock', 'uses', 'email']);

    if (empty($codeData['cid'])) {
        echo json_encode($lang->ougc_invite_system_register_error_invalid);
    } else {
        $userID = (int)$codeData['uid'];

        $userData = get_user($userID);

        $userGroupPermissions = userGetPermissions($userID);

        $codeStock = (int)$codeData['stock'];

        if (
            !empty($userData['ougc_invite_system_banned']) ||
            empty($userGroupPermissions['ougc_invite_system_canview']) ||
            $codeStock !== CODE_STOCK_IS_INFINITE && $codeData['uses'] >= $codeStock
        ) {
            echo json_encode($lang->ougc_invite_system_register_error_invalid);

            exit;
        }

        if (!empty($codeData['email']) && $codeData['email'] != $mybb->get_input('email')) {
            echo json_encode($lang->ougc_invite_system_register_error_invalidemail);

            exit;
        }

        echo json_encode('true');
    }

    exit;
}

function newpoints_logs_log_row(): bool
{
    global $log_data;

    if (!in_array($log_data['action'], [
        'ougc_invite_system_register',
        'ougc_invite_system'
    ])) {
        return false;
    }

    global $lang;
    global $log_action;

    languageLoad();

    if ($log_data['action'] === 'ougc_invite_system_register') {
        $log_action = $lang->ougc_invite_system_newpoints_logs_register;
    }

    if ($log_data['action'] === 'ougc_invite_system') {
        $log_action = $lang->ougc_invite_system_newpoints_logs_referral;
    }

    $codeID = (int)$log_data['log_primary_id'];

    $codeData = codeGet(["cid='{$codeID}'"], ['code', 'uid']);

    if (empty($codeData)) {
        return false;
    }

    global $log_primary, $log_secondary, $log_tertiary;

    if (!empty($codeData['code'])) {
        $log_primary = $codeData['code'];
    }

    if (!empty($log_data['log_secondary_id'])) {
        $userID = (int)$log_data['log_secondary_id'];
    } elseif (!empty($codeData['uid']) && $log_data['action'] === 'ougc_invite_system_register') {
        $userID = (int)$codeData['uid'];
    }

    if (!empty($userID)) {
        $userData = get_user($userID);

        if (!empty($userData)) {
            global $mybb;

            $log_secondary = $lang->sprintf(
                $lang->ougc_invite_system_newpoints_logs_secondary,
                $mybb->settings['bburl'],
                get_profile_link($userID),
                format_name(
                    htmlspecialchars_uni($userData['username']),
                    $userData['usergroup'],
                    $userData['displaygroup']
                )
            );
        }
    }

    return true;
}

function newpoints_logs_end(): bool
{
    global $lang;
    global $action_types;

    languageLoad();

    foreach ($action_types as $key => &$action_type) {
        if ($key === 'ougc_invite_system_register') {
            $action_type = $lang->ougc_invite_system_newpoints_logs_register;
        }

        if ($key === 'ougc_invite_system') {
            $action_type = $lang->ougc_invite_system_newpoints_logs_referral;
        }
    }

    return true;
}