<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const navItems = [
    { label: 'Dashboard', routeName: 'admin.dashboard', icon: 'bi-grid-1x2' },
    { label: 'Businesses', routeName: 'admin.businesses.index', icon: 'bi-building' },
    { label: 'Managers', routeName: 'admin.managers.index', icon: 'bi-person-badge' },
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
                    <span>AI Booking OS</span>
                </div>
            </div>

            <nav class="admin-nav">
                <Link
                    v-for="item in navItems"
                    :key="item.routeName"
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
                        <small>Platform Admin</small>
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
                    <p class="admin-eyebrow">Slotify Admin Console</p>
                    <h2>
                        <slot name="title">Dashboard</slot>
                    </h2>
                </div>

                <div class="admin-topbar-actions">
                    <span class="admin-status-dot"></span>
                    <span>System Online</span>
                </div>
            </header>

            <section class="admin-content">
                <slot />
            </section>
        </main>
    </div>
</template>