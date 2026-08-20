<!-- <script setup lang="ts">
// import WeeklyAvailability from '@/Components/WeeklyAvailability.vue';
import WeeklyScheduleCard from './components/WeeklyScheduleCard.vue'
import CalendarExceptionsCard from './components/CalendarExceptionsCard.vue'
import WeeklyDayEditor from './components/WeeklyDayEditor.vue'
import StickySaveBar from './components/StickySaveBar.vue'
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import BookingWindowCard from './components/BookingWindowCard.vue'
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import '@/../../resources/css/Pages/Dashboard/Availability/index.css'
import type { Day, AvailabilityBreak } from '../../../types/global.d.ts';



const props = defineProps<{
    days: Day[];
    breaks: AvailabilityBreak[];
    dateOverrides: DateOverride[];
    bookingWindowDays: number;
}>();

const form = useForm({
    days: props.days.map((day) => ({ ...day })),
    breaks: props.breaks.map((breakItem) => ({ ...breakItem })),
    dateOverrides: props.dateOverrides.map((override) => ({ ...override })),
    booking_window_days: props.bookingWindowDays,
});

const today = new Date();

const { t, locale } = useI18n();

const calYear = ref(today.getFullYear());
const calMonth = ref(today.getMonth());
const selectedDate = ref<number | null>(today.getDate());
const selectedDayIndex = ref<number | null>(
    form.days.findIndex((day) => day.day_of_week === today.getDay()),
);

const saved = ref(false);

const MONTHS = computed(() => {
    return Array.from({ length: 12}, (_,month) => {
        return new Intl.DateTimeFormat(locale.value, {
            month: 'long',
        }).format(new Date(2026, month, 1));
    });
});

const WEEKDAYS = computed(() => {
    const baseDate = new Date(2026, 4, 3);
    return Array.from({ length: 7}, (_, index) => {
        return new Intl.DateTimeFormat(locale.value, {
            weekday: 'short',
        }).format(new Date(baseDate.getTime() + index * 86400000));
    });
});

const monthNames = computed(() => MONTHS.value);
const weekdayNames = computed(() => WEEKDAYS.value);

const translatedDayName = (day: string) => {
    const days: Record<string, string> = {
        Sunday: t('days.sunday'),
        Monday: t('days.monday'),
        Tuesday: t('days.tuesday'),
        Wednesday: t('days.wednesday'),
        Thursday: t('days.thursday'),
        Friday: t('days.friday'),
        Saturday: t('days.saturday'),
    };

    return days[day] || day;
};

const PRESETS = [
    { label: '9–5', start: '09:00', end: '17:00' },
    { label: '8–6', start: '08:00', end: '18:00' },
    { label: '10–3', start: '10:00', end: '15:00' },
    { label: t('availability.halfDayAM'), start: '09:00', end: '13:00' },
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


const selectedDayBreaks = computed(() => {
    if (!selectedDay.value) return [];

    return form.breaks.filter((breakItem) => {
        return breakItem.day_of_week === selectedDay.value?.day_of_week
            && breakItem.date === null;
    });
});

const addBreak = () => {
    if (!selectedDay.value) return;

    form.breaks.push({
        day_of_week: selectedDay.value.day_of_week,
        date: null,
        start_time: '13:00',
        end_time: '14:00',
    });
};

const removeBreak = (index: number) => {
    const breakItem = selectedDayBreaks.value[index];

    const realIndex = form.breaks.findIndex((item) => item === breakItem);

    if (realIndex >= 0) {
        form.breaks.splice(realIndex, 1);
    }
};

const isBreakInvalid = (breakItem: AvailabilityBreak) => {
    if (!selectedDay.value) return false;

    const workStart = timeToMinutes(selectedDay.value.start_time);
    const workEnd = timeToMinutes(selectedDay.value.end_time);
    const breakStart = timeToMinutes(breakItem.start_time);
    const breakEnd = timeToMinutes(breakItem.end_time);

    return (
        breakStart < workStart ||
        breakEnd > workEnd ||
        breakStart >= breakEnd
    );
};

const hasInvalidBreaks = computed(() => {
    return selectedDayBreaks.value.some((breakItem) => isBreakInvalid(breakItem));
});

const isBreakStartInvalid = (breakItem: AvailabilityBreak) => {
    if (!selectedDay.value) return false;

    const workStart = timeToMinutes(selectedDay.value.start_time);
    const workEnd = timeToMinutes(selectedDay.value.end_time);
    const breakStart = timeToMinutes(breakItem.start_time);
    const breakEnd = timeToMinutes(breakItem.end_time);

    return breakStart < workStart || breakStart >= workEnd || breakStart >= breakEnd;
};

const isBreakEndInvalid = (breakItem: AvailabilityBreak) => {
    if (!selectedDay.value) return false;

    const workStart = timeToMinutes(selectedDay.value.start_time);
    const workEnd = timeToMinutes(selectedDay.value.end_time);
    const breakStart = timeToMinutes(breakItem.start_time);
    const breakEnd = timeToMinutes(breakItem.end_time);

    return breakEnd > workEnd || breakEnd <= workStart || breakEnd <= breakStart;
};


const selectedFullDate = computed(() => {
    if (!selectedDate.value) return null;

    const month = String(calMonth.value + 1).padStart(2, '0');
    const day = String(selectedDate.value).padStart(2, '0');

    return `${calYear.value}-${month}-${day}`;
});

const selectedDateOverride = computed(() => {
    if (!selectedFullDate.value) return null;

    return form.dateOverrides.find(
        (override) => override.date === selectedFullDate.value
    ) ?? null;
});

const addSpecialDate = () => {
    if (!selectedFullDate.value || !selectedDay.value) return;

    const exists = form.dateOverrides.some(
        (override) => override.date === selectedFullDate.value
    );

    if (exists) return;

    form.dateOverrides.push({
        date: selectedFullDate.value,
        is_active: selectedDay.value.is_active,
        start_time: selectedDay.value.start_time,
        end_time: selectedDay.value.end_time,
    });
};

const removeSpecialDate = (date: string) => {
    const index = form.dateOverrides.findIndex(
        (override) => override.date === date
    );

    if (index >= 0) {
        form.dateOverrides.splice(index, 1);
    }
};


</script>

<template>
    <Head :title="t('availability.title')" />

    <ManagerLayout>
        <template #title>
            {{ t('availability.title') }}
        </template>

        <div class="availability-page">
            <div class="availability-header">
                <div>
                    <h2>{{ t('availability.workingHours') }}</h2>
                    <p>{{ t('availability.subtitle') }}</p>
                </div>

                <div class="availability-stats">
                    <div>
                        <strong>{{ openDaysCount }}</strong>
                        <span>{{ t('availability.openDays') }}</span>
                    </div>

                    <div class="availability-stat-divider"></div>

                    <div>
                        <strong>{{ totalHoursLabel }}</strong>
                        <span>{{ t('availability.totalWeek') }}</span>
                    </div>
                </div>
            </div>

            <div class="availability-grid">
                <div class="availability-left">
                    
                    <CalendarExceptionsCard
                        :cal-year="calYear"
                        :cal-month="calMonth"
                        :month-name="monthNames[calMonth]"
                        :weekday-names="weekdayNames"
                        :calendar-days="calendarDays"
                        :selected-date="selectedDate"
                        :selected-full-date="selectedFullDate"
                        :date-overrides="form.dateOverrides"
                        :is-today="isToday"
                        :is-selected="isSelected"
                        :is-day-active="isDayActive"
                        @prev-month="prevMonth"
                        @next-month="nextMonth"
                        @select-date="selectDate"
                        @add-override="addSpecialDate"
                        @remove-override="removeSpecialDate"
                    />
                    <WeeklyDayEditor
                        :day="selectedDay"
                        :breaks="selectedDayBreaks"
                        :translated-day-name="translatedDayName"
                        @add-break="addBreak"
                        @remove-break="removeBreak"
                        @apply-preset="applyPreset"
                        @copy-to-open-days="copyToAllOpenDays"
                        />
                </div>

                <div class="availability-right">
                    <div class="availability-card availability-weekly">
                        <div class="availability-weekly-header">
                            <h3>{{ t('availability.weeklySchedule') }}</h3>
                            <span>{{ t('availability.clickDayToEdit') }}</span>
                        </div>
                        <BookingWindowCard v-model="form.booking_window_days" />        
                        <WeeklyScheduleCard
                            :days="form.days"
                            :selected-day-index="selectedDayIndex"
                            :translated-day-name="translatedDayName"
                            @select="onSelectDay"
                        />
                    </div>

                    <StickySaveBar
                        :processing="form.processing"
                        :saved="saved"
                        :disabled="hasInvalidBreaks"
                        @save="handleSave"
                    />
                </div>
            </div>
        </div>
    </ManagerLayout>
</template> -->


<script setup lang="ts">
import CalendarExceptionsCard from './components/CalendarExceptionsCard.vue'
import WeeklyDayEditor from './components/WeeklyDayEditor.vue'
import StickySaveBar from './components/StickySaveBar.vue'
import BookingWindowCard from './components/BookingWindowCard.vue'

import ManagerLayout from '@/Layouts/ManagerLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import '@/../../resources/css/Pages/Dashboard/Availability/index.css'

import type {
    Day,
    AvailabilityBreak,
} from '../../../types/global.d.ts'

type DateOverride = {
    id?: number
    date: string
    is_active: boolean
    start_time: string
    end_time: string
}

type CalendarCell = {
    date: number | null
    dow: number | null
}

const props = defineProps<{
    days: Day[]
    breaks: AvailabilityBreak[]
    dateOverrides: DateOverride[]
    bookingWindowDays: number
}>()

const { t, locale } = useI18n()

const form = useForm({
    days: props.days.map((day) => ({ ...day })),
    breaks: props.breaks.map((breakItem) => ({ ...breakItem })),
    dateOverrides: props.dateOverrides.map((override) => ({ ...override })),
    booking_window_days: props.bookingWindowDays,
})

const today = new Date()

const calYear = ref(today.getFullYear())
const calMonth = ref(today.getMonth())
const selectedDate = ref<number | null>(today.getDate())

const initialDayIndex = form.days.findIndex(
    (day) => day.day_of_week === today.getDay(),
)

const selectedDayIndex = ref<number | null>(
    initialDayIndex >= 0 ? initialDayIndex : null,
)

const saved = ref(false)

const monthNames = computed(() => {
    return Array.from({ length: 12 }, (_, month) => {
        return new Intl.DateTimeFormat(locale.value, {
            month: 'long',
        }).format(new Date(2026, month, 1))
    })
})

const weekdayNames = computed(() => {
    const baseDate = new Date(2026, 4, 3)

    return Array.from({ length: 7 }, (_, index) => {
        return new Intl.DateTimeFormat(locale.value, {
            weekday: 'short',
        }).format(
            new Date(baseDate.getTime() + index * 86_400_000),
        )
    })
})

const translatedDayName = (day: string): string => {
    const translatedDays: Record<string, string> = {
        Sunday: t('days.sunday'),
        Monday: t('days.monday'),
        Tuesday: t('days.tuesday'),
        Wednesday: t('days.wednesday'),
        Thursday: t('days.thursday'),
        Friday: t('days.friday'),
        Saturday: t('days.saturday'),
    }

    return translatedDays[day] ?? day
}

const timeToMinutes = (time: string): number => {
    if (!time) {
        return 0
    }

    const [hours, minutes] = time.split(':').map(Number)

    return hours * 60 + minutes
}

const calendarDays = computed<CalendarCell[]>(() => {
    const firstDay = new Date(
        calYear.value,
        calMonth.value,
        1,
    ).getDay()

    const daysInMonth = new Date(
        calYear.value,
        calMonth.value + 1,
        0,
    ).getDate()

    const cells: CalendarCell[] = []

    for (let index = 0; index < firstDay; index++) {
        cells.push({
            date: null,
            dow: null,
        })
    }

    for (let date = 1; date <= daysInMonth; date++) {
        cells.push({
            date,
            dow: new Date(
                calYear.value,
                calMonth.value,
                date,
            ).getDay(),
        })
    }

    return cells
})

const selectedDay = computed<Day | null>(() => {
    if (selectedDayIndex.value === null) {
        return null
    }

    return form.days[selectedDayIndex.value] ?? null
})

const openDaysCount = computed(() => {
    return form.days.filter((day) => day.is_active).length
})

const totalHours = computed(() => {
    return form.days
        .filter((day) => day.is_active)
        .reduce((total, day) => {
            const start = timeToMinutes(day.start_time)
            const end = timeToMinutes(day.end_time)

            return total + Math.max(0, end - start)
        }, 0)
})

const totalHoursLabel = computed(() => {
    const hours = Math.floor(totalHours.value / 60)
    const minutes = totalHours.value % 60

    return minutes > 0
        ? `${hours}h ${minutes}m`
        : `${hours}h`
})

const selectedFullDate = computed<string | null>(() => {
    if (!selectedDate.value) {
        return null
    }

    const month = String(calMonth.value + 1).padStart(2, '0')
    const day = String(selectedDate.value).padStart(2, '0')

    return `${calYear.value}-${month}-${day}`
})

const selectedDayBreaks = computed(() => {
    if (!selectedDay.value) {
        return []
    }

    return form.breaks.filter((breakItem) => {
        return (
            breakItem.day_of_week === selectedDay.value?.day_of_week
            && breakItem.date === null
        )
    })
})

const isToday = (cell: CalendarCell): boolean => {
    return (
        cell.date === today.getDate()
        && calMonth.value === today.getMonth()
        && calYear.value === today.getFullYear()
    )
}

const isSelected = (cell: CalendarCell): boolean => {
    return (
        cell.date === selectedDate.value
        && cell.dow === selectedDay.value?.day_of_week
    )
}

const isDayActive = (dayOfWeek: number | null): boolean => {
    if (dayOfWeek === null) {
        return false
    }

    return (
        form.days.find(
            (day) => day.day_of_week === dayOfWeek,
        )?.is_active ?? false
    )
}

const prevMonth = (): void => {
    if (calMonth.value === 0) {
        calMonth.value = 11
        calYear.value--

        return
    }

    calMonth.value--
}

const nextMonth = (): void => {
    if (calMonth.value === 11) {
        calMonth.value = 0
        calYear.value++

        return
    }

    calMonth.value++
}

const selectDate = (cell: CalendarCell): void => {
    if (!cell.date || cell.dow === null) {
        return
    }

    selectedDate.value = cell.date

    const dayIndex = form.days.findIndex(
        (day) => day.day_of_week === cell.dow,
    )

    selectedDayIndex.value =
        dayIndex >= 0 ? dayIndex : null
}

const applyPreset = (preset: {
    label: string
    start: string
    end: string
}): void => {
    if (!selectedDay.value) {
        return
    }

    selectedDay.value.start_time = preset.start
    selectedDay.value.end_time = preset.end
    selectedDay.value.is_active = true
}

const copyToAllOpenDays = (): void => {
    if (!selectedDay.value) {
        return
    }

    form.days.forEach((day) => {
        if (!day.is_active) {
            return
        }

        day.start_time = selectedDay.value!.start_time
        day.end_time = selectedDay.value!.end_time
    })
}

const addBreak = (): void => {
    if (!selectedDay.value) {
        return
    }

    form.breaks.push({
        day_of_week: selectedDay.value.day_of_week,
        date: null,
        start_time: '13:00',
        end_time: '14:00',
    })
}

const removeBreak = (index: number): void => {
    const breakItem = selectedDayBreaks.value[index]

    if (!breakItem) {
        return
    }

    const formIndex = form.breaks.findIndex(
        (item) => item === breakItem,
    )

    if (formIndex >= 0) {
        form.breaks.splice(formIndex, 1)
    }
}

const isBreakInvalid = (
    breakItem: AvailabilityBreak,
): boolean => {
    if (!selectedDay.value) {
        return false
    }

    const workingStart = timeToMinutes(
        selectedDay.value.start_time,
    )

    const workingEnd = timeToMinutes(
        selectedDay.value.end_time,
    )

    const breakStart = timeToMinutes(
        breakItem.start_time,
    )

    const breakEnd = timeToMinutes(
        breakItem.end_time,
    )

    return (
        breakStart < workingStart
        || breakEnd > workingEnd
        || breakStart >= breakEnd
    )
}

const hasInvalidBreaks = computed(() => {
    return selectedDayBreaks.value.some(
        (breakItem) => isBreakInvalid(breakItem),
    )
})

const addSpecialDate = (): void => {
    if (!selectedFullDate.value || !selectedDay.value) {
        return
    }

    const alreadyExists = form.dateOverrides.some(
        (override) => {
            return override.date === selectedFullDate.value
        },
    )

    if (alreadyExists) {
        return
    }

    form.dateOverrides.push({
        date: selectedFullDate.value,
        is_active: selectedDay.value.is_active,
        start_time: selectedDay.value.start_time,
        end_time: selectedDay.value.end_time,
    })
}

const removeSpecialDate = (date: string): void => {
    const overrideIndex = form.dateOverrides.findIndex(
        (override) => override.date === date,
    )

    if (overrideIndex >= 0) {
        form.dateOverrides.splice(overrideIndex, 1)
    }
}

const handleSave = (): void => {
    form.put(route('dashboard.availability.update'), {
        preserveScroll: true,

        onSuccess: () => {
            saved.value = true

            window.setTimeout(() => {
                saved.value = false
            }, 2500)
        },

        onError: (errors) => {
            console.error(
                'Availability validation errors:',
                errors,
            )
        },
    })
}
</script>

<template>
    <Head :title="t('availability.title')" />

    <ManagerLayout>
        <template #title>
            {{ t('availability.title') }}
        </template>

        <div class="availability-page">
            <header class="availability-header">
                <div class="availability-header__content">
                    <span class="availability-header__eyebrow">
                        {{ t('availability.title') }}
                    </span>

                    <h2>
                        {{ t('availability.workingHours') }}
                    </h2>

                    <p>
                        {{ t('availability.subtitle') }}
                    </p>
                </div>

                <div class="availability-stats">
                    <div class="availability-stat">
                        <strong>{{ openDaysCount }}</strong>

                        <span>
                            {{ t('availability.openDays') }}
                        </span>
                    </div>

                    <div class="availability-stat-divider"></div>

                    <div class="availability-stat">
                        <strong>{{ totalHoursLabel }}</strong>

                        <span>
                            {{ t('availability.totalWeek') }}
                        </span>
                    </div>
                </div>
            </header>

            <section class="availability-section">
                <BookingWindowCard
                    v-model="form.booking_window_days"
                />
            </section>

            <div class="availability-workspace">
                <section class="availability-workspace__calendar">
                    <CalendarExceptionsCard
                        :cal-year="calYear"
                        :cal-month="calMonth"
                        :month-name="monthNames[calMonth]"
                        :weekday-names="weekdayNames"
                        :calendar-days="calendarDays"
                        :selected-date="selectedDate"
                        :selected-full-date="selectedFullDate"
                        :date-overrides="form.dateOverrides"
                        :is-today="isToday"
                        :is-selected="isSelected"
                        :is-day-active="isDayActive"
                        @prev-month="prevMonth"
                        @next-month="nextMonth"
                        @select-date="selectDate"
                        @add-override="addSpecialDate"
                        @remove-override="removeSpecialDate"
                    />
                </section>

                <aside class="availability-workspace__editor">
                    <WeeklyDayEditor
                        :day="selectedDay"
                        :breaks="selectedDayBreaks"
                        :translated-day-name="translatedDayName"
                        @add-break="addBreak"
                        @remove-break="removeBreak"
                        @apply-preset="applyPreset"
                        @copy-to-open-days="copyToAllOpenDays"
                    />
                </aside>
            </div>

            <StickySaveBar
                :processing="form.processing"
                :saved="saved"
                :disabled="hasInvalidBreaks"
                @save="handleSave"
            />
        </div>
    </ManagerLayout>
</template>