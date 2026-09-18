<script setup>
defineProps({
  currentUserName: {
    type: String,
    required: true,
  },
  canChangeUser: {
    type: Boolean,
    default: true,
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
    </div>

    <div class="project-board__actions">
      <div class="project-board__user">
        <span class="project-board__avatar" aria-hidden="true">
          {{ currentUserName.charAt(0).toUpperCase() }}
        </span>

        <div class="project-board__user-details">
          <span>Pracujesz jako</span>
          <strong>{{ currentUserName }}</strong>
        </div>

        <button
          v-if="canChangeUser"
          class="project-board__change-user"
          type="button"
          @click="$emit('change-user')"
        >
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
  font-size: clamp(24px, 3vw, 36px);
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

.project-board__actions {
  display: flex;
  align-items: center;
  gap: 24px;
}

.project-board__user {
  display: flex;
  align-items: center;
  gap: 10px;
}

.project-board__user strong {
  color: var(--color-heading);
}

.project-board__change-user {
  padding: 2px 4px;
  border: 0;
  background: none;
  color: var(--color-brand);
  font: inherit;
  font-size: 10px;
  font-weight: 600;
  line-height: 1;
  cursor: pointer;
}

.project-board__change-user:hover {
  color: var(--color-brand-hover);
  text-decoration: underline;
}

.project-board__avatar {
  display: flex;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: var(--color-brand-light);
  color: var(--color-brand);
  font-size: 14px;
  font-weight: 800;
}

.project-board__user-details {
  display: grid;
  gap: 1px;
  text-align: left;
}

.project-board__user-details span {
  color: var(--color-muted);
  font-size: 10px;
}

.project-board__user-details strong {
  color: var(--color-heading);
  font-size: 13px;
}

@media (max-width: 767px) {
  .project-board__header {
    flex-direction: column;
    align-items: stretch;
    gap: 24px;
  }

  .project-board__actions {
    width: 100%;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 16px;
  }

  .project-board__user {
    min-width: 0;
  }

  .project-board__actions > button:last-child {
    width: 100%;
  }

  .button {
    width: 100%;
  }

  h1 {
    font-size: clamp(24px, 8vw, 32px);
  }
}

@media (max-width: 479px) {
  .project-board__avatar {
    width: 36px;
    height: 36px;
    font-size: 12px;
  }

  .project-board__user-details span {
    display: none;
  }

  .project-board__user-details strong {
    display: block;
    max-width: 120px;
    overflow: hidden;
    font-size: 12px;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .project-board__change-user {
    font-size: 10px;
  }
}
</style>
