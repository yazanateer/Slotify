<script setup lang="ts">
type DateOverride = {
  id?: number
  date: string
  is_active: boolean
  start_time: string
  end_time: string
}

defineProps<{
  dateOverrides: DateOverride[]
  selectedFullDate: string | null
}>()

const emit = defineEmits<{
  (e: 'add'): void
  (e: 'remove', date: string): void
}>()
</script>

<template>
  <div class="availability-card special-dates-card">
    <div class="availability-weekly-header">
      <div>
        <h3>Special Dates</h3>
        <span>Override availability for one specific date only.</span>
      </div>
    </div>

    <button
      type="button"
      class="special-date-add-btn"
      :disabled="!selectedFullDate"
      @click="emit('add')"
    >
      <i class="bi bi-calendar-plus"></i>
      Add selected date as special date
    </button>

    <div v-if="dateOverrides.length > 0" class="special-date-list">
      <div
        v-for="override in dateOverrides"
        :key="override.date"
        class="special-date-item"
      >
        <div class="special-date-main">
          <div>
            <strong>{{ override.date }}</strong>
            <span>
              {{ override.is_active ? 'Custom working hours' : 'Closed all day' }}
            </span>
          </div>

          <label class="availability-switch">
            <input v-model="override.is_active" type="checkbox" />

            <span class="availability-switch-track">
              <span class="availability-switch-thumb"></span>
            </span>

            <small>{{ override.is_active ? 'Open' : 'Closed' }}</small>
          </label>
        </div>

        <div v-if="override.is_active" class="special-date-times">
          <div>
            <label>Start</label>
            <input v-model="override.start_time" type="time" />
          </div>

          <span></span>

          <div>
            <label>End</label>
            <input v-model="override.end_time" type="time" />
          </div>
        </div>

        <button
          type="button"
          class="special-date-remove-btn"
          @click="emit('remove', override.date)"
        >
          <i class="bi bi-trash"></i>
          Remove
        </button>
      </div>
    </div>

    <div v-else class="special-date-empty">
      <i class="bi bi-calendar-event"></i>
      <p>No special dates added yet.</p>
    </div>
  </div>
</template>

<style scoped>
.special-dates-card {
    margin-top: 24px;
}

.special-date-add-btn {
    width: 100%;
    margin-top: 22px;
    padding: 18px 20px;
    border-radius: 18px;
    border: 1.5px dashed var(--slot-border);
    background: #fff;
    color: var(--slot-muted);
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.special-date-add-btn:hover:not(:disabled) {
    border-color: var(--slot-blue);
    color: var(--slot-blue);
    background: var(--slot-blue-soft);
}

.special-date-add-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.special-date-list {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.special-date-item {
    padding: 18px;
    border: 1.5px solid var(--slot-border);
    border-radius: 20px;
    background: #f8fbff;
}

.special-date-main {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: center;
}

.special-date-main strong {
    display: block;
    font-size: 16px;
    color: var(--slot-text);
}

.special-date-main span {
    display: block;
    margin-top: 4px;
    font-size: 13px;
    color: var(--slot-muted);
}

.special-date-times {
    margin-top: 16px;
    display: grid;
    grid-template-columns: 1fr 24px 1fr;
    align-items: end;
    gap: 12px;
}

.special-date-times label {
    display: block;
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 800;
    color: var(--slot-muted);
}

.special-date-times input {
    width: 100%;
    height: 48px;
    border: 1.5px solid var(--slot-border);
    border-radius: 14px;
    padding: 0 14px;
    font-weight: 700;
    color: var(--slot-text);
}

.special-date-times > span {
    height: 2px;
    background: var(--slot-blue);
    margin-bottom: 23px;
    border-radius: 999px;
}

.special-date-remove-btn {
    margin-top: 16px;
    border: 0;
    background: #fff1f2;
    color: #e11d48;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.special-date-empty {
    margin-top: 18px;
    padding: 28px;
    border-radius: 18px;
    background: #f8fbff;
    border: 1.5px solid var(--slot-border);
    text-align: center;
    color: var(--slot-muted);
}

.special-date-empty i {
    display: block;
    font-size: 26px;
    margin-bottom: 8px;
}

.special-date-empty p {
    margin: 0;
}

@media (max-width: 768px) {
    .special-date-main {
        flex-direction: column;
        align-items: flex-start;
    }

    .special-date-times {
        grid-template-columns: 1fr;
    }

    .special-date-times > span {
        display: none;
    }
}
</style>
