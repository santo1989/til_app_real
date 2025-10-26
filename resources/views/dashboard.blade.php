<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php $role = auth()->user()->role ?? 'guest'; @endphp
            @switch($role)
                @case('hr_admin')
                    @include('appraisal.hr_admin.dashboard')
                @break

                @case('line_manager')
                    @include('appraisal.line_manager.dashboard')
                @break

                @case('dept_head')
                    @include('appraisal.dept_head.dashboard')
                @break

                @case('board')
                    @include('appraisal.board.dashboard')
                @break

                @case('super_admin')
                    @include('appraisal.super_admin.dashboard')
                @break

                @default
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            Welcome, {{ auth()->user()->name }}. Use the menu to navigate the system.
                        </div>
                    </div>
            @endswitch
        </div>
    </div>
</x-app-layout>
