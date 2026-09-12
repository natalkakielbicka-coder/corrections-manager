<script setup>
const emit = defineEmits(['add'])
</script>

<template>
  <div class="empty-state">
    <h2>Brak poprawek</h2>

    <p>Nie dodano jeszcze żadnych uwag do projektu.</p>

    <button type="button" @click="emit('add')">Dodaj pierwszą poprawkę</button>
  </div>
</template>

<style scoped>
.empty-state {
  display: flex;
  min-height: 420px;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  padding: 48px 24px;
  text-align: center;
}

.empty-state h2 {
  margin: 0;
  color: var(--color-heading);
  font-size: clamp(28px, 4vw, 36px);
}

.empty-state p {
  margin: 12px 0 0;
  color: var(--color-muted);
  font-size: 18px;
}

.empty-state button {
  min-height: 48px;
  margin-top: 28px;
  padding: 12px 22px;
  border: 0;
  border-radius: 8px;
  background-color: var(--color-brand);
  color: #ffffff;
  font-weight: 700;
}

.empty-state button:hover {
  background-color: var(--color-brand-hover);
}
</style>
