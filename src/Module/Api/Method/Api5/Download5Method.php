<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2022 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Api\Method\Api5;

use Ampache\Repository\Model\Podcast_Episode;
use Ampache\Repository\Model\Random;
use Ampache\Repository\Model\Song;
use Ampache\Repository\Model\User;
use Ampache\Module\Api\Api5;
use Ampache\Module\System\Session;

/**
 * Class Download5Method
 */
final class Download5Method
{
    public const ACTION = 'download';

    /**
     * download
     * MINIMUM_API_VERSION=400001
     *
     * Downloads a given media file. set format=raw to download the full file
     *
     * @param array $input
     * id     = (string) $song_id| $podcast_episode_id
     * type   = (string) 'song', 'podcast_episode', 'search', 'playlist'
     * format = (string) 'mp3', 'ogg', etc //optional
     * @return boolean
     */
    public static function download(array $input): bool
    {
        if (!Api5::check_parameter($input, array('id', 'type'), self::ACTION)) {
            http_response_code(400);

            return false;
        }
        $object_id = (int) $input['id'];
        $type      = (string) $input['type'];
        $format    = $input['format'];
        $original  = $format && $format != 'raw';
        $user      = User::get_from_username(Session::username($input['auth']));

        $params = '&client=api&action=download&cache=1';
        if ($format && in_array($type, array('song', 'search', 'playlist'))) {
            $params .= '&format=' . $format;
        }
        $url = '';
        if ($type == 'song') {
            $media = new Song($object_id);
            $url   = $media->play_url($params, 'api', function_exists('curl_version'), $user->id);
        }
        if ($type == 'podcast_episode' || $type == 'podcast') {
            $media = new Podcast_Episode($object_id);
            $url   = $media->play_url($params, 'api', function_exists('curl_version'), $user->id);
        }
        if ($type == 'search' || $type == 'playlist') {
            $song_id = Random::get_single_song($type, $user, (int)$_REQUEST['random_id']);
            $media   = new Song($song_id);
            $url     = $media->play_url($params, 'api', function_exists('curl_version'), $user->id);
        }
        if (!empty($url)) {
            Session::extend($input['auth']);
            header('Location: ' . str_replace(':443/play', '/play', $url));

            return true;
        }

        // download not found
        http_response_code(404);

        return false;
    }
}
