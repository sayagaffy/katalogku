<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <span v-if="loading" class="loading-spinner"></span>
    <slot v-else />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'button',
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'ghost'].includes(value),
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  fullWidth: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['click'])

const buttonClasses = computed(() => {
  const classes = [
    'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-300',
    'focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800',
  ]

  // Size classes
  const sizeClasses = {
    sm: 'px-4 py-2 text-sm',
    md: 'px-5 py-2.5 text-base',
    lg: 'px-7 py-3.5 text-lg',
  }
  classes.push(sizeClasses[props.size])

  // Variant classes with dark mode support
  const variantClasses = {
    primary:
      'bg-gradient-to-r from-primary-600 to-secondary-600 dark:from-primary-500 dark:to-secondary-500 text-white hover:from-primary-700 hover:to-secondary-700 dark:hover:from-primary-600 dark:hover:to-secondary-600 focus:ring-primary-500 dark:focus:ring-primary-400 disabled:from-primary-300 disabled:to-secondary-300 dark:disabled:from-primary-800 dark:disabled:to-secondary-800 shadow-lg hover:shadow-glow transform hover:scale-105 active:scale-95',
    secondary:
      'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 focus:ring-gray-500 dark:focus:ring-gray-400 disabled:bg-gray-100 dark:disabled:bg-gray-800',
    danger:
      'bg-red-600 dark:bg-red-500 text-white hover:bg-red-700 dark:hover:bg-red-600 focus:ring-red-500 dark:focus:ring-red-400 disabled:bg-red-300 dark:disabled:bg-red-800 shadow-lg hover:shadow-red-500/50',
    ghost:
      'bg-transparent text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 focus:ring-primary-500 dark:focus:ring-primary-400 disabled:text-primary-300 dark:disabled:text-primary-700',
  }
  classes.push(variantClasses[props.variant])

  // Full width
  if (props.fullWidth) {
    classes.push('w-full')
  }

  // Disabled cursor
  if (props.disabled || props.loading) {
    classes.push('cursor-not-allowed opacity-60')
  }

  return classes.join(' ')
})

function handleClick(event) {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>
.loading-spinner {
  display: inline-block;
  width: 1em;
  height: 1em;
  border: 2px solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: spin 0.75s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
