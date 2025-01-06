<?php

/***************************************************************************
 *
 *    ougc Invite System plugin (/inc/plugins/ougc_invite_system/hooks/shared.php)
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

namespace ougc\InviteSystem\Hooks\Shared;

use MyLanguage;
use UserDataHandler;

use function Newpoints\Core\log_add;
use function Newpoints\Core\points_add_simple;
use function ougc\InviteSystem\Core\codeDelete;
use function ougc\InviteSystem\Core\codeGet;
use function ougc\InviteSystem\Core\codeUpdate;
use function ougc\InviteSystem\Core\getSetting;
use function ougc\InviteSystem\Core\logDelete;
use function ougc\InviteSystem\Core\logInsert;
use function ougc\InviteSystem\Core\newPointsIsInstalled;
use function ougc\InviteSystem\Core\userGetPermissions;
use function ougc\InviteSystem\Core\languageLoad;
use function ougc\InviteSystem\Core\alertSend;

use const Newpoints\Core\LOGGING_TYPE_INCOME;
use const ougc\InviteSystem\Core\CODE_STOCK_IS_INFINITE;

function datahandler_user_validate(UserDataHandler &$dataHandler): UserDataHandler
{
    global $mybb, $lang, $db;

    languageLoad();

    if ($dataHandler->method !== 'insert') {
        return $dataHandler;
    }

    $codeInput = $mybb->input['ougc_invite_system'] ?? $mybb->get_input('invite');

    if (!$codeInput) {
        if (getSetting('requirecode')) {
            $dataHandler->set_error($lang->ougc_invite_system_register_error_required);
        }

        return $dataHandler;
    }

    $timeNow = TIME_NOW;

    $codeInput = $db->escape_string(my_strtolower($codeInput));

    $codeData = codeGet(["active='1'", "code='{$codeInput}'", "(expire=0 OR expire>{$timeNow})"]);

    if (empty($codeData)) {
        $dataHandler->set_error($lang->ougc_invite_system_register_error_invalid);
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
            $dataHandler->set_error($lang->ougc_invite_system_register_error_invalid);

            return $dataHandler;
        }

        if (!empty($codeData['email']) && $codeData['email'] !== $mybb->get_input('email')) {
            $dataHandler->set_error($lang->ougc_invite_system_register_error_invalidemail);
        }
    }

    if (!empty($dataHandler->errors)) {
        my_setcookie('regattempts', isset($mybb->cookies['regattempts']) ? $mybb->cookies['regattempts'] + 1 : 1);

        if ($mybb->cookies['regattempts'] > getSetting('regattempts')) {
            $timeNow = isset($mybb->cookies['regattempts_time']) ? (int)$mybb->cookies['regattempts_time'] : 0;

            $waitingMinutes = getSetting('regattempts_minutes') - round((TIME_NOW - $timeNow) / 60);

            if ($waitingMinutes > 0) {
                error($lang->sprintf($lang->ougc_invite_system_register_error_maxregattempts, $waitingMinutes));
            } else {
                my_setcookie('regattempts', 0);
            }
        }

        my_setcookie('regattempts_time', TIME_NOW);
    } elseif (!empty($codeData)) {
        global $ougcInviteSystemCodeData;

        $ougcInviteSystemCodeData = $codeData;
    }

    return $dataHandler;
}

function datahandler_user_insert20(UserDataHandler &$dataHandler): UserDataHandler
{
    global $ougcInviteSystemCodeData;

    if (empty($ougcInviteSystemCodeData)) {
        return $dataHandler;
    }

    global $db;

    $dataHandler->user_insert_data['usergroup'] = (int)$ougcInviteSystemCodeData['usergroup'];

    $dataHandler->user_insert_data['additionalgroups'] = $db->escape_string(
        $ougcInviteSystemCodeData['additionalgroups']
    );

    if (getSetting('referralsystem')) {
        $dataHandler->user_insert_data['referrer'] = (int)$ougcInviteSystemCodeData['uid'];
    }

    if (!empty($ougcInviteSystemCodeData['points']) && newPointsIsInstalled()) {
        if (isset($dataHandler->user_insert_data['newpoints'])) {
            $ougcInviteSystemCodeData['points'] += $dataHandler->user_insert_data['newpoints'];
        }

        $dataHandler->user_insert_data['newpoints'] = (float)$ougcInviteSystemCodeData['points'];
    }

    return $dataHandler;
}

function datahandler_user_insert_end(UserDataHandler &$dataHandler): UserDataHandler
{
    global $ougcInviteSystemCodeData;

    if (empty($ougcInviteSystemCodeData)) {
        return $dataHandler;
    }

    $userID = (int)$dataHandler->uid;

    $codeID = (int)$ougcInviteSystemCodeData['cid'];

    $referrerUserID = (int)$ougcInviteSystemCodeData['uid'];

    if (isset($dataHandler->user_insert_data['newpoints'])) {
        log_add(
            'ougc_invite_system_register',
            '',
            $dataHandler->user_insert_data['username'] ?? '',
            $userID,
            (float)$dataHandler->user_insert_data['newpoints'],
            $codeID,
            $referrerUserID,
            0,
            LOGGING_TYPE_INCOME
        );
    }

    global $mybb, $lang;

    $updateData = [
        'uses' => ++$ougcInviteSystemCodeData['uses']
    ];

    $codeStock = (int)$ougcInviteSystemCodeData['stock'];

    if ($codeStock !== CODE_STOCK_IS_INFINITE && $ougcInviteSystemCodeData['uses'] + 1 >= $codeStock) {
        $updateData['active'] = 0;
    }

    codeUpdate($updateData, $codeID);

    logInsert(['uid' => $userID, 'cid' => $codeID]);

    $referrerData = get_user($referrerUserID);

    languageLoad();

    if (my_strpos(',' . getSetting('notifications') . ',', 'pm') !== false) {
        if ($dataHandler->user_insert_data['language'] &&
            $lang->language_exists($dataHandler->user_insert_data['language'])) {
            $userLanguage = $dataHandler->user_insert_data['language'];
        } elseif ($mybb->settings['bblanguage']) {
            $userLanguage = $mybb->settings['bblanguage'];
        } else {
            $userLanguage = 'english';
        }

        if ($userLanguage === $mybb->settings['bblanguage']) {
            $messageSubject = $lang->ougc_invite_system_pm_subject;

            $messageBody = $lang->ougc_invite_system_pm_content;
        } else {
            $userLanguageObject = new MyLanguage();

            $userLanguageObject->set_path(MYBB_ROOT . 'inc/languages');

            $userLanguageObject->set_language($userLanguage);

            languageLoad($userLanguageObject);

            $messageSubject = $userLanguageObject->ougc_invite_system_pm_subject;

            $messageBody = $userLanguageObject->ougc_invite_system_pm_content;
        }

        send_pm([
            'subject' => $messageSubject,
            'message' => $lang->sprintf(
                $messageBody,
                $referrerData['username'],
                $dataHandler->user_insert_data['username'],
                $mybb->settings['bburl'],
                get_profile_link($userID),
                $mybb->settings['bbname']
            ),
            'touid' => $referrerUserID
        ], -1, true);
    }

    if (my_strpos(',' . getSetting('notifications') . ',', 'myalerts') !== false) {
        alertSend($userID, $referrerUserID);
    }

    if (
        !empty($ougcInviteSystemCodeData['points']) &&
        newPointsIsInstalled() &&
        getSetting('referral_points') &&
        !empty($referrerData)
    ) {
        $percentage = min(max((int)getSetting('referral_points'), 0), 100);

        $codePoints = ($percentage / 100) * $ougcInviteSystemCodeData['points'];

        $userGroupPermissions = userGetPermissions($referrerUserID);

        $codePoints = $codePoints * $userGroupPermissions['newpoints_rate_addition'];

        if ($codePoints) {
            points_add_simple($referrerUserID, $codePoints);

            log_add(
                'ougc_invite_system',
                '',
                $referrerData['username'],
                $referrerUserID,
                $codePoints,
                $codeID,
                $userID,
                0,
                LOGGING_TYPE_INCOME
            );
        }
    }

    my_unsetcookie('regattempts');

    my_unsetcookie('regattempts_time');

    return $dataHandler;
}

function datahandler_user_delete_end(UserDataHandler &$dataHandler): UserDataHandler
{
    if ($dataHandler->delete_uids) {
        codeDelete(["uid IN({$dataHandler->delete_uids})"]);

        logDelete(["uid IN({$dataHandler->delete_uids})"]);
    }

    return $dataHandler;
}