<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(empty($tableNames), new Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.'));
        throw_if($teams && empty($columnNames['team_foreign_key'] ?? null), new Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.'));

        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            // $table->engine('InnoDB');
            $table->bigIncrements('id'); // permission id
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            // $table->engine('InnoDB');
            $table->bigIncrements('id'); // role id
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }

        });

        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);

            $table->foreign($pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }@push('css')
    <style>
   
html[data-bs-theme="light"] #sidebar-dropdown {
    background-color: #000; /* Background untuk menu dropdown di light mode */
}

html[data-bs-theme="light"] #sidebar-dropdown a.menu-link {
    color: #fff; /* Warna teks default di light mode (abu-abu gelap) */
}

/* Hover effect untuk menu-item di dropdown pada light mode */
html[data-bs-theme="light"] #sidebar-dropdown a.menu-link:hover {
    background-color: #343a40; /* Background menjadi hitam penuh saat hover */
    color: #c3c0c0; /* Ubah teks menjadi putih saat hover */
}
</style>
@endpush
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
   data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
   data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

   <!-- Sidebar Logo -->
   <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
      <a href="{{route('dashboard')}}">
         <div class="d-flex align-items-center app-sidebar-logo-default">
            <img alt="Logo" src={{ asset($logoUrl) }} class="h-35px app-sidebar-logo-default" />
            <p class="mb-0 ms-3 fs-4 fw-bold text-white app-sidebar-logo-default">{{ $webName }}</p>
         </div>
         <img alt="Logo" src={{ asset($logoUrl) }} class="h-30px app-sidebar-logo-minimize" />
      </a>

      <!-- Sidebar Toggle Button -->
      <div id="kt_app_sidebar_toggle"
         class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
         data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
         data-kt-toggle-name="app-sidebar-minimize">
         <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
            <span class="path1"></span>
            <span class="path2"></span>
         </i>
      </div>
   </div>

   <!-- Sidebar Menu -->
   <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
      <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
         <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
            data-kt-scroll-activate="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">

            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="kt_app_sidebar_menu"
               data-kt-menu="true" data-kt-menu-expand="false">

               <!-- Loop through navigation items -->
               @foreach (App\Helpers\SidebarHelper::getNavMenu() as $navItem)
                  @if (isset($navItem['menu']) && count($navItem['menu']) > 0)
                     <div class="menu-item pt-5">
                        <div class="menu-content">
                           <span class="menu-heading fw-bold text-uppercase fs-7">{{ $navItem['label'] }}</span>
                        </div>
                     </div>

                     <!-- Loop through submenus -->
                     @foreach ($navItem['menu'] as $menu)
                        @if (isset($menu['drop-down']) && count($menu['drop-down']) > 0)
                           <div data-kt-menu-trigger="hover" class="menu-item menu-accordion">
                              <a href="#" class="menu-link">
                                 <span class="menu-icon">
                                    <x-icon-component :name="$menu['icon']" />
                                 </span>
                                 <span class="menu-title">{{ $menu['label'] }}</span>
                                 <span class="menu-arrow" style="transform: rotate(-90deg)"></span>
                              </a>
                              <div class="menu-sub menu-sub-dropdown p-3 w-200px" id="sidebar-dropdown">
                                 @foreach ($menu['drop-down'] as $dropdown)
                                    <div class="menu-item">
                                       <a class="menu-link px-3 py-2 w-100 {{ Request::url() === url($dropdown['link']) ? 'active' : '' }}"
                                          href="{{ url($dropdown['link']) }}">
                                          <span class="menu-title">{{ $dropdown['label'] }}</span>
                                       </a>
                                    </div>
                                 @endforeach
                              </div>
                           </div>
                        @else
                           <div class="menu-item">
                              <a class="menu-link text-white {{ Request::url() === url($menu['link']) ? 'active' : '' }}"
                                 href="{{ url($menu['link']) }}">
                                 <span class="menu-icon">
                                    <x-icon-component :name="$menu['icon']" />
                                 </span>
                                 <span class="menu-title">{{ $menu['label'] }}</span>
                              </a>
                           </div>
                        @endif
                     @endforeach
                  @else
                     <div class="menu-item">
                        <a class="menu-link text-white {{ Request::url() === url($navItem['link']) ? 'active' : '' }}"
                           href="{{ url($navItem['link']) }}">
                           <span class="menu-icon">
                              <x-icon-component :name="$navItem['icon']" />
                           </span>
                           <span class="menu-title">{{ $navItem['label'] }}</span>
                        </a>
                     </div>
                  @endif
               @endforeach

            </div>
         </div>
      </div>
   </div>

</div>


        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
