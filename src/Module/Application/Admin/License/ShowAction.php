<?php

declare(strict_types=0);

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

namespace Ampache\Module\Application\Admin\License;

use Ampache\Module\Authorization\AccessTypeEnum;
use Ampache\Repository\LicenseRepositoryInterface;
use Ampache\Repository\Model\ModelFactoryInterface;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Application\Exception\AccessDeniedException;
use Ampache\Module\Authorization\AccessLevelEnum;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ShowAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'show';

    private UiInterface $ui;

    private ModelFactoryInterface $modelFactory;
    private LicenseRepositoryInterface $licenseRepository;

    public function __construct(
        UiInterface $ui,
        ModelFactoryInterface $modelFactory,
        LicenseRepositoryInterface $licenseRepository
    ) {
        $this->ui                = $ui;
        $this->modelFactory      = $modelFactory;
        $this->licenseRepository = $licenseRepository;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        if ($gatekeeper->mayAccess(AccessTypeEnum::INTERFACE, AccessLevelEnum::MANAGER) === false) {
            throw new AccessDeniedException();
        }

        $this->ui->showHeader();

        $browse = $this->modelFactory->createBrowse();
        $browse->set_type('license');
        $browse->set_simple_browse(true);
        $browse->set_sort('order');
        $browse->show_objects(
            array_keys(
                iterator_to_array(
                    $this->licenseRepository->getList()
                )
            )
        );

        $browse->store();

        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}
