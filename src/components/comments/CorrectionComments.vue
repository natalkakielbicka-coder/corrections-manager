<template>
  <section class="comments">
    <h3 class="comments__title">Komentarze</h3>

    <p class="comments__empty">Brak komentarzy.</p>

    <form class="comments__form">
      <label class="sr-only" for="comment"> Napisz komentarz </label>

      <input id="comment" type="text" placeholder="Napisz komentarz..." />

      <button type="submit">Dodaj</button>
    </form>
  </section>
</template>

<style scoped>
.comments {
  padding-left: 32px;
  border-left: 1px solid var(--color-border);
}

.comments__title {
  margin: 0;
  color: var(--color-heading);
  font-size: 18px;
}

.comments__empty {
  margin: 20px 0;
  color: var(--color-muted);
  font-size: 14px;
}

.comments__form {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}

.comments__form input {
  width: 100%;
  min-width: 0;
  min-height: 44px;
  padding: 10px 14px;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  color: var(--color-heading);
  outline: none;
}

.comments__form input::placeholder {
  color: var(--color-muted);
}

.comments__form input:focus {
  border-color: var(--color-brand);
  box-shadow: 0 0 0 3px var(--color-brand-light);
}

.comments__form button {
  flex-shrink: 0;
  min-height: 44px;
  padding: 10px 20px;
  border: 0;
  border-radius: 6px;
  background-color: var(--color-brand);
  color: #ffffff;
  font-weight: 700;
}

.comments__form button:hover {
  background-color: var(--color-brand-hover);
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

@media (max-width: 767px) {
  .comments {
    margin-top: 24px;
    padding-top: 24px;
    padding-left: 0;
    border-top: 1px solid var(--color-border);
    border-left: 0;
  }

  .comments__form {
    flex-direction: column;
  }
}
</style>
