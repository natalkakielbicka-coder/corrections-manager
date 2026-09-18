<script setup>
import { ref } from 'vue'
import { formatDateTime } from '../../utils/dateFormatters'

const props = defineProps({
  comments: {
    type: Array,
    required: true,
  },
  correctionId: {
    type: Number,
    required: true,
  },
  isSubmitting: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['add', 'delete'])
const newComment = ref('')

const commentInputId = `comment-${props.correctionId}`

const submitComment = () => {
  if (props.isSubmitting) return

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
          {{ comment.author?.charAt(0).toUpperCase() || '?' }}
        </div>

        <div class="comment__content">
          <strong class="comment__author">
            {{ comment.author || 'Nieznany użytkownik' }}
          </strong>

          <time class="comment__date" :datetime="comment.createdAt">
            {{ formatDateTime(comment.createdAt) }}
          </time>

          <p class="comment__text">
            {{ comment.content }}
          </p>

          <button
            class="comment__delete"
            type="button"
            :aria-label="`Usuń komentarz użytkownika ${comment.author || 'nieznanego'}`"
            @click="
              emit('delete', {
                correctionId,
                commentId: comment.id,
              })
            "
          >
            ×
          </button>
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
        :disabled="isSubmitting"
        required
      />

      <button type="submit" :disabled="isSubmitting">
        {{ isSubmitting ? 'Dodawanie...' : 'Dodaj' }}
      </button>
    </form>
  </section>
</template>

<style scoped>
.comments__title {
  margin: 0;
  color: var(--color-heading);
  font-size: 16px;
}

.comments__list {
  display: grid;
  gap: 14px;
  margin-top: 16px;
}

.comment {
  display: grid;
  grid-template-columns: 36px minmax(0, 1fr);
  align-items: center;
  gap: 10px;
}

.comment + .comment {
  padding-top: 14px;
  border-top: 1px solid var(--color-border);
}

.comment__avatar {
  display: flex;
  width: 36px;
  height: 36px;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: var(--color-brand-light);
  color: var(--color-brand);
  font-size: 12px;
  font-weight: 800;
}

.comment__content {
  display: grid;
  grid-template-columns: 120px 120px minmax(0, 1fr) 24px;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.comment__author {
  grid-column: 1;
  overflow: hidden;
  color: var(--color-heading);
  font-size: 13px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.comment__date {
  grid-column: 2;
  color: var(--color-muted);
  font-size: 11px;
  white-space: nowrap;
}

.comment__text {
  grid-column: 3;
  min-width: 0;
  margin: 0;
  color: var(--color-text);
  font-size: 13px;
  line-height: 1.5;
  overflow-wrap: anywhere;
}

.comment__delete {
  display: flex;
  grid-column: 4;
  width: 24px;
  height: 24px;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: none;
  color: var(--color-muted);
  font-size: 18px;
  line-height: 1;
  cursor: pointer;
}

.comment__delete:hover {
  background-color: #fee2e2;
  color: #b42318;
}

.comments__empty {
  margin: 20px 0;
  color: var(--color-muted);
  font-size: 14px;
}

.comments__form {
  display: flex;
  gap: 8px;
  margin-top: 16px;
}

.comments__form input {
  width: 100%;
  min-width: 0;
  min-height: 40px;
  padding: 8px 12px;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  color: var(--color-heading);
  font: inherit;
  font-size: 13px;
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
  min-height: 40px;
  padding: 8px 16px;
  border: 0;
  border-radius: 6px;
  background-color: var(--color-brand);
  color: #ffffff;
  font: inherit;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
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
  .comment {
    align-items: start;
  }

  .comment__content {
    grid-template-columns: minmax(0, 1fr) 24px;
    gap: 3px 8px;
  }

  .comment__author {
    grid-column: 1;
    grid-row: 1;
  }

  .comment__delete {
    grid-column: 2;
    grid-row: 1;
  }

  .comment__date {
    grid-column: 1 / -1;
    grid-row: 2;
  }

  .comment__text {
    grid-column: 1 / -1;
    grid-row: 3;
    margin-top: 5px;
  }
}

@media (max-width: 479px) {
  .comment {
    grid-template-columns: 32px minmax(0, 1fr);
    gap: 8px;
  }

  .comment__avatar {
    width: 32px;
    height: 32px;
  }

  .comments__form {
    flex-direction: column;
  }

  .comments__form button {
    width: 100%;
  }
}
</style>
