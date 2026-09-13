<script setup>
import { useToast } from '../../composables/useToast'

const { toast, hideToast } = useToast()
</script>

<template>
  <Transition name="toast">
    <div
      v-if="toast"
      class="toast"
      :class="{ 'toast--delete': toast.type === 'delete' }"
      role="status"
      aria-live="polite"
    >
      <span class="toast__icon" aria-hidden="true">
        {{ toast.type === 'delete' ? '×' : '✓' }}
      </span>

      <p class="toast__message">
        {{ toast.message }}
      </p>

      <button class="toast__close" type="button" aria-label="Zamknij komunikat" @click="hideToast">
        ×
      </button>
    </div>
  </Transition>
</template>

<style scoped>
.toast {
  position: fixed;
  right: 24px;
  bottom: 24px;
  z-index: 200;
  display: flex;
  width: min(360px, calc(100% - 32px));
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border: 1px solid rgba(7, 153, 138, 0.2);
  border-radius: 10px;
  background-color: #ffffff;
  box-shadow: 0 14px 40px rgba(7, 25, 54, 0.16);
}

.toast__icon {
  display: flex;
  width: 28px;
  height: 28px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: var(--color-brand-light);
  color: var(--color-brand);
  font-size: 14px;
  font-weight: 800;
}

.toast--delete {
  border-color: rgba(180, 35, 24, 0.2);
}

.toast--delete .toast__icon {
  background-color: #fee2e2;
  color: #b42318;
}

.toast__message {
  flex: 1;
  margin: 0;
  color: var(--color-heading);
  font-size: 14px;
  font-weight: 600;
  line-height: 1.4;
}

.toast__close {
  display: flex;
  width: 28px;
  height: 28px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: none;
  color: var(--color-muted);
  font-size: 20px;
  line-height: 1;
  cursor: pointer;
}

.toast__close:hover {
  background-color: var(--color-brand-light);
  color: var(--color-brand);
}

.toast-enter-active,
.toast-leave-active {
  transition:
    opacity 200ms ease,
    transform 200ms ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

@media (max-width: 479px) {
  .toast {
    right: 16px;
    bottom: 16px;
    left: 16px;
    width: auto;
  }
}
</style>
