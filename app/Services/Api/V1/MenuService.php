<?php

namespace App\Services\Api\V1;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Builds the sidebar tree the way loginController/MenuMiddleware do (rights = individual OR group, menu must be
 * enabled for the institute / client, MASTER menus excluded) without MySQL-only FIND_IN_SET/GROUP_CONCAT.
 */
class MenuService
{
    /** @return array [menu id => ['view','add','edit','delete' => bool]]; individual rights win over group rights */
    public function permissions(array $principal): array
    {
        $own = (int) $principal['sub_institute_id']; // rights tables are keyed on the user's own institute (0 for client admins)
        $group = DB::table('tblgroupwise_rights')->where('profile_id', $principal['user_profile_id'])
            ->where('sub_institute_id', $own)->get()->keyBy('menu_id');
        $individual = DB::table('tblindividual_rights')->where('user_id', $principal['id'])
            ->where('sub_institute_id', $own)->get()->keyBy('menu_id');

        $out = [];
        foreach ($group->keys()->merge($individual->keys())->unique() as $menuId) {
            $r = $individual[$menuId] ?? $group[$menuId];
            $out[(int) $menuId] = [
                'view' => (int) ($r->can_view ?? 0) === 1,
                'add' => (int) ($r->can_add ?? 0) === 1,
                'edit' => (int) ($r->can_edit ?? 0) === 1,
                'delete' => (int) ($r->can_delete ?? 0) === 1,
            ];
        }

        return $out;
    }

    public function tree(array $principal, array $context): array
    {
        $perms = $this->permissions($principal);
        $clientAdmin = $context['is_client_admin'];
        $scopeId = $clientAdmin ? (string) $context['client_id'] : (string) $context['institute_id'];
        $scopeCol = $clientAdmin ? 'client_id' : 'sub_institute_id';

        $rows = DB::table('tblmenumaster')->where('status', 1)->whereIn('level', [1, 2, 3])
            ->whereIn('id', array_keys($perms))->orderBy('sort_order')->get()
            ->filter(function ($m) use ($scopeCol, $scopeId) {
                $scope = array_map('trim', explode(',', (string) $m->{$scopeCol}));

                return in_array($scopeId, $scope, true) && ($m->menu_type ?? null) !== 'MASTER';
            });

        $byParent = $rows->groupBy(fn ($m) => (int) $m->parent_menu_id);
        $build = function ($parentId, $level) use (&$build, $byParent, $perms) {
            return ($byParent[$parentId] ?? collect())->where('level', $level)->values()->map(function ($m) use (&$build, $perms, $level) {
                return [
                    'id' => (int) $m->id,
                    'name' => $m->name,
                    'link' => $m->link,
                    'path' => $this->pathFor($m->link),
                    'icon' => $m->icon,
                    'level' => (int) $m->level,
                    'sort_order' => (int) $m->sort_order,
                    'permissions' => $perms[(int) $m->id] ?? ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                    'children' => $level < 3 ? $build((int) $m->id, $level + 1) : [],
                ];
            })->all();
        };

        return $build(0, 1);
    }

    /** Blade URL path for a menu link (links are Laravel route names), or null for headings / unknown routes. */
    private function pathFor(?string $link): ?string
    {
        if (! $link || ! Route::has($link)) {
            return null;
        }
        try {
            return '/'.ltrim(route($link, [], false), '/');
        } catch (\Throwable $e) {
            return null; // route needs parameters
        }
    }
}
