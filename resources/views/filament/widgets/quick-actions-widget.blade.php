<div class="fi-wi-quick-actions">
    <div class="fi-wi-header">
        <h3 class="fi-wi-heading text-base font-semibold leading-6 text-gray-900 dark:text-white">
            Quick Actions
        </h3>
        <p class="fi-wi-description text-sm text-gray-500 dark:text-gray-400">
            Common tasks to get things done quickly
        </p>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4 mt-6">
        <!-- Create Event -->
        @can('create_events')
        <a href="{{ route('filament.admin.resources.events.create', ['tenant' => $tenant]) }}"
           class="group relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400 hover:shadow-md focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:hover:border-gray-500">
            <div>
                <span class="inline-flex rounded-lg p-3 bg-green-50 text-green-700 group-hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    Create Event
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Set up a new event with tickets and venue details
                </p>
            </div>
        </a>
        @endcan


        <!-- Create User - Only show to users with user creation permissions -->
        @can('create_users')
        <a href="{{ route('filament.admin.resources.users.create', ['tenant' => $tenant]) }}"
           class="group relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400 hover:shadow-md focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:hover:border-gray-500">
            <div>
                <span class="inline-flex rounded-lg p-3 bg-purple-50 text-purple-700 group-hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    Create User
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Add a new team member with access credentials
                </p>
            </div>
        </a>
        @endcan

        <!-- Create Discount -->
        @can('create_discounts')
        <a href="{{ route('filament.admin.resources.discounts.create', ['tenant' => $tenant]) }}"
           class="group relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm hover:border-gray-400 hover:shadow-md focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:hover:border-gray-500">
            <div>
                <span class="inline-flex rounded-lg p-3 bg-yellow-50 text-yellow-700 group-hover:bg-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008V13.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    <span class="absolute inset-0" aria-hidden="true"></span>
                    Create Discount
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Set up promotional codes and special offers
                </p>
            </div>
        </a>
        @endcan
    </div>
</div>
