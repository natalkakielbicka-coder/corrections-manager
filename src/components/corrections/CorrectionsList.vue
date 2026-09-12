<script setup>
import CorrectionCard from './CorrectionCard.vue'

defineProps({
  corrections: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['add-comment', 'update-status', 'edit', 'delete'])
</script>

<template>
  <div class="corrections-list">
    <CorrectionCard
      v-for="(correction, index) in corrections"
      :key="correction.id"
      :correction="correction"
      :number="index + 1"
      @add-comment="emit('add-comment', $event)"
      @update-status="emit('update-status', $event)"
      @edit="emit('edit', $event)"
      @delete="emit('delete', $event)"
    />
  </div>
</template>

<style scoped>
.corrections-list {
  display: grid;
}
</style>
