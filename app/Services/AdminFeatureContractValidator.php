<?php

declare(strict_types=1);

/**
 * Validates the Admin Feature Register against the permission vocabulary and
 * dependency graph. Pure/static: it never connects to the database.
 */
final class AdminFeatureContractValidator
{
    public function validate(): array
    {
        $features = AdminFeatureRegistry::all();
        $knownIds = array_fill_keys(array_keys($features), true);
        $permissionNames = array_fill_keys(AdminPermissionCatalog::allNames(), true);
        $errors = [];
        $checkedDependencies = 0;

        foreach ($features as $featureId => $feature) {
            $permission = $feature['permission'] ?? null;
            if (!is_string($permission) || !isset($permissionNames[$permission])) {
                $errors[] = [
                    'feature' => $featureId,
                    'code' => 'permission_missing_from_catalog',
                    'value' => $permission,
                ];
            }

            foreach (($feature['dependencies'] ?? []) as $dependency) {
                $checkedDependencies++;

                if (!is_string($dependency) || !isset($knownIds[$dependency])) {
                    $errors[] = [
                        'feature' => $featureId,
                        'code' => 'unknown_dependency',
                        'value' => $dependency,
                    ];
                }
            }

            if (
                !isset($feature['title'], $feature['area']) ||
                !is_string($feature['title']) ||
                trim($feature['title']) === '' ||
                !is_string($feature['area']) ||
                trim($feature['area']) === ''
            ) {
                $errors[] = [
                    'feature' => $featureId,
                    'code' => 'required_metadata_missing',
                    'value' => null,
                ];
            }
        }

        $cycles = $this->dependencyCycles($features);
        foreach ($cycles as $cycle) {
            $errors[] = [
                'feature' => $cycle[0] ?? 'unknown',
                'code' => 'dependency_cycle',
                'value' => $cycle,
            ];
        }

        return [
            'valid' => $errors === [],
            'feature_count' => count($features),
            'permission_count' => count($permissionNames),
            'dependency_edges_checked' => $checkedDependencies,
            'errors' => $errors,
            'database_accessed' => false,
        ];
    }

    private function dependencyCycles(array $features): array
    {
        $visited = [];
        $active = [];
        $cycles = [];

        $visit = function (string $id, array $path) use (
            &$visit,
            &$visited,
            &$active,
            &$cycles,
            $features
        ): void {
            if (isset($active[$id])) {
                $start = array_search($id, $path, true);
                $cycle = $start === false
                    ? [$id]
                    : array_slice($path, $start);
                $cycle[] = $id;
                $signature = implode('>', $cycle);
                $cycles[$signature] = $cycle;
                return;
            }

            if (isset($visited[$id]) || !isset($features[$id])) {
                return;
            }

            $active[$id] = true;
            $path[] = $id;

            foreach (($features[$id]['dependencies'] ?? []) as $dependency) {
                if (is_string($dependency)) {
                    $visit($dependency, $path);
                }
            }

            unset($active[$id]);
            $visited[$id] = true;
        };

        foreach (array_keys($features) as $id) {
            $visit($id, []);
        }

        return array_values($cycles);
    }
}
