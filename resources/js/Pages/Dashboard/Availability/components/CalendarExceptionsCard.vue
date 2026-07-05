<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

type CalendarCell = {
  date: number | null
  dow: number | null
}

type DateOverride = {
  id?: number
  date: string
  is_active: boolean
  start_time: string
  end_time: string
}

const props = defineProps<{
  calYear: number
  calMonth: number
  monthName: string
  weekdayNames: string[]
  calendarDays: CalendarCell[]
  selectedDate: number | null
  selectedFullDate: string | null
  dateOverrides: DateOverride[]
  isToday: (cell: CalendarCell) => boolean
  isSelected: (cell: CalendarCell) => boolean
  isDayActive: (dow: number | null) => boolean
}>()

const emit = defineEmits<{
  (e: 'prev-month'): void
  (e: 'next-month'): void
  (e: 'select-date', cell: CalendarCell): void
  (e: 'add-override'): void
  (e: 'remove-override', date: string): void
}>()

const { t } = useI18n()

const selectedOverride = computed(() => {
  if (!props.selectedFullDate) return null

  return props.dateOverrides.find(
    (override) => override.date === props.selectedFullDate
  ) ?? null
})

const hasOverride = (cell: CalendarCell) => {
  if (!cell.date) return false

  const month = String(props.calMonth + 1).padStart(2, '0')
  const day = String(cell.date).padStart(2, '0')
  const date = `${props.calYear}-${month}-${day}`

  return props.dateOverrides.some((override) => override.date === date)
}
</script>

<template>
  <section class="availability-card calendar-exceptions-card">
    <div class="calendar-exceptions-header">
      <div>
        <h3>{{ t('availability.calendarExceptions') }}</h3>
        <p>{{ t('availability.calendarExceptionsDescription') }}</p>
      </div>
    </div>

    <div class="calendar-exceptions-layout">
      <div class="calendar-exceptions-calendar">
        <div class="availability-calendar-header">
          <button type="button" @click="emit('prev-month')">
            <i class="bi bi-chevron-left"></i>
          </button>

          <h3>{{ monthName }} {{ calYear }}</h3>

          <button type="button" @click="emit('next-month')">
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>

        <div class="availability-calendar-grid">
          <div
            v-for="weekday in weekdayNames"
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
              'availability-day--override': cell.date && hasOverride(cell),
            }"
            :disabled="!cell.date"
            @click="emit('select-date', cell)"
          >
            <span v-if="cell.date">{{ cell.date }}</span>

            <span
              v-if="cell.date"
              class="availability-day-dot"
              :class="{
                'availability-day-dot--open': isDayActive(cell.dow),
                'availability-day-dot--override': hasOverride(cell),
              }"
            ></span>
          </button>
        </div>

        <div class="availability-legend">
          <span>
            <i class="availability-legend-dot availability-legend-dot--open"></i>
            {{ t('availability.weeklySchedule') }}
          </span>

          <span>
            <i class="availability-legend-dot calendar-exceptions-dot"></i>
            {{ t('availability.override') }}
          </span>

          <span>
            <i class="availability-legend-dot"></i>
            {{ t('common.closed') }}
          </span>
        </div>
      </div>

      <div class="calendar-exceptions-editor">
        <template v-if="selectedFullDate">
          <div class="calendar-exceptions-selected">
            <span>{{ t('availability.selectedDate') }}</span>
            <h4>{{ selectedFullDate }}</h4>
          </div>

          <template v-if="selectedOverride">
            <div class="calendar-exceptions-status">
              <label class="availability-switch">
                <input v-model="selectedOverride.is_active" type="checkbox" />

                <span class="availability-switch-track">
                  <span class="availability-switch-thumb"></span>
                </span>

                <small>
                  {{ selectedOverride.is_active ? t('common.open') : t('common.closed') }}
                </small>
              </label>
            </div>

            <div v-if="selectedOverride.is_active" class="calendar-exceptions-times">
              <div>
                <label>{{ t('availability.startTime') }}</label>
                <input v-model="selectedOverride.start_time" type="time" />
              </div>

              <span></span>

              <div>
                <label>{{ t('availability.endTime') }}</label>
                <input v-model="selectedOverride.end_time" type="time" />
              </div>
            </div>

            <button
              type="button"
              class="calendar-exceptions-remove"
              @click="emit('remove-override', selectedOverride.date)"
            >
              <i class="bi bi-trash"></i>
              {{ t('availability.removeOverride') }}
            </button>
          </template>

          <template v-else>
            <div class="calendar-exceptions-empty">
              <i class="bi bi-calendar-check"></i>
              <h4>{{ t('availability.usesWeeklySchedule') }}</h4>
              <p>{{ t('availability.addOverrideDescription') }}</p>
            </div>

            <button
              type="button"
              class="calendar-exceptions-add"
              @click="emit('add-override')"
            >
              <i class="bi bi-calendar-plus"></i>
              {{ t('availability.overrideThisDate') }}
            </button>
          </template>
        </template>

        <div v-else class="calendar-exceptions-empty">
          <i class="bi bi-calendar-event"></i>
          <h4>{{ t('availability.selectDate') }}</h4>
          <p>{{ t('availability.selectDateDescription') }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.calendar-exceptions-header {
  margin-bottom: 24px;
}

.calendar-exceptions-header h3 {
  font-size: 20px;
  font-weight: 950;
  margin: 0 0 6px;
  color: var(--slot-text);
}

.calendar-exceptions-header p {
  margin: 0;
  color: var(--slot-muted);
  font-size: 14px;
}

.calendar-exceptions-layout {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 24px;
  align-items: start;
}

.calendar-exceptions-calendar {
  min-width: 0;
}

.calendar-exceptions-editor {
  border: 1.5px solid var(--slot-border);
  border-radius: 22px;
  background: #f8fbff;
  padding: 22px;
  min-height: 100%;
}

.availability-day--override {
  border-color: #f59e0b;
  background: #fffbeb;
}

.availability-day-dot--override,
.calendar-exceptions-dot {
  background: #f59e0b !important;
}

.calendar-exceptions-selected span {
  display: block;
  color: var(--slot-muted);
  font-size: 12px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 6px;
}

.calendar-exceptions-selected h4 {
  margin: 0 0 20px;
  font-size: 24px;
  font-weight: 950;
  color: var(--slot-text);
}

.calendar-exceptions-status {
  margin-bottom: 20px;
}

.calendar-exceptions-times {
  display: grid;
  grid-template-columns: 1fr 24px 1fr;
  gap: 12px;
  align-items: end;
}

.calendar-exceptions-times label {
  display: block;
  margin-bottom: 8px;
  color: var(--slot-muted);
  font-size: 12px;
  font-weight: 800;
}

.calendar-exceptions-times input {
  width: 100%;
  height: 50px;
  border: 1.5px solid var(--slot-border);
  border-radius: 16px;
  padding: 0 14px;
  font-weight: 800;
  color: var(--slot-text);
  background: #fff;
}

.calendar-exceptions-times > span {
  height: 2px;
  background: var(--slot-border);
  margin-bottom: 24px;
}

.calendar-exceptions-add,
.calendar-exceptions-remove {
  width: 100%;
  margin-top: 20px;
  border-radius: 16px;
  padding: 14px 16px;
  font-weight: 900;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.calendar-exceptions-add {
  border: 0;
  color: #fff;
  background: linear-gradient(135deg, var(--slot-blue), var(--slot-blue-2));
}

.calendar-exceptions-remove {
  border: 0;
  color: #e11d48;
  background: #fff1f2;
}

.calendar-exceptions-empty {
  text-align: center;
  padding: 34px 18px;
  color: var(--slot-muted);
}

.calendar-exceptions-empty i {
  display: block;
  font-size: 34px;
  color: var(--slot-blue);
  margin-bottom: 14px;
}

.calendar-exceptions-empty h4 {
  margin: 0 0 8px;
  color: var(--slot-text);
  font-size: 18px;
  font-weight: 950;
}

.calendar-exceptions-empty p {
  margin: 0;
  font-size: 14px;
}

@media (max-width: 992px) {
  .calendar-exceptions-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .calendar-exceptions-editor {
    padding: 18px;
  }

  .calendar-exceptions-times {
    grid-template-columns: 1fr;
  }

  .calendar-exceptions-times > span {
    display: none;
  }
}
</style>