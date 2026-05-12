<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const navItems = [
    { label: 'Dashboard', routeName: 'dashboard', icon: 'bi-grid-1x2' },
    { label: 'Appointments', routeName: 'dashboard.appointments.index', icon: 'bi-calendar-check' },
    { label: 'Services', routeName: 'dashboard.services.index', icon: 'bi-briefcase' },
    { label: 'Availability', routeName: 'dashboard.availability.index', icon: 'bi-clock' },
    // { label: 'Booking Link', routeName: 'dashboard', icon: 'bi-link-45deg' },
];

const isActive = (routeName: string) => {
    return route().current(routeName);
};
</script>

<template>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <div class="admin-brand-icon">
                    <img src="../../../images/logo.png" alt="Slotify" />
                </div>

                <div>
                    <h1>Slotify</h1>
                    <span>Business Dashboard</span>
                </div>
            </div>

            <nav class="admin-nav">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="route(item.routeName)"
                    class="admin-nav-link"
                    :class="{ active: isActive(item.routeName) }"
                >
                    <i :class="['bi', item.icon]"></i>
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <div class="admin-sidebar-footer">
                <div class="admin-user-card">
                    <div class="admin-user-avatar">
                        {{ page.props.auth.user.name.charAt(0) }}
                    </div>

                    <div>
                        <strong>{{ page.props.auth.user.name }}</strong>
                        <small>Business Manager</small>
                    </div>
                </div>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="admin-logout-btn"
                >
                    Logout
                </Link>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <div>
                    <p class="admin-eyebrow">Slotify Business Console</p>
                    <h2>
                        <slot name="title">Dashboard</slot>
                    </h2>
                </div>

                <div class="admin-topbar-actions">
                    <span class="admin-status-dot"></span>
                    <span>Business Active</span>
                </div>
            </header>

            <section class="admin-content">
                <slot />
            </section>
        </main>
    </div>
</template>