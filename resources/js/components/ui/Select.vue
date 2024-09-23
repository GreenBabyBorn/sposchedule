<template>
    <div class="custom-select" @click="toggleDropdown">
        <!-- Отображаем выбранный элемент -->
        <div class="selected-option">
            {{ selectedOption ? selectedOption.label : placeholder }}
        </div>

        <!-- Значок стрелки -->
        <span class="arrow">&#9662;</span>

        <!-- Выпадающий список -->
        <div v-if="isOpen" class="dropdown">
            <ul>
                <li v-for="option in options" :key="option.name" :class="{ 'selected': option.value === modelValue }"
                    @click="selectOption(option)">
                    {{ option.name }}
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CustomSelect',
    props: {
        options: {
            type: Array,
            required: true,
            default: () => []
        },
        modelValue: [String, Number, Object],
        placeholder: {
            type: String,
            default: 'Select...'
        }
    },
    data() {
        return {
            isOpen: false
        };
    },
    computed: {
        selectedOption() {
            return this.options.find(option => option.value === this.modelValue) || null;
        }
    },
    methods: {
        toggleDropdown() {
            this.isOpen = !this.isOpen;
        },
        selectOption(option) {
            this.$emit('update:modelValue', option.value);
            this.isOpen = false;
        },
        closeDropdown() {
            this.isOpen = false;
        }
    },
    mounted() {
        // Закрытие выпадающего списка при клике вне компонента
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },
    methods: {
        toggleDropdown() {
            this.isOpen = !this.isOpen;
        },
        selectOption(option) {
            this.$emit('update:modelValue', option.value);
            this.isOpen = false;
        },
        handleClickOutside(event) {
            if (!this.$el.contains(event.target)) {
                this.closeDropdown();
            }
        },
        closeDropdown() {
            this.isOpen = false;
        }
    }
};
</script>

<style scoped>
.custom-select {
    position: relative;
    width: 200px;
    cursor: pointer;
    user-select: none;
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 4px;
}

.selected-option {
    padding-right: 20px;
}

.arrow {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
}

.dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

.dropdown ul {
    list-style-type: none;
    margin: 0;
    padding: 0;
}

.dropdown li {
    padding: 10px;
    cursor: pointer;
}

.dropdown li.selected {
    background-color: #007bff;
    color: white;
}

.dropdown li:hover {
    background-color: #f0f0f0;
}
</style>