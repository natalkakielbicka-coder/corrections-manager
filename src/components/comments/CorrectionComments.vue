<script setup>
import { ref } from 'vue'
import { formatDate } from '../../utils/dateFormatters'

const props = defineProps({
  comments: {
    type: Array,
    required: true,
  },
  correctionId: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['add'])
const newComment = ref('')

const commentInputId = `comment-${props.correctionId}`

const submitComment = () => {
  const content = newComment.value.trim()

  if (!content) return

  emit('add', {
    correctionId: props.correctionId,
    content,
  })

  newComment.value = ''
}
</script>

<template>
  <section class="comments">
    <h3 class="comments__title">Komentarze</h3>

    <div v-if="comments.length" class="comments__list">
      <article v-for="comment in comments" :key="comment.id" class="comment">
        <div class="comment__avatar" aria-hidden="true">
          {{ comment.author.charAt(0) }}
        </div>

        <div class="comment__content">
          <div class="comment__header">
            <strong>{{ comment.author }}</strong>

            <time :datetime="comment.createdAt">
              {{ formatDate(comment.createdAt) }}
            </time>
          </div>

          <p>{{ comment.content }}</p>
        </div>
      </article>
    </div>

    <p v-else class="comments__empty">Brak komentarzy.</p>

    <form class="comments__form" @submit.prevent="submitComment">
      <label class="sr-only" :for="commentInputId"> Napisz komentarz </label>

      <input
        :id="commentInputId"
        v-model="newComment"
        type="text"
        placeholder="Napisz komentarz..."
        required
      />

      <button type="submit">Dodaj</button>
    </form>
  </section>
</template>

<style scoped>
.comments__title {
  margin: 0;
  color: var(--color-heading);
  font-size: 18px;
}

.comments__list {
  display: grid;
  gap: 20px;
  margin-top: 20px;
}

.comment {
  display: grid;
  grid-template-columns: 44px minmax(0, 1fr);
  gap: 12px;
}

.comment + .comment {
  padding-top: 20px;
  border-top: 1px solid var(--color-border);
}

.comment__avatar {
  display: flex;
  width: 44px;
  height: 44px;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: var(--color-brand-light);
  color: var(--color-brand);
  font-size: 14px;
  font-weight: 800;
}

.comment__content {
  min-width: 0;
}

.comment__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.comment__header strong {
  color: var(--color-heading);
  font-size: 14px;
}

.comment__header time {
  flex-shrink: 0;
  color: var(--color-muted);
  font-size: 12px;
}

.comment__content p {
  margin: 4px 0 0;
  color: var(--color-text);
  font-size: 14px;
  line-height: 1.5;
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

@media (max-width: 479px) {
  .comment__header {
    align-items: flex-start;
    flex-direction: column;
    gap: 2px;
  }

  .comments__form {
    flex-direction: column;
  }
}
</style>
