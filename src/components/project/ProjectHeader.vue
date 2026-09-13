<script setup>
defineProps({
  statusCounts: {
    type: Object,
    required: true,
  },
  currentUserName: {
    type: String,
    required: true,
  },
})

defineEmits(['add-correction', 'change-user'])
</script>

<template>
  <header class="project-board__header">
    <div>
      <h1>Poprawki do strony</h1>

      <p class="project-board__name">Zielony Dom</p>

      <p class="project-board__description">
        Tu wspólnie zapisujemy i omawiamy poprawki do projektu.
      </p>

      <dl class="status-summary">
        <div class="status-summary__item status-summary__item--new">
          <dt>Nowe</dt>
          <dd>{{ statusCounts.new }}</dd>
        </div>

        <div class="status-summary__item status-summary__item--progress">
          <dt>W trakcie</dt>
          <dd>{{ statusCounts.inProgress }}</dd>
        </div>

        <div class="status-summary__item status-summary__item--review">
          <dt>Do weryfikacji</dt>
          <dd>{{ statusCounts.review }}</dd>
        </div>

        <div class="status-summary__item status-summary__item--ready">
          <dt>Gotowe</dt>
          <dd>{{ statusCounts.ready }}</dd>
        </div>
      </dl>
    </div>

    <div class="project-board__actions">
      <div class="project-board__user">
        <span>
          Pracujesz jako: <strong>{{ currentUserName }}</strong>
        </span>

        <button class="project-board__change-user" type="button" @click="$emit('change-user')">
          Zmień
        </button>
      </div>

      <button class="button" type="button" @click="$emit('add-correction')">
        + Dodaj poprawkę
      </button>
    </div>
  </header>
</template>

<style scoped>
.project-board__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 32px;
  padding-bottom: 32px;
  border-bottom: 1px solid var(--color-border);
}

h1 {
  margin: 0;
  color: var(--color-heading);
  font-size: clamp(32px, 4vw, 44px);
  line-height: 1.1;
}

.project-board__name {
  margin: 8px 0 0;
  color: var(--color-heading);
  font-size: 20px;
  font-weight: 500;
}

.project-board__description {
  margin: 8px 0 0;
  color: var(--color-muted);
  line-height: 1.6;
}

.button {
  display: inline-flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 48px;
  padding: 12px 20px;
  border: 0;
  border-radius: 8px;
  background-color: var(--color-brand);
  color: #ffffff;
  font-weight: 700;
}

.button:hover {
  background-color: var(--color-brand-hover);
}

.status-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 20px 0 0;
}

.status-summary__item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.status-summary__item dt,
.status-summary__item dd {
  margin: 0;
}

.status-summary__item dd {
  display: flex;
  min-width: 22px;
  height: 22px;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.75);
}

.status-summary__item--new {
  background-color: #fee2e2;
  color: #b42318;
}

.status-summary__item--progress {
  background-color: #fef3c7;
  color: #92400e;
}

.status-summary__item--review {
  background-color: #ede9fe;
  color: #5b21b6;
}

.status-summary__item--ready {
  background-color: #d1fae5;
  color: #067647;
}

.project-board__actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 14px;
}

.project-board__user {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--color-muted);
  font-size: 12px;
}

.project-board__user strong {
  color: var(--color-heading);
}

.project-board__change-user {
  padding: 0;
  border: 0;
  background: none;
  color: var(--color-brand);
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.project-board__change-user:hover {
  color: var(--color-brand-hover);
  text-decoration: underline;
}

@media (max-width: 767px) {
  .project-board__header {
    flex-direction: column;
  }

  .button {
    width: 100%;
  }

  .project-board__actions {
    width: 100%;
    align-items: stretch;
  }

  .project-board__user {
    justify-content: flex-end;
  }
}

@media (max-width: 479px) {
  .status-summary {
    display: grid;
    grid-template-columns: 1fr;
  }

  .status-summary__item {
    justify-content: space-between;
  }
}
</style>
