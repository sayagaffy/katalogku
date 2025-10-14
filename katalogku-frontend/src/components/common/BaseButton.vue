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
    'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200',
    'focus:outline-none focus:ring-2 focus:ring-offset-2',
  ]

  // Size classes
  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-base',
    lg: 'px-6 py-3 text-lg',
  }
  classes.push(sizeClasses[props.size])

  // Variant classes
  const variantClasses = {
    primary:
      'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 disabled:bg-blue-300',
    secondary:
      'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-500 disabled:bg-gray-100',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 disabled:bg-red-300',
    ghost:
      'bg-transparent text-blue-600 hover:bg-blue-50 focus:ring-blue-500 disabled:text-blue-300',
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
