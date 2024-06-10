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

namespace Ampache\Module\Application\Admin\License;

use Ampache\Config\ConfigContainerInterface;
use Ampache\MockeryTestCase;
use Ampache\Module\Application\Exception\AccessDeniedException;
use Ampache\Module\Authorization\AccessLevelEnum;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Util\UiInterface;
use Ampache\Repository\LicenseRepositoryInterface;
use Ampache\Repository\Model\License;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;

class ShowCreateActionTest extends MockeryTestCase
{
    private MockInterface&UiInterface $ui;

    private LicenseRepositoryInterface&MockObject $licenseRepository;

    private ConfigContainerInterface&MockObject $configContainer;

    private ShowCreateAction $subject;

    protected function setUp(): void
    {
        $this->ui                = $this->mock(UiInterface::class);
        $this->licenseRepository = $this->createMock(LicenseRepositoryInterface::class);
        $this->configContainer   = $this->createMock(ConfigContainerInterface::class);

        $this->subject = new ShowCreateAction(
            $this->ui,
            $this->licenseRepository,
            $this->configContainer,
        );
    }

    public function testRunThrowsExceptionIfAccessIsDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $request    = $this->mock(ServerRequestInterface::class);
        $gatekeeper = $this->mock(GuiGatekeeperInterface::class);

        $gatekeeper->shouldReceive('mayAccess')
            ->with(AccessLevelEnum::TYPE_INTERFACE, AccessLevelEnum::LEVEL_MANAGER)
            ->once()
            ->andReturnFalse();

        $this->subject->run($request, $gatekeeper);
    }

    public function testRunRendersAndReturnsNull(): void
    {
        $request    = $this->mock(ServerRequestInterface::class);
        $gatekeeper = $this->mock(GuiGatekeeperInterface::class);
        $license    = $this->createMock(License::class);

        $webPath = 'some-web-path';

        $gatekeeper->shouldReceive('mayAccess')
            ->with(AccessLevelEnum::TYPE_INTERFACE, AccessLevelEnum::LEVEL_MANAGER)
            ->once()
            ->andReturnTrue();

        $this->configContainer->expects(static::once())
            ->method('getWebPath')
            ->willReturn($webPath);

        $this->licenseRepository->expects(static::once())
            ->method('prototype')
            ->willReturn($license);

        $this->ui->shouldReceive('showHeader')
            ->withNoArgs()
            ->once();
        $this->ui->shouldReceive('showBoxTop')
            ->with('Create license')
            ->once();
        $this->ui->shouldReceive('show')
            ->with(
                'show_edit_license.inc.php',
                [
                    'license' => $license,
                    'webPath' => $webPath,
                ]
            )
            ->once();
        $this->ui->shouldReceive('showBoxBottom')
            ->once();
        $this->ui->shouldReceive('showQueryStats')
            ->withNoArgs()
            ->once();
        $this->ui->shouldReceive('showFooter')
            ->withNoArgs()
            ->once();

        $this->assertNull(
            $this->subject->run($request, $gatekeeper)
        );
    }
}
