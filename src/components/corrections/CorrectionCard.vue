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
  <article
    class="correction-card"
    :class="{ 'correction-card--without-comments': !showComments }"
    :data-status="correction.status"
  >
    <div class="correction-card__content">
      <div
        class="correction-card__main"
        :class="{ 'correction-card__main--with-image': correction.imageUrl }"
      >
        <button
          v-if="correction.imageUrl"
          class="correction-card__image"
          type="button"
          aria-label="Powiększ załączone zdjęcie"
          @click="isImagePreviewOpen = true"
        >
          <img :src="correction.imageUrl" :alt="`Załącznik do poprawki: ${correction.title}`" />
        </button>

        <div class="correction-card__details">
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
        </div>
      </div>

      <div class="correction-card__footer">
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
  padding: 14px 18px;
  border: 1px solid var(--color-border);
  border-left-width: 4px;
  border-radius: 10px;
  background-color: #ffffff;
}

.correction-card + .correction-card {
  margin-top: 12px;
}

.correction-card[data-status='new'] {
  border-left-color: #f87171;
}

.correction-card[data-status='inProgress'] {
  border-left-color: #fbbf24;
}

.correction-card[data-status='review'] {
  border-left-color: #8b5cf6;
}

.correction-card[data-status='ready'] {
  border-left-color: #34d399;
}

.correction-card__content,
.correction-card__details {
  min-width: 0;
}

.correction-card__main--with-image {
  display: grid;
  grid-template-columns: 180px minmax(0, 1fr);
  align-items: start;
  gap: 20px;
}

.correction-card__header {
  display: flex;
  width: 100%;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
}

.correction-card__title {
  min-width: 0;
  margin: 0;
  color: var(--color-heading);
  font-size: 18px;
  line-height: 1.4;
}

.correction-card__title span {
  margin-right: 4px;
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

.correction-card__description {
  margin: 8px 0 0;
  color: var(--color-text);
  line-height: 1.5;
}

.correction-card__page {
  display: flex;
  width: fit-content;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  color: var(--color-brand);
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
}

.correction-card__page:hover {
  color: var(--color-brand-hover);
}

.correction-card__image {
  display: block;
  width: 180px;
  height: 112px;
  margin: 0;
  padding: 0;
  overflow: hidden;
  border: 0;
  border-radius: 8px;
  background: none;
  cursor: pointer;
}

.correction-card__image img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.2s ease;
}

.correction-card__image:hover img {
  transform: scale(1.03);
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
  font: inherit;
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

.correction-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-top: 10px;
}

.correction-card__actions {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--color-muted);
}

.correction-card__actions button {
  padding: 0;
  border: 0;
  background: none;
  color: inherit;
  font: inherit;
  font-size: 14px;
  cursor: pointer;
}

.correction-card__actions button:hover {
  color: var(--color-brand);
}

.correction-card__actions .correction-card__delete:hover {
  color: #b42318;
}

.correction-card--without-comments {
  background-color: #fbfcfc;
}

.correction-card__comments-toggle {
  display: flex;
  width: fit-content;
  align-items: center;
  gap: 8px;
  margin: 0;
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

@media (max-width: 767px) {
  .correction-card__main--with-image {
    grid-template-columns: 1fr;
  }

  .correction-card__main--with-image .correction-card__details {
    grid-row: 1;
  }

  .correction-card__main--with-image .correction-card__image {
    grid-row: 2;
    width: 100%;
    height: 180px;
    margin-top: 14px;
  }
}

@media (max-width: 479px) {
  .correction-card {
    padding: 16px;
  }

  .correction-card__header {
    flex-direction: column;
    gap: 10px;
  }

  .correction-card__status {
    width: 100%;
    align-self: stretch;
  }

  .correction-card__footer {
    flex-direction: column-reverse;
    align-items: stretch;
    gap: 12px;
  }

  .correction-card__actions {
    justify-content: center;
  }

  .correction-card__comments-toggle {
    justify-content: center;
    width: 100%;
  }

  .correction-card__comments-panel {
    padding: 14px;
  }
}
</style>
