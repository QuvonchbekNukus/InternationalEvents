<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function applyOwnScope(
        Request $request,
        Builder $query,
        string $viewPermission,
        string $viewOwnPermission,
        callable $scopeCallback
    ): void {
        $user = $request->user();

        if ($user?->can($viewPermission)) {
            return;
        }

        abort_unless($user?->can($viewOwnPermission), 403);

        $scopeCallback($query, $user);
    }

    protected function authorizeOwnedRecord(
        Request $request,
        Model $record,
        string $editPermission,
        string $editOwnPermission,
        callable $ownershipCallback
    ): void {
        $user = $request->user();

        if ($user?->can($editPermission)) {
            return;
        }

        abort_unless($user?->can($editOwnPermission) && $ownershipCallback($record, $user), 403);
    }
}
