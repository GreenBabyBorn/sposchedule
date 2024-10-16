<script setup lang="ts">
  import Button from 'primevue/button';
</script>

<template>
  <header class="dark:text-surface-100 py-4">
    <div class="max-w-screen-xl mx-auto px-4">
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
