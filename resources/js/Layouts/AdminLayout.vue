<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const page = usePage();
const { t, locale } = useI18n();
const navItems = [
    { labelKey: 'admin.nav.dashboard', routeName: 'admin.dashboard', icon: 'bi-grid-1x2' },
    { labelKey: 'admin.nav.businesses', routeName: 'admin.businesses.index', icon: 'bi-building' },
    { labelKey: 'admin.nav.managers', routeName: 'admin.managers.index', icon: 'bi-person-badge' },
];

const isActive = (routeName: string) => {
    return route().current(routeName);
};

const changeLanguage = (event: Event) => {
    const target = event.target as HTMLSelectElement;

    locale.value = target.value;

    localStorage.setItem('locale', target.value);
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
                    <span>{{ t(item.labelKey) }}</span>
                </Link>
            </nav>

            <div class="admin-sidebar-footer">
                <div class="admin-user-card">
                    <div class="admin-user-avatar">
                        {{ page.props.auth.user.name.charAt(0) }}
                    </div>

                    <div>
                        <strong>{{ page.props.auth.user.name }}</strong>
                        <small>{{t('admin.platformAdmin')}}</small>
                    </div>
                </div>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="admin-logout-btn"
                >
                    {{t('common.logout')}}
                </Link>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-topbar">
                <select 
                        class="admin-language-select"
                        :value="locale"
                        @change="changeLanguage"
                        >
                            <option value="en">EN</option>
                            <option value="he">HE</option>
                            <option value="ar">AR</option>
                    </select>
                <div>
                    <p class="admin-eyebrow">{{t('admin.console')}}</p>
                    <h2>
                        <slot name="title">{{t('admin.nav.dashboard')}}</slot>
                    </h2>
                </div>

                <div class="admin-topbar-actions">
                    <span class="admin-status-dot"></span>
                    <span>{{t('admin.systemOnline')}}</span>
                </div>
            </header>

            <section class="admin-content">
                <slot />
            </section>
        </main>
    </div>
</template>

<style scoped>

.admin-language-select {
    border: 1px solid #e5ecf6;
    background: #ffffff;
    border-radius: 12px;
    padding: 8px 12px;
    font-weight: 700;
    color: #071533;
    outline: none;
}
</style>