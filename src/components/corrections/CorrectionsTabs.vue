<script setup>
defineProps({
  activeTab: {
    type: String,
    required: true,
  },
  counts: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['change'])

const tabs = [
  {
    value: 'active',
    label: 'Aktywne',
  },
  {
    value: 'review',
    label: 'Do weryfikacji',
  },
  {
    value: 'ready',
    label: 'Gotowe',
  },
]
</script>

<template>
  <div class="corrections-tabs" role="tablist" aria-label="Statusy poprawek">
    <button
      v-for="tab in tabs"
      :key="tab.value"
      class="corrections-tabs__button"
      :class="{ 'corrections-tabs__button--active': activeTab === tab.value }"
      type="button"
      role="tab"
      :aria-selected="activeTab === tab.value"
      @click="emit('change', tab.value)"
    >
      {{ tab.label }}
      <span class="corrections-tabs__count">
        {{ counts[tab.value] }}
      </span>
    </button>
  </div>
</template>

<style scoped>
.corrections-tabs {
  display: flex;
  width: 100%;
  gap: 36px;
  overflow-x: auto;
  border-bottom: 1px solid var(--color-border);
  scrollbar-width: none;
}

.corrections-tabs::-webkit-scrollbar {
  display: none;
}

.corrections-tabs__button {
  position: relative;
  display: flex;
  flex-shrink: 0;
  align-items: center;
  gap: 6px;
  padding: 16px 0;
  border: 0;
  background: none;
  color: var(--color-muted);
  font: inherit;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
}

.corrections-tabs__button::after {
  position: absolute;
  right: 0;
  bottom: -1px;
  left: 0;
  height: 3px;
  background-color: transparent;
  content: '';
}

.corrections-tabs__button:hover {
  color: var(--color-heading);
}

.corrections-tabs__button--active {
  color: var(--color-heading);
}

.corrections-tabs__button--active::after {
  background-color: var(--color-brand);
}

.corrections-tabs__count {
  color: inherit;
  font-weight: 500;
}

@media (max-width: 479px) {
  .corrections-tabs {
    gap: 24px;
  }

  .corrections-tabs__button {
    padding-block: 14px;
    font-size: 13px;
  }
}
</style>
