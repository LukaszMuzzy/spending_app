<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const setupActive = computed(() => {
    return [
        'shops.index',
        'shopping_types.index',
        'shopping_groups.index',
        'payment_methods.index',
        'bank.index',
        'imports.index',
    ].some((name) => route().current(name));
});
</script>

<template>
    <div class="min-h-screen bg-surface">
        <nav class="border-b border-border bg-card/90 backdrop-blur">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex min-w-0 items-center gap-6">
                        <Link :href="route('dashboard')" class="flex shrink-0 items-center gap-2">
                            <ApplicationLogo class="block h-8 w-auto fill-current text-accent" />
                            <span class="hidden text-sm font-semibold tracking-tight text-ink sm:inline">Spendings</span>
                        </Link>

                        <div class="hidden items-center gap-1 md:flex">
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Overview
                            </NavLink>
                            <NavLink :href="route('receipts.index')" :active="route().current('receipts.*')">
                                Spending
                            </NavLink>
                            <NavLink :href="route('dd_payments.index')" :active="route().current('dd_payments.*')">
                                Bills
                            </NavLink>
                            <NavLink :href="route('plan.index')" :active="route().current('plan.*')">
                                Plan
                            </NavLink>

                            <div class="relative ml-1">
                                <Dropdown align="left" width="48">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium transition"
                                            :class="setupActive ? 'bg-accent-soft text-accent' : 'text-ink-muted hover:bg-surface hover:text-ink'"
                                        >
                                            Setup
                                            <svg class="ml-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('shops.index')">Shops</DropdownLink>
                                        <DropdownLink :href="route('shopping_types.index')">Categories</DropdownLink>
                                        <DropdownLink :href="route('shopping_groups.index')">Groups</DropdownLink>
                                        <DropdownLink :href="route('payment_methods.index')">Payment methods</DropdownLink>
                                        <DropdownLink :href="route('imports.index')">CSV import</DropdownLink>
                                        <DropdownLink :href="route('bank.index')">Bank connection</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>

                    <div class="hidden items-center md:flex">
                        <div class="relative ml-3">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-lg border border-transparent bg-card px-3 py-2 text-sm font-medium text-ink-muted transition hover:text-ink focus:outline-none"
                                        >
                                            {{ $page.props.auth.user.name }}
                                            <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Log out</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="-mr-2 flex items-center md:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center rounded-md p-2 text-ink-muted transition hover:bg-surface hover:text-ink focus:outline-none"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex': !showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex': showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="md:hidden">
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                        Overview
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('receipts.index')" :active="route().current('receipts.*')">
                        Spending
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('dd_payments.index')" :active="route().current('dd_payments.*')">
                        Bills
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('plan.index')" :active="route().current('plan.*')">
                        Plan
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('shops.index')" :active="route().current('shops.index')">
                        Shops
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('shopping_types.index')" :active="route().current('shopping_types.index')">
                        Categories
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('shopping_groups.index')" :active="route().current('shopping_groups.index')">
                        Groups
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('payment_methods.index')" :active="route().current('payment_methods.index')">
                        Payment methods
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('imports.index')" :active="route().current('imports.*')">
                        CSV import
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('bank.index')" :active="route().current('bank.*')">
                        Bank connection
                    </ResponsiveNavLink>
                </div>

                <div class="border-t border-border pb-3 pt-4">
                    <div class="px-4">
                        <div class="text-base font-medium text-ink">{{ $page.props.auth.user.name }}</div>
                        <div class="text-sm font-medium text-ink-muted">{{ $page.props.auth.user.email }}</div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log out</ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="border-b border-border bg-card">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
