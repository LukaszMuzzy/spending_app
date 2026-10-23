<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Spendings" />

    <div class="min-h-screen bg-surface">
        <header class="mx-auto flex max-w-5xl items-center justify-between px-6 py-6">
            <div class="flex items-center gap-2">
                <ApplicationLogo class="h-8 w-auto fill-current text-accent" />
                <span class="text-sm font-semibold tracking-tight text-ink">Spendings</span>
            </div>
            <div v-if="canLogin" class="flex items-center gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-white"
                >
                    Open app
                </Link>
                <template v-else>
                    <Link :href="route('login')" class="text-sm font-medium text-ink-muted hover:text-ink">
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-white"
                    >
                        Register
                    </Link>
                </template>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 pb-16 pt-10">
            <div class="max-w-2xl">
                <p class="text-sm font-medium uppercase tracking-wide text-accent">Personal budget tracker</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-tight text-ink sm:text-5xl">
                    See where the month is headed before it ends.
                </h1>
                <p class="mt-4 text-lg text-ink-muted">
                    Log spending, set category budgets, track bills, and get a clear end-of-month projection.
                    Import a CSV from your bank when you want, without connecting an account.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-xl bg-accent px-5 py-3 text-sm font-semibold text-white"
                    >
                        Go to overview
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="rounded-xl bg-accent px-5 py-3 text-sm font-semibold text-white"
                    >
                        Log in to continue
                    </Link>
                </div>
            </div>

            <div class="mt-14 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                    <h2 class="font-semibold text-ink">Track spending</h2>
                    <p class="mt-2 text-sm text-ink-muted">Receipts by shop, category, and household group, with filters and search.</p>
                </div>
                <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                    <h2 class="font-semibold text-ink">Plan the month</h2>
                    <p class="mt-2 text-sm text-ink-muted">Budgets and expected income sit next to what you have already spent.</p>
                </div>
                <div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
                    <h2 class="font-semibold text-ink">Project ahead</h2>
                    <p class="mt-2 text-sm text-ink-muted">Upcoming bills and your recent pace estimate how the month will finish.</p>
                </div>
            </div>
        </main>
    </div>
</template>
