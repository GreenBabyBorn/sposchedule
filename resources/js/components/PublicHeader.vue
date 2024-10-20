<script setup lang="ts">
  import Button from 'primevue/button';
</script>

<template>
  <header class="py-4 dark:text-surface-100">
    <div class="mx-auto max-w-screen-xl px-4">
      <nav>
        <ul class="flex items-center justify-evenly gap-2">
          <li class="basis-1/2">
            <Button
              outlined
              severity="secondary"
              class="w-full"
              as="router-link"
              label="Пары"
              to="/"
            />
          </li>
          <li class="basis-1/2">
            <Button
              outlined
              severity="secondary"
              class="w-full"
              as="router-link"
              label="Звонки"
              to="/bells"
            />
          </li>
        </ul>
      </nav>
    </div>
  </header>
</template>
