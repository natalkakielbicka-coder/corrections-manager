<script setup>
import { computed, ref } from 'vue'
import { usePages } from '../../composables/usePages'
import { correctionStatuses } from '../../constants/correctionStatuses'
import CorrectionComments from '../comments/CorrectionComments.vue'
import ImageLightbox from '../ui/ImageLightbox.vue'
import { formatDateTime } from '../../utils/dateFormatters'

const props = defineProps({
  correction: {
    type: Object,
    required: true,
  },
  number: {
    type: Number,
    required: true,
  },
  showComments: {
    type: Boolean,
    default: true,
  },
})

const { getPageById } = usePages()

const selectedPage = computed(() => {
  return getPageById(props.correction.pageId)
})

const isImagePreviewOpen = ref(false)

const isCommentsOpen = ref(false)

const emit = defineEmits(['add-comment', 'update-status', 'edit', 'delete', 'delete-comment'])
</script>

<template>
  <article class="correction-card" :class="{ 'correction-card--without-comments': !showComments }">
    <div class="correction-card__content">
      <div class="correction-card__header">
        <h2 class="correction-card__title">
          <span>{{ number }}.</span>
          {{ correction.title }}
        </h2>
        <select
          class="correction-card__status"
          :class="correctionStatuses[correction.status]?.className"
          :value="correction.status"
          aria-label="Zmień status poprawki"
          @change="
            emit('update-status', {
              correctionId: correction.id,
              status: $event.target.value,
            })
          "
        >
          <option
            v-for="(statusData, statusValue) in correctionStatuses"
            :key="statusValue"
            :value="statusValue"
          >
            {{ statusData.label }}
          </option>
        </select>
      </div>

      <div class="correction-card__meta">
        <span v-if="correction.author"> Zgłaszający: {{ correction.author }} </span>

        <time v-if="correction.createdAt" :datetime="correction.createdAt">
          {{ formatDateTime(correction.createdAt) }}
        </time>
      </div>

      <p class="correction-card__description">
        {{ correction.description }}
      </p>

      <a
        v-if="selectedPage"
        class="correction-card__page"
        :href="selectedPage.url"
        target="_blank"
        rel="noopener noreferrer"
      >
        <span aria-hidden="true">🔗</span>
        {{ selectedPage.title }}
      </a>

      <button
        v-if="correction.imageUrl"
        class="correction-card__image"
        type="button"
        aria-label="Powiększ załączone zdjęcie"
        @click="isImagePreviewOpen = true"
      >
        <img :src="correction.imageUrl" :alt="`Załącznik do poprawki: ${correction.title}`" />
      </button>

      <button
        v-if="showComments"
        class="correction-card__comments-toggle"
        type="button"
        :aria-expanded="isCommentsOpen"
        @click="isCommentsOpen = !isCommentsOpen"
      >
        <span aria-hidden="true">💬</span>

        Komentarze ({{ correction.comments.length }})

        <span aria-hidden="true">
          {{ isCommentsOpen ? '▲' : '▼' }}
        </span>
      </button>

      <div class="correction-card__actions">
        <button type="button" @click="emit('edit', correction.id)">Edytuj</button>

        <span aria-hidden="true">|</span>

        <button
          class="correction-card__delete"
          type="button"
          @click="emit('delete', correction.id)"
        >
          Usuń
        </button>
      </div>
    </div>

    <div v-if="showComments && isCommentsOpen" class="correction-card__comments-panel">
      <CorrectionComments
        :comments="correction.comments"
        :correction-id="correction.id"
        @add="emit('add-comment', $event)"
        @delete="emit('delete-comment', $event)"
      />
    </div>

    <ImageLightbox
      v-if="isImagePreviewOpen"
      :src="correction.imageUrl"
      :alt="`Załącznik do poprawki: ${correction.title}`"
      @close="isImagePreviewOpen = false"
    />
  </article>
</template>

<style scoped>
.correction-card {
  display: grid;
  grid-template-columns: 1fr;
  padding-block: 24px;
  border-bottom: 1px solid var(--color-border);
}

.correction-card:last-child {
  border-bottom: 0;
}

.correction-card__content {
  min-width: 0;
}

.correction-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  width: 100%;
}

.correction-card__title {
  min-width: 0;
  margin: 0;
  color: var(--color-heading);
  font-size: 20px;
  line-height: 1.4;
}

.correction-card__description {
  margin: 8px 0 0;
  color: var(--color-text);
  line-height: 1.6;
}

.correction-card__page {
  display: flex;
  width: fit-content;
  align-items: center;
  gap: 8px;
  margin: 8px 0 0;
  color: var(--color-brand);
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
}

.correction-card__page:hover {
  color: var(--color-brand-hover);
}

.correction-card__status {
  flex-shrink: 0;
  padding: 6px 30px 6px 12px;
  border: 0;
  border-radius: 999px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2340516c' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  background-size: 12px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  outline: none;
  appearance: none;
}

.status--new {
  background-color: #fee2e2;
  color: #b42318;
}

.status--in-progress {
  background-color: #fef3c7;
  color: #92400e;
}

.status--review {
  background-color: #ede9fe;
  color: #5b21b6;
}

.status--ready {
  background-color: #d1fae5;
  color: #067647;
}

.correction-card__actions {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 12px;
  color: var(--color-muted);
}

.correction-card__actions button {
  padding: 0;
  border: 0;
  background: none;
  color: inherit;
  font-size: 14px;
}

.correction-card__actions button:hover {
  color: var(--color-brand);
}

.correction-card__actions .correction-card__delete:hover {
  color: #b42318;
}

.correction-card > .comments {
  min-width: 0;
  padding-left: 24px;
  border-left: 1px solid var(--color-border);
}

.correction-card__image {
  display: block;
  width: min(100%, 320px);
  overflow: hidden;
  margin-top: 16px;
  padding: 0;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background-color: var(--color-background);
  cursor: zoom-in;
}

.correction-card__image img {
  width: 100%;
  max-height: 220px;
  object-fit: cover;
  transition: transform 200ms ease;
}

.correction-card__image:hover img {
  transform: scale(1.03);
}

.correction-card--without-comments {
  grid-template-columns: 1fr;
}

.correction-card--without-comments .correction-card__content {
  padding-right: 0;
}

.correction-card__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 14px;
  margin-top: 6px;
  color: #7b8986;
  font-size: 12px;
  line-height: 1.4;
}

.correction-card__meta time {
  color: #96a19f;
}

.correction-card__comments-toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  width: fit-content;
  margin: 16px 0 0 auto;
  padding: 8px 12px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background-color: #ffffff;
  color: var(--color-heading);
  font: inherit;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition:
    border-color 0.2s ease,
    color 0.2s ease,
    background-color 0.2s ease;
}

.correction-card__comments-toggle:hover {
  border-color: var(--color-brand);
  background-color: rgba(7, 153, 138, 0.05);
  color: var(--color-brand);
}

.correction-card__comments-panel {
  margin-top: 20px;
  padding: 20px;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  background-color: #f8faf9;
}

@media (max-width: 991px) {
  .correction-card__header {
    align-items: flex-start;
  }

  .correction-card__status {
    flex-shrink: 0;
  }
}

@media (max-width: 479px) {
  .correction-card__header {
    flex-direction: column;
    gap: 10px;
  }

  .correction-card__status {
    align-self: flex-end;
  }

  .correction-card__comments-toggle {
    justify-content: center;
    width: 100%;
  }
}
</style>
