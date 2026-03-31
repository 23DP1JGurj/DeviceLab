<template>
  <div class="autocomplete" @focusout="handleFocusOut">
    <input
      ref="inputEl"
      class="control autocompleteInput"
      :value="modelValue"
      :placeholder="placeholder"
      type="text"
      autocomplete="off"
      @input="handleInput"
      @focus="isOpen = true"
      @keydown.down.prevent="moveActive(1)"
      @keydown.up.prevent="moveActive(-1)"
      @keydown.enter.prevent="chooseActive"
      @keydown.esc="close"
    />

    <div v-if="isOpen && suggestions.length > 0" class="suggestions" role="listbox">
      <button
        v-for="(suggestion, index) in suggestions"
        :key="suggestion"
        class="suggestionItem"
        :class="{ active: index === activeIndex }"
        type="button"
        role="option"
        @mousedown.prevent="choose(suggestion)"
        @mouseenter="activeIndex = index"
      >
        <span class="searchIcon">⌕</span>
        <span>{{ suggestion }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  suggestions: {
    type: Array,
    default: () => [],
  },
  modelModifiers: {
    type: Object,
    default: () => ({}),
  },
  placeholder: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:modelValue', 'input', 'select'])

const inputEl = ref(null)
const isOpen = ref(false)
const activeIndex = ref(0)

function handleInput(event) {
  const value = props.modelModifiers.trim ? event.target.value.trim() : event.target.value
  emit('update:modelValue', value)
  emit('input', value)
  isOpen.value = true
}

function choose(value) {
  emit('update:modelValue', value)
  emit('select', value)
  close()
}

function chooseActive() {
  const value = props.suggestions[activeIndex.value]

  if (value) {
    choose(value)
  }
}

function moveActive(direction) {
  if (props.suggestions.length === 0) return

  isOpen.value = true
  activeIndex.value = (activeIndex.value + direction + props.suggestions.length) % props.suggestions.length
}

function close() {
  isOpen.value = false
  activeIndex.value = 0
}

function handleFocusOut(event) {
  if (!event.currentTarget.contains(event.relatedTarget)) {
    close()
  }
}

watch(
  () => props.suggestions,
  () => {
    activeIndex.value = 0
  },
)
</script>

<style scoped>
.autocomplete {
  position: relative;
}

.control {
  width: 100%;
  min-width: 0;
  min-height: 50px;
  padding: 11px 12px;
  border: 1px solid #d8e0eb;
  border-radius: 14px;
  background: #fff;
  color: #071833;
  font: inherit;
  outline: none;
  transition: border-color 160ms ease, box-shadow 160ms ease;
}

.control:hover {
  border-color: #bdc9d9;
}

.control:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.autocompleteInput {
  padding-right: 42px;
}

.autocomplete::after {
  content: "";
  position: absolute;
  right: 15px;
  top: 50%;
  width: 8px;
  height: 8px;
  border-right: 2px solid #0f172a;
  border-bottom: 2px solid #0f172a;
  pointer-events: none;
  transform: translateY(-65%) rotate(45deg);
}

.suggestions {
  position: absolute;
  z-index: 120;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  max-height: 280px;
  overflow-y: auto;
  padding: 8px;
  background: #fff;
  border: 1px solid #d8e0eb;
  border-radius: 0 0 20px 20px;
  box-shadow: 0 22px 48px rgba(15, 23, 42, 0.18);
}

.suggestionItem {
  display: flex;
  width: 100%;
  align-items: center;
  gap: 12px;
  padding: 11px 12px;
  border: 0;
  border-radius: 12px;
  background: transparent;
  color: #0f172a;
  cursor: pointer;
  font: inherit;
  text-align: left;
}

.suggestionItem:hover,
.suggestionItem.active {
  background: #f1f5f9;
}

.searchIcon {
  display: grid;
  width: 22px;
  height: 22px;
  place-items: center;
  color: #64748b;
  font-weight: 800;
}
</style>
