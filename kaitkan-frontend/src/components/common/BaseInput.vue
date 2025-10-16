<template>
  <div class="base-input">
    <label
      v-if="label"
      :for="inputId"
      class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 transition-colors"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 dark:text-red-400">*</span>
    </label>

    <div class="relative">
      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :maxlength="maxlength"
        class="block w-full px-4 py-3 text-base border rounded-xl shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed transition-all duration-300 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-600 focus:ring-primary-500 dark:focus:ring-primary-400 focus:border-primary-500 dark:focus:border-primary-400 hover:border-gray-400 dark:hover:border-gray-500"
        :class="{ 'border-red-300 dark:border-red-700 focus:ring-red-500 dark:focus:ring-red-400 focus:border-red-500 dark:focus:border-red-400': error }"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />

      <div
        v-if="error"
        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
      >
        <svg class="h-5 w-5 text-red-500 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd"
          />
        </svg>
      </div>
    </div>

    <p v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-400 animate-slide-down">
      {{ error }}
    </p>

    <p v-else-if="hint" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'text',
  },
  placeholder: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
  maxlength: {
    type: [String, Number],
    default: null,
  },
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

const inputId = computed(() => {
  return `input-${Math.random().toString(36).substr(2, 9)}`
})

const inputClasses = computed(() => {
  const classes = [
    'block w-full px-4 py-3 border rounded-xl shadow-sm',
    'placeholder-gray-400 dark:placeholder-gray-500',
    'focus:outline-none focus:ring-2 focus:ring-offset-0',
    'disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed',
    'transition-all duration-300',
    'bg-white dark:bg-gray-900',
    'text-gray-900 dark:text-gray-100',
  ]

  if (props.error) {
    classes.push(
      'border-red-300 dark:border-red-700',
      'focus:ring-red-500 dark:focus:ring-red-400',
      'focus:border-red-500 dark:focus:border-red-400'
    )
  } else {
    classes.push(
      'border-gray-300 dark:border-gray-600',
      'focus:ring-primary-500 dark:focus:ring-primary-400',
      'focus:border-primary-500 dark:focus:border-primary-400',
      'hover:border-gray-400 dark:hover:border-gray-500'
    )
  }

  return classes.join(' ')
})

function handleInput(event) {
  emit('update:modelValue', event.target.value)
}

function handleBlur(event) {
  emit('blur', event)
}

function handleFocus(event) {
  emit('focus', event)
}
</script>

<style scoped>
.base-input {
  margin-bottom: 1rem;
}

.base-input:last-child {
  margin-bottom: 0;
}
</style>
