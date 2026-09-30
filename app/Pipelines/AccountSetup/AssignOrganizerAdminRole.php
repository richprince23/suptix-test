<?php

namespace App\Pipelines\AccountSetup;

use App\Models\Organizer;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AssignOrganizerAdminRole
{
    /**
     * Handle the pipeline step to assign Organizer Admin role.
     *
     * @param  array  $data
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(array $data, Closure $next)
    {
        $organizer = $data['organizer'] ?? null;
        $user = $data['user'] ?? null;

        if (!$organizer instanceof Organizer || !$user instanceof User) {
            Log::warning('AssignOrganizerAdminRole pipeline: Missing organizer or user', [
                'has_organizer' => !is_null($organizer),
                'has_user' => !is_null($user),
            ]);

            return $next($data);
        }

        try {
            // Ensure the role exists
            $role = Role::firstOrCreate(['name' => 'Organizer Admin']);

            // Get all users associated with this organizer
            $allOrganizerUsers = $organizer->users;

            // Check if there's already an organizer admin
            $hasOrganizerAdmin = $allOrganizerUsers->filter(
                fn($u) => $u->hasRole('Organizer Admin')
            )->count() > 0;

            // Only assign if this is the first user OR no admin exists
            if ($allOrganizerUsers->count() === 1 || !$hasOrganizerAdmin) {
                if (!$user->hasRole('Organizer Admin')) {
                    $user->assignRole('Organizer Admin');

                    Log::info('Organizer Admin role assigned via pipeline', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'organizer_id' => $organizer->id,
                        'reason' => $allOrganizerUsers->count() === 1 ? 'first_user' : 'no_existing_admin',
                    ]);

                    $data['role_assigned'] = true;
                } else {
                    Log::info('User already has Organizer Admin role', [
                        'user_id' => $user->id,
                        'organizer_id' => $organizer->id,
                    ]);

                    $data['role_assigned'] = false;
                    $data['role_already_exists'] = true;
                }
            } else {
                Log::info('Role assignment skipped - organization already has admin', [
                    'organizer_id' => $organizer->id,
                    'user_count' => $allOrganizerUsers->count(),
                    'has_admin' => $hasOrganizerAdmin,
                ]);

                $data['role_assigned'] = false;
                $data['role_skip_reason'] = 'organization_has_admin';
            }

        } catch (\Exception $e) {
            Log::error('Failed to assign Organizer Admin role in pipeline', [
                'organizer_id' => $organizer->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $data['role_assignment_error'] = $e->getMessage();
        }

        return $next($data);
    }
}
