<script setup lang="ts">
import WeeklyAvailability from '@/Components/WeeklyAvailability.vue';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Day = {
    day_of_week: number;
    label: string;
    is_active: boolean;
    start_time: string;
    end_time: string;
};

const props = defineProps<{
    days: Day[];
}>();

const form = useForm({
    days: props.days.map((day) => ({ ...day })),
});

const today = new Date();

const calYear = ref(today.getFullYear());
const calMonth = ref(today.getMonth());
const selectedDate = ref<number | null>(today.getDate());
const selectedDayIndex = ref<number | null>(
    form.days.findIndex((day) => day.day_of_week === today.getDay()),
);

const saved = ref(false);

const MONTHS = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const WEEKDAYS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

const PRESETS = [
    { label: '9–5', start: '09:00', end: '17:00' },
    { label: '8–6', start: '08:00', end: '18:00' },
    { label: '10–3', start: '10:00', end: '15:00' },
    { label: 'Half day AM', start: '09:00', end: '13:00' },
];

const timeToMinutes = (time: string): number => {
    if (!time) return 0;

    const [hours, minutes] = time.split(':').map(Number);

    return hours * 60 + minutes;
};

const calendarDays = computed(() => {
    const firstDay = new Date(calYear.value, calMonth.value, 1).getDay();
    const daysInMonth = new Date(calYear.value, calMonth.value + 1, 0).getDate();

    const cells: Array<{ date: number | null; dow: number | null }> = [];

    for (let i = 0; i < firstDay; i++) {
        cells.push({ date: null, dow: null });
    }

    for (let date = 1; date <= daysInMonth; date++) {
        cells.push({
            date,
            dow: new Date(calYear.value, calMonth.value, date).getDay(),
        });
    }

    return cells;
});

const selectedDay = computed(() => {
    return selectedDayIndex.value !== null
        ? form.days[selectedDayIndex.value]
        : null;
});

const openDaysCount = computed(() => {
    return form.days.filter((day) => day.is_active).length;
});

const totalHours = computed(() => {
    return form.days
        .filter((day) => day.is_active)
        .reduce((sum, day) => {
            const start = timeToMinutes(day.start_time);
            const end = timeToMinutes(day.end_time);

            return sum + Math.max(0, end - start);
        }, 0);
});

const totalHoursLabel = computed(() => {
    const hours = Math.floor(totalHours.value / 60);
    const minutes = totalHours.value % 60;

    return minutes > 0 ? `${hours}h ${minutes}m` : `${hours}h`;
});

const isToday = (cell: { date: number | null }) => {
    return (
        cell.date === today.getDate()
        && calMonth.value === today.getMonth()
        && calYear.value === today.getFullYear()
    );
};

const isSelected = (cell: { date: number | null; dow: number | null }) => {
    return (
        cell.date === selectedDate.value
        && cell.dow === selectedDay.value?.day_of_week
    );
};

const isDayActive = (dow: number | null) => {
    if (dow === null) return false;

    return form.days.find((day) => day.day_of_week === dow)?.is_active ?? false;
};

const prevMonth = () => {
    if (calMonth.value === 0) {
        calMonth.value = 11;
        calYear.value--;
        return;
    }

    calMonth.value--;
};

const nextMonth = () => {
    if (calMonth.value === 11) {
        calMonth.value = 0;
        calYear.value++;
        return;
    }

    calMonth.value++;
};

const selectDate = (cell: { date: number | null; dow: number | null }) => {
    if (!cell.date || cell.dow === null) return;

    selectedDate.value = cell.date;

    const index = form.days.findIndex((day) => day.day_of_week === cell.dow);

    selectedDayIndex.value = index >= 0 ? index : null;
};

const onSelectDay = (index: number) => {
    selectedDayIndex.value = index;
};

const applyPreset = (preset: { label: string; start: string; end: string }) => {
    if (!selectedDay.value) return;

    selectedDay.value.start_time = preset.start;
    selectedDay.value.end_time = preset.end;
    selectedDay.value.is_active = true;
};

const copyToAllOpenDays = () => {
    if (!selectedDay.value) return;

    form.days.forEach((day) => {
        if (day.is_active) {
            day.start_time = selectedDay.value!.start_time;
            day.end_time = selectedDay.value!.end_time;
        }
    });
};

const handleSave = () => {
    form.put(route('dashboard.availability.update'), {
        onSuccess: () => {
            saved.value = true;

            setTimeout(() => {
                saved.value = false;
            }, 2500);
        },
    });
};
</script>

<template>
    <Head title="Availability" />

    <ManagerLayout>
        <template #title>
            Availability
        </template>

        <div class="availability-page">
            <div class="availability-header">
                <div>
                    <h2>Working Hours</h2>
                    <p>Set when customers can book appointments.</p>
                </div>

                <div class="availability-stats">
                    <div>
                        <strong>{{ openDaysCount }}</strong>
                        <span>Open Days</span>
                    </div>

                    <div class="availability-stat-divider"></div>

                    <div>
                        <strong>{{ totalHoursLabel }}</strong>
                        <span>Total / Week</span>
                    </div>
                </div>
            </div>

            <div class="availability-grid">
                <div class="availability-left">
                    <div class="availability-card availability-calendar">
                        <div class="availability-calendar-header">
                            <button type="button" @click="prevMonth">
                                <i class="bi bi-chevron-left"></i>
                            </button>

                            <h3>{{ MONTHS[calMonth] }} {{ calYear }}</h3>

                            <button type="button" @click="nextMonth">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>

                        <div class="availability-calendar-grid">
                            <div
                                v-for="weekday in WEEKDAYS"
                                :key="weekday"
                                class="availability-weekday"
                            >
                                {{ weekday }}
                            </div>

                            <button
                                v-for="(cell, index) in calendarDays"
                                :key="index"
                                type="button"
                                class="availability-day"
                                :class="{
                                    'availability-day--empty': !cell.date,
                                    'availability-day--today': cell.date && isToday(cell),
                                    'availability-day--selected': cell.date && isSelected(cell),
                                    'availability-day--closed': cell.date && !isDayActive(cell.dow),
                                }"
                                :disabled="!cell.date"
                                @click="selectDate(cell)"
                            >
                                <span v-if="cell.date">
                                    {{ cell.date }}
                                </span>

                                <span
                                    v-if="cell.date"
                                    class="availability-day-dot"
                                    :class="{ 'availability-day-dot--open': isDayActive(cell.dow) }"
                                ></span>
                            </button>
                        </div>

                        <div class="availability-legend">
                            <span>
                                <i class="availability-legend-dot availability-legend-dot--open"></i>
                                Open
                            </span>

                            <span>
                                <i class="availability-legend-dot"></i>
                                Closed
                            </span>
                        </div>
                    </div>

                    <Transition name="availability-slide">
                        <div
                            v-if="selectedDay"
                            class="availability-card availability-editor"
                        >
                            <div class="availability-editor-header">
                                <div>
                                    <span>Editing</span>
                                    <h3>{{ selectedDay.label }}</h3>
                                </div>

                                <label class="availability-switch">
                                    <input
                                        v-model="selectedDay.is_active"
                                        type="checkbox"
                                    />

                                    <span class="availability-switch-track">
                                        <span class="availability-switch-thumb"></span>
                                    </span>

                                    <small>{{ selectedDay.is_active ? 'Open' : 'Closed' }}</small>
                                </label>
                            </div>

                            <Transition name="availability-fade">
                                <div v-if="selectedDay.is_active">
                                    <div class="availability-time-row">
                                        <div>
                                            <label>Start Time</label>
                                            <input
                                                v-model="selectedDay.start_time"
                                                type="time"
                                            />
                                        </div>

                                        <span class="availability-time-separator"></span>

                                        <div>
                                            <label>End Time</label>
                                            <input
                                                v-model="selectedDay.end_time"
                                                type="time"
                                            />
                                        </div>
                                    </div>

                                    <div class="availability-presets">
                                        <span>Quick Set</span>

                                        <div>
                                            <button
                                                v-for="preset in PRESETS"
                                                :key="preset.label"
                                                type="button"
                                                @click="applyPreset(preset)"
                                            >
                                                {{ preset.label }}
                                            </button>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="availability-copy-btn"
                                        @click="copyToAllOpenDays"
                                    >
                                        <i class="bi bi-copy"></i>
                                        Apply these hours to all open days
                                    </button>
                                </div>
                            </Transition>

                            <div
                                v-if="!selectedDay.is_active"
                                class="availability-closed-message"
                            >
                                This day is marked as closed. Toggle open to set hours.
                            </div>
                        </div>
                    </Transition>
                </div>

                <div class="availability-right">
                    <div class="availability-card availability-weekly">
                        <div class="availability-weekly-header">
                            <h3>Weekly Schedule</h3>
                            <span>Click a day to edit</span>
                        </div>

                        <WeeklyAvailability
                            :days="form.days"
                            :selected-day-index="selectedDayIndex"
                            @select="onSelectDay"
                        />
                    </div>

                    <div class="availability-save-row">
                        <Transition name="availability-fade">
                            <span
                                v-if="saved"
                                class="availability-saved"
                            >
                                <i class="bi bi-check-circle"></i>
                                Saved successfully
                            </span>
                        </Transition>

                        <button
                            type="button"
                            class="availability-save-btn"
                            :disabled="form.processing"
                            @click="handleSave"
                        >
                            <i
                                class="bi"
                                :class="form.processing ? 'bi-arrow-repeat' : 'bi-check-lg'"
                            ></i>

                            {{ form.processing ? 'Saving...' : 'Save Availability' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>

<style scoped>
.availability-page {
    --slot-navy: #071533;
    --slot-blue: #2563ff;
    --slot-blue-2: #3b82f6;
    --slot-blue-soft: rgba(37, 99, 255, 0.08);
    --slot-bg: #f4f7fb;
    --slot-card: #ffffff;
    --slot-border: #e5ecf6;
    --slot-text: #071533;
    --slot-muted: #6b7890;
    --slot-success: #16a34a;
    --slot-success-soft: rgba(34, 197, 94, 0.13);

    color: var(--slot-text);
    padding-bottom: 40px;
}

.availability-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 28px;
}

.availability-header h2 {
    font-size: 26px;
    font-weight: 900;
    margin: 0 0 6px;
    color: var(--slot-text);
}

.availability-header p {
    margin: 0;
    color: var(--slot-muted);
}

.availability-stats {
    display: flex;
    align-items: center;
    gap: 22px;
    background: var(--slot-card);
    border: 1px solid var(--slot-border);
    border-radius: 18px;
    padding: 14px 22px;
    box-shadow: 0 18px 45px rgba(7, 21, 51, 0.08);
}

.availability-stats div {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.availability-stats strong {
    font-size: 24px;
    line-height: 1;
    color: var(--slot-blue);
}

.availability-stats span {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--slot-muted);
}

.availability-stat-divider {
    width: 1px;
    height: 34px;
    background: var(--slot-border);
}

.availability-grid {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 24px;
    align-items: start;
}

.availability-left,
.availability-right {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.availability-card {
    background: var(--slot-card);
    border: 1px solid var(--slot-border);
    border-radius: 26px;
    padding: 26px;
    box-shadow: 0 18px 45px rgba(7, 21, 51, 0.08);
}

.availability-calendar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
}

.availability-calendar-header h3 {
    font-size: 17px;
    font-weight: 850;
    margin: 0;
}

.availability-calendar-header button {
    width: 38px;
    height: 38px;
    border: 1px solid var(--slot-border);
    border-radius: 12px;
    background: #ffffff;
    color: var(--slot-muted);
}

.availability-calendar-header button:hover {
    border-color: var(--slot-blue);
    color: var(--slot-blue);
    background: var(--slot-blue-soft);
}

.availability-calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}

.availability-weekday {
    text-align: center;
    color: var(--slot-muted);
    font-size: 12px;
    font-weight: 800;
    padding-bottom: 8px;
}

.availability-day {
    position: relative;
    aspect-ratio: 1;
    border: 1px solid transparent;
    border-radius: 14px;
    background: transparent;
    color: var(--slot-text);
    font-weight: 700;
    transition: 0.16s ease;
}

.availability-day:not(.availability-day--empty):hover {
    border-color: var(--slot-border);
    background: var(--slot-blue-soft);
}

.availability-day--today {
    border-color: var(--slot-blue);
}

.availability-day--selected {
    background: linear-gradient(135deg, var(--slot-blue), var(--slot-blue-2));
    color: #ffffff;
    border-color: var(--slot-blue);
    box-shadow: 0 12px 28px rgba(37, 99, 255, 0.25);
}

.availability-day--empty {
    cursor: default;
}

.availability-day--closed {
    opacity: 0.45;
}

.availability-day-dot {
    position: absolute;
    width: 6px;
    height: 6px;
    bottom: 7px;
    left: 50%;
    transform: translateX(-50%);
    border-radius: 50%;
    background: #cbd5e1;
}

.availability-day-dot--open {
    background: var(--slot-blue);
}

.availability-day--selected .availability-day-dot {
    background: #ffffff;
}

.availability-legend {
    display: flex;
    gap: 18px;
    border-top: 1px solid var(--slot-border);
    margin-top: 22px;
    padding-top: 18px;
    color: var(--slot-muted);
    font-size: 13px;
}

.availability-legend span {
    display: flex;
    align-items: center;
    gap: 8px;
}

.availability-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #cbd5e1;
}

.availability-legend-dot--open {
    background: var(--slot-blue);
}

.availability-editor-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
}

.availability-editor-header span {
    display: block;
    color: var(--slot-muted);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 4px;
}

.availability-editor-header h3 {
    font-size: 28px;
    font-weight: 900;
    margin: 0;
}

.availability-switch {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.availability-switch input {
    display: none;
}

.availability-switch-track {
    position: relative;
    width: 48px;
    height: 28px;
    border-radius: 999px;
    background: #dbe3ef;
    transition: 0.18s ease;
}

.availability-switch-thumb {
    position: absolute;
    width: 22px;
    height: 22px;
    top: 3px;
    left: 3px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(7, 21, 51, 0.18);
    transition: 0.18s ease;
}

.availability-switch input:checked ~ .availability-switch-track {
    background: var(--slot-blue);
}

.availability-switch input:checked ~ .availability-switch-track .availability-switch-thumb {
    transform: translateX(20px);
}

.availability-switch small {
    color: var(--slot-muted);
    font-weight: 700;
}

.availability-time-row {
    display: flex;
    align-items: flex-end;
    gap: 14px;
    margin-bottom: 24px;
}

.availability-time-row > div {
    flex: 1;
}

.availability-time-row label,
.availability-presets > span {
    display: block;
    color: var(--slot-muted);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.availability-time-row input {
    width: 100%;
    border: 1px solid var(--slot-border);
    background: #ffffff;
    border-radius: 16px;
    padding: 14px 16px;
    color: var(--slot-text);
    font-size: 16px;
    font-weight: 700;
}

.availability-time-row input:focus {
    outline: none;
    border-color: var(--slot-blue);
    box-shadow: 0 0 0 4px rgba(37, 99, 255, 0.1);
}

.availability-time-separator {
    width: 24px;
    height: 2px;
    background: var(--slot-border);
    margin-bottom: 24px;
}

.availability-presets {
    margin-bottom: 22px;
}

.availability-presets div {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.availability-presets button {
    border: 1px solid var(--slot-border);
    background: #ffffff;
    color: var(--slot-muted);
    border-radius: 999px;
    padding: 7px 13px;
    font-weight: 700;
}

.availability-presets button:hover {
    color: var(--slot-blue);
    border-color: var(--slot-blue);
    background: var(--slot-blue-soft);
}

.availability-copy-btn {
    width: 100%;
    border: 1.5px dashed var(--slot-border);
    background: transparent;
    color: var(--slot-muted);
    border-radius: 16px;
    padding: 13px 16px;
    font-weight: 700;
}

.availability-copy-btn:hover {
    color: var(--slot-blue);
    border-color: var(--slot-blue);
    background: var(--slot-blue-soft);
}

.availability-closed-message {
    color: var(--slot-muted);
    background: var(--slot-blue-soft);
    border-radius: 16px;
    padding: 18px;
}

.availability-weekly-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 24px;
}

.availability-weekly-header h3 {
    font-size: 20px;
    font-weight: 900;
    margin: 0;
}

.availability-weekly-header span {
    color: var(--slot-muted);
    font-size: 13px;
}

.availability-save-row {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 14px;
}

.availability-save-btn {
    border: 0;
    border-radius: 16px;
    padding: 14px 26px;
    background: linear-gradient(135deg, var(--slot-blue), var(--slot-blue-2));
    color: #ffffff;
    font-weight: 800;
    box-shadow: 0 16px 34px rgba(37, 99, 255, 0.26);
}

.availability-save-btn:disabled {
    opacity: 0.65;
}

.availability-saved {
    display: flex;
    align-items: center;
    gap: 7px;
    color: var(--slot-success);
    font-weight: 700;
}

.availability-slide-enter-active,
.availability-slide-leave-active,
.availability-fade-enter-active,
.availability-fade-leave-active {
    transition: 0.22s ease;
}

.availability-slide-enter-from,
.availability-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.availability-fade-enter-from,
.availability-fade-leave-to {
    opacity: 0;
}

@media (max-width: 1200px) {
    .availability-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .availability-header {
        flex-direction: column;
    }

    .availability-time-row {
        flex-direction: column;
    }

    .availability-time-separator {
        display: none;
    }

    .availability-card {
        padding: 20px;
    }
}
</style>