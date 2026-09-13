<script setup>
import { ref } from 'vue'

const emit = defineEmits(['submit'])

const userName = ref('')

const handleSubmit = () => {
  const trimmedName = userName.value.trim()

  if (!trimmedName) return

  emit('submit', trimmedName)
}
</script>

<template>
  <section class="user-entry">
    <div class="user-entry__content">
      <p class="user-entry__eyebrow">Manager poprawek</p>

      <h1 class="user-entry__title">Jak masz na imię?</h1>

      <p class="user-entry__description">
        Podaj imię, którym będą podpisywane dodawane przez Ciebie poprawki i komentarze.
      </p>

      <form class="user-entry__form" @submit.prevent="handleSubmit">
        <label class="user-entry__label" for="user-name">Twoje imię</label>

        <input
          id="user-name"
          v-model="userName"
          class="user-entry__input"
          type="text"
          name="user-name"
          placeholder="Np. Natalia"
          autocomplete="name"
          autofocus
          maxlength="40"
          required
        />

        <button class="user-entry__button" type="submit">Rozpocznij</button>
      </form>
    </div>
  </section>
</template>

<style scoped>
.user-entry {
  display: grid;
  min-height: 100vh;
  padding: 24px;
  place-items: center;
}

.user-entry__content {
  width: min(100%, 480px);
  padding: 48px;
  border: 1px solid #dfe7e4;
  border-radius: 16px;
  background-color: #ffffff;
  box-shadow: 0 24px 70px rgba(17, 54, 46, 0.1);
}

.user-entry__eyebrow {
  margin: 0 0 12px;
  color: #078c7d;
  font-size: 14px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.user-entry__title {
  margin: 0;
  color: var(--color-heading);
  line-height: 1.1;
  font-size: clamp(24px, 3vw, 36px);
}

.user-entry__description {
  margin: 16px 0 32px;
  color: #65758f;
  line-height: 1.6;
}

.user-entry__form {
  display: grid;
  gap: 12px;
}

.user-entry__label {
  color: #071936;
  font-size: 14px;
  font-weight: 700;
}

.user-entry__input {
  width: 100%;
  min-height: 48px;
  padding: 0 16px;
  border: 1px solid #ccd8d4;
  border-radius: 8px;
  color: #071936;
  font: inherit;
  outline: none;
}

.user-entry__input:focus {
  border-color: #07998a;
  box-shadow: 0 0 0 3px rgba(7, 153, 138, 0.14);
}

.user-entry__button {
  min-height: 48px;
  margin-top: 8px;
  padding: 0 20px;
  border: 0;
  border-radius: 8px;
  background-color: #07998a;
  color: #ffffff;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
  transition:
    background-color 0.2s ease,
    transform 0.2s ease;
}

.user-entry__button:hover {
  background-color: #067f73;
}

.user-entry__button:active {
  transform: translateY(1px);
}

@media (max-width: 479px) {
  .user-entry {
    padding: 16px;
  }

  .user-entry__content {
    padding: 32px 24px;
  }
}
</style>
