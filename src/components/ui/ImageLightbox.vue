<script setup>
import { onMounted, onUnmounted } from 'vue'

defineProps({
  src: {
    type: String,
    required: true,
  },

  alt: {
    type: String,
    default: 'Podgląd załączonego obrazu',
  },
})

const emit = defineEmits(['close'])

const handleKeydown = (event) => {
  if (event.key === 'Escape') {
    emit('close')
  }
}

let previousBodyOverflow = ''

onMounted(() => {
  previousBodyOverflow = document.body.style.overflow
  document.body.style.overflow = 'hidden'

  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.body.style.overflow = previousBodyOverflow

  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <Teleport to="body">
    <div
      class="lightbox"
      role="dialog"
      aria-modal="true"
      :aria-label="alt"
      @click.self="emit('close')"
    >
      <button
        class="lightbox__close"
        type="button"
        aria-label="Zamknij podgląd zdjęcia"
        @click="emit('close')"
      >
        ×
      </button>

      <img class="lightbox__image" :src="src" :alt="alt" />
    </div>
  </Teleport>
</template>

<style scoped>
.lightbox {
  position: fixed;
  inset: 0;
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 72px 32px 32px;
  background-color: rgba(7, 25, 54, 0.78);
  backdrop-filter: blur(3px);
}

.lightbox__image {
  display: block;
  max-width: 100%;
  max-height: calc(100vh - 104px);
  object-fit: contain;
  border-radius: 4px;
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
}

.lightbox__close {
  position: absolute;
  top: 20px;
  right: 24px;
  display: flex;
  width: 44px;
  height: 44px;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.08);
  color: #ffffff;
  font-size: 32px;
  line-height: 1;
}

.lightbox__close:hover {
  background-color: rgba(255, 255, 255, 0.18);
}

@media (max-width: 479px) {
  .lightbox {
    padding: 64px 16px 16px;
  }

  .lightbox__image {
    max-height: calc(100vh - 80px);
  }

  .lightbox__close {
    top: 12px;
    right: 12px;
  }
}
</style>
