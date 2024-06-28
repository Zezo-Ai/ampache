<?php

declare(strict_types=1);

/**
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright Ampache.org, 2001-2024
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

namespace Ampache\Module\Application\Admin\User;

use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Renders the confirmation dialogue for avatar deletion
 */
final class ShowDeleteAvatarAction extends AbstractUserAction
{
    use UserAdminApplicationTrait;

    public const REQUEST_KEY = 'show_delete_avatar';

    private UiInterface $ui;

    public function __construct(
        UiInterface $ui
    ) {
        $this->ui = $ui;
    }

    protected function handle(ServerRequestInterface $request): ?ResponseInterface
    {
        return $this->showGenericUserConfirmation(
            $request,
            function (int $userId): void {
                $this->ui->showConfirmation(
                    T_('Are You Sure?'),
                    T_('This Avatar will be deleted'),
                    sprintf(
                        'admin/users.php?action=%s&user_id=%d',
                        DeleteAvatarAction::REQUEST_KEY,
                        $userId
                    ),
                    1,
                    'delete_avatar'
                );
            }
        );
    }
}
