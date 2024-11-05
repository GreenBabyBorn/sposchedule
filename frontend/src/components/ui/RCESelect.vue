<script setup lang="ts">
  import {
    defineProps,
    defineEmits,
    ref,
    onMounted,
    onBeforeUnmount,
  } from 'vue';
  type Option = { name: string; value: string };
  const props = defineProps<{
    options: Option[];
    modelValue: any;
    placeholder?: string;
  }>();

  const emit = defineEmits(['update:modelValue']);

  const selectedValue = ref(props.modelValue);
  const isDropdownVisible = ref(false);

  const selectOption = (option: Option) => {
    selectedValue.value = option;
    emit('update:modelValue', option.name);
    isDropdownVisible.value = false;
    searchTerm.value = option.name;
  };

  const dropdownRef = ref<HTMLElement | null>(null);
  const inputRef = ref<HTMLElement | null>(null);
  const dropdownStyles = ref({});
  const filteredOptions = ref(props.options);
  const highlightedIndex = ref(0);
  const searchTerm = ref('');

  const filterOptions = () => {
    if (searchTerm.value === '') {
      filteredOptions.value = props.options;
    } else {
      filteredOptions.value = props.options.filter(option =>
        option.name.toLowerCase().includes(searchTerm.value.toLowerCase())
      );
    }
    highlightedIndex.value = 0; // Сброс выделенной опции при новой фильтрации
  };

  const handleClickOutside = (event: MouseEvent) => {
    if (
      inputRef.value &&
      dropdownRef.value &&
      !inputRef.value.contains(event.target as Node) && // Проверка на клик вне инпута
      !dropdownRef.value.contains(event.target as Node)
    ) {
      isDropdownVisible.value = false;
    }
  };

  const updateDropdownPosition = () => {
    if (inputRef.value && dropdownRef.value) {
      const rect = inputRef.value.getBoundingClientRect();
      dropdownStyles.value = {
        top: `${rect.bottom + window.scrollY}px`,
        left: `${rect.left + window.scrollX}px`,
        // width: `${rect.width}px`,
        // 'z-index': '5111',
      };
    }
  };

  const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'ArrowDown') {
      // Перемещение вниз по опциям
      highlightedIndex.value = Math.min(
        highlightedIndex.value + 1,
        filteredOptions.value.length - 1
      );
      event.preventDefault();
    } else if (event.key === 'ArrowUp') {
      // Перемещение вверх по опциям
      highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
      event.preventDefault();
    } else if (event.key === 'Enter') {
      // Выбор текущей опции
      if (filteredOptions.value[highlightedIndex.value]) {
        selectOption(filteredOptions.value[highlightedIndex.value]);
      }
    }
  };

  // Обновляем позицию на маунте и при изменении состояния
  onMounted(() => {
    window.addEventListener('resize', updateDropdownPosition);
    window.addEventListener('click', handleClickOutside);

    // updateDropdownPosition();
  });

  onBeforeUnmount(() => {
    window.removeEventListener('resize', updateDropdownPosition);
    window.removeEventListener('click', handleClickOutside);
  });
</script>

<template>
  <div
    class="relative inline-flex w-full cursor-pointer select-none rounded-md border border-surface-300 bg-surface-0 text-left transition-all duration-200 hover:border-surface-400 invalid:hover:border-red-500 invalid:focus:ring-red-200 dark:border-surface-600 dark:border-surface-700 dark:bg-surface-950 dark:hover:border-surface-600"
  >
    <input
      ref="inputRef"
      v-model="searchTerm"
      :placeholder="props.placeholder"
      class="relative block w-[1%] flex-auto appearance-none overflow-hidden overflow-ellipsis whitespace-nowrap rounded-none border-0 bg-transparent px-3 py-2 leading-[normal] text-surface-800 transition duration-200 placeholder:text-surface-400 focus:shadow-none focus:outline-none dark:text-white/80 dark:placeholder:text-surface-500"
      type="text"
      @input="filterOptions"
      @focus="isDropdownVisible = true"
      @keydown="handleKeyDown"
    />
    <div
      ref="dropdownBtnRef"
      class="flex w-12 shrink-0 items-center justify-center rounded-r-md bg-transparent text-surface-500"
      data-pc-section="dropdown"
      @click.stop="isDropdownVisible = !isDropdownVisible"
    >
      <svg
        width="14"
        height="14"
        viewBox="0 0 14 14"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
        class=""
        data-pc-section="dropdownicon"
      >
        <path
          d="M7.01744 10.398C6.91269 10.3985 6.8089 10.378 6.71215 10.3379C6.61541 10.2977 6.52766 10.2386 6.45405 10.1641L1.13907 4.84913C1.03306 4.69404 0.985221 4.5065 1.00399 4.31958C1.02276 4.13266 1.10693 3.95838 1.24166 3.82747C1.37639 3.69655 1.55301 3.61742 1.74039 3.60402C1.92777 3.59062 2.11386 3.64382 2.26584 3.75424L7.01744 8.47394L11.769 3.75424C11.9189 3.65709 12.097 3.61306 12.2748 3.62921C12.4527 3.64535 12.6199 3.72073 12.7498 3.84328C12.8797 3.96582 12.9647 4.12842 12.9912 4.30502C13.0177 4.48162 12.9841 4.662 12.8958 4.81724L7.58083 10.1322C7.50996 10.2125 7.42344 10.2775 7.32656 10.3232C7.22968 10.3689 7.12449 10.3944 7.01744 10.398Z"
          fill="currentColor"
        ></path>
      </svg>
    </div>
    <Teleport to="body">
      <div
        v-if="isDropdownVisible && filteredOptions.length > 0"
        ref="dropdownRef"
        :style="dropdownStyles"
        class="absolute rounded-md border border-surface-300 bg-surface-0 text-surface-700 shadow-md dark:border-surface-700 dark:bg-surface-900 dark:text-white/80"
      >
        <ul class="max-h-60 overflow-auto">
          <li
            v-for="(option, index) in filteredOptions"
            :key="option.name"
            :class="{
              'bg-blue-500 text-white': index === highlightedIndex,
              'cursor-pointer p-2': true,
            }"
            class="relative m-0 mt-[2px] flex cursor-pointer items-center overflow-hidden whitespace-nowrap rounded border-0 px-3 py-2 leading-none transition-shadow duration-200 first:mt-0 hover:bg-surface-100 dark:hover:bg-[rgba(255,255,255,0.03)]"
            @click="selectOption(option as any)"
            @mouseover="highlightedIndex = index"
          >
            {{ option.name }}
          </li>
        </ul>
        <div
          v-if="isDropdownVisible && filteredOptions.length === 0"
          class="p-2 text-center"
        >
          Нет совпадений
        </div>
      </div>
    </Teleport>
  </div>
</template>
