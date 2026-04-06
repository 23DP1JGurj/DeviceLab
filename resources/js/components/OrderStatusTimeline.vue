<template>
  <section class="timelineBox">
    <div class="timelineHead">
      <div>
        <div class="timelineTitle">{{ title }}</div>
        <div v-if="currentStatus" class="timelineCurrent">
          Pašreizējais statuss:
          <span class="statusBadge" :class="'st_' + currentStatus">{{ statusLabel(currentStatus) }}</span>
        </div>
      </div>
    </div>

    <div v-if="events.length === 0" class="timelineEmpty">
      Statusa vēsture vēl nav pieejama.
    </div>

    <div v-else class="timelineList">
      <article class="timelineItem" v-for="event in events" :key="event.id">
        <div class="timelineDot"></div>
        <div class="timelineContent">
          <div class="timelineTop">
            <span class="statusBadge" :class="'st_' + event.new_status">
              {{ statusLabel(event.new_status) }}
            </span>
            <span class="timelineDate">{{ formatDate(event.created_at) }}</span>
          </div>

          <div v-if="event.comment" class="timelineComment">{{ event.comment }}</div>

          <div v-if="event.changed_by?.name" class="timelineBy">
            Darbinieks: {{ event.changed_by.name }}
          </div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { statusLabel } from '../orderStatus'

const props = defineProps({
  histories: {
    type: Array,
    default: () => [],
  },
  currentStatus: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    default: 'Statusa vēsture',
  },
})

const events = computed(() => props.histories ?? [])

function formatDate(value) {
  if (!value) return ''

  try {
    return new Date(value).toLocaleString('lv-LV', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return value
  }
}
</script>

<style scoped>
.timelineBox {
  margin-top: 18px;
  padding-top: 16px;
  border-top: 1px solid rgba(15, 23, 42, 0.08);
}

.timelineHead {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
}

.timelineTitle {
  color: #071833;
  font-size: 14px;
  font-weight: 900;
}

.timelineCurrent {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 7px;
  color: #64748b;
  font-size: 13px;
}

.timelineEmpty {
  color: #64748b;
  font-size: 14px;
  padding: 12px 14px;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 14px;
}

.timelineList {
  position: relative;
  display: grid;
  gap: 12px;
}

.timelineList::before {
  content: "";
  position: absolute;
  left: 7px;
  top: 8px;
  bottom: 8px;
  width: 2px;
  background: #dbeafe;
}

.timelineItem {
  position: relative;
  display: grid;
  grid-template-columns: 16px minmax(0, 1fr);
  gap: 12px;
}

.timelineDot {
  position: relative;
  z-index: 1;
  width: 16px;
  height: 16px;
  margin-top: 5px;
  background: #0a66ff;
  border: 4px solid #dbeafe;
  border-radius: 999px;
}

.timelineContent {
  min-width: 0;
  padding: 12px 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
}

.timelineTop {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 8px;
}

.timelineDate,
.timelineBy {
  color: #64748b;
  font-size: 12px;
}

.timelineComment {
  margin-top: 8px;
  color: #334155;
  font-size: 14px;
  line-height: 1.45;
}

.timelineBy {
  margin-top: 6px;
}

.statusBadge {
  display: inline-flex;
  align-items: center;
  min-height: 26px;
  padding: 4px 10px;
  color: #0f172a;
  background: rgba(15, 23, 42, 0.04);
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
}

.st_new { background: rgba(37, 99, 235, 0.10); border-color: rgba(37, 99, 235, 0.22); }
.st_confirmed { background: rgba(16, 185, 129, 0.10); border-color: rgba(16, 185, 129, 0.22); }
.st_diagnostics { background: rgba(14, 165, 233, 0.10); border-color: rgba(14, 165, 233, 0.24); }
.st_in_progress { background: rgba(245, 158, 11, 0.10); border-color: rgba(245, 158, 11, 0.24); }
.st_waiting_parts { background: rgba(139, 92, 246, 0.10); border-color: rgba(139, 92, 246, 0.24); }
.st_ready { background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.24); }
.st_done { background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.24); }
.st_cancelled { background: rgba(239, 68, 68, 0.10); border-color: rgba(239, 68, 68, 0.24); }
</style>
