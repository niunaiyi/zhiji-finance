<?php

namespace App\Ship\Parents\Actions;

use Apiato\Core\Actions\Action as AbstractAction;

use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class Action extends AbstractAction
{
    /**
     * @param string[] $allowedRoles
     * @throws HttpException
     */
    public function checkRole(array $allowedRoles): void
    {
        $currentRole = app('current.role');

        if (!in_array($currentRole, $allowedRoles)) {
            throw new HttpException(403, '权限不足：该操作仅限 ' . implode(', ', $allowedRoles) . ' 角色。');
        }
    }
}
