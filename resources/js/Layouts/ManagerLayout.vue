<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const page = usePage();
const { t, locale } = useI18n();
const navItems = [
    { labelKey: 'manager.nav.dashboard', routeName: 'dashboard', icon: 'bi-grid-1x2' },
    { labelKey: 'manager.nav.appointments', routeName: 'dashboard.appointments.index', icon: 'bi-calendar-check' },
    { labelKey: 'manager.nav.services', routeName: 'dashboard.services.index', icon: 'bi-briefcase' },
    { labelKey: 'manager.nav.availability', routeName: 'dashboard.availability.index', icon: 'bi-clock' },
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
                    <span>{{t('manager.businessDashboard')}}</span>
                </div>
            </div>

            <nav class="admin-nav">
                <Link
                    v-for="item in navItems"
                    :key="item.labelKey"
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
                        <small>{{t('manager.businessManager')}}</small>
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
                <div>
                    <p class="admin-eyebrow">{{t('manager.console')}}</p>
                    <h2>
                        <slot name="title">{{t('manager.nav.dashboard')}}</slot>
                    </h2>
                </div>

                <div class="admin-topbar-actions">
                    <select 
                        class="admin-language-select"
                        :value="locale"
                        @change="changeLanguage"
                        >
                            <option value="en">EN</option>
                            <option value="he">HE</option>
                            <option value="ar">AR</option>
                    </select>
                    <span class="admin-status-dot"></span>
                    <span>{{t('manager.businessActive')}}</span>
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